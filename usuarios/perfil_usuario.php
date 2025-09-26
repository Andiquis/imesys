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
    $foto = is_array($usuario) && isset($usuario['foto']) ? $usuario['foto'] : null; // Mantener la foto actual por defecto
    
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
                $usuario_actualizado = $result->fetch_assoc();
                $stmt->close();
                if ($usuario_actualizado && is_array($usuario_actualizado)) {
                    $usuario = $usuario_actualizado;
                } else {
                    $error = 'No se pudo recargar el usuario después de la actualización.';
                }
            } else {
                $error = "Error al actualizar el perfil: " . $conexion->error;
            }
        }
    }
    // Si hubo error, prellenar $usuario con los datos del POST para no perder lo ingresado
    if (!empty($error)) {
        $usuario = [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'dni' => $dni,
            'correo' => $correo,
            'telefono' => $telefono,
            'direccion' => $direccion,
            'fecha_nacimiento' => $fecha_nacimiento,
            'genero' => $genero,
            'foto' => $foto
        ];
    }
}

// Calcular ruta y existencia de la foto de perfil antes del HTML
$ruta_foto = '';
$imagen_existe = false;
if (is_array($usuario) && isset($usuario['foto']) && $usuario['foto']) {
    $ruta_foto = $upload_dir . $usuario['foto'];
    $ruta_foto_absoluta = $absolute_upload_dir . $usuario['foto'];
    $imagen_existe = file_exists($ruta_foto_absoluta);
}

// Asegurar que $usuario tenga todas las claves necesarias antes del HTML
$claves_usuario = ['nombre','apellido','dni','correo','telefono','direccion','fecha_nacimiento','genero','foto'];
if (!is_array($usuario)) $usuario = [];
foreach ($claves_usuario as $clave) {
    if (!isset($usuario[$clave])) {
        $usuario[$clave] = '';
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
        @media (min-width: 1024px) {
            .content-area {
                margin-left: 270px !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Barra superior y sidebar -->
    <?php include 'header_usuarios.php'; ?>

    <!-- Contenido Principal -->
    <div class="content-area" id="mainContent">
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Editar Mi Perfil</h1>
                <a href="inicio_imesys.php" class="boton-outline">
                    <i class="fas fa-arrow-left mr-2"></i> Volver al inicio
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
                                        <?php if (is_array($usuario) && isset($usuario['foto']) && $usuario['foto'] && $imagen_existe): ?>
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
                                    
                                    <?php if (is_array($usuario) && isset($usuario['foto']) && $usuario['foto']): ?>
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
                                        <input type="text" id="nombre" name="nombre" value="<?= is_array($usuario) && isset($usuario['nombre']) ? htmlspecialchars($usuario['nombre']) : '' ?>" 
                                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="apellido">Apellido</label>
                                        <input type="text" id="apellido" name="apellido" value="<?= is_array($usuario) && isset($usuario['apellido']) ? htmlspecialchars($usuario['apellido']) : '' ?>" 
                                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="dni">DNI</label>
                                        <input type="text" id="dni" name="dni" value="<?= is_array($usuario) && isset($usuario['dni']) ? htmlspecialchars($usuario['dni']) : '' ?>" 
                                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="correo">Correo Electrónico</label>
                                        <input type="email" id="correo" name="correo" value="<?= is_array($usuario) && isset($usuario['correo']) ? htmlspecialchars($usuario['correo']) : '' ?>" 
                                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                    </div>
                                </div>
                                
                                <!-- Columna 2 -->
                                <div>
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="telefono">Teléfono</label>
                                        <input type="tel" id="telefono" name="telefono" value="<?= is_array($usuario) && isset($usuario['telefono']) ? htmlspecialchars($usuario['telefono']) : '' ?>" 
                                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="direccion">Dirección</label>
                                        <textarea id="direccion" name="direccion" 
                                                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?= is_array($usuario) && isset($usuario['direccion']) ? htmlspecialchars($usuario['direccion']) : '' ?></textarea>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="fecha_nacimiento">Fecha de Nacimiento</label>
                                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= is_array($usuario) && isset($usuario['fecha_nacimiento']) ? $usuario['fecha_nacimiento'] : '' ?>" 
                                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2">Género</label>
                                        <div class="flex gap-4">
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="genero" value="Masculino" <?= is_array($usuario) && isset($usuario['genero']) && $usuario['genero'] === 'Masculino' ? 'checked' : '' ?> class="form-radio">
                                                <span class="ml-2">Masculino</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="genero" value="Femenino" <?= is_array($usuario) && isset($usuario['genero']) && $usuario['genero'] === 'Femenino' ? 'checked' : '' ?> class="form-radio">
                                                <span class="ml-2">Femenino</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="genero" value="Otro" <?= is_array($usuario) && isset($usuario['genero']) && $usuario['genero'] === 'Otro' ? 'checked' : '' ?> class="form-radio">
                                                <span class="ml-2">Otro</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end gap-4">
                        <a href="perfil_usuario.php" class="boton-outline">
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
 <?php include 'footer_usuario.php'; ?>
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
        
        
    </script>
</body>
</html>