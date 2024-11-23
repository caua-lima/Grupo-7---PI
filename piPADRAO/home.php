<?php
include 'auth.php'; // Arquivo de autenticação e início da sessão

// Obtém o ID do funcionário logado da sessão
if (!isset($_SESSION['idfuncionario'])) {
  // Redireciona para o login caso a sessão não esteja configurada
  header("Location: index.php");
  exit;
}

$idfuncionario = $_SESSION['idfuncionario'];

// Incluir o arquivo de conexão com o banco de dados
include 'conexao.php'; // Este arquivo deve definir a variável $conn

// Variáveis para armazenar o número de formulários pendentes e indeferidos
$pendentes = 0;
$indeferidos = 0;

try {
  // Consulta para buscar formulários pendentes para o funcionário logado
  $stmtFormulariosPendentes = $conn->prepare("
        SELECT COUNT(*) 
        FROM formulario_faltas 
        WHERE situacao = 'Aguardando Reposição' AND idfuncionario = ?
    ");
  $stmtFormulariosPendentes->execute([$idfuncionario]);
  $pendentes = $stmtFormulariosPendentes->fetchColumn();

  // Consulta para buscar formulários indeferidos para o funcionário logado
  $stmtIndeferido = $conn->prepare("
        SELECT COUNT(*) 
        FROM formulario_reposicao 
        WHERE situacao = 'indeferido' AND idfuncionario = ?
    ");
  $stmtIndeferido->execute([$idfuncionario]);
  $indeferidos = $stmtIndeferido->fetchColumn();
} catch (PDOException $e) {
  echo "Erro ao buscar dados: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Padrao - Professor</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="icon" type="image/png" href="./img/favicon-32x32.png">

  <script src="https://kit.fontawesome.com/f3cc8687d6.js" crossorigin="anonymous"></script>
</head>

<body>
  <header>
    <!-- Início Cabeçalho -->
    <div class="containerh">
      <div class="first-columh">
        <a class="link-sp" href="#">
          <img src="img/logo-governo-do-estado-sp.png">
        </a>
      </div>
      <div class="second-columnh">
        <div class="social-media-sp">
          <ul class="list-social-media">
            <a href="#" class="link-social-media">
              <li class="item-social-media"><i class="fa-brands fa-flickr"></i></li>
            </a>
            <a href="#" class="link-social-media">
              <li class="item-social-media"><i class="fa-brands fa-linkedin-in"></i></li>
            </a>
            <a href="#" class="link-social-media">
              <li class="item-social-media"><i class="fa-brands fa-tiktok"></i></li>
            </a>
            <a href="#" class="link-social-media">
              <li class="item-social-media"><i class="fa-brands fa-youtube"></i></li>
            </a>
            <a href="#" class="link-social-media">
              <li class="item-social-media"><i class="fa-brands fa-x-twitter"></i></li>
            </a>
            <a href="#" class="link-social-media">
              <li class="item-social-media"><i class="fa-brands fa-instagram"></i></li>
            </a>
            <a href="#" class="link-social-media">
              <li class="item-social-media"><i class="fa-brands fa-facebook-f"></i></li>
            </a>
          </ul>
          <div class="gov">
            <p class="descricaoh">/governosp</p>
          </div>
        </div>
      </div>
    </div>
  </header>
  <!-- Fim cabeçalho -->

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

      <!-- Adicionado nome do funcionário -->
      <?php if (isset($_SESSION['nome'])): ?>
      <span class="bem-vindo-nome" style="margin: 0 10px; font-size: 16px; color: #333;">
        Bem-vindo(a) <br> Prof. <?php echo htmlspecialchars($_SESSION['nome']); ?>
      </span>
      <?php endif; ?>
      <a class="btn-sair" href="logout.php">
        <btn>Sair</btn>
      </a>
    </div>

  </div>

  <!-- Exibição da notificação se houver formulários pendentes -->
  <div class="container-geral">
    <div class="content">
      <a class="link-column" href="faltas.php">
        <div class="first-column">
          <ul class="list-window">
            <li class="item-window"><i class="fa-solid fa-clipboard-list"></i></li>
            <li class="desc-window">
              <p>FORMULÁRIO</p>
            </li>
            <li class="desc-window">
              <p>DE FALTAS</p>
            </li>
          </ul>
        </div>
      </a>
      <a class="link-column" href="verReposicao.php">
        <div class="second-column">
          <!-- Exibição da notificação se houver formulários pendentes -->
          <?php if ($pendentes > 0): ?>
          <div class="notificacao-pendentes">
            <p>Você tem <strong><?php echo $pendentes; ?> reposição(ões)</strong> para marcar!</p>
          </div>
          <?php endif; ?>
          <ul class="list-window">
            <li class="item-window"><i class="fa-regular fa-calendar-days"></i></li>
            <li class="desc-window">
              <p>REPOSIÇÃO</p>
            </li>
            <li class="desc-window">
              <p>DE AULAS</p>
            </li>
          </ul>
        </div>
      </a>

      <a class="link-column" href="professor.php">
        <div class="third-column">
          <!-- Exibição da notificação para formulários indeferidos -->
          <?php if ($indeferidos > 0): ?>
          <div class="notificacao-indeferidos">
            <p>Você tem <strong><?php echo $indeferidos; ?> reposição(ões)</strong> indeferido(s)!</p>
          </div>
          <?php endif; ?>
          <ul class="list-window">
            <li class="item-window"><i class="fa-regular fa-clock"></i></li>
            <li class="desc-window">
              <p>HISTÓRICO</p>
            </li>
          </ul>
        </div>
      </a>
    </div>
  </div>

  <footer>
    <div class="containerf">
      <a href="">
        <img src="img/logo-governo-do-estado-sp.png">
      </a>
    </div>
  </footer>
</body>

</html>