<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $idProfessor = 1; // ID fixo
  $dia_semana = $_POST['dia_semana'];
  $data_atividade = $_POST['data_atividade'];
  $horario_inicio = $_POST['horario_inicio'];
  $horario_fim = $_POST['horario_fim'];
  $tipo_atividade = $_POST['tipo_atividade'];

  try {
    $stmt = $conn->prepare("
            INSERT INTO horas_hae_professor (idfuncionario, dia_semana, data_atividade, horario_inicio, horario_fim, tipo_atividade, hae_total, hae_usadas)
            VALUES (?, ?, ?, ?, ?, ?, 40, 0)
        ");
    $stmt->execute([$idProfessor, $dia_semana, $data_atividade, $horario_inicio, $horario_fim, $tipo_atividade]);
    header("Location: index.php");
  } catch (PDOException $e) {
    echo "Erro ao adicionar a atividade de HAE: " . $e->getMessage();
  }
}
