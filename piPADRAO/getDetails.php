<?php
include 'conexao.php';

if (isset($_GET['idform_faltas'])) {
    $id = intval($_GET['idform_faltas']);

    $sql = "SELECT f.motivo_falta, f.datainicio, f.datafim, f.pdf_atestado, c.nome_curso 
            FROM formulario_faltas f
            JOIN formulario_faltas_cursos fc ON f.idform_faltas = fc.idform_faltas
            JOIN cursos c ON fc.idcursos = c.idcursos
            WHERE f.idform_faltas = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($details) {
        echo json_encode($details);
    } else {
        echo json_encode(['error' => 'No details found']);
    }
} else {
    echo json_encode(['error' => 'ID not provided']);
}
?>
