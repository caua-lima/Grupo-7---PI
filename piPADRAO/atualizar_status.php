<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $idform_reposicao = $_POST['idform_reposicao'];
  $status = $_POST['status'];
  $motivo_indeferimento = isset($_POST['motivo_indeferimento']) ? $_POST['motivo_indeferimento'] : '';

  try {
    $stmt = $conn->prepare("UPDATE formulario_reposicao SET situacao = ?, motivo_indeferimento = ? WHERE idform_reposicao = ?");
    $stmt->execute([$status, $motivo_indeferimento, $idform_reposicao]);

    echo "Status atualizado com sucesso!";
  } catch (PDOException $e) {
    echo "Erro ao atualizar o status: " . $e->getMessage();
  }
} else {
  echo "Método inválido.";
}