<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

require 'conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Inicio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .boton {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(to right, #0097A7, #7AC943);
            padding: 10px 20px;
            border-radius: 20px;
            color: black;
            font-weight: bold;
            text-decoration: none;
            font-family: Arial, sans-serif;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .boton:hover {
            transform: scale(1.05);
        }
        
        .boton-outline {
            background: transparent;
            border: 2px solid #0097A7;
            color: #0097A7;
        }
    </style>
</head>
<body class="bg-gray-100">
    <nav class="bg-gradient-to-r from-[#5DD9FC] to-[#0052CC] p-4 text-white">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <img src="img/logo.png" alt="Logo IMESYS" class="h-10 mr-3">
                <span class="font-bold text-xl">IMESYS</span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="hidden md:inline">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido']); ?></span>
                <a href="logout.php" class="boton-outline text-white border-white hover:bg-white hover:text-[#0052CC]">
                    <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-4 mt-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-3 text-[#0052CC]">
                    <i class="fas fa-user-circle mr-2"></i>Mi Perfil
                </h2>
                <p class="mb-2"><span class="font-medium">Nombre:</span> <?php echo htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido']); ?></p>
                <p><span class="font-medium">Email:</span> <?php echo htmlspecialchars($_SESSION['correo']); ?></p>
                <button class="boton mt-4">
                    <i class="fas fa-edit mr-2"></i> Editar perfil
                </button>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-3 text-[#0052CC]">
                    <i class="fas fa-calendar-check mr-2"></i>Mis Citas
                </h2>
                <p class="text-gray-600 mb-4">Aquí puedes gestionar tus citas médicas.</p>
                <button class="boton">
                    <i class="fas fa-plus mr-2"></i> Nueva cita
                </button>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-3 text-[#0052CC]">
                    <i class="fas fa-heartbeat mr-2"></i>Salud
                </h2>
                <div class="space-y-3">
                    <button class="boton w-full text-left">
                        <i class="fas fa-robot mr-2"></i> Asistente Virtual
                    </button>
                    <button class="boton w-full text-left">
                        <i class="fas fa-file-medical mr-2"></i> Historial Médico
                    </button>
                    <button class="boton w-full text-left">
                        <i class="fas fa-pills mr-2"></i> Medicamentos
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>