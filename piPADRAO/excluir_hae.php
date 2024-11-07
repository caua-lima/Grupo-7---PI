<?php
include 'conexao.php';

if (isset($_GET['idhae'])) {
  $idhae = $_GET['idhae'];

  try {
    $stmt = $conn->prepare("DELETE FROM horas_hae_professor WHERE idhae = ?");
    $stmt->execute([$idhae]);
    header("Location: index.php");
  } catch (PDOException $e) {
    echo "Erro ao excluir a atividade de HAE: " . $e->getMessage();
  }
}