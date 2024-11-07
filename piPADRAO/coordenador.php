<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Coordenação</title>
<style>
    body {
    font-family: Arial, sans-serif;
    background-color: #f5f5f5;
    margin: 0;
}

header {
    background-color: #ccc;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    width: 100%;
    box-sizing: border-box;
    margin-bottom: 20px;
    text-align: center; /* Centraliza o conteúdo do cabeçalho horizontalmente */
}

.container {
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    width: 80%;
    max-width: 600px;
    text-align: center;
    margin: 0 auto; /* Centraliza o container horizontalmente */
}

    h1 {
        margin-bottom: 20px;
        color: #333;
    }

    .teacher-list {
        list-style-type: none;
        padding: 0;
    }

    .teacher {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #ccc;
        padding: 10px 0;
    }

    .teacher-info {
        flex-grow: 1;
        text-align: left;
    }

    .teacher-info h2 {
        margin: 0;
    }

    .status {
        padding: 5px 10px;
        border-radius: 4px;
        font-weight: bold;
        text-align: center;
    }

    .status.waiting {
        background-color: #ffc107; /* Amarelo para "Aguardando" */
        color: #333; /* Cor do texto */
    }

    .logo, .cps-logo {
        max-height: 80px; /* Ajusta a altura máxima dos logos */
    }

    .cps-logo {
        margin-left: auto;
        margin-right: 20px;
    }
    .details-btn {
    background-color: #007bff; /* Cor de fundo azul */
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.details-btn:hover {
    background-color: #0056b3; /* Cor de fundo azul mais escura ao passar o mouse */
}

.reason {
    display: none;
    width: 100%;
    margin-top: 10px;
    padding: 5px;
    box-sizing: border-box;
}

.submit-reason-btn {
    display: none;
    background-color: #28a745; /* Verde */
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 10px;
    transition: background-color 0.3s ease;
}

.submit-reason-btn:hover {
    background-color: #218838; /* Verde mais escuro ao passar o mouse */
}

.action-buttons {
    margin-top: 10px;
}
</style>
</head>
<body>
    <header>
        <img src="img/fatec.itapira.png" class="logo" alt="Logo Fatec Itapira">
        <h1 class="form-title">Formulário Justificativa de Faltas - Coordenação</h1>
        <img src="img/cps.png" class="cps-logo" alt="Logo Centro de Paula Souza" >
    </header>
    <div class="container">
        <h1>Lista de Professores Aguardando Aprovação</h1>
        <ul class="teacher-list">
            <li class="teacher">
                <div class="teacher-info">
                    <h2>Prof. Júnior</h2>
                    <p>Disciplina: Algoritmos e Lógica de Programação</p>
                    <p class="justification" style="display: none;">Justificativa: O professor Júnior está ausente devido a problemas de saúde.</p>
                </div>
                <div class="status waiting">Aguardando</div>
                <button class="details-btn">Ver detalhes</button>
                <div class="action-buttons" style="display: none;">
                    <textarea class="reason" placeholder="Motivo"></textarea>
                    <button class="submit-reason-btn">Responder</button>
                </div>
            </li>
            <li class="teacher">
                <div class="teacher-info">
                    <h2>Prof. Wladimir</h2>
                    <p>Disciplina: Modelagem de Banco de Dados</p>
                    <p class="justification" style="display: none;">Justificativa: O professor Wladimir está ausente por motivos pessoais.</p>
                </div>
                <div class="status waiting">Aguardando</div>
                <button class="details-btn">Ver detalhes</button>
                <div class="action-buttons" style="display: none;">
                    <textarea class="reason" placeholder="Motivo"></textarea>
                    <button class="submit-reason-btn">Responder</button>
                </div>
            </li>
            <li class="teacher">
                <div class="teacher-info">
                    <h2>Prof. Ana Célia</h2>
                    <p>Disciplina: Engenharia de Software</p>
                    <p class="justification" style="display: none;">Justificativa: A professora Ana Célia está ausente devido a um compromisso inadiável.</p>
                </div>
                <div class="status waiting">Aguardando</div>
                <button class="details-btn">Ver detalhes</button>
                <div class="action-buttons" style="display: none;">
                    <textarea class="reason" placeholder="Motivo"></textarea>
                    <button class="submit-reason-btn">Responder</button>
                </div>
            </li>
        </ul>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const detailButtons = document.querySelectorAll('.details-btn');
        
        detailButtons.forEach(button => {
            button.addEventListener('click', function() {
                const justification = this.parentNode.querySelector('.justification');
    
                // Mostrar a justificativa da falta
                justification.style.display = 'block';
    
                // Mostrar os botões de ação
                const actionButtons = this.parentNode.querySelector('.action-buttons');
                actionButtons.style.display = 'block';
    
                // Esconder o botão "Ver detalhes"
                this.style.display = 'none';
            });
        });
    
        const submitReasonButtons = document.querySelectorAll('.submit-reason-btn');
    
        submitReasonButtons.forEach(button => {
            button.addEventListener('click', function() {
                const reasonTextarea = this.parentNode.querySelector('.reason');
                const reason = reasonTextarea.value.trim();
                const teacherInfo = this.parentNode.parentNode.querySelector('.teacher-info');
                const professorName = teacherInfo.querySelector('h2').textContent;
                const discipline = teacherInfo.querySelector('p').textContent;
    
                if (reason === '') {
                    alert('Por favor, forneça um motivo para deferir ou indeferir.');
                } else {
                    const actionButtons = this.parentNode;
                    actionButtons.innerHTML = `<button class="approve-btn">Deferir</button>
                                               <button class="reject-btn">Indeferir</button>`;
    
                    const approveButton = actionButtons.querySelector('.approve-btn');
                    const rejectButton = actionButtons.querySelector('.reject-btn');
    
                    approveButton.addEventListener('click', function() {
                        alert(`Professor ${professorName} aprovado para ministrar ${discipline}.`);
                    });
    
                    rejectButton.addEventListener('click', function() {
                        alert(`Professor ${professorName} indeferido para ministrar ${discipline}.\nMotivo: ${reason}`);
                    });
                }
            });
        });
    });
    </script>
</body>
</html>
