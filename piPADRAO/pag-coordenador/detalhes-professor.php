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
            f.idform_faltas,  
            f.datainicio,
            f.datafim,
            f.motivo_falta,
            func.nome AS nome_professor,
            ar.data_reposicao,
            ar.nome_disciplina,
            ar.horarioinicio,
            ar.horariofim
        FROM formulario_reposicao fr
        JOIN formulario_faltas f ON fr.idform_faltas = f.idform_faltas
        JOIN funcionarios func ON f.idfuncionario = func.idfuncionario
        LEFT JOIN aulas_reposicao ar ON fr.idform_reposicao = ar.idform_reposicao
        WHERE fr.idform_reposicao = ?
        ORDER BY ar.data_reposicao, ar.horarioinicio
    ");
  $stmtReposicao->execute([$idform_reposicao]);
  $reposicoes = $stmtReposicao->fetchAll(PDO::FETCH_ASSOC);

  if (!$reposicoes) {
    echo "Nenhuma informação de reposição encontrada para este formulário.";
    exit;
  }

  $reposicaoInfo = $reposicoes[0];

  $stmtFaltas = $conn->prepare("
        SELECT pdf_atestado
        FROM formulario_faltas
        WHERE idform_faltas = ?
    ");
  $stmtFaltas->execute([$reposicaoInfo['idform_faltas']]);
  $falta = $stmtFaltas->fetch(PDO::FETCH_ASSOC);

  $pdfFile = '../uploads/' . $falta['pdf_atestado'];
  if (!file_exists($pdfFile)) {
    $pdfFile = null;
  }
} catch (PDOException $e) {
  echo "Erro ao buscar dados: " . $e->getMessage();
  exit;
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
    <h1>Justificativa da Falta: <?php echo htmlspecialchars($reposicaoInfo['motivo_falta']); ?></h1>
    <p class="justify-text">O professor <?php echo htmlspecialchars($reposicaoInfo['nome_professor']); ?> está ausente
      devido a <?php echo htmlspecialchars($reposicaoInfo['motivo_falta']); ?>.</p>

    <?php if (!empty($pdfFile)): ?>
    <div class="pdf-container">
      <embed src="<?php echo $pdfFile; ?>" type="application/pdf" width="100%" height="400px" />
    </div>
    <?php else: ?>
    <p>Arquivo PDF do atestado médico não encontrado.</p>
    <?php endif; ?>

    <div class="date-info">
      <p><strong>Data de Início da Falta:</strong> <?php echo htmlspecialchars($reposicaoInfo['datainicio']); ?></p>
      <p><strong>Data de Fim da Falta:</strong> <?php echo htmlspecialchars($reposicaoInfo['datafim']); ?></p>
      <p><strong>Situação da Reposição:</strong> <?php echo htmlspecialchars($reposicaoInfo['situacao']); ?></p>
      <p><strong>Data de Entrega da Reposição:</strong> <?php echo htmlspecialchars($reposicaoInfo['data_entrega']); ?>
      </p>
    </div>

    <h2>Detalhes da Reposição de Aulas</h2>

    <?php foreach ($reposicoes as $reposicao): ?>
    <div class="reposition-info">
      <p><strong>Data da Reposição:</strong> <?php echo htmlspecialchars($reposicao['data_reposicao']); ?></p>
      <p><strong>Disciplina:</strong> <?php echo htmlspecialchars($reposicao['nome_disciplina']); ?></p>
      <p><strong>Horário da Reposição:</strong>
        <?php echo htmlspecialchars($reposicao['horarioinicio']) . " às " . htmlspecialchars($reposicao['horariofim']); ?>
      </p>
    </div>
    <?php endforeach; ?>

    <!-- Botões de Ação -->
    <div class="action-buttons">
      <button class="btn" onclick="updateStatus('deferido')">Deferir</button>
      <button class="btn" onclick="showIndeferForm()">Indeferir</button>
    </div>

    <!-- Formulário de Indeferimento -->
    <form id="reasonForm" style="display: none;">
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
  </script>
</body>

</html>