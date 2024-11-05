<!DOCTYPE html>

<html lang="pt-BR">

<head>

  <meta charset="UTF-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Plano de Reposições</title>

  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      height: 100%;
      width: 100%;
    }

    header {
      background-color: #f0f0f0;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 10px;
      width: 100%;
      box-sizing: border-box;
      margin-bottom: 20px;
      text-align: center;
      /* Centraliza o conteúdo do cabeçalho horizontalmente */
    }

    .logo,
    .cps-logo {
      max-height: 80px;
      /* Ajusta a altura máxima dos logos */
    }

    .cps-logo {
      margin-left: auto;
      margin-right: 20px;
    }

    .form-title {
      text-align: center;
      color: #333;
    }

    .container {
      max-width: 8000px;
      margin: 0 auto;
    }

    fieldset {
      border: 1px solid #ccc;
      padding: 10px 40px;
      margin-bottom: 10px;
    }

    legend {
      font-weight: bold;
    }

    label {
      display: inline;
      margin-top: 10px;
      margin: 5px 0;
    }

    .small-input {
      width: auto;
      display: inline-block;
    }

    .section-title {
      margin-top: 20px;
      font-weight: bold;
    }

    .form-footer {
      text-align: center;
      margin-top: 20px;
    }

    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      line-height: normal;
    }

    h1 {
      text-align: center;
    }

    input[type="date"] input[type="time"] {
      width: auto;
      padding: 5px;
      margin-bottom: 5px;
      box-sizing: content-box;
    }

    input[type="text"] {
      width: 50%;
      padding: 5px;
      margin-bottom: 5px;
      margin-top: 5px;
      box-sizing: content-box;
    }

    input[type="checkbox"] {
      margin-right: 10px;
    }

    input[type="number"] {
      width: 40px;
    }

    table,
    th,
    td {
      margin-bottom: 20px;
      border: 2px solid #000;
      border-collapse: collapse;
      padding: 10px;
      text-align: center;
      margin-top: 10px;
    }

    .signature {
      display: block;
      justify-content: space-between;
      align-items: center;
      margin-top: 20px;
    }

    .menu {
      display: flex;
      gap: 20px;
      align-items: center;
    }

    .menu a {
      text-decoration: none;
      color: #333;
      font-weight: bold;
      padding: 10px 20px;
      border-radius: 5px;
      transition: background-color 0.3s, color 0.3s;
    }

    .menu a:hover {
      background-color: #555;
      color: #fff;
    }

    #disciplinas {
      width: 300px;
    }

    .btn-enviar {
      text-decoration: none;
      color: #fff;
      background-color: #007bff;
      /* Cor de fundo azul */
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      transition: background-color 0.3s, color 0.3s;
      cursor: pointer;
      bottom: 20px;
      /* Distância do rodapé */
      z-index: 999;
      /* Garante que o botão fique acima de todos os outros elementos */
    }

    .btn-enviar:hover {
      background-color: #0056b3;
      /* Cor de fundo azul mais escura no hover */
    }

    .footer {
      background-color: #f0f0f0;
      padding: 30px 0;
      color: #333;
      font-size: 14px;
      text-align: center;
      position: relative;
      margin-top: 20px;
      /* Distância do footer ao botão enviar */
    }

    .footer-container {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
    }

    .footer-section {
      flex: 1 1 300px;
      /* Cresce, encolhe, base */
      margin-bottom: 20px;
    }

    .footer-section h3 {
      margin-bottom: 10px;
      color: #333;
      /* Cor do link padrão */
    }

    .footer-section ul {
      list-style-type: none;
      padding: 0;
    }

    .footer-section ul li {
      margin-bottom: 5px;
    }

    .footer-section ul li a {
      color: #333;
      text-decoration: none;
      transition: color 0.3s;
    }

    .footer-section ul li a:hover {
      color: #007bff;
      /* Cor do link no hover */
    }

    .footer-bottom {
      background-color: #ddd;
      padding: 10px 0;
    }

    .footer-bottom p {
      margin: 0;
      font-size: 12px;
    }
  </style>

</head>

