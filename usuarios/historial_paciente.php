<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

require 'conexion.php';

// Obtener información del usuario
$user_id = $_SESSION['id_usuario'];
$stmt_user = $conexion->prepare("SELECT nombre, apellido, dni FROM usuarios WHERE id_usuario = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$stmt_user->bind_result($nombre, $apellido, $dni);
$stmt_user->fetch();
$stmt_user->close();

// Obtener todas las especialidades para el filtro
$stmt_especialidades = $conexion->prepare("SELECT id_especialidad, nombre_especialidad FROM especialidades ORDER BY nombre_especialidad");
$stmt_especialidades->execute();
$especialidades = $stmt_especialidades->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_especialidades->close();

// Obtener parámetros de filtro
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
$especialidad_id = isset($_GET['especialidad']) ? $_GET['especialidad'] : '';

// Construir la consulta con filtros
$query = "
    SELECT 
        hc.id_historial,
        m.nombre AS medico_nombre,
        m.apellido AS medico_apellido,
        e.id_especialidad,
        e.nombre_especialidad,
        hc.motivo,
        hc.diagnostico,
        hc.tratamiento,
        hc.fecha_hora
    FROM 
        historial_consultas hc
    JOIN 
        medicos m ON hc.id_medico = m.id_medico
    JOIN 
        especialidades e ON m.id_especialidad = e.id_especialidad
    WHERE 
        hc.id_usuario = ?
";

$params = array($user_id);
$types = "i";

// Añadir condiciones de filtro
if (!empty($fecha_inicio)) {
    $query .= " AND DATE(hc.fecha_hora) >= ?";
    $params[] = $fecha_inicio;
    $types .= "s";
}

if (!empty($fecha_fin)) {
    $query .= " AND DATE(hc.fecha_hora) <= ?";
    $params[] = $fecha_fin;
    $types .= "s";
}

if (!empty($especialidad_id)) {
    $query .= " AND e.id_especialidad = ?";
    $params[] = $especialidad_id;
    $types .= "i";
}

$query .= " ORDER BY hc.fecha_hora DESC";

// Preparar y ejecutar la consulta
$stmt = $conexion->prepare($query);
if ($types != "i") {
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param($types, $params[0]);
}
$stmt->execute();
$result = $stmt->get_result();
$historial = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Historial Médico</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .historial-card {
            transition: all 0.3s ease;
        }
        .historial-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .filter-box {
            transition: all 0.3s ease;
        }
        .filter-box:focus-within {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        @media (min-width: 1024px) {
            .container.main-content-area {
                margin-left: 270px !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Barra superior -->
    <?php include 'header_usuarios.php'; ?>
    <br><br>

    <!-- Contenido principal -->
    <div class="container main-content-area mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Encabezado -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Historial Médico</h1>
                    <p class="text-gray-600">Consulta tus registros médicos anteriores</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <div class="flex items-center bg-white rounded-lg shadow-sm px-4 py-2">
                        <span class="text-gray-500 mr-2">
                            <i class="fas fa-user"></i>
                        </span>
                        <div>
                            <p class="font-medium text-gray-700"><?= htmlspecialchars($nombre . ' ' . $apellido) ?></p>
                            <p class="text-sm text-gray-500">DNI: <?= htmlspecialchars($dni) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <form id="filterForm" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Filtro por fechas -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-3">Filtrar por fecha</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="filter-box">
                                    <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                                    <input type="date" id="fecha_inicio" name="fecha_inicio" 
                                           value="<?= htmlspecialchars($fecha_inicio) ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="filter-box">
                                    <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                                    <input type="date" id="fecha_fin" name="fecha_fin" 
                                           value="<?= htmlspecialchars($fecha_fin) ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Filtro por especialidad -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-3">Filtrar por especialidad</h3>
                            <div class="filter-box">
                                <select id="especialidad" name="especialidad" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Todas las especialidades</option>
                                    <?php foreach ($especialidades as $esp): ?>
                                        <option value="<?= $esp['id_especialidad'] ?>" <?= ($especialidad_id == $esp['id_especialidad']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($esp['nombre_especialidad']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2 pt-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition flex items-center">
                            <i class="fas fa-filter mr-2"></i> Aplicar Filtros
                        </button>
                        <button type="button" id="resetFilter" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg transition flex items-center">
                            <i class="fas fa-times mr-2"></i> Limpiar Filtros
                        </button>
                    </div>
                </form>
            </div>

            <!-- Resumen de filtros aplicados -->
            <?php if (!empty($fecha_inicio) || !empty($fecha_fin) || !empty($especialidad_id)): ?>
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded">
                <div class="flex items-start">
                    <div class="flex-shrink-0 pt-0.5">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Filtros aplicados:</h3>
                        <div class="mt-1 text-sm text-blue-700">
                            <ul class="list-disc list-inside space-y-1">
                                <?php if (!empty($fecha_inicio) && !empty($fecha_fin)): ?>
                                    <li>Período: <?= date('d/m/Y', strtotime($fecha_inicio)) ?> al <?= date('d/m/Y', strtotime($fecha_fin)) ?></li>
                                <?php elseif (!empty($fecha_inicio)): ?>
                                    <li>Desde: <?= date('d/m/Y', strtotime($fecha_inicio)) ?></li>
                                <?php elseif (!empty($fecha_fin)): ?>
                                    <li>Hasta: <?= date('d/m/Y', strtotime($fecha_fin)) ?></li>
                                <?php endif; ?>
                                <?php if (!empty($especialidad_id)): 
                                    $esp_seleccionada = array_filter($especialidades, function($e) use ($especialidad_id) {
                                        return $e['id_especialidad'] == $especialidad_id;
                                    });
                                    $esp_seleccionada = reset($esp_seleccionada);
                                ?>
                                    <li>Especialidad: <?= htmlspecialchars($esp_seleccionada['nombre_especialidad']) ?></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Lista de historial -->
            <div class="space-y-4">
                <?php if (count($historial) > 0): ?>
                    <?php foreach ($historial as $consulta): ?>
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden historial-card">
                            <div class="p-6">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800">
                                            <?= htmlspecialchars($consulta['medico_nombre'] . ' ' . htmlspecialchars($consulta['medico_apellido'])) ?>
                                        </h3>
                                        <p class="text-blue-600"><?= htmlspecialchars($consulta['nombre_especialidad']) ?></p>
                                    </div>
                                    <div class="mt-2 md:mt-0">
                                        <span class="inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm">
                                            <?= date('d/m/Y H:i', strtotime($consulta['fecha_hora'])) ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Motivo</h4>
                                        <p class="mt-1 text-gray-800"><?= nl2br(htmlspecialchars($consulta['motivo'])) ?></p>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Diagnóstico</h4>
                                        <p class="mt-1 text-gray-800"><?= nl2br(htmlspecialchars($consulta['diagnostico'])) ?></p>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Tratamiento</h4>
                                        <p class="mt-1 text-gray-800"><?= nl2br(htmlspecialchars($consulta['tratamiento'])) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                        <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-file-medical text-3xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-700 mb-2">
                            <?= (!empty($fecha_inicio) || !empty($fecha_fin) || !empty($especialidad_id)) ? 
                               'No hay registros con los filtros seleccionados' : 
                               'No hay registros médicos' ?>
                        </h3>
                        <p class="text-gray-500">
                            <?= (!empty($fecha_inicio) || !empty($fecha_fin) || !empty($especialidad_id)) ? 
                               'Intenta con otros criterios de búsqueda' : 
                               'Aún no tienes consultas registradas en tu historial médico.' ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php include 'footer_usuario.php'; ?>

    <script>
        // Limpiar filtros
        document.getElementById('resetFilter').addEventListener('click', function() {
            document.getElementById('fecha_inicio').value = '';
            document.getElementById('fecha_fin').value = '';
            document.getElementById('especialidad').value = '';
            document.getElementById('filterForm').submit();
        });

        // Validar que fecha inicio no sea mayor a fecha fin
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;
            
            if (fechaInicio && fechaFin && fechaInicio > fechaFin) {
                alert('La fecha de inicio no puede ser mayor a la fecha final');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>