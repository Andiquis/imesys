<?php
session_start();

if (!isset($_SESSION['admin_loggedin'])) {
    header("Location: login_admin.php");
    exit;
}

require '../conexion.php';

// Obtener parámetros de filtro
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');
$especialidad_id = isset($_GET['especialidad']) ? $_GET['especialidad'] : '';
$medico_id = isset($_GET['medico']) ? $_GET['medico'] : '';

// Obtener listas para filtros
$especialidades = [];
$query = "SELECT id_especialidad, nombre_especialidad FROM especialidades ORDER BY nombre_especialidad";
$result = $conexion->query($query);
$especialidades = $result->fetch_all(MYSQLI_ASSOC);

$medicos = [];
$query = "SELECT id_medico, nombre, apellido FROM medicos ORDER BY apellido, nombre";
$result = $conexion->query($query);
$medicos = $result->fetch_all(MYSQLI_ASSOC);

// Consulta para reporte de consultas por especialidad
$query_consultas_especialidad = "SELECT 
    e.nombre_especialidad,
    COUNT(hc.id_historial) as total_consultas
FROM especialidades e
LEFT JOIN medicos m ON e.id_especialidad = m.id_especialidad
LEFT JOIN historial_consultas hc ON m.id_medico = hc.id_medico
    AND hc.fecha_hora BETWEEN ? AND ?
";

if (!empty($especialidad_id)) {
    $query_consultas_especialidad .= " AND e.id_especialidad = ?";
}

if (!empty($medico_id)) {
    $query_consultas_especialidad .= " AND m.id_medico = ?";
}

$query_consultas_especialidad .= " GROUP BY e.id_especialidad ORDER BY total_consultas DESC";

$stmt = $conexion->prepare($query_consultas_especialidad);
$params = [$fecha_inicio . ' 00:00:00', $fecha_fin . ' 23:59:59'];
$types = "ss";

if (!empty($especialidad_id)) {
    $params[] = $especialidad_id;
    $types .= "i";
}

