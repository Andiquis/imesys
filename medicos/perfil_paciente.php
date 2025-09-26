<?php
// Incluimos el header médico
include 'header_medico.php';

// Verificamos si se ha proporcionado un ID de paciente
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Paciente no especificado'); window.location.href = 'buscador_pacientes.php';</script>";
    exit;
}

$id_paciente = $_GET['id']; // Cambiamos de id_paciente a simplemente id

// Resto del código sigue igual...
require 'conexion.php';

// Obtener información básica del paciente
$query_paciente = "SELECT * FROM usuarios WHERE id_usuario = ?";
$stmt_paciente = $conexion->prepare($query_paciente);
$stmt_paciente->bind_param("i", $id_paciente);
$stmt_paciente->execute();
$result_paciente = $stmt_paciente->get_result();

if ($result_paciente->num_rows === 0) {
    echo "<script>alert('Paciente no encontrado'); window.location.href = 'buscador_pacientes.php';</script>";
    exit;
}

$paciente = $result_paciente->fetch_assoc();
$stmt_paciente->close();

// Obtener datos biométricos del paciente (últimos 5 registros)
$query_biometricos = "SELECT * FROM datos_biometricos 
                      WHERE id_usuario = ? 
                      ORDER BY fecha_registro DESC 
                      LIMIT 5";
$stmt_biometricos = $conexion->prepare($query_biometricos);
$stmt_biometricos->bind_param("i", $id_paciente);
$stmt_biometricos->execute();
$result_biometricos = $stmt_biometricos->get_result();
$biometricos = $result_biometricos->fetch_all(MYSQLI_ASSOC);
$stmt_biometricos->close();

// Calcular edad
$fecha_nacimiento = new DateTime($paciente['fecha_nacimiento']);
$hoy = new DateTime();
$edad = $hoy->diff($fecha_nacimiento)->y;

// Procesar ruta de la foto
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/imesys/';
$ruta_foto = '';
$imagen_existe = false;

