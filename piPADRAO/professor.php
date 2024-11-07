<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Status - Visualização do Professor</title>
<style>
    
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
}

header {
    background-color: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    width: 100%;
    box-sizing: border-box;
    margin-bottom: 20px;
    text-align: center; /* Centraliza o conteúdo do cabeçalho horizontalmente */
}
.menu a {
    text-decoration: none;
    color: #333;
    font-weight: bold;
    padding: 10px 20px;
    border-radius: 5px;
    transition: background-color 0.3s, color 0.3s;
}

.menu a:hover {
    background-color: #555;
    color: #fff;
}
.logo, .cps-logo {
        max-height: 80px; /* Ajusta a altura máxima dos logos */
    }

    .cps-logo {
        margin-left: auto;
        margin-right: 20px;
    }
    .form-title {
        text-align: center;
        color: #333;
    }
    .container {
    margin: 20px auto; /* Centraliza horizontalmente */
    text-align: center;
    background-color: white;
    margin-top: 20px;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    width: 80%; /* Largura da caixa principal */
    max-width: 600px; /* Largura máxima da caixa principal */
    }
    .container1, .container2, .container3 {
    margin: 20px auto; /* Centraliza horizontalmente */
    margin-top: 20px;
    text-align: center;
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    width: 80%; /* Largura da caixa principal */
    max-width: 600px; /* Largura máxima da caixa principal */
    }

    h1 {
        margin-bottom: 20px;
        color: #333;
    }

    .status-bar {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-top: 20px;
    }

    .status {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
        margin: 0 5px;
        border-radius: 4px;
        color: white;
        font-weight: bold;
        text-align: center;
        background-color: #007bff;
        width: 40%;
    }

    .sent {
        background-color: #007bff;
    }

    .analyzing {
        background-color: #ffc107;
    }

    .approved {
        background-color: #28a745;
    }

    .rejected {
        background-color: #dc3545;
    }

    .current {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
    }

    .details-button {
        margin-left: 50px;
        background-color: #333;
        color: white;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        border-radius: 4px;
    }

    .details-button:hover {
        background-color: #555;
    }
    .footer {
    background-color: #f0f0f0;
    padding: 30px 0;
    color: #333;
    font-size: 14px;
    text-align: center;
    position: relative;
    margin-top: 20px; /* Distância do footer ao botão enviar */
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
}

.footer-section {
    flex: 1 1 300px; /* Cresce, encolhe, base */
    margin-bottom: 20px;
}

.footer-section h3 {
    margin-bottom: 10px;
    color: #333; /* Cor do link padrão */
}

.footer-section ul {
    list-style-type: none;
    padding: 0;
}

.footer-section ul li {
    margin-bottom: 5px;
}

.footer-section ul li a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s;
}

.footer-section ul li a:hover {
    color: #007bff; /* Cor do link no hover */
}

.footer-bottom {
    background-color: #ddd;
    padding: 10px 0;
}

.footer-bottom p {
    margin: 0;
    font-size: 12px;
}

.reposicao{
    font-family: 'Segoe UI', Tahoma, Verdana, sans-serif;
    font-size: large;
    margin-right: 20px;
}
</style>
<script>
    function showDetails(status) {
        let message = '';
        switch (status) {
            case 'sent':
                message = "SUA SOLICITAÇÃO DE REPOSIÇÃO DE AULAS FOI ENVIADA E SERÁ ANALISADA EM BREVE!";
                break;
            case 'analyzing':
                message = 'SUA SOLICITAÇÃO DE REPOSIÇÃO DE AULAS ESTÁ SENDO ANALISADA PELA COORDENADORIA!';
                break;
            case 'approved':
                message = 'SUA SOLICITAÇÃO DE REPOSIÇÃO DE AULAS FOI AUTORIZADA!';
                break;
            case 'rejected':
                message = 'SUA SOLICITAÇÃO DE REPOSIÇÃO DE AULAS FOI NEGADA, POR FALTA DE DOCUMENTAÇÃO!';
                break;
        }
        alert(message);
    }
</script>
</head>
<body>
    <header>
        <img src="img/fatec.itapira.png" class="logo" alt="Logo Fatec Itapira">
        <h1 class="form-title">Status - Visualização do Professor</h1>
        <img src="img/cps.png" class="cps-logo" alt="Logo Centro de Paula Souza">
        <nav class="menu">
            <a href="home.html">Início</a>
            <a href="reposicao.html">Faltas</a>
            <a href="professor.html">Reposições</a>
        </nav>
    </header>
    <div class="container">
        <h1>Solicitação de Reposição de Aulas</h1>
        <div class="status-bar">
            <div class="reposicao">Reposição das faltas dia 26/04/24</div>
            <div class="status sent">Enviado
                <button class="details-button" onclick="showDetails('sent')">Ver detalhes</button>
            </div>
        </div>
        <div class="status-bar">
            <div class="reposicao">Reposição das faltas dia 22/02/24</div>
            <div class="status analyzing">Analisando
                <button class="details-button" onclick="showDetails('analyzing')">Ver detalhes</button>
            </div>
        </div>
        <div class="status-bar">
            <div class="reposicao">Reposição das faltas dia 19/11/23</div>
            <div class="status approved">Deferido
                <button class="details-button" onclick="showDetails('approved')">Ver detalhes</button>
            </div>
        </div>
        <div class="status-bar">
            <div class="reposicao">Reposição das faltas dia 07/10/34</div>
            <div class="status rejected">Indeferido
                <button class="details-button" onclick="showDetails('rejected')">Ver detalhes</button>
            </div>
        </div>
    </div>
    
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>Contatos</h3>
                <ul>
                    <li>Email: contato@fatecitapira.edu.br</li>
                    <li>Telefone: (19) 1234-5678</li>
                    <li>Endereço: Rua das Palmeiras, 123 - Itapira/SP</li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Links Úteis</h3>
                <ul>
                    <li><a href="links-footer/privacidade.html">Política de Privacidade</a></li>
                    <li><a href="links-footer/termos.html">Termos de Uso</a></li>
                    <li><a href="links-footer/faq.html">FAQ</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Fatec Itapira. Todos os direitos reservados.</p>
        </div>
    </footer>
</body>
</html>
