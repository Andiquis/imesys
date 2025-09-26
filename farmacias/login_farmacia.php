<?php
session_start();
require 'conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $contrasena = $_POST['contrasena'];
    
    $stmt = $conexion->prepare("SELECT id_farmacia, nombre, contrasena FROM farmacias WHERE correo = ? AND activa = TRUE");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $farmacia = $result->fetch_assoc();
        
        if (password_verify($contrasena, $farmacia['contrasena'])) {
            $_SESSION['farmacia_loggedin'] = true;
            $_SESSION['farmacia_id'] = $farmacia['id_farmacia'];
            $_SESSION['farmacia_nombre'] = $farmacia['nombre'];
            
            // Actualizar fecha de último login
            $conexion->query("UPDATE farmacias SET fecha_ultimo_login = NOW() WHERE id_farmacia = {$farmacia['id_farmacia']}");
            
            header("Location: verificar_codigo.php");
            exit;
        } else {
            $error = "Credenciales incorrectas";
        }
    } else {
        $error = "Credenciales incorrectas o cuenta no activa";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Login Farmacias</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<a href="../prelogin.php" 
   class="inline-flex items-center justify-center w-10 h-10 border border-white rounded-full text-white hover:bg-white hover:text-blue-700 transition-all duration-300">
    <i class="fas fa-arrow-left"></i>
</a>
<br><br>
<body class="bg-gradient-to-r from-[#5DD9FC] to-[#0052CC] min-h-screen flex flex-col items-center justify-center p-4">

    <div class="bg-white p-10 rounded-2xl shadow-xl w-full max-w-md border border-gray-200">
        <div class="text-center mb-8">
            <img src="../img/logo.png" alt="Logo IMESYS" class="h-16 mx-auto drop-shadow-md">
            <h1 class="text-3xl font-bold text-gray-800 mt-4">Acceso para Farmacias</h1>
            <p class="text-gray-500 mt-1">Sistema de validación de canjes</p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                <input type="email" id="email" name="email" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="contrasena" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <button type="submit"
                        class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-300">
                    Iniciar Sesión <i class="fas fa-sign-in-alt ml-2"></i>
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <a href="recuperar_contrasena_farmacia.php" class="text-sm text-blue-600 hover:text-blue-800">
                ¿Olvidaste tu contraseña?
            </a>
        </div>
    </div>

</body>
</html>
