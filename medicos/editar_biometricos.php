<?php
session_start();

// Primero incluye la conexión
require_once 'conexion.php';

// Luego incluye el header, que necesita `$conexion`
include 'header_medico.php';


// Mostrar errores de MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Obtener el ID del paciente desde la URL
$id_paciente = isset($_GET['id_paciente']) ? intval($_GET['id_paciente']) : 0;

// Consultar los últimos datos biométricos del paciente
$query = "SELECT * FROM datos_biometricos WHERE id_usuario = ? ORDER BY fecha_registro DESC LIMIT 1";
$stmt = $conexion->prepare($query);
if (!$stmt) {
    die("❌ Error al preparar consulta de selección: " . $conexion->error);
}
$stmt->bind_param("i", $id_paciente);
$stmt->execute();
$result = $stmt->get_result();
$biometrico = $result->fetch_assoc();

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y validar los datos del formulario
    $peso = isset($_POST['peso']) ? floatval($_POST['peso']) : null;
    $altura = isset($_POST['altura']) ? floatval($_POST['altura']) : null;
    $presion_arterial = isset($_POST['presion_arterial']) ? htmlspecialchars($_POST['presion_arterial']) : null;
    $frecuencia_cardiaca = isset($_POST['frecuencia_cardiaca']) ? intval($_POST['frecuencia_cardiaca']) : null;
    $nivel_glucosa = isset($_POST['nivel_glucosa']) ? floatval($_POST['nivel_glucosa']) : null;

    if ($biometrico) {
        // Actualizar el registro existente
        $update_query = "UPDATE datos_biometricos SET 
                        peso = ?, 
                        altura = ?, 
                        presion_arterial = ?, 
                        frecuencia_cardiaca = ?, 
                        nivel_glucosa = ? 
                        WHERE id_dato = ?";
        $stmt = $conexion->prepare($update_query);
        if (!$stmt) {
            die("❌ Error al preparar consulta de actualización: " . $conexion->error);
        }
        $stmt->bind_param("ddssdi", $peso, $altura, $presion_arterial, 
                         $frecuencia_cardiaca, $nivel_glucosa, $biometrico['id_dato']);
    } else {
        // Insertar un nuevo registro
        $insert_query = "INSERT INTO datos_biometricos 
                        (id_usuario, peso, altura, presion_arterial, frecuencia_cardiaca, nivel_glucosa) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($insert_query);
        if (!$stmt) {
            die("❌ Error al preparar consulta de inserción: " . $conexion->error);
        }
        $stmt->bind_param("iddssd", $id_paciente, $peso, $altura, $presion_arterial, 
                         $frecuencia_cardiaca, $nivel_glucosa);
    }

    try {
        if ($stmt->execute()) {
            echo "<div style='padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px;'>
                    ✅ ¡Registro exitoso de los datos biométricos!
                  </div>";
        } else {
            echo "<div style='padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px;'>
                    ❌ Error al ejecutar la consulta: " . $stmt->error . "
                  </div>";
        }
    } catch (Exception $e) {
        echo "<div style='padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px;'>
                ❌ Excepción capturada: " . $e->getMessage() . "
              </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Biométricos - IMESYS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100"><br><br><br><br>
    <div class="flex justify-between items-center mb-6">
        
        <h1 class="text-2xl font-bold text-gray-800">Editar Datos Biométricos</h1>
        <a href="perfil_paciente.php?id=<?= $id_paciente ?>"  class="boton-outline">
            <i class="fas fa-arrow-left mr-2"></i> Volver al perfil
        </a>
    </div>

    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md overflow-hidden p-6">
            <form method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Columna 1 -->
                    <div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="peso">Peso (kg)</label>
                            <input type="number" step="0.1" id="peso" name="peso" value="<?= $biometrico['peso'] ?? '' ?>" 
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="altura">Altura (m)</label>
                            <input type="number" step="0.01" id="altura" name="altura" value="<?= $biometrico['altura'] ?? '' ?>" 
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                    </div>
                    
                    <!-- Columna 2 -->
                    <div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="presion_arterial">Presión Arterial</label>
                            <input type="text" id="presion_arterial" name="presion_arterial" value="<?= htmlspecialchars($biometrico['presion_arterial'] ?? '') ?>" 
                                   placeholder="Ej: 120/80"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="frecuencia_cardiaca">Frecuencia Cardíaca (lpm)</label>
                            <input type="number" id="frecuencia_cardiaca" name="frecuencia_cardiaca" value="<?= $biometrico['frecuencia_cardiaca'] ?? '' ?>" 
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="nivel_glucosa">Nivel de Glucosa (mg/dL)</label>
                            <input type="number" step="0.1" id="nivel_glucosa" name="nivel_glucosa" value="<?= $biometrico['nivel_glucosa'] ?? '' ?>" 
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end gap-4">
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
    <?php include 'footer_medico.php'; ?>
</body>
</html>
