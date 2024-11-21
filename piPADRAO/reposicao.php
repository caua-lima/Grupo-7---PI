<?php
include 'conexao.php';
include 'header.html';
include 'auth.php';

// Verifica se o funcionário está logado
if (!isset($_SESSION['idfuncionario'])) {
  header("Location: index.php");
  exit;
}

$idFuncionario = $_SESSION['idfuncionario'];
$errorMessage = '';
$successMessage = '';

// Obter o próximo ID de formulário de reposição apenas se não estiver em modo de edição
$nextIdForm = null;
if (!isset($_GET['idform_reposicao'])) {
  try {
    $stmt = $conn->query("SHOW TABLE STATUS LIKE 'formulario_reposicao'");
    $tableStatus = $stmt->fetch(PDO::FETCH_ASSOC);
    $nextIdForm = $tableStatus['Auto_increment'];
  } catch (PDOException $e) {
    $errorMessage = "Erro ao obter o próximo ID: " . $e->getMessage();
  }
}

// Buscar nome do funcionário pelo ID
try {
  $stmtNome = $conn->prepare("SELECT nome FROM funcionarios WHERE idfuncionario = ?");
  $stmtNome->execute([$idFuncionario]);
  $resultadoNome = $stmtNome->fetch(PDO::FETCH_ASSOC);
  if ($resultadoNome) {
    $nomeFuncionario = $resultadoNome['nome'];
  } else {
    throw new Exception("Nome do funcionário não encontrado.");
  }
} catch (PDOException $e) {
  $errorMessage = "Erro ao buscar nome do funcionário: " . $e->getMessage();
} catch (Exception $e) {
  $errorMessage = $e->getMessage();
}

