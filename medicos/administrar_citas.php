<?php
session_start();
require 'conexion.php';

// Verificar si es médico o admin
if (!isset($_SESSION['loggedin']) || ($_SESSION['tipo_usuario'] != 'medico' && $_SESSION['tipo_usuario'] != 'admin')) {
    header("Location: login.php");
    exit;
}

// Obtener parámetros de filtro
$filtro_fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$filtro_fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';

// Consulta base con JOINs
$query = "
    SELECT c.*, 
           u.nombre AS paciente_nombre, u.apellido AS paciente_apellido, u.telefono AS paciente_telefono,
           m.nombre AS medico_nombre, m.apellido AS medico_apellido,
           e.nombre_especialidad
    FROM citas c
    JOIN usuarios u ON c.id_usuario = u.id_usuario
    JOIN medicos m ON c.id_medico = m.id_medico
    JOIN especialidades e ON m.id_especialidad = e.id_especialidad
    WHERE 1=1
";

// Aplicar filtros
if (!empty($filtro_fecha_inicio)) {
    $query .= " AND DATE(c.fecha_cita) >= ?";
}
if (!empty($filtro_fecha_fin)) {
    $query .= " AND DATE(c.fecha_cita) <= ?";
}
if (!empty($filtro_estado)) {
    $query .= " AND c.estado = ?";
}

$query .= " ORDER BY c.fecha_cita DESC";

$stmt = $conexion->prepare($query);

// Bind parameters dinámicamente
$types = '';
$params = [];

if (!empty($filtro_fecha_inicio)) {
    $types .= 's';
    $params[] = $filtro_fecha_inicio;
}
if (!empty($filtro_fecha_fin)) {
    $types .= 's';
    $params[] = $filtro_fecha_fin;
}
if (!empty($filtro_estado)) {
    $types .= 's';
    $params[] = $filtro_estado;
}

if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$citas = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Administrar Citas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Barra y sidebar (similar a tu estructura) -->
<?php include 'header_medico.php'; ?>
    <div class="content-area pt-20">
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold mb-6">Administración de Citas</h1>
            
            <!-- Filtros -->
            <div class="bg-white p-4 rounded-lg shadow-md mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estado</label>
                        <select name="estado" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                            <option value="">Todos los estados</option>
                            <option value="Pendiente" <?= $filtro_estado == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="Confirmada" <?= $filtro_estado == 'Confirmada' ? 'selected' : '' ?>>Confirmada</option>
                            <option value="Cancelada" <?= $filtro_estado == 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
                            <option value="Completada" <?= $filtro_estado == 'Completada' ? 'selected' : '' ?>>Completada</option>
                        </select>
                    </div>
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            <i class="fas fa-filter mr-2"></i> Filtrar
                        </button>
                        <a href="ver_citas.php" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300">
                            <i class="fas fa-times mr-2"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Tabla de citas -->
            <div class="bg-white rounded-lg shadow-md overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="py-3 px-4">ID</th>
                            <th class="py-3 px-4">Paciente</th>
                            <th class="py-3 px-4">Médico</th>
                            <th class="py-3 px-4">Fecha/Hora</th>
                            <th class="py-3 px-4">Estado</th>
                            <th class="py-3 px-4">Motivo</th>
                            <th class="py-3 px-4">Respuesta</th>
                            <th class="py-3 px-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($cita = $citas->fetch_assoc()): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4"><?= $cita['id_cita'] ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($cita['paciente_nombre'] . ' ' . $cita['paciente_apellido']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($cita['medico_nombre'] . ' ' . $cita['medico_apellido']) ?></td>
                            <td class="py-3 px-4"><?= date('d/m/Y H:i', strtotime($cita['fecha_cita'])) ?></td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 rounded-full text-xs 
                                    <?= $cita['estado'] == 'Confirmada' ? 'bg-green-100 text-green-800' : 
                                       ($cita['estado'] == 'Cancelada' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                    <?= ucfirst($cita['estado']) ?>
                                </span>
                            </td>
                            <td class="py-3 px-4"><?= htmlspecialchars($cita['motivo']) ?></td>
                            <td class="py-3 px-4">
                                <form action="actualizar_cita.php" method="POST" class="flex flex-col gap-2">
                                    <input type="hidden" name="id_cita" value="<?= $cita['id_cita'] ?>">
                                    <textarea name="respuesta" rows="2" class="border p-1 text-sm"><?= htmlspecialchars($cita['respuesta'] ?? '') ?></textarea>
                                    <select name="estado" class="border p-1 text-sm">
                                        <?php foreach (['Pendiente', 'Confirmada', 'Cancelada', 'Completada'] as $opcion): ?>
                                            <option value="<?= $opcion ?>" <?= $cita['estado'] == $opcion ? 'selected' : '' ?>>
                                                <?= $opcion ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="bg-blue-600 text-white px-2 py-1 text-xs rounded hover:bg-blue-700">
                                        <i class="fas fa-save mr-1"></i> Guardar
                                    </button>
                                </form>
                            </td>
                            <td class="py-3 px-4">
                                <?php if ($cita['estado'] == 'Confirmada'): ?>
                                    <a href="generar_comprobante.php?id=<?= $cita['id_cita'] ?>" 
                                       class="text-green-600 hover:text-green-800" title="Descargar Comprobante">
                                        <i class="fas fa-file-pdf"></i>
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
    <?php include 'footer_medico.php'; ?>
</body>
</html>