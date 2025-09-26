<?php
include 'header_medico.php';

// Verificar si se recibió el ID del paciente
if (!isset($_GET['id_paciente']) || empty($_GET['id_paciente'])) {
    echo "<script>alert('Paciente no especificado'); window.location.href = 'buscador_pacientes.php';</script>";
    exit;
}

$id_paciente = $_GET['id_paciente'];

require 'conexion.php';

// Obtener información actual del paciente
$query = "SELECT * FROM usuarios WHERE id_usuario = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id_paciente);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Paciente no encontrado'); window.location.href = 'buscador_pacientes.php';</script>";
    exit;
}

$paciente = $result->fetch_assoc();
$stmt->close();

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar los datos
    $nombre = htmlspecialchars(trim($_POST['nombre']));
    $apellido = htmlspecialchars(trim($_POST['apellido']));
    $dni = htmlspecialchars(trim($_POST['dni']));
    $correo = htmlspecialchars(trim($_POST['correo']));
    $telefono = htmlspecialchars(trim($_POST['telefono'] ?? ''));
    $direccion = htmlspecialchars(trim($_POST['direccion'] ?? ''));
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'];
    
    // Procesar la imagen subida
    $foto = $paciente['foto']; // Mantener la foto existente por defecto
    
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/pacientes/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $file_name = 'paciente_' . $id_paciente . '_' . time() . '.' . $file_ext;
        $target_file = $upload_dir . $file_name;
        
        // Validar tipo de archivo
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($file_ext), $allowed_types)) {
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                // Eliminar la foto anterior si existe
                if (!empty($paciente['foto']) && file_exists($paciente['foto'])) {
                    unlink($paciente['foto']);
                }
                $foto = $target_file;
            }
        }
    } elseif (isset($_POST['eliminar_foto']) && $_POST['eliminar_foto'] === 'on') {
        // Eliminar la foto actual si se marcó la opción
        if (!empty($paciente['foto']) && file_exists($paciente['foto'])) {
            unlink($paciente['foto']);
        }
        $foto = '';
    }
    
    // Actualizar los datos del paciente
    $query = "UPDATE usuarios SET
                nombre = ?,
                apellido = ?,
                dni = ?,
                correo = ?,
                telefono = ?,
                direccion = ?,
                fecha_nacimiento = ?,
                genero = ?,
                foto = ?
              WHERE id_usuario = ?";
    
    $stmt = $conexion->prepare($query);
    $stmt->bind_param(
        "sssssssssi", 
        $nombre, 
        $apellido, 
        $dni, 
        $correo, 
        $telefono, 
        $direccion, 
        $fecha_nacimiento, 
        $genero,
        $foto,
        $id_paciente
    );
    
    if ($stmt->execute()) {
        echo "<script>
                alert('Perfil del paciente actualizado correctamente');
                window.location.href = 'perfil_paciente.php?id=$id_paciente';
              </script>";
        exit;
    } else {
        $error = "Error al actualizar el perfil: " . $conexion->error;
    }
    
    $stmt->close();
}
$conexion->close();

// Procesar ruta de la foto para vista previa
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/imesys/';
$ruta_foto = '';
$imagen_existe = false;

if ($paciente['foto']) {
    $nombre_archivo = basename($paciente['foto']);
    $ruta_foto = $base_url . 'uploads/pacientes/' . $nombre_archivo;
    $ruta_absoluta = $_SERVER['DOCUMENT_ROOT'] . '/imesys/uploads/pacientes/' . $nombre_archivo;
    $imagen_existe = file_exists($ruta_absoluta);
}
?>

