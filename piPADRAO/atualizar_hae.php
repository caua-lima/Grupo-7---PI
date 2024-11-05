<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $idhae = $_POST['idhae'];
  $dia_semana = $_POST['dia_semana'];
  $data_atividade = $_POST['data_atividade'];
  $horario_inicio = $_POST['horario_inicio'];
  $horario_fim = $_POST['horario_fim'];
  $tipo_atividade = $_POST['tipo_atividade'];

  try {
    $stmt = $conn->prepare("
            UPDATE horas_hae_professor
            SET dia_semana = ?, data_atividade = ?, horario_inicio = ?, horario_fim = ?, tipo_atividade = ?
            WHERE idhae = ?
        ");
    $stmt->execute([$dia_semana, $data_atividade, $horario_inicio, $horario_fim, $tipo_atividade, $idhae]);
    echo "Atividade de HAE atualizada com sucesso!";
  } catch (PDOException $e) {
    echo "Erro ao atualizar a atividade de HAE: " . $e->getMessage();
  }
}
