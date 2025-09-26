<?php
include 'header_medico.php';

// Verificar si se recibió ID de paciente (opcional)
$id_paciente = $_GET['id_paciente'] ?? null;

require 'conexion.php';

// Obtener información del paciente si se proporcionó ID
$paciente = null;
if ($id_paciente) {
    $query = "SELECT id_usuario, nombre, apellido, dni FROM usuarios WHERE id_usuario = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id_paciente);
    $stmt->execute();
    $result = $stmt->get_result();
    $paciente = $result->fetch_assoc();
    $stmt->close();
}

// Lista de medicamentos (en una aplicación real, esto vendría de una base de datos)
$medicamentos = [
    'Paracetamol 500mg',
    'Ibuprofeno 400mg',
    'Amoxicilina 500mg',
    'Omeprazol 20mg',
    'Loratadina 10mg'
];

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_medico = $_SESSION['id_medico'];
    $id_paciente = $_POST['id_paciente'];
    
    // Validar que el paciente exista
    $query = "SELECT id_usuario FROM usuarios WHERE id_usuario = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id_paciente);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $error = "El paciente seleccionado no existe";
    } else {
        $fecha_emision = date('Y-m-d H:i:s');
        $meds = [];
        
        // Procesar medicamentos
        foreach ($_POST['medicamentos'] as $index => $nombre) {
            $meds[] = [
                'nombre' => $nombre,
                'dosis' => $_POST['dosis'][$index],
                'frecuencia' => $_POST['frecuencia'][$index]
            ];
        }
        
        $medicamentos_json = json_encode($meds);
        $instrucciones = $_POST['instrucciones'];
        $observaciones = $_POST['observaciones'] ?? '';
        
        // Insertar en la base de datos
        $query = "INSERT INTO recetas (id_medico, id_paciente, fecha_emision, medicamentos, instrucciones, observaciones)
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("iissss", $id_medico, $id_paciente, $fecha_emision, $medicamentos_json, $instrucciones, $observaciones);
        
        if ($stmt->execute()) {
            $id_receta = $stmt->insert_id;
            echo "<script>
                    alert('Receta creada correctamente');
                    window.location.href = 'generar_pdf_receta.php?id=$id_receta';
                  </script>";
            exit;
        } else {
            $error = "Error al crear la receta: " . $conexion->error;
        }
    }
    $stmt->close();
    $conexion->close();
}
?>

