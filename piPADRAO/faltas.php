<?php

include 'conexao.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $arquivo = $_FILES['arquivo_pdf'];
    $motivo_falta = $_POST['motivo_falta'];
    $curso = $_POST['curso']; // Captura o curso selecionado

    if ($arquivo['error'] === UPLOAD_ERR_OK) {
        $nomeArquivo = uniqid() . "-" . basename($arquivo['name']);
        move_uploaded_file($arquivo['tmp_name'], "uploads/$nomeArquivo");

        // Insere os dados na tabela formulario_faltas
        $stmt = $conn->prepare("INSERT INTO formulario_faltas (datainicio, datafim, pdf_atestado, motivo_falta) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data_inicio, $data_fim, $nomeArquivo, $motivo_falta]);

        // Obtém o id_formulario gerado
        $id_formulario = $conn->lastInsertId();

        // Insere o relacionamento na tabela formulario_faltas_cursos
        $stmtCurso = $conn->prepare("INSERT INTO formulario_faltas_cursos (id_formulario, id_curso) VALUES (?, ?)");
        $stmtCurso->execute([$id_formulario, $curso]);

        // Redireciona para a página inicial após o processo
        header("Location: home.php");
        exit;
    }
}


?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulário de Faltas</title>

</head>

<body>
  <header>
    <img src="img/fatec.itapira.png" class="logo" alt="Logo Fatec Itapira">
    <h1 class="form-title">Formulário Justificativa de Faltas</h1>
    <img src="img/cps.png" class="cps-logo" alt="Logo Centro de Paula Souza">
    <nav class="menu">
      <a href="home.html">Início</a>
      <a href="reposicao.html">Reposições</a>
      <a href="professor.html">Histórico</a>
    </nav>
    <link rel="stylesheet" href="cssFaltas.css">
  </header>

  <form method="POST" enctype="multipart/form-data">
    <fieldset>
      <legend>Informações Pessoais</legend>
      <div class="input-row">
        <label for="nome">Nome:</label>
        <input type="text" class="nome" name="nome" required>
        <script>
        var name = localStorage.getItem("userName");
        if (name) {
          document.querySelector('.nome').value = name;
        }
        </script>
      </div>
      <div class="input-row">
        <label for="matricula">Matrícula:</label>
        <input type="text" class="matricula" name="matricula" id="matricula" readonly required>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
          var matricula = localStorage.getItem("userMatricula");
          if (matricula) {
            document.getElementById("matricula").value = matricula;
          }
        });
        </script>
        <label for="funcao">Função:</label>
        <input type="text" class="funcao" name="funcao" value="Professor de Ensino Superior" readonly required>
        <label for="regime">Regime Jurídico:</label>
        <input type="text" class="regime" name="regime" value="CLT" readonly required>
      </div>
    </fieldset>
    <div class="ausencia">
      <legend>Curso(s) Envolvido(s) na Ausência</legend>
      <label><input type="radio" class="CEA" name="curso" value="CST-DSM"> CST-DSM</label>
      <label><input type="radio" class="CEA" name="curso" value="CST-GE"> CST-GE</label>
      <label><input type="radio" class="CEA" name="curso" value="CST-GPI"> CST-GPI</label>
      <label><input type="radio" class="CEA" name="curso" value="CST-GTI"> CST-GTI</label>
      <label><input type="radio" class="CEA" name="curso" value="HAE"> HAE</label>
    </div>

    <fieldset class="faltas">
      <legend>Falta Referente</legend>
      <label for="data-falta">Falta referente ao dia:</label>
      <input type="date" class="data-falta" name="data_inicio">
      <label class="textfim" for="data-fim">Até:</label>
      <input type="date" class="data-fim small-input" name="data_fim">
    </fieldset>


    <fieldset class="motivo-falta">
      <legend>Motivo da Falta</legend>

      <label for="motivo">Selecione o motivo da falta:</label>
      <select id="motivo" name="motivo">
        <option value="" disabled selected>Selecione o motivo</option>
        <option value="licenca-falta-medica">Licença e Falta Médica</option>
        <option value="falta-injustificada">Falta Injustificada (Com desconto do DSR)</option>
        <option value="faltas-justificadas">Faltas Justificadas</option>
        <option value="faltas-previstas-legislacao">Faltas Previstas na Legislação Trabalhista</option>
      </select>


      <div id="opcoes_licenca-falta-medica" style="display: none;" class="motivo licenca-falta-medica">
        <label><input type="radio" class="LFM" name="motivo_falta" value="falta-medica"> Falta Médica (Atestado médico
          de 1 dia)</label>
        <label><input type="radio" class="LFM" name="motivo_falta" value="comparecimento-medico"> Comparecimento ao
          Médico no período das <input type="time" class="small-input"> às <input type="time"
            class="small-input"></label>
        <label><input type="radio" class="LFM" name="motivo_falta" value="licenca-saude"> Licença-Saúde (Atestado médico
          igual ou superior a 2 dias)</label>
        <label><input type="radio" class="LFM" name="motivo_falta" value="licenca-maternidade"> Licença-Maternidade
          (Atestado médico até 15 dias)</label>
      </div>

      <div id="opcoes_falta-injustificada" style="display: none;" class="motivo falta-injustificada">
        <label><input type="radio" class="FI" name="motivo_falta" value="falta-injustificada"> Falta</label>
        <label><input type="radio" class="LFM" name="motivo_falta" value="comparecimento-medico"> Atraso ou Saída
          Antecipada, das <input type="time" class="small-input"> às <input type="time" class="small-input"></label>
      </div>

      <div id="opcoes_faltas-justificadas" style="display: none;" class="motivo faltas-justificadas">
        <label><input type="radio" class="FJ" name="motivo_falta" value="falta-justificada"> Falta por motivo
          de:</label>
        <textarea name="motivo-descricao" rows="1"></textarea>
        <label><input type="radio" class="LFM" name="motivo_falta" value="comparecimento-medico"> Atraso ou Saída
          Antecipada das <input type="time" class="small-input"> às <input type="time" class="small-input"> Por motivo
          de:</label>
        <textarea name="atraso-descricao" rows="1"></textarea>
      </div>

      <div id="opcoes-faltas-previstas-legislacao" style="display: none;" class="motivo faltas-previstas-legislacao">
        <label><input type="radio" class="FPLT" name="motivo_falta" value="falecimento-conjuge"> Falecimento de cônjuge,
          pai, mãe, filho (9 dias consecutivos)</label>
        <label><input type="radio" class="FPLT" name="motivo_falta" value="falecimento-outros"> Falecimento de outros
          familiares (2 dias consecutivos)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="casamento"> Casamento (9 dias
          consecutivos)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="nascimento-filho"> Nascimento de filho (5
          dias)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="acompanhamento-esposa"> Acompanhar esposa ou
          companheira (Até 2 dias)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="acompanhamento-filho"> Acompanhar filho até 6
          anos (1 dia por ano)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="doacao-sangue"> Doação voluntária de sangue
          (1 dia em cada 12 meses)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="alistamento-eleitor"> Alistamento como
          eleitor (Até 2 dias)</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="depoimento-judicial"> Convocação para
          depoimento judicial</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="comparecimento-juri"> Comparecimento como
          jurado no Tribunal do Júri</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="servico-eleitoral"> Convocação para serviço
          eleitoral</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="composicao-mesa-eleitoral"> Dispensa para
          compor mesas eleitorais</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="prova-vestibular"> Realização de Prova de
          Vestibular</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="justica-trabalho"> Comparecimento como parte
          na Justiça do Trabalho</label>

        <label><input type="radio" class="FPLT" name="motivo_falta" value="acidente-transporte"> Atrasos devido a
          acidentes de transporte</label>
      </div>
      </div>
      <script>
      // Função para exibir as opções de acordo com a categoria dos motivos selecionada
      document.getElementById('motivo').addEventListener('change', function() {
        // Esconder as divs de opções
        document.getElementById('opcoes_licenca-falta-medica').style.display = 'none';
        document.getElementById('opcoes_falta-injustificada').style.display = 'none';
        document.getElementById('opcoes_faltas-justificadas').style.display = 'none';
        document.getElementById('opcoes-faltas-previstas-legislacao').style.display = 'none';

        // Mostrar a div correspondente à categoria dos motivos escolhida
        var categoriaSelecionada = this.value;
        if (categoriaSelecionada == 'licenca-falta-medica') {
          document.getElementById('opcoes_licenca-falta-medica').style.display = 'block';
        } else if (categoriaSelecionada == 'falta-injustificada') {
          document.getElementById('opcoes_falta-injustificada').style.display = 'block';
        } else if (categoriaSelecionada == 'faltas-justificadas') {
          document.getElementById('opcoes_faltas-justificadas').style.display = 'block';
        } else if (categoriaSelecionada == 'faltas-previstas-legislacao') {
          document.getElementById('opcoes-faltas-previstas-legislacao').style.display = 'block';
        }
      });
      </script>
    </fieldset>


    <div class="form-footer">
      <label for=""></label>
      <input type="file" class="form-control" name="arquivo_pdf" accept=".pdf" id="fileInput" required>
      <ul id="fileList"></ul>
    </div>

    <div class="form-footer">
      <button class="btn-enviar" type="submit">Enviar Formulário</button>
    </div>
  </form>

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