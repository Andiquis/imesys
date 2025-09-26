<?php
session_start();
require_once 'conexion.php';

// Verificar si el médico está logueado
if (!isset($_SESSION['id_medico'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['paciente_id'])) {
    header('Location: buscar_paciente.php');
    exit;
}

$paciente_id = $_GET['paciente_id'];
$db = new mysqli($host, $usuario, $contrasena, $base_datos);

// Obtener información del paciente
$stmt = $db->prepare("SELECT id_usuario, nombre, apellido, dni FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $paciente_id);
$stmt->execute();
$result = $stmt->get_result();
$paciente = $result->fetch_assoc();

if (!$paciente) {
    header('Location: buscar_paciente.php');
    exit;
}

// Obtener información del médico
$medico_id = $_SESSION['id_medico'];
$stmt = $db->prepare("SELECT nombre, apellido FROM medicos WHERE id_medico = ?");
$stmt->bind_param("i", $medico_id);
$stmt->execute();
$result = $stmt->get_result();
$medico = $result->fetch_assoc();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medicamentos = $_POST['medicamentos'];
    $instrucciones = $_POST['instrucciones'];
    $observaciones = $_POST['observaciones'] ?? '';
    
    if (empty($medicamentos)) {
        $error = 'Debe ingresar al menos un medicamento';
    } else {
        // Convertir medicamentos a JSON
        $meds_array = explode("\n", $medicamentos);
        $meds_json = json_encode(array_map('trim', $meds_array));
        
        // Guardar receta en la base de datos
        $fecha_emision = date('Y-m-d H:i:s');
        $stmt = $db->prepare("INSERT INTO recetas (id_medico, id_paciente, fecha_emision, medicamentos, instrucciones, observaciones) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissss", $medico_id, $paciente_id, $fecha_emision, $meds_json, $instrucciones, $observaciones);
        
        if ($stmt->execute()) {
            $id_receta = $db->insert_id;
            $success = 'Receta generada correctamente. <a href="modelo_receta.php?id=' . $id_receta . '" class="text-blue-600 hover:text-blue-800 font-medium">Ver Receta</a>';
        } else {
            $error = 'Error al guardar la receta: ' . $db->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Receta Médica</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="receta.css">
</head>
<body>
    <?php include 'header_medico.php'; ?>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Crear Receta Médica</h1>
            </div>
            
            <div class="patient-info">
                <h3>Paciente: <?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']) ?></h3>
                <p><strong>DNI:</strong> <?= htmlspecialchars($paciente['dni']) ?></p>
                <p><strong>Médico:</strong> Dr. <?= htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']) ?></p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle mr-2"></i><?= $error ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-2"></i><?= $success ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="medicamentos" class="form-label">Medicamentos (uno por línea)</label>
                    <textarea id="medicamentos" name="medicamentos" class="form-control" required 
                              placeholder="Ejemplo:&#10;Paracetamol 500mg - 1 tableta cada 8 horas&#10;Amoxicilina 500mg - 1 cápsula cada 12 horas"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="instrucciones" class="form-label">Instrucciones Generales</label>
                    <textarea id="instrucciones" name="instrucciones" class="form-control" required 
                              placeholder="Ejemplo:&#10;Tomar con alimentos&#10;Evitar la exposición al sol"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="observaciones" class="form-label">Observaciones (opcional)</label>
                    <textarea id="observaciones" name="observaciones" class="form-control" 
                              placeholder="Ejemplo:&#10;Volver a consulta en 7 días"></textarea>
                </div>
                
                <div class="form-actions">
                    <a href="buscar_pacientes_receta.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Generar Receta</button>
                </div>
            </form>
        </div>
    </div>
    
    <?php include 'footer_medico.php'; ?>
</body>
</html>