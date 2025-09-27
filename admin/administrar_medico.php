<?php
session_start();

if (!isset($_SESSION['admin_loggedin'])) {
    header("Location: login_admin.php");
    exit;
}
require_once '../conexion.php';

// Eliminar médico
if (isset($_GET['eliminar'])) {
    $id_medico = $_GET['eliminar'];
    
    $conexion->autocommit(false);
    
    try {
        $conexion->query("DELETE FROM citas WHERE id_medico = $id_medico");
        $conexion->query("DELETE FROM medicos WHERE id_medico = $id_medico");
        $conexion->commit();
        echo "<script>alert('Médico eliminado correctamente'); window.location='administrar_medicos.php';</script>";
    } catch (Exception $e) {
        $conexion->rollback();
        die("Error al eliminar médico: " . $e->getMessage());
    } finally {
        $conexion->autocommit(true);
    }
}

// Buscar médicos
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT m.*, e.nombre_especialidad 
          FROM medicos m
          JOIN especialidades e ON m.id_especialidad = e.id_especialidad";

if (!empty($search)) {
    $query .= " WHERE m.nombre LIKE '%$search%' OR e.nombre_especialidad LIKE '%$search%'";
}

$result = $conexion->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Médicos | IMESYS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            transition: all 0.3s ease;
        }
        .sidebar-item:hover {
            background-color: #ebf4ff;
        }
        .content-container {
            background-color: #f8fafc;
            min-height: calc(100vh - 60px);
        }
        .table-container {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="sidebar w-64 bg-blue-800 text-white fixed h-full">
            <div class="p-4 border-b border-blue-700">
                <h1 class="text-xl font-bold">IMESYS Admin</h1>
                <p class="text-sm text-blue-200">Panel de Administración</p>
            </div>
            
           <nav class="space-y-2">
                <a href="dashboard_admin.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg sidebar-item">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                    
                     <a href="administrar_medico.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg sidebar-item">
                        <i class="fas fa-user-md"></i>
                        <span>Médicos</span>
                    </a>
                    <a href="administrar_farmacia.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg sidebar-item">
                        <i class="fas fa-cog"></i>
                        <span>Farmacias</span>
                    </a>
                    <a href="logout_admin.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg sidebar-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </a>
            </nav>
            <div class="p-4 border-t border-blue-700 absolute bottom-0 w-64">
                <p class="text-xs text-blue-200">Sistema IMESYS v1.0</p>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="ml-64 flex-1">
            <!-- Barra superior -->
            <header class="bg-white shadow-sm">
                <div class="flex justify-between items-center px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">Administrar Médicos</h2>
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
            <div class="content-container p-6">
                <div class="max-w-7xl mx-auto">
                    <!-- Barra de búsqueda y acciones -->
                    <div class="flex justify-between items-center mb-6">
                        <form method="GET" class="flex-1 max-w-md">
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="search" 
                                    class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Buscar por nombre o especialidad..."
                                    value="<?= htmlspecialchars($search) ?>"
                                >
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </form>
                        <a href="registro_medico.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center ml-4">
                            <i class="fas fa-plus mr-2"></i>
                            Nuevo Médico
                        </a>
                    </div>

                    <!-- Tabla de médicos -->
                    <div class="table-container overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Especialidad</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Correo</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Colegiatura</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php while ($medico = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
    <?php if (!empty($medico['foto'])): ?>
        <?php 
        // Asumiendo que $medico['foto'] contiene solo el nombre del archivo
        $ruta_imagen = '../uploads/medicos/' . htmlspecialchars($medico['foto']);
        $imagen_existe = file_exists($ruta_imagen) && is_file($ruta_imagen);
        ?>
        
        <?php if ($imagen_existe): ?>
            <img class="h-10 w-10 rounded-full object-cover" 
                 src="<?= $ruta_imagen ?>" 
                 alt="Foto de <?= htmlspecialchars($medico['nombre']) ?>">
        <?php else: ?>
            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="fas fa-user-md text-blue-600"></i>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
            <i class="fas fa-user-md text-blue-600"></i>
        </div>
    <?php endif; ?>
</div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($medico['nombre']) ?></div>
                                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($medico['apellido']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?= htmlspecialchars($medico['nombre_especialidad']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?= htmlspecialchars($medico['correo']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?= htmlspecialchars($medico['numero_colegiatura']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                
                                                <a href="administrar_medicos.php?eliminar=<?= $medico['id_medico'] ?>" 
                                                   class="text-red-600 hover:text-red-900"
                                                   onclick="return confirm('¿Está seguro de eliminar al Dr. <?= htmlspecialchars(addslashes($medico['nombre'])) ?>?')">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>