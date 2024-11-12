<?php
include '../conexao.php';
include '../header.html';

if (!isset($_GET['idform_reposicao'])) {
  echo "Formulário de reposição não especificado.";
  exit;
}

$idform_reposicao = $_GET['idform_reposicao'];

try {
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
        GROUP BY fr.data_entrega, fr.situacao, fr.virtude, fr.motivo_indeferimento, f.idform_faltas, f.datainicio, f.datafim, f.motivo_falta, func.nome, f.pdf_atestado
    ");
  $stmtReposicao->execute([$idform_reposicao]);
  $reposicaoInfo = $stmtReposicao->fetch(PDO::FETCH_ASSOC);

  if (!$reposicaoInfo) {
    echo "Nenhuma informação de reposição encontrada para este formulário.";
    exit;
  }

  $pdfFile = '../uploads/' . $reposicaoInfo['pdf_atestado'];
  if (!file_exists($pdfFile)) {
    $pdfFile = null;
  }
} catch (PDOException $e) {
  echo "Erro ao buscar dados: " . $e->getMessage();
  exit;
}

// Função para formatar datas no formato "09 de novembro"
function formatarData($data)
{
  setlocale(LC_TIME, 'pt_BR.utf8');
  return strftime('%d de %B', strtotime($data));
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detalhes da Reposição de Aulas</title>
  <link rel="stylesheet" href="../css/detalhes-professor.css">
</head>

<body>
  <div class="container">
    <!-- Título e Justificativa da Falta -->
    <h1>Justificativa da Falta</h1>
    <div class="section">
      <p><strong>Professor:</strong> <?php echo htmlspecialchars($reposicaoInfo['nome_professor']); ?></p>
      <p><strong>Motivo da Falta:</strong> <?php echo htmlspecialchars($reposicaoInfo['motivo_falta']); ?></p>
      <p><strong>Data:</strong> <?php echo htmlspecialchars(formatarData($reposicaoInfo['datainicio'])); ?>
      </p>

      <?php if ($reposicaoInfo['datainicio'] !== $reposicaoInfo['datafim']): ?>
      <p><strong>Data de Fim:</strong> <?php echo htmlspecialchars(formatarData($reposicaoInfo['datafim'])); ?></p>
      <?php endif; ?>

      <p><strong>Número de Aulas:</strong> <?php echo htmlspecialchars($reposicaoInfo['num_aulas']); ?></p>
      <p><strong>Cursos:</strong> <?php echo htmlspecialchars($reposicaoInfo['cursos']); ?></p>
    </div>

    <!-- Exibir PDF do Atestado Médico -->
    <h2>Atestado Médico</h2>
    <?php if (!empty($pdfFile)): ?>
    <div class="pdf-container">
      <embed src="<?php echo $pdfFile; ?>" type="application/pdf" width="100%" height="400px" />
    </div>
    <?php else: ?>
    <p>Arquivo PDF do atestado médico não encontrado.</p>
    <?php endif; ?>

    <!-- Informações da Reposição -->
    <h2>Detalhes da Reposição de Aulas</h2>
    <div class="section">
      <p><strong>Data de Entrega:</strong> <?php echo htmlspecialchars(formatarData($reposicaoInfo['data_entrega'])); ?>
      </p>
      <p><strong>Situação da Reposição:</strong> <?php echo htmlspecialchars($reposicaoInfo['situacao']); ?></p>

      <?php if (strtolower($reposicaoInfo['situacao']) === 'indeferido' && !empty($reposicaoInfo['motivo_indeferimento'])): ?>
      <p><strong>Motivo do Indeferimento:</strong>
        <?php echo htmlspecialchars($reposicaoInfo['motivo_indeferimento']); ?></p>
      <?php endif; ?>
    </div>

    <!-- Detalhes das Aulas de Reposição -->
    <div class="reposition-info section">
      <p><strong>Datas das Reposições:</strong>
        <?php echo htmlspecialchars(formatarData($reposicaoInfo['datas_reposicao'])); ?></p>
      <p><strong>Disciplinas:</strong> <?php echo htmlspecialchars($reposicaoInfo['disciplinas']); ?></p>
      <p><strong>Horários:</strong> <?php echo htmlspecialchars($reposicaoInfo['horarios']); ?></p>
    </div>

    <!-- Botões de Ação -->
    <div class="action-buttons">
      <button class="btn" onclick="updateStatus('deferido')">Deferir</button>
      <button class="btn" onclick="showIndeferForm()">Indeferir</button>
      <button class="btn" onclick="goBack()">Voltar</button>
    </div>

    <!-- Formulário de Indeferimento -->
    <form id="reasonForm" style="display: none;" class="section">
      <label for="reason">Motivo da Indeferência:</label><br>
      <textarea id="reason" name="reason" rows="4" cols="50"></textarea><br>
      <button class="btn" type="button" onclick="updateStatus('indeferido')">Enviar</button>
    </form>
  </div>

  <script>
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
        location.reload(); // Recarrega a página após a atualização
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