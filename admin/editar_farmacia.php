<?php
require_once '../conexion.php';

// Obtener ID de la farmacia a editar
$id_farmacia = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Obtener datos de la farmacia
$farmacia = null;
if ($id_farmacia > 0) {
    $stmt = $conexion->prepare("SELECT * FROM farmacias WHERE id_farmacia = ?");
    $stmt->bind_param("i", $id_farmacia);
    $stmt->execute();
    $result = $stmt->get_result();
    $farmacia = $result->fetch_assoc();
}

// Procesar formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];

    try {
        $stmt = $conexion->prepare("UPDATE farmacias SET nombre = ?, direccion = ?, telefono = ?, correo = ? WHERE id_farmacia = ?");
        $stmt->bind_param("ssssi", $nombre, $direccion, $telefono, $correo, $id_farmacia);
        $stmt->execute();

        echo "<script>alert('Farmacia actualizada correctamente'); window.location='administrar_farmacia.php';</script>";
    } catch (Exception $e) {
        $error = "Error al actualizar farmacia: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id_farmacia ? 'Editar' : 'Nueva' ?> Farmacia | iMesys</title>
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
                        <?= $id_farmacia ? 'Editar Farmacia' : 'Nueva Farmacia' ?>
                    </h1>
                    <a href="administrar_farmacia.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver
                    </a>
                </div>

                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                            <input 
                                type="text" 
                                id="nombre" 
                                name="nombre" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                value="<?= htmlspecialchars($farmacia['nombre'] ?? '') ?>"
                                required
                            >
                        </div>

                        <div>
                            <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                            <input 
                                type="text" 
                                id="telefono" 
                                name="telefono" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                value="<?= htmlspecialchars($farmacia['telefono'] ?? '') ?>"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="direccion" class="block text-sm font-medium text-gray-700">Dirección</label>
                        <textarea 
                            id="direccion" 
                            name="direccion" 
                            rows="3"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required
                        ><?= htmlspecialchars($farmacia['direccion'] ?? '') ?></textarea>
                    </div>

                    <div>
                        <label for="correo" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                        <input 
                            type="email" 
                            id="correo" 
                            name="correo" 
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            value="<?= htmlspecialchars($farmacia['correo'] ?? '') ?>"
                        >
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button 
                            type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center"
                        >
                            <i class="fas fa-save mr-2"></i>
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>