<!-- Contenido principal -->
<div id="contentArea" class="content-area">
    <div class="container mx-auto px-4 py-6">
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Nueva Receta Médica</h1>
            <a href="recetas.php" class="boton-outline">
                <i class="fas fa-arrow-left mr-2"></i> Volver a recetas
            </a>
        </div>
        
        <!-- Formulario -->
        <form method="POST" class="bg-white rounded-lg shadow-md p-6">
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <!-- Información del paciente -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">Datos del Paciente</h2>
                
                <?php if ($paciente): ?>
                    <input type="hidden" name="id_paciente" value="<?= $paciente['id_usuario'] ?>">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Nombre</p>
                            <p class="font-medium"><?= htmlspecialchars($paciente['nombre']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Apellido</p>
                            <p class="font-medium"><?= htmlspecialchars($paciente['apellido']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">DNI</p>
                            <p class="font-medium"><?= htmlspecialchars($paciente['dni']) ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="mb-4">
                        <label for="buscar_paciente" class="block text-sm font-medium text-gray-700 mb-1">Buscar Paciente</label>
                        <div class="flex">
                            <input type="text" id="buscar_paciente" placeholder="Ingrese DNI o nombre" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md shadow-sm">
                            <button type="button" id="btn_buscar_paciente" class="boton-outline rounded-l-none">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div id="resultados_pacientes" class="hidden border border-gray-200 rounded-md p-2 mb-4 max-h-40 overflow-y-auto"></div>
                    <input type="hidden" name="id_paciente" id="id_paciente_seleccionado">
                    <div id="info_paciente" class="hidden grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-500">Nombre</p>
                            <p class="font-medium" id="nombre_paciente"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Apellido</p>
                            <p class="font-medium" id="apellido_paciente"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">DNI</p>
                            <p class="font-medium" id="dni_paciente"></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Medicamentos -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">Medicamentos</h2>
                
                <div id="medicamentos_container">
                    <!-- Medicamento 1 -->
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 medicamento-item">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Medicamento</label>
                            <select name="medicamentos[]" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($medicamentos as $med): ?>
                                    <option value="<?= htmlspecialchars($med) ?>"><?= htmlspecialchars($med) ?></option>
                                <?php endforeach; ?>
                                <option value="otro">Otro (especificar)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dosis</label>
                            <input type="text" name="dosis[]" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" placeholder="Ej: 1 comp" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Frecuencia</label>
                            <input type="text" name="frecuencia[]" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" placeholder="Ej: cada 8h" required>
                        </div>
                        <div class="flex items-end">
                            <button type="button" class="boton-outline text-red-600 border-red-300 hover:bg-red-50 h-10 w-full eliminar-medicamento" disabled>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <button type="button" id="agregar_medicamento" class="boton-outline mt-2">
                    <i class="fas fa-plus mr-2"></i> Añadir otro medicamento
                </button>
            </div>
            
            <!-- Instrucciones -->
            <div class="mb-6">
                <label for="instrucciones" class="block text-sm font-medium text-gray-700 mb-1">Instrucciones Generales</label>
                <textarea id="instrucciones" name="instrucciones" rows="4" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Indicaciones para el paciente"></textarea>
            </div>
            
            <!-- Observaciones -->
            <div class="mb-6">
                <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                <textarea id="observaciones" name="observaciones" rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Notas adicionales"></textarea>
            </div>
            
            <!-- Botones -->
            <div class="flex justify-end space-x-4">
                <button type="reset" class="boton-outline">
                    Limpiar formulario
                </button>
                <button type="submit" class="boton">
                    <i class="fas fa-save mr-2"></i> Guardar Receta
                </button>
            </div>
        </form>
    </div>
</div>

<script src="js/buscar_pacientes_receta.js"></script>

<script>
// Manejar medicamentos
document.getElementById('agregar_medicamento').addEventListener('click', function() {
    const container = document.getElementById('medicamentos_container');
    const count = container.querySelectorAll('.medicamento-item').length;
    const nuevoMedicamento = `
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 medicamento-item">
            <div class="md:col-span-2">
                <select name="medicamentos[]" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($medicamentos as $med): ?>
                        <option value="<?= htmlspecialchars($med) ?>"><?= htmlspecialchars($med) ?></option>
                    <?php endforeach; ?>
                    <option value="otro">Otro (especificar)</option>
                </select>
            </div>
            <div>
                <input type="text" name="dosis[]" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" placeholder="Ej: 1 comp" required>
            </div>
            <div>
                <input type="text" name="frecuencia[]" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" placeholder="Ej: cada 8h" required>
            </div>
            <div class="flex items-end">
                <button type="button" class="boton-outline text-red-600 border-red-300 hover:bg-red-50 h-10 w-full eliminar-medicamento">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', nuevoMedicamento);
    
    // Habilitar botones de eliminar si hay más de un medicamento
    if (count + 1 > 1) {
        document.querySelectorAll('.eliminar-medicamento').forEach(btn => {
            btn.disabled = false;
        });
    }
});

// Eliminar medicamento
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('eliminar-medicamento')) {
        const item = e.target.closest('.medicamento-item');
        item.remove();
        
        // Deshabilitar botones de eliminar si solo queda un medicamento
        const count = document.querySelectorAll('.medicamento-item').length;
        if (count <= 1) {
            document.querySelector('.eliminar-medicamento').disabled = true;
        }
    }
});
</script>

<?php include 'footer_medico.php'; ?>