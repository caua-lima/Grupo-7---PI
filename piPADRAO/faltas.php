<?php
include 'conexao.php';
include 'index.html';

// Definir o ID do funcionário (fixo como 1 por enquanto)
$idFuncionario = 1;

try {
  // Consulta para buscar as informações do funcionário com base no ID
  $stmtFuncionario = $conn->prepare("SELECT nome, matricula, funcao, regime_juridico FROM funcionarios WHERE idfuncionario = ?");
  $stmtFuncionario->execute([$idFuncionario]);
  $funcionario = $stmtFuncionario->fetch(PDO::FETCH_ASSOC);

  if (!$funcionario) {
    throw new Exception("Funcionário não encontrado.");
  }

  // Consulta para buscar as disciplinas que o funcionário ministra
  $stmtDisciplinas = $conn->prepare("SELECT DISTINCT disciplina FROM aulas_semanal_professor WHERE idfuncionario = ?");
  $stmtDisciplinas->execute([$idFuncionario]);
  $disciplinas = $stmtDisciplinas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Erro ao buscar informações do funcionário: " . $e->getMessage());
} catch (Exception $e) {
  die($e->getMessage());
}

// Passar os dados das disciplinas para o JavaScript
$disciplinasJson = json_encode($disciplinas);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tipo_falta = $_POST['tipo_falta'] ?? null;
  $data_unica = $_POST['data_unica'] ?? null;
  $data_inicio_periodo = $_POST['data_inicio_periodo'] ?? null;
  $data_fim_periodo = $_POST['data_fim_periodo'] ?? null;
  $num_aulas = $_POST['num_aulas'] ?? null;
  $motivo_falta = $_POST['motivo_falta'] ?? null;
  $cursosSelecionados = $_POST['curso'] ?? []; // Captura os cursos selecionados como um array
  $situacao = $_POST['situacao'] ?? 'Aguardando Reposição';

  try {
    // Processar o arquivo PDF
    $arquivo = $_FILES['arquivo_pdf'];
    if ($arquivo['error'] === UPLOAD_ERR_OK) {
      $nomeArquivo = uniqid() . "-" . basename($arquivo['name']);
      $destinoArquivo = "uploads/$nomeArquivo";
      if (!move_uploaded_file($arquivo['tmp_name'], $destinoArquivo)) {
        throw new Exception("Erro ao fazer o upload do arquivo.");
      }

      // Insere os dados na tabela formulario_faltas
      $stmt = $conn->prepare("INSERT INTO formulario_faltas (idfuncionario, datainicio, datafim, pdf_atestado, motivo_falta, situacao) VALUES (?, ?, ?, ?, ?, ?)");
      $stmt->execute([$idFuncionario, $data_unica ?? $data_inicio_periodo, $data_unica ?? $data_fim_periodo, $nomeArquivo, $motivo_falta, $situacao]);

      // Obtém o ID do formulário gerado
      $id_formulario = $conn->lastInsertId();

      // Insere os cursos selecionados na tabela formulario_faltas_cursos
      $stmtCurso = $conn->prepare("INSERT INTO formulario_faltas_cursos (idform_faltas, idcursos) VALUES (?, ?)");
      foreach ($cursosSelecionados as $curso) {
        $stmtCurso->execute([$id_formulario, $curso]);

        // Captura as disciplinas selecionadas para cada curso
        if (isset($_POST["disciplinas_curso_$curso"])) {
          $disciplinasSelecionadas = $_POST["disciplinas_curso_$curso"];

          // Insere as disciplinas na tabela aulas_falta
          $stmtAulaFalta = $conn->prepare("INSERT INTO aulas_falta (idform_faltas, num_aulas, data_aula, nome_disciplina) VALUES (?, ?, ?, ?)");
          foreach ($disciplinasSelecionadas as $disciplina) {
            if ($tipo_falta === 'unica' && $data_unica) {
              $stmtAulaFalta->execute([$id_formulario, $num_aulas, $data_unica, $disciplina]);
            } elseif ($tipo_falta === 'periodo' && $data_inicio_periodo && $data_fim_periodo) {
              $dataAtual = new DateTime($data_inicio_periodo);
              $dataFim = new DateTime($data_fim_periodo);
              while ($dataAtual <= $dataFim) {
                $stmtAulaFalta->execute([$id_formulario, $num_aulas, $dataAtual->format('Y-m-d'), $disciplina]);
                $dataAtual->modify('+1 day');
              }
            }
          }
        }
      }

      // Redireciona para a página inicial após o processo
      header("Location: home.php");
      exit;
    } else {
      throw new Exception("Erro no upload do arquivo: código de erro " . $arquivo['error']);
    }
  } catch (PDOException $e) {
    die("Erro ao inserir dados no banco de dados: " . $e->getMessage());
  } catch (Exception $e) {
    die($e->getMessage());
  }
}
?>



<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulário de Faltas</title>
  <link rel="stylesheet" href="./css/cssFaltas.css">

</head>

