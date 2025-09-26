<?php
include 'header_medico.php';

// Verificar si se recibió el ID de la consulta
if (!isset($_GET['id_consulta']) || empty($_GET['id_consulta'])) {
    echo "<script>alert('Consulta no especificada'); window.location.href = 'buscar_paciente.php';</script>";
    exit;
}

$id_consulta = $_GET['id_consulta'];
$id_medico = $_SESSION['id_medico'];

require 'conexion.php';

// Obtener información de la consulta a editar
$query = "SELECT hc.*, 
                 u.nombre as paciente_nombre, u.apellido as paciente_apellido, u.dni
          FROM historial_consultas hc
          JOIN usuarios u ON hc.id_usuario = u.id_usuario
          WHERE hc.id_historial = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id_consulta);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Consulta no encontrada'); window.location.href = 'buscar_paciente.php';</script>";
    exit;
}

$consulta = $result->fetch_assoc();
$stmt->close();

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar los datos
    $motivo = htmlspecialchars($_POST['motivo'] ?? '');
    $observacion = htmlspecialchars($_POST['observacion'] ?? '');
    $diagnostico = htmlspecialchars($_POST['diagnostico'] ?? '');
    $tratamiento = htmlspecialchars($_POST['tratamiento'] ?? '');
    $dato_opcional = htmlspecialchars($_POST['dato_opcional'] ?? '');
    
    // Procesar la imagen subida
    $imagen_path = $consulta['imagen']; // Mantener la imagen existente por defecto
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/consultas/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $file_name = 'consulta_' . $consulta['id_usuario'] . '_' . time() . '.' . $file_ext;
        $target_file = $upload_dir . $file_name;
        
        // Validar tipo de archivo
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($file_ext), $allowed_types)) {
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file)) {
                // Eliminar la imagen anterior si existe
                if (!empty($consulta['imagen']) && file_exists($consulta['imagen'])) {
                    unlink($consulta['imagen']);
                }
                $imagen_path = $target_file;
            }
        }
    }
    
    // Actualizar la consulta en la base de datos
    $query = "UPDATE historial_consultas SET
                motivo = ?,
                observacion = ?,
                diagnostico = ?,
                tratamiento = ?,
                imagen = ?,
                dato_opcional = ?
              WHERE id_historial = ?";
    
    $stmt = $conexion->prepare($query);
    $stmt->bind_param(
        "ssssssi", 
        $motivo, 
        $observacion, 
        $diagnostico, 
        $tratamiento, 
        $imagen_path, 
        $dato_opcional,
        $id_consulta
    );
    
    if ($stmt->execute()) {
        echo "<script>
                alert('Consulta actualizada correctamente');
                window.location.href = 'ver_consulta.php?id_consulta=$id_consulta';
              </script>";
        exit;
    } else {
        $error = "Error al actualizar la consulta: " . $conexion->error;
    }
    
    $stmt->close();
}
$conexion->close();
?>

<!-- Contenido principal -->
<div id="contentArea" class="content-area">
    <div class="container mx-auto px-4 py-6">
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Editar Consulta</h1>
            <a href="ver_consulta.php?id_consulta=<?= $id_consulta ?>" class="boton-outline">
                <i class="fas fa-arrow-left mr-2"></i> Volver a consulta
            </a>
        </div>
        
        <!-- Información del paciente -->
        <div class="bg-blue-50 rounded-lg p-4 mb-6">
            <h2 class="text-lg font-semibold text-blue-800 mb-2">Paciente</h2>
            <p class="font-medium"><?= htmlspecialchars($consulta['paciente_nombre'] . ' ' . $consulta['paciente_apellido']) ?></p>
            <p class="text-sm text-blue-600">DNI: <?= htmlspecialchars($consulta['dni']) ?></p>
            <p class="text-sm text-blue-600">Fecha original: <?= date('d/m/Y H:i', strtotime($consulta['fecha_hora'])) ?></p>
        </div>
        
        <!-- Formulario de edición -->
        <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6">
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <!-- Motivo de consulta -->
            <div class="mb-6">
                <label for="motivo" class="block text-sm font-medium text-gray-700 mb-1">
                    Motivo de la consulta <span class="text-red-500">*</span>
                </label>
                <textarea id="motivo" name="motivo" rows="3" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Describa el motivo principal de la consulta"><?= htmlspecialchars($consulta['motivo']) ?></textarea>
            </div>
            
            <!-- Observaciones -->
            <div class="mb-6">
                <label for="observacion" class="block text-sm font-medium text-gray-700 mb-1">
                    Observaciones
                </label>
                <textarea id="observacion" name="observacion" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Notas médicas durante la consulta"><?= htmlspecialchars($consulta['observacion']) ?></textarea>
            </div>
            
            <!-- Diagnóstico -->
            <div class="mb-6">
                <label for="diagnostico" class="block text-sm font-medium text-gray-700 mb-1">
                    Diagnóstico
                </label>
                <textarea id="diagnostico" name="diagnostico" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Diagnóstico establecido"><?= htmlspecialchars($consulta['diagnostico']) ?></textarea>
            </div>
            
            <!-- Tratamiento -->
            <div class="mb-6">
                <label for="tratamiento" class="block text-sm font-medium text-gray-700 mb-1">
                    Tratamiento recomendado
                </label>
                <textarea id="tratamiento" name="tratamiento" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Medicamentos, terapias, recomendaciones..."><?= htmlspecialchars($consulta['tratamiento']) ?></textarea>
            </div>
            
            <!-- Imagen -->
            <div class="mb-6">
                <label for="imagen" class="block text-sm font-medium text-gray-700 mb-1">
                    Imagen médica (opcional)
                </label>
                <?php if (!empty($consulta['imagen'])): ?>
                    <div class="mb-2">
                        <img src="<?= htmlspecialchars($consulta['imagen']) ?>" alt="Imagen actual" class="h-32 rounded-lg border">
                        <a href="<?= htmlspecialchars($consulta['imagen']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm block mt-1">
                            <i class="fas fa-external-link-alt mr-1"></i> Ver imagen actual
                        </a>
                        <label class="inline-flex items-center mt-2">
                            <input type="checkbox" name="eliminar_imagen" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-600">Eliminar imagen actual</span>
                        </label>
                    </div>
                <?php endif; ?>
                <div class="mt-1 flex items-center">
                    <input type="file" id="imagen" name="imagen" accept="image/*"
                        class="py-2 px-3 border border-gray-300 rounded-md shadow-sm">
                </div>
                <p class="mt-1 text-sm text-gray-500">Formatos aceptados: JPG, PNG, GIF (Máx. 5MB)</p>
            </div>
            
            <!-- Dato opcional -->
            <div class="mb-6">
                <label for="dato_opcional" class="block text-sm font-medium text-gray-700 mb-1">
                    Información adicional
                </label>
                <input type="text" id="dato_opcional" name="dato_opcional"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Otros datos relevantes"
                    value="<?= htmlspecialchars($consulta['dato_opcional']) ?>">
            </div>
            
            <!-- Botones -->
            <div class="flex justify-end space-x-4">
                <a href="ver_consulta.php?id_consulta=<?= $id_consulta ?>" class="boton-outline">
                    Cancelar
                </a>
                <button type="submit" class="boton">
                    <i class="fas fa-save mr-2"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer_medico.php'; ?>