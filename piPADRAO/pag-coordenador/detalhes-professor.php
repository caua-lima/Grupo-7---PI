<?php
include '../conexao.php';
include '../header.html';

// Verifica se o ID do formulário de reposição foi passado via URL
if (!isset($_GET['idform_reposicao'])) {
    echo "Formulário de reposição não especificado.";
    exit;
}

$idform_reposicao = $_GET['idform_reposicao'];

try {
    // Consulta para obter dados do formulário de reposição e associar ao formulário de faltas e ao professor
    $stmtReposicao = $conn->prepare("
        SELECT 
            fr.data_entrega,
            fr.situacao,
            fr.virtude,
            f.idform_faltas,  -- Precisamos do ID do formulário de faltas para a segunda consulta
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

    // Extrair as informações principais do primeiro registro
    $reposicaoInfo = $reposicoes[0];

    // Segunda consulta para obter o PDF do formulário de faltas
    $stmtFaltas = $conn->prepare("
        SELECT pdf_atestado
        FROM formulario_faltas
        WHERE idform_faltas = ?
    ");
    $stmtFaltas->execute([$reposicaoInfo['idform_faltas']]);
    $falta = $stmtFaltas->fetch(PDO::FETCH_ASSOC);

    // Definir o caminho completo para o PDF do formulário de faltas
    $pdfFile = '../uploads/' . $falta['pdf_atestado'];
    if (!file_exists($pdfFile)) {
        $pdfFile = null; // Configura como null se o arquivo não for encontrado
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
    <!-- Título e Justificativa da Falta -->
    <h1>Justificativa da Falta: <?php echo htmlspecialchars($reposicaoInfo['motivo_falta']); ?></h1>
    <p class="justify-text">O professor <?php echo htmlspecialchars($reposicaoInfo['nome_professor']); ?> está ausente
      devido a <?php echo htmlspecialchars($reposicaoInfo['motivo_falta']); ?>.</p>

    <!-- Exibir PDF do Atestado Médico do Formulário de Faltas -->
    <?php if (!empty($pdfFile)): ?>
    <div class="pdf-container">
      <embed src="<?php echo $pdfFile; ?>" type="application/pdf" width="100%" height="400px" />
    </div>
    <?php else: ?>
    <p>Arquivo PDF do atestado médico não encontrado.</p>
    <?php endif; ?>

    <!-- Informações de datas e situação -->
    <div class="date-info">
      <p><strong>Data de Início da Falta:</strong> <?php echo htmlspecialchars($reposicaoInfo['datainicio']); ?></p>
      <p><strong>Data de Fim da Falta:</strong> <?php echo htmlspecialchars($reposicaoInfo['datafim']); ?></p>
      <p><strong>Situação da Reposição:</strong> <?php echo htmlspecialchars($reposicaoInfo['situacao']); ?></p>
      <p><strong>Data de Entrega da Reposição:</strong> <?php echo htmlspecialchars($reposicaoInfo['data_entrega']); ?>
      </p>
    </div>

    <!-- Informações do Formulário de Reposição -->
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
      <button class="btn"
        onclick="deferClasses('<?php echo htmlspecialchars($reposicaoInfo['nome_professor']); ?>')">Deferir</button>
      <button class="btn" onclick="showIndeferForm()">Indeferir</button>
    </div>

    <!-- Formulário de Indeferimento -->
    <form id="reasonForm" style="display: none;">
      <label for="reason">Motivo da Indeferência:</label><br>
      <textarea id="reason" name="reason" rows="4" cols="50"></textarea><br>
      <label for="repositionDate">Data para Reposição de Aulas:</label><br>
      <input type="date" id="repositionDate" name="repositionDate"><br><br>
      <button class="btn" type="button"
        onclick="rejectClasses('<?php echo htmlspecialchars($reposicaoInfo['nome_professor']); ?>')">Enviar</button>
    </form>
  </div>

  <script>
  function showIndeferForm() {
    document.getElementById('reasonForm').style.display = 'block';
  }

  function deferClasses(professorName) {
    alert("Reposição deferida para o professor " + professorName);
    // Implementar chamada AJAX para registrar deferimento no sistema
  }

  function rejectClasses(professorName) {
    alert("Reposição indeferida para o professor " + professorName);
    // Implementar chamada AJAX para registrar indeferimento no sistema
  }
  </script>
</body>

</html>