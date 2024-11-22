<?php
include 'conexao.php';
include 'header.html';
include 'auth.php';

// Verifica se o funcionário está logado
if (!isset($_SESSION['idfuncionario'])) {
  header("Location: index.php");
  exit;
}

$idfuncionario = $_SESSION['idfuncionario'];
$errorMessage = '';

// Detecta se está em modo de edição via GET
$idform_faltas = $_GET['idform_faltas'] ?? null;
$formulario = null;
$cursosExistentes = [];
$aulasExistentes = [];

if ($idform_faltas) {
  try {
    // Busca os dados do formulário existente
    $stmtFormulario = $conn->prepare("SELECT * FROM formulario_faltas WHERE idform_faltas = ? AND idfuncionario = ?");
    $stmtFormulario->execute([$idform_faltas, $idfuncionario]);
    $formulario = $stmtFormulario->fetch(PDO::FETCH_ASSOC);

    if (!$formulario) {
      throw new Exception("Formulário de falta não encontrado ou não autorizado.");
    }

    // Busca os cursos associados ao formulário
    $stmtCursos = $conn->prepare("SELECT idcursos FROM formulario_faltas_cursos WHERE idform_faltas = ?");
    $stmtCursos->execute([$idform_faltas]);
    $cursosExistentes = $stmtCursos->fetchAll(PDO::FETCH_COLUMN);

    // Busca as aulas associadas ao formulário
    $stmtAulas = $conn->prepare("SELECT data_aula, idcursos, nome_disciplina, num_aulas FROM aulas_falta WHERE idform_faltas = ?");
    $stmtAulas->execute([$idform_faltas]);
    $aulasExistentes = $stmtAulas->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $errorMessage = "Erro ao buscar dados do formulário: " . $e->getMessage();
  } catch (Exception $e) {
    $errorMessage = $e->getMessage();
  }
}

try {
  // Consulta para buscar as informações do funcionário com base no ID
  $stmtFuncionario = $conn->prepare("SELECT nome, matricula, funcao, regime_juridico FROM funcionarios WHERE idfuncionario = ?");
  $stmtFuncionario->execute([$idfuncionario]);
  $funcionario = $stmtFuncionario->fetch(PDO::FETCH_ASSOC);

  if (!$funcionario) {
    throw new Exception("Funcionário não encontrado.");
  }

  // Consulta para buscar as disciplinas que o funcionário ministra, incluindo `idcursos` e `dia_semana`
  $stmtDisciplinas = $conn->prepare("SELECT DISTINCT disciplina, idcursos, dia_semana FROM aulas_semanal_professor WHERE idfuncionario = ?");
  $stmtDisciplinas->execute([$idfuncionario]);
  $disciplinas = $stmtDisciplinas->fetchAll(PDO::FETCH_ASSOC);
  if (!$disciplinas) {
    $disciplinas = [];
  }

  // Consulta para buscar os tipos de atividade HAE do professor
  $stmtHAE = $conn->prepare("SELECT tipo_atividade FROM horas_hae_professor WHERE idfuncionario = ?");
  $stmtHAE->execute([$idfuncionario]);
  $haeAtividades = $stmtHAE->fetchAll(PDO::FETCH_ASSOC);
  if (!$haeAtividades) {
    $haeAtividades = [];
  }
} catch (PDOException $e) {
  $errorMessage = "Erro ao buscar informações do funcionário: " . $e->getMessage();
} catch (Exception $e) {
  $errorMessage = $e->getMessage();
}

$disciplinasJson = json_encode($disciplinas);
$haeAtividadesJson = json_encode($haeAtividades);

