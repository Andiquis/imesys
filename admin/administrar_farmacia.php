<?php
require_once '../conexion.php';

// Eliminar farmacia
if (isset($_GET['eliminar'])) {
    $id_farmacia = $_GET['eliminar'];
    
    try {
        $conexion->begin_transaction();
        
        // Eliminar farmacia
        $stmt = $conexion->prepare("DELETE FROM farmacias WHERE id_farmacia = ?");
        $stmt->bind_param("i", $id_farmacia);
        $stmt->execute();
        
        $conexion->commit();
        echo "<script>alert('Farmacia eliminada correctamente'); window.location='administrar_farmacia.php';</script>";
    } catch (Exception $e) {
        $conexion->rollback();
        die("Error al eliminar farmacia: " . $e->getMessage());
    }
}

// Buscar farmacias
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT * FROM farmacias";

if (!empty($search)) {
    $query .= " WHERE nombre LIKE '%$search%' OR direccion LIKE '%$search%'";
}

$farmacias = $conexion->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Farmacias | iMesys</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar-item {
            transition: all 0.3s ease;
        }
        .sidebar-item:hover {
            transform: translateX(5px);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-blue-800 text-white p-4">
            <div class="flex items-center space-x-2 mb-8 p-2">
                <i class="fas fa-hospital text-2xl"></i>
                <span class="text-xl font-bold">iMesys</span>
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

        <!-- Main Content -->
        <div class="flex-1 p-8 overflow-auto">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-prescription-bottle-alt mr-2"></i>
                        Administrar Farmacias
                    </h1>
                    <a href="registro_farmacia.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i>
                        Nueva Farmacia
                    </a>
                </div>

                <!-- Buscador -->
                <div class="mb-6">
                    <form method="GET" class="flex">
                        <div class="relative flex-1">
                            <input 
                                type="text" 
                                name="search" 
                                class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Buscar por nombre o dirección..."
                                value="<?= htmlspecialchars($search) ?>"
                            >
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                        <button type="submit" class="ml-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            Buscar
                        </button>
                    </form>
                </div>

                <!-- Tabla de Farmacias -->
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white rounded-lg overflow-hidden">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="py-3 px-4 text-left">ID</th>
                                <th class="py-3 px-4 text-left">Nombre</th>
                                <th class="py-3 px-4 text-left">Dirección</th>
                                <th class="py-3 px-4 text-left">Teléfono</th>
                                <th class="py-3 px-4 text-left">Correo</th>
                                <th class="py-3 px-4 text-left">Registro</th>
                                <th class="py-3 px-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php while($farmacia = $farmacias->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4"><?= htmlspecialchars($farmacia['id_farmacia']) ?></td>
                                <td class="py-3 px-4 font-medium"><?= htmlspecialchars($farmacia['nombre']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($farmacia['direccion']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($farmacia['telefono']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($farmacia['correo']) ?></td>
                                <td class="py-3 px-4"><?= date('d/m/Y', strtotime($farmacia['fecha_registro'])) ?></td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        <a href="editar_farmacia.php?id=<?= $farmacia['id_farmacia'] ?>" 
                                           class="bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded-full w-8 h-8 flex items-center justify-center"
                                           title="Editar">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <a href="administrar_farmacia.php?eliminar=<?= $farmacia['id_farmacia'] ?>" 
                                           class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-full w-8 h-8 flex items-center justify-center"
                                           title="Eliminar"
                                           onclick="return confirm('¿Estás seguro de eliminar la farmacia <?= htmlspecialchars(addslashes($farmacia['nombre'])) ?>?')">
                                            <i class="fas fa-trash text-xs"></i>
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

    <script>
        // Confirmación antes de eliminar
        function confirmarEliminacion(nombre) {
            return confirm(`¿Estás seguro de eliminar la farmacia ${nombre}?`);
        }
    </script>
</body>
</html>