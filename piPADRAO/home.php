<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Home</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            font-family: Arial, sans-serif;
        }

        .container {
            display: flex;
            height: 100%;
        }

        .left-section {
            background-color: white; /* cor da parte esquerda */
            width: 65%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            position: relative;
            padding: 20px;
        }

        .header {
            width: 100%;
            text-align: center;
            padding: 10px;
            background-color: white; /* cor do cabeçalho */
            margin-bottom: 20px;
        }

        .logos {
            width: 100%;
            display: flex;
            justify-content: space-between;
            position: absolute;
            bottom: 20px;
            padding: 0 20px;
        }

        .right-section {
            background-color: #ffcb9b; /* cor da parte direita */
            width: 35%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 15px;
            position: relative;
        }

        .button {
            padding: 10px 20px;
            background-color: white;
            color: black;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 26px;
            text-decoration: none;
            text-align: center;
        }

        .button2 {
            padding: 10px 42px;
            background-color: white;
            color: black;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 26px;
            text-decoration: none;
            text-align: center;
        }

        .button3{
            padding: 10px 82px;
            background-color: white;
            color: black;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 26px;
            text-decoration: none;
            text-align: center;
        }

        .button:hover {
            background-color: #fda756;
        }

        .button2:hover {
            background-color: #fda756;
        }

        .button3:hover {
            background-color: #fda756;
        }

        .logout-button {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: red;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .fatec-logo {
            max-height: 100px;
        }

        .cps-logo {
            max-height: 80px;
            margin-right: 50px;
        }

        .boneco {
            width: 700px;
            align-self: center; /* Centraliza horizontalmente */
            margin-top: auto; /* Centraliza verticalmente */
            margin-bottom: auto; /* Centraliza verticalmente */
        }

        .padrao {
            color: #333;
            font-size: 50px;
            font-family:'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
        }

        .significado-padrao {
            color: #333;
            font-size: 25px;
            margin-top: -30px;
            font-family:'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
        }

        .welcome {
            font-size: 20px;
            color: #333;
            margin-top: 20px;
        }

        .popup {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #333;
            color: #ADD8E6;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 16px;
            z-index: 1000;
            display: none;
        }

        .confirm-dialog {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: none;
            z-index: 1000;
        }

        .confirm-dialog button {
            margin: 5px;
        }

    </style>
    <script>
        function displayWelcomeMessage() {
            var name = localStorage.getItem("userName");
            if (name) {
                var welcomeMessage = document.getElementById("welcomeMessage");
                welcomeMessage.innerText = "Bem-vindo ao PADRAO, Professor " + name;
                welcomeMessage.style.display = "block";
            }
        }

        function showConfirmDialog() {
            document.getElementById('confirmDialog').style.display = 'block';
        }

        function hideConfirmDialog() {
            document.getElementById('confirmDialog').style.display = 'none';
        }

        function logout() {
            localStorage.removeItem("userName");
            window.location.href = "login.html";
        }

        window.onload = function() {
            displayWelcomeMessage();
        }
    </script>
</head>
<body>
    <div class="popup" id="welcomeMessage"></div>
    <div class="confirm-dialog" id="confirmDialog">
        <p>Tem certeza que deseja sair?</p>
        <button onclick="logout()">Sim</button>
        <button onclick="hideConfirmDialog()">Não</button>
    </div>
    <div class="container">
        <div class="left-section">
            <div class="header">
                <h1 class="padrao">PADRAO</h1>
                <h2 class="significado-padrao">( Programa para Ausências Docentes e Reposições de Aulas Oficiais )</h2>
            </div>
            <div class="boneco">
                <img class="boneco" src="img/boneco.png" alt="Boneco Aulas Agendadas">
            </div>
            <div class="logos">
                <img class="fatec-logo" src="img/fatec.itapira.png" alt="Fatec Itapira">
                <img class="cps-logo" src="img/cps.png" alt="Logo Centro de Paula Souza">
            </div>
        </div>
        <div class="right-section">
            <button class="logout-button" onclick="showConfirmDialog()">Sair</button>
            <a href="faltas.php" class="button2">Justificar Faltas</a>
            <a href="reposicao.html" class="button">Planejar Reposição</a>
            <a href="professor.html" class="button3">Histórico</a>
        </div>
    </div>
</body>
</html>
