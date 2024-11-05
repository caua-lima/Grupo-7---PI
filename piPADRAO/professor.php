<?php
include 'conexao.php';
include 'index.html';
?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Status - Visualização do Professor</title>
  <link rel="stylesheet" href="css/cssProfessor.css">
  <script>
    function showDetails(status) {
      let message = '';
      switch (status) {
        case 'sent':
          message = "SUA SOLICITAÇÃO DE REPOSIÇÃO DE AULAS FOI ENVIADA E SERÁ ANALISADA EM BREVE!";
          break;
        case 'analyzing':
          message = 'SUA SOLICITAÇÃO DE REPOSIÇÃO DE AULAS ESTÁ SENDO ANALISADA PELA COORDENADORIA!';
          break;
        case 'approved':
          message = 'SUA SOLICITAÇÃO DE REPOSIÇÃO DE AULAS FOI AUTORIZADA!';
          break;
        case 'rejected':
          message = 'SUA SOLICITAÇÃO DE REPOSIÇÃO DE AULAS FOI NEGADA, POR FALTA DE DOCUMENTAÇÃO!';
          break;
      }
      alert(message);
    }
  </script>
</head>

<body>

  <div class="container">
    <h1>Solicitação de Reposição de Aulas</h1>
    <div class="status-bar">
      <div class="reposicao">Reposição das faltas dia 26/04/24</div>
      <div class="status sent">Enviado
        <button class="details-button" onclick="showDetails('sent')">Ver detalhes</button>
      </div>
    </div>
    <div class="status-bar">
      <div class="reposicao">Reposição das faltas dia 22/02/24</div>
      <div class="status analyzing">Analisando
        <button class="details-button" onclick="showDetails('analyzing')">Ver detalhes</button>
      </div>
    </div>
    <div class="status-bar">
      <div class="reposicao">Reposição das faltas dia 19/11/23</div>
      <div class="status approved">Deferido
        <button class="details-button" onclick="showDetails('approved')">Ver detalhes</button>
      </div>
    </div>
    <div class="status-bar">
      <div class="reposicao">Reposição das faltas dia 07/10/34</div>
      <div class="status rejected">Indeferido
        <button class="details-button" onclick="showDetails('rejected')">Ver detalhes</button>
      </div>
    </div>
  </div>

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
          <li><a href="links-footer/privacidade.html">Política de Privacidade</a></li>
          <li><a href="links-footer/termos.html">Termos de Uso</a></li>
          <li><a href="links-footer/faq.html">FAQ</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2024 Fatec Itapira. Todos os direitos reservados.</p>
    </div>
  </footer>
</body>

</html>