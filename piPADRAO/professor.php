<?php
include 'conexao.php';
include 'header.html';
include 'auth.php';

// Verifica se o funcionário está logado
if (!isset($_SESSION['idfuncionario'])) {
  header("Location: index.php");
  exit;
}

$idfuncionario = $_SESSION['idfuncionario'];

// Inicializa variáveis de filtro para evitar erros de "undefined variable"
$filtroMotivo = $_GET['motivo_falta'] ?? '';
$filtroDataEntrega = $_GET['data_entrega'] ?? '';
$filtroStatus = $_GET['status'] ?? '';
$filtroDisciplina = $_GET['disciplina'] ?? '';
$ordenacao = $_GET['ordenacao'] ?? 'fr.data_entrega DESC';

try {
  // Consulta para buscar os dados filtrados do formulário de faltas
  $query = "
        SELECT 
            fr.idform_reposicao,
            fr.idform_faltas,
            fr.data_entrega,
            fr.situacao,
            fr.motivo_indeferimento,
            f.motivo_falta,
            func.nome AS nome_professor,
            GROUP_CONCAT(DISTINCT ar.nome_disciplina ORDER BY ar.nome_disciplina SEPARATOR ', ') AS disciplinas,
            GROUP_CONCAT(DISTINCT ar.data_reposicao ORDER BY ar.data_reposicao SEPARATOR ', ') AS datas_reposicao,
            GROUP_CONCAT(DISTINCT CONCAT(ar.horarioinicio, ' às ', ar.horariofim) ORDER BY ar.horarioinicio SEPARATOR ', ') AS horarios_reposicao
        FROM formulario_reposicao fr
        JOIN funcionarios func ON fr.idfuncionario = func.idfuncionario
        JOIN formulario_faltas f ON fr.idform_faltas = f.idform_faltas
        LEFT JOIN aulas_reposicao ar ON fr.idform_reposicao = ar.idform_reposicao
        WHERE fr.situacao IN ('deferido', 'indeferido', 'Proposta Enviada') 
          AND fr.idfuncionario = ?";

  $params = [$idfuncionario];

  // Adiciona filtros dinâmicos
  if (!empty($filtroMotivo)) {
    $query .= " AND f.motivo_falta LIKE ?";
    $params[] = "%$filtroMotivo%";
  }
  if (!empty($filtroDataEntrega)) {
    $query .= " AND fr.data_entrega >= ?";
    $params[] = $filtroDataEntrega;
  }
  if (!empty($filtroStatus)) {
    $query .= " AND fr.situacao = ?";
    $params[] = $filtroStatus;
  }
  if (!empty($filtroDisciplina)) {
    $query .= " AND ar.nome_disciplina LIKE ?";
    $params[] = "%$filtroDisciplina%";
  }

  // Adiciona agrupamento e ordenação
  $query .= " GROUP BY fr.idform_reposicao ORDER BY $ordenacao";

  // Prepara e executa a consulta
  $stmt = $conn->prepare($query);
  $stmt->execute($params);
  $faltas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo "Erro ao buscar dados: " . htmlspecialchars($e->getMessage());
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
  <title>Histórico de Faltas e Reposições</title>
  <link rel="stylesheet" href="css/professor.css">
</head>

<body>
  <!-- Sub Cabeçalho -->
  <div class="container-sc">
    <div class="first-column-sc">
      <a href="home.php">
        <img class="logo-ita" src="img/logo-fatec_itapira.png" alt="">
      </a>
      <a href="home.php">
        <img class="logo-cps" src="img/logo-cps.png" alt="">
      </a>
    </div>
    <div class="second-column-sc">
      <h2 class="title"> Histórico de Faltas </h2>
    </div>
    <div class="third-column-sc">
      <img class="logo-padrao" src="img/logo-padrao.png" alt="">
      <span class="bem-vindo-nome" style="margin: 0 10px; font-size: 16px; color: #333;">
        <p>Prof. <br><?php echo htmlspecialchars($_SESSION['nome']); ?></p>
      </span>
      <a class="btn-voltar" href="home.php">
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
    <button class="btn-filtro" onclick="abrirModal()">Filtrar</button>

    <!-- Modal de Filtro -->
    <div id="filterModal" class="modal">
      <div class="modal-content">
        <span class="close" onclick="fecharModal()">&times;</span>
        <h2>Filtrar Dados</h2>
        <form method="GET" action="">
          <label for="motivo_falta">Motivo da Falta:</label>
          <input type="text" id="motivo_falta" name="motivo_falta"
            value="<?php echo htmlspecialchars($filtroMotivo); ?>" placeholder="Digite o motivo">

          <label for="data_entrega">Data de Entrega:</label>
          <input type="date" id="data_entrega" name="data_entrega"
            value="<?php echo htmlspecialchars($filtroDataEntrega); ?>">

          <label for="status">Situação:</label>
          <select id="status" name="status">
            <option value="">Todos</option>
            <option value="deferido" <?php if ($filtroStatus === 'deferido') echo 'selected'; ?>>Deferido</option>
            <option value="indeferido" <?php if ($filtroStatus === 'indeferido') echo 'selected'; ?>>Indeferido</option>
          </select>

          <label for="disciplina">Disciplina:</label>
          <input type="text" id="disciplina" name="disciplina"
            value="<?php echo htmlspecialchars($filtroDisciplina); ?>" placeholder="Digite a disciplina">

          <label for="ordenacao">Ordenar por:</label>
          <select id="ordenacao" name="ordenacao">
            <option value="fr.data_entrega DESC" <?php if ($ordenacao === 'fr.data_entrega DESC') echo 'selected'; ?>>
              Data de Entrega (mais recente)</option>
            <option value="fr.data_entrega ASC" <?php if ($ordenacao === 'fr.data_entrega ASC') echo 'selected'; ?>>Data
              de Entrega (mais antiga)</option>
          </select>

          <button type="submit" class="btn-aplicar">Aplicar Filtros</button>
          <a href="historico.php" class="btn-limpar">Limpar Filtros</a>
        </form>
      </div>
    </div>
    <!-- Lista de Registros -->
    <ul class="teacher-list">
      <?php if (count($faltas) > 0): ?>
      <?php foreach ($faltas as $falta): ?>
      <li class="teacher">
        <div class="teacher-info">
          <h2><?php echo htmlspecialchars("Prof. " . $falta['nome_professor']); ?></h2>
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
                <td><?php echo htmlspecialchars($falta['disciplinas']); ?></td>
              </tr>
              <tr>
                <td>Datas de Reposição:</td>
                <td><?php echo htmlspecialchars(formatarData($falta['datas_reposicao'])); ?></td>
              </tr>
              <tr>
                <td>Horários:</td>
                <td><?php echo htmlspecialchars($falta['horarios_reposicao']); ?></td>
              </tr>
            </tbody>
          </table>
        </div>


        <div class="teacher-actions">
          <div class="status <?php echo strtolower(str_replace(' ', '-', $falta['situacao'])); ?>">
            <?php echo ucfirst(htmlspecialchars($falta['situacao'])); ?>
          </div>

          <?php if (strtolower($falta['situacao']) === 'indeferido'): ?>
          <div class="motivo-indeferimento">
            <strong>Motivo do Indeferimento:</strong>
            <?php echo htmlspecialchars($falta['motivo_indeferimento']); ?>
          </div>
          <div class="action-buttons">
            <button class="btn-editar" onclick="editarFalta(<?php echo $falta['idform_faltas']; ?>)">Editar
              Falta</button>
            <button class="btn-editar-reposicao"
              onclick="editarReposicao(<?php echo $falta['idform_reposicao']; ?>)">Editar
              Reposição</button>
          </div>
          <?php else: ?>
          <button class="btn-desativado" disabled>Não Editável</button>
          <?php endif; ?>
        </div>

        <div class="buttons-container">
          <button class="pdf-btn" onclick="generatePDF('<?php echo $falta['idform_reposicao']; ?>')">
            <i class="fas fa-file-pdf"></i>
          </button>
          <button class="details-btn" onclick="redirectToDetails('<?php echo $falta['idform_reposicao']; ?>')">Ver
            Detalhes</button>
        </div>
      </li>
      <?php endforeach; ?>
      <?php else: ?>
      <li class="no-data">Nenhuma falta registrada.</li>
      <?php endif; ?>
    </ul>
    <!-- Modal para exibir o PDF -->
    <div id="pdfModal" class="pdf-modal">
      <div class="pdf-modal-content">
        <span class="pdf-close" onclick="closePDFModal()">&times;</span>
        <iframe id="pdfIframe" src="" width="100%" height="100%" frameborder="0"></iframe>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script>
  function redirectToDetails(formId) {
    window.location.href = 'detalhes-historico.php?idform_reposicao=' + encodeURIComponent(formId);
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

  function abrirModal() {
    document.getElementById('filterModal').style.display = 'block';
  }

  function fecharModal() {
    document.getElementById('filterModal').style.display = 'none';
  }

  function editarFalta(id) {
    window.location.href = 'faltas.php?idform_faltas=' + id;
  }

  function editarReposicao(id) {
    window.location.href = 'reposicao.php?idform_reposicao=' + id;
  }

  window.onclick = function(event) {
    const modal = document.getElementById('filterModal');
    if (event.target === modal) {
      fecharModal();
    }
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