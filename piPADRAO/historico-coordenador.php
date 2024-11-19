<?php
include 'conexao.php';
include 'header.html';

// Inicializa as variáveis de filtro para evitar erros de undefined variable
$filtroNome = '';
$filtroData = '';
$filtroStatus = '';
$filtroDisciplina = '';
$ordenacao = 'fr.data_entrega DESC'; // Ordenação padrão

// Verifica se os filtros foram enviados via GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $filtroNome = $_GET['nome_professor'] ?? '';
  $filtroData = $_GET['data_reposicao'] ?? '';
  $filtroStatus = $_GET['status'] ?? '';
  $filtroDisciplina = $_GET['disciplina'] ?? '';
  $ordenacao = $_GET['ordenacao'] ?? 'fr.data_entrega DESC';
}

try {
  // Consulta para buscar dados do formulário de reposição com filtros aplicados
  $query = "
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
      WHERE fr.situacao IN ('deferido', 'indeferido')
  ";

  // Condições de filtro dinâmicas
  $params = [];
  if (!empty($filtroNome)) {
    $query .= " AND func.nome LIKE ?";
    $params[] = "%$filtroNome%";
  }
  if (!empty($filtroData)) {
    $query .= " AND ar.data_reposicao = ?";
    $params[] = $filtroData;
  }
  if (!empty($filtroStatus)) {
    $query .= " AND fr.situacao = ?";
    $params[] = $filtroStatus;
  }
  if (!empty($filtroDisciplina)) {
    $query .= " AND ar.nome_disciplina LIKE ?";
    $params[] = "%$filtroDisciplina%";
  }

  // Adiciona a cláusula de GROUP BY e ORDER BY
  $query .= " GROUP BY fr.idform_reposicao ORDER BY $ordenacao";

  // Executa a consulta com os parâmetros dinâmicos
  $stmtFormularios = $conn->prepare($query);
  $stmtFormularios->execute($params);
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
  <link rel="stylesheet" href="css/historico-coordenador.css">
  <style>
  /* Estilos para o modal */
  .modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
  }

  .modal-content {
    background-color: #fff;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 500px;
    border-radius: 5px;
  }

  .close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
  }
  </style>
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
            <a class="btn" href="home_coordenador.php"><btn>VOLTAR</btn></a>
        </div>
    </div>
  <div class="container">
    <h1>Lista de Formulários de Reposição - Deferidos e Indeferidos</h1>

    <!-- Botão para abrir o modal de filtro -->
    <button onclick="document.getElementById('filterModal').style.display='block'" class="btn-filtro" >Abrir Filtros</button>

    <!-- Modal de Filtro -->
    <div id="filterModal" class="modal">
      <div class="modal-content">
        <span class="close" onclick="document.getElementById('filterModal').style.display='none'">&times;</span>
        <h2>Filtrar Formulários</h2>

        <!-- Formulário de Filtro no Modal -->
         <div class="form-modal">
            <form method="GET" action="">
              <label>Nome do Professor:</label>
              <input type="text" name="nome_professor" value="<?php echo htmlspecialchars($filtroNome); ?>"
                placeholder="Nome do Professor">

              <label>Data de Reposição:</label>
              <input type="date" name="data_reposicao" value="<?php echo htmlspecialchars($filtroData); ?>">

              <label>Status:</label>
              <select name="status">
                <option value="">Todos</option>
                <option value="deferido" <?php if ($filtroStatus == 'deferido') echo 'selected'; ?>>Deferido</option>
                <option value="indeferido" <?php if ($filtroStatus == 'indeferido') echo 'selected'; ?>>Indeferido</option>
              </select>

              <label>Disciplina:</label>
              <input type="text" name="disciplina" value="<?php echo htmlspecialchars($filtroDisciplina); ?>"
                placeholder="Disciplina">

              <label>Ordenação:</label>
              <select name="ordenacao">
                <option value="fr.data_entrega DESC" <?php if ($ordenacao == 'fr.data_entrega DESC') echo 'selected'; ?>>
                  Data de Entrega (mais recente)</option>
                <option value="func.nome ASC" <?php if ($ordenacao == 'func.nome ASC') echo 'selected'; ?>>Nome do Professor
                  (A-Z)</option>
              </select>

            <div class="btns">
              <button class="btn-aplica" type="submit" >Aplicar Filtros</button>
              <a class="btn-limpa" href="historico-coordenador.php" style="margin-left: 10px;">Limpar Filtros</a>
            </div>
            </form>
          </div>
      </div>
    </div>

    <!-- Lista de Registros -->
    <ul class="teacher-list">
      <?php foreach ($formularios as $formulario): ?>
      <li class="teacher">
        <div class="teacher-info">
          <h2><?php echo htmlspecialchars("Prof. " . $formulario['nome_professor']); ?></h2>
          <p>Disciplinas: <?php echo htmlspecialchars($formulario['disciplinas']); ?></p>
          <p>Datas de Reposição: <?php echo htmlspecialchars($formulario['datas_reposicao']); ?></p>
          <p>Horários: <?php echo htmlspecialchars($formulario['horarios_reposicao']); ?></p>
        </div>
        <div class="status <?php echo strtolower(str_replace(' ', '-', $formulario['situacao'])); ?>">
          <?php echo htmlspecialchars($formulario['situacao']); ?>
        </div>

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
  // Função para redirecionar aos detalhes
  function redirectToDetails(formId) {
    window.location.href = 'detalhes-professor.php?idform_reposicao=' + encodeURIComponent(formId);
  }

  // Fechar o modal ao clicar fora do conteúdo
  window.onclick = function(event) {
    const modal = document.getElementById('filterModal');
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }
  </script>
</body>

</html>