if ($paciente['foto']) {
    $nombre_archivo = basename($paciente['foto']);
    $ruta_foto = $base_url . 'uploads/pacientes/' . $nombre_archivo;
    $ruta_absoluta = $_SERVER['DOCUMENT_ROOT'] . '/imesys/uploads/pacientes/' . $nombre_archivo;
    $imagen_existe = file_exists($ruta_absoluta);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Panel Médico</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="estilos_inicio.css">
    </head>
<body class="bg-gray-100">

<!-- Contenido principal -->
<div id="contentArea" class="content-area">
    <div class="container mx-auto px-4 py-6">
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Perfil del Paciente</h1>
            <a href="buscador_pacientes.php" class="boton-outline">
                <i class="fas fa-arrow-left mr-2"></i> Volver a la lista
            </a>
        </div>

        <!-- Tarjeta de información del paciente -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="p-6">
                <div class="flex flex-col md:flex-row items-start md:items-center gap-6 mb-6">
                    <!-- Foto del paciente -->
                    <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-blue-100">
                        <?php if($paciente['foto'] && $imagen_existe): ?>
                            <img src="<?= $ruta_foto ?>" alt="Foto del paciente" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-user text-4xl text-blue-500"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Información básica -->
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-gray-800">
                            <?= htmlspecialchars($paciente['nombre'] . ' ' . htmlspecialchars($paciente['apellido'])) ?>
                        </h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <p class="text-sm text-gray-500">Edad</p>
                                <p class="font-medium"><?= $edad ?> años</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Género</p>
                                <p class="font-medium"><?= htmlspecialchars($paciente['genero']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">DNI</p>
                                <p class="font-medium"><?= htmlspecialchars($paciente['dni']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Teléfono</p>
                                <p class="font-medium"><?= htmlspecialchars($paciente['telefono'] ?? 'No registrado') ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Correo</p>
                                <p class="font-medium"><?= htmlspecialchars($paciente['correo']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Fecha Nacimiento</p>
                                <p class="font-medium"><?= date('d/m/Y', strtotime($paciente['fecha_nacimiento'])) ?></p>
                            </div>
                            <div class="flex gap-2 mt-2">
    <a href="editar_perfil_paciente.php?id_paciente=<?= $id_paciente ?>" class="boton-outline">
        <i class="fas fa-user-edit mr-2"></i> Editar perfil
    </a>
</div>
                        </div>
                    </div>
                </div>
                
                <!-- Información adicional -->
                <div class="border-t pt-4">
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Información Adicional</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Dirección</p>
                            <p class="font-medium"><?= htmlspecialchars($paciente['direccion'] ?? 'No registrada') ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Fecha de Registro</p>
                            <p class="font-medium"><?= date('d/m/Y H:i', strtotime($paciente['fecha_registro'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de datos biométricos -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800">Datos Biométricos</h2>
                    <!--a href="datos_bio.php?id_paciente=<?= $id_paciente ?>" class="boton">
                        <i class="fas fa-plus mr-2"></i> Nuevo Registro
                    </a-->
                    
     <a href="editar_biometricos.php?id_paciente=<?= $id_paciente ?>" class="boton-outline">
        <i class="fas fa-heartbeat mr-2"></i> Editar biométricos
    </a>

                </div>
                
                <?php if (count($biometricos) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peso (kg)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Altura (m)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Presión Arterial</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frec. Cardíaca</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Glucosa (mg/dL)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Predicción IA</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($biometricos as $dato): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= date('d/m/Y H:i', strtotime($dato['fecha_registro'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?= $dato['peso'] ?? '--' ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= $dato['altura'] ?? '--' ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($dato['presion_arterial'] ?? '--') ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= $dato['frecuencia_cardiaca'] ?? '--' ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= $dato['nivel_glucosa'] ?? '--' ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($dato['resultado_prediccion'] ?? '--') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <a href="historial_biometricos.php?id_paciente=<?= $id_paciente ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Ver historial completo <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-heartbeat text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">No se encontraron registros biométricos</p>
                        <p class="text-sm text-gray-400 mt-2">Registre los primeros datos biométricos del paciente</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sección de acciones -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <a href="agenda_medico.php?id_paciente=<?= $id_paciente ?>" class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow flex items-center">
                <div class="bg-blue-100 p-3 rounded-full mr-4">
                    <i class="fas fa-calendar-plus text-blue-600"></i>
                </div>
                <div>
                    <h3 class="font-medium text-gray-800">Agendar Cita</h3>
                    <p class="text-sm text-gray-500">Programar una nueva consulta</p>
                </div>
            </a>
            
            <a href="agregar_consulta.php?id_paciente=<?= $id_paciente ?>" class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow flex items-center">
                <div class="bg-green-100 p-3 rounded-full mr-4">
                    <i class="fas fa-prescription text-green-600"></i>
                </div>
                <div>
                    <h3 class="font-medium text-gray-800">Añadir Consulta</h3>
                    <p class="text-sm text-gray-500">Registrar una nueva consulta</p>
                </div>
            </a>
            
            <a href="historial_medico.php?id_paciente=<?= $id_paciente ?>" class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow flex items-center">
                <div class="bg-purple-100 p-3 rounded-full mr-4">
                    <i class="fas fa-file-medical text-purple-600"></i>
                </div>
                <div>
                    <h3 class="font-medium text-gray-800">Historial Médico</h3>
                    <p class="text-sm text-gray-500">Ver historial completo</p>
                </div>
            </a>
        </div>

        <!-- Sección de contacto -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Soporte Técnico</h2>
            <p class="text-gray-600 mb-4">¿Necesitas ayuda con el sistema o tienes alguna sugerencia?</p>
            <a href="https://wa.me/51930173314" target="_blank">
                <button class="boton">
                    <i class="fas fa-headset mr-2"></i> Contactar soporte
                </button>
            </a>
        </div>
    </div>
</div>

<?php include 'footer_medico.php'; ?>

</body>
</html>