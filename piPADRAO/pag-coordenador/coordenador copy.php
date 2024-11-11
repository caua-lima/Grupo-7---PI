<?php
include '../conexao.php';
include '../header.html';

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
    <h1 class="form-title">Formulário Justificativa de Faltas - Coordenação</h1>
    <img src="../img/cps.png" class="cps-logo" alt="Logo Centro de Paula Souza">
    <nav class="menu">
      <a href="home-coordenador.html">Início</a>
    </nav>
  </header>
  <div class="container">
    <h1>Lista de Professores Aguardando Aprovação</h1>
    <ul class="teacher-list">
      <li class="teacher">
        <div class="teacher-info">
          <h2>Prof. Júnior</h2>
          <p>Disciplina: Algoritmos e Lógica de Programação</p>
        </div>
        <div class="status waiting">À analisar</div>
        <button class="details-btn" onclick="redirectToDetails('Prof. Júnior')">Ver detalhes</button>
      </li>
      <li class="teacher">
        <div class="teacher-info">
          <h2>Prof. Wladimir</h2>
          <p>Disciplina: Modelagem de Banco de Dados</p>
        </div>
        <div class="status waiting">À analisar</div>
        <button class="details-btn" onclick="redirectToDetails('Prof. Wladimir')">Ver detalhes</button>
      </li>
      <li class="teacher">
        <div class="teacher-info">
          <h2>Prof. Ana Célia</h2>
          <p>Disciplina: Engenharia de Software</p>
        </div>
        <div class="status waiting">À analisar</div>
        <button class="details-btn" onclick="redirectToDetails('Prof. Ana Célia')">Ver detalhes</button>
      </li>
      <li class="teacher">
        <div class="teacher-info">
          <h2>Prof. Thiago</h2>
          <p>Disciplina: Desenvolvimento Web</p>
        </div>
        <div class="status deferido">Deferido</div>
      </li>
    </ul>
    <li class="teacher">
      <div class="teacher-info">
        <h2>Prof. Édison</h2>
        <p>Disciplina: Sistemas Operacionais</p>
      </div>
      <div class="status pending">Aguardando - Professor</div>
    </li>
  </div>
  <script>
  function redirectToDetails(professorName) {
    let url;
    if (professorName === 'Prof. Júnior') {
      url = 'detalhes-junior.html';
    } else if (professorName === 'Prof. Wladimir') {
      url = 'detalhes-wladimir.html';
    } else if (professorName === 'Prof. Ana Célia') {
      url = 'detalhes-ana-celia.html';
    } else if (professorName === 'Prof. Thiago') {
      url = 'detalhes-thiago.html';
    } else {
      url = 'detalhes-professor.html?professor=' + encodeURIComponent(professorName);
    }
    window.location.href = url;
  }
  </script>
  <footer class="footer">
    <div class="footer-container">
      <div class="footer-section">
        <h3>Contatos</h3>
        <ul>
          <li>Email: contato@fatecitapira.edu.br</li>
          <li>Telefone: (19) 1234-5678</li>
          <li>Endereço: Rua das Palmeiras, 123 - Itapira/SP</li>
        </ul>
      </div>
      <div class="footer-section">
        <h3>Links Úteis</h3>
        <ul>
          <li><a href="..//links-footer/privacidade.html">Política de Privacidade</a></li>
          <li><a href="../links-footer/termos.html">Termos de Uso</a></li>
          <li><a href="../links-footer/faq.html">FAQ</a></li>
        </ul>
      </div>
  </footer>
</body>

</html>