<?php
// Incluye tu conexión a la base de datos y verificación de sesión
require_once 'conexion.php';
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['loggedin'])) {
    header("Location: login_usuario.php");
    exit;
}

// Obtener el ID del usuario desde la sesión
$id_usuario = $_SESSION['id_usuario'];

// Consultar los datos del usuario
$query = "SELECT * FROM usuarios WHERE id_usuario = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

if (!$usuario) {
    // Si no se encuentra el usuario, redirigir
    header("Location: inicio_usuarios.php");
    exit;
}

// Directorio donde se guardarán las imágenes
$upload_dir = 'uploads/usuarios/';
$absolute_upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/imesys/' . $upload_dir;

// Asegurarse de que el directorio existe
if (!file_exists($absolute_upload_dir)) {
    mkdir($absolute_upload_dir, 0777, true);
}

$mensaje = '';
$error = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y sanitizar los datos del formulario
    $nombre = htmlspecialchars($_POST['nombre']);
    $apellido = htmlspecialchars($_POST['apellido']);
    $dni = htmlspecialchars($_POST['dni']);
    $correo = htmlspecialchars($_POST['correo']);
    $telefono = htmlspecialchars($_POST['telefono']);
    $direccion = htmlspecialchars($_POST['direccion']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'];
    
    // Procesar la imagen de perfil
    $foto = $usuario['foto']; // Mantener la foto actual por defecto
    
    // Si se marca para eliminar la imagen
    if (isset($_POST['eliminar_imagen']) && $_POST['eliminar_imagen'] === '1') {
        if ($foto && file_exists($absolute_upload_dir . $foto)) {
            unlink($absolute_upload_dir . $foto);
        }
        $foto = null; // Establecer a NULL para eliminar la referencia en la BD
    }
    // Si se sube una nueva imagen
    elseif (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK && $_FILES['foto_perfil']['size'] > 0) {
        $file = $_FILES['foto_perfil'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed_ext)) {
            // Verificar tamaño del archivo (max 2MB)
            if ($file['size'] <= 2097152) {
                // Eliminar la foto anterior si existe
                if ($foto && file_exists($absolute_upload_dir . $foto)) {
                    unlink($absolute_upload_dir . $foto);
                }
                
                // Generar un nombre único para el archivo
                $new_filename = 'perfil_' . $id_usuario . '_' . time() . '.' . $file_ext;
                $destination = $absolute_upload_dir . $new_filename;
                
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $foto = $new_filename;
                } else {
                    $error = "Error al subir la imagen de perfil: " . error_get_last()['message'];
                }
            } else {
                $error = "El archivo es demasiado grande. Tamaño máximo: 2MB";
            }
        } else {
            $error = "Formato de archivo no permitido. Use JPG, JPEG, PNG o GIF.";
        }
    }
    
    // Verificar si el correo ya existe (y no es del usuario actual)
    $check_email = "SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario != ?";
    $stmt = $conexion->prepare($check_email);
    $stmt->bind_param("si", $correo, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error = "El correo electrónico ya está en uso por otro usuario.";
    } else {
        // Verificar si el DNI ya existe (y no es del usuario actual)
        $check_dni = "SELECT id_usuario FROM usuarios WHERE dni = ? AND id_usuario != ?";
        $stmt = $conexion->prepare($check_dni);
        $stmt->bind_param("si", $dni, $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "El DNI ya está registrado por otro usuario.";
        } else {
            // Actualizar en la base de datos
            $update_query = "UPDATE usuarios SET 
                            nombre = ?, 
                            apellido = ?, 
                            dni = ?, 
                            correo = ?, 
                            telefono = ?, 
                            direccion = ?, 
                            fecha_nacimiento = ?, 
                            genero = ?";
            
            // Solo incluir la foto en la actualización si hay un valor
            if ($foto !== null) {
                $update_query .= ", foto = ?";
                $types = "ssssssss" . "s";
                $params = [$nombre, $apellido, $dni, $correo, $telefono, $direccion, $fecha_nacimiento, $genero, $foto];
            } else {
                $update_query .= ", foto = NULL";
                $types = "ssssssss";
                $params = [$nombre, $apellido, $dni, $correo, $telefono, $direccion, $fecha_nacimiento, $genero];
            }
            
            $update_query .= " WHERE id_usuario = ?";
            $types .= "i";
            $params[] = $id_usuario;
            
            $stmt = $conexion->prepare($update_query);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                $mensaje = "Perfil actualizado correctamente";
                
                // Actualizar los datos en la sesión si es necesario
                $_SESSION['nombre'] = $nombre;
                $_SESSION['apellido'] = $apellido;
                
                // Recargar los datos del usuario
                $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
                $stmt->bind_param("i", $id_usuario);
                $stmt->execute();
                $result = $stmt->get_result();
                $usuario = $result->fetch_assoc();
            } else {
                $error = "Error al actualizar el perfil: " . $conexion->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Mi Perfil - IMESYS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="estilos_inicio.css">
    <style>
        .preview-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }
        .file-input-label {
            cursor: pointer;
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: #f3f4f6;
            border: 1px dashed #d1d5db;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }
        .file-input-label:hover {
            background-color: #e5e7eb;
            border-color: #9ca3af;
        }
        .main-content {
            margin-top: 70px;
            margin-left: 0;
            transition: margin-left 0.3s;
            padding: 20px;
        }
        .sidebar-open .main-content {
            margin-left: 250px;
        }
        .boton {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background-color: #0052CC;
            color: white;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .boton:hover {
            background-color: #003d99;
        }
        .boton-outline {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background-color: transparent;
            color: #0052CC;
            border: 1px solid #0052CC;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .boton-outline:hover {
            background-color: #0052CC;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Barra superior (incluir o copiar del archivo inicio_usuarios.php) -->
    <nav class="bg-gradient-to-r from-[#5DD9FC] to-[#0052CC] p-4 text-white flex justify-between items-center fixed top-0 left-0 right-0 z-50">
        <div class="flex items-center">
            <button id="menuToggle" class="menu-toggle mr-4 text-white">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <div class="flex items-center">
                <img src="img/logo.png" alt="Logo IMESYS" class="h-10 mr-3">
                <span class="font-bold text-xl">IMESYS</span>
            </div>
        </div>
        
        <div class="flex items-center">
            <?php 
            // Definir URL base
            $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/imesys/';
            
            // Procesar ruta de la foto
            $ruta_foto = '';
            $imagen_existe = false;

            if ($usuario['foto']) {
                $nombre_archivo = basename($usuario['foto']);
                $ruta_foto = $base_url . 'uploads/usuarios/' . $nombre_archivo;
                $ruta_absoluta = $absolute_upload_dir . $nombre_archivo;
                $imagen_existe = file_exists($ruta_absoluta);
            }
            ?>

            <?php if($usuario['foto'] && $imagen_existe): ?>
                <img src="<?= $ruta_foto ?>" alt="Foto de perfil" class="user-avatar mr-2">
            <?php else: ?>
                <div class="user-avatar bg-white text-blue-600 flex items-center justify-center">
                    <i class="fas fa-user"></i>
                </div>
            <?php endif; ?>

            <div class="hidden md:inline text-right mr-3">
                <span>Bienvenido, <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></span>
                <div class="text-xs"><?= htmlspecialchars($usuario['telefono'] ?? 'Sin teléfono registrado'); ?></div>
            </div>
            <a href="logout_usuario.php" class="boton-outline text-white border-white hover:bg-white hover:text-[#0052CC]">
                <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
            </a>
        </div>
    </nav>

    <!-- Overlay -->
    <div id="overlay" class="overlay"></div>

    <!-- Sidebar (incluir o copiar del archivo inicio_usuarios.php) -->
    <div id="sidebar" class="sidebar">
        <div class="p-6 h-full flex flex-col">
            <!-- Información de usuario -->
            <div class="user-info mb-8">
                <?php if($usuario['foto'] && $imagen_existe): ?>
                    <img src="<?= $ruta_foto ?>" alt="Foto de perfil" class="user-avatar mr-2">
                <?php else: ?>
                    <div class="user-avatar bg-white text-blue-600 flex items-center justify-center">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
                <h3 class="text-white font-semibold"><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></h3>
                
                <p class="text-gray-100 text-sm mt-1"><?php echo htmlspecialchars($usuario['correo']); ?></p>
            </div>

            - Sección de menú -->
            <div class="mb-6 flex-grow">
                <h2 class="menu-title font-semibold text-gray-100 uppercase text-xs mb-4">Predicción Médica IA</h2>
                <div class="space-y-2">
                    <a href="inicio_imesys.php" class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-home mr-3 text-white"></i>
                        <span>Inicio</span>
                    </a>
                    <a href="imesys_ai.php" class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-comment-alt mr-3 text-white"></i>
                        <span>Chat IA</span>
                    </a>
                    <a href="datos_bio.php" class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-comment-alt mr-3 text-white"></i>
                        <span>IA Datos Biométricos</span>
                    </a>
                    <a href="lista_medicos.php" class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-user-md mr-3 text-white"></i>
                        <span>Lista de Especialistas</span>
                    </a>
                    <a href="reservar_citas_menu.php" class="menu-item flex items-center p-3 rounded">
                    <i class="fas fa-calendar-check mr-3 text-white"></i>
                        <span>Reservar Citas</span>
                    </a>
                    <a href="recompensas.php" class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-pills mr-3 text-white"></i>
                        <span>Descuentos en Medicamentos</span>
                    </a>
                    <a href="mis_citas.php" class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-image mr-3 text-white"></i>
                        <span>Mis citas</span>
                    </a>
                    <a href="historial_paciente.php" class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-file-medical mr-3 text-white"></i>
                        <span>Mi Historial Médico</span>
                    </a>
                    <a href="comentarios_medicos.php" class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-comment-dots mr-3 text-white"></i>
                        <span>Comentarios</span>
                    </a>

                    
                    <a href="perfil_usuario.php " class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-cog mr-3 text-white"></i>
                        <span>Ajustes</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido Principal -->
    <div class="main-content" id="mainContent">
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Editar Mi Perfil</h1>
                <a href="perfil_paciente.php" class="boton-outline">
                    <i class="fas fa-arrow-left mr-2"></i> Volver a mi perfil
                </a>
            </div>

            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($mensaje)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= $mensaje ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-md overflow-hidden p-6">
                <form method="POST" enctype="multipart/form-data">
                    <div class="flex flex-col md:flex-row gap-8">
                        <!-- Columna izquierda - Foto de perfil -->
                        <div class="w-full md:w-1/3">
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Foto de Perfil</h3>
                                
                                <div class="flex flex-col items-center">
                                    <!-- Vista previa de la imagen -->
                                    <div class="mb-4">
                                        <?php if ($usuario['foto'] && $imagen_existe): ?>
                                            <img id="imagePreview" src="<?= $ruta_foto ?>" 
                                                 alt="Foto de perfil" class="preview-image">
                                        <?php else: ?>
                                            <div id="imagePreview" class="w-32 h-32 rounded-full bg-blue-100 flex items-center justify-center">
                                                <i class="fas fa-user text-4xl text-blue-500"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Input para subir imagen -->
                                    <label for="fotoInput" class="file-input-label mb-2">
                                        <i class="fas fa-camera mr-2"></i> Cambiar imagen
                                    </label>
                                    <input type="file" id="fotoInput" name="foto_perfil" accept="image/*" 
                                           class="hidden" onchange="previewImage(this)">
                                    
                                    <p class="text-sm text-gray-500 mt-2">Formatos: JPG, PNG, GIF (Max. 2MB)</p>
                                    
                                    <?php if ($usuario['foto']): ?>
                                        <button type="button" onclick="removeImage()" 
                                                class="text-red-600 text-sm mt-2 hover:text-red-800">
                                            <i class="fas fa-trash-alt mr-1"></i> Eliminar imagen
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Columna derecha - Datos del perfil -->
                        <div class="w-full md:w-2/3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Columna 1 -->
                                <div>
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="nombre">Nombre</label>
                                        <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" 
                                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="apellido">Apellido</label>
                                        <input type="text" id="apellido" name="apellido" value="<?= htmlspecialchars($usuario['apellido']) ?>" 
                                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="dni">DNI</label>
                                        <input type="text" id="dni" name="dni" value="<?= htmlspecialchars($usuario['dni']) ?>" 
                                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="correo">Correo Electrónico</label>
                                        <input type="email" id="correo" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" 
                                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                    </div>
                                </div>
                                
                                <!-- Columna 2 -->
                                <div>
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="telefono">Teléfono</label>
                                        <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>" 
                                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="direccion">Dirección</label>
                                        <textarea id="direccion" name="direccion" 
                                                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?= htmlspecialchars($usuario['direccion'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="fecha_nacimiento">Fecha de Nacimiento</label>
                                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= $usuario['fecha_nacimiento'] ?>" 
                                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2">Género</label>
                                        <div class="flex gap-4">
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="genero" value="Masculino" <?= $usuario['genero'] === 'Masculino' ? 'checked' : '' ?> class="form-radio">
                                                <span class="ml-2">Masculino</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="genero" value="Femenino" <?= $usuario['genero'] === 'Femenino' ? 'checked' : '' ?> class="form-radio">
                                                <span class="ml-2">Femenino</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="genero" value="Otro" <?= $usuario['genero'] === 'Otro' ? 'checked' : '' ?> class="form-radio">
                                                <span class="ml-2">Otro</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end gap-4">
                        <a href="perfil_paciente.php" class="boton-outline">
                            Cancelar
                        </a>
                        <button type="submit" class="boton">
                            <i class="fas fa-save mr-2"></i> Guardar Cambios
                        </button>
                    </div>
                    
                    <!-- Campo oculto para eliminar imagen -->
                    <input type="hidden" id="eliminar_imagen" name="eliminar_imagen" value="0">
                </form>
            </div>
        </div>
    </div>

    <script>
        // Función para previsualizar la imagen seleccionada
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Restablecer el valor de eliminar_imagen
                    document.getElementById('eliminar_imagen').value = '0';
                    
                    if (preview.tagName === 'IMG') {
                        preview.src = e.target.result;
                    } else {
                        // Si no es un img (es el div por defecto), lo reemplazamos
                        const newImg = document.createElement('img');
                        newImg.id = 'imagePreview';
                        newImg.src = e.target.result;
                        newImg.className = 'preview-image';
                        preview.parentNode.replaceChild(newImg, preview);
                    }
                }
                
                reader.readAsDataURL(file);
            }
        }
        
        // Función para eliminar la imagen actual
        function removeImage() {
            if (confirm('¿Estás seguro de que quieres eliminar tu foto de perfil?')) {
                const preview = document.getElementById('imagePreview');
                const defaultPreview = document.createElement('div');
                defaultPreview.id = 'imagePreview';
                defaultPreview.className = 'w-32 h-32 rounded-full bg-blue-100 flex items-center justify-center';
                defaultPreview.innerHTML = '<i class="fas fa-user text-4xl text-blue-500"></i>';
                
                preview.parentNode.replaceChild(defaultPreview, preview);
                
                // Limpiar el input de archivo
                document.getElementById('fotoInput').value = '';
                
                // Marcar para eliminar la imagen
                document.getElementById('eliminar_imagen').value = '1';
            }
        }
        
        // Toggle para el menú lateral
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const mainContent = document.getElementById('mainContent');
            
            function toggleSidebar() {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('show');
                document.body.classList.toggle('sidebar-open');
                mainContent.classList.toggle('sidebar-open');
            }
            
            menuToggle.addEventListener('click', toggleSidebar);
            overlay.addEventListener('click', toggleSidebar);
        });
    </script>
</body>
</html>