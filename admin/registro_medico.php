<?php
session_start();

if (!isset($_SESSION['admin_loggedin'])) {
    header("Location: login_admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Registro de Médico</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            transition: all 0.3s ease;
        }
        .sidebar-item:hover {
            background-color: #ebf4ff;
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
                    <a href="dashboard_admin.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg sidebar-item">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="registro_medico.php" class="flex items-center space-x-2 px-4 py-2 bg-blue-700 rounded-lg sidebar-item">
                        <i class="fas fa-user-md"></i>
                        <span>Médicos</span>
                    
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
                    <h2 class="text-xl font-semibold text-gray-800">Registro de Médico</h2>
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

            <!-- Contenido del formulario -->
            <main class="p-6">
                <div class="bg-white p-8 rounded-2xl shadow-md w-full max-w-2xl mx-auto">
                    <h2 class="text-2xl font-bold mb-6 text-center">Registro de Médico</h2>
                    <form action="guardar_medico.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" name="nombre" placeholder="Nombre" required class="border p-2 rounded w-full">
                            <input type="text" name="apellido" placeholder="Apellido" required class="border p-2 rounded w-full">
                            <input type="email" name="correo" placeholder="Correo" required class="border p-2 rounded w-full">
                            <input type="password" name="contrasena" placeholder="Contraseña" required class="border p-2 rounded w-full">
                            <input type="text" name="telefono" placeholder="Teléfono" class="border p-2 rounded w-full">
                            <input type="text" name="numero_colegiatura" placeholder="Número de Colegiatura" required class="border p-2 rounded w-full">
                        </div>

                        <div>
                            <label class="block font-medium">Especialidad</label>
                            <select name="id_especialidad" required class="border p-2 rounded w-full">
                                <?php
                                $conn = new mysqli("localhost", "root", "admin123", "BD_imesys");
                                $query = "SELECT id_especialidad, nombre_especialidad FROM especialidades";
                                $result = $conn->query($query);
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['id_especialidad']}'>{$row['nombre_especialidad']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div>
                            <label class="block font-medium">Dirección del Consultorio</label>
                            <input type="text" name="direccion_consultorio" class="border p-2 rounded w-full">
                        </div>

                        <div>
                            <label class="block font-medium">Foto</label>
                            <input type="file" name="foto" accept="image/*" class="border p-2 rounded w-full">
                        </div>

                        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition duration-300">Registrar Médico</button>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>