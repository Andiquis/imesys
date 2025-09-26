<?php
session_start();
require_once 'conexion.php';

// Verificar si el médico está logueado
if (!isset($_SESSION['id_medico'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$paciente = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = trim($_POST['dni']);
    
    if (empty($dni)) {
        $error = 'Por favor ingrese un DNI';
    } else {
        // Buscar paciente por DNI
        $stmt = $conexion->prepare("SELECT id_usuario, nombre, apellido, dni, fecha_nacimiento, genero FROM usuarios WHERE dni = ?");
        $stmt->bind_param("s", $dni);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $paciente = $result->fetch_assoc();
        } else {
            $error = 'No se encontró ningún paciente con ese DNI';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Paciente - Recetas Médicas</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="buscar_receta.css">
</head>
<body>
    <?php include 'header_medico.php'; ?>
    <div class="main-content">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">Buscar Paciente</h1>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= $error ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="search-form">
                    <div class="form-group">
                        <label for="dni" class="form-label">DNI del Paciente</label>
                        <input type="text" id="dni" name="dni" class="form-control" 
                               placeholder="Ingrese DNI (8 dígitos)" required
                               pattern="[0-9]{8}" title="Ingrese un DNI válido de 8 dígitos">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar Paciente
                    </button>
                </form>
                
                <?php if ($paciente): ?>
                    <div class="patient-card">
                        <div class="patient-header">
                            <h3 class="patient-title">Información del Paciente</h3>
                        </div>
                        
                        <div class="patient-detail">
                            <strong>Nombre:</strong>
                            <span><?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']) ?></span>
                        </div>
                        
                        <div class="patient-detail">
                            <strong>DNI:</strong>
                            <span><?= htmlspecialchars($paciente['dni']) ?></span>
                        </div>
                        
                        <div class="patient-detail">
                            <strong>Fecha de Nacimiento:</strong>
                            <span><?= date('d/m/Y', strtotime($paciente['fecha_nacimiento'])) ?></span>
                        </div>
                        
                        <div class="patient-detail">
                            <strong>Género:</strong>
                            <span><?= htmlspecialchars($paciente['genero']) ?></span>
                        </div>
                        
                        <div class="actions">
                            <a href="crear_receta.php?paciente_id=<?= $paciente['id_usuario'] ?>" class="btn btn-primary">
                                <i class="fas fa-file-medical"></i> Generar Receta
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'footer_medico.php'; ?>
</body>
</html>