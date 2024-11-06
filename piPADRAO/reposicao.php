<?php
include 'conexao.php';
include 'index.html';

// ID do professor (fixo por enquanto)
$idFuncionario = 1; // Este valor será substituído pela variável de sessão no futuro

// Obter o próximo ID de formulário de reposição
$nextIdForm = null;
try {
  $stmt = $conn->query("SHOW TABLE STATUS LIKE 'formulario_reposicao'");
  $tableStatus = $stmt->fetch(PDO::FETCH_ASSOC);
  $nextIdForm = $tableStatus['Auto_increment'];
} catch (PDOException $e) {
  echo "Erro ao obter o próximo ID: " . $e->getMessage();
}

// Buscar nome do funcionário pelo ID
$nomeFuncionario = '';
try {
  $stmtNome = $conn->prepare("SELECT nome FROM funcionarios WHERE idfuncionario = ?");
  $stmtNome->execute([$idFuncionario]);
  $resultadoNome = $stmtNome->fetch(PDO::FETCH_ASSOC);
  if ($resultadoNome) {
    $nomeFuncionario = $resultadoNome['nome'];
  }
} catch (PDOException $e) {
  echo "Erro ao buscar nome do funcionário: " . $e->getMessage();
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
  echo "Erro ao buscar aulas: " . $e->getMessage();
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
  echo "Erro ao buscar atividades de HAE: " . $e->getMessage();
}

$idform = $_GET['idform'] ?? null;

if ($idform) {
  try {
    $stmt = $conn->prepare("
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
            WHERE f.idform_faltas = ? AND f.idfuncionario = ?  -- Verifica o idfuncionario
        ");
    $stmt->execute([$idform, $idFuncionario]);
    $formularios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verifica se a data de início está disponível e formata o mês e ano para o input de mês
    $mesAno = '';
    if ($formularios && !empty($formularios[0]['datainicio'])) {
      $mesAno = date('Y-m', strtotime($formularios[0]['datainicio']));
    }
  } catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
  }
}

if (!$formularios) {
  echo "Formulário não encontrado ou não autorizado.";
  exit;
}