<body>


  <form method="POST" enctype="multipart/form-data">
    <fieldset>
      <legend>Informações Pessoais</legend>
      <div class="input-row">
        <label for="nome">Nome:</label>
        <input type="text" class="nome" name="nome" value="<?php echo htmlspecialchars($funcionario['nome']); ?>"
          readonly required>
      </div>
      <div class="input-row">
        <label for="matricula">Matrícula:</label>
        <input type="text" class="matricula" name="matricula"
          value="<?php echo htmlspecialchars($funcionario['matricula']); ?>" readonly required>
        <label for="funcao">Função:</label>
        <input type="text" class="funcao" name="funcao" value="<?php echo htmlspecialchars($funcionario['funcao']); ?>"
          readonly required>
        <label for="regime">Regime Jurídico:</label>
        <input type="text" class="regime" name="regime"
          value="<?php echo htmlspecialchars($funcionario['regime_juridico']); ?>" readonly required>
      </div>
    </fieldset>

    <fieldset class="ausencia">
      <legend>Curso(s) Envolvido(s) na Ausência</legend>
      <label><input type="checkbox" class="CEA" name="curso[]" value="1" onclick="gerarSelecaoDisciplinas()">
        DSM</label>
      <label><input type="checkbox" class="CEA" name="curso[]" value="2" onclick="gerarSelecaoDisciplinas()"> GE</label>
      <label><input type="checkbox" class="CEA" name="curso[]" value="3" onclick="gerarSelecaoDisciplinas()">
        GPI</label>
      <label><input type="checkbox" class="CEA" name="curso[]" value="4" onclick="gerarSelecaoDisciplinas()">
        GTI</label>
      <label><input type="checkbox" class="CEA" name="curso[]" value="5" onclick="gerarSelecaoDisciplinas()">
        HAE</label>
    </fieldset>

    <fieldset class="faltas">
      <legend>Falta Referente</legend>
      <label>
        <input type="radio" name="tipo_falta" value="unica" checked onclick="togglePeriodo(false)"> Falta referente ao
        dia:
      </label>
      <input type="date" class="data-falta" name="data_unica" id="data_unica">

      <label>
        <input type="radio" name="tipo_falta" value="periodo" onclick="togglePeriodo(true)"> Período de
      </label>
      <input type="number" class="num-dias" name="num_dias" id="num_dias" min="1" placeholder="Nº de dias">
      <label for="data-inicio-periodo">dias: </label>
      <input type="date" class="data-inicio-periodo" name="data_inicio_periodo" id="data_inicio_periodo">
      <label for="data-fim-periodo">até</label>
      <input type="date" class="data-fim-periodo" name="data_fim_periodo" id="data_fim_periodo" readonly>

      <!-- Campo para número de aulas -->
      <label for="num_aulas">Número de Aulas:</label>
      <input type="number" name="num_aulas" id="num_aulas" min="2" max="4" required placeholder="2 - 4">

      <!-- Container para seleção de disciplinas -->
      <div id="disciplinas-container"></div>
    </fieldset>

    <script>
      const disciplinas = <?php echo $disciplinasJson; ?>; // Passa as disciplinas do PHP para o JavaScript

      function togglePeriodo(isPeriodo) {
        document.getElementById('data_unica').disabled = isPeriodo;
        document.getElementById('num_dias').disabled = !isPeriodo;
        document.getElementById('data_inicio_periodo').disabled = !isPeriodo;
        document.getElementById('data_fim_periodo').disabled = !isPeriodo;

        if (!isPeriodo) {
          document.getElementById('num_dias').value = '';
          document.getElementById('data_inicio_periodo').value = '';
          document.getElementById('data_fim_periodo').value = '';
        }
      }

      document.getElementById('data_inicio_periodo').addEventListener('change', function() {
        const numDias = parseInt(document.getElementById('num_dias').value, 10);
        if (numDias && this.value) {
          const dataInicio = new Date(this.value);
          dataInicio.setDate(dataInicio.getDate() + numDias - 1);
          const dataFim = dataInicio.toISOString().split('T')[0];
          document.getElementById('data_fim_periodo').value = dataFim;
        }
      });

      function gerarSelecaoDisciplinas() {
        const cursosSelecionados = document.querySelectorAll('input[name="curso[]"]:checked');
        const container = document.getElementById('disciplinas-container');
        container.innerHTML = ''; // Limpa o container antes de adicionar novas seleções

        if (cursosSelecionados.length === 0) {
          container.innerHTML = '<p>Selecione ao menos um curso.</p>';
          return;
        }

        cursosSelecionados.forEach((curso, index) => {
          const label = document.createElement('label');
          label.textContent = `Disciplina do curso ${curso.nextSibling.textContent.trim()}:`;
          label.htmlFor = `disciplina-${curso.value}`;

          const select = document.createElement('select');
          select.name = `disciplinas_curso_${curso.value}[]`; // Nome ajustado para capturar como array por curso
          select.id = `disciplina-${curso.value}`;
          select.multiple = true; // Permite múltipla seleção
          select.required = true;

          // Adiciona opções ao select a partir da variável disciplinas
          disciplinas.forEach(disciplina => {
            const option = document.createElement('option');
            option.value = disciplina.disciplina;
            option.textContent = disciplina.disciplina;
            select.appendChild(option);
          });

          container.appendChild(label);
          container.appendChild(select);
        });
      }
    </script>
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
    <input type="hidden" name="situacao" value="Aguardando Reposição">

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