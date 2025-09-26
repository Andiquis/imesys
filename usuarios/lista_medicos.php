<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin'])) {
    header("Location: login_usuario.php");
    exit;
}

require 'conexion.php';

// Obtener todas las especialidades para el filtro
$especialidades = [];
$stmt_especialidades = $conexion->prepare("SELECT id_especialidad, nombre_especialidad FROM especialidades ORDER BY nombre_especialidad");
$stmt_especialidades->execute();
$result_especialidades = $stmt_especialidades->get_result();
while ($row = $result_especialidades->fetch_assoc()) {
    $especialidades[$row['id_especialidad']] = $row['nombre_especialidad'];
}
$stmt_especialidades->close();

// Obtener parámetro de filtro
$filtro_especialidad = isset($_GET['especialidad']) ? intval($_GET['especialidad']) : 0;

// Consulta para obtener médicos
if ($filtro_especialidad > 0) {
    $stmt = $conexion->prepare("
        SELECT m.id_medico, m.nombre, m.apellido, m.foto, m.telefono, m.direccion_consultorio, 
               m.numero_colegiatura, e.nombre_especialidad 
        FROM medicos m
        JOIN especialidades e ON m.id_especialidad = e.id_especialidad
        WHERE m.id_especialidad = ?
        ORDER BY m.nombre, m.apellido
    ");
    $stmt->bind_param("i", $filtro_especialidad);
} else {
    $stmt = $conexion->prepare("
        SELECT m.id_medico, m.nombre, m.apellido, m.foto, m.telefono, m.direccion_consultorio, 
               m.numero_colegiatura, e.nombre_especialidad 
        FROM medicos m
        JOIN especialidades e ON m.id_especialidad = e.id_especialidad
        ORDER BY e.nombre_especialidad, m.nombre, m.apellido
    ");
}

$stmt->execute();
$result = $stmt->get_result();
$medicos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Listado de Médicos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .doctor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .specialty-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
</head>
<body class="bg-gray-100">
    
 <!-- Barra superior -->
    <?php include 'header_usuarios.php'; ?>
    <!-- Contenido principal -->
    
        <div class="container mx-auto px-4 py-8">
            <!-- Encabezado y filtro -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Nuestros Médicos Especialistas</h1>
                
                <div class="w-full md:w-auto">
                    <form method="GET" action="lista_medicos.php" class="flex items-center">
                        <label for="especialidad" class="mr-2 text-gray-700">Filtrar por:</label>
                        <select name="especialidad" id="especialidad" onchange="this.form.submit()" 
                                class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                            <option value="0">Todas las especialidades</option>
                            <?php foreach ($especialidades as $id => $nombre): ?>
                                <option value="<?= $id ?>" <?= $filtro_especialidad == $id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($nombre) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Resultados del filtro -->
            <?php if ($filtro_especialidad > 0 && isset($especialidades[$filtro_especialidad])): ?>
                <div class="bg-blue-50 text-blue-800 p-4 rounded-lg mb-6">
                    Mostrando médicos de: <strong><?= htmlspecialchars($especialidades[$filtro_especialidad]) ?></strong>
                    <a href="lista_medicos.php" class="text-blue-600 hover:text-blue-800 ml-4">
                        <i class="fas fa-times"></i> Limpiar filtro
                    </a>
                </div>
            <?php endif; ?>

            <!-- Listado de médicos -->
            <?php if (empty($medicos)): ?>
                <div class="bg-white rounded-lg shadow p-6 text-center">
                    <i class="fas fa-user-md text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700">No se encontraron médicos</h3>
                    <p class="text-gray-500 mt-2">No hay médicos registrados<?= $filtro_especialidad > 0 ? ' para esta especialidad' : '' ?></p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <?php foreach ($medicos as $medico): ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden doctor-card transition duration-300 relative">
                            <span class="specialty-badge bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                <?= htmlspecialchars($medico['nombre_especialidad']) ?>
                            </span>
                            
                            <!-- Foto del médico -->
<div class="h-48 bg-gray-200 flex items-center justify-center overflow-hidden">
    <?php 
    // Definir URL base del sitio
    $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/proyectoimesys/';
    
    $ruta_foto = '';
    $imagen_existe = false;

    if (!empty($medico['foto'])) {
        // Obtener solo el nombre del archivo por si viene una ruta larga desde la BD
        $nombre_archivo = basename($medico['foto']);
        $ruta_foto = $base_url . 'uploads/medicos/' . $nombre_archivo;
        $ruta_absoluta = $_SERVER['DOCUMENT_ROOT'] . '/proyectoimesys/uploads/medicos/' . $nombre_archivo;
        $imagen_existe = file_exists($ruta_absoluta);
    }
    ?>

    <?php if (!empty($medico['foto']) && $imagen_existe): ?>
        <img src="<?= $ruta_foto ?>" 
             alt="<?= htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']) ?>" 
             class="w-full h-full object-cover">
    <?php else: ?>
        <div class="text-gray-400 text-6xl">
            <i class="fas fa-user-md"></i>
        </div>
    <?php endif; ?>
</div>

                            
                            <!-- Información del médico -->
                            <div class="p-4">
                                <h3 class="text-xl font-bold text-gray-800">
                                    <?= htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']) ?>
                                </h3>
                                
                                <div class="mt-2 text-sm text-gray-600">
                                    <p class="flex items-center">
                                        <i class="fas fa-id-card-alt mr-2 text-blue-500"></i>
                                        <span class="font-medium">Colegiatura:</span> 
                                        <?= htmlspecialchars($medico['numero_colegiatura']) ?>
                                    </p>
                                    
                                    <p class="flex items-center mt-1">
                                        <i class="fas fa-phone-alt mr-2 text-blue-500"></i>
                                        <span class="font-medium">Teléfono:</span> 
                                        <?= htmlspecialchars($medico['telefono'] ?? 'No disponible') ?>
                                    </p>
                                    
                                    <p class="flex items-start mt-1">
                                        <i class="fas fa-map-marker-alt mr-2 text-blue-500 mt-1"></i>
                                        <span>
                                            <span class="font-medium">Consultorio:</span> 
                                            <?= htmlspecialchars($medico['direccion_consultorio'] ?? 'No disponible') ?>
                                        </span>
                                    </p>
                                </div>
                                
                                <div class="mt-4 flex justify-between items-center">
                                <a href="reservar_cita.php?id_medico=<?= $medico['id_medico'] ?>" 
   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
    <i class="fas fa-calendar-check mr-1"></i> Reservar cita
</a>
                                    
                                    <a href="ver_perfil_medico.php?id=<?= $medico['id_medico'] ?>" 
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Ver perfil
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php include 'footer_usuario.php'; ?>

    <!-- Scripts (igual que en tu página principal) -->
    <script>
        // ... (copia los mismos scripts de tu página principal para el sidebar, etc.) ...
        
        // Función para el filtro responsive
        document.getElementById('especialidad').addEventListener('change', function() {
            if (window.innerWidth < 768) {
                this.form.submit();
            }
        });
    </script>
</body>
</html>