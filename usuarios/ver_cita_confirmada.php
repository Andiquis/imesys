<?php
session_start();
require 'conexion.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: mis_citas.php");
    exit;
}

$id_cita = intval($_GET['id']);
$id_usuario = $_SESSION['id_usuario'];

// Obtener información completa de la cita
$stmt = $conexion->prepare("
    SELECT c.id_cita, c.fecha_cita, c.estado, c.motivo, c.respuesta,
           m.nombre AS medico_nombre, m.apellido AS medico_apellido, m.foto AS medico_foto,
           m.direccion_consultorio, m.telefono AS medico_telefono,
           e.nombre_especialidad,
           u.nombre AS usuario_nombre, u.apellido AS usuario_apellido, u.telefono AS usuario_telefono
    FROM citas c
    JOIN medicos m ON c.id_medico = m.id_medico
    JOIN especialidades e ON m.id_especialidad = e.id_especialidad
    JOIN usuarios u ON c.id_usuario = u.id_usuario
    WHERE c.id_cita = ? AND c.id_usuario = ? AND c.estado = 'Confirmada'
");
$stmt->bind_param("ii", $id_cita, $id_usuario);
$stmt->execute();
$cita = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cita) {
    header("Location: mis_citas.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Cita Confirmada</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            .no-print {
                display: none;
            }
            body {
                background-color: white;
                color: black;
            }
            .comprobante {
                border: none;
                box-shadow: none;
            }
        }
        .comprobante {
            border: 2px dashed #ccc;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-green-100 p-6 text-center">
                <div class="text-green-600 text-6xl mb-4">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">¡Cita Confirmada!</h1>
                <p class="mt-2 text-gray-600">Tu cita ha sido confirmada por el médico</p>
            </div>
            
            <div class="p-6">
                <!-- Comprobante de cita -->
                <div id="comprobante" class="comprobante p-6 mb-8 rounded-lg">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <img src="img/logo.png" alt="IMESYS" class="h-12">
                        </div>
                        <div class="text-right">
                            <h2 class="text-xl font-bold">Comprobante de Cita</h2>
                            <p class="text-gray-600">N° <?= str_pad($cita['id_cita'], 6, '0', STR_PAD_LEFT) ?></p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <div>
                            <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Información del Médico</h3>
                            <div class="flex items-start gap-4 mb-4">
                                <?php if ($cita['medico_foto']): ?>
                                    <img src="uploads/medicos/<?= htmlspecialchars($cita['medico_foto']) ?>" 
                                         alt="<?= htmlspecialchars($cita['medico_nombre'] . ' ' . $cita['medico_apellido']) ?>" 
                                         class="w-16 h-16 rounded-full object-cover">
                                <?php else: ?>
                                    <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-400">
                                        <i class="fas fa-user-md text-2xl"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <p class="font-semibold"><?= htmlspecialchars($cita['medico_nombre'] . ' ' . $cita['medico_apellido']) ?></p>
                                    <p class="text-blue-600"><?= htmlspecialchars($cita['nombre_especialidad']) ?></p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <i class="fas fa-phone-alt mr-1"></i> <?= htmlspecialchars($cita['medico_telefono']) ?>
                                    </p>
                                </div>
                            </div>
                            <p class="text-sm">
                                <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                                <?= htmlspecialchars($cita['direccion_consultorio']) ?>
                            </p>
                        </div>
                        
                        <div>
                            <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Información del Paciente</h3>
                            <p class="font-semibold"><?= htmlspecialchars($cita['usuario_nombre'] . ' ' . $cita['usuario_apellido']) ?></p>
                            <p class="text-sm text-gray-600 mt-1">
                                <i class="fas fa-phone-alt mr-1"></i> <?= htmlspecialchars($cita['usuario_telefono']) ?>
                            </p>
                            
                            <div class="mt-4">
                                <h3 class="font-bold text-gray-700 border-b pb-2 mb-3">Detalles de la Cita</h3>
                                <p><span class="font-medium">Fecha y hora:</span> <?= date('d/m/Y H:i', strtotime($cita['fecha_cita'])) ?></p>
                                <p><span class="font-medium">Estado:</span> <span class="text-green-600 font-bold"><?= ucfirst(htmlspecialchars($cita['estado'])) ?></span></p>
                                <p><span class="font-medium">Motivo:</span> <?= htmlspecialchars($cita['motivo']) ?></p>
                                <?php if (!empty($cita['respuesta'])): ?>
                                    <p><span class="font-medium">Comentarios del médico:</span> <?= htmlspecialchars($cita['respuesta']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center text-sm text-gray-500 mt-6">
                        <p>Presentar este comprobante al llegar a la consulta</p>
                        <p>Recuerde que el pago debe hacerce antes de entrar a la cita de forma precencial</p>
                        <p class="mt-1">IMESYS - Sistema de Salud Inteligente</p>
                    </div>
                </div>
                
                <!-- Botones de acción -->
                <div class="flex flex-col sm:flex-row justify-center gap-4 no-print">
                    <a href="mis_citas.php" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition text-center">
                        <i class="fas fa-calendar-alt mr-2"></i> Volver a Mis Citas
                    </a>
                    
                    <button onclick="window.print()" 
                            class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-medium transition">
                        <i class="fas fa-print mr-2"></i> Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>