try {
  // Consulta para buscar as informações das aulas de falta associadas ao `idform_faltas`
  $stmtAulasFaltas = $conn->prepare("
        SELECT 
            af.data_aula, 
            af.num_aulas, 
            af.nome_disciplina
        FROM aulas_falta af
        WHERE af.idform_faltas = ?
    ");
  $stmtAulasFaltas->execute([$idform]);
  $aulasFaltas = $stmtAulasFaltas->fetchAll(PDO::FETCH_ASSOC);

  if (!$aulasFaltas) {
    echo "Nenhuma aula registrada para este formulário de faltas.";
  }
} catch (PDOException $e) {
  echo "Erro ao buscar informações de aulas não ministradas: " . $e->getMessage();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Captura dos dados do formulário antes da validação
  $datasReposicao = $_POST['dataReposicao'] ?? [];
  $inicioHorarios = $_POST['inicioHorario'] ?? [];
  $teroHorarios = $_POST['teroHorario'] ?? [];
  $disciplinas = $_POST['nome_disciplina'] ?? [];

  // Processa os dados do formulário de reposição
  $virtude = $formularios[0]['motivo_falta'];
  $data_entrega = date('Y-m-d');
  $situacao = $_POST['situacao'] ?? 'Proposta Enviada';

  try {
    // Validação inicial para garantir que os campos obrigatórios estão presentes
    if (empty($datasReposicao) || empty($inicioHorarios) || empty($teroHorarios) || empty($disciplinas)) {
      throw new Exception('Todos os campos de datas, horários e disciplinas devem ser preenchidos.');
    }

    // Insere os dados na tabela formulario_reposicao
    $stmt = $conn->prepare("INSERT INTO formulario_reposicao (virtude, data_entrega, situacao, idfuncionario) VALUES (?, ?, ?, ?)");
    $stmt->execute([$virtude, $data_entrega, $situacao, $idFuncionario]);

    // Obtém o id_form_reposicao gerado
    $id_form_reposicao = $conn->lastInsertId();

    // Preparação das consultas
    $stmtAulas = $conn->prepare("INSERT INTO aulas_reposicao (data_reposicao, nome_disciplina, horarioinicio, horariofim) VALUES (?, ?, ?, ?)");
    $stmtRelacionamento = $conn->prepare("INSERT INTO aulas_reposicoa_formulario_reposicao (idaulas_reposicao, idform_reposicao) VALUES (?, ?)");
    $stmtInserirHAE = $conn->prepare("INSERT INTO horas_hae_professor (idfuncionario, dia_semana, data_atividade, horario_inicio, horario_fim, tipo_atividade, hae_total, hae_usadas) VALUES (?, ?, ?, ?, ?, ?, 0, 0)");

    $diasSemanaMap = [
      'Sunday' => 'Domingo',
      'Monday' => 'Segunda',
      'Tuesday' => 'Terça',
      'Wednesday' => 'Quarta',
      'Thursday' => 'Quinta',
      'Friday' => 'Sexta',
      'Saturday' => 'Sábado'
    ];

    for ($i = 0; $i < count($datasReposicao); $i++) {
      // Verificação de campos vazios na linha atual
      if (empty($datasReposicao[$i]) || empty($inicioHorarios[$i]) || empty($teroHorarios[$i]) || empty($disciplinas[$i])) {
        throw new Exception('Todos os campos de datas, horários e disciplinas devem ser preenchidos.');
      }

      // Capturar os horários de início e término da reposição atual
      $horario_inicio_novo = $inicioHorarios[$i];
      $horario_fim_novo = $teroHorarios[$i];
      $data_reposicao = $datasReposicao[$i];
      $nome_disciplina = $disciplinas[$i];

      // Validações adicionais para evitar conflitos de horário
      $stmtVerificaConflito = $conn->prepare("
              SELECT * FROM horas_hae_professor
              WHERE idfuncionario = ? AND data_atividade = ?
              AND horario_inicio < ? AND horario_fim > ?
          ");
      $stmtVerificaConflito->execute([
        $idFuncionario,
        $data_reposicao,
        $horario_fim_novo,
        $horario_inicio_novo
      ]);
      $conflito = $stmtVerificaConflito->fetch(PDO::FETCH_ASSOC);

      if ($conflito) {
        throw new Exception("Conflito de horário detectado para a data: " . $data_reposicao . " no horário " . $horario_inicio_novo . " - " . $horario_fim_novo);
      }

      // Insere as aulas de reposição
      $stmtAulas->execute([$data_reposicao, $nome_disciplina, $horario_inicio_novo, $horario_fim_novo]);
      $idaulas_reposicao = $conn->lastInsertId();
      $stmtRelacionamento->execute([$idaulas_reposicao, $id_form_reposicao]);

      // Verifica o dia da semana da data de reposição
      $diaSemana = date('l', strtotime($data_reposicao));
      $diaSemanaPT = $diasSemanaMap[$diaSemana] ?? '-';

      // Insere na tabela horas_hae_professor para manter a agenda completa
      $stmtInserirHAE->execute([$idFuncionario, $diaSemanaPT, $data_reposicao, $horario_inicio_novo, $horario_fim_novo, 'Reposição de Aula']);
    }

    // Insere os cursos relacionados à tabela formulario_reposicao_cursos
    $cursosSelecionados = array_column($formularios, 'idcursos');
    $stmtCurso = $conn->prepare("INSERT INTO formulario_reposicao_cursos (idform_reposicao, idcursos) VALUES (?, ?)");
    foreach ($cursosSelecionados as $curso) {
      $stmtCurso->execute([$id_form_reposicao, $curso]);
    }

    // Atualiza a situação do formulário de faltas original para "Proposta Enviada"
    $stmtUpdate = $conn->prepare("UPDATE formulario_faltas SET situacao = 'Proposta Enviada' WHERE idform_faltas = ? AND idfuncionario = ?");
    $stmtUpdate->execute([$formularios[0]['idform_faltas'], $idFuncionario]);

    echo "Formulário de reposição, aulas e cursos salvos com sucesso!";
    header("Location: home.php");
    exit;
  } catch (PDOException $e) {
    echo "Erro ao salvar os dados: " . $e->getMessage();
  } catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
  }
}
?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <title>Planejar Reposição</title>
  <link rel="stylesheet" href="./css/cssreposicao.css">

</head>

<body>
  <form id="form-reposicao" method="POST" enctype="multipart/form-data">
    <div class="title">
      <h1>Planejar Reposição</h1>
    </div>
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
          type="text" id="numero" value="<?php echo htmlspecialchars($nextIdForm); ?>" readonly>
      </div>
      <div class="form-field centralizado">
        <label for="reposicoes-mes">Reposições mês:</label>
        <input style="text-align: center; width: 230px; border: none; background-color: #f4f4f4; padding: 5px;"
          type="month" id="reposicoes-mes" value="<?php echo $mesAno; ?>" readonly>
      </div>


    </div>
    <div class="form-group-inline">
      <!-- Turno -->
      <div class="form-field">
        <label for="turno">Turno:</label>
        <div class="form-checks turno">
          <label><input type="radio" name="turno" value="manha" onclick="mostrarTabela('manha')"> Manhã</label>
          <label><input type="radio" name="turno" value="tarde" onclick="mostrarTabela('tarde')"> Tarde</label>
          <label><input type="radio" name="turno" value="noite" onclick="mostrarTabela('noite')"> Noite</label>
        </div>
      </div>

      <!-- Motivo da Reposição -->
      <div class="form-field">
        <label for="motivo_falta">Reposição em virtude de:</label>
        <input type="text" readonly value="
          <?php echo htmlspecialchars($formularios[0]['motivo_falta']); ?>"
          style="text-align: center; border: none; background-color: #f4f4f4; padding: 5px;">
      </div>

      <!-- Cursos Envolvidos -->
      <div class="form-field">
        <label>Cursos Envolvidos:</label>
        <div class="cursos-envolvidos">
          <?php foreach ($formularios as $formulario): ?>
            <input type="text" readonly value="<?php echo htmlspecialchars($formulario['nome_curso']); ?>"
              style="border: none; background-color: #f4f4f4; margin-right: 5px; padding: 5px;">
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Contêiner flexível para as duas seções -->
    <div class="tables-side-by-side">
      <!-- Aulas Não Mistradas -->
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
              <th>Data da Falta</th>
              <th>Data da Reposição</th>
              <th>Horário de Início e Término</th>
              <th>Disciplina(s)</th>
            </tr>
          </thead>
          <tbody>
            <?php $ordem = 1; ?>
            <?php if (!empty($aulasFaltas)): ?>
              <?php foreach ($aulasFaltas as $aula): ?>
                <tr>
                  <td><?php echo $ordem++; ?></td>
                  <td><?php echo date('d/m/Y', strtotime(htmlspecialchars($aula['data_aula']))); ?></td>
                  <td><input type="date" name="dataReposicao[]" required></td>
                  <td>
                    <input type="time" name="inicioHorario[]" required> às
                    <input type="time" name="teroHorario[]" required>
                  </td>
                  <td>
                    <?php echo htmlspecialchars($aula['nome_disciplina']); ?>
                    <input type="hidden" name="nome_disciplina[]"
                      value="<?php echo htmlspecialchars($aula['nome_disciplina']); ?>">
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5">Nenhuma aula registrada para reposição.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

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
          foreach (array_merge($aulas, $atividadesHAE) as $evento) {
            $horarioInicioFormatado = date('H:i', strtotime($evento['horario_inicio']));
            $horarioFimFormatado = date('H:i', strtotime($evento['horario_fim']));
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

                // Verifica se há uma reposição de aula inserida dinamicamente
                if (strpos($eventoEncontrado, 'Reposição de Aula') !== false) {
                  $classeEvento = 'reposicao'; // Classe para reposição
                }
              ?>
                <td class="<?php echo $classeEvento; ?>"><?php echo htmlspecialchars($eventoEncontrado); ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </table>
        <p style="text-align: center; margin-top: 10px;">Observe as exigências legais: máximo 8 horas diárias de
          trabalho, intervalo de 1 hora entre um expediente e outro e 6 horas em cada expediente.</p>


      </fieldset>
    </div>

    <input type="hidden" name="situacao" value="Proposta Enviada">

    <!-- Botão de Enviar -->
    <div class="form-footer">
      <button class="btn-enviar" type="submit">Enviar Formulário</button>
    </div>

  </form>

  <script>
    function atualizarAgendaCompletada() {
      // Captura os elementos dos inputs do formulário
      const datasReposicao = document.getElementsByName('dataReposicao[]');
      const inicioHorarios = document.getElementsByName('inicioHorario[]');
      const teroHorarios = document.getElementsByName('teroHorario[]');
      const disciplinas = document.getElementsByName('nome_disciplina[]');

      const tabela = document.getElementById('tabela-aulas');
      const linhas = tabela.querySelectorAll('tr');

      for (let i = 0; i < datasReposicao.length; i++) {
        if (datasReposicao[i].value && inicioHorarios[i].value && teroHorarios[i].value && disciplinas[i].value) {
          // Formata o horário de início e término
          const novoHorario = `${inicioHorarios[i].value} - ${teroHorarios[i].value}`;

          // Converte a data para obter o dia da semana em formato maiúsculo
          const data = new Date(datasReposicao[i].value);
          const diaSemana = data.toLocaleDateString('pt-BR', {
            weekday: 'long'
          }).toUpperCase();

          // Mapeia o dia da semana em português para o formato esperado (sem domingo)
          const diasSemanaMap = {
            'SEGUNDA-FEIRA': 2,
            'TERÇA-FEIRA': 3,
            'QUARTA-FEIRA': 4,
            'QUINTA-FEIRA': 5,
            'SEXTA-FEIRA': 6,
            'SÁBADO': 7
          };

          const colunaIndex = diasSemanaMap[diaSemana] ?? null;

          // Verifica se o dia da semana é válido (exclui domingo)
          if (colunaIndex !== null) {
            // Verifica se a linha com o horário já existe na tabela
            let linhaExistente = null;
            for (let j = 1; j < linhas.length; j++) {
              const celulaHorario = linhas[j].cells[0].textContent.trim();
              if (celulaHorario === novoHorario) {
                linhaExistente = linhas[j];
                break;
              }
            }

            if (linhaExistente) {
              // Atualiza a célula correspondente ao dia da semana
              linhaExistente.cells[colunaIndex].textContent = 'Reposição de Aula - ' + disciplinas[i].value;
              linhaExistente.cells[colunaIndex].classList.add('reposicao');
            } else {
              // Cria uma nova linha e insere na posição correta
              const novaLinha = tabela.insertRow();
              const celulaHorario = novaLinha.insertCell(0);
              celulaHorario.textContent = novoHorario;

              // Adiciona células vazias para os dias da semana
              for (let k = 1; k <= 6; k++) {
                const celulaDia = novaLinha.insertCell(k);
                if (k === colunaIndex) {
                  celulaDia.textContent = 'Reposição de Aula - ' + disciplinas[i].value;
                  celulaDia.classList.add('reposicao');
                } else {
                  celulaDia.textContent = '-';
                }
              }

              // Insere a nova linha na posição correta para manter a ordem
              let inserido = false;
              for (let l = 1; l < linhas.length; l++) {
                const horarioAtual = linhas[l].cells[0].textContent.trim();
                if (novoHorario < horarioAtual) {
                  tabela.insertBefore(novaLinha, linhas[l]);
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

    // Adiciona eventos de mudança nos campos do formulário para atualizar a agenda automaticamente
    document.querySelectorAll('input[name="dataReposicao[]"], input[name="inicioHorario[]"], input[name="teroHorario[]"]')
      .forEach(input => {
        input.addEventListener('change', atualizarAgendaCompletada);
      });
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