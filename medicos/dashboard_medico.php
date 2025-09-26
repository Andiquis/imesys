<?php
include 'header_medico.php';

require 'conexion.php';

// Obtener estadísticas generales
$id_medico = $_SESSION['id_medico'];

// Consultas totales del médico
$query_total_consultas = "SELECT COUNT(*) as total FROM historial_consultas WHERE id_medico = ?";
$stmt = $conexion->prepare($query_total_consultas);
$stmt->bind_param("i", $id_medico);
$stmt->execute();
$result = $stmt->get_result();
$total_consultas = $result->fetch_assoc()['total'];
$stmt->close();

// Pacientes únicos atendidos
$query_pacientes_unicos = "SELECT COUNT(DISTINCT id_usuario) as total FROM historial_consultas WHERE id_medico = ?";
$stmt = $conexion->prepare($query_pacientes_unicos);
$stmt->bind_param("i", $id_medico);
$stmt->execute();
$result = $stmt->get_result();
$total_pacientes = $result->fetch_assoc()['total'];
$stmt->close();

// Consultas este mes
$query_consultas_mes = "SELECT COUNT(*) as total FROM historial_consultas 
                        WHERE id_medico = ? 
                        AND fecha_hora >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')";
$stmt = $conexion->prepare($query_consultas_mes);
$stmt->bind_param("i", $id_medico);
$stmt->execute();
$result = $stmt->get_result();
$consultas_mes = $result->fetch_assoc()['total'];
$stmt->close();

// Consultas últimos 6 meses para el gráfico
$query_consultas_6meses = "SELECT 
                            DATE_FORMAT(fecha_hora, '%Y-%m') as mes,
                            COUNT(*) as cantidad
                           FROM historial_consultas
                           WHERE id_medico = ?
                           AND fecha_hora >= DATE_SUB(CURRENT_DATE, INTERVAL 5 MONTH)
                           GROUP BY DATE_FORMAT(fecha_hora, '%Y-%m')
                           ORDER BY mes ASC";
$stmt = $conexion->prepare($query_consultas_6meses);
$stmt->bind_param("i", $id_medico);
$stmt->execute();
$result = $stmt->get_result();
$datos_grafico = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Motivos más comunes de consulta
$query_motivos_comunes = "SELECT motivo, COUNT(*) as cantidad
                          FROM historial_consultas
                          WHERE id_medico = ?
                          GROUP BY motivo
                          ORDER BY cantidad DESC
                          LIMIT 5";
$stmt = $conexion->prepare($query_motivos_comunes);
$stmt->bind_param("i", $id_medico);
$stmt->execute();
$result = $stmt->get_result();
$motivos_comunes = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conexion->close();

// Preparar datos para el gráfico
$labels = [];
$data = [];
$meses = [
    '01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr',
    '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
    '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic'
];

// Rellenar los últimos 6 meses (incluyendo meses sin datos)
$fechas_esperadas = [];
for ($i = 5; $i >= 0; $i--) {
    $fecha = date('Y-m', strtotime("-$i months"));
    $fechas_esperadas[$fecha] = 0;
}

// Llenar con datos reales
foreach ($datos_grafico as $fila) {
    $fechas_esperadas[$fila['mes']] = $fila['cantidad'];
}

// Formatear para Chart.js
foreach ($fechas_esperadas as $mes => $cantidad) {
    $partes = explode('-', $mes);
    $labels[] = $meses[$partes[1]] . ' ' . $partes[0];
    $data[] = $cantidad;
}
?>

<!-- Contenido principal -->
<div id="contentArea" class="main-content">
    <div class="container mx-auto px-4 py-6">
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Dashboard Médico</h1>
            <div class="text-sm text-gray-500">
                <?= date('d/m/Y') ?>
            </div>
        </div>
        
        <!-- Tarjetas resumen -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total consultas -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Consultas Totales</p>
                        <p class="text-3xl font-bold text-gray-800"><?= $total_consultas ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-stethoscope text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 text-sm text-gray-500">
                    <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                    <span><?= $consultas_mes ?> este mes</span>
                </div>
            </div>
            
            <!-- Pacientes únicos -->
<div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Pacientes Atendidos</p>
            <p class="text-3xl font-bold text-gray-800"><?= $total_pacientes ?></p>
        </div>
        <div class="bg-green-100 p-3 rounded-full">
            <i class="fas fa-user-friends text-green-600 text-xl"></i>
        </div>
    </div>
    <div class="mt-4 text-sm text-gray-500">
        <i class="fas fa-user-plus text-blue-500 mr-1"></i>
        <span>
            <?php 
            if ($total_pacientes > 0) {
                echo round($total_consultas/$total_pacientes, 1) . ' consultas/paciente';
            } else {
                echo '0 consultas/paciente';
            }
            ?>
        </span>
    </div>
</div>
            <!-- Consultas este mes -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Consultas Este Mes</p>
                        <p class="text-3xl font-bold text-gray-800"><?= $consultas_mes ?></p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-calendar-check text-purple-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 text-sm text-gray-500">
                    <i class="fas fa-calendar-day text-orange-500 mr-1"></i>
                    <span><?= date('F Y') ?></span>
                </div>
            </div>
        </div>
        
        <!-- Gráfico y datos -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Gráfico de consultas -->
            <div class="bg-white rounded-lg shadow-md p-6 lg:col-span-2">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Consultas últimos 6 meses</h2>
                <canvas id="consultasChart" height="250"></canvas>
            </div>
            
            <!-- Motivos comunes -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Motivos más frecuentes</h2>
                <div class="space-y-4">
                    <?php if (empty($motivos_comunes)): ?>
                        <p class="text-gray-500 text-center py-4">No hay datos suficientes</p>
                    <?php else: ?>
                        <?php foreach ($motivos_comunes as $motivo): ?>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-700 truncate" title="<?= htmlspecialchars($motivo['motivo']) ?>">
                                        <?= htmlspecialchars(mb_strimwidth($motivo['motivo'], 0, 30, '...')) ?>
                                    </span>
                                    <span class="text-gray-600"><?= $motivo['cantidad'] ?></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" 
                                         style="width: <?= ($motivo['cantidad']) / max(array_column($motivos_comunes, 'cantidad')) * 100 ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Próximas citas -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Próximas Citas</h2>
                <a href="administrar_citas.php" class="text-blue-600 hover:text-blue-800 text-sm">
                    Ver todas <i class="fas fa-chevron-right ml-1"></i>
                </a>
            </div>
            
            <?php
            // Obtener próximas citas (ejemplo estático - deberías conectar con tu tabla de citas)
            $proximas_citas = [
                ['paciente' => 'Juan Pérez', 'fecha' => date('Y-m-d H:i:s', strtotime('+2 days'))], 
                ['paciente' => 'María López', 'fecha' => date('Y-m-d H:i:s', strtotime('+3 days'))]
            ];
            ?>
            
            <?php if (empty($proximas_citas)): ?>
                <p class="text-gray-500 text-center py-4">No hay citas próximas</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($proximas_citas as $cita): ?>
                        <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-800"><?= htmlspecialchars($cita['paciente']) ?></p>
                                <p class="text-sm text-gray-500">
                                    <i class="far fa-clock text-blue-500 mr-1"></i>
                                    <?= date('d/m/Y H:i', strtotime($cita['fecha'])) ?>
                                </p>
                            </div>
                            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Incluir Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de consultas
const ctx = document.getElementById('consultasChart').getContext('2d');
const consultasChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Consultas por mes',
            data: <?= json_encode($data) ?>,
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
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

<?php include 'footer_medico.php'; ?>