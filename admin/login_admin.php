<?php
session_start();

// Credenciales fijas de emergencia (solo para desarrollo)
$credenciales_fijas = [
    'usuario' => 'admin',
    'contrasena' => 'senati' // En producción usar hash
];

require '../conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = trim($_POST['usuario']);
    $contrasena = trim($_POST['contrasena']);
    
    // Verificar credenciales fijas primero (solo para desarrollo)
    if ($usuario === $credenciales_fijas['usuario'] && $contrasena === $credenciales_fijas['contrasena']) {
        $_SESSION['admin_loggedin'] = true;
        $_SESSION['admin_usuario'] = 'admin';
        $_SESSION['admin_nombre'] = 'Administrador Principal';
        header('Location: dashboard_admin.php');
        exit;
    }
    
    // Si no coinciden con las fijas, verificar en la base de datos
    $stmt = $conexion->prepare("SELECT id_admin, usuario, contrasena, nombre FROM administradores WHERE usuario = ? AND activo = TRUE");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id_admin, $db_usuario, $db_contrasena, $db_nombre);
        $stmt->fetch();
        
        if (password_verify($contrasena, $db_contrasena)) {
            $_SESSION['admin_loggedin'] = true;
            $_SESSION['admin_id'] = $id_admin;
            $_SESSION['admin_usuario'] = $db_usuario;
            $_SESSION['admin_nombre'] = $db_nombre;
            
            // Actualizar último acceso
            $update = $conexion->prepare("UPDATE administradores SET ultimo_acceso = NOW() WHERE id_admin = ?");
            $update->bind_param("i", $id_admin);
            $update->execute();
            
            header('Location: dashboard_admin.php');
            exit;
        } else {
            $error = "Credenciales incorrectas";
        }
    } else {
        $error = "Usuario no encontrado";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<a href="../prelogin.php" 
   class="inline-flex items-center justify-center w-10 h-10 border border-white rounded-full text-white hover:bg-white hover:text-blue-700 transition-all duration-300">
    <i class="fas fa-arrow-left"></i>
</a>
<br><br>
<body class="bg-gradient-to-r from-[#5DD9FC] to-[#0052CC] min-h-screen flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-md p-8 space-y-8 bg-white rounded-3xl shadow-2xl border border-blue-100">
        <div class="text-center">
            <img src="../img/logo.png" alt="IMESYS Logo" class="mx-auto h-16 drop-shadow-md">
            <h2 class="mt-6 text-3xl font-extrabold text-blue-700">Panel Administrativo</h2>
            <p class="text-sm text-gray-500">Accede con tus credenciales</p>
        </div>
        
        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg shadow-sm" role="alert">
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form class="mt-6 space-y-5" method="POST">
            <div class="space-y-4">
                <div>
                    <label for="usuario" class="block text-sm font-medium text-gray-700">Usuario</label>
                    <input id="usuario" name="usuario" type="text" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Ingrese su usuario">
                </div>
                <div>
                    <label for="contrasena" class="block text-sm font-medium text-gray-700">Contraseña</label>
                    <input id="contrasena" name="contrasena" type="password" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Ingrese su contraseña">
                </div>
            </div>

            <div>
                <button type="submit"
                        class="w-full flex justify-center items-center gap-2 py-3 px-4 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold hover:from-cyan-600 hover:to-blue-700 transition duration-300 shadow-lg">
                    <i class="fas fa-lock"></i> Iniciar Sesión
                </button>
            </div>
        </form>
    </div>

</body>
</html>
