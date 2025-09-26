<?php
session_start();

// Verificar si el médico ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['tipo_usuario'] !== 'medico') {
    header("Location: login_medico.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Médico - IMESYS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="estilos_panel.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h1 class="text-2xl font-bold text-blue-600">Bienvenido, <?php echo $_SESSION['nombre'] . ' ' . $_SESSION['apellido']; ?>!</h1>
            <p class="text-gray-700 mt-2">Este es tu panel de control en IMESYS.</p>
            
            <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="citas_medico.php" class="block bg-blue-500 text-white p-4 rounded-lg text-center shadow-md hover:bg-blue-700">
                    <i class="fas fa-calendar-alt text-2xl"></i>
                    <p class="mt-2">Mis Citas</p>
                </a>
                
                <a href="historial_pacientes.php" class="block bg-green-500 text-white p-4 rounded-lg text-center shadow-md hover:bg-green-700">
                    <i class="fas fa-user-injured text-2xl"></i>
                    <p class="mt-2">Historial de Pacientes</p>
                </a>
                
                <a href="ajustes.php" class="block bg-yellow-500 text-white p-4 rounded-lg text-center shadow-md hover:bg-yellow-700">
                    <i class="fas fa-cogs text-2xl"></i>
                    <p class="mt-2">Ajustes</p>
                </a>
                
                <a href="logout.php" class="block bg-red-500 text-white p-4 rounded-lg text-center shadow-md hover:bg-red-700">
                    <i class="fas fa-sign-out-alt text-2xl"></i>
                    <p class="mt-2">Cerrar Sesión</p>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
