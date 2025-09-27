<?php 
include '../conexion.php';

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $direccion = trim($_POST['direccion']);
    $telefono = trim($_POST['telefono']);
    $correo = trim($_POST['correo']);
    $contrasena = trim($_POST['contrasena']);
    
    // Validación básica
    if ($nombre && $direccion && $correo && $contrasena) {
        // Hashear la contraseña
        $contrasena_hashed = password_hash($contrasena, PASSWORD_BCRYPT);

        // Verificar que el correo no esté ya registrado
        $verificar = $conexion->prepare("SELECT id_farmacia FROM farmacias WHERE correo = ?");
        $verificar->bind_param("s", $correo);
        $verificar->execute();
        $verificar->store_result();

        if ($verificar->num_rows > 0) {
            $mensaje = "⚠️ El correo ya está registrado.";
        } else {
            // Insertar la nueva farmacia
            $stmt = $conexion->prepare("INSERT INTO farmacias (nombre, direccion, telefono, correo, contrasena) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nombre, $direccion, $telefono, $correo, $contrasena_hashed);

            if ($stmt->execute()) {
                $mensaje = "✅ Registro exitoso.";
            } else {
                $mensaje = "❌ Error al registrar: " . $stmt->error;
            }
        }

        $verificar->close();
    } else {
        $mensaje = "⚠️ Todos los campos obligatorios deben llenarse.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>IMESYS - Registro de Farmacia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
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
        <div class="sidebar w-64 bg-blue-800 text-white relative">
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
        <div class="flex-1 overflow-auto">
            <!-- Barra superior -->
            <header class="bg-white shadow-sm">
                <div class="flex justify-between items-center px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">Registro de Farmacia</h2>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <i class="fas fa-bell text-gray-500"></i>
                            <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
                        </div>
                        <div class="flex items-center">
                            <!-- Suponiendo que hay sesión admin_nombre -->
                            <img src="https://ui-avatars.com/api/?name=<?= isset($_SESSION['admin_nombre']) ? urlencode($_SESSION['admin_nombre']) : 'Admin' ?>&background=3B82F6&color=fff" 
                                 alt="Admin" class="h-8 w-8 rounded-full" />
                            <span class="ml-2 text-sm font-medium"><?= isset($_SESSION['admin_nombre']) ? htmlspecialchars($_SESSION['admin_nombre']) : 'Admin' ?></span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Contenido del formulario -->
            <main class="p-6">
                <div class="bg-white p-8 rounded-2xl shadow-md w-full max-w-lg mx-auto">
                    <h2 class="text-2xl font-bold mb-6 text-center">Registro de Farmacia</h2>
                    
                    <?php if ($mensaje): ?>
                        <p class="mb-6 text-center font-semibold text-red-600"><?= $mensaje ?></p>
                    <?php endif; ?>

                    <form method="POST" action="" class="space-y-6">
                        <div>
                            <label class="block font-medium mb-1">Nombre de la farmacia *</label>
                            <input type="text" name="nombre" required
                                   class="border border-gray-300 rounded p-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <div>
                            <label class="block font-medium mb-1">Dirección *</label>
                            <textarea name="direccion" required rows="3"
                                      class="border border-gray-300 rounded p-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div>
                            <label class="block font-medium mb-1">Teléfono</label>
                            <input type="text" name="telefono"
                                   class="border border-gray-300 rounded p-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <div>
                            <label class="block font-medium mb-1">Correo electrónico *</label>
                            <input type="email" name="correo" required
                                   class="border border-gray-300 rounded p-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <div>
                            <label class="block font-medium mb-1">Contraseña *</label>
                            <input type="password" name="contrasena" required
                                   class="border border-gray-300 rounded p-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <button type="submit" 
                                class="w-full bg-blue-600 text-white py-3 rounded hover:bg-blue-700 transition duration-300 font-semibold">
                            Registrar
                        </button>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
