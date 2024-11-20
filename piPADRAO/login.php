<?php
session_start();
require 'conexao.php'; // Arquivo onde está a conexão PDO

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Verificando se o email existe no banco de dados
    $stmt = $conn->prepare("SELECT idfuncionario, nome, matricula, funcao, regime_juridico, senha
    FROM funcionarios WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($funcionario && password_verify($senha, $funcionario['senha'])) {
        // Login bem-sucedido, armazenando informações na sessão
        $_SESSION['id_funcionario'] = $funcionario['idfuncionario'];
        $_SESSION['nome_funcionario'] = $funcionario['nome'];
        $_SESSION['matricula_funcionario'] = $funcionario['matricula'];
        $_SESSION['funcao_funcionario'] = $funcionario['funcao'];
        $_SESSION['regime_juridico'] = $funcionario['regime_juridico'];

        // Redirecionar com base no nível de acesso
        if ($funcionario['funcao'] == 'COORDENADOR') {
            header('Location: home_coordenador.php');
        } elseif ($funcionario['funcao'] == 'Professor de Ensino Superior') {
            header('Location: home.php');
        } else {
            header('Location: erro.php');
        }
        exit;
    } else {
        // Falha no login, redireciona de volta para o login
        $_SESSION['erro_login'] = 'Email ou senha inválidos.';
        echo "<script>alert('Credenciais Inválidas!');
        window.location.href = 'index.php';</script>";
        exit;
    }
} else {
    header('Location: erro.php');
    exit;
}