if (!empty($medico_id)) {
    $params[] = $medico_id;
    $types .= "i";
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$consultas_por_especialidad = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Consulta para reporte de consultas por médico
$query_consultas_medico = "SELECT 
    CONCAT(m.nombre, ' ', m.apellido) as medico,
    e.nombre_especialidad,
    COUNT(hc.id_historial) as total_consultas
FROM medicos m
LEFT JOIN especialidades e ON m.id_especialidad = e.id_especialidad
LEFT JOIN historial_consultas hc ON m.id_medico = hc.id_medico
    AND hc.fecha_hora BETWEEN ? AND ?
";

if (!empty($especialidad_id)) {
    $query_consultas_medico .= " AND e.id_especialidad = ?";
}

if (!empty($medico_id)) {
    $query_consultas_medico .= " AND m.id_medico = ?";
}

$query_consultas_medico .= " GROUP BY m.id_medico ORDER BY total_consultas DESC LIMIT 10";

$stmt = $conexion->prepare($query_consultas_medico);
$params = [$fecha_inicio . ' 00:00:00', $fecha_fin . ' 23:59:59'];
$types = "ss";

if (!empty($especialidad_id)) {
    $params[] = $especialidad_id;
    $types .= "i";
}

if (!empty($medico_id)) {
    $params[] = $medico_id;
    $types .= "i";
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$consultas_por_medico = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Consulta para reporte de crecimiento mensual
$query_crecimiento = "SELECT 
    DATE_FORMAT(fecha_hora, '%Y-%m') as mes,
    COUNT(*) as total_consultas
FROM historial_consultas
WHERE fecha_hora BETWEEN ? AND ?
";

if (!empty($especialidad_id)) {
    $query_crecimiento .= " AND id_medico IN (SELECT id_medico FROM medicos WHERE id_especialidad = ?)";
}

if (!empty($medico_id)) {
    $query_crecimiento .= " AND id_medico = ?";
}

$query_crecimiento .= " GROUP BY DATE_FORMAT(fecha_hora, '%Y-%m') ORDER BY mes";

$stmt = $conexion->prepare($query_crecimiento);
$params = [$fecha_inicio . ' 00:00:00', $fecha_fin . ' 23:59:59'];
$types = "ss";

if (!empty($especialidad_id)) {
    $params[] = $especialidad_id;
    $types .= "i";
}

if (!empty($medico_id)) {
    $params[] = $medico_id;
    $types .= "i";
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$crecimiento_mensual = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Consulta para reporte de pacientes nuevos
$query_pacientes_nuevos = "SELECT 
    DATE_FORMAT(fecha_registro, '%Y-%m') as mes,
    COUNT(*) as total_pacientes
FROM usuarios
WHERE fecha_registro BETWEEN ? AND ?
GROUP BY DATE_FORMAT(fecha_registro, '%Y-%m') 
ORDER BY mes";

$stmt = $conexion->prepare($query_pacientes_nuevos);
$fecha_inicio_completa = $fecha_inicio . ' 00:00:00';
$fecha_fin_completa = $fecha_fin . ' 23:59:59';

$stmt->bind_param("ss", $fecha_inicio_completa, $fecha_fin_completa);


$stmt->execute();
$pacientes_nuevos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conexion->close();

// Preparar datos para gráficos
$meses = [];
$consultas_mensuales = [];
$pacientes_mensuales = [];

foreach ($crecimiento_mensual as $mes) {
    $meses[] = date('M Y', strtotime($mes['mes']));
    $consultas_mensuales[] = $mes['total_consultas'];
}

foreach ($pacientes_nuevos as $mes) {
    $pacientes_mensuales[] = $mes['total_pacientes'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Reportes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar {
            transition: all 0.3s ease;
        }
        .sidebar-item:hover {
            background-color: #ebf4ff;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .filter-box {
            transition: all 0.3s ease;
        }
        .filter-box:focus-within {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include 'sidebar_admin.php'; ?>

        <!-- Contenido principal -->
        <div class="flex-1 overflow-auto">
            <!-- Barra superior -->
            <header class="bg-white shadow-sm">
                <div class="flex justify-between items-center px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">Reportes del Sistema</h2>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <i class="fas fa-bell text-gray-500"></i>
                            <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
                        </div>
                        <div class="flex items-center">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['admin_nombre']) ?>&background=3B82F6&color=fff" 
                                 alt="Admin" class="h-8 w-8 rounded-full">
                            <span class="ml-2 text-sm font-medium"><?= htmlspecialchars($_SESSION['admin_nombre']) ?></span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Contenido -->
            <main class="p-6">
                <!-- Filtros -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <form method="GET" class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Filtrar Reportes</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Fecha inicio -->
                            <div class="filter-box">
                                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                                <input type="date" id="fecha_inicio" name="fecha_inicio" 
                                       value="<?= htmlspecialchars($fecha_inicio) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <!-- Fecha fin -->
                            <div class="filter-box">
                                <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                                <input type="date" id="fecha_fin" name="fecha_fin" 
                                       value="<?= htmlspecialchars($fecha_fin) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <!-- Especialidad -->
                            <div class="filter-box">
                                <label for="especialidad" class="block text-sm font-medium text-gray-700 mb-1">Especialidad</label>
                                <select id="especialidad" name="especialidad" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Todas las especialidades</option>
                                    <?php foreach ($especialidades as $esp): ?>
                                        <option value="<?= $esp['id_especialidad'] ?>" <?= ($especialidad_id == $esp['id_especialidad']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($esp['nombre_especialidad']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Médico -->
                            <div class="filter-box">
                                <label for="medico" class="block text-sm font-medium text-gray-700 mb-1">Médico</label>
                                <select id="medico" name="medico" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Todos los médicos</option>
                                    <?php foreach ($medicos as $med): ?>
                                        <option value="<?= $med['id_medico'] ?>" <?= ($medico_id == $med['id_medico']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($med['nombre'] . ' ' . $med['apellido']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-2">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition flex items-center">
                                <i class="fas fa-filter mr-2"></i> Aplicar Filtros
                            </button>
                            <a href="reportes.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg transition flex items-center">
                                <i class="fas fa-times mr-2"></i> Limpiar
                            </a>
                            <button type="button" onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition flex items-center">
                                <i class="fas fa-print mr-2"></i> Imprimir
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Resumen de filtros -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 pt-0.5">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Reporte generado con los siguientes filtros:</h3>
                            <div class="mt-1 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Período: <?= date('d/m/Y', strtotime($fecha_inicio)) ?> al <?= date('d/m/Y', strtotime($fecha_fin)) ?></li>
                                    <?php if (!empty($especialidad_id)): 
                                        $esp_seleccionada = array_filter($especialidades, function($e) use ($especialidad_id) {
                                            return $e['id_especialidad'] == $especialidad_id;
                                        });
                                        $esp_seleccionada = reset($esp_seleccionada);
                                    ?>
                                        <li>Especialidad: <?= htmlspecialchars($esp_seleccionada['nombre_especialidad']) ?></li>
                                    <?php endif; ?>
                                    <?php if (!empty($medico_id)): 
                                        $med_seleccionado = array_filter($medicos, function($m) use ($medico_id) {
                                            return $m['id_medico'] == $medico_id;
                                        });
                                        $med_seleccionado = reset($med_seleccionado);
                                    ?>
                                        <li>Médico: <?= htmlspecialchars($med_seleccionado['nombre'] . ' ' . $med_seleccionado['apellido']) ?></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos principales -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Consultas por especialidad -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Consultas por Especialidad</h3>
                        <?php if (empty($consultas_por_especialidad)): ?>
                            <p class="text-gray-500 text-center py-4">No hay datos con los filtros seleccionados</p>
                        <?php else: ?>
                            <canvas id="especialidadChart" height="250"></canvas>
                            <div class="mt-4 overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Especialidad</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consultas</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Porcentaje</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php 
                                        $total_consultas = array_sum(array_column($consultas_por_especialidad, 'total_consultas'));
                                        foreach ($consultas_por_especialidad as $especialidad): 
                                            $porcentaje = $total_consultas > 0 ? ($especialidad['total_consultas'] / $total_consultas) * 100 : 0;
                                        ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($especialidad['nombre_especialidad']) ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap"><?= $especialidad['total_consultas'] ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="w-20 bg-gray-200 rounded-full h-2.5 mr-2">
                                                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?= $porcentaje ?>%"></div>
                                                        </div>
                                                        <span><?= round($porcentaje, 1) ?>%</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Top médicos -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Top 10 Médicos con más Consultas</h3>
                        <?php if (empty($consultas_por_medico)): ?>
                            <p class="text-gray-500 text-center py-4">No hay datos con los filtros seleccionados</p>
                        <?php else: ?>
                            <canvas id="medicoChart" height="250"></canvas>
                            <div class="mt-4 space-y-3">
                                <?php foreach ($consultas_por_medico as $medico): ?>
                                    <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg">
                                        <div>
                                            <p class="font-medium text-gray-800"><?= htmlspecialchars($medico['medico']) ?></p>
                                            <p class="text-sm text-gray-500"><?= htmlspecialchars($medico['nombre_especialidad']) ?></p>
                                        </div>
                                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                            <?= $medico['total_consultas'] ?> consultas
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Crecimiento y pacientes -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Crecimiento mensual -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Crecimiento Mensual de Consultas</h3>
                        <?php if (empty($crecimiento_mensual)): ?>
                            <p class="text-gray-500 text-center py-4">No hay datos con los filtros seleccionados</p>
                        <?php else: ?>
                            <canvas id="crecimientoChart" height="250"></canvas>
                        <?php endif; ?>
                    </div>

                    <!-- Pacientes nuevos -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Registro de Nuevos Pacientes</h3>
                        <?php if (empty($pacientes_nuevos)): ?>
                            <p class="text-gray-500 text-center py-4">No hay datos con los filtros seleccionados</p>
                        <?php else: ?>
                            <canvas id="pacientesChart" height="250"></canvas>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Reporte detallado -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Reporte Detallado</h3>
                        <button onclick="exportToExcel()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition flex items-center">
                            <i class="fas fa-file-excel mr-2"></i> Exportar a Excel
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table id="reporteDetallado" class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Médico</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Especialidad</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <!-- Ejemplo estático - deberías conectar con tu base de datos -->
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= date('d/m/Y H:i', strtotime('-2 days')) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">Juan Pérez</td>
                                    <td class="px-6 py-4 whitespace-nowrap">Dr. Carlos Mendoza</td>
                                    <td class="px-6 py-4 whitespace-nowrap">Cardiología</td>
                                    <td class="px-6 py-4">Dolor en el pecho</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= date('d/m/Y H:i', strtotime('-1 week')) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">María López</td>
                                    <td class="px-6 py-4 whitespace-nowrap">Dra. Ana García</td>
                                    <td class="px-6 py-4 whitespace-nowrap">Pediatría</td>
                                    <td class="px-6 py-4">Control niño sano</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Gráfico de consultas por especialidad
        <?php if (!empty($consultas_por_especialidad)): ?>
        const ctxEspecialidad = document.getElementById('especialidadChart').getContext('2d');
        const especialidadChart = new Chart(ctxEspecialidad, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($consultas_por_especialidad, 'nombre_especialidad')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($consultas_por_especialidad, 'total_consultas')) ?>,
                    backgroundColor: [
                        '#3B82F6', '#10B981', '#F59E0B', '#6366F1', '#EC4899',
                        '#14B8A6', '#F97316', '#8B5CF6', '#EF4444', '#06B6D4'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });
        <?php endif; ?>

        // Gráfico de top médicos
        <?php if (!empty($consultas_por_medico)): ?>
        const ctxMedico = document.getElementById('medicoChart').getContext('2d');
        const medicoChart = new Chart(ctxMedico, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($consultas_por_medico, 'medico')) ?>,
                datasets: [{
                    label: 'Consultas',
                    data: <?= json_encode(array_column($consultas_por_medico, 'total_consultas')) ?>,
                    backgroundColor: '#3B82F6',
                    borderColor: '#2563EB',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        <?php endif; ?>

        // Gráfico de crecimiento
        <?php if (!empty($crecimiento_mensual)): ?>
        const ctxCrecimiento = document.getElementById('crecimientoChart').getContext('2d');
        const crecimientoChart = new Chart(ctxCrecimiento, {
            type: 'line',
            data: {
                labels: <?= json_encode($meses) ?>,
                datasets: [{
                    label: 'Consultas',
                    data: <?= json_encode($consultas_mensuales) ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderColor: '#3B82F6',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        <?php endif; ?>

        // Gráfico de pacientes nuevos
        <?php if (!empty($pacientes_nuevos)): ?>
        const ctxPacientes = document.getElementById('pacientesChart').getContext('2d');
        const pacientesChart = new Chart(ctxPacientes, {
            type: 'line',
            data: {
                labels: <?= json_encode($meses) ?>,
                datasets: [{
                    label: 'Pacientes nuevos',
                    data: <?= json_encode($pacientes_mensuales) ?>,
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderColor: '#10B981',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        <?php endif; ?>

        // Exportar a Excel
        function exportToExcel() {
            // Crear un libro de Excel con los datos
            // Esto es un ejemplo básico, en producción usarías una librería como SheetJS
            let html = '<html><head><meta charset="UTF-8"></head><body>';
            html += '<table>';
            
            // Encabezados
            html += '<tr>';
            document.querySelectorAll('#reporteDetallado thead th').forEach(th => {
                html += '<th>' + th.innerText + '</th>';
            });
            html += '</tr>';
            
            // Datos
            document.querySelectorAll('#reporteDetallado tbody tr').forEach(tr => {
                html += '<tr>';
                tr.querySelectorAll('td').forEach(td => {
                    html += '<td>' + td.innerText + '</td>';
                });
                html += '</tr>';
            });
            
            html += '</table></body></html>';
            
            // Descargar
            const blob = new Blob([html], {type: 'application/vnd.ms-excel'});
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'reporte_imesys_<?= date('Y-m-d') ?>.xls';
            a.click();
        }
    </script>
</body>
</html>