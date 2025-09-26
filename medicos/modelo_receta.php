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

$id_receta = $_GET['id'];
$db = new mysqli($host, $usuario, $contrasena, $base_datos);

// Obtener información de la receta
$stmt = $db->prepare("SELECT r.*, m.nombre as medico_nombre, m.apellido as medico_apellido, 
                     u.nombre as paciente_nombre, u.apellido as paciente_apellido, u.dni as paciente_dni
                     FROM recetas r
                     JOIN medicos m ON r.id_medico = m.id_medico
                     JOIN usuarios u ON r.id_paciente = u.id_usuario
                     WHERE r.id_receta = ?");
$stmt->bind_param("i", $id_receta);
$stmt->execute();
$result = $stmt->get_result();
$receta = $result->fetch_assoc();

if (!$receta) {
    header('Location: buscar_paciente.php');
    exit;
}

// Decodificar medicamentos (almacenados como JSON)
$medicamentos = json_decode($receta['medicamentos']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modelo de Receta Médica</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #3B82F6;
            --primary-dark: #2563EB;
            --secondary: #10B981;
            --danger: #EF4444;
            --light: #F9FAFB;
            --dark: #1F2937;
            --gray: #6B7280;
            --gray-light: #E5E7EB;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .receta-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .receta-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--primary);
        }
        
        .receta-title {
            color: var(--primary);
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0 0 10px 0;
        }
        
        .receta-date {
            color: var(--gray);
            font-size: 1rem;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            color: var(--primary);
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .info-item {
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: 500;
            color: var(--dark);
        }
        
        .medicamento-item {
            margin-bottom: 12px;
            padding-left: 20px;
            position: relative;
            line-height: 1.5;
        }
        
        .medicamento-item:before {
            content: "•";
            position: absolute;
            left: 0;
            color: var(--primary);
            font-size: 1.5rem;
            line-height: 1;
            top: 3px;
        }
        
        .receta-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid var(--primary);
            text-align: right;
        }
        
        .firma {
            display: inline-block;
            margin-top: 50px;
        }
        
        .firma-line {
            border-top: 1px solid var(--dark);
            width: 200px;
            margin: 0 auto;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            font-weight: 500;
            text-align: center;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-secondary {
            background-color: var(--gray-light);
            color: var(--dark);
        }
        
        .btn-secondary:hover {
            background-color: #D1D5DB;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            
            .container {
                padding: 0;
                max-width: 100%;
            }
            
            .receta-card {
                box-shadow: none;
                border: none;
                padding: 20px;
                margin: 0;
            }
            
            .action-buttons {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php include 'header_medico.php'; ?>
    
    <div class="container">
        <div class="receta-card">
            <div class="receta-header">
                <h1 class="receta-title">RECETA MÉDICA</h1>
                <p class="receta-date"><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($receta['fecha_emision'])) ?></p>
            </div>
            
            <div class="section">
                <h2 class="section-title">Información del Médico</h2>
                <div class="info-item">
                    <span class="info-label">Nombre:</span> Dr. <?= htmlspecialchars($receta['medico_nombre'] . ' ' . $receta['medico_apellido']) ?>
                </div>
            </div>
            
            <div class="section">
                <h2 class="section-title">Información del Paciente</h2>
                <div class="info-item">
                    <span class="info-label">Nombre:</span> <?= htmlspecialchars($receta['paciente_nombre'] . ' ' . $receta['paciente_apellido']) ?>
                </div>
                <div class="info-item">
                    <span class="info-label">DNI:</span> <?= htmlspecialchars($receta['paciente_dni']) ?>
                </div>
            </div>
            
            <div class="section">
                <h2 class="section-title">Medicamentos Recetados</h2>
                <?php foreach ($medicamentos as $med): ?>
                    <div class="medicamento-item"><?= htmlspecialchars($med) ?></div>
                <?php endforeach; ?>
            </div>
            
            <div class="section">
                <h2 class="section-title">Instrucciones</h2>
                <p><?= nl2br(htmlspecialchars($receta['instrucciones'])) ?></p>
            </div>
            
            <?php if (!empty($receta['observaciones'])): ?>
            <div class="section">
                <h2 class="section-title">Observaciones</h2>
                <p><?= nl2br(htmlspecialchars($receta['observaciones'])) ?></p>
            </div>
            <?php endif; ?>
            
            <div class="receta-footer">
                <div class="firma">
                    <div class="firma-line"></div>
                    <p>Firma del Médico</p>
                </div>
            </div>
        </div>
        
        <div class="action-buttons">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Imprimir/Guardar como PDF
            </button>
            <a href="crear_receta.php?paciente_id=<?= $receta['id_paciente'] ?>" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Volver a Editar
            </a>
        </div>
    </div>
    
    <?php include 'footer_medico.php'; ?>
    
    <script>
        // Configurar el título del documento al imprimir
        window.onbeforeprint = function() {
            document.title = "Receta_<?= $receta['paciente_nombre'] ?>_<?= $receta['paciente_dni'] ?>";
        };
    </script>
</body>
</html>