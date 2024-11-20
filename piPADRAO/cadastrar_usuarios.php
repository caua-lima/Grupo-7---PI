<?php

session_start();
include 'conexao.php'; // Conexão com banco de dados

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $matricula = $_POST['matricula'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // CRIPTOGRAFIA A CONTA

    $sql = "INSERT INTO funcionarios (nome, email, matricula, funcao, regime_juridico, senha) 
    VALUES (?, ?, ?, 'Professor de Ensino Superior', 'CLT', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$nome, $email, $matricula, $senha]);

    echo "<script>alert('Usuário cadastrado com sucesso!');
    window.location.href = 'index.php';</script>";
}else{
    echo "Nem conectou";
}
?>