<?php
include 'conexao.php';
include 'index.html';

// ID do professor (fixo por enquanto)
$idProfessor = 1;

// Consulta para buscar as aulas semanais do professor
try {
  $stmtAulas = $conn->prepare("
        SELECT dia_semana, horario_inicio, horario_fim, disciplina
        FROM aulas_semanal_professor
        WHERE idfuncionario = ?
        ORDER BY FIELD(dia_semana, 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'), horario_inicio
    ");
  $stmtAulas->execute([$idProfessor]);
  $aulas = $stmtAulas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo "Erro ao buscar aulas: " . $e->getMessage();
}

// Consulta para buscar e calcular horas de HAE do professor
try {
  $stmtHAE = $conn->prepare("
        SELECT idhae, dia_semana, data_atividade, horario_inicio, horario_fim, tipo_atividade
        FROM horas_hae_professor
        WHERE idfuncionario = ?
        ORDER BY data_atividade
    ");
  $stmtHAE->execute([$idProfessor]);
  $atividadesHAE = $stmtHAE->fetchAll(PDO::FETCH_ASSOC);

  // Cálculo das horas usadas de HAE
  $totalHorasUsadas = 0;
  foreach ($atividadesHAE as $atividade) {
    $inicio = new DateTime($atividade['horario_inicio']);
    $fim = new DateTime($atividade['horario_fim']);
    $intervalo = $fim->diff($inicio);
    $horasUsadas = $intervalo->h + ($intervalo->i / 60); // Soma as horas e minutos convertidos em frações de hora
    $totalHorasUsadas += $horasUsadas;
  }

  // Define o total de horas HAE como 40 e calcula as horas restantes
  $totalHorasHAE = 40;
  $horasRestantes = max(0, $totalHorasHAE - $totalHorasUsadas);
} catch (PDOException $e) {
  echo "Erro ao buscar atividades de HAE: " . $e->getMessage();
}

// Consulta para buscar informações detalhadas dos formulários de faltas
try {
  $stmtFormularios = $conn->prepare("
        SELECT 
            f.idform_faltas, 
            f.datainicio, 
            f.datafim, 
            f.motivo_falta,
            f.situacao,
            f.pdf_atestado,
            GROUP_CONCAT(c.nome_curso SEPARATOR ', ') AS cursos
        FROM formulario_faltas f
        JOIN formulario_faltas_cursos fc ON f.idform_faltas = fc.idform_faltas
        JOIN cursos c ON fc.idcursos = c.idcursos
        WHERE f.situacao != 'Proposta Enviada'
        GROUP BY f.idform_faltas
    ");
  $stmtFormularios->execute();
  $formularios = $stmtFormularios->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo "Erro ao buscar formulários: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agenda e Atividades de HAE</title>
  <!-- Inclua um reset CSS para evitar conflitos de estilo -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
  <!-- CSS principal -->
  <link rel="stylesheet" href="./css/cssVerReposicao.css">
  <!-- FontAwesome para ícones -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <!-- Verifique se o caminho para o arquivo CSS está correto -->
  <style>
    /* Estilo embutido para testar rapidamente */
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Verdana, sans-serif;
      background-color: #f4f4f4;
    }
  </style>
</head>

<body>
  <div class="container">

    <div class="layout">
      <!-- Reposições Pendentes à esquerda -->
      <div class="reposicoes-pendentes">
        <h2>Reposições Pendentes</h2>
        <?php if (!empty($formularios)): ?>
          <?php foreach ($formularios as $formulario): ?>
            <div class="status-bar">
              <div class="reposicao">
                <div class="label">Reposição da(s) Falta(s) de:</div>
                <div class="data"><?php echo date('d \d\e F', strtotime($formulario['datainicio'])); ?></div>
              </div>
              <div class="status sent">
                <?php echo htmlspecialchars($formulario['situacao']); ?>
                <button class="ver" onclick="openModal(<?php echo $formulario['idform_faltas']; ?>)">Ver detalhes</button>
              </div>
            </div>

            <!-- Modal -->
            <div id="modal-<?php echo $formulario['idform_faltas']; ?>" class="modal">
              <div class="modal-content">
                <span class="close" onclick="closeModal(<?php echo $formulario['idform_faltas']; ?>)">&times;</span>
                <h2>Detalhes do Formulário</h2>
                <table class="modal-table">
                  <tr>
                    <th>Data Início</th>
                    <td><?php echo htmlspecialchars($formulario['datainicio']); ?></td>
                  </tr>
                  <tr>
                    <th>Data Fim</th>
                    <td><?php echo htmlspecialchars($formulario['datafim']); ?></td>
                  </tr>
                  <tr>
                    <th>Motivo da Falta</th>
                    <td><?php echo htmlspecialchars($formulario['motivo_falta']); ?></td>
                  </tr>
                  <tr>
                    <th>Cursos Envolvidos</th>
                    <td><?php echo htmlspecialchars($formulario['cursos']); ?></td>
                  </tr>
                  <tr>
                    <th>Situação</th>
                    <td><?php echo htmlspecialchars($formulario['situacao']); ?></td>
                  </tr>
                  <tr>
                    <th>PDF Enviado</th>
                    <td>
                      <a href="uploads/<?php echo htmlspecialchars($formulario['pdf_atestado']); ?>"
                        target="_blank">Visualizar PDF</a>
                    </td>
                  </tr>
                </table>
                <a href="reposicao.php?idform=<?php echo $formulario['idform_faltas']; ?>" class="details-button">Planejar
                  Reposição</a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>Nenhum formulário encontrado.</p>
        <?php endif; ?>
      </div>

      <!-- Formulário de Adição (Modal) -->
      <div id="addFormModal" class="modal">
        <div class="modal-content">
          <span class="close" onclick="hideAddForm()">&times;</span>
          <h3>Adicionar HAE</h3>
          <form id="addForm" method="post" action="adicionar_hae.php" onsubmit="return submitAddForm(event)">

            <!-- Primeira linha de inputs -->
            <div>
              <label for="add_dia_semana">Dia da Semana:</label>
              <input type="text" name="dia_semana" id="add_dia_semana" required>
            </div>
            <div>
              <label for="add_data_atividade">Data da Atividade:</label>
              <input type="date" name="data_atividade" id="add_data_atividade" required>
            </div>

            <!-- Segunda linha de inputs -->
            <div>
              <label for="add_horario_inicio">Horário de Início:</label>
              <input type="time" name="horario_inicio" id="add_horario_inicio" required>
            </div>
            <div>
              <label for="add_horario_fim">Horário de Fim:</label>
              <input type="time" name="horario_fim" id="add_horario_fim" required>
            </div>

            <!-- Linha única para o input de tipo de atividade -->
            <div style="grid-column: span 2;">
              <label for="add_tipo_atividade">Tipo de Atividade:</label>
              <input type="text" name="tipo_atividade" id="add_tipo_atividade" required>
            </div>

            <!-- Botões de ação -->
            <div style="grid-column: span 2; display: flex; justify-content: space-between;">
              <button type="submit">Adicionar Atividade</button>
              <button type="button" onclick="hideAddForm()">Cancelar</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Formulário de Edição (Modal) -->
      <div id="editFormModal" class="modal">
        <div class="modal-content">
          <span class="close" onclick="hideEditForm()">&times;</span>
          <h3>Editar Atividade de HAE</h3>
          <form id="editForm" method="post" action="atualizar_hae.php" onsubmit="return submitEditForm(event)">

            <input type="hidden" name="idhae" id="edit_idhae">

            <div>
              <label for="edit_dia_semana">Dia da Semana:</label>
              <input type="text" name="dia_semana" id="edit_dia_semana" required>
            </div>
            <div>
              <label for="edit_data_atividade">Data da Atividade:</label>
              <input type="date" name="data_atividade" id="edit_data_atividade" required>
            </div>
            <div>
              <label for="edit_horario_inicio">Horário de Início:</label>
              <input type="time" name="horario_inicio" id="edit_horario_inicio" required>
            </div>
            <div>
              <label for="edit_horario_fim">Horário de Fim:</label>
              <input type="time" name="horario_fim" id="edit_horario_fim" required>
            </div>
            <div style="grid-column: span 2;">
              <label for="edit_tipo_atividade">Tipo de Atividade:</label>
              <input type="text" name="tipo_atividade" id="edit_tipo_atividade" required>
            </div>

            <div style="grid-column: span 2; display: flex; justify-content: space-between;">
              <button type="submit">Salvar Alterações</button>
              <button type="button" onclick="hideEditForm()">Cancelar</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Agenda Semanal e Tabela de Atividades à direita -->
      <div class="atividades-e-agenda">
        <div class="agenda-semanal">
          <h2>Agenda Semanal do Professor</h2>
          <table class="styled-table">
            <tr>
              <th>Dia da Semana</th>
              <th>Horário de Início</th>
              <th>Horário de Fim</th>
              <th>Disciplina</th>
            </tr>
            <?php foreach ($aulas as $aula): ?>
              <tr>
                <td><?php echo htmlspecialchars($aula['dia_semana']); ?></td>
                <td><?php echo htmlspecialchars($aula['horario_inicio']); ?></td>
                <td><?php echo htmlspecialchars($aula['horario_fim']); ?></td>
                <td><?php echo htmlspecialchars($aula['disciplina']); ?></td>
              </tr>
            <?php endforeach; ?>
          </table>
        </div>

        <div class="tabela-atividades">

          <div class="hae-header">
            <h2>Atividades de HAE</h2>
            <button onclick="showAddForm()">Adicionar HAE</button>
          </div>

          <table class="styled-table">
            <tr>
              <th>Dia da Semana</th>
              <th>Data da Atividade</th>
              <th>Horário de Início</th>
              <th>Horário de Fim</th>
              <th>Tipo de Atividade</th>
              <th>Ações</th>
            </tr>
            <?php foreach ($atividadesHAE as $atividade): ?>
              <tr data-id="<?php echo $atividade['idhae']; ?>">
                <td><?php echo htmlspecialchars($atividade['dia_semana']); ?></td>
                <td><?php echo htmlspecialchars($atividade['data_atividade']); ?></td>
                <td><?php echo htmlspecialchars($atividade['horario_inicio']); ?></td>
                <td><?php echo htmlspecialchars($atividade['horario_fim']); ?></td>
                <td><?php echo htmlspecialchars($atividade['tipo_atividade']); ?></td>
                <td>
                  <button class="icon-button" onclick="showEditForm('<?php echo $atividade['idhae']; ?>')">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="icon-button delete" onclick="deleteHAE('<?php echo $atividade['idhae']; ?>')">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </table>
          <div class="container">
            <h2>Horas de HAE</h2>
            <p>Total de Horas de HAE: 40</p>
            <p>Horas de HAE Usadas: <?php echo number_format($totalHorasUsadas, 2); ?></p>
            <p>Horas de HAE Restantes: <?php echo number_format($horasRestantes, 2); ?></p>

          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function showAddForm() {
      document.getElementById('addFormModal').style.display = 'block';
    }

    function hideAddForm() {
      document.getElementById('addFormModal').style.display = 'none';
    }

    function showEditForm(id) {
      document.getElementById('editFormModal').style.display = 'block';
      var row = document.querySelector("tr[data-id='" + id + "']");
      document.getElementById('edit_idhae').value = id;
      document.getElementById('edit_dia_semana').value = row.cells[0].innerText;
      document.getElementById('edit_data_atividade').value = row.cells[1].innerText;
      document.getElementById('edit_horario_inicio').value = row.cells[2].innerText;
      document.getElementById('edit_horario_fim').value = row.cells[3].innerText;
      document.getElementById('edit_tipo_atividade').value = row.cells[4].innerText;
    }

    function hideEditForm() {
      document.getElementById('editFormModal').style.display = 'none';
    }

    function deleteHAE(id) {
      if (confirm('Tem certeza que deseja excluir esta atividade?')) {
        fetch('excluir_hae.php?idhae=' + id, {
            method: 'GET' // Ou 'POST' se necessário
          })
          .then(response => {
            if (response.ok) {
              alert('Atividade excluída com sucesso.');
              window.location.reload();
            } else {
              alert('Erro ao excluir a atividade.');
            }
          })
          .catch(error => {
            console.error('Erro:', error);
            alert('Ocorreu um erro ao processar sua solicitação.');
          });
      }
    }

    function openModal(id) {
      document.getElementById('modal-' + id).style.display = 'block';
    }

    function closeModal(id) {
      document.getElementById('modal-' + id).style.display = 'none';
    }

    window.onclick = function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
      }
    }

    // Função para enviar o formulário de edição e recarregar a página dinamicamente
    function submitEditForm(event) {
      event.preventDefault();

      const form = document.getElementById('editForm');
      const formData = new FormData(form);

      fetch(form.action, {
          method: 'POST',
          body: formData
        })
        .then(response => {
          if (response.ok) {
            hideEditForm();
            window.location.reload();
          } else {
            alert('Erro ao salvar as alterações.');
          }
        })
        .catch(error => {
          console.error('Erro:', error);
          alert('Ocorreu um erro ao processar sua solicitação.');
        });

      return false;
    }

    // Função para enviar o formulário de adição e recarregar a página dinamicamente
    function submitAddForm(event) {
      event.preventDefault();

      const form = document.getElementById('addForm');
      const formData = new FormData(form);

      fetch(form.action, {
          method: 'POST',
          body: formData
        })
        .then(response => {
          if (response.ok) {
            hideAddForm();
            window.location.reload();
          } else {
            alert('Erro ao adicionar a atividade.');
          }
        })
        .catch(error => {
          console.error('Erro:', error);
          alert('Ocorreu um erro ao processar sua solicitação.');
        });

      return false;
    }
  </script>
</body>

</html>