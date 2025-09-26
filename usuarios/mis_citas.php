<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: login_usuario.php");
    exit;
}

require 'conexion.php';

$id_usuario = $_SESSION['id_usuario'];

// Filtro por fechas (si se envía el formulario)
$filtro_fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$filtro_fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

// Consulta base con JOINs para obtener datos completos
$query = "
    SELECT 
        c.id_cita, c.fecha_cita, c.estado, c.motivo, c.respuesta,
        m.nombre AS medico_nombre, m.apellido AS medico_apellido,
        e.nombre_especialidad,
        m.direccion_consultorio, m.telefono AS medico_telefono
    FROM citas c
    JOIN medicos m ON c.id_medico = m.id_medico
    JOIN especialidades e ON m.id_especialidad = e.id_especialidad
    WHERE c.id_usuario = ?
";

// Aplicar filtro de fechas si existen
if (!empty($filtro_fecha_inicio)) {
    $query .= " AND DATE(c.fecha_cita) >= ?";
}
if (!empty($filtro_fecha_fin)) {
    $query .= " AND DATE(c.fecha_cita) <= ?";
}

$query .= " ORDER BY c.fecha_cita DESC";

$stmt = $conexion->prepare($query);

// Bind parameters dinámicamente según filtros
if (!empty($filtro_fecha_inicio) && !empty($filtro_fecha_fin)) {
    $stmt->bind_param("iss", $id_usuario, $filtro_fecha_inicio, $filtro_fecha_fin);
} elseif (!empty($filtro_fecha_inicio)) {
    $stmt->bind_param("is", $id_usuario, $filtro_fecha_inicio);
} elseif (!empty($filtro_fecha_fin)) {
    $stmt->bind_param("is", $id_usuario, $filtro_fecha_fin);
} else {
    $stmt->bind_param("i", $id_usuario);
}

$stmt->execute();
$citas = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Mis Citas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media (min-width: 1024px) {
            .content-area {
                margin-left: 270px !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Barra superior y sidebar (usar tu estructura actual) -->
<!-- Barra superior -->
    <?php include 'header_usuarios.php'; ?>
    <div class="content-area pt-20">
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold mb-6">Mis Citas</h1>

            <!-- Filtro por fechas -->
            <form method="GET" class="mb-6 bg-white p-4 rounded-lg shadow-md">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha inicial</label>
                        <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($filtro_fecha_inicio) ?>" 
                               class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha final</label>
                        <input type="date" name="fecha_fin" value="<?= htmlspecialchars($filtro_fecha_fin) ?>" 
                               class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            <i class="fas fa-filter mr-2"></i> Filtrar
                        </button>
                        <?php if (!empty($filtro_fecha_inicio) || !empty($filtro_fecha_fin)): ?>
                            <a href="ver_citas_paciente.php" class="ml-2 text-gray-600 hover:text-gray-800">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>

            <!-- Tabla de citas -->
            <div class="bg-white rounded-lg shadow-md overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Médico</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Especialidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Fecha/Hora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Motivo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Comentarios del Médico</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($cita = $citas->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?= $cita['id_cita'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?= htmlspecialchars($cita['medico_nombre'] . ' ' . $cita['medico_apellido']) ?>
                                <br>
                                <span class="text-gray-500 text-xs">
                                    <?= htmlspecialchars($cita['direccion_consultorio']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?= htmlspecialchars($cita['nombre_especialidad']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?= date('d/m/Y H:i', strtotime($cita['fecha_cita'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 rounded-full text-xs 
                                    <?= $cita['estado'] == 'Confirmada' ? 'bg-green-100 text-green-800' : 
                                       ($cita['estado'] == 'Cancelada' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                    <?= ucfirst($cita['estado']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?= htmlspecialchars($cita['motivo']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?= nl2br(htmlspecialchars($cita['respuesta'] ?? 'Sin comentarios')) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if ($cita['estado'] == 'Confirmada'): ?>
                                    <a href="ver_cita_confirmada.php?id=<?= $cita['id_cita'] ?>" 
                                       class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 inline-flex items-center">
                                        <i class="fas fa-print mr-2"></i> Imprimir
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
<?php include 'footer_usuario.php'; ?>

</body>
</html>