// Consulta para buscar as aulas semanais do professor
try {
  $stmtAulas = $conn->prepare("
    SELECT dia_semana, horario_inicio, horario_fim, disciplina
    FROM aulas_semanal_professor
    WHERE idfuncionario = ?
    ORDER BY FIELD(dia_semana, 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'), horario_inicio
  ");
  $stmtAulas->execute([$idFuncionario]);
  $aulas = $stmtAulas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $errorMessage = "Erro ao buscar aulas: " . $e->getMessage();
}

// Consulta para buscar horas de HAE do professor
try {
  $stmtHAE = $conn->prepare("
    SELECT idhae, dia_semana, data_atividade, horario_inicio, horario_fim, tipo_atividade, hae_total, hae_usadas
    FROM horas_hae_professor
    WHERE idfuncionario = ?
  ");
  $stmtHAE->execute([$idFuncionario]);
  $atividadesHAE = $stmtHAE->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $errorMessage = "Erro ao buscar atividades de HAE: " . $e->getMessage();
}

// Identifica se está em modo de edição via GET
$idform_reposicao = $_GET['idform_reposicao'] ?? null;
$formularioReposicao = null;
$aulasReposicao = [];
$cursosReposicao = [];
$mesAno = date('Y-m'); // Default para criação

// Variáveis para carregar as aulas de falta e motivo da falta
$aulasFaltas = [];
$motivoFalta = '';

if ($idform_reposicao) {
  try {
    // Busca os dados do formulário de reposição existente
    $stmtReposicao = $conn->prepare("SELECT * FROM formulario_reposicao WHERE idform_reposicao = ? AND idfuncionario = ?");
    $stmtReposicao->execute([$idform_reposicao, $idFuncionario]);
    $formularioReposicao = $stmtReposicao->fetch(PDO::FETCH_ASSOC);

    if (!$formularioReposicao) {
      throw new Exception("Formulário de reposição não encontrado ou não autorizado.");
    }

    // Busca as aulas de reposição associadas
    $stmtAulasReposicao = $conn->prepare("SELECT * FROM aulas_reposicao WHERE idform_reposicao = ?");
    $stmtAulasReposicao->execute([$idform_reposicao]);
    $aulasReposicao = $stmtAulasReposicao->fetchAll(PDO::FETCH_ASSOC);

    // Busca os cursos associados à reposição
    $stmtCursosReposicao = $conn->prepare("
      SELECT c.nome_curso 
      FROM formulario_reposicao_cursos frc 
      JOIN cursos c ON frc.idcursos = c.idcursos 
      WHERE frc.idform_reposicao = ?
    ");
    $stmtCursosReposicao->execute([$idform_reposicao]);
    $cursosReposicao = $stmtCursosReposicao->fetchAll(PDO::FETCH_COLUMN);

    // Busca o motivo da falta associado
    $stmtMotivo = $conn->prepare("
      SELECT f.motivo_falta 
      FROM formulario_reposicao fr 
      JOIN formulario_faltas f ON fr.idform_faltas = f.idform_faltas 
      WHERE fr.idform_reposicao = ?
    ");
    $stmtMotivo->execute([$idform_reposicao]);
    $motivoFalta = $stmtMotivo->fetchColumn();

    // Formatar o mês e ano para o input de mês
    if (!empty($formularioReposicao['data_entrega'])) {
      $mesAno = date('Y-m', strtotime($formularioReposicao['data_entrega']));
    }

    // Identificar o idform_faltas para carregar as aulas de falta
    $idform_faltas = $formularioReposicao['idform_faltas'] ?? null;
    if ($idform_faltas) {
      // Buscar as aulas de falta associadas ao idform_faltas
      $stmtAulasFaltas = $conn->prepare("
        SELECT 
            af.data_aula, 
            af.num_aulas, 
            af.nome_disciplina
        FROM aulas_falta af
        WHERE af.idform_faltas = ?
      ");
      $stmtAulasFaltas->execute([$idform_faltas]);
      $aulasFaltas = $stmtAulasFaltas->fetchAll(PDO::FETCH_ASSOC);
    }

    if (empty($aulasFaltas)) {
      $errorMessage = "Nenhuma aula registrada para este formulário de faltas.";
    }
  } catch (PDOException $e) {
    $errorMessage = "Erro ao buscar dados de reposição: " . $e->getMessage();
  } catch (Exception $e) {
    $errorMessage = $e->getMessage();
  }
} elseif (isset($_GET['idform'])) {
  // Modo Criação: Buscar aulas de faltas e motivo
  $idform_faltas = $_GET['idform'];
  try {
    $stmtFaltas = $conn->prepare("
      SELECT 
          f.idform_faltas, 
          f.datainicio, 
          f.datafim, 
          f.motivo_falta,
          c.nome_curso,
          c.idcursos
      FROM formulario_faltas f
      JOIN formulario_faltas_cursos fc ON f.idform_faltas = fc.idform_faltas
      JOIN cursos c ON fc.idcursos = c.idcursos
      WHERE f.idform_faltas = ? AND f.idfuncionario = ?
    ");
    $stmtFaltas->execute([$idform_faltas, $idFuncionario]);
    $formularios = $stmtFaltas->fetchAll(PDO::FETCH_ASSOC);

    // Verifica se o formulário foi encontrado
    if (empty($formularios)) {
      throw new Exception("Formulário não encontrado ou não autorizado.");
    }

    // Verifica se a data de início está disponível e formata o mês e ano para o input de mês
    if (!empty($formularios[0]['datainicio'])) {
      $mesAno = date('Y-m', strtotime($formularios[0]['datainicio']));
    }

    // Buscar as aulas de falta associadas ao formulário
    $stmtAulasFaltas = $conn->prepare("
      SELECT 
          af.data_aula, 
          af.num_aulas, 
          af.nome_disciplina
      FROM aulas_falta af
      WHERE af.idform_faltas = ?
    ");
    $stmtAulasFaltas->execute([$idform_faltas]);
    $aulasFaltas = $stmtAulasFaltas->fetchAll(PDO::FETCH_ASSOC);

    if (empty($aulasFaltas)) {
      $errorMessage = "Nenhuma aula registrada para este formulário de faltas.";
    }

    // Capturar o motivo da falta
    $motivoFalta = $formularios[0]['motivo_falta'] ?? '';
  } catch (PDOException $e) {
    $errorMessage = "Erro ao buscar informações de faltas: " . $e->getMessage();
  } catch (Exception $e) {
    $errorMessage = $e->getMessage();
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Captura dos dados do formulário antes da validação
  $datasReposicao = $_POST['dataReposicao'] ?? [];
  $inicioHorarios = $_POST['inicioHorario'] ?? [];
  $teroHorarios = $_POST['teroHorario'] ?? [];
  $disciplinas = $_POST['nome_disciplina'] ?? [];
  $situacao = $_POST['situacao'] ?? 'Proposta Enviada';

  // Obter o motivo da falta associado
  $virtude = $motivoFalta ?? '';
  $data_entrega = date('Y-m-d');

  try {
    // Inicia a transação
    $conn->beginTransaction();

    // Validação inicial para garantir que os campos obrigatórios estão presentes
    if (empty($datasReposicao) || empty($inicioHorarios) || empty($teroHorarios) || empty($disciplinas)) {
      throw new Exception('Todos os campos de datas, horários e disciplinas devem ser preenchidos.');
    }

    if ($idform_reposicao) {
      // Modo Edição: Atualizar o formulário de reposição existente

      // Atualizar os dados do formulário de reposição
      $stmtUpdate = $conn->prepare("UPDATE formulario_reposicao SET virtude = ?, data_entrega = ?, situacao = ? WHERE idform_reposicao = ? AND idfuncionario = ?");
      $stmtUpdate->execute([$virtude, $data_entrega, $situacao, $idform_reposicao, $idFuncionario]);

      // Remover aulas de reposição existentes
      $stmtDeleteAulas = $conn->prepare("DELETE FROM aulas_reposicao WHERE idform_reposicao = ?");
      $stmtDeleteAulas->execute([$idform_reposicao]);

      // Remover cursos de reposição existentes
      $stmtDeleteCursos = $conn->prepare("DELETE FROM formulario_reposicao_cursos WHERE idform_reposicao = ?");
      $stmtDeleteCursos->execute([$idform_reposicao]);

      $currentIdFormReposicao = $idform_reposicao;
    } else {
      // Modo Criação: Inserir um novo formulário de reposição

      // Obter 'idform_faltas' via GET
      $idform_faltas = $_GET['idform'] ?? null;
      if (!$idform_faltas) {
        throw new Exception("ID do formulário de faltas não fornecido.");
      }

      // Inserir os dados na tabela formulario_reposicao
      $stmtInsert = $conn->prepare("INSERT INTO formulario_reposicao (virtude, data_entrega, situacao, idfuncionario, idform_faltas) VALUES (?, ?, ?, ?, ?)");
      $stmtInsert->execute([$virtude, $data_entrega, $situacao, $idFuncionario, $idform_faltas]);
      $currentIdFormReposicao = $conn->lastInsertId(); // ID do novo formulario_reposicao
    }

    // Preparação das consultas de inserção para aulas e relacionamento de tabelas
    $stmtAulas = $conn->prepare("INSERT INTO aulas_reposicao (data_reposicao, nome_disciplina, horarioinicio, horariofim, idform_reposicao) VALUES (?, ?, ?, ?, ?)");
    $stmtRelacionamento = $conn->prepare("INSERT INTO aulas_reposicoa_formulario_reposicao (idaulas_reposicao, idform_reposicao) VALUES (?, ?)");

    // Mapeamento de dias da semana para tradução
    $diasSemanaMap = [
      'Sunday' => 'Domingo',
      'Monday' => 'Segunda',
      'Tuesday' => 'Terça',
      'Wednesday' => 'Quarta',
      'Thursday' => 'Quinta',
      'Friday' => 'Sexta',
      'Saturday' => 'Sábado'
    ];

    // Inserir cada aula e verificar conflitos
    for ($i = 0; $i < count($datasReposicao); $i++) {
      if (empty($datasReposicao[$i]) || empty($inicioHorarios[$i]) || empty($teroHorarios[$i]) || empty($disciplinas[$i])) {
        throw new Exception('Todos os campos de datas, horários e disciplinas devem ser preenchidos.');
      }

      $horario_inicio_novo = $inicioHorarios[$i];
      $horario_fim_novo = $teroHorarios[$i];
      $data_reposicao = $datasReposicao[$i];
      $nome_disciplina = $disciplinas[$i];

      // Verificar conflitos de horário
      $stmtVerificaConflito = $conn->prepare("
        SELECT * FROM horas_hae_professor
        WHERE idfuncionario = ? AND data_atividade = ?
        AND horario_inicio < ? AND horario_fim > ?
      ");
      $stmtVerificaConflito->execute([$idFuncionario, $data_reposicao, $horario_fim_novo, $horario_inicio_novo]);
      $conflito = $stmtVerificaConflito->fetch(PDO::FETCH_ASSOC);

      if ($conflito) {
        throw new Exception("Conflito de horário detectado para a data: " . $data_reposicao . " no horário " . $horario_inicio_novo . " - " . $horario_fim_novo);
      }

      // Insere as aulas de reposição, incluindo o id_form_reposicao
      $stmtAulas->execute([$data_reposicao, $nome_disciplina, $horario_inicio_novo, $horario_fim_novo, $currentIdFormReposicao]);
      $idaulas_reposicao = $conn->lastInsertId();

      // Relaciona a aula ao formulário de reposição
      $stmtRelacionamento->execute([$idaulas_reposicao, $currentIdFormReposicao]);

      // Determina o dia da semana e insere na tabela horas_hae_professor
      $diaSemana = date('l', strtotime($data_reposicao));
      $diaSemanaPT = $diasSemanaMap[$diaSemana] ?? '-';
    }

    // Insere os cursos relacionados à tabela formulario_reposicao_cursos
    // Supondo que você tenha um campo de seleção múltipla para cursos no formulário com name="cursos_envolvidos[]"
    $cursosSelecionados = $_POST['cursos_envolvidos'] ?? [];
    if (!empty($cursosSelecionados)) {
      $stmtCurso = $conn->prepare("INSERT INTO formulario_reposicao_cursos (idform_reposicao, idcursos) VALUES (?, ?)");
      foreach ($cursosSelecionados as $curso) {
        $stmtCurso->execute([$currentIdFormReposicao, $curso]);
      }
    }

    if (!$idform_reposicao) {
      // Atualiza a situação do formulário de faltas original para "Proposta Enviada" apenas para novos formulários
      $stmtUpdate = $conn->prepare("UPDATE formulario_faltas SET situacao = 'Proposta Enviada' WHERE idform_faltas = ? AND idfuncionario = ?");
      $stmtUpdate->execute([$idform_faltas, $idFuncionario]);
    }

    // Confirma a transação
    $conn->commit();

    // Mensagem de sucesso
    $successMessage = "Formulário de reposição salvo com sucesso!";
    header("Location: home.php?msg=Formulário salvo com sucesso!");
    exit;
    // Redireciona para a página de histórico com mensagem de sucesso (opcional)
    // header("Location: home.php?msg=Formulário de reposição salvo com sucesso!");
    // exit;

  } catch (PDOException $e) {
    // Desfaz a transação em caso de erro
    $conn->rollBack();
    $errorMessage = "Erro ao salvar os dados: " . $e->getMessage();
  } catch (Exception $e) {
    // Desfaz a transação em caso de erro
    $conn->rollBack();
    $errorMessage = "Erro: " . $e->getMessage();
  }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <title>Planejar Reposição</title>
  <link rel="stylesheet" href="./css/reposicao.css">
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
      <h2 class="title">Formulario de Reposições</h2>
    </div>
    <div class="third-column-sc">
      <img class="logo-padrao" src="img/logo-padrao.png" alt="Logo Padrão">
      <span class="bem-vindo-nome" style="margin: 0 10px; font-size: 16px; color: #333;">
        <p>Prof. <br><?php echo htmlspecialchars($_SESSION['nome']); ?></p>
      </span>
      <a class="btn-voltar" href="#" onclick="history.back(); return false;">VOLTAR</a>

    </div>
  </div>

  <?php if (!empty($errorMessage)): ?>
  <div class="error-message">
    <?php echo htmlspecialchars($errorMessage); ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($successMessage)): ?>
  <div class="success-message">
    <?php echo htmlspecialchars($successMessage); ?>
  </div>
  <?php endif; ?>

  <form id="form-reposicao" method="POST" enctype="multipart/form-data">

    <!-- Número e Mês -->
    <div class="form-group-inline">
      <!-- Campo de Nome do Funcionário -->
      <div class="form-field centralizado">
        <label for="nome">Nome: </label>
        <input type="text" style="text-align: center; border: none; background-color: #f4f4f4; padding: 5px;"
          class="nome" name="nome" value="<?php echo htmlspecialchars($nomeFuncionario); ?>" readonly>
      </div>
      <div class="form-field centralizado">
        <label for="numero">Número:</label>
        <input style="text-align: center; width: 130px; border: none; background-color: #f4f4f4; padding: 5px; "
          type="text" id="numero"
          value="<?php echo htmlspecialchars($nextIdForm ?? $formularioReposicao['idform_reposicao'] ?? ''); ?>"
          readonly>
      </div>
      <div class="form-field centralizado">
        <label for="reposicoes-mes">Reposições mês:</label>
        <input style="text-align: center; width: 230px; border: none; background-color: #f4f4f4; padding: 5px;"
          type="month" id="reposicoes-mes" value="<?php echo htmlspecialchars($mesAno); ?>" readonly>
      </div>
    </div>

    <div class="form-group-inline">
      <!-- Turno -->
      <div class="form-field">
        <label for="turno">Turno:</label>
        <div class="form-checks turno">
          <label>
            <input type="radio" name="turno" value="manha" onclick="mostrarTabela('manha')"
              <?php echo (isset($formularioReposicao['turno']) && $formularioReposicao['turno'] === 'manha') ? 'checked' : ''; ?>>
            Manhã
          </label>
          <label>
            <input type="radio" name="turno" value="tarde" onclick="mostrarTabela('tarde')"
              <?php echo (isset($formularioReposicao['turno']) && $formularioReposicao['turno'] === 'tarde') ? 'checked' : ''; ?>>
            Tarde
          </label>
          <label>
            <input type="radio" name="turno" value="noite" onclick="mostrarTabela('noite')"
              <?php echo (isset($formularioReposicao['turno']) && $formularioReposicao['turno'] === 'noite') ? 'checked' : ''; ?>>
            Noite
          </label>
        </div>
      </div>

      <!-- Motivo da Reposição -->
      <div class="form-field">
        <label for="motivo_falta">Reposição em virtude de:</label>
        <input type="text" readonly value="<?php echo htmlspecialchars($motivoFalta ?? ''); ?>"
          style="text-align: center; border: none; background-color: #f4f4f4; padding: 5px;">
      </div>

      <!-- Cursos Envolvidos -->
      <div class="form-field">
        <label>Cursos Envolvidos:</label>
        <div class="cursos-envolvidos">
          <?php
          if ($idform_reposicao) {
            // Modo Edição: Exibir cursos da reposição
            foreach ($cursosReposicao as $curso):
          ?>
          <input type="text" readonly value="<?php echo htmlspecialchars($curso); ?>"
            style="border: none; background-color: #f4f4f4; margin-right: 5px; padding: 5px;">
          <?php
            endforeach;
          } elseif (isset($formularios)) {
            // Modo Criação: Exibir cursos das faltas
            foreach ($formularios as $formulario):
            ?>
          <input type="text" readonly value="<?php echo htmlspecialchars($formulario['nome_curso']); ?>"
            style="border: none; background-color: #f4f4f4; margin-right: 5px; padding: 5px;">
          <?php
            endforeach;
          }
          ?>
        </div>
      </div>
    </div>

    <!-- Contêiner flexível para as duas seções -->
    <div class="tables-side-by-side">
      <!-- Aulas Não Ministradas -->
      <div class="form-group">
        <legend>Aulas não Ministradas:</legend>
        <table class="styled-table">
          <thead>
            <tr>
              <th>Data(as):</th>
              <th>Nº de aulas</th>
              <th>Nome da(s) Disciplina(s)</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($aulasFaltas)): ?>
            <?php foreach ($aulasFaltas as $aula): ?>
            <tr>
              <td><?php echo date('d/m/Y', strtotime(htmlspecialchars($aula['data_aula']))); ?></td>
              <td><?php echo htmlspecialchars($aula['num_aulas']); ?></td>
              <td><?php echo htmlspecialchars($aula['nome_disciplina']); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr>
              <td colspan="3">Nenhuma aula registrada.</td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Dados das Aulas de Reposição -->
      <div class="form-group">
        <legend>Dados da(s) aulas de reposição:</legend>
        <table class="styled-table">
          <thead>
            <tr>
              <th>Ordem</th>
              <th>Data da Reposição</th>
              <th>Horário de Início e Término</th>
              <th>Disciplina(s)</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // Determinar o número de reposições
            if ($idform_reposicao && !empty($aulasReposicao)) {
              $totalReposicoes = count($aulasReposicao);
            } elseif (!$idform_reposicao && !empty($aulasFaltas)) {
              $totalReposicoes = count($aulasFaltas);
            } else {
              $totalReposicoes = 1; // Pelo menos uma linha
            }
            ?>
            <?php for ($i = 0; $i < $totalReposicoes; $i++): ?>
            <tr>
              <td><?php echo ($i + 1); ?></td>
              <td>
                <input type="date" name="dataReposicao[]" id="dataReposicao-<?php echo $i; ?>" required
                  onchange="verificarData(<?php echo $i; ?>)" min="<?php echo date('Y-m-d'); ?>" value="<?php
                                                                                                          if ($idform_reposicao && isset($aulasReposicao[$i]['data_reposicao'])) {
                                                                                                            echo htmlspecialchars($aulasReposicao[$i]['data_reposicao']);
                                                                                                          } elseif (!$idform_reposicao && isset($aulasFaltas[$i]['data_aula'])) {
                                                                                                            echo htmlspecialchars(date('Y-m-d', strtotime($aulasFaltas[$i]['data_aula'])));
                                                                                                          }
                                                                                                          ?>">
              </td>
              <td>
                <input type="time" name="inicioHorario[]" id="inicioHorario-<?php echo $i; ?>" min="07:30" max="20:40"
                  required onchange="calcularHorarioTermino(<?php echo $i; ?>, <?php
                                                                                  if ($idform_reposicao && isset($aulasReposicao[$i]['num_aulas'])) {
                                                                                    echo htmlspecialchars($aulasReposicao[$i]['num_aulas']);
                                                                                  } elseif (!$idform_reposicao && isset($aulasFaltas[$i]['num_aulas'])) {
                                                                                    echo htmlspecialchars($aulasFaltas[$i]['num_aulas']);
                                                                                  } else {
                                                                                    echo '1'; // Default
                                                                                  }
                                                                                  ?>)" value="<?php
                                                                                              if ($idform_reposicao && isset($aulasReposicao[$i]['horarioinicio'])) {
                                                                                                echo htmlspecialchars($aulasReposicao[$i]['horarioinicio']);
                                                                                              }
                                                                                              ?>">
                às
                <input type="time" name="teroHorario[]" id="teroHorario-<?php echo $i; ?>" readonly value="<?php
                                                                                                              if ($idform_reposicao && isset($aulasReposicao[$i]['horariofim'])) {
                                                                                                                echo htmlspecialchars($aulasReposicao[$i]['horariofim']);
                                                                                                              }
                                                                                                              ?>">
              </td>
              <td>
                <input type="text" readonly value="<?php
                                                      if ($idform_reposicao && isset($aulasReposicao[$i]['nome_disciplina'])) {
                                                        echo htmlspecialchars($aulasReposicao[$i]['nome_disciplina']);
                                                      } elseif (!$idform_reposicao && isset($aulasFaltas[$i]['nome_disciplina'])) {
                                                        echo htmlspecialchars($aulasFaltas[$i]['nome_disciplina']);
                                                      }
                                                      ?>"
                  style="border: none; background-color: #f4f4f4; padding: 5px;">
                <input type="hidden" name="nome_disciplina[]" value="<?php
                                                                        if ($idform_reposicao && isset($aulasReposicao[$i]['nome_disciplina'])) {
                                                                          echo htmlspecialchars($aulasReposicao[$i]['nome_disciplina']);
                                                                        } elseif (!$idform_reposicao && isset($aulasFaltas[$i]['nome_disciplina'])) {
                                                                          echo htmlspecialchars($aulasFaltas[$i]['nome_disciplina']);
                                                                        }
                                                                        ?>">
              </td>
            </tr>
            <?php endfor; ?>
          </tbody>
        </table>
      </div>

      <script>
      // Função para restringir a data de reposição a dias futuros, sem domingos
      function verificarData(index) {
        const dataSelecionada = document.getElementById(`dataReposicao-${index}`).value;

        // Verifica se o campo foi preenchido
        if (!dataSelecionada) {
          alert("Por favor, selecione uma data.");
          return;
        }

        // Cria o objeto Date com a data selecionada
        const dataSelecionadaObj = new Date(
          `${dataSelecionada}T00:00:00`); // Garantir horário fixo para evitar ambiguidades

        // Verifica se a data é domingo (0 representa domingo)
        if (dataSelecionadaObj.getDay() === 0) {
          alert("Domingo não é permitido. Por favor, selecione outro dia.");
          document.getElementById(`dataReposicao-${index}`).value = ""; // Limpa a data inválida
          return;
        }

        // Define a data mínima para os campos subsequentes com intervalo de uma semana
        const totalReposicoes = document.querySelectorAll('input[name="dataReposicao[]"]').length;
        for (let i = index + 1; i < totalReposicoes; i++) {
          const proximaData = new Date(dataSelecionadaObj);
          proximaData.setDate(proximaData.getDate() + 7); // Adiciona 7 dias
          document.getElementById(`dataReposicao-${i}`).min = proximaData.toISOString().split("T")[0];
        }
      }


      // Função para calcular automaticamente o horário de término
      function calcularHorarioTermino(index, numAulas) {
        const inicioHorario = document.getElementById(`inicioHorario-${index}`).value;

        if (!inicioHorario) {
          alert("Por favor, selecione o horário de início.");
          return;
        }

        const [horas, minutos] = inicioHorario.split(":").map(Number);
        const totalMinutos = horas * 60 + minutos + (numAulas * 50);

        const horasTermino = Math.floor(totalMinutos / 60);
        const minutosTermino = totalMinutos % 60;

        const horarioTermino =
          `${horasTermino.toString().padStart(2, '0')}:${minutosTermino.toString().padStart(2, '0')}`;
        document.getElementById(`teroHorario-${index}`).value = horarioTermino;

        // Verifica se o horário de início e término estão dentro dos limites
        if (inicioHorario > "20:40" || horarioTermino > "22:40") {
          alert(
            "O horário de início não pode ultrapassar 20:40, e o término não pode exceder 22:40."
          );
          document.getElementById(`inicioHorario-${index}`).value = "";
          document.getElementById(`teroHorario-${index}`).value = "";
        }
      }
      </script>
    </div>
    <!-- Tabela de Horários com Informações de Aulas e Atividades de HAE -->
    <div id="agenda-completa" class="tabela">
      <fieldset>
        <legend>Agenda Completa do Professor</legend>
        <!-- Legenda das cores -->
        <div class="legenda-cores"
          style="text-align: center; display: flex; justify-content: center; gap: 15px; align-items: center; margin-bottom: 10px;">
          <span
            style="background-color: #05fda2; display: inline-block; width: 20px; height: 20px; margin-right: 5px;"></span>
          Aulas
          <span
            style="background-color: #02c0ff; display: inline-block; width: 20px; height: 20px; margin-left: 15px; margin-right: 5px;"></span>
          HAE (Horas de Atividade Extra)
          <span
            style="background-color: #ffcccb; display: inline-block; width: 20px; height: 20px; margin-left: 15px; margin-right: 5px;"></span>
          Reposição de Aulas
        </div>

        <table id="tabela-aulas" class="styled-table">
          <tr>
            <th>Horário</th>
            <th>Segunda</th>
            <th>Terça</th>
            <th>Quarta</th>
            <th>Quinta</th>
            <th>Sexta</th>
            <th>Sábado</th>
          </tr>

          <?php
          // Obter todos os horários únicos de aulas e HAE com formatação de hora
          $horariosUnicos = [];
          foreach (array_merge($aulas, $atividadesHAE, $aulasReposicao) as $evento) {
            $horarioInicioFormatado = date('H:i', strtotime($evento['horario_inicio'] ?? $evento['horarioinicio']));
            $horarioFimFormatado = date('H:i', strtotime($evento['horario_fim'] ?? $evento['horariofim']));
            $horariosUnicos[] = $horarioInicioFormatado . ' - ' . $horarioFimFormatado;
          }
          $horariosUnicos = array_unique($horariosUnicos);
          sort($horariosUnicos); // Ordena os horários para exibição

          // Exibição da tabela com os dados de horários
          foreach ($horariosUnicos as $horario):
          ?>
          <tr>
            <td><?php echo htmlspecialchars($horario); ?></td>
            <?php
              // Colunas de segunda a sábado
              $diasSemana = ['SEGUNDA', 'TERÇA', 'QUARTA', 'QUINTA', 'SEXTA', 'SÁBADO'];
              foreach ($diasSemana as $dia):
                $eventoEncontrado = '-';
                $classeEvento = '';

                // Verifica se há uma aula para o dia e horário específico
                foreach ($aulas as $aula) {
                  $aulaHorarioInicio = date('H:i', strtotime($aula['horario_inicio']));
                  $aulaHorarioFim = date('H:i', strtotime($aula['horario_fim']));
                  if (
                    strtoupper($aula['dia_semana']) === strtoupper($dia) &&
                    ($aulaHorarioInicio . ' - ' . $aulaHorarioFim) === $horario
                  ) {
                    $eventoEncontrado = $aula['disciplina'];
                    $classeEvento = 'aula'; // Classe para aula
                    break;
                  }
                }

                // Verifica se há uma atividade de HAE para o dia e horário específico
                if ($eventoEncontrado === '-') {
                  foreach ($atividadesHAE as $hae) {
                    $haeHorarioInicio = date('H:i', strtotime($hae['horario_inicio']));
                    $haeHorarioFim = date('H:i', strtotime($hae['horario_fim']));
                    if (
                      strtoupper($hae['dia_semana']) === strtoupper($dia) &&
                      ($haeHorarioInicio . ' - ' . $haeHorarioFim) === $horario
                    ) {
                      $eventoEncontrado = $hae['tipo_atividade'];
                      $classeEvento = 'hae'; // Classe para HAE
                      break;
                    }
                  }
                }

                // Verifica se há uma reposição de aula inserida dinamicamente
                if ($eventoEncontrado === '-') {
                  foreach ($aulasReposicao as $aulaReposicao) {
                    // Calcula o dia da semana a partir da data da reposição
                    $diaSemana = date('l', strtotime($aulaReposicao['data_reposicao']));
                    $diaSemanaPT = $diasSemanaMap[$diaSemana] ?? '-';
                    $horarioReposicao = date('H:i', strtotime($aulaReposicao['horarioinicio'])) . ' - ' . date('H:i', strtotime($aulaReposicao['horariofim']));

                    if (
                      strtoupper($diaSemanaPT) === strtoupper($dia) &&
                      ($horarioReposicao) === $horario
                    ) {
                      $eventoEncontrado = 'Reposição de Aula - ' . htmlspecialchars($aulaReposicao['nome_disciplina']);
                      $classeEvento = 'reposicao'; // Classe para reposição
                      break;
                    }
                  }
                }
              ?>
            <td class="<?php echo htmlspecialchars($classeEvento); ?>">
              <?php echo htmlspecialchars($eventoEncontrado); ?></td>
            <?php endforeach; ?>
          </tr>
          <?php endforeach; ?>
        </table>
        <p style="text-align: center; margin-top: 10px;">Observe as exigências legais: máximo 8 horas diárias de
          trabalho, intervalo de 1 hora entre um expediente e outro e 6 horas em cada expediente.</p>
      </fieldset>
    </div>

    <!-- Botão de Enviar -->
    <div class="form-footer">
      <button class="btn-enviar"
        type="submit"><?php echo ($idform_reposicao) ? 'Atualizar Formulário' : 'Enviar Formulário'; ?></button>
    </div>

  </form>

  <!-- Scripts JavaScript Consolidados -->
  <script>
  // Função para mostrar a tabela correspondente ao turno selecionado
  function mostrarTabela(turno) {
    // Implementar lógica para mostrar/ocultar tabelas conforme o turno, se necessário
    // Exemplo:
    // document.getElementById('tabela-manha').style.display = (turno === 'manha') ? 'block' : 'none';
    // document.getElementById('tabela-tarde').style.display = (turno === 'tarde') ? 'block' : 'none';
    // document.getElementById('tabela-noite').style.display = (turno === 'noite') ? 'block' : 'none';
  }

  // Atualiza a tabela da Agenda Completa ao alterar horários
  function atualizarAgendaCompletada() {
    const datasReposicao = document.getElementsByName('dataReposicao[]');
    const inicioHorarios = document.getElementsByName('inicioHorario[]');
    const teroHorarios = document.getElementsByName('teroHorario[]');
    const disciplinas = document.getElementsByName('nome_disciplina[]');

    const tabela = document.getElementById('tabela-aulas');
    const linhas = tabela.querySelectorAll('tr');

    // Primeiro, limpa todas as entradas de reposição existentes na tabela
    linhas.forEach((linha) => {
      const celulas = linha.querySelectorAll('td');
      celulas.forEach((celula) => {
        if (celula.classList.contains('reposicao')) {
          celula.textContent = '-';
          celula.classList.remove('reposicao');
        }
      });
    });

    // Adiciona os novos horários de reposição selecionados
    for (let i = 0; i < datasReposicao.length; i++) {
      if (datasReposicao[i].value && inicioHorarios[i].value && teroHorarios[i].value && disciplinas[i].value) {
        const dataReposicao = new Date(`${datasReposicao[i].value}T00:00:00`); // Garantir precisão da data
        const horarioInicio = inicioHorarios[i].value;
        const horarioFim = teroHorarios[i].value;
        const disciplina = disciplinas[i].value;

        // Formata o horário de início e término
        const novoHorario = `${horarioInicio} - ${horarioFim}`;

        // Converte a data para o dia da semana
        const diaSemana = dataReposicao.toLocaleDateString('pt-BR', {
          weekday: 'long',
        }).toUpperCase();

        const diasSemanaMap = {
          'DOMINGO': 0,
          'SEGUNDA-FEIRA': 1,
          'TERÇA-FEIRA': 2,
          'QUARTA-FEIRA': 3,
          'QUINTA-FEIRA': 4,
          'SEXTA-FEIRA': 5,
          'SÁBADO': 6,
        };

        const colunaIndex = diasSemanaMap[diaSemana] || null;

        // Se o dia da semana é válido (não é domingo)
        if (colunaIndex !== null && colunaIndex > 0) {
          let linhaExistente = null;

          // Verifica se já existe uma linha com o mesmo horário
          for (let j = 1; j < linhas.length; j++) {
            const celulaHorario = linhas[j].cells[0].textContent.trim();
            if (celulaHorario === novoHorario) {
              linhaExistente = linhas[j];
              break;
            }
          }

          if (linhaExistente) {
            // Atualiza a célula correspondente ao dia da semana
            linhaExistente.cells[colunaIndex].textContent = `Reposição de Aula - ${disciplina}`;
            linhaExistente.cells[colunaIndex].classList.add('reposicao');
          } else {
            // Adiciona uma nova linha com o horário e reposição
            const novaLinha = tabela.insertRow();
            const celulaHorario = novaLinha.insertCell(0);
            celulaHorario.textContent = novoHorario;

            // Adiciona células para cada dia da semana
            for (let k = 1; k <= 6; k++) {
              const celulaDia = novaLinha.insertCell(k);
              if (k === colunaIndex) {
                celulaDia.textContent = `Reposição de Aula - ${disciplina}`;
                celulaDia.classList.add('reposicao');
              } else {
                celulaDia.textContent = '-';
              }
            }

            // Insere a nova linha na posição correta
            let inserido = false;
            for (let j = 1; j < linhas.length; j++) {
              const horarioAtual = linhas[j].cells[0].textContent.trim();
              if (novoHorario < horarioAtual) {
                tabela.insertBefore(novaLinha, linhas[j]);
                inserido = true;
                break;
              }
            }

            if (!inserido) {
              tabela.appendChild(novaLinha);
            }
          }
        }
      }
    }
  }



  // Adiciona eventos de mudança nos campos do formulário
  document.querySelectorAll('input[name="dataReposicao[]"], input[name="inicioHorario[]"], input[name="teroHorario[]"]')
    .forEach(input => {
      input.addEventListener('change', atualizarAgendaCompletada);
    });


  // Adiciona eventos de mudança nos campos do formulário para atualizar a agenda automaticamente
  document.querySelectorAll('input[name="dataReposicao[]"], input[name="inicioHorario[]"], input[name="teroHorario[]"]')
    .forEach(input => {
      input.addEventListener('change', atualizarAgendaCompletada);
    });
  </script>

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