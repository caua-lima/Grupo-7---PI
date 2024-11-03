<?php
include 'index-coordenador.html';
// conexão com o banco de dados
require 'conexao.php';

// Função para buscar as ausências
function buscarAusencias($pdo) {
    try {
        // Consulta para obter as ausências
        $sql = "
            SELECT f.idform_faltas, f.datainicio, f.datafim, f.motivo_falta, a.nome_disciplina, p.nome AS funcionario_nome
FROM formulario_faltas f
JOIN formulario_faltas_cursos fc ON f.idform_faltas = fc.idform_faltas
JOIN cursos c ON fc.idcursos = c.idcursos
JOIN aulas_falta a ON a.idaulas_falta = fc.idform_faltas
JOIN funcionarios p ON p.idfuncionario = f.idfuncionario -- Altere aqui para a tabela correta
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        // Fetching results
        $ausencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $ausencias;

    } catch (PDOException $e) {
        echo "Erro ao buscar ausências: " . $e->getMessage();
    }
}

// Executando a função
$ausencias = buscarAusencias($conn);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Padrao - Coordenação</title>
<link rel="stylesheet" href="css/coordenador.css">
</head>

<body>
    <div class="container">
        <h1>Lista de Professores Aguardando Aprovação</h1>
        <ul>
<?php foreach ($ausencias as $falta): ?>
    <li class="teacher">
        <div class="teacher-info">
            <h2>Prof. <?= htmlspecialchars($falta['funcionario_nome']) ?></h2>
            <p>Disciplina: <?= htmlspecialchars($falta['disciplina']) ?></p>
            <p class="justification" style="display: none;"></p>
        </div>
        <div class="status waiting">Aguardando</div>
        <button class="details-btn" data-id="<?= $falta['idform_faltas'] ?>">Ver detalhes</button>
        <div class="action-buttons" style="display: none;">
            <textarea class="reason" placeholder="Motivo"></textarea>
            <button class="submit-reason-btn">Responder</button>
        </div>
    </li>
<?php endforeach; ?>
</ul>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const detailButtons = document.querySelectorAll('.details-btn');

            detailButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const teacherId = this.dataset.id;
                    fetch(`getDetails.php?idform_faltas=${teacherId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                alert(data.error);
                            } else {
                                const justification = this.parentNode.querySelector('.justification');
                                justification.style.display = 'block';
                                
                                justification.innerHTML = `
                                    <p><strong>Motivo da Falta:</strong> ${data[0].motivo_falta}</p>
                                    <p><strong>Data da Falta:</strong> ${data[0].datainicio} até ${data[0].datafim}</p>
                                    <p><strong>Curso(s) Envolvido(s):</strong> ${data.map(d => d.nome_curso).join(', ')}</p>
                                    <a href="${data[0].pdf_atestado}" target="_blank">Ver Atestado</a>
                                `;

                                const actionButtons = this.parentNode.querySelector('.action-buttons');
                                actionButtons.style.display = 'block';
                                
                                this.style.display = 'none';
                            }
                        })
                        .catch(error => console.error('Erro ao buscar detalhes:', error));
                });
            });
        });
    </script>
</body>

<footer>
    <div class="containerf">
        <a href="">
            <img src="img/logo-governo-do-estado-sp.png">
        </a>
    </div>
</footer>
</html>