<!-- Contenido principal -->
<div id="contentArea" class="content-area">
    <div class="container mx-auto px-4 py-6">
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Editar Perfil del Paciente</h1>
            <a href="perfil_paciente.php?id=<?= $id_paciente ?>" class="boton-outline">
                <i class="fas fa-arrow-left mr-2"></i> Volver al perfil
            </a>
        </div>
        
        <!-- Formulario de edición -->
        <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6">
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <!-- Foto de perfil con previsualización -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Foto de perfil</label>
                <div class="flex items-center">
                    <div class="mr-4">
                        <!-- Contenedor de la vista previa -->
                        <div id="imagePreview" class="w-24 h-24 rounded-full overflow-hidden border-2 border-blue-100 bg-blue-50 flex items-center justify-center">
                            <?php if($paciente['foto'] && $imagen_existe): ?>
                                <img src="<?= $ruta_foto ?>" alt="Foto actual" class="w-full h-full object-cover" id="currentImage">
                            <?php else: ?>
                                <i class="fas fa-user text-3xl text-blue-500" id="defaultIcon"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <input type="file" id="foto" name="foto" accept="image/*" class="text-sm text-gray-500" onchange="previewImage(this)">
                        <p class="mt-1 text-xs text-gray-500">Formatos: JPG, PNG, GIF (Máx. 2MB)</p>
                        <?php if($paciente['foto']): ?>
                            <label class="inline-flex items-center mt-2">
                                <input type="checkbox" name="eliminar_foto" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" id="deleteCheckbox" onclick="toggleImageDelete()">
                                <span class="ml-2 text-sm text-gray-600">Eliminar foto actual</span>
                            </label>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
        
            <!-- Datos personales -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" id="nombre" name="nombre" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        value="<?= htmlspecialchars($paciente['nombre']) ?>">
                </div>
                
                <div>
                    <label for="apellido" class="block text-sm font-medium text-gray-700 mb-1">Apellido <span class="text-red-500">*</span></label>
                    <input type="text" id="apellido" name="apellido" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        value="<?= htmlspecialchars($paciente['apellido']) ?>">
                </div>
                
                <div>
                    <label for="dni" class="block text-sm font-medium text-gray-700 mb-1">DNI <span class="text-red-500">*</span></label>
                    <input type="text" id="dni" name="dni" required maxlength="8"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        value="<?= htmlspecialchars($paciente['dni']) ?>">
                </div>
                
                <div>
                    <label for="correo" class="block text-sm font-medium text-gray-700 mb-1">Correo <span class="text-red-500">*</span></label>
                    <input type="email" id="correo" name="correo" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        value="<?= htmlspecialchars($paciente['correo']) ?>">
                </div>
                
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        value="<?= htmlspecialchars($paciente['telefono']) ?>">
                </div>
                
                <div>
                    <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Nacimiento <span class="text-red-500">*</span></label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        value="<?= htmlspecialchars($paciente['fecha_nacimiento']) ?>">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Género <span class="text-red-500">*</span></label>
                    <div class="mt-1 space-y-2">
                        <div class="flex items-center">
                            <input id="genero-masculino" name="genero" type="radio" value="Masculino" 
                                class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                                <?= $paciente['genero'] === 'Masculino' ? 'checked' : '' ?>>
                            <label for="genero-masculino" class="ml-2 block text-sm text-gray-700">Masculino</label>
                        </div>
                        <div class="flex items-center">
                            <input id="genero-femenino" name="genero" type="radio" value="Femenino" 
                                class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                                <?= $paciente['genero'] === 'Femenino' ? 'checked' : '' ?>>
                            <label for="genero-femenino" class="ml-2 block text-sm text-gray-700">Femenino</label>
                        </div>
                        <div class="flex items-center">
                            <input id="genero-otro" name="genero" type="radio" value="Otro" 
                                class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                                <?= $paciente['genero'] === 'Otro' ? 'checked' : '' ?>>
                            <label for="genero-otro" class="ml-2 block text-sm text-gray-700">Otro</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Dirección -->
            <div class="mb-6">
                <label for="direccion" class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                <textarea id="direccion" name="direccion" rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($paciente['direccion']) ?></textarea>
            </div>
            
            <!-- Botones -->
            <div class="flex justify-end space-x-4">
                <a href="perfil_paciente.php?id=<?= $id_paciente ?>" class="boton-outline">
                    Cancelar
                </a>
                <button type="submit" class="boton">
                    <i class="fas fa-save mr-2"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
<script>
// Función para previsualizar la imagen seleccionada
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const defaultIcon = document.getElementById('defaultIcon');
    const currentImage = document.getElementById('currentImage');
    const deleteCheckbox = document.getElementById('deleteCheckbox');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            // Eliminar elementos existentes
            if (defaultIcon) defaultIcon.remove();
            if (currentImage) currentImage.remove();
            
            // Crear y mostrar la nueva imagen
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'w-full h-full object-cover';
            preview.innerHTML = '';
            preview.appendChild(img);
            
            // Desmarcar checkbox de eliminar si está marcado
            if (deleteCheckbox) {
                deleteCheckbox.checked = false;
            }
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Función para manejar el checkbox de eliminar imagen
function toggleImageDelete() {
    const checkbox = document.getElementById('deleteCheckbox');
    const fileInput = document.getElementById('foto');
    const preview = document.getElementById('imagePreview');
    
    if (checkbox.checked) {
        // Mostrar icono por defecto
        preview.innerHTML = '<i class="fas fa-user text-3xl text-blue-500" id="defaultIcon"></i>';
        // Limpiar input file
        fileInput.value = '';
    } else {
        // Volver a mostrar la imagen actual si existe
        <?php if($paciente['foto'] && $imagen_existe): ?>
            preview.innerHTML = '<img src="<?= $ruta_foto ?>" alt="Foto actual" class="w-full h-full object-cover" id="currentImage">';
        <?php else: ?>
            preview.innerHTML = '<i class="fas fa-user text-3xl text-blue-500" id="defaultIcon"></i>';
        <?php endif; ?>
    }
}
</script>

<?php include 'footer_medico.php'; ?>