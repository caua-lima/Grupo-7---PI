<?php
include '../conexao.php';
include '../header.html';

try {
  // Consulta para buscar dados do formulário de reposição e informações associadas
  $stmtFormularios = $conn->prepare("
        SELECT 
            fr.idform_reposicao,
            fr.data_entrega,
            fr.situacao,
            fr.motivo_indeferimento,  -- Seleciona o motivo de indeferimento
            func.nome AS nome_professor,
            GROUP_CONCAT(DISTINCT ar.nome_disciplina ORDER BY ar.nome_disciplina SEPARATOR ', ') AS disciplinas,
            GROUP_CONCAT(DISTINCT ar.data_reposicao ORDER BY ar.data_reposicao SEPARATOR ', ') AS datas_reposicao,
            GROUP_CONCAT(DISTINCT CONCAT(ar.horarioinicio, ' às ', ar.horariofim) ORDER BY ar.horarioinicio SEPARATOR ', ') AS horarios_reposicao
        FROM formulario_reposicao fr
        JOIN funcionarios func ON fr.idfuncionario = func.idfuncionario
        LEFT JOIN aulas_reposicao ar ON fr.idform_reposicao = ar.idform_reposicao
        GROUP BY fr.idform_reposicao
    ");
  $stmtFormularios->execute();
  $formularios = $stmtFormularios->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo "Erro ao buscar dados: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Coordenação</title>
  <link rel="stylesheet" href="../css/cordenador.css">
</head>

<body>
  <header>
    <img src="../img/fatec.itapira.png" class="logo" alt="Logo Fatec Itapira">
    <h1 class="form-title">Formulário de Reposição - Coordenação</h1>
    <img src="../img/cps.png" class="cps-logo" alt="Logo Centro de Paula Souza">
    <nav class="menu">
      <a href="home-coordenador.html">Início</a>
    </nav>
  </header>

  <div class="container">
    <h1>Lista de Formulários de Reposição</h1>
    <ul class="teacher-list">
      <?php foreach ($formularios as $formulario): ?>
      <li class="teacher">
        <div class="teacher-info">
          <h2><?php echo htmlspecialchars("Prof. " . $formulario['nome_professor']); ?></h2>
          <p>Disciplinas: <?php echo htmlspecialchars($formulario['disciplinas']); ?></p>
          <p>Datas de Reposição: <?php echo htmlspecialchars($formulario['datas_reposicao']); ?></p>
          <p>Horários: <?php echo htmlspecialchars($formulario['horarios_reposicao']); ?></p>
          <p>Data de Entrega: <?php echo htmlspecialchars($formulario['data_entrega']); ?></p>
        </div>
        <div class="status <?php echo strtolower(str_replace(' ', '-', $formulario['situacao'])); ?>">
          <?php echo htmlspecialchars($formulario['situacao']); ?>
        </div>

        <!-- Mostrar motivo de indeferimento se a situação for "indeferido" -->
        <?php if (strtolower($formulario['situacao']) === 'indeferido' && !empty($formulario['motivo_indeferimento'])): ?>
        <div class="motivo-indeferimento">
          <strong>Motivo do Indeferimento:</strong> <?php echo htmlspecialchars($formulario['motivo_indeferimento']); ?>
        </div>
        <?php endif; ?>

        <button class="details-btn" onclick="redirectToDetails('<?php echo $formulario['idform_reposicao']; ?>')">Ver
          detalhes</button>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <script>
  function redirectToDetails(formId) {
    // Redireciona para a página de detalhes do formulário com o ID do formulário de reposição
    window.location.href = 'detalhes-professor.php?idform_reposicao=' + encodeURIComponent(formId);
  }
  </script>
</body>

</html>