<?php
include 'conexao.php';
include 'header_coordenador.html';
include 'auth.php';

// Verifica se o funcionário está logado
if (!isset($_SESSION['idfuncionario'])) {
  header("Location: index.php");
  exit;
}

// Verifica se o ID da reposição foi fornecido
if (!isset($_GET['idform_reposicao'])) {
  echo "Formulário de reposição não especificado.";
  exit;
}

$idform_reposicao = $_GET['idform_reposicao'];

try {
  // Buscar informações do formulário de reposição e obter o idfuncionario do professor
  $stmtReposicao = $conn->prepare("
    SELECT 
        fr.data_entrega,
        fr.situacao,
        fr.virtude,
        fr.motivo_indeferimento,
        f.idform_faltas,
        f.datainicio,
        f.datafim,
        f.motivo_falta,
        f.idfuncionario,
        func.nome AS nome_professor,
        MAX(af.num_aulas) AS num_aulas,
        GROUP_CONCAT(DISTINCT ar.data_reposicao ORDER BY ar.data_reposicao SEPARATOR ', ') AS datas_reposicao,
        GROUP_CONCAT(DISTINCT ar.nome_disciplina ORDER BY ar.nome_disciplina SEPARATOR ', ') AS disciplinas,
        GROUP_CONCAT(DISTINCT CONCAT(ar.horarioinicio, ' às ', ar.horariofim) ORDER BY ar.horarioinicio SEPARATOR ', ') AS horarios,
        f.pdf_atestado,
        GROUP_CONCAT(DISTINCT c.nome_curso ORDER BY c.nome_curso SEPARATOR ', ') AS cursos
    FROM formulario_reposicao fr
    JOIN formulario_faltas f ON fr.idform_faltas = f.idform_faltas
    JOIN funcionarios func ON f.idfuncionario = func.idfuncionario
    LEFT JOIN aulas_reposicao ar ON fr.idform_reposicao = ar.idform_reposicao
    LEFT JOIN aulas_falta af ON f.idform_faltas = af.idform_faltas
    LEFT JOIN formulario_faltas_cursos fc ON f.idform_faltas = fc.idform_faltas
    LEFT JOIN cursos c ON fc.idcursos = c.idcursos
    WHERE fr.idform_reposicao = ?
    GROUP BY fr.data_entrega, fr.situacao, fr.virtude, fr.motivo_indeferimento, f.idform_faltas, f.datainicio, f.datafim, f.motivo_falta, f.idfuncionario, func.nome, f.pdf_atestado
  ");
  $stmtReposicao->execute([$idform_reposicao]);
  $reposicaoInfo = $stmtReposicao->fetch(PDO::FETCH_ASSOC);

  if (!$reposicaoInfo) {
    echo "Nenhuma informação de reposição encontrada para este formulário.";
    exit;
  }

  $pdfFile = './uploads/' . $reposicaoInfo['pdf_atestado'];
  if (!file_exists($pdfFile)) {
    $pdfFile = null;
  }

  // Obter o idfuncionario do professor
  $professorId = $reposicaoInfo['idfuncionario'];

  // Mapear dias da semana em inglês para português
  $diasSemanaMap = [
    'Monday' => 'SEGUNDA',
    'Tuesday' => 'TERÇA',
    'Wednesday' => 'QUARTA',
    'Thursday' => 'QUINTA',
    'Friday' => 'SEXTA',
    'Saturday' => 'SÁBADO',
    'Sunday' => 'DOMINGO',
  ];

  // Definir o array $diasSemana
  $diasSemana = ['SEGUNDA', 'TERÇA', 'QUARTA', 'QUINTA', 'SEXTA', 'SÁBADO'];

  // Consultas para buscar informações de aulas e reposições
  // Usando o $professorId em vez de $idfuncionario da sessão
  $stmtAulas = $conn->prepare("
    SELECT dia_semana, horario_inicio, horario_fim, disciplina
    FROM aulas_semanal_professor
    WHERE idfuncionario = ?
    ORDER BY FIELD(dia_semana, 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'), horario_inicio
  ");
  $stmtAulas->execute([$professorId]);
  $aulas = $stmtAulas->fetchAll(PDO::FETCH_ASSOC);

  $stmtHAE = $conn->prepare("
    SELECT idhae, dia_semana, data_atividade, horario_inicio, horario_fim, tipo_atividade, hae_total, hae_usadas
    FROM horas_hae_professor
    WHERE idfuncionario = ?
  ");
  $stmtHAE->execute([$professorId]);
  $atividadesHAE = $stmtHAE->fetchAll(PDO::FETCH_ASSOC);

  $stmtReposicaoAulas = $conn->prepare("
    SELECT data_reposicao, nome_disciplina, horarioinicio, horariofim
    FROM aulas_reposicao
    WHERE idform_reposicao = ?
  ");
  $stmtReposicaoAulas->execute([$idform_reposicao]);
  $aulasReposicao = $stmtReposicaoAulas->fetchAll(PDO::FETCH_ASSOC);

  // Obter todos os horários únicos de aulas, HAE e reposições com formatação de hora
  $horariosUnicos = [];

  foreach (array_merge($aulas, $atividadesHAE, $aulasReposicao) as $evento) {
    // Verifica e formata os horários corretamente
    $horarioInicioFormatado = date('H:i', strtotime($evento['horario_inicio'] ?? $evento['horarioinicio']));
    $horarioFimFormatado = date('H:i', strtotime($evento['horario_fim'] ?? $evento['horariofim']));
    $horariosUnicos[] = $horarioInicioFormatado . ' - ' . $horarioFimFormatado;
  }

  // Remove horários duplicados e ordena
  $horariosUnicos = array_unique($horariosUnicos);
  sort($horariosUnicos); // Ordena os horários para exibição

} catch (PDOException $e) {
  echo "Erro ao buscar dados: " . $e->getMessage();
  exit;
}

function formatarData($data)
{
  $meses = [
    'January' => 'Janeiro',
    'February' => 'Fevereiro',
    'March' => 'Março',
    'April' => 'Abril',
    'May' => 'Maio',
    'June' => 'Junho',
    'July' => 'Julho',
    'August' => 'Agosto',
    'September' => 'Setembro',
    'October' => 'Outubro',
    'November' => 'Novembro',
    'December' => 'Dezembro'
  ];

  $dataFormatada = date('d / F', strtotime($data));
  return strtr($dataFormatada, $meses);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detalhes da Reposição de Aulas</title>
  <link rel="stylesheet" href="./css/detalhes-professor.css">
</head>

<body>
  <!-- Modal -->
  <div id="pdfModal" class="modal">
    <div class="modal-content">
      <span class="filter-close" onclick="closeModal()">&times;</span>
      <iframe src="gerar_pdf.php?idform_reposicao=<?php echo $idform_reposicao; ?>" width="100%" height="100%"
        frameborder="0"></iframe>
    </div>
  </div>

  <!-- Sub Cabeçalho -->
  <div class="container-sc">
    <div class="first-column-sc">
      <a href="#">
        <img class="logo-ita" src="img/logo-fatec_itapira.png" alt="Logo FATEC Itapira">
      </a>
      <a href="#">
        <img class="logo-cps" src="img/logo-cps.png" alt="Logo CPS">
      </a>
    </div>
    <div class="second-column-sc">
      <h2 class="title">Detalhe do Formulário</h2><br>
      <h2 class="title">Faltas e Reposições</h2><br>
    </div>
    <div class="third-column-sc">
      <img class="logo-padrao" src="img/logo-padrao.png" alt="Logo Padrão">
      <span class="bem-vindo-nome">
        <p>Cord. <?php echo htmlspecialchars($_SESSION['nome']); ?></p>
      </span>
      <!-- Botão Voltar -->
      <button class="btn-voltar" onclick="goBack()">Voltar</button>
    </div>
  </div>

  <?php if (!empty($errorMessage)): ?>
  <div class="error-message">
    <?php echo htmlspecialchars($errorMessage); ?>
  </div>
  <?php endif; ?>

  <!-- Botões de Ação -->
  <div class="action-buttons">
    <button class="btn" onclick="updateStatus('deferido')">Deferir</button>
    <button class="btn" onclick="showIndeferForm()">Indeferir</button>
    <!-- Botão para abrir o modal -->
    <button class="btn" onclick="showModal()">Gerar PDF</button>
  </div>

  <!-- Formulário de Indeferimento -->
  <form id="reasonForm" style="display: none;" class="section">
    <label for="reason">Motivo da Indeferência:</label><br>
    <textarea id="reason" name="reason" rows="4" cols="50"></textarea><br>
    <button class="btn" type="button" onclick="updateStatus('indeferido')">Enviar</button>
  </form>

  <div class="container">
    <!-- Título e Justificativa da Falta -->
    <div class="flex-container">
      <!-- Seção Esquerda -->
      <div class="left-section">
        <h1>Justificativa da Falta</h1>
        <div class="section">
          <table class="info-table">
            <tr>
              <td><strong>Professor:</strong></td>
              <td><?php echo htmlspecialchars($reposicaoInfo['nome_professor']); ?></td>
            </tr>
            <tr>
              <td><strong>Motivo da Falta:</strong></td>
              <td><?php echo htmlspecialchars($reposicaoInfo['motivo_falta']); ?></td>
            </tr>
            <tr>
              <td><strong>Data:</strong></td>
              <td><?php echo htmlspecialchars(formatarData($reposicaoInfo['datainicio'])); ?></td>
            </tr>
            <?php if ($reposicaoInfo['datainicio'] !== $reposicaoInfo['datafim']): ?>
            <tr>
              <td><strong>Data de Fim:</strong></td>
              <td><?php echo htmlspecialchars(formatarData($reposicaoInfo['datafim'])); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
              <td><strong>Cursos:</strong></td>
              <td><?php echo htmlspecialchars($reposicaoInfo['cursos']); ?></td>
            </tr>
            <tr>
              <td><strong>Disciplinas:</strong></td>
              <td><?php echo htmlspecialchars($reposicaoInfo['disciplinas']); ?></td>
            </tr>
            <tr>
              <td><strong>Número de Aulas:</strong></td>
              <td><?php echo htmlspecialchars($reposicaoInfo['num_aulas']); ?></td>
            </tr>
          </table>
        </div>

        <!-- Exibir PDF do Atestado Médico -->
        <h1>Atestado / Comprovante</h1>
        <?php if (!empty($pdfFile)): ?>
        <div class="pdf-container">
          <embed src="<?php echo $pdfFile; ?>" type="application/pdf" width="100%" height="400px" />
        </div>
        <?php else: ?>
        <p>Arquivo PDF do atestado médico não encontrado.</p>
        <?php endif; ?>
      </div>

      <!-- Seção Direita -->
      <div class="right-section">
        <h1>Formulário da Reposição</h1>
        <div class="section">
          <table class="info-table">
            <tr>
              <td><strong>Datas das Reposições:</strong></td>
              <td><?php echo htmlspecialchars(formatarData($reposicaoInfo['datas_reposicao'])); ?></td>
            </tr>
            <tr>
              <td><strong>Disciplinas:</strong></td>
              <td><?php echo htmlspecialchars($reposicaoInfo['disciplinas']); ?></td>
            </tr>
            <tr>
              <td><strong>Horários:</strong></td>
              <td><?php echo htmlspecialchars($reposicaoInfo['horarios']); ?></td>
            </tr>
            <tr>
              <td><strong>Data de Entrega:</strong></td>
              <td><?php echo htmlspecialchars(formatarData($reposicaoInfo['data_entrega'])); ?></td>
            </tr>
            <tr>
              <td><strong>Situação da Reposição:</strong></td>
              <td>
                <span class="status 
      <?php
      echo strtolower($reposicaoInfo['situacao']) === 'indeferido' ? 'indeferido' : (strtolower($reposicaoInfo['situacao']) === 'deferido' ? 'deferido' :
        'proposta-enviada');
      ?>">
                  <?php echo htmlspecialchars($reposicaoInfo['situacao']); ?>
                </span>
              </td>
            </tr>
            <?php if (strtolower($reposicaoInfo['situacao']) === 'indeferido' && !empty($reposicaoInfo['motivo_indeferimento'])): ?>
            <tr>
              <td><strong>Motivo do Indeferimento:</strong></td>
              <td class="motivo-indeferimento">
                <?php echo htmlspecialchars($reposicaoInfo['motivo_indeferimento']); ?>
              </td>
            </tr>
            <?php endif; ?>
          </table>
        </div>

        <h1>Agenda Completa do Professor</h1>
        <!-- Tabela de Horários com Informações de Aulas e Atividades de HAE -->
        <div id="agenda-completa" class="tabela">
          <!-- Legenda das cores -->
          <div class="legenda-cores">
            <span
              style="background-color: #d4edda; display: inline-block; width: 20px; height: 20px; margin-right: 5px;"></span>
            Aulas
            <span
              style="background-color: #cce5ff; display: inline-block; width: 20px; height: 20px; margin-left: 15px; margin-right: 5px;"></span>
            HAE (Horas de Atividade Extra)
            <span
              style="background-color: #ffcccb; display: inline-block; width: 20px; height: 20px; margin-left: 15px; margin-right: 5px;"></span>
            Reposição de Aulas
          </div>
          <table id="tabela-aulas" class="styled-table">
            <thead>
              <tr>
                <th>Horário</th>
                <th>Segunda</th>
                <th>Terça</th>
                <th>Quarta</th>
                <th>Quinta</th>
                <th>Sexta</th>
                <th>Sábado</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Exibe os horários e as aulas de reposição
              foreach ($horariosUnicos as $horario) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($horario) . "</td>";

                // Exibe as aulas, HAE e reposição para cada dia da semana
                foreach ($diasSemana as $dia) {
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

                  // Exibe o evento encontrado
                  echo "<td class=\"" . htmlspecialchars($classeEvento) . "\">" . htmlspecialchars($eventoEncontrado) . "</td>";
                }
                echo "</tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script>
  function showModal() {
    document.getElementById('pdfModal').style.display = 'block';
  }

  function closeModal() {
    document.getElementById('pdfModal').style.display = 'none';
  }

  function showIndeferForm() {
    document.getElementById('reasonForm').style.display = 'block';
  }

  function updateStatus(status) {
    let motivoIndeferimento = '';
    if (status === 'indeferido') {
      motivoIndeferimento = document.getElementById('reason').value;
    }

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "atualizar_status.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
        alert(xhr.responseText);
        location.reload();
      }
    };
    xhr.send("idform_reposicao=<?php echo $idform_reposicao; ?>&status=" + status + "&motivo_indeferimento=" +
      encodeURIComponent(motivoIndeferimento));
  }

  function goBack() {
    window.history.back();
  }
  </script>
</body>

</html>