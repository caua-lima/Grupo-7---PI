<?php
session_start();
require 'conexao.php'; // Arquivo onde está a conexão PDO

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    if (!$email || !$senha) {
        $_SESSION['erro_login'] = 'Preencha todos os campos.';
        header('Location: index.php');
        exit;
    }

    try {
        // Verificando se o email existe no banco de dados
        $stmt = $conn->prepare("
            SELECT idfuncionario, nome, matricula, funcao, regime_juridico, senha 
            FROM funcionarios WHERE email = :email
        ");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Validando as credenciais
        if ($funcionario && password_verify($senha, $funcionario['senha'])) {
            // Armazenando informações na sessão
            $_SESSION['idfuncionario'] = $funcionario['idfuncionario'];
            $_SESSION['nome'] = $funcionario['nome'];
            $_SESSION['funcao'] = $funcionario['funcao'];

            // Redirecionando com base no nível de acesso
            if ($funcionario['funcao'] === 'COORDENADOR') {
                header('Location: home_coordenador.php');
            } elseif ($funcionario['funcao'] === 'Professor de Ensino Superior') {
                header('Location: home.php');
            } else {
                header('Location: erro.php');
            }
            exit;
        } else {
            // Credenciais inválidas
            $_SESSION['erro_login'] = 'Email ou senha inválidos.';
            header('Location: index.php');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['erro_login'] = 'Erro no sistema: ' . $e->getMessage();
        header('Location: index.php');
        exit;
    }
} else {
    header('Location: erro.php');
    exit;
}