<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

require_once('tcpdf/tcpdf.php');
include 'conexao.php';

// Verifica se o ID do formulário de reposição foi fornecido
if (!isset($_GET['idform_reposicao'])) {
  exit;
}

$idform_reposicao = $_GET['idform_reposicao'];

try {
  // Seu código existente para obter os dados ($reposicaoInfo, $aulas, etc.)
  // ...
  // Consulta para obter as informações de reposição
  $stmtReposicao = $conn->prepare("
 SELECT 
     fr.data_entrega,
     fr.situacao,
     fr.virtude,
     fr.motivo_indeferimento,
     f.idform_faltas,
     f.datainicio,
     f.datafim,
     f.motivo_falta,
     f.idfuncionario,
     func.nome AS nome_professor,
     MAX(af.num_aulas) AS num_aulas,
     GROUP_CONCAT(DISTINCT ar.data_reposicao ORDER BY ar.data_reposicao SEPARATOR ', ') AS datas_reposicao,
     GROUP_CONCAT(DISTINCT ar.nome_disciplina ORDER BY ar.nome_disciplina SEPARATOR ', ') AS disciplinas,
     GROUP_CONCAT(DISTINCT CONCAT(ar.horarioinicio, ' às ', ar.horariofim) ORDER BY ar.horarioinicio SEPARATOR ', ') AS horarios,
     f.pdf_atestado,
     GROUP_CONCAT(DISTINCT c.nome_curso ORDER BY c.nome_curso SEPARATOR ', ') AS cursos
 FROM formulario_reposicao fr
 JOIN formulario_faltas f ON fr.idform_faltas = f.idform_faltas
 JOIN funcionarios func ON f.idfuncionario = func.idfuncionario
 LEFT JOIN aulas_reposicao ar ON fr.idform_reposicao = ar.idform_reposicao
 LEFT JOIN aulas_falta af ON f.idform_faltas = af.idform_faltas
 LEFT JOIN formulario_faltas_cursos fc ON f.idform_faltas = fc.idform_faltas
 LEFT JOIN cursos c ON fc.idcursos = c.idcursos
 WHERE fr.idform_reposicao = ?
 GROUP BY fr.data_entrega, fr.situacao, fr.virtude, fr.motivo_indeferimento, f.idform_faltas, f.datainicio, f.datafim, f.motivo_falta, f.idfuncionario, func.nome, f.pdf_atestado
");
  $stmtReposicao->execute([$idform_reposicao]);
  $reposicaoInfo = $stmtReposicao->fetch(PDO::FETCH_ASSOC);

  if (!$reposicaoInfo) {
    // Não exibe mensagem de erro para evitar saída antes do PDF
    exit;
  }

  // Obter o idfuncionario do professor
  $professorId = $reposicaoInfo['idfuncionario'];

  // Mapear dias da semana em inglês para português
  $diasSemanaMap = [
    'Monday' => 'SEGUNDA',
    'Tuesday' => 'TERÇA',
    'Wednesday' => 'QUARTA',
    'Thursday' => 'QUINTA',
    'Friday' => 'SEXTA',
    'Saturday' => 'SÁBADO',
    'Sunday' => 'DOMINGO',
  ];

  // Definir o array $diasSemana
  $diasSemana = ['SEGUNDA', 'TERÇA', 'QUARTA', 'QUINTA', 'SEXTA', 'SÁBADO'];

  // Consultas para buscar informações de aulas e reposições
  $stmtAulas = $conn->prepare("
 SELECT dia_semana, horario_inicio, horario_fim, disciplina
 FROM aulas_semanal_professor
 WHERE idfuncionario = ?
 ORDER BY FIELD(dia_semana, 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'), horario_inicio
");
  $stmtAulas->execute([$professorId]);
  $aulas = $stmtAulas->fetchAll(PDO::FETCH_ASSOC);

  $stmtHAE = $conn->prepare("
 SELECT idhae, dia_semana, data_atividade, horario_inicio, horario_fim, tipo_atividade, hae_total, hae_usadas
 FROM horas_hae_professor
 WHERE idfuncionario = ?
");
  $stmtHAE->execute([$professorId]);
  $atividadesHAE = $stmtHAE->fetchAll(PDO::FETCH_ASSOC);

  $stmtReposicaoAulas = $conn->prepare("
 SELECT data_reposicao, nome_disciplina, horarioinicio, horariofim
 FROM aulas_reposicao
 WHERE idform_reposicao = ?
");
  $stmtReposicaoAulas->execute([$idform_reposicao]);
  $aulasReposicao = $stmtReposicaoAulas->fetchAll(PDO::FETCH_ASSOC);

  // Obter todos os horários únicos de aulas, HAE e reposições com formatação de hora
  $horariosUnicos = [];

  foreach (array_merge($aulas, $atividadesHAE, $aulasReposicao) as $evento) {
    // Verifica e formata os horários corretamente
    $horarioInicioFormatado = date('H:i', strtotime($evento['horario_inicio'] ?? $evento['horarioinicio']));
    $horarioFimFormatado = date('H:i', strtotime($evento['horario_fim'] ?? $evento['horariofim']));
    $horariosUnicos[] = $horarioInicioFormatado . ' - ' . $horarioFimFormatado;
  }

  // Remove horários duplicados e ordena
  $horariosUnicos = array_unique($horariosUnicos);
  sort($horariosUnicos); // Ordena os horários para exibição

} catch (PDOException $e) {
  exit;
}

function formatarData($data)
{
  $meses = [
    'January' => 'janeiro',
    'February' => 'fevereiro',
    'March' => 'março',
    'April' => 'abril',
    'May' => 'maio',
    'June' => 'junho',
    'July' => 'julho',
    'August' => 'agosto',
    'September' => 'setembro',
    'October' => 'outubro',
    'November' => 'novembro',
    'December' => 'dezembro'
  ];

  $dataFormatada = date('d de F de Y', strtotime($data));
  return strtr($dataFormatada, $meses);
}

// Cria um novo documento PDF com orientação paisagem
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

// Define informações do documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sua Instituição');
$pdf->SetTitle('Detalhes da Reposição de Aulas');
$pdf->SetSubject('Reposição de Aulas');

// Define margens
$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// Define quebra automática de página
$pdf->SetAutoPageBreak(TRUE, 25);

// Define a fonte com tamanho menor
$pdf->SetFont('helvetica', '', 10);

// Adiciona uma página
$pdf->AddPage();

// Ajusta a proporção da altura das células
$pdf->setCellHeightRatio(0.9);

// Desabilita quebras de página automáticas antes da tabela
$auto_page_break = $pdf->getAutoPageBreak();
$pdf->SetAutoPageBreak(false, 0);

// Inclui estilos CSS no conteúdo HTML
$html = '<style>
    h1, h2 {
        color: #007bff;
        text-align: center;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    table th, table td {
        border: 1px solid #000;
        padding: 4px;
        font-size: 9px;
        text-align: center;
    }
    table th {
        background-color: #f2f2f2;
    }
</style>';

// Seu conteúdo HTML existente
$html .= '<h1>Justificativa da Falta</h1>
<!-- Resto do conteúdo -->
';

// Cria o conteúdo HTML
$html = '<h1>Justificativa da Falta</h1>
<table cellpadding="5">
    <tr>
        <td><strong>Professor:</strong></td>
        <td>' . htmlspecialchars($reposicaoInfo['nome_professor']) . '</td>
    </tr>
    <tr>
        <td><strong>Motivo da Falta:</strong></td>
        <td>' . htmlspecialchars($reposicaoInfo['motivo_falta']) . '</td>
    </tr>
    <tr>
        <td><strong>Data:</strong></td>
        <td>' . htmlspecialchars(formatarData($reposicaoInfo['datainicio'])) . '</td>
    </tr>';

if ($reposicaoInfo['datainicio'] !== $reposicaoInfo['datafim']) {
  $html .= '<tr>
        <td><strong>Data de Fim:</strong></td>
        <td>' . htmlspecialchars(formatarData($reposicaoInfo['datafim'])) . '</td>
    </tr>';
}

$html .= '<tr>
        <td><strong>Número de Aulas:</strong></td>
        <td>' . htmlspecialchars($reposicaoInfo['num_aulas']) . '</td>
    </tr>
    <tr>
        <td><strong>Cursos:</strong></td>
        <td>' . htmlspecialchars($reposicaoInfo['cursos']) . '</td>
    </tr>
</table>

<h2>Detalhes da Reposição de Aulas</h2>
<table cellpadding="5">
    <tr>
        <td><strong>Data de Entrega:</strong></td>
        <td>' . htmlspecialchars(formatarData($reposicaoInfo['data_entrega'])) . '</td>
    </tr>
    <tr>
        <td><strong>Situação da Reposição:</strong></td>
        <td>' . htmlspecialchars($reposicaoInfo['situacao']) . '</td>
    </tr>';

if (strtolower($reposicaoInfo['situacao']) === 'indeferido' && !empty($reposicaoInfo['motivo_indeferimento'])) {
  $html .= '<tr>
        <td><strong>Motivo do Indeferimento:</strong></td>
        <td>' . htmlspecialchars($reposicaoInfo['motivo_indeferimento']) . '</td>
    </tr>';
}

$html .= '</table>

<h2>Detalhes das Aulas de Reposição</h2>
<table cellpadding="5">
    <tr>
        <td><strong>Datas das Reposições:</strong></td>
        <td>' . htmlspecialchars($reposicaoInfo['datas_reposicao']) . '</td>
    </tr>
    <tr>
        <td><strong>Disciplinas:</strong></td>
        <td>' . htmlspecialchars($reposicaoInfo['disciplinas']) . '</td>
    </tr>
    <tr>
        <td><strong>Horários:</strong></td>
        <td>' . htmlspecialchars($reposicaoInfo['horarios']) . '</td>
    </tr>
</table>';

// Adicionar a tabela de agenda completa
$html .= '<h2>Agenda Completa do Professor</h2>';

// Iniciar a tabela
$html .= '<table border="1" cellpadding="4">
    <thead>
        <tr>
            <th><strong>Horário</strong></th>
            <th><strong>Segunda</strong></th>
            <th><strong>Terça</strong></th>
            <th><strong>Quarta</strong></th>
            <th><strong>Quinta</strong></th>
            <th><strong>Sexta</strong></th>
            <th><strong>Sábado</strong></th>
        </tr>
    </thead>
    <tbody>';

// Gerar as linhas da tabela
foreach ($horariosUnicos as $horario) {
  $html .= '<tr>';
  $html .= '<td>' . htmlspecialchars($horario) . '</td>';

  foreach ($diasSemana as $dia) {
    $eventoEncontrado = '-';

    // Verifica se há uma aula para o dia e horário específico
    foreach ($aulas as $aula) {
      $aulaHorarioInicio = date('H:i', strtotime($aula['horario_inicio']));
      $aulaHorarioFim = date('H:i', strtotime($aula['horario_fim']));
      if (
        strtoupper($aula['dia_semana']) === strtoupper($dia) &&
        ($aulaHorarioInicio . ' - ' . $aulaHorarioFim) === $horario
      ) {
        $eventoEncontrado = $aula['disciplina'];
        break;
      }
    }

    // Verifica se há uma atividade de HAE para o dia e horário específico
    if ($eventoEncontrado === '-') {
      foreach ($atividadesHAE as $hae) {
        $haeHorarioInicio = date('H:i', strtotime($hae['horario_inicio']));
        $haeHorarioFim = date('H:i', strtotime($hae['horario_fim']));
        if (
          strtoupper($hae['dia_semana']) === strtoupper($dia) &&
          ($haeHorarioInicio . ' - ' . $haeHorarioFim) === $horario
        ) {
          $eventoEncontrado = $hae['tipo_atividade'];
          break;
        }
      }
    }

    // Verifica se há uma reposição de aula inserida dinamicamente
    if ($eventoEncontrado === '-') {
      foreach ($aulasReposicao as $aulaReposicao) {
        $diaSemana = date('l', strtotime($aulaReposicao['data_reposicao']));
        $diaSemanaPT = $diasSemanaMap[$diaSemana] ?? '-';
        $horarioReposicao = date('H:i', strtotime($aulaReposicao['horarioinicio'])) . ' - ' . date('H:i', strtotime($aulaReposicao['horariofim']));

        if (
          strtoupper($diaSemanaPT) === strtoupper($dia) &&
          ($horarioReposicao) === $horario
        ) {
          $eventoEncontrado = 'Reposição - ' . htmlspecialchars($aulaReposicao['nome_disciplina']);
          break;
        }
      }
    }

    $html .= '<td>' . htmlspecialchars($eventoEncontrado) . '</td>';
  }

  $html .= '</tr>';
}

$html .= '</tbody></table>';

// Escreve o conteúdo HTML no PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Restaura as quebras de página automáticas
$pdf->SetAutoPageBreak($auto_page_break['status'], $auto_page_break['margin']);

// Gera o PDF
$pdf->Output('reposicao_detalhes.pdf', 'I');