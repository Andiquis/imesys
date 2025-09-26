<?php
session_start();

if (!isset($_SESSION['admin_loggedin'])) {
    header("Location: login_admin.php");
    exit;
}

require '../conexion.php';

// Obtener estadísticas generales del sistema
$stats = [];

// Total de médicos
$query = "SELECT COUNT(*) as total FROM medicos";
$result = $conexion->query($query);
$stats['total_medicos'] = $result->fetch_assoc()['total'];

// Total de pacientes
$query = "SELECT COUNT(*) as total FROM usuarios";
$result = $conexion->query($query);
$stats['total_pacientes'] = $result->fetch_assoc()['total'];

// Total de consultas
$query = "SELECT COUNT(*) as total FROM historial_consultas";
$result = $conexion->query($query);
$stats['total_consultas'] = $result->fetch_assoc()['total'];

// Consultas este mes
$query = "SELECT COUNT(*) as total FROM historial_consultas 
          WHERE fecha_hora >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')";
$result = $conexion->query($query);
$stats['consultas_mes'] = $result->fetch_assoc()['total'];

// Médicos más activos
$query = "SELECT m.nombre, m.apellido, COUNT(hc.id_historial) as consultas
          FROM medicos m
          LEFT JOIN historial_consultas hc ON m.id_medico = hc.id_medico
          GROUP BY m.id_medico
          ORDER BY consultas DESC
          LIMIT 5";
$result = $conexion->query($query);
$medicos_activos = $result->fetch_all(MYSQLI_ASSOC);

// Especialidades más solicitadas
$query = "SELECT e.nombre_especialidad, COUNT(hc.id_historial) as consultas
          FROM especialidades e
          LEFT JOIN medicos m ON e.id_especialidad = m.id_especialidad
          LEFT JOIN historial_consultas hc ON m.id_medico = hc.id_medico
          GROUP BY e.id_especialidad
          ORDER BY consultas DESC
          LIMIT 5";
$result = $conexion->query($query);
$especialidades_populares = $result->fetch_all(MYSQLI_ASSOC);

// Crecimiento mensual
$query = "SELECT 
            DATE_FORMAT(fecha_hora, '%Y-%m') as mes,
            COUNT(*) as consultas
          FROM historial_consultas
          WHERE fecha_hora >= DATE_SUB(CURRENT_DATE, INTERVAL 5 MONTH)
          GROUP BY DATE_FORMAT(fecha_hora, '%Y-%m')
          ORDER BY mes ASC";
$result = $conexion->query($query);
$crecimiento_mensual = $result->fetch_all(MYSQLI_ASSOC);

