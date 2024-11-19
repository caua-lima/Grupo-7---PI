<?php
include 'conexao.php';
include 'header.html';

// Definir o ID do funcionário (fixo como 1 por enquanto)
$idFuncionario = 1;
$errorMessage = ''; // Variável para armazenar a mensagem de erro

try {
  // Consulta para buscar as informações do funcionário com base no ID
  $stmtFuncionario = $conn->prepare("SELECT nome, matricula, funcao, regime_juridico FROM funcionarios WHERE idfuncionario = ?");
  $stmtFuncionario->execute([$idFuncionario]);
  $funcionario = $stmtFuncionario->fetch(PDO::FETCH_ASSOC);

  if (!$funcionario) {
    throw new Exception("Funcionário não encontrado.");
  }

  // Consulta para buscar as disciplinas que o funcionário ministra, incluindo `idcursos` e `dia_semana`
  $stmtDisciplinas = $conn->prepare("SELECT DISTINCT disciplina, idcursos, dia_semana FROM aulas_semanal_professor WHERE idfuncionario = ?");
  $stmtDisciplinas->execute([$idFuncionario]);
  $disciplinas = $stmtDisciplinas->fetchAll(PDO::FETCH_ASSOC);

  // Consulta para buscar os tipos de atividade HAE do professor
  $stmtHAE = $conn->prepare("SELECT tipo_atividade FROM horas_hae_professor WHERE idfuncionario = ?");
  $stmtHAE->execute([$idFuncionario]);
  $haeAtividades = $stmtHAE->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $errorMessage = "Erro ao buscar informações do funcionário: " . $e->getMessage();
} catch (Exception $e) {
  $errorMessage = $e->getMessage();
}

