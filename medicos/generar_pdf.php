<?php
session_start();
require_once 'conexion.php';

// Verificar si el médico está logueado
if (!isset($_SESSION['id_medico'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: buscar_paciente.php');
    exit;
}

$receta_id = $_GET['id'];

// Obtener información de la receta
$stmt = $conexion->prepare("SELECT r.*, m.nombre as medico_nombre, m.apellido as medico_apellido, 
                           u.nombre as paciente_nombre, u.apellido as paciente_apellido, u.dni as paciente_dni
                           FROM recetas r
                           JOIN medicos m ON r.id_medico = m.id_medico
                           JOIN usuarios u ON r.id_paciente = u.id_usuario
                           WHERE r.id_receta = ?");
$stmt->bind_param("i", $receta_id);
$stmt->execute();
$result = $stmt->get_result();
$receta = $result->fetch_assoc();

if (!$receta) {
    header('Location: buscar_paciente.php');
    exit;
}

// Incluir TCPDF
require_once('tcpdf/tcpdf.php');

// Crear nuevo documento PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Información del documento
$pdf->SetCreator('Sistema de Recetas Electrónicas');
$pdf->SetAuthor('Dr. ' . $receta['medico_nombre'] . ' ' . $receta['medico_apellido']);
$pdf->SetTitle('Receta Médica - ' . $receta['paciente_nombre']);
$pdf->SetSubject('Receta Médica');

// Margenes
$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Saltos de página automáticos
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Establecer fuente
$pdf->SetFont('helvetica', '', 12);

// Agregar página
$pdf->AddPage();

// Contenido del PDF
$html = '
<h1 style="text-align:center;">RECETA MÉDICA</h1>
<br>
<p><strong>Fecha:</strong> ' . date('d/m/Y', strtotime($receta['fecha_emision'])) . '</p>
<p><strong>Médico:</strong> Dr. ' . $receta['medico_nombre'] . ' ' . $receta['medico_apellido'] . '</p>
<p><strong>Paciente:</strong> ' . $receta['paciente_nombre'] . ' ' . $receta['paciente_apellido'] . '</p>
<p><strong>DNI:</strong> ' . $receta['paciente_dni'] . '</p>
<br>
<h3>Medicamentos Recetados:</h3>';

// Decodificar medicamentos (almacenados como JSON)
$medicamentos = json_decode($receta['medicamentos']);
foreach ($medicamentos as $med) {
    $html .= '<p>- ' . $med . '</p>';
}

$html .= '
<br>
<h3>Instrucciones:</h3>
<p>' . nl2br($receta['instrucciones']) . '</p>';

if (!empty($receta['observaciones'])) {
    $html .= '
    <br>
    <h3>Observaciones:</h3>
    <p>' . nl2br($receta['observaciones']) . '</p>';
}

// Escribir el contenido HTML
$pdf->writeHTML($html, true, false, true, false, '');

// Pie de página
$pdf->SetY(-15);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 10, 'Receta generada electrónicamente por el Sistema de Recetas Médicas', 0, 0, 'C');

// Salida del PDF
$pdf->Output('receta_medica_' . $receta['paciente_dni'] . '.pdf', 'D');