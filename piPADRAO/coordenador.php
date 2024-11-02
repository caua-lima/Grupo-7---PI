<?php

include 'index.html';

// Toda alteração no cabeçalho e footer devem ser modificadas no index.html

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Padrao - Coordenação</title>
<link rel="stylesheet" href="css/coordenador.css">
</head>

<!-- Fim do Head -->

<body>
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

<!-- Fim do HTML no body -->

<!-- Início do JS -->

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

<!-- Fim do JS -->

</body>
</html>
