<?php
include 'conexao.php';
include 'auth.php';

// Verifica se o funcionário está logado
if (!isset($_SESSION['idfuncionario'])) {
  header("Location: index.php");
  exit;
}

$idfuncionario = $_SESSION['idfuncionario'];
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Padrao - Coordenador</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="icon" type="image/png" href="./img/favicon-32x32.png">

  <script src="https://kit.fontawesome.com/f3cc8687d6.js" crossorigin="anonymous"></script>
</head>

<body>
  <header>
    <!-- Inicio Cabeçalho -->
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
        Bem-vindo(a)</br>Coord. <?php echo htmlspecialchars($_SESSION['nome']); ?>
      </span>
      <?php endif; ?>

      <a class="btn-sair" href="logout.php">
        <btn>Sair</btn>
      </a>
    </div>
  </div>

  <div class="container-geral">
    <div class="content">
      <a class="link-column" href="coordenador.php">
        <div class="first-column">
          <ul class="list-window">
            <li class="item-window"><i class="fa-solid fa-magnifying-glass"></i></li>
            <li class="desc-window">
              <p>ANÁLISAR</p>
            </li>
            <li class="desc-window">
              <p>SOLICITAÇÕES</p>
            </li>
          </ul>
        </div>
      </a>
      <a class="link-column" href="historico-coordenador.php">
        <div class="third-column">
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