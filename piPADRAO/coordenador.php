<?php
include 'conexao.php';
include 'header_coordenador.html';
include 'auth.php';

// Verifica se o funcionário está logado
if (!isset($_SESSION['idfuncionario'])) {
  header("Location: index.php");
  exit;
}

$idfuncionario = $_SESSION['idfuncionario'];

try {
  // Consulta para buscar dados do formulário de reposição, excluindo os que estão como "deferido" e "indeferido"
  $stmtFormularios = $conn->prepare("
        SELECT 
            fr.idform_reposicao,
            fr.data_entrega,
            fr.situacao,
            fr.motivo_indeferimento,
            func.nome AS nome_professor,
            GROUP_CONCAT(DISTINCT ar.nome_disciplina ORDER BY ar.nome_disciplina SEPARATOR ', ') AS disciplinas,
            GROUP_CONCAT(DISTINCT ar.data_reposicao ORDER BY ar.data_reposicao SEPARATOR ', ') AS datas_reposicao,
            GROUP_CONCAT(DISTINCT CONCAT(ar.horarioinicio, ' às ', ar.horariofim) ORDER BY ar.horarioinicio SEPARATOR ', ') AS horarios_reposicao
        FROM formulario_reposicao fr
        JOIN funcionarios func ON fr.idfuncionario = func.idfuncionario
        LEFT JOIN aulas_reposicao ar ON fr.idform_reposicao = ar.idform_reposicao
        WHERE fr.situacao NOT IN ('deferido', 'indeferido') 
         GROUP BY fr.idform_reposicao
    ");
  $stmtFormularios->execute();
  $formularios = $stmtFormularios->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {

  echo "Erro ao buscar dados: " . $e->getMessage();
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
  <title>Coordenação</title>
  <link rel="stylesheet" href="./css/historico-coordenador.css">
</head>

<body>

  <!-- Sub Cabeçalho -->
  <div class="container-sc">
    <div class="first-column-sc">
      <a href="#">
        <img class="logo-ita" src="img/logo-fatec_itapira.png" alt="">
      </a>
      <a href="home_coordenador.php">
        <img class="logo-cps" src="img/logo-cps.png" alt="">
      </a>
    </div>
    <div class="second-column-sc">
      <h2 class="title">Lista de Formulários de Reposição</h2>
      <h2 class="title">Deferir / Indiferir</h2>
    </div>
    <div class="third-column-sc">
      <img class="logo-padrao" src="img/logo-padrao.png" alt="">
      <span class="bem-vindo-nome" style="margin: 0 10px; font-size: 16px; color: #333;">
        <p>Cord. <?php echo htmlspecialchars($_SESSION['nome']); ?></p>

      </span>
      <a class="btn-voltar" href="home_coordenador.php">
        <btn>VOLTAR</btn>
      </a>

    </div>
  </div>
  <?php if (!empty($errorMessage)): ?>
  <div class="error-message">
    <?php echo htmlspecialchars($errorMessage); ?>
  </div>
  <?php endif; ?>

  <div class="container">
    <ul class="teacher-list">
      <?php foreach ($formularios as $formulario): ?>
      <li class="teacher">
        <div class="teacher-info">
          <h2><?php echo htmlspecialchars("Prof. " . $formulario['nome_professor']); ?></h2>
          <div class="teacher-row">
            <span class="label">Disciplinas:</span>
            <span class="value"><?php echo htmlspecialchars($formulario['disciplinas']); ?></span>
          </div>
          <div class="teacher-row">
            <span class="label">Datas de Reposição:</span>
            <span class="value"><?php echo htmlspecialchars(formatarData($formulario['datas_reposicao'])); ?></span>
          </div>
          <div class="teacher-row">
            <span class="label">Horários:</span>
            <span class="value"><?php echo htmlspecialchars($formulario['horarios_reposicao']); ?></span>
          </div>
        </div>
        <div class="status <?php echo strtolower(str_replace(' ', '-', $formulario['situacao'])); ?>">
          <?php echo htmlspecialchars($formulario['situacao']); ?>
        </div>

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
  <footer>
    <div class="containerf">
      <a href="#">
        <img src="img/logo-governo-do-estado-sp.png" alt="Logo Governo do Estado SP">
      </a>
    </div>
  </footer>
</body>

</html>