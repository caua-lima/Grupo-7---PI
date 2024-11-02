<!DOCTYPE html>

<html lang="pt-BR">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Plano de Reposições</title>
  <link rel="stylesheet" href="cssreposicao.css">

</head>

<body>
  <header>
    <img src="img/fatec.itapira.png" class="logo" alt="Logo Fatec Itapira">
    <h1 class="form-title">Formulário Justificativa de Reposição</h1>
    <img src="img/cps.png" class="cps-logo" alt="Logo Centro de Paula Souza">
    <nav class="menu">
      <a href="home.html">Início</a>
      <a href="faltas.php">Faltas</a>
      <a href="professor.php">Histórico</a>
    </nav>
  </header>

  <div>
    <label>Nome Completo: </label>
    <input type="text" id="nome-completo" placeholder="Seu nome aqui!">




    <legend>Cursos envolvidos na ausência:</legend>
    <label><input type="radio" name="curso" value="CST-DSM"> CST - DSM</label>
    <label><input type="radio" name="curso" value="CST-GE"> CST - GE</label>
    <label><input type="radio" name="curso" value="CST-GPI"> CST - GPI</label>
    <label><input type="radio" name="curso" value="CST-GTI"> CST - GTI</label>



    <legend>Turno(s):</legend>
    <label><input type="checkbox" name="turno" value="Manha"> Manhã</label>
    <label><input type="checkbox" name="turno" value="Noite"> Noite</label>



    <legend>Aulas não ministradas:</legend>
    <table border="1">
      <thead>
        <tr>
          <th>Data(as):</th>
          <th>Nº de aulas</th>
          <th>Nome da(s) Disciplina(s)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><input type="date"></td>
          <td><input type="number" min="1"></td>
          <td><input type="text"></td>
        </tr>
        <tr>
          <td><input type="date"></td>
          <td><input type="number" min="1"></td>
          <td><input type="text"></td>
        </tr>
        <tr>
          <td><input type="date"></td>
          <td><input type="number" min="1"></td>
          <td><input type="text"></td>
        </tr>
        <tr>
          <td><input type="date"></td>
          <td><input type="number" min="1"></td>
          <td><input type="text"></td>
        </tr>
      </tbody>
    </table>

  </div>

  <div>
    <fieldset>
      <legend>5) Dados da(s) aulas de reposição</legend>
      <table>
        <tr>
          <th>Ordem</th>
          <th>Data da Falta</th>
          <th>Data da Reposição</th>
          <th>Horário de Início e Término</th>
          <th>Disciplina(s) *</th>
        </tr>
        <tr>
          <td>01</td>
          <td><input type="date" id="datafalta" name="datafalta"></td>
          <td><input type="date" id="DNM1" name="DNM1"></td>
          <td><input type="time" id="horas"> às <input type="time" id="horas"></td>
          <td><input type="text" id="disciplinas"></td>
        </tr>
      </table>
    </fieldset>
  </div>

  <div>
    <label for="periodo">Selecione o Período:</label>
    <select id="periodo" onchange="mostrarTabela()">
      <option value="">--Selecione--</option>
      <option value="manha">Manhã</option>
      <option value="tarde">Tarde</option>
      <option value="noite">Noite</option>
    </select>
  </div>

  <div id="manha" class="tabela" style="display: none;">
    <fieldset>
      <legend>Manhã</legend>
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
  </div>

  <div id="tarde" class="tabela" style="display: none;">
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
        <tr>
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
  </div>

  <div id="noite" class="tabela" style="display: none;">
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
    </fieldset>
  </div>

  <footer class="footer">
    <div class="footer-container">
      <div class="footer-section">
        <h3>Links Úteis</h3>
        <ul>
          <li><a href="#">Sobre Nós</a></li>
          <li><a href="#">Fale Conosco</a></li>
        </ul>
      </div>
      <div class="footer-section">
        <h3>Redes Sociais</h3>
        <ul>
          <li><a href="#">Facebook</a></li>
          <li><a href="#">Instagram</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2024 Nome da Instituição. Todos os direitos reservados.</p>
    </div>
  </footer>

  <script>
  function mostrarTabela() {
    const periodoSelecionado = document.getElementById("periodo").value;
    const tabelas = document.getElementsByClassName("tabela");

    for (let i = 0; i < tabelas.length; i++) {
      tabelas[i].style.display = "none";
    }

    if (periodoSelecionado) {
      document.getElementById(periodoSelecionado).style.display = "block";
    }
  }
  </script>


</body>

</html>