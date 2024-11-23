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
  <!-- Meta tags -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Coordenação</title>
  <!-- CSS externo -->
  <link rel="stylesheet" href="./css/historico-coordenador.css">
  <!-- Font Awesome para os ícones -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>

<body>
  <!-- Sub Cabeçalho -->
  <div class="container-sc">
    <div class="first-column-sc">
      <a href="home_coordenador.php">
        <img class="logo-ita" src="img/logo-fatec_itapira.png" alt="">
      </a>
      <a href="home_coordenador.php">
        <img class="logo-cps" src="img/logo-cps.png" alt="">
      </a>
    </div>
    <div class="second-column-sc">
      <h2 class="title"> Histórico </h2>
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

    <!-- Botão para abrir o modal de filtro -->
    <button onclick="document.getElementById('filterModal').style.display='block'" class="btn-filtro">Filtrar</button>

    <!-- Modal de Filtro -->
    <div id="filterModal" class="filter-modal">
      <div class="filter-modal-content">
        <span class="filter-close" onclick="document.getElementById('filterModal').style.display='none'">&times;</span>
        <h2>Filtrar Formulários</h2>
        <form method="GET" action="">
          <label for="nome_professor">Nome do Professor:</label>
          <input type="text" id="nome_professor" name="nome_professor"
            value="<?php echo htmlspecialchars($filtroNome); ?>" placeholder="Nome do Professor">

          <label for="data_reposicao">Data de Reposição:</label>
          <input type="date" id="data_reposicao" name="data_reposicao"
            value="<?php echo htmlspecialchars($filtroData); ?>">

          <label for="status">Status:</label>
          <select id="status" name="status">
            <option value="">Todos</option>
            <option value="deferido" <?php if ($filtroStatus === 'deferido') echo 'selected'; ?>>Deferido</option>
            <option value="indeferido" <?php if ($filtroStatus === 'indeferido') echo 'selected'; ?>>Indeferido</option>
          </select>

          <label for="disciplina">Disciplina:</label>
          <input type="text" id="disciplina" name="disciplina"
            value="<?php echo htmlspecialchars($filtroDisciplina); ?>" placeholder="Disciplina">

          <label for="ordenacao">Ordenação:</label>
          <select id="ordenacao" name="ordenacao">
            <option value="fr.data_entrega DESC" <?php if ($ordenacao === 'fr.data_entrega DESC') echo 'selected'; ?>>
              Data de Entrega (mais recente)</option>
            <option value="func.nome ASC" <?php if ($ordenacao === 'func.nome ASC') echo 'selected'; ?>>Nome do
              Professor (A-Z)</option>
          </select>

          <button type="submit" class="btn-filtro">Aplicar Filtros</button>
          <a href="historico-coordenador.php" class="btn-filtro"
            style="background-color: #dc3545; margin-left: 10px;">Limpar Filtros</a>
        </form>
      </div>
    </div>

    <!-- Lista de Registros -->
    <ul class="teacher-list">
      <?php foreach ($formularios as $formulario): ?>
      <li class="teacher">
        <div class="teacher-info">
          <h2><?php echo htmlspecialchars("Prof. " . $formulario['nome_professor']); ?></h2>
          <table>
            <thead>
              <tr>
                <th>Informação</th>
                <th>Detalhes</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Disciplinas:</td>
                <td><?php echo htmlspecialchars($formulario['disciplinas']); ?></td>
              </tr>
              <tr>
                <td>Datas de Reposição:</td>
                <td><?php echo htmlspecialchars(formatarData($formulario['datas_reposicao'])); ?></td>
              </tr>
              <tr>
                <td>Horários:</td>
                <td><?php echo htmlspecialchars($formulario['horarios_reposicao']); ?></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="teacher-actions">
          <div class="status <?php echo strtolower($formulario['situacao']); ?>">
            <?php echo htmlspecialchars($formulario['situacao']); ?>
          </div>
          <?php if (strtolower($formulario['situacao']) === 'indeferido' && !empty($formulario['motivo_indeferimento'])): ?>
          <div class="motivo-indeferimento">
            <strong>Motivo do Indeferimento:</strong>
            <?php echo htmlspecialchars($formulario['motivo_indeferimento']); ?>
          </div>
          <?php endif; ?>
        </div>
        <div class="buttons-container">
          <!-- Ícone de PDF -->
          <button class="pdf-btn" onclick="generatePDF('<?php echo $formulario['idform_reposicao']; ?>')">
            <i class="fas fa-file-pdf"></i>
          </button>
          <!-- Botão "Ver Detalhes" -->
          <button class="details-btn" onclick="redirectToDetails('<?php echo $formulario['idform_reposicao']; ?>')">Ver
            Detalhes</button>
        </div>

      </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <!-- Modal para exibir o PDF -->
  <div id="pdfModal" class="pdf-modal">
    <div class="pdf-modal-content">
      <span class="pdf-close" onclick="closePDFModal()">&times;</span>
      <iframe id="pdfIframe" src="" width="100%" height="100%" frameborder="0"></iframe>
    </div>
  </div>

  <!-- Scripts -->
  <script>
  // Função para redirecionar aos detalhes
  function redirectToDetails(formId) {
    window.location.href = 'detalhes-professor.php?idform_reposicao=' + encodeURIComponent(formId);
  }

  // Função para gerar o PDF e exibir no modal
  function generatePDF(formId) {
    document.getElementById('pdfIframe').src = 'gerar_pdf.php?idform_reposicao=' + encodeURIComponent(formId);
    document.getElementById('pdfModal').style.display = 'block';
  }

  // Função para fechar o modal do PDF
  function closePDFModal() {
    document.getElementById('pdfModal').style.display = 'none';
    document.getElementById('pdfIframe').src = '';
  }

  // Fechar o modal ao clicar fora do conteúdo
  window.onclick = function(event) {
    const filterModal = document.getElementById('filterModal');
    const pdfModal = document.getElementById('pdfModal');
    if (event.target == filterModal) {
      filterModal.style.display = "none";
    } else if (event.target == pdfModal) {
      closePDFModal();
    }
  };
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