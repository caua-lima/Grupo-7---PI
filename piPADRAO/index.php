<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Login</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f1f1f1;
        }

        .login-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
            width: 300px;
            text-align: center;
        }

        .login-container h2 {
            margin-bottom: 20px;
        }

        .login-container input {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .login-container button {
            width: calc(100% - 20px);
            padding: 10px;
            background-color: #008CBA;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .login-container button:hover {
            background-color: #005f73;
        }
        .logar-professor {
            margin-bottom: 10px;
        }

    </style>
    <script>
        function login(role, redirectUrl) {
            var name = document.getElementById("name").value;
            var email = document.getElementById("email").value;
            var matricula = document.getElementById("matricula").value;
            var password = document.getElementById("password").value;

            if (name && email && matricula && password) {
                localStorage.setItem("userName", name);
                localStorage.setItem("userEmail", email);
                localStorage.setItem("userMatricula", matricula);
                localStorage.setItem("userRole", role);
                window.location.href = redirectUrl;
            } else {
                alert("Por favor, preencha todos os campos.");
            }
        }
    </script>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <input type="text" id="name" placeholder="Nome Completo">
        <input type="email" id="email" placeholder="Email Constitucional">
        <input type="text" id="matricula" placeholder="Número da Matrícula">
        <input type="password" id="password" placeholder="Senha">
        <button class="logar-professor" onclick="login('professor', 'home.php')">Logar como Professor</button>
        <button onclick="login('coordenador', 'pag-coordenador/home-coordenador.html')">Logar como Coordenador</button>
    </div>
</body>
</html>
