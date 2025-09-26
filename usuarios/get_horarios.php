<?php
require 'conexion.php';

if (!isset($_GET['id_medico']) || !isset($_GET['fecha'])) {
    header('HTTP/1.1 400 Bad Request');
    exit;
}

$id_medico = intval($_GET['id_medico']);
$fecha = $_GET['fecha'];

// Validar formato de fecha
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    header('HTTP/1.1 400 Bad Request');
    exit;
}

// 1. Obtener horarios registrados en la base de datos
$stmt = $conexion->prepare("
    SELECT id_agenda, fecha_hora, estado 
    FROM agenda_medico 
    WHERE id_medico = ? 
    AND DATE(fecha_hora) = ?
    ORDER BY fecha_hora
");
$stmt->bind_param("is", $id_medico, $fecha);
$stmt->execute();
$result = $stmt->get_result();
$horarios_registrados = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 2. Generar horarios por defecto (9am a 6pm) si no hay registros
$horarios = [];
$intervalo = 30; // minutos entre citas

if (empty($horarios_registrados)) {
    $hora_inicio = strtotime($fecha . ' 09:00:00');
    $hora_fin = strtotime($fecha . ' 18:00:00');
    
    for ($hora = $hora_inicio; $hora <= $hora_fin; $hora += ($intervalo * 60)) {
        $horarios[] = [
            'id_agenda' => null,
            'fecha_hora' => date('Y-m-d H:i:s', $hora),
            'estado' => 'Disponible'
        ];
    }
} else {
    // Usar los horarios registrados en la base de datos
    foreach ($horarios_registrados as $hr) {
        if ($hr['estado'] === 'Disponible') {
            $horarios[] = [
                'id_agenda' => $hr['id_agenda'],
                'fecha_hora' => $hr['fecha_hora'],
                'estado' => $hr['estado']
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($horarios);
?>