<body>
  <header>
    <img src="img/fatec.itapira.png" class="logo" alt="Logo Fatec Itapira">
    <h1 class="form-title">Formulário Justificativa de Reposição</h1>
    <img src="img/cps.png" class="cps-logo" alt="Logo Centro de Paula Souza">
    <nav class="menu">
      <a href="home.html">Início</a>
      <a href="faltas.html">Faltas</a>
      <a href="professor.html">Histórico</a>
    </nav>
  </header>

  <div>
    <fieldset>
      <label>Número: </label><input type="text" id="numero" value="2506/2024" readonly>
      <label for="reposicoes-mes">Reposições mês: </label><input type="month" id="reposicoes-mes"
        placeholder="________________/2024">
    </fieldset>
  </div>
  <fieldset>
    <label>Nome do Professor: </label>
    <input type="text" id="nome-professor" value="">
  </fieldset>
  <fieldset>
    <legend>1) Curso</legend>
    <label><input type="checkbox" class="Curso" id="CST-DSM"> CST-DSM</label>
    <label><input type="checkbox" class="Curso" id="CST-GE"> CST-GE</label>
    <label><input type="checkbox" class="Curso" id="CST-GPI"> CST-GPI</label>
    <label><input type="checkbox" class="Curso" id="CST-GTI"> CST-GTI</label>
    <label><input type="checkbox" class="Curso" id="HAE"> HAE</label>
  </fieldset>
  <script>
    function ensureOnlyOneChecked(event) {
      var checkboxes = document.querySelectorAll('.Curso');
      checkboxes.forEach(function(checkbox) {
        if (checkbox !== event.target) {
          checkbox.checked = false;
        }
      });
    }
    document.querySelectorAll('.Curso').forEach(function(checkbox) {
      checkbox.addEventListener('change', ensureOnlyOneChecked);
    });
  </script>
  <fieldset>
    <legend>2) Turno</legend>
    <label><input type="checkbox" class="Turno" id="Manha"> Manhã</label>
    <label><input type="checkbox" class="Turno" id="Tarde"> Tarde</label>
    <label><input type="checkbox" class="Turno" id="Noite"> Noite</label>
  </fieldset>
  <script>
    function ensureOnlyOneChecked(event) {
      var checkboxes = document.querySelectorAll('.Turno');
      checkboxes.forEach(function(checkbox) {
        if (checkbox !== event.target) {
          checkbox.checked = false;
        }
      });
    }
    document.querySelectorAll('.Turno').forEach(function(checkbox) {
      checkbox.addEventListener('change', ensureOnlyOneChecked);
    });
  </script>
  <fieldset>
    <legend>3) Reposicao em virtude de:</legend>
    <label><input type="checkbox" class="RVD"> Claro Docente</label>
    <label><input type="checkbox" class="RVD"> Falta</label>
    <label><input type="checkbox" class="RVD"> Substituição</label>
  </fieldset>
  <script>
    function ensureOnlyOneChecked(event) {
      var checkboxes = document.querySelectorAll('.RVD');

      checkboxes.forEach(function(checkbox) {
        if (checkbox !== event.target) {
          checkbox.checked = false;
        }
      });
    }
    document.querySelectorAll('.RVD').forEach(function(checkbox) {
      checkbox.addEventListener('change', ensureOnlyOneChecked);
    });
  </script>
  <div>
    <fieldset>
      <legend>4) Dados da(s) aulas não ministradas</legend>
      <table>
        <tr>
          <th>Ordem</th>
          <th>Data da(s) aula(s) não ministrada(s)</th>
          <th>Nº de aulas</th>
          <th>Nome da(s) Disciplina(s)</th>
        </tr>
        <tr>
          <td>01</td>
          <td><input type="date" id="DNM1" name="DNM1"></td>
          <td><input type="number" id="n-aulas"></td>
          <td><input type="text" id="disciplinas"></td>
        </tr>
        <tr>
          <td>02</td>
          <td><input type="date" id="DNM1" name="DNM1"></td>
          <td><input type="number" id="n-aulas"></td>
          <td><input type="text" id="disciplinas"></td>
        </tr>
        <tr>
          <td>03</td>
          <td><input type="date" id="DNM1" name="DNM1"></td>
          <td><input type="number" id="n-aulas"></td>
          <td><input type="text" id="disciplinas"></td>
        </tr>
        <tr>
          <td>04</td>
          <td><input type="date" id="DNM1" name="DNM1"></td>
          <td><input type="number" id="n-aulas"></td>
          <td><input type="text" id="disciplinas"></td>
        </tr>
      </table>
    </fieldset>
  </div>
  <div>

    <fieldset>
      <legend>5) Dados da(s) aulas de reposição</legend>
      <table>
        <tr>
          <th>Ordem</th>
          <th>Data da Reposição</th>
          <th>Horário de Início e Término</th>
          <th>Disciplina(s) *</th>

        </tr>
        <tr>
          <td>01</td>
          <td><input type="date" id="DNM1" name="DNM1"></td>
          <td><input type="time" id="horas"> as <input type="time" id="horas"></td>
          <td><input type="text" id="disciplinas"></td>

        </tr>
        <tr>
          <td>02</td>
          <td><input type="date" id="DNM1" name="DNM1"></td>
          <td><input type="time" id="horas"> as <input type="time" id="horas"></td>
          <td><input type="text" id="disciplinas"></td>

        </tr>
        <tr>
          <td>03</td>
          <td><input type="date" id="DNM1" name="DNM1"></td>
          <td><input type="time" id="horas"> as <input type="time" id="horas"></td>
          <td><input type="text" id="disciplinas"></td>

        </tr>
        <tr>
          <td>04</td>
          <td><input type="date" id="DNM1" name="DNM1"></td>
          <td><input type="time" id="horas"> as <input type="time" id="horas"></td>
          <td><input type="text" id="disciplinas"></td>

        </tr>
      </table>
    </fieldset>
  </div>
  <div>
    <fieldset>
      <legend>6) Entregue na Coordenação em:</legend>
      <input type="date">
    </fieldset>
  </div>
  <div>

    <fieldset>
      <legend>7) Confirmo a(s) reposição(ões) da(s) disciplinas e encaminho à Diretoria Administrativa para as
        providências cabíveis.</legend>
      <h2></h2>
      <label>Itapira: <input type="date"></label>
      <div class="signature">
      </div>
    </fieldset>

  </div>
  <div>
    <fieldset>
      <legend>8) Represente no quadro: ( X ) as Aulas, ( HAE ) Hora Atividade Específica e ( R ) Reposição proposta.
      </legend>

      <fieldset>
        <legend>Manha</legend>
        <table>

          <tr>
            <th>HAE-RJI-JORNADA</th>
            <th>HORA-AULA</th>
            <th>SEGUNDA</th>
            <th>TERÇA</th>
            <th>QUARTA</th>
            <th>QUINTA</th>
            <th>SEXTA</th>
            <th>SÁBADO</th>
          </tr>
          <tr>
            <td>-</td>
            <td>07h40min<BR>-<BR>08h30min</td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
          </tr>
          <tr>
            <td>08h00 - 09h00</td>
            <td> 08h30min<BR>-<BR>09h20min </td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
          </tr>
          <tr>
            <td>09h00 - 10h00</td>
            <td>09h20min<BR>-<BR>10h10min</td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
          </tr>
          <tr>
            <td>10h00 - 11h00</td>
            <td>10h10min<BR>-<BR>11h00min</td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
          </tr>
          <tr>
            <td>11h00 - 12h00</td>
            <td>11h00min<BR>-<BR>12h00min</td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
          </tr>
          <tr>
            <td>12h00 - 13h00</td>
            <td>12h00min<BR>-<BR>12h50min</td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
          </tr>
        </table>
      </fieldset>
      <fieldset>
        <legend>Tarde</legend>
        <table>
          <tr>
            <th>HAE-RJI-JORNADA</th>
            <th>HORA-AULA</th>
            <th>SEGUNDA</th>
            <th>TERÇA</th>
            <th>QUARTA</th>
            <th>QUINTA</th>
            <th>SEXTA</th>
            <th>SÁBADO</th>
          </tr>
          </tr>
          <tr>
            <td>13h - 14h</td>
            <td>13h00min<BR>-<BR>13h50min</td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
          </tr>
          <tr>
            <td>14h - 15h</td>
            <td>13h50min<BR>-<BR>14h40min</td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
          </tr>
          <tr>
            <td>15h - 16h</td>
            <td>14h50min<BR>-<BR>15h40min</td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
          </tr>
          <tr>
            <td>16h - 17h</td>
            <td>15h40min<BR>-<BR>16h30min</td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
          </tr>
          <tr>
            <td>17h - 18h</td>
            <td>16h30min<BR>-<BR>17h30min</td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
          </tr>
          <td>-</td>
          <td>17h30min<BR>-<BR>18h20min</td>
          <td><input type="checkbox" id="TABELA"></td>
          <td><input type="checkbox" id="TABELA"></td>
          <td><input type="checkbox" id="TABELA"></td>
          <td><input type="checkbox" id="TABELA"></td>
          <td><input type="checkbox" id="TABELA"></td>
          <td><input type="checkbox" id="TABELA"></td>
          </tr>
        </table>
      </fieldset>
      <fieldset>
        <legend>Noite</legend>
        <table>
          <tr>
            <th>HAE-RJI-JORNADA</th>
            <th>HORA-AULA</th>
            <th>SEGUNDA</th>
            <th>TERÇA</th>
            <th>QUARTA</th>
            <th>QUINTA</th>
            <th>SEXTA</th>
            <th>SÁBADO</th>
          </tr>
          <tr>
            <td>18h - 19h</td>
            <td>18h10min<BR>-<BR>19h00min</td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
          </tr>
          <tr>
            <td>19h - 20h</td>
            <td>19h00min<BR>-<BR>19h50min</td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
          </tr>
          <tr>
            <td>20h - 21h</td>
            <td>19h50min<BR>-<BR>20h40min</td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
          </tr>
          <tr>
            <td>21h - 22h</td>
            <td>20h50min<BR>-<BR>21h40min</td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
          </tr>
          <tr>
            <td>-</td>
            <td>21h40min<BR>-<BR>22h30min</td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
            <td><input type="checkbox" id="TABELA"></td>
          </tr>
        </table>
        <p>Observe as exigências legais: máximo 8 horas diárias de trabalho, intervalo de 1 hora entre um expediente e
          outro e 6 horas em cada expediente.</p>
      </fieldset>
    </fieldset>
  </div>
  </fieldset>
  <div class="form-footer">
    <a href="professor.html" class="btn-enviar" type="submit">Enviar Formulário</button></a>
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

  </form>
  </div>
</body>

</html>