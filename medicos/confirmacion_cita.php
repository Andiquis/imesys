<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: login_usuario.php");
    exit;
}

require 'conexion.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: lista_medicos.php");
    exit;
}

$id_cita = intval($_GET['id']);
$id_usuario = $_SESSION['id_usuario'];

// Obtener información de la cita
$stmt = $conexion->prepare("
    SELECT c.id_cita, c.fecha_cita, c.estado, c.motivo,
           m.nombre AS medico_nombre, m.apellido AS medico_apellido, m.foto AS medico_foto,
           m.direccion_consultorio, m.telefono AS medico_telefono,
           e.nombre_especialidad,
           u.nombre AS usuario_nombre, u.apellido AS usuario_apellido, u.telefono AS usuario_telefono
    FROM citas c
    JOIN medicos m ON c.id_medico = m.id_medico
    JOIN especialidades e ON m.id_especialidad = e.id_especialidad
    JOIN usuarios u ON c.id_usuario = u.id_usuario
    WHERE c.id_cita = ? AND c.id_usuario = ?
");
$stmt->bind_param("ii", $id_cita, $id_usuario);
$stmt->execute();
$cita = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cita) {
    header("Location: lista_medicos.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Confirmación de Cita</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .comprobante {
            border: 2px dashed #ccc;
            background-color: #f9f9f9;
        }
        @media print {
            .no-print {
                display: none;
            }
            .comprobante {
                border: none;
                background-color: white;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Barra superior -->
    <nav class="bg-gradient-to-r from-[#5DD9FC] to-[#0052CC] p-4 text-white flex justify-between items-center fixed top-0 left-0 right-0 z-50">
        <!-- ... (navbar como en tu página principal) ... -->
    </nav>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar">
        <!-- ... (sidebar como en tu página principal) ... -->
    </div>

    <!-- Contenido principal -->
    <div id="contentArea" class="content-area pt-20">
        <div class="container mx-auto px-4 py-8 max-w-4xl">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-green-100 p-6 text-center">
                




                <!-- Cambia esta sección (parte del éxito) -->
<div class="bg-green-100 p-6 text-center">
    <div class="text-blue-600 text-6xl mb-4"> <!-- Cambiado a azul para indicar "pendiente" -->
        <i class="fas fa-clock"></i> <!-- Icono de reloj en lugar de check -->
    </div>
    <h1 class="text-2xl font-bold text-gray-800">¡Cita Guardada Correctamente!</h1>
    <p class="mt-2 text-gray-600">
        Tu cita está <span class="font-semibold">pendiente de confirmación</span> por el médico. 
        Por favor, revisa en <a href="mis_citas.php" class="text-blue-600 underline">Mis Citas</a> 
        para verificar su estado.
    </p>
    <p class="mt-2 text-sm text-gray-500">
        * El médico podría ajustar horarios por imprevistos. Te notificaremos cuando confirme.
    </p>
</div>
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
                                    <p><span class="font-medium">Estado:</span> <?= ucfirst(htmlspecialchars($cita['estado'])) ?></p>
                                    <p><span class="font-medium">Motivo:</span> <?= htmlspecialchars($cita['motivo']) ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center text-sm text-gray-500 mt-6">
                            <p></p>
                            <p class="mt-1">IMESYS - Sistema de Salud Inteligente</p>
                        </div>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="flex flex-col sm:flex-row justify-center gap-4 no-print">
                        <a href="mis_citas.php" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition text-center">
                            <i class="fas fa-calendar-alt mr-2"></i> Ver Mis Citas
                        </a>
                        
                        <button onclick="window.print()" 
                                class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-medium transition">
                            <i class="fas fa-print mr-2"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer no-print">
        <!-- ... (footer como en tu página principal) ... -->
    </footer>
</body>
</html>