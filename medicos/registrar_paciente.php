<?php
session_start();

// Verificar si el usuario está logueado (si es necesario)
$loggedin = isset($_SESSION['loggedin']);
$medico_id = $_SESSION['id_medico'] ?? null;

// Obtener información del médico si está logueado
if ($loggedin) {
    require 'conexion.php';
    
    $stmt = $conexion->prepare("SELECT m.nombre, m.apellido, m.correo, m.foto, e.nombre_especialidad 
                               FROM medicos m
                               JOIN especialidades e ON m.id_especialidad = e.id_especialidad
                               WHERE m.id_medico = ?");
    $stmt->bind_param("i", $medico_id);
    $stmt->execute();
    $stmt->bind_result($nombre, $apellido, $correo, $foto, $especialidad);
    $stmt->fetch();
    $stmt->close();
}

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si la conexión existe
    if (!isset($conexion)) {
        require_once __DIR__ . '/../conexion.php';
    }

    if (!isset($conexion)) {
        die("Error: No se pudo establecer conexión con la base de datos");
    }

    $nombre_paciente = $_POST['nombre'] ?? '';
    $apellido_paciente = $_POST['apellido'] ?? '';
    $dni_paciente = $_POST['dni'] ?? '';
    $correo_paciente = $_POST['correo'] ?? '';
    $contrasena = password_hash($_POST['contrasena'] ?? '', PASSWORD_DEFAULT);
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $genero = $_POST['genero'] ?? '';
    
    // Procesar foto
    $fotoNombre = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fotoTemp = $_FILES['foto']['tmp_name'];
        $fotoNombre = uniqid() . '_' . basename($_FILES['foto']['name']);
        $uploadDir = 'C:/xampp/htdocs/proyectoimesys/uploads/usuarios/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        if (move_uploaded_file($fotoTemp, $uploadDir . $fotoNombre)) {
            // Guardar solo la ruta relativa en la base de datos
            $fotoNombre = 'uploads/usuarios/' . $fotoNombre;
        } else {
            $fotoNombre = '';
            $errorSubida = "Error al subir la imagen";
        }
    }
    
    try {
        $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, apellido, dni, correo, contrasena, telefono, direccion, fecha_nacimiento, genero, foto) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", $nombre_paciente, $apellido_paciente, $dni_paciente, $correo_paciente, $contrasena, $telefono, $direccion, $fecha_nacimiento, $genero, $fotoNombre);
        
        if ($stmt->execute()) {
            $mensaje = "Paciente registrado correctamente";
            $claseMensaje = "bg-green-100 border-green-400 text-green-700";
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        $mensaje = "Error al registrar: " . $e->getMessage();
        $claseMensaje = "bg-red-100 border-red-400 text-red-700";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Registro de Pacientes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="estilos_inicio.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f7fafc; }
        .form-container { max-width: 800px; margin: 2rem auto; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .form-header { background: linear-gradient(to right, #5DD9FC, #0052CC); }
        
        /* Estilos para el sidebar y contenido */
        .content-area {
            margin-left: 0;
            transition: margin-left 0.3s;
            padding-top: 80px;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            position: fixed;
            top: 0;
            left: -280px;
            height: 100%;
            background: linear-gradient(to bottom, #5DD9FC, #0052CC);
            transition: left 0.3s;
            z-index: 1000;
            color: white;
        }
        
        .sidebar.active {
            left: 0;
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .menu-item {
            color: white;
            transition: all 0.2s;
        }
        
        .menu-item:hover, .menu-item.active {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .menu-title {
            letter-spacing: 0.05em;
        }
        
        .footer {
            background-color: #fff;
            padding: 20px 0;
            margin-top: 40px;
            border-top: 1px solid #e2e8f0;
        }
        
        @media (min-width: 768px) {
            .sidebar {
                left: 0;
            }
            
            .content-area {
                margin-left: 280px;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'header_medico.php'; ?>
    <!-- Contenido principal -->
   
        <div class="container mx-auto px-4 py-6">
            <div class="form-container bg-white rounded-lg overflow-hidden">
                <div class="form-header p-6 text-white">
                    <h1 class="text-2xl font-bold">Registro de Paciente</h1>
                    <p class="mt-2">Complete el formulario para registrar un nuevo paciente</p>
                </div>
                
                <?php if (!empty($mensaje)): ?>
                    <div class="border-l-4 <?= $claseMensaje ?> p-4 mb-6 mx-6 mt-4 rounded">
                        <?= htmlspecialchars($mensaje) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errorSubida)): ?>
                    <div class="border-l-4 bg-red-100 border-red-400 text-red-700 p-4 mb-6 mx-6 mt-4 rounded">
                        <?= htmlspecialchars($errorSubida) ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="mb-4">
                                <label for="nombre" class="block text-gray-700 font-medium mb-2">Nombre *</label>
                                <input type="text" id="nombre" name="nombre" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="mb-4">
                                <label for="apellido" class="block text-gray-700 font-medium mb-2">Apellido *</label>
                                <input type="text" id="apellido" name="apellido" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div class="mb-4">
                                <label for="dni" class="block text-gray-700 font-medium mb-2">Dni *</label>
                                <input type="text" id="dni" name="dni" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="mb-4">
                                <label for="correo" class="block text-gray-700 font-medium mb-2">Correo Electrónico *</label>
                                <input type="email" id="correo" name="correo" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="mb-4">
                                <label for="contrasena" class="block text-gray-700 font-medium mb-2">Contraseña *</label>
                                <input type="password" id="contrasena" name="contrasena" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div>
                            <div class="mb-4">
                                <label for="telefono" class="block text-gray-700 font-medium mb-2">Teléfono</label>
                                <input type="tel" id="telefono" name="telefono" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="mb-4">
                                <label for="direccion" class="block text-gray-700 font-medium mb-2">Dirección</label>
                                <textarea id="direccion" name="direccion" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label for="fecha_nacimiento" class="block text-gray-700 font-medium mb-2">Fecha de Nacimiento</label>
                                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 font-medium mb-2">Género</label>
                                <div class="flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="genero" value="Masculino" class="form-radio text-blue-600">
                                        <span class="ml-2">Masculino</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="genero" value="Femenino" class="form-radio text-blue-600">
                                        <span class="ml-2">Femenino</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="genero" value="Otro" class="form-radio text-blue-600">
                                        <span class="ml-2">Otro</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="foto" class="block text-gray-700 font-medium mb-2">Foto de Perfil</label>
                                <input type="file" id="foto" name="foto" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-sm text-gray-500 mt-1">Formatos aceptados: JPG, PNG, GIF</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-4 pt-4">
                        <button type="reset" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                            Limpiar
                        </button>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-user-plus mr-2"></i>Registrar Paciente
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php include 'footer_medico.php'; ?>
</body>
</html>