// Definindo $tipo_falta e $motivo_falta_categoria
if ($idform_faltas && $formulario) {
  if ($formulario['datainicio'] === $formulario['datafim']) {
    $tipo_falta = 'unica';
  } else {
    $tipo_falta = 'periodo';
  }

  // Determinar a categoria do motivo da falta com base em 'motivo_falta'
  switch ($formulario['motivo_falta']) {
    case 'Falta Medica':
    case 'Comparecimento ao Medico':
    case 'Licenca Saude':
    case 'Licenca Maternidade':
      $motivo_falta_categoria = 'licenca-falta-medica';
      break;
    case 'falta-injustificada':
    case 'comparecimento-medico':
      $motivo_falta_categoria = 'falta-injustificada';
      break;
    case 'Falta justificada':
      $motivo_falta_categoria = 'faltas-justificadas';
      break;
      // Adicione outros casos conforme necessário
    default:
      $motivo_falta_categoria = '';
  }
} else {
  // Se não estiver em modo de edição, definir a partir de $_POST ou padrão
  $tipo_falta = $_POST['tipo_falta'] ?? 'unica';
  $motivo_falta_categoria = $_POST['motivo_falta_categoria'] ?? '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Atualizar as variáveis com base na submissão do formulário
  $tipo_falta = $_POST['tipo_falta'] ?? null;
  $data_unica = $_POST['data_unica'] ?? null;
  $data_inicio_periodo = $_POST['data_inicio_periodo'] ?? null;
  $data_fim_periodo = $_POST['data_fim_periodo'] ?? null;
  $motivo_falta_categoria = $_POST['motivo_falta_categoria'] ?? null;
  $motivo_falta = $_POST['motivo_falta'] ?? null;
  if ($idform_faltas) {
    // Modo de Edição: Atualiza a situação para "Proposta Enviada"
    $situacao = 'Proposta Enviada';
  } else {
    // Modo de Criação: Mantém a situação como "Aguardando Reposição"
    $situacao = 'Aguardando Reposição';
  }
  $cursosSelecionados = $_POST['cursos_por_data'] ?? []; // Captura os cursos selecionados como um array

  // Verificação se os campos obrigatórios foram preenchidos
  if (!$tipo_falta || !$motivo_falta || empty($cursosSelecionados)) {
    $errorMessage = "Por favor, preencha todos os campos obrigatórios.";
  } else {
    // Validação para garantir que cada curso tenha ao menos uma disciplina selecionada e o número de aulas
    foreach ($cursosSelecionados as $data => $cursoData) {
      if (empty($cursoData['curso'])) {
        $errorMessage = "Por favor, selecione ao menos um curso na data $data.";
        break;
      }

      foreach ($cursoData['curso'] as $cursoId) {
        // Verificar se disciplinas foram selecionadas para o curso específico
        if (empty($cursoData["disciplinas_$cursoId"])) {
          $errorMessage = "Por favor, selecione ao menos uma disciplina para o curso ID $cursoId na data $data.";
          break 2;
        }

        // Verificar se o número de aulas foi preenchido e é maior que zero
        $numAulas = $_POST["num_aulas_${data}_${cursoId}"] ?? null;
        if (!$numAulas || intval($numAulas) <= 0) {
          $errorMessage = "Por favor, preencha corretamente o número de aulas para o curso ID $cursoId na data $data.";
          break 2;
        }
      }
    }
  }

  // Se não houver erros, prosseguir com o processamento do formulário
  if (empty($errorMessage)) {
    try {
      // Iniciar transação para garantir a integridade dos dados
      $conn->beginTransaction();

      // Processar o arquivo PDF apenas se estiver sendo enviado ou se já existir em modo de edição
      if (isset($_FILES['arquivo_pdf']) && $_FILES['arquivo_pdf']['error'] === UPLOAD_ERR_OK) {
        $arquivo = $_FILES['arquivo_pdf'];
        $nomeArquivo = uniqid() . "-" . basename($arquivo['name']);
        $destinoArquivo = "uploads/$nomeArquivo";
        if (!move_uploaded_file($arquivo['tmp_name'], $destinoArquivo)) {
          throw new Exception("Erro ao fazer o upload do arquivo.");
        }
      } elseif ($idform_faltas) {
        // Em modo de edição, manter o PDF existente se não for enviado um novo
        $stmtPDF = $conn->prepare("SELECT pdf_atestado FROM formulario_faltas WHERE idform_faltas = ?");
        $stmtPDF->execute([$idform_faltas]);
        $pdfExistente = $stmtPDF->fetchColumn();
        $nomeArquivo = $pdfExistente;
      } else {
        throw new Exception("Erro no upload do arquivo: código de erro " . ($_FILES['arquivo_pdf']['error'] ?? 'N/A'));
      }

      if ($idform_faltas) {
        // Edição: Atualizar o formulário existente
        $situacao = 'Proposta Enviada'; // Definindo a situação como "Proposta Enviada" na edição

        $stmt = $conn->prepare("
            UPDATE formulario_faltas
            SET datainicio = ?, datafim = ?, pdf_atestado = ?, motivo_falta = ?, situacao = ?
            WHERE idform_faltas = ? AND idfuncionario = ?
        ");
        $stmt->execute([
          $data_unica ?? $data_inicio_periodo,
          $data_unica ?? $data_fim_periodo,
          $nomeArquivo,
          $motivo_falta,
          $situacao, // Passando a situação "Proposta Enviada"
          $idform_faltas,
          $idfuncionario
        ]);

        // Remover cursos e aulas existentes para re-inserir as atualizações
        $stmtDeleteCursos = $conn->prepare("DELETE FROM formulario_faltas_cursos WHERE idform_faltas = ?");
        $stmtDeleteCursos->execute([$idform_faltas]);

        $stmtDeleteAulas = $conn->prepare("DELETE FROM aulas_falta WHERE idform_faltas = ?");
        $stmtDeleteAulas->execute([$idform_faltas]);

        $id_formulario = $idform_faltas; // Reutiliza o ID do formulário existente
      } else {
        // Criação: Inserir um novo formulário
        $situacao = 'Aguardando Reposição'; // Definindo a situação como "Aguardando Reposição" na criação

        $stmt = $conn->prepare("INSERT INTO formulario_faltas (idfuncionario, datainicio, datafim, pdf_atestado, motivo_falta, situacao) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$idfuncionario, $data_unica ?? $data_inicio_periodo, $data_unica ?? $data_fim_periodo, $nomeArquivo, $motivo_falta, $situacao]); // Usando a situação "Aguardando Reposição"
        // Obtém o ID do formulário gerado
        $id_formulario = $conn->lastInsertId();
      }

      // Inserir dados nas tabelas relacionadas para cada data selecionada
      $stmtCurso = $conn->prepare("INSERT INTO formulario_faltas_cursos (idform_faltas, idcursos) VALUES (?, ?)");
      $stmtAulaFalta = $conn->prepare("INSERT INTO aulas_falta (idform_faltas, idcursos, num_aulas, data_aula, nome_disciplina) VALUES (?, ?, ?, ?, ?)");

      foreach ($cursosSelecionados as $data => $cursoData) {
        foreach ($cursoData['curso'] as $cursoId) {
          $cursoId = (int) $cursoId; // Confirma que é um número inteiro
          $stmtCurso->execute([$id_formulario, $cursoId]);

          // Insere disciplinas associadas a cada curso
          if (isset($cursoData["disciplinas_$cursoId"])) {
            foreach ($cursoData["disciplinas_$cursoId"] as $disciplina) {
              $num_aulas = intval($_POST["num_aulas_${data}_${cursoId}"]);
              $stmtAulaFalta->execute([$id_formulario, $cursoId, $num_aulas, $data, $disciplina]);
            }
          }
        }
      }

      // Commit da transação
      $conn->commit();

      // Redireciona para a página de histórico com mensagem de sucesso
      header("Location: home.php?msg=Formulário salvo com sucesso!");
      exit;
    } catch (PDOException $e) {
      // Rollback em caso de erro
      $conn->rollBack();
      $errorMessage = "Erro ao processar o formulário: " . $e->getMessage();
    } catch (Exception $e) {
      $conn->rollBack();
      $errorMessage = $e->getMessage();
    }
  }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulário de Faltas</title>
  <link rel="stylesheet" href="./css/faltas.css">
</head>

<body>
  <!-- Sub Cabeçalho -->
  <div class="container-sc">
    <div class="first-column-sc">
      <a href="home.php">
        <img class="logo-ita" src="img/logo-fatec_itapira.png" alt="Logo FATEC Itapira">
      </a>
      <a href="home.php">
        <img class="logo-cps" src="img/logo-cps.png" alt="Logo CPS">
      </a>
    </div>
    <div class="second-column-sc">
      <h2 class="title">Formulário de Faltas</h2>
    </div>
    <div class="third-column-sc">
      <img class="logo-padrao" src="img/logo-padrao.png" alt="Logo Padrão">
      <span class="bem-vindo-nome" style="margin: 0 10px; font-size: 16px; color: #333;">
        <p>Prof. <br><?php echo htmlspecialchars($_SESSION['nome']); ?></p>
      </span>
      <a class="btn-voltar" href="home.php">
        <btn>VOLTAR</btn>
      </a>

    </div>
  </div>

  <?php if (!empty($errorMessage)): ?>
  <div class="error-message">
    <?php echo htmlspecialchars($errorMessage); ?>
  </div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <fieldset class="fieldset">
      <legend>Informações Pessoais</legend>
      <div class="input-row">
        <label for="nome">Nome:</label>
        <input type="text" style="text-align: center; background-color: #f4f4f4; padding: 5px;" class="nome" name="nome"
          value="<?php echo htmlspecialchars($funcionario['nome']); ?>" readonly required>

        <label for="matricula">Matrícula:</label>
        <input type="text" style="text-align: center; background-color: #f4f4f4; padding: 5px;" class="matricula"
          name="matricula" value="<?php echo htmlspecialchars($funcionario['matricula']); ?>" readonly required>
        <label for="funcao">Função:</label>
        <input type="text" style="text-align: center; background-color: #f4f4f4; padding: 5px;" class="funcao"
          name="funcao" value="<?php echo htmlspecialchars($funcionario['funcao']); ?>" readonly required>
        <label for="regime">Regime Jurídico:</label>
        <input type="text" style="text-align: center; background-color: #f4f4f4; padding: 5px;" class="regime"
          name="regime" value="<?php echo htmlspecialchars($funcionario['regime_juridico']); ?>" readonly required>
      </div>
    </fieldset>

    <fieldset class="faltas">
      <legend class="legenda">Falta Referente</legend>
      <p>Selecione a Data ou o Periodo da falta: </p>
      <div class="radio-group">
        <label>
          <input type="radio" name="tipo_falta" value="unica" id="radio_unica"
            <?php echo ($tipo_falta === 'unica') ? 'checked' : ''; ?> onclick="togglePeriodo(false)">
          Falta referente ao dia:
        </label>
        <input type="date" class="data-falta" name="data_unica" id="data_unica"
          value="<?php echo htmlspecialchars($formulario['datainicio'] ?? ($_POST['data_unica'] ?? '')); ?>"
          <?php echo ($tipo_falta === 'periodo') ? 'disabled' : ''; ?> onchange="gerarSelecaoCursos()">
      </div>

      <div class="radio-group">
        <label>
          <input type="radio" name="tipo_falta" value="periodo" id="radio_periodo"
            <?php echo ($tipo_falta === 'periodo') ? 'checked' : ''; ?> onclick="togglePeriodo(true)">
          Período de
        </label>
        <input type="number" class="num-dias" name="num_dias" id="num_dias" min="1" max="15" placeholder="Nº de dias"
          value="<?php echo htmlspecialchars($_POST['num_dias'] ?? ($tipo_falta === 'periodo' ? count(array_unique(array_column($aulasExistentes, 'data_aula'))) : '')); ?>"
          <?php echo ($tipo_falta !== 'periodo') ? 'disabled' : ''; ?>>
        <label for="data-inicio-periodo">Dias: </label>
        <input type="date" class="data-inicio-periodo" name="data_inicio_periodo" id="data_inicio_periodo"
          value="<?php echo htmlspecialchars($formulario['datainicio'] ?? ($_POST['data_inicio_periodo'] ?? '')); ?>"
          <?php echo ($tipo_falta !== 'periodo') ? 'disabled' : ''; ?> onchange="gerarSelecaoCursosPeriodo()">
        <label for="data-fim-periodo" class="ate">Até: </label>
        <input type="date" class="data-fim-periodo" name="data_fim_periodo" id="data_fim_periodo"
          value="<?php echo htmlspecialchars($formulario['datafim'] ?? ($_POST['data_fim_periodo'] ?? '')); ?>"
          readonly>
      </div>
      <!-- Container para Seleções Dinâmicas -->
      <div id="selecoes-container"></div>
    </fieldset>

    <fieldset class="motivo-falta">
      <legend>Motivo da Falta</legend>

      <label for="motivo">Selecione o motivo da falta:</label>
      <select id="motivo" name="motivo_falta_categoria">
        <option value="" disabled selected>Selecione o motivo</option>
        <option value="licenca-falta-medica"
          <?php echo ($motivo_falta_categoria === 'licenca-falta-medica') ? 'selected' : ''; ?>>Licença e Falta Médica
        </option>
        <option value="falta-injustificada"
          <?php echo ($motivo_falta_categoria === 'falta-injustificada') ? 'selected' : ''; ?>>Falta Injustificada (Com
          desconto do DSR)</option>
        <option value="faltas-justificadas"
          <?php echo ($motivo_falta_categoria === 'faltas-justificadas') ? 'selected' : ''; ?>>Faltas Justificadas
        </option>
        <option value="faltas-previstas-legislacao"
          <?php echo ($motivo_falta_categoria === 'faltas-previstas-legislacao') ? 'selected' : ''; ?>>Faltas Previstas
          na Legislação Trabalhista</option>
      </select>


      <div id="opcoes_licenca-falta-medica"
        style="display: <?php echo ($motivo_falta_categoria === 'licenca-falta-medica') ? 'block' : 'none'; ?>;"
        class="motivo licenca-falta-medica">
        <label><input type="radio" class="LFM" name="motivo_falta" value="Falta Medica"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Falta Medica') ? 'checked' : ''; ?>>
          Falta Médica (Atestado médico de 1 dia)</label>
        <label><input type="radio" class="LFM" name="motivo_falta" value="Comparecimento ao Medico"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Comparecimento ao Medico') ? 'checked' : ''; ?>>
          Comparecimento ao Médico no período das <input type="time" class="small-input" name="horario_inicio_medico"
            value="<?php echo htmlspecialchars($_POST['horario_inicio_medico'] ?? ($aulasExistentes[0]['horario_inicio_medico'] ?? '')); ?>">
          às <input type="time" class="small-input" name="horario_fim_medico"
            value="<?php echo htmlspecialchars($_POST['horario_fim_medico'] ?? ($aulasExistentes[0]['horario_fim_medico'] ?? '')); ?>"></label>
        <label><input type="radio" class="LFM" name="motivo_falta" value="Licenca Saude"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Licenca Saude') ? 'checked' : ''; ?>>
          Licença-Saúde (Atestado médico igual ou superior a 2 dias)</label>
        <label><input type="radio" class="LFM" name="motivo_falta" value="Licenca Maternidade"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Licenca Maternidade') ? 'checked' : ''; ?>>
          Licença-Maternidade (Atestado médico até 15 dias)</label>
      </div>

      <div id="opcoes_falta-injustificada"
        style="display: <?php echo ($motivo_falta_categoria === 'falta-injustificada') ? 'block' : 'none'; ?>;"
        class="motivo falta-injustificada">
        <label><input type="radio" class="FI" name="motivo_falta" value="falta-injustificada"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'falta-injustificada') ? 'checked' : ''; ?>>
          Falta</label>
        <label><input type="radio" class="LFM" name="motivo_falta" value="comparecimento-medico"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'comparecimento-medico') ? 'checked' : ''; ?>>
          Atraso ou Saída Antecipada, das <input type="time" class="small-input" name="horario_inicio_atraso"
            value="<?php echo htmlspecialchars($_POST['horario_inicio_atraso'] ?? ($aulasExistentes[0]['horario_inicio_atraso'] ?? '')); ?>">
          às <input type="time" class="small-input" name="horario_fim_atraso"
            value="<?php echo htmlspecialchars($_POST['horario_fim_atraso'] ?? ($aulasExistentes[0]['horario_fim_atraso'] ?? '')); ?>"></label>
      </div>

      <div id="opcoes_faltas-justificadas"
        style="display: <?php echo ($motivo_falta_categoria === 'faltas-justificadas') ? 'block' : 'none'; ?>;"
        class="motivo faltas-justificadas">
        <label><input type="radio" class="FJ" name="motivo_falta" value="Falta justificada"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Falta justificada') ? 'checked' : ''; ?>>
          Falta por motivo de:</label>
        <textarea name="motivo_descricao"
          rows="1"><?php echo htmlspecialchars($_POST['motivo_descricao'] ?? ($formulario['motivo_descricao'] ?? '')); ?></textarea>
        <label><input type="radio" class="LFM" name="motivo_falta" value="Comparecimento ao Medico"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Comparecimento ao Medico') ? 'checked' : ''; ?>>
          Atraso ou Saída Antecipada das <input type="time" class="small-input" name="horario_inicio_atraso_justificado"
            value="<?php echo htmlspecialchars($_POST['horario_inicio_atraso_justificado'] ?? ($aulasExistentes[0]['horario_inicio_atraso_justificado'] ?? '')); ?>">
          às <input type="time" class="small-input" name="horario_fim_atraso_justificado" value="<?php echo htmlspecialchars($_POST['horario_fim_atraso_justificado'] ?? ($aulasExistentes[0]['horario_fim_atraso_justificado'] ?? '')); ?>> Por motivo de:</label>
        <textarea name=" atraso_descricao"
            rows="1"><?php echo htmlspecialchars($_POST['atraso_descricao'] ?? ($formulario['atraso_descricao'] ?? '')); ?></textarea>
      </div>

      <div id="opcoes-faltas-previstas-legislacao"
        style="display: <?php echo ($motivo_falta_categoria === 'faltas-previstas-legislacao') ? 'block' : 'none'; ?>;"
        class="motivo faltas-previstas-legislacao">
        <label><input type="radio" class="FPLT" name="motivo_falta" value="Falecimento do Conjuge"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Falecimento do Conjuge') ? 'checked' : ''; ?>>
          Falecimento de cônjuge, pai, mãe, filho (9 dias consecutivos)</label>
        <label><input type="radio" class="FPLT" name="motivo_falta" value="Falecimento Familiares"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Falecimento Familiares') ? 'checked' : ''; ?>>
          Falecimento de outros familiares (2 dias consecutivos)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Casamento"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Casamento') ? 'checked' : ''; ?>>
          Casamento (9 dias consecutivos)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Nascimento do Filho"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Nascimento do Filho') ? 'checked' : ''; ?>>
          Nascimento de filho (5 dias)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Acompanhamento da(o) Esposa(o)"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Acompanhamento da(o) Esposa(o)') ? 'checked' : ''; ?>>
          Acompanhar esposa ou companheira (Até 2 dias)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Acompanhamento do Filho"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Acompanhamento do Filho') ? 'checked' : ''; ?>>
          Acompanhar filho até 6 anos (1 dia por ano)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Doacao de Sangue"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Doacao de Sangue') ? 'checked' : ''; ?>>
          Doação voluntária de sangue (1 dia em cada 12 meses)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Alistamento Eleitor"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Alistamento Eleitor') ? 'checked' : ''; ?>>
          Alistamento como eleitor (Até 2 dias)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Depoimento Judicial"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Depoimento Judicial') ? 'checked' : ''; ?>>
          Convocação para depoimento judicial</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Comparecimento Juri"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Comparecimento Juri') ? 'checked' : ''; ?>>
          Comparecimento como jurado no Tribunal do Júri</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Servico Eleitoral"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Servico Eleitoral') ? 'checked' : ''; ?>>
          Convocação para serviço eleitoral</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Composicao Mesa Eleitoral"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Composicao Mesa Eleitoral') ? 'checked' : ''; ?>>
          Dispensa para compor mesas eleitorais</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Prova de Vestibular"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Prova de Vestibular') ? 'checked' : ''; ?>>
          Realização de Prova de Vestibular</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Justica do trabalho"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Justica do trabalho') ? 'checked' : ''; ?>>
          Comparecimento como parte na Justiça do Trabalho</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Acidente de Transporte"
            <?php echo (isset($formulario['motivo_falta']) && $formulario['motivo_falta'] === 'Acidente de Transporte') ? 'checked' : ''; ?>>
          Atrasos devido a acidentes de transporte</label>
      </div>
      <script>
      // Função para exibir as opções de acordo com a categoria dos motivos selecionada
      document.getElementById('motivo').addEventListener('change', function() {
        // Esconder as divs de opções
        document.getElementById('opcoes_licenca-falta-medica').style.display = 'none';
        document.getElementById('opcoes_falta-injustificada').style.display = 'none';
        document.getElementById('opcoes_faltas-justificadas').style.display = 'none';
        document.getElementById('opcoes-faltas-previstas-legislacao').style.display = 'none';

        // Mostrar a div correspondente à categoria dos motivos escolhida
        var categoriaSelecionada = this.value;
        if (categoriaSelecionada == 'licenca-falta-medica') {
          document.getElementById('opcoes_licenca-falta-medica').style.display = 'block';
        } else if (categoriaSelecionada == 'falta-injustificada') {
          document.getElementById('opcoes_falta-injustificada').style.display = 'block';
        } else if (categoriaSelecionada == 'faltas-justificadas') {
          document.getElementById('opcoes_faltas-justificadas').style.display = 'block';
        } else if (categoriaSelecionada == 'faltas-previstas-legislacao') {
          document.getElementById('opcoes-faltas-previstas-legislacao').style.display = 'block';
        }
      });

      // Se em modo de edição, disparar o evento para exibir as opções corretas
      <?php if ($idform_faltas): ?>
      document.addEventListener("DOMContentLoaded", function() {
        const categoria = '<?php echo htmlspecialchars($motivo_falta_categoria); ?>';
        if (categoria) {
          const motivoSelect = document.getElementById('motivo');
          motivoSelect.value = categoria;
          motivoSelect.dispatchEvent(new Event('change'));
        }
      });
      <?php endif; ?>
      </script>
    </fieldset>


    <div class="form-footer">
      <label for="arquivo_pdf">Upload do Atestado (PDF):</label>
      <input type="file" class="form-control" name="arquivo_pdf" accept=".pdf" id="fileInput"
        <?php echo ($idform_faltas) ? '' : 'required'; ?>>
      <?php if ($idform_faltas && !empty($formulario['pdf_atestado'])): ?>
      <p>Arquivo Atual: <a href="uploads/<?php echo htmlspecialchars($formulario['pdf_atestado']); ?>"
          target="_blank"><?php echo htmlspecialchars($formulario['pdf_atestado']); ?></a></p>
      <?php endif; ?>
      <ul id="fileList"></ul>


      <?php if ($idform_faltas): ?>
      <input type="hidden" name="idform_faltas" value="<?php echo htmlspecialchars($idform_faltas); ?>">
      <?php endif; ?>


      <button class="btn-enviar"
        type="submit"><?php echo ($idform_faltas) ? 'Atualizar Formulário' : 'Enviar Formulário'; ?></button>
    </div>
  </form>

  <!-- Script Consolidado para a Área de Datas, Matérias e Cursos -->
  <script>
  // Dados de disciplinas e atividades HAE obtidos do PHP
  const disciplinas = <?php echo $disciplinasJson; ?>;
  const haeAtividades = <?php echo $haeAtividadesJson; ?>;

  // Mapeamento dos dias da semana para português em maiúsculas
  const diasSemana = ["DOMINGO", "SEGUNDA", "TERÇA", "QUARTA", "QUINTA", "SEXTA", "SÁBADO"];

  // Função para obter o dia da semana em português e maiúsculo a partir de uma data
  function getDiaSemana(data) {
    const [year, month, day] = data.split('-');
    const date = new Date(year, month - 1, day); // Mês começa em 0 no JavaScript
    return diasSemana[date.getDay()]; // Usar getDay para obter o dia correto
  }

  // Função para alternar entre Falta Única e Período
  function togglePeriodo(isPeriodo) {
    document.getElementById('data_unica').disabled = isPeriodo;
    document.getElementById('num_dias').disabled = !isPeriodo;
    document.getElementById('data_inicio_periodo').disabled = !isPeriodo;
    document.getElementById('data_fim_periodo').disabled = !isPeriodo;

    if (!isPeriodo) {
      document.getElementById('num_dias').value = '';
      document.getElementById('data_inicio_periodo').value = '';
      document.getElementById('data_fim_periodo').value = '';
      document.getElementById('selecoes-container').innerHTML = '';
    } else {
      document.getElementById('data_unica').value = '';
    }
  }

  // Limitar datas para o passado e gerar seleções existentes se estiver em modo de edição
  document.addEventListener("DOMContentLoaded", function() {
    const hoje = new Date().toISOString().split('T')[0];
    document.getElementById("data_unica").setAttribute("max", hoje);
    document.getElementById("data_inicio_periodo").setAttribute("max", hoje);

    // Se estiver em modo de edição e for período, gerar as seleções existentes
    <?php if ($idform_faltas && $tipo_falta === 'periodo'): ?>
    gerarSelecaoCursosPeriodo();
    <?php elseif ($idform_faltas && $tipo_falta === 'unica'): ?>
    gerarSelecaoCursos();
    <?php endif; ?>
  });

  // Geração da data final para faltas em período
  document.getElementById('data_inicio_periodo').addEventListener('change', function() {
    const numDias = parseInt(document.getElementById('num_dias').value, 10);
    if (isNaN(numDias) || numDias <= 0) return;

    const startDate = new Date(this.value);
    let daysCounted = 0;

    while (daysCounted < numDias) {
      startDate.setDate(startDate.getDate() + 1);
      if (startDate.getDay() !== 0) { // Ignorar domingos
        daysCounted++;
      }
    }
    document.getElementById('data_fim_periodo').value = startDate.toISOString().split('T')[0];
    gerarSelecaoCursosPeriodo();
  });

  // Geração de seleção de cursos e disciplinas para uma falta única
  function gerarSelecaoCursos() {
    const selectedDate = document.getElementById('data_unica').value;
    if (!selectedDate) return;

    const container = document.getElementById('selecoes-container');
    container.innerHTML = ''; // Limpa o container para nova seleção

    const dataLabel = document.createElement('p');
    dataLabel.textContent = `Data Selecionada: ${selectedDate}`;
    container.appendChild(dataLabel);

    gerarSelecaoCursosDia(container, selectedDate);
  }

  // Geração de seleção de cursos e disciplinas para cada data em um período
  function gerarSelecaoCursosPeriodo() {
    const container = document.getElementById('selecoes-container');
    container.innerHTML = ''; // Limpa o container

    const dataInicio = new Date(document.getElementById('data_inicio_periodo').value);
    const dataFim = new Date(document.getElementById('data_fim_periodo').value);

    let currentDate = new Date(dataInicio);

    while (currentDate <= dataFim) {
      if (currentDate.getDay() !== 0) { // Ignora domingos
        const dataStr = currentDate.toISOString().split('T')[0];

        const dataLabel = document.createElement('p');
        dataLabel.textContent = `Data: ${dataStr}`;
        container.appendChild(dataLabel);

        gerarSelecaoCursosDia(container, dataStr);
      }
      currentDate.setDate(currentDate.getDate() + 1);
    }
  }

  // Função auxiliar para gerar a seleção de cursos e disciplinas por dia
  function gerarSelecaoCursosDia(container, data) {
    const cursosSelecionados = [];

    function limitarSelecaoCursos(event) {
      const cursoCheckboxes = document.querySelectorAll(`input[name="cursos_por_data[${data}][curso][]"]`);
      const checkedCount = Array.from(cursoCheckboxes).filter(c => c.checked).length;

      if (checkedCount > 2) {
        alert("Selecione no máximo 2 cursos.");
        event.target.checked = false;
      }
    }

    // Lista de cursos (ajuste conforme necessário)
    const cursos = [{
        id: 1,
        nome: "DSM"
      },
      {
        id: 2,
        nome: "GE"
      },
      {
        id: 3,
        nome: "GPI"
      },
      {
        id: 4,
        nome: "GTI"
      },
      {
        id: 5,
        nome: "HAE"
      }
    ];

    cursos.forEach(curso => {
      const checkbox = document.createElement('input');
      checkbox.type = 'checkbox';
      checkbox.name = `cursos_por_data[${data}][curso][]`;
      checkbox.value = curso.id;

      // Verifica se o curso está selecionado (em modo de edição)
      <?php if ($idform_faltas): ?>
      <?php foreach ($cursosExistentes as $cursoExistente): ?>
      if (curso.id === <?php echo $cursoExistente; ?>) {
        checkbox.checked = true;
      }
      <?php endforeach; ?>
      <?php endif; ?>

      checkbox.addEventListener('change', limitarSelecaoCursos);

      const label = document.createElement('label');
      label.textContent = ` ${curso.nome}`;
      label.prepend(checkbox);
      container.appendChild(label);

      checkbox.addEventListener('change', function() {
        if (this.checked) {
          cursosSelecionados.push(curso.id);
          if (curso.id == 5) { // HAE
            gerarSelecaoAtividadesHAE(container, data, curso.id);
          } else {
            gerarSelecaoDisciplinasDia(container, data, curso.id, cursosSelecionados.length);
          }
          gerarSelecaoNumAulas(container, data, curso.id);
        } else {
          cursosSelecionados.splice(cursosSelecionados.indexOf(curso.id), 1);
          removerSelecaoDisciplinasDia(data, curso.id);
          removerSelecaoAtividadesHAE(data, curso.id);
          removerSelecaoNumAulas(data, curso.id);
        }
      });

      // Se em modo de edição, gerar as seleções existentes
      <?php if ($idform_faltas): ?>
      <?php foreach ($aulasExistentes as $aula): ?>
      if (curso.id === <?php echo $aula['idcursos']; ?> && data === '<?php echo $aula['data_aula']; ?>') {
        // Gerar disciplinas
        gerarSelecaoDisciplinasDia(container, data, curso.id, cursosSelecionados.length);
        // Gerar número de aulas
        gerarSelecaoNumAulas(container, data, curso.id);
      }
      <?php endforeach; ?>
      <?php endif; ?>
    });
  }

  // Função para exibir tipos de atividades HAE
  function gerarSelecaoAtividadesHAE(container, data, cursoId) {
    const atividadeList = document.createElement('div');
    atividadeList.id = `atividades_${data}_${cursoId}`;

    const atividadeLabel = document.createElement('p');
    atividadeLabel.textContent = `Atividades HAE (Data: ${data}):`;
    atividadeList.appendChild(atividadeLabel);

    haeAtividades.forEach(atividade => {
      const option = document.createElement('input');
      option.type = 'checkbox';
      option.name = `hae_atividades_${data}_${cursoId}[]`;
      option.value = atividade.tipo_atividade;

      // Verifica se a atividade está selecionada (em modo de edição)
      <?php if ($idform_faltas): ?>
      <?php foreach ($aulasExistentes as $aula): ?>
      if (atividade.tipo_atividade === '<?php echo addslashes($aula['nome_disciplina']); ?>' && data ===
        '<?php echo $aula['data_aula']; ?>' && cursoId === <?php echo $aula['idcursos']; ?>) {
        option.checked = true;
      }
      <?php endforeach; ?>
      <?php endif; ?>

      const labelAtividade = document.createElement('label');
      labelAtividade.textContent = ` ${atividade.tipo_atividade}`;
      labelAtividade.prepend(option);
      atividadeList.appendChild(labelAtividade);
    });

    container.appendChild(atividadeList);
  }

  // Geração dinâmica de disciplinas baseado nas regras de seleção por curso, data e dia da semana
  function gerarSelecaoDisciplinasDia(container, data, cursoId, numCursos) {
    const disciplinaList = document.createElement('div');
    disciplinaList.id = `disciplinas_${data}_${cursoId}`;

    const disciplinaLabel = document.createElement('p');
    disciplinaLabel.textContent = `Disciplinas da Data: ${data}:`;
    disciplinaList.appendChild(disciplinaLabel);

    const diaSemanaSelecionado = getDiaSemana(data); // Obter o dia da semana selecionado

    disciplinas
      .filter(disciplina => disciplina.idcursos == cursoId && disciplina.dia_semana === diaSemanaSelecionado)
      .forEach(disciplina => {
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = `cursos_por_data[${data}][disciplinas_${cursoId}][]`;
        checkbox.value = disciplina.disciplina;

        // Verifica se a disciplina está selecionada (em modo de edição)
        <?php if ($idform_faltas): ?>
        <?php foreach ($aulasExistentes as $aula): ?>
        if (disciplina.disciplina === '<?php echo addslashes($aula['nome_disciplina']); ?>' && data ===
          '<?php echo $aula['data_aula']; ?>' && cursoId === <?php echo $aula['idcursos']; ?>) {
          checkbox.checked = true;
        }
        <?php endforeach; ?>
        <?php endif; ?>

        const labelDisciplina = document.createElement('label');
        labelDisciplina.textContent = ` ${disciplina.disciplina}`;
        labelDisciplina.prepend(checkbox);
        disciplinaList.appendChild(labelDisciplina);
      });

    container.appendChild(disciplinaList);
  }

  // Geração de Número de Aulas
  function gerarSelecaoNumAulas(container, data, cursoId) {
    const numAulasDiv = document.createElement('div');
    numAulasDiv.id = `num_aulas_${data}_${cursoId}`;

    const numAulasLabel = document.createElement('p');
    numAulasLabel.textContent = `Nº de Aulas: `;
    numAulasDiv.appendChild(numAulasLabel);

    const numAulasInput = document.createElement('input');
    numAulasInput.type = 'number';
    numAulasInput.name = `num_aulas_${data}_${cursoId}`;
    numAulasInput.min = 1;
    numAulasInput.max = (cursoId === 5) ? 2 : 4; // Limite de 2 para HAE, 4 para os outros

    // Preencher o número de aulas se em modo de edição
    <?php if ($idform_faltas): ?>
    <?php foreach ($aulasExistentes as $aula): ?>
    if (cursoId === <?php echo $aula['idcursos']; ?> && data === '<?php echo $aula['data_aula']; ?>') {
      numAulasInput.value = <?php echo (int)$aula['num_aulas']; ?>;
    }
    <?php endforeach; ?>
    <?php endif; ?>

    numAulasDiv.appendChild(numAulasInput);

    container.appendChild(numAulasDiv);
  }

  // Funções para remover seleções
  function removerSelecaoDisciplinasDia(data, cursoId) {
    const disciplinaContainer = document.getElementById(`disciplinas_${data}_${cursoId}`);
    if (disciplinaContainer) disciplinaContainer.remove();
  }

  function removerSelecaoAtividadesHAE(data, cursoId) {
    const atividadeContainer = document.getElementById(`atividades_${data}_${cursoId}`);
    if (atividadeContainer) atividadeContainer.remove();
  }

  function removerSelecaoNumAulas(data, cursoId) {
    const numAulasContainer = document.getElementById(`num_aulas_${data}_${cursoId}`);
    if (numAulasContainer) numAulasContainer.remove();
  }
  </script>

  <style>
  /* Estilização das divs que contêm os grupos de rádio */
  .radio-group {
    margin-bottom: 10px;
  }
  </style>

  <br><br>
  <footer>
    <div class="containerf">
      <a href="#">
        <img src="img/logo-governo-do-estado-sp.png" alt="Logo Governo do Estado SP">
      </a>
    </div>
  </footer>
</body>

</html>