$disciplinasJson = json_encode($disciplinas);
$haeAtividadesJson = json_encode($haeAtividades);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tipo_falta = $_POST['tipo_falta'] ?? null;
  $data_unica = $_POST['data_unica'] ?? null;
  $data_inicio_periodo = $_POST['data_inicio_periodo'] ?? null;
  $data_fim_periodo = $_POST['data_fim_periodo'] ?? null;
  $motivo_falta = $_POST['motivo_falta'] ?? null;
  $situacao = $_POST['situacao'] ?? 'Aguardando Reposição';
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
      // Processar o arquivo PDF
      $arquivo = $_FILES['arquivo_pdf'];
      if ($arquivo['error'] === UPLOAD_ERR_OK) {
        $nomeArquivo = uniqid() . "-" . basename($arquivo['name']);
        $destinoArquivo = "uploads/$nomeArquivo";
        if (!move_uploaded_file($arquivo['tmp_name'], $destinoArquivo)) {
          throw new Exception("Erro ao fazer o upload do arquivo.");
        }

        // Insere os dados na tabela formulario_faltas
        $stmt = $conn->prepare("INSERT INTO formulario_faltas (idfuncionario, datainicio, datafim, pdf_atestado, motivo_falta, situacao) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$idFuncionario, $data_unica ?? $data_inicio_periodo, $data_unica ?? $data_fim_periodo, $nomeArquivo, $motivo_falta, $situacao]);

        // Obtém o ID do formulário gerado
        $id_formulario = $conn->lastInsertId();

        // Inserir dados nas tabelas relacionadas para cada data selecionada
        $stmtCurso = $conn->prepare("INSERT INTO formulario_faltas_cursos (idform_faltas, idcursos) VALUES (?, ?)");
        $stmtAulaFalta = $conn->prepare("INSERT INTO aulas_falta (idform_faltas, num_aulas, data_aula, nome_disciplina) VALUES (?, ?, ?, ?)");

        foreach ($cursosSelecionados as $data => $cursoData) {
          foreach ($cursoData['curso'] as $cursoId) {
            $cursoId = (int) $cursoId; // Confirma que é um número inteiro
            $stmtCurso->execute([$id_formulario, $cursoId]);

            // Insere disciplinas associadas a cada curso
            if (isset($cursoData["disciplinas_$cursoId"])) {
              $numCursosSelecionados = count($cursoData['curso']);
              $num_aulas = (4 / $numCursosSelecionados); // Divide 4 aulas pelo número de cursos selecionados

              foreach ($cursoData["disciplinas_$cursoId"] as $disciplina) {
                $stmtAulaFalta->execute([$id_formulario, $num_aulas, $data, $disciplina]);
              }
            }
          }
        }

        // Redireciona para a página inicial após o processo
        header("Location: home.php");
        exit;
      } else {
        throw new Exception("Erro no upload do arquivo: código de erro " . $arquivo['error']);
      }
    } catch (PDOException $e) {
      $errorMessage = "Erro ao inserir dados no banco de dados: " . $e->getMessage();
    } catch (Exception $e) {
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
            <a href="#">
                <img class="logo-ita" src="img/logo-fatec_itapira.png" alt="">
            </a>
            <a href="#">
                <img class="logo-cps" src="img/logo-cps.png" alt="">
            </a>
        </div>
        <div class="second-column-sc">
            <h2 class="title">Programa para Ausências Docentes</h2>
            <h2 class="title">e Reposição de Aulas Oficiais</h2>
        </div>
        <div class="third-column-sc">
            <img class="logo-padrao" src="img/logo-padrao.png" alt="">
            <a class="btn" href="home.php"><btn>VOLTAR</btn></a>
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
        <input type="text" class="nome" name="nome" value="<?php echo htmlspecialchars($funcionario['nome']); ?>"
          readonly required>
      </div>
      <div class="input-row">
        <label for="matricula">Matrícula:</label>
        <input type="text" class="matricula" name="matricula"
          value="<?php echo htmlspecialchars($funcionario['matricula']); ?>" readonly required>
        <label for="funcao">Função:</label>
        <input type="text" class="funcao" name="funcao" value="<?php echo htmlspecialchars($funcionario['funcao']); ?>"
          readonly required>
        <label for="regime">Regime Jurídico:</label>
        <input type="text" class="regime" name="regime"
          value="<?php echo htmlspecialchars($funcionario['regime_juridico']); ?>" readonly required>
      </div>
    </fieldset>
    <fieldset class="faltas">
      <legend>Falta Referente</legend>

      <div class="radio-group">
        <label>
          <input type="radio" name="tipo_falta" value="unica" id="radio_unica" checked onclick="togglePeriodo(false)">
          Falta referente ao dia:
        </label>
        <input type="date" class="data-falta" name="data_unica" id="data_unica" onchange="gerarSelecaoCursos()">
      </div>

      <div class="radio-group">
        <label>
          <input type="radio" name="tipo_falta" value="periodo" id="radio_periodo" onclick="togglePeriodo(true)">
          Período de
        </label>
        <input type="number" class="num-dias" name="num_dias" id="num_dias" min="1" max="15" placeholder="Nº de dias">
        <label for="data-inicio-periodo">Dias: </label>
        <input type="date" class="data-inicio-periodo" name="data_inicio_periodo" id="data_inicio_periodo"
          onchange="gerarSelecaoCursosPeriodo()">
        <label for="data-fim-periodo" class="ate">Até: </label>
        <input type="date" class="data-fim-periodo" name="data_fim_periodo" id="data_fim_periodo" readonly>
      </div>

    </fieldset>

    <script>
    // Desativa o outro rádio quando um é selecionado
    function togglePeriodo(isPeriodo) {
      const radioUnica = document.getElementById('radio_unica');
      const radioPeriodo = document.getElementById('radio_periodo');

      if (isPeriodo) {
        radioUnica.disabled = true;
        radioPeriodo.disabled = false;
      } else {
        radioUnica.disabled = false;
        radioPeriodo.disabled = true;
      }
    }

    // Configuração inicial para garantir a funcionalidade ao carregar
    document.addEventListener("DOMContentLoaded", function() {
      togglePeriodo(document.getElementById('radio_periodo').checked);
    });
    </script>

    <style>
    /* Estilização das divs que contêm os grupos de rádio */
    .radio-group {
      margin-bottom: 10px;
    }
    </style>


    <!-- Container dinâmico para seleção de cursos, disciplinas e número de aulas -->
    <div id="selecoes-container"></div>

    <fieldset class="motivo-falta">
      <legend>Motivo da Falta</legend>

      <label for="motivo">Selecione o motivo da falta:</label>
      <select id="motivo" name="motivo">
        <option value="" disabled selected>Selecione o motivo</option>
        <option value="licenca-falta-medica">Licença e Falta Médica</option>
        <option value="falta-injustificada">Falta Injustificada (Com desconto do DSR)</option>
        <option value="faltas-justificadas">Faltas Justificadas</option>
        <option value="faltas-previstas-legislacao">Faltas Previstas na Legislação Trabalhista</option>
      </select>


      <div id="opcoes_licenca-falta-medica" style="display: none;" class="motivo licenca-falta-medica">
        <label><input type="radio" class="LFM" name="motivo_falta" value="Falta Medica"> Falta Médica (Atestado médico
          de 1 dia)</label>
        <label><input type="radio" class="LFM" name="motivo_falta" value="Comparecimentoao Medico"> Comparecimento ao
          Médico no período das <input type="time" class="small-input"> às <input type="time"
            class="small-input"></label>
        <label><input type="radio" class="LFM" name="motivo_falta" value="Licenca Saude"> Licença-Saúde (Atestado médico
          igual ou superior a 2 dias)</label>
        <label><input type="radio" class="LFM" name="motivo_falta" value="Licenca Maternidade"> Licença-Maternidade
          (Atestado médico até 15 dias)</label>
      </div>

      <div id="opcoes_falta-injustificada" style="display: none;" class="motivo falta-injustificada">
        <label><input type="radio" class="FI" name="motivo_falta" value="falta-injustificada"> Falta</label>
        <label><input type="radio" class="LFM" name="motivo_falta" value="comparecimento-medico"> Atraso ou Saída
          Antecipada, das <input type="time" class="small-input"> às <input type="time" class="small-input"></label>
      </div>

      <div id="opcoes_faltas-justificadas" style="display: none;" class="motivo faltas-justificadas">
        <label><input type="radio" class="FJ" name="motivo_falta" value="Falta justificada"> Falta por motivo
          de:</label>
        <textarea name="motivo-descricao" rows="1"></textarea>
        <label><input type="radio" class="LFM" name="motivo_falta" value="Comparecimento ao Medico"> Atraso ou Saída
          Antecipada das <input type="time" class="small-input"> às <input type="time" class="small-input"> Por motivo
          de:</label>
        <textarea name="atraso-descricao" rows="1"></textarea>
      </div>

      <div id="opcoes-faltas-previstas-legislacao" style="display: none;" class="motivo faltas-previstas-legislacao">
        <label><input type="radio" class="FPLT" name="motivo_falta" value="Falecimento do Conjuge"> Falecimento de
          cônjuge,
          pai, mãe, filho (9 dias consecutivos)</label>
        <label><input type="radio" class="FPLT" name="motivo_falta" value="Falecimento Familiares"> Falecimento de
          outros
          familiares (2 dias consecutivos)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Casamento"> Casamento (9 dias
          consecutivos)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Nascimento do Filho"> Nascimento de filho (5
          dias)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Acompanhamento da(o) Esposa(o)"> Acompanhar
          esposa ou
          companheira (Até 2 dias)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Acompanhamento do Filho"> Acompanhar filho
          até 6
          anos (1 dia por ano)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Doacao de Sangue"> Doação voluntária de
          sangue
          (1 dia em cada 12 meses)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Alistamento Eleitor"> Alistamento como
          eleitor (Até 2 dias)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Depoimento Judicial"> Convocação para
          depoimento judicial</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Comparecimento Juri"> Comparecimento como
          jurado no Tribunal do Júri</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Servico Eleitoral"> Convocação para serviço
          eleitoral</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Composicao Mesa Eleitoral"> Dispensa para
          compor mesas eleitorais</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Prova de Vestibular"> Realização de Prova de
          Vestibular</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Justica do trabalho"> Comparecimento como
          parte
          na Justiça do Trabalho</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="Acidente de Transporte"> Atrasos devido a
          acidentes de transporte</label>
      </div>
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
      </script>
    </fieldset>


    <div class="form-footer">
      <label for=""></label>
      <input type="file" class="form-control" name="arquivo_pdf" accept=".pdf" id="fileInput" required>
      <ul id="fileList"></ul>
    </div>
    <input type="hidden" name="situacao" value="Aguardando Reposição">

    <div class="form-footer">
      <button class="btn-enviar" type="submit">Enviar Formulário</button>
    </div>
  </form>
