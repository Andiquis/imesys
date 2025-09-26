<?php
include 'header_medico.php';

// Verificar si se recibió el ID de la consulta
if (!isset($_GET['id_consulta']) || empty($_GET['id_consulta'])) {
    echo "<script>alert('Consulta no especificada'); window.location.href = 'buscar_paciente.php';</script>";
    exit;
}

$id_consulta = $_GET['id_consulta'];

require 'conexion.php';

// Obtener información completa de la consulta
$query = "SELECT hc.*, 
                 u.nombre as paciente_nombre, u.apellido as paciente_apellido, u.dni,
                 m.nombre as medico_nombre, m.apellido as medico_apellido
          FROM historial_consultas hc
          JOIN usuarios u ON hc.id_usuario = u.id_usuario
          JOIN medicos m ON hc.id_medico = m.id_medico
          WHERE hc.id_historial = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id_consulta);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Consulta no encontrada'); window.location.href = 'buscar_paciente.php';</script>";
    exit;
}

$consulta = $result->fetch_assoc();
$stmt->close();
$conexion->close();

// Calcular edad del paciente
$fecha_nacimiento = new DateTime($consulta['fecha_nacimiento']);
$hoy = new DateTime();
$edad = $hoy->diff($fecha_nacimiento)->y;
?>

<!-- Contenido principal -->
<div id="contentArea" class="content-area">
    <div class="container mx-auto px-4 py-6">
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Detalles de Consulta</h1>
            <a href="historial_medico.php?id=<?= $consulta['id_usuario'] ?>" class="boton-outline">
                <i class="fas fa-arrow-left mr-2"></i> Volver al historial
            </a>
        </div>
        
        <!-- Información de la consulta -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Encabezado con datos básicos -->
            <div class="bg-blue-50 px-6 py-4 border-b border-blue-100">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                    <div>
                        <h2 class="text-lg font-semibold text-blue-800">
                            <?= date('d/m/Y H:i', strtotime($consulta['fecha_hora'])) ?>
                        </h2>
                        <p class="text-sm text-blue-600">
                            Dr. <?= htmlspecialchars($consulta['medico_nombre'] . ' ' . $consulta['medico_apellido']) ?>
                        </p>
                    </div>
                    <div class="mt-2 md:mt-0">
                        <a href="editar_consulta.php?id_consulta=<?= $id_consulta ?>" class="boton py-1 px-3 text-sm">
                            <i class="fas fa-edit mr-1"></i> Editar Consulta
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Cuerpo con todos los detalles -->
            <div class="p-6">
                <!-- Información del paciente -->
                <div class="mb-8">
                    <h3 class="text-md font-semibold text-gray-700 mb-3">Datos del Paciente</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Nombre completo</p>
                            <p class="font-medium"><?= htmlspecialchars($consulta['paciente_nombre'] . ' ' . $consulta['paciente_apellido']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">DNI</p>
                            <p class="font-medium"><?= htmlspecialchars($consulta['dni']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Edad</p>
                            <p class="font-medium"><?= $edad ?> años</p>
                        </div>
                    </div>
                </div>
                
                <!-- Motivo de consulta -->
                <div class="mb-6">
                    <h3 class="text-md font-semibold text-gray-700 mb-2">Motivo de la Consulta</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-700 whitespace-pre-line"><?= htmlspecialchars($consulta['motivo']) ?></p>
                    </div>
                </div>
                
                <!-- Observaciones -->
                <?php if (!empty($consulta['observacion'])): ?>
                <div class="mb-6">
                    <h3 class="text-md font-semibold text-gray-700 mb-2">Observaciones</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-700 whitespace-pre-line"><?= htmlspecialchars($consulta['observacion']) ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Diagnóstico -->
                <?php if (!empty($consulta['diagnostico'])): ?>
                <div class="mb-6">
                    <h3 class="text-md font-semibold text-gray-700 mb-2">Diagnóstico</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-700 whitespace-pre-line"><?= htmlspecialchars($consulta['diagnostico']) ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Tratamiento -->
                <?php if (!empty($consulta['tratamiento'])): ?>
                <div class="mb-6">
                    <h3 class="text-md font-semibold text-gray-700 mb-2">Tratamiento</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-700 whitespace-pre-line"><?= htmlspecialchars($consulta['tratamiento']) ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Imagen médica -->
                <?php if (!empty($consulta['imagen'])): ?>
                <div class="mb-6">
                    <h3 class="text-md font-semibold text-gray-700 mb-2">Imagen Médica</h3>
                    <div class="bg-gray-50 p-4 rounded-lg flex flex-col items-center">
                        <img src="<?= htmlspecialchars($consulta['imagen']) ?>" alt="Imagen médica" class="max-w-full h-auto max-h-64 rounded-lg">
                        <a href="<?= htmlspecialchars($consulta['imagen']) ?>" target="_blank" class="mt-2 text-blue-600 hover:text-blue-800">
                            <i class="fas fa-expand mr-1"></i> Ver en tamaño completo
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Dato opcional -->
                <?php if (!empty($consulta['dato_opcional'])): ?>
                <div class="mb-6">
                    <h3 class="text-md font-semibold text-gray-700 mb-2">Información Adicional</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-700 whitespace-pre-line"><?= htmlspecialchars($consulta['dato_opcional']) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer_medico.php'; ?>