// Preparar datos para gráficos
$meses = [];
$consultas_por_mes = [];
foreach ($crecimiento_mensual as $mes) {
    $meses[] = date('M Y', strtotime($mes['mes']));
    $consultas_por_mes[] = $mes['consultas'];
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Admin Dashboard</title>
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
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="sidebar w-64 bg-blue-800 text-white">
            <div class="p-4 border-b border-blue-700">
                <h1 class="text-xl font-bold">IMESYS Admin</h1>
                <p class="text-sm text-blue-200">Panel de Administración</p>
            </div>
            <nav class="p-4">
                <div class="space-y-2">
                    <a href="dashboard_admin.php" class="flex items-center space-x-2 px-4 py-2 bg-blue-700 rounded-lg sidebar-item">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="registro_medico.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg sidebar-item">
                        <i class="fas fa-user-md"></i>
                        <span>Médicos</span>
                    </a>
                    
                    <a href="registro_farmacia.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg sidebar-item">
                        <i class="fas fa-cog"></i>
                        <span>Farmacias</span>
                    </a>
                    <a href="logout_admin.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg sidebar-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </div>
            </nav>
            <div class="p-4 border-t border-blue-700 absolute bottom-0 w-64">
                <p class="text-xs text-blue-200">Sistema IMESYS v1.0</p>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="flex-1 overflow-auto">
            <!-- Barra superior -->
            <header class="bg-white shadow-sm">
                <div class="flex justify-between items-center px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
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
                <!-- Tarjetas resumen -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Médicos -->
                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500 card-hover transition duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Médicos Registrados</p>
                                <p class="text-2xl font-bold text-gray-800"><?= $stats['total_medicos'] ?></p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-full">
                                <i class="fas fa-user-md text-blue-600"></i>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-gray-500">
                            <a href="gestion_medicos.php" class="text-blue-600 hover:text-blue-800">Ver todos →</a>
                        </div>
                    </div>

                    <!-- Total Pacientes -->
                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500 card-hover transition duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Pacientes Registrados</p>
                                <p class="text-2xl font-bold text-gray-800"><?= $stats['total_pacientes'] ?></p>
                            </div>
                            <div class="bg-green-100 p-3 rounded-full">
                                <i class="fas fa-users text-green-600"></i>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-gray-500">
                            <a href="gestion_pacientes.php" class="text-green-600 hover:text-green-800">Ver todos →</a>
                        </div>
                    </div>

                    <!-- Total Consultas -->
                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500 card-hover transition duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Consultas Totales</p>
                                <p class="text-2xl font-bold text-gray-800"><?= $stats['total_consultas'] ?></p>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-full">
                                <i class="fas fa-stethoscope text-purple-600"></i>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-gray-500">
                            <span class="text-green-500"><?= $stats['consultas_mes'] ?> este mes</span>
                        </div>
                    </div>

                    <!-- Consultas/Mes -->
                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500 card-hover transition duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Consultas/Mes</p>
                                <p class="text-2xl font-bold text-gray-800"><?= round($stats['total_consultas'] / max(1, date('n')), 1) ?></p>
                            </div>
                            <div class="bg-yellow-100 p-3 rounded-full">
                                <i class="fas fa-chart-line text-yellow-600"></i>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-gray-500">
                            <span><?= date('F Y') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Gráficos y tablas -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Gráfico de crecimiento -->
                    <div class="bg-white rounded-lg shadow-md p-6 lg:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Crecimiento Mensual</h3>
                        <canvas id="crecimientoChart" height="250"></canvas>
                    </div>

                    <!-- Médicos más activos -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Médicos más Activos</h3>
                        <div class="space-y-4">
                            <?php if (empty($medicos_activos)): ?>
                                <p class="text-gray-500 text-center py-4">No hay datos disponibles</p>
                            <?php else: ?>
                                <?php foreach ($medicos_activos as $medico): ?>
                                    <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="bg-blue-100 p-2 rounded-full mr-3">
                                                <i class="fas fa-user-md text-blue-600"></i>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-800"><?= htmlspecialchars($medico['nombre'] . ' ' . htmlspecialchars($medico['apellido'])) ?></p>
                                                <p class="text-sm text-gray-500"><?= $medico['consultas'] ?> consultas</p>
                                            </div>
                                        </div>
                                        <a href="#" class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Especialidades y actividad -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Especialidades populares -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Especialidades Populares</h3>
                        <div class="space-y-4">
                            <?php if (empty($especialidades_populares)): ?>
                                <p class="text-gray-500 text-center py-4">No hay datos disponibles</p>
                            <?php else: ?>
                                <?php foreach ($especialidades_populares as $especialidad): ?>
                                    <div>
                                        <div class="flex justify-between text-sm mb-1">
                                            <span class="font-medium text-gray-700"><?= htmlspecialchars($especialidad['nombre_especialidad']) ?></span>
                                            <span class="text-gray-600"><?= $especialidad['consultas'] ?></span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" 
                                                 style="width: <?= ($especialidad['consultas'] / max(array_column($especialidades_populares, 'consultas'))) * 100 ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Actividad reciente -->
                    <div class="bg-white rounded-lg shadow-md p-6 lg:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Actividad Reciente</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <!-- Ejemplo estático - deberías conectar con tu tabla de logs -->
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">Dr. Juan Pérez</td>
                                        <td class="px-6 py-4 whitespace-nowrap">Nueva consulta registrada</td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?= date('d/m/Y H:i', strtotime('-1 hour')) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">María López</td>
                                        <td class="px-6 py-4 whitespace-nowrap">Registro de paciente</td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?= date('d/m/Y H:i', strtotime('-3 hours')) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">Sistema</td>
                                        <td class="px-6 py-4 whitespace-nowrap">Respaldo automático</td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?= date('d/m/Y H:i', strtotime('-1 day')) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Gráfico de crecimiento
        const ctx = document.getElementById('crecimientoChart').getContext('2d');
        const crecimientoChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($meses) ?>,
                datasets: [{
                    label: 'Consultas por mes',
                    data: <?= json_encode($consultas_por_mes) ?>,
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
    </script>
</body>
</html>