</div>

  <!-- Script para a area de datas e materias e cursos -->
  <script>
  // Dados de disciplinas e atividades HAE obtidos do PHP
  const disciplinas = <?php echo $disciplinasJson; ?>;
  const haeAtividades = <?php echo $haeAtividadesJson; ?>;

  // Mapeamento dos dias da semana para português em maiúsculas
  const diasSemana = ["DOMINGO", "SEGUNDA", "TERÇA", "QUARTA", "QUINTA", "SEXTA", "SÁBADO"];

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

  // Limitar datas para o passado
  document.addEventListener("DOMContentLoaded", function() {
    const hoje = new Date().toISOString().split('T')[0];
    document.getElementById("data_unica").setAttribute("max", hoje);
    document.getElementById("data_inicio_periodo").setAttribute("max", hoje);
  });

  // Geração da data final para faltas em período
  document.getElementById('data_inicio_periodo').addEventListener('change', function() {
    const numDias = parseInt(document.getElementById('num_dias').value, 10);
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

  // Função para obter o dia da semana em português e maiúsculo a partir de uma data
  function getDiaSemana(data) {
    const [year, month, day] = data.split('-');
    const date = new Date(year, month - 1, day); // Mês começa em 0 no JavaScript
    return diasSemana[date.getUTCDay()]; // Usar getUTCDay para evitar problemas de fuso horário
  }

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
        const dataLabel = document.createElement('p');
        dataLabel.textContent = `Data: ${currentDate.toISOString().split('T')[0]}`;
        container.appendChild(dataLabel);

        gerarSelecaoCursosDia(container, currentDate.toISOString().split('T')[0]);
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
      checkbox.addEventListener('change', limitarSelecaoCursos);

      const label = document.createElement('label');
      label.textContent = curso.nome;
      label.appendChild(checkbox);
      container.appendChild(label);

      checkbox.addEventListener('change', function() {
        if (this.checked) {
          cursosSelecionados.push(curso.id);
          if (curso.id == 5) {
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

      const labelAtividade = document.createElement('label');
      labelAtividade.textContent = atividade.tipo_atividade;
      labelAtividade.appendChild(option);
      atividadeList.appendChild(labelAtividade);
    });

    container.appendChild(atividadeList);
  }

  // Geração dinâmica de disciplinas baseado nas regras de seleção por curso, data e dia da semana
  function gerarSelecaoDisciplinasDia(container, data, cursoId, numCursos) {
    const disciplinaList = document.createElement('div');
    disciplinaList.id = `disciplinas_${data}_${cursoId}`;

    const disciplinaLabel = document.createElement('p');
    disciplinaLabel.textContent = `Disciplinas para o curso ${cursoId} (Data: ${data}):`;
    disciplinaList.appendChild(disciplinaLabel);

    const diaSemanaSelecionado = getDiaSemana(data); // Obter o dia da semana selecionado

    disciplinas
      .filter(disciplina => disciplina.idcursos == cursoId && disciplina.dia_semana === diaSemanaSelecionado)
      .forEach(disciplina => {
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = `cursos_por_data[${data}][disciplinas_${cursoId}][]`;
        checkbox.value = disciplina.disciplina;

        const labelDisciplina = document.createElement('label');
        labelDisciplina.textContent = disciplina.disciplina;
        labelDisciplina.appendChild(checkbox);
        disciplinaList.appendChild(labelDisciplina);
      });


    container.appendChild(disciplinaList);

  }

  function gerarSelecaoNumAulas(container, data, cursoId) {
    const numAulasDiv = document.createElement('div');
    numAulasDiv.id = `num_aulas_${data}_${cursoId}`;

    const numAulasLabel = document.createElement('label');
    numAulasLabel.textContent = `Número de Aulas para o curso ${cursoId} (Data: ${data}): `;
    numAulasDiv.appendChild(numAulasLabel);

    const numAulasInput = document.createElement('input');
    numAulasInput.type = 'number';
    numAulasInput.name = `num_aulas_${data}_${cursoId}`;
    numAulasInput.min = 1;
    numAulasInput.max = (cursoId === 5) ? 2 : 4; // Limite de 2 para HAE, 4 para os outros
    numAulasDiv.appendChild(numAulasInput);

    container.appendChild(numAulasDiv);
  }

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

  <br><br>
  <footer>
    <div class="containerf">
      <a href="">
        <img src="img/logo-governo-do-estado-sp.png">
      </a>
    </div>
  </footer>
</body>

</html>
