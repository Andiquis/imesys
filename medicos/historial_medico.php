<?php
include 'header_medico.php';

// Verificar si se recibió el ID del paciente (parámetro 'id' desde el botón)
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Paciente no especificado'); window.location.href = 'buscar_paciente.php';</script>";
    exit;
}

$id_paciente = $_GET['id']; // Recibimos como 'id' desde el botón
$id_medico = $_SESSION['id_medico'];

// Obtener información básica del paciente
require 'conexion.php';
$query_paciente = "SELECT nombre, apellido, dni, fecha_nacimiento FROM usuarios WHERE id_usuario = ?";
$stmt_paciente = $conexion->prepare($query_paciente);
$stmt_paciente->bind_param("i", $id_paciente);
$stmt_paciente->execute();
$result_paciente = $stmt_paciente->get_result();

if ($result_paciente->num_rows === 0) {
    echo "<script>alert('Paciente no encontrado'); window.location.href = 'buscar_paciente.php';</script>";
    exit;
}

$paciente = $result_paciente->fetch_assoc();
$stmt_paciente->close();

// Calcular edad
$fecha_nacimiento = new DateTime($paciente['fecha_nacimiento']);
$hoy = new DateTime();
$edad = $hoy->diff($fecha_nacimiento)->y;

// Procesar filtros
$filtro_fecha_desde = $_GET['fecha_desde'] ?? '';
$filtro_fecha_hasta = $_GET['fecha_hasta'] ?? '';
$filtro_motivo = $_GET['motivo'] ?? '';

// Construir consulta con filtros
$query_consultas = "SELECT hc.*, m.nombre as medico_nombre, m.apellido as medico_apellido 
                    FROM historial_consultas hc
                    JOIN medicos m ON hc.id_medico = m.id_medico
                    WHERE hc.id_usuario = ?";

$params = [$id_paciente];
$types = "i";

// Añadir filtros si existen
if (!empty($filtro_fecha_desde)) {
    $query_consultas .= " AND hc.fecha_hora >= ?";
    $params[] = $filtro_fecha_desde;
    $types .= "s";
}

if (!empty($filtro_fecha_hasta)) {
    $query_consultas .= " AND hc.fecha_hora <= ?";
    $params[] = $filtro_fecha_hasta . ' 23:59:59';
    $types .= "s";
}

if (!empty($filtro_motivo)) {
    $query_consultas .= " AND hc.motivo LIKE ?";
    $params[] = '%' . $filtro_motivo . '%';
    $types .= "s";
}

$query_consultas .= " ORDER BY hc.fecha_hora DESC";

$stmt_consultas = $conexion->prepare($query_consultas);
$stmt_consultas->bind_param($types, ...$params);
$stmt_consultas->execute();
$result_consultas = $stmt_consultas->get_result();
$consultas = $result_consultas->fetch_all(MYSQLI_ASSOC);
$stmt_consultas->close();
$conexion->close();
?>

<!-- Contenido principal -->
<div id="contentArea" class="content-area">
    <div class="container mx-auto px-4 py-6">
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Historial Médico Completo</h1>
            <a href="perfil_paciente.php?id=<?= $id_paciente ?>" class="boton-outline">
                <i class="fas fa-arrow-left mr-2"></i> Volver al perfil
            </a>
        </div>
        
        <!-- Información del paciente -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                <div class="w-24 h-24 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-user text-3xl text-blue-600"></i>
                </div>
                
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-800">
                        <?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']) ?>
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <p class="text-sm text-gray-500">DNI</p>
                            <p class="font-medium"><?= htmlspecialchars($paciente['dni']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Edad</p>
                            <p class="font-medium"><?= $edad ?> años</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">N° Consultas</p>
                            <p class="font-medium"><?= count($consultas) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Última consulta</p>
                            <p class="font-medium">
                                <?= !empty($consultas) ? date('d/m/Y', strtotime($consultas[0]['fecha_hora'])) : 'N/A' ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Filtrar Historial</h2>
            <form method="GET" action="historial_medico.php" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="hidden" name="id" value="<?= $id_paciente ?>">
                
                <div>
                    <label for="fecha_desde" class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                    <input type="date" id="fecha_desde" name="fecha_desde" value="<?= htmlspecialchars($filtro_fecha_desde) ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                
                <div>
                    <label for="fecha_hasta" class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                    <input type="date" id="fecha_hasta" name="fecha_hasta" value="<?= htmlspecialchars($filtro_fecha_hasta) ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                
                <div>
                    <label for="motivo" class="block text-sm font-medium text-gray-700 mb-1">Motivo contiene</label>
                    <input type="text" id="motivo" name="motivo" value="<?= htmlspecialchars($filtro_motivo) ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="boton h-10 w-full md:w-auto">
                        <i class="fas fa-filter mr-2"></i> Filtrar
                    </button>
                    <a href="historial_medico.php?id=<?= $id_paciente ?>" class="boton-outline h-10 ml-2 flex items-center justify-center px-4">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Listado de consultas -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <?php if (empty($consultas)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-file-medical text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500">No se encontraron consultas registradas</p>
                    <a href="registrar_consulta.php?id_paciente=<?= $id_paciente ?>" class="boton mt-4 inline-block">
                        <i class="fas fa-plus mr-2"></i> Registrar primera consulta
                    </a>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($consultas as $consulta): ?>
                        <div class="p-6 hover:bg-gray-50">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold text-blue-600">
                                        <?= date('d/m/Y H:i', strtotime($consulta['fecha_hora'])) ?>
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Atendido por: Dr. <?= htmlspecialchars($consulta['medico_nombre'] . ' ' . $consulta['medico_apellido']) ?>
                                    </p>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="ver_consulta.php?id_consulta=<?= $consulta['id_historial'] ?>" 
                                       class="boton-outline py-1 px-3 text-sm">
                                        <i class="fas fa-eye mr-1"></i> Ver
                                    </a>
                                    <a href="editar_consulta.php?id_consulta=<?= $consulta['id_historial'] ?>" 
                                       class="boton-outline py-1 px-3 text-sm">
                                        <i class="fas fa-edit mr-1"></i> Editar
                                    </a>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h4 class="font-medium text-gray-800">Motivo:</h4>
                                <p class="text-gray-600"><?= nl2br(htmlspecialchars($consulta['motivo'])) ?></p>
                                
                                <?php if (!empty($consulta['diagnostico'])): ?>
                                    <h4 class="font-medium text-gray-800 mt-3">Diagnóstico:</h4>
                                    <p class="text-gray-600"><?= nl2br(htmlspecialchars($consulta['diagnostico'])) ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($consulta['tratamiento'])): ?>
                                    <h4 class="font-medium text-gray-800 mt-3">Tratamiento:</h4>
                                    <p class="text-gray-600"><?= nl2br(htmlspecialchars($consulta['tratamiento'])) ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($consulta['imagen'])): ?>
                                    <h4 class="font-medium text-gray-800 mt-3">Imagen médica:</h4>
                                    <a href="<?= htmlspecialchars($consulta['imagen']) ?>" target="_blank" 
                                       class="text-blue-600 hover:text-blue-800 inline-block mt-1">
                                        <i class="fas fa-image mr-1"></i> Ver imagen
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer_medico.php'; ?>