<?php
// Inclua a conexão ao banco de dados
include 'conexao.php';

// Obtenha o id do professor logado (supondo que o ID esteja na sessão)
//session_start();
$idfuncionario = 1; // $_SESSION['idfuncionario']; // Aqui estamos assumindo que você tem o ID do professor na sessão.

// Prepare a consulta para buscar os registros de faltas
$query = "
    SELECT f.idform_faltas, f.datainicio, f.datafim, f.motivo_falta, c.nome_curso 
    FROM formulario_faltas f
    JOIN formulario_faltas_cursos fc ON f.idform_faltas = fc.idform_faltas
    JOIN cursos c ON fc.idcursos = c.idcursos
    WHERE f.idfuncionario = :idfuncionario
    ORDER BY f.datainicio DESC
";

// Prepare e execute a consulta
$stmt = $conn->prepare($query);
$stmt->bindParam(':idfuncionario', $idfuncionario, PDO::PARAM_INT);
$stmt->execute();

// Fetch os resultados
$historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Histórico de Faltas - Professor</title>
  <link rel="stylesheet" href="css/professor.css">
</head>

<body>

  <header>
    <h1>Histórico de Justificativas de Faltas</h1>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="logout.php">Sair</a>
    </nav>
  </header>

  <main>
    <table>
      <thead>
        <tr>
          <th>Motivo da Falta</th>
          <th>Curso(s) Envolvido(s)</th>
          <th>Data Início</th>
          <th>Data Fim</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($historico) > 0): ?>
        <?php foreach ($historico as $registro): ?>
        <tr>
          <td><?php echo htmlspecialchars($registro['motivo_falta']); ?></td>
          <td><?php echo htmlspecialchars($registro['nome_curso']); ?></td>
          <td><?php echo htmlspecialchars($registro['datainicio']); ?></td>
          <td><?php echo htmlspecialchars($registro['datafim']); ?></td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr>
          <td colspan="4">Nenhuma falta registrada.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </main>

</body>

</html>