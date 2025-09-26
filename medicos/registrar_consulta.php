<?php
include 'header_medico.php';

// Verificar si se recibió el ID del paciente
if (!isset($_GET['id_paciente']) || empty($_GET['id_paciente'])) {
    echo "<script>alert('Paciente no especificado'); window.location.href = 'buscar_paciente.php';</script>";
    exit;
}

$id_paciente = $_GET['id_paciente'];
$id_medico = $_SESSION['id_medico'];

// Obtener información básica del paciente
require 'conexion.php';
$query_paciente = "SELECT nombre, apellido, dni FROM usuarios WHERE id_usuario = ?";
$stmt_paciente = $conexion->prepare($query_paciente);
$stmt_paciente->bind_param("i", $id_paciente);
$stmt_paciente->execute();
$result_paciente = $stmt_paciente->get_result();

if ($result_paciente->num_rows === 0) {
    echo "<script>alert('Paciente no encontrado'); window.location.href = 'buscar_paciente.php';</script>";
    exit;
}

$paciente = $result_paciente->fetch_assoc();
$stmt_paciente->close();

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar los datos
    $motivo = htmlspecialchars($_POST['motivo'] ?? '');
    $observacion = htmlspecialchars($_POST['observacion'] ?? '');
    $diagnostico = htmlspecialchars($_POST['diagnostico'] ?? '');
    $tratamiento = htmlspecialchars($_POST['tratamiento'] ?? '');
    $dato_opcional = htmlspecialchars($_POST['dato_opcional'] ?? '');
    
    // Procesar la imagen subida
    $imagen_path = '';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/consultas/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $file_name = 'consulta_' . $id_paciente . '_' . time() . '.' . $file_ext;
        $target_file = $upload_dir . $file_name;
        
        // Validar tipo de archivo
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($file_ext), $allowed_types)) {
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file)) {
                $imagen_path = $target_file;
            }
        }
    }
    
    // Insertar la consulta en la base de datos
    $query = "INSERT INTO historial_consultas (
                id_usuario, 
                id_medico, 
                motivo, 
                observacion, 
                diagnostico, 
                tratamiento, 
                imagen, 
                dato_opcional
              ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($query);
    $stmt->bind_param(
        "iissssss", 
        $id_paciente, 
        $id_medico, 
        $motivo, 
        $observacion, 
        $diagnostico, 
        $tratamiento, 
        $imagen_path, 
        $dato_opcional
    );
    
    if ($stmt->execute()) {
        echo "<script>
                alert('Consulta registrada correctamente');
                window.location.href = 'historial_medico.php?id=$id_paciente';
              </script>";
        exit;
    } else {
        $error = "Error al registrar la consulta: " . $conexion->error;
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
            <h1 class="text-2xl font-bold text-gray-800">Registrar Nueva Consulta</h1>
            <a href="perfil_paciente.php?id=<?= $id_paciente ?>" class="boton-outline">
                <i class="fas fa-arrow-left mr-2"></i> Volver al perfil
            </a>
        </div>
        
        <!-- Información del paciente -->
        <div class="bg-blue-50 rounded-lg p-4 mb-6">
            <h2 class="text-lg font-semibold text-blue-800 mb-2">Paciente</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-blue-600">Nombre completo</p>
                    <p class="font-medium"><?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-blue-600">DNI</p>
                    <p class="font-medium"><?= htmlspecialchars($paciente['dni']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-blue-600">Fecha de consulta</p>
                    <p class="font-medium"><?= date('d/m/Y H:i') ?></p>
                </div>
            </div>
        </div>
        
        <!-- Formulario de consulta -->
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
                    placeholder="Describa el motivo principal de la consulta"></textarea>
            </div>
            
            <!-- Observaciones -->
            <div class="mb-6">
                <label for="observacion" class="block text-sm font-medium text-gray-700 mb-1">
                    Observaciones
                </label>
                <textarea id="observacion" name="observacion" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Notas médicas durante la consulta"></textarea>
            </div>
            
            <!-- Diagnóstico -->
            <div class="mb-6">
                <label for="diagnostico" class="block text-sm font-medium text-gray-700 mb-1">
                    Diagnóstico
                </label>
                <textarea id="diagnostico" name="diagnostico" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Diagnóstico establecido"></textarea>
            </div>
            
            <!-- Tratamiento -->
            <div class="mb-6">
                <label for="tratamiento" class="block text-sm font-medium text-gray-700 mb-1">
                    Tratamiento recomendado
                </label>
                <textarea id="tratamiento" name="tratamiento" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Medicamentos, terapias, recomendaciones..."></textarea>
            </div>
            
            <!-- Imagen -->
            <div class="mb-6">
                <label for="imagen" class="block text-sm font-medium text-gray-700 mb-1">
                    Imagen médica (opcional)
                </label>
                <div class="mt-1 flex items-center">
                    <input type="file" id="imagen" name="imagen" accept="image/*"
                        class="py-2 px-3 border border-gray-300 rounded-md shadow-sm">
                </div>
                <p class="mt-1 text-sm text-gray-500">Formatos aceptados: JPG, PNG, GIF</p>
            </div>
            
            <!-- Dato opcional -->
            <div class="mb-6">
                <label for="dato_opcional" class="block text-sm font-medium text-gray-700 mb-1">
                    Información adicional
                </label>
                <input type="text" id="dato_opcional" name="dato_opcional"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Otros datos relevantes">
            </div>
            
            <!-- Botones -->
            <div class="flex justify-end space-x-4">
                <a href="historial_medico.php?id=<?= $id_paciente ?>" class="boton-outline">
                    Cancelar
                </a>
                <button type="submit" class="boton">
                    <i class="fas fa-save mr-2"></i> Guardar Consulta
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer_medico.php'; ?>