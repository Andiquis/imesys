<?php
include 'header_medico.php';

require 'conexion.php';

// Procesar filtros
$filtro_fecha_desde = $_GET['fecha_desde'] ?? '';
$filtro_fecha_hasta = $_GET['fecha_hasta'] ?? '';
$filtro_busqueda = $_GET['busqueda'] ?? '';

// Construir consulta con filtros
$query = "SELECT hc.*, 
                 u.nombre as paciente_nombre, 
                 u.apellido as paciente_apellido, 
                 u.dni as paciente_dni,
                 m.nombre as medico_nombre, 
                 m.apellido as medico_apellido
          FROM historial_consultas hc
          JOIN usuarios u ON hc.id_usuario = u.id_usuario
          JOIN medicos m ON hc.id_medico = m.id_medico
          WHERE 1=1";

$params = [];
$types = "";

// Añadir filtros si existen
if (!empty($filtro_fecha_desde)) {
    $query .= " AND hc.fecha_hora >= ?";
    $params[] = $filtro_fecha_desde;
    $types .= "s";
}

if (!empty($filtro_fecha_hasta)) {
    $query .= " AND hc.fecha_hora <= ?";
    $params[] = $filtro_fecha_hasta . ' 23:59:59';
    $types .= "s";
}

if (!empty($filtro_busqueda)) {
    $query .= " AND (u.dni LIKE ? OR u.nombre LIKE ? OR u.apellido LIKE ? OR CONCAT(u.nombre, ' ', u.apellido) LIKE ?)";
    $search_term = '%' . $filtro_busqueda . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= str_repeat("s", 4);
}

$query .= " ORDER BY hc.fecha_hora DESC LIMIT 10";

$stmt = $conexion->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$consultas = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conexion->close();
?>

<!-- Contenido principal -->
<div id="contentArea" class="main-content">
    <div class="container mx-auto px-4 py-6">
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Búsqueda de Historiales Médicos</h1>
        </div>
        
        <!-- Filtros de búsqueda -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Filtrar Historiales</h2>
            <form method="GET" action="buscar_historiales.php" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                    <label for="busqueda" class="block text-sm font-medium text-gray-700 mb-1">Buscar paciente (DNI/Nombre)</label>
                    <input type="text" id="busqueda" name="busqueda" value="<?= htmlspecialchars($filtro_busqueda) ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                
                <div class="flex items-end space-x-2">
                    <button type="submit" class="boton h-10 w-full md:w-auto">
                        <i class="fas fa-search mr-2"></i> Buscar
                    </button>
                    <a href="buscar_historiales.php" class="boton-outline h-10 flex items-center justify-center px-4">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Resultados de búsqueda -->
        <div class="space-y-4">
            <?php if (empty($consultas)): ?>
                <div class="bg-white rounded-lg shadow-md p-8 text-center">
                    <i class="fas fa-file-medical text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500">No se encontraron consultas registradas</p>
                    <p class="text-sm text-gray-400 mt-2">Prueba ajustando los filtros de búsqueda</p>
                </div>
            <?php else: ?>
                <?php foreach ($consultas as $consulta): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="p-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold text-blue-600">
                                        <?= date('d/m/Y H:i', strtotime($consulta['fecha_hora'])) ?>
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium">Paciente:</span> 
                                            <?= htmlspecialchars($consulta['paciente_nombre'] . ' ' . $consulta['paciente_apellido']) ?>
                                            (DNI: <?= htmlspecialchars($consulta['paciente_dni']) ?>)
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium">Médico:</span> 
                                            Dr. <?= htmlspecialchars($consulta['medico_nombre'] . ' ' . $consulta['medico_apellido']) ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="ver_consulta.php?id_consulta=<?= $consulta['id_historial'] ?>" 
                                       class="boton-outline py-1 px-3 text-sm">
                                        <i class="fas fa-eye mr-1"></i> Ver
                                    </a>
                                    <a href="perfil_paciente.php?id=<?= $consulta['id_usuario'] ?>" 
                                       class="boton-outline py-1 px-3 text-sm">
                                        <i class="fas fa-user mr-1"></i> Perfil
                                    </a>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h4 class="font-medium text-gray-800">Motivo:</h4>
                                <p class="text-gray-600 truncate"><?= htmlspecialchars($consulta['motivo']) ?></p>
                                
                                <?php if (!empty($consulta['diagnostico'])): ?>
                                    <h4 class="font-medium text-gray-800 mt-3">Diagnóstico:</h4>
                                    <p class="text-gray-600 truncate"><?= htmlspecialchars($consulta['diagnostico']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer_medico.php'; ?>