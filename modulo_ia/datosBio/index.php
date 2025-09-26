<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['id_usuario'])) {
    header("Location: ../../login_usuario.php");
    exit;
}

// Simulación de AuthService
class AuthService {
    public static function getSession() {
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
            return [
                'id_usuario' => $_SESSION['id_usuario'],
                'nombre' => $_SESSION['nombre'],
                'apellido' => $_SESSION['apellido'],
                'correo' => $_SESSION['correo']
            ];
        }
        return null;
    }
}

// Configuración de la API
$API_URL_BIOMETRIC = 'http://localhost:5000/api/datos_biometricos';
$API_URL_PREDICTION = 'http://localhost:5000/api/prediccion_bio';

// Ejemplos predefinidos
$examples = [
    'healthy' => [
        'peso' => 70,
        'altura' => 170,
        'presion_arterial' => '120/80',
        'frecuencia_cardiaca' => 65,
        'nivel_glucosa' => 85
    ],
    'hypertension' => [
        'peso' => 90,
        'altura' => 175,
        'presion_arterial' => '150/95',
        'frecuencia_cardiaca' => 88,
        'nivel_glucosa' => 110
    ],
    'diabetic' => [
        'peso' => 85,
        'altura' => 168,
        'presion_arterial' => '135/85',
        'frecuencia_cardiaca' => 75,
        'nivel_glucosa' => 180
    ]
];

// Inicialización de variables
$currentUser = AuthService::getSession();
$userHistory = [];
$selectedHistoryItem = null;
$notification = null;
$isLoadingHistory = false;
$predictionResult = null;
$errors = [];
$formData = [];

// Funciones auxiliares
function makeHttpRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data))
        ]);
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    }
    throw new Exception("Error en la solicitud HTTP: Código $httpCode");
}

function validateForm($data) {
    $errors = [];
    if (empty($data['peso']) || !is_numeric($data['peso']) || $data['peso'] <= 0) {
        $errors[] = 'El peso es requerido y debe ser mayor que 0';
    }
    if (empty($data['altura']) || !is_numeric($data['altura']) || $data['altura'] <= 0) {
        $errors[] = 'La altura es requerida y debe ser mayor que 0';
    }
    if (empty($data['presion_arterial'])) {
        $errors[] = 'La presión arterial es requerida';
    }
    if (empty($data['frecuencia_cardiaca']) || !is_numeric($data['frecuencia_cardiaca']) || $data['frecuencia_cardiaca'] <= 0) {
        $errors[] = 'La frecuencia cardíaca es requerida y debe ser mayor que 0';
    }
    if (empty($data['nivel_glucosa']) || !is_numeric($data['nivel_glucosa']) || $data['nivel_glucosa'] <= 0) {
        $errors[] = 'El nivel de glucosa es requerido y debe ser mayor que 0';
    }
    return $errors;
}

function loadUserHistory($userId, &$userHistory, &$selectedHistoryItem) {
    global $API_URL_BIOMETRIC;
    try {
        $data = makeHttpRequest("$API_URL_BIOMETRIC/usuario/$userId");
        $userHistory = $data;
        usort($userHistory, function($a, $b) {
            $dateA = $a['fecha_registro'] ? strtotime($a['fecha_registro']) : 0;
            $dateB = $b['fecha_registro'] ? strtotime($b['fecha_registro']) : 0;
            return $dateB - $dateA;
        });
        if (!empty($userHistory)) {
            $selectId = isset($_GET['select']) ? (int)$_GET['select'] : null;
            foreach ($userHistory as $item) {
                if ($selectId && $item['id_dato'] === $selectId) {
                    $selectedHistoryItem = $item;
                    break;
                }
            }
            if (!$selectedHistoryItem) {
                $selectedHistoryItem = $userHistory[0];
            }
        }
    } catch (Exception $e) {
        return "Error al cargar el historial: " . $e->getMessage();
    }
    return null;
}

// Cargar historial al inicio
if ($currentUser && isset($currentUser['id_usuario'])) {
    $isLoadingHistory = true;
    $error = loadUserHistory($currentUser['id_usuario'], $userHistory, $selectedHistoryItem);
    if ($error) {
        $notification = ['message' => $error, 'type' => 'error'];
    }
    $isLoadingHistory = false;
} else {
    header("Location: login.php");
    exit;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_submit'])) {
    $formData = [
        'peso' => $_POST['peso'] ?? '',
        'altura' => $_POST['altura'] ?? '',
        'presion_arterial' => $_POST['presion_arterial'] ?? '',
        'frecuencia_cardiaca' => $_POST['frecuencia_cardiaca'] ?? '',
        'nivel_glucosa' => $_POST['nivel_glucosa'] ?? '',
        'descripcion_resultado' => $_POST['descripcion_resultado'] ?? ''
    ];
    
    $errors = validateForm($formData);
    
    if (empty($errors) && $currentUser) {
        $biometricData = [
            'id_usuario' => $currentUser['id_usuario'],
            'peso' => (float)$formData['peso'],
            'altura' => (float)$formData['altura'],
            'presion_arterial' => $formData['presion_arterial'],
            'frecuencia_cardiaca' => (int)$formData['frecuencia_cardiaca'],
            'nivel_glucosa' => (float)$formData['nivel_glucosa'],
            'descripcion_resultado' => $formData['descripcion_resultado']
        ];
        
        try {
            // Obtener predicción
            $predictionData = [
                'peso' => $biometricData['peso'],
                'altura' => $biometricData['altura'],
                'presion_arterial' => $biometricData['presion_arterial'],
                'frecuencia_cardiaca' => $biometricData['frecuencia_cardiaca'],
                'nivel_glucosa' => $biometricData['nivel_glucosa']
            ];
            $prediction = makeHttpRequest($API_URL_PREDICTION, 'POST', ['message' => $predictionData]);
            $predictionResult = $prediction['response'];
            $biometricData['resultado_prediccion'] = $predictionResult;
            
            // Guardar datos biométricos
            $result = makeHttpRequest($API_URL_BIOMETRIC, 'POST', $biometricData);
            $notification = [
                'message' => 'Dato biométrico guardado con ID: 00' . $result['id'] . 'A Puede revisar sus resultados en el historial',
                'type' => 'success'
            ];
            
            // Recargar historial
            loadUserHistory($currentUser['id_usuario'], $userHistory, $selectedHistoryItem);
            $formData = []; // Limpiar formulario
            $predictionResult = null;
        } catch (Exception $e) {
            $notification = ['message' => 'Error al guardar los datos: ' . $e->getMessage(), 'type' => 'error'];
        }
    } else {
        $notification = ['message' => implode('. ', $errors), 'type' => 'error'];
    }
}

// Eliminar dato
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_dato'])) {
    $id = (int)$_POST['delete_dato'];
    try {
        makeHttpRequest("$API_URL_BIOMETRIC/$id", 'DELETE');
        $notification = ['message' => 'Dato biométrico eliminado correctamente', 'type' => 'success'];
        loadUserHistory($currentUser['id_usuario'], $userHistory, $selectedHistoryItem);
        if ($selectedHistoryItem && $selectedHistoryItem['id_dato'] === $id) {
            $selectedHistoryItem = !empty($userHistory) ? $userHistory[0] : null;
        }
    } catch (Exception $e) {
        $notification = ['message' => 'Error al eliminar: ' . $e->getMessage(), 'type' => 'error'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Datos Biométricos</title>
    <link rel="stylesheet" href="styles.scss">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mx-auto p-4 max-w-7xl">
        <!-- Notificación -->
        <?php if ($notification): ?>
            <div class="mb-4 p-4 rounded-lg shadow-md transition-opacity duration-300 notification <?php echo $notification['type'] === 'success' ? 'bg-green-100' : 'bg-red-100'; ?>">
                <?php echo htmlspecialchars($notification['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para nuevos datos biométricos -->
        <div style="margin-bottom: 2rem; background-color: #fff; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem; color: #333;">Registrar Nuevo Dato Biométrico</h2>
            <form method="POST" action="" id="biometricForm">
                <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #555;">Peso (kg)</label>
                        <input type="number" name="peso" value="<?php echo htmlspecialchars($formData['peso'] ?? ''); ?>" style="margin-top: 0.25rem; width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 0.25rem;" required min="0" step="0.1">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #555;">Altura (cm)</label>
                        <input type="number" name="altura" value="<?php echo htmlspecialchars($formData['altura'] ?? ''); ?>" style="margin-top: 0.25rem; width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 0.25rem;" required min="0" step="0.1">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #555;">Presión Arterial (mmHg)</label>
                        <input type="text" name="presion_arterial" value="<?php echo htmlspecialchars($formData['presion_arterial'] ?? ''); ?>" style="margin-top: 0.25rem; width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 0.25rem;" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #555;">Frecuencia Cardíaca (lpm)</label>
                        <input type="number" name="frecuencia_cardiaca" value="<?php echo htmlspecialchars($formData['frecuencia_cardiaca'] ?? ''); ?>" style="margin-top: 0.25rem; width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 0.25rem;" required min="0">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #555;">Nivel de Glucosa (mg/dL)</label>
                        <input type="number" name="nivel_glucosa" value="<?php echo htmlspecialchars($formData['nivel_glucosa'] ?? ''); ?>" style="margin-top: 0.25rem; width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 0.25rem;" required min="0" step="0.1">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #555;">Descripción</label>
                        <input type="text" name="descripcion_resultado" value="<?php echo htmlspecialchars($formData['descripcion_resultado'] ?? ''); ?>" style="margin-top: 0.25rem; width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 0.25rem;">
                    </div>
                </div>
                <div style="margin-top: 1rem; display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    <button type="submit" name="create_submit" style="background-color: #2563eb; color: #fff; padding: 0.5rem 1rem; border-radius: 0.25rem; border: none; cursor: pointer;">Registrar</button>
                    <button type="button" onclick="loadExample('healthy')" style="background-color: #6b7280; color: #fff; padding: 0.5rem 1rem; border-radius: 0.25rem; border: none; cursor: pointer;">Cargar Ejemplo Saludable</button>
                    <button type="button" onclick="loadExample('hypertension')" style="background-color: #6b7280; color: #fff; padding: 0.5rem 1rem; border-radius: 0.25rem; border: none; cursor: pointer;">Cargar Ejemplo Hipertensión</button>
                    <button type="button" onclick="loadExample('diabetic')" style="background-color: #6b7280; color: #fff; padding: 0.5rem 1rem; border-radius: 0.25rem; border: none; cursor: pointer;">Cargar Ejemplo Diabético</button>
                </div>
            </form>
            <?php if ($predictionResult): ?>
                <div style="margin-top: 1rem; padding: 1rem; background-color: #dbeafe; color: #1e40af; border-radius: 0.5rem;">
                    Predicción de la IA: <?php echo htmlspecialchars($predictionResult); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Historial de usuario -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Historial de Datos Biométricos</h2>
            <?php if ($isLoadingHistory): ?>
                <div class="text-center text-gray-600">Cargando historial...</div>
            <?php elseif (empty($userHistory)): ?>
                <div class="text-center text-gray-500">No hay datos biométricos registrados.</div>
            <?php else: ?>
                <div class="flex flex-row gap-4">
                    <!-- Lista de historial -->
                    <div class="w-1/3">
                        <ul class="divide-y divide-gray-200">
                            <?php foreach ($userHistory as $item): ?>
                                <li class="py-2 cursor-pointer <?php echo ($selectedHistoryItem && $selectedHistoryItem['id_dato'] === $item['id_dato']) ? 'bg-gray-100' : ''; ?>"
                                    onclick="window.location.href='?select=<?php echo $item['id_dato']; ?>'">
                                    <p class="font-medium text-gray-800">Registro: 00<?php echo htmlspecialchars($item['id_dato']); ?>A</p>
                                    <p><strong class="text-gray-700">Descripción:</strong> <?php echo htmlspecialchars($item['descripcion_resultado'] ?? 'N/A'); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars(date('d/m/Y, H:i A', strtotime($item['fecha_registro']))); ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <!-- Detalles del elemento seleccionado -->
                    <div class="w-2/3">
                    <?php if ($selectedHistoryItem): ?>
                        <div class="p-4 bg-gray-50 rounded-lg h-full">
                            <h3 class="text-lg font-semibold text-blue-900 mb-4">Detalles del Registro: 00<?php echo htmlspecialchars($selectedHistoryItem['id_dato']); ?>A</h3>
                            <table class="w-full border-collapse border border-gray-300 text-sm">
                                <tbody>
                                    <tr class="border-b border-gray-300">
                                        <td class="p-3 font-semibold text-blue-900 w-1/3 bg-gray-100">Peso:</td>
                                        <td class="p-3 text-gray-700"><?php echo htmlspecialchars($selectedHistoryItem['peso']); ?> kg</td>
                                    </tr>
                                    <tr class="border-b border-gray-300">
                                        <td class="p-3 font-semibold text-blue-900 w-1/3 bg-gray-100">Altura:</td>
                                        <td class="p-3 text-gray-700"><?php echo htmlspecialchars($selectedHistoryItem['altura']); ?> cm</td>
                                    </tr>
                                    <tr class="border-b border-gray-300">
                                        <td class="p-3 font-semibold text-blue-900 w-1/3 bg-gray-100">Presión Arterial:</td>
                                        <td class="p-3 text-gray-700"><?php echo htmlspecialchars($selectedHistoryItem['presion_arterial']); ?> mmHg</td>
                                    </tr>
                                    <tr class="border-b border-gray-300">
                                        <td class="p-3 font-semibold text-blue-900 w-1/3 bg-gray-100">Frecuencia Cardíaca:</td>
                                        <td class="p-3 text-gray-700"><?php echo htmlspecialchars($selectedHistoryItem['frecuencia_cardiaca']); ?> lpm</td>
                                    </tr>
                                    <tr class="border-b border-gray-300">
                                        <td class="p-3 font-semibold text-blue-900 w-1/3 bg-gray-100">Nivel de Glucosa:</td>
                                        <td class="p-3 text-gray-700"><?php echo htmlspecialchars($selectedHistoryItem['nivel_glucosa']); ?> mg/dL</td>
                                    </tr>
                                    <tr class="border-b border-gray-300">
                                        <td class="p-3 font-semibold text-blue-900 w-1/3 bg-gray-100">Predicción de la IA:</td>
                                        <td class="p-3 text-gray-700"><?php echo htmlspecialchars($selectedHistoryItem['resultado_prediccion'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr class="border-b border-gray-300">
                                        <td class="p-3 font-semibold text-blue-900 w-1/3 bg-gray-100">Fecha:</td>
                                        <td class="p-3 text-gray-700"><?php echo htmlspecialchars(date('d/m/Y, H:i:s A', strtotime($selectedHistoryItem['fecha_registro']))); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                            <form method="POST" action="" onsubmit="return confirm('¿Está seguro de que desea eliminar este dato biométrico?');">
                                <input type="hidden" name="delete_dato" value="<?php echo $selectedHistoryItem['id_dato']; ?>">
                                <button type="submit" class="mt-4 bg-red-300 text-white px-4 py-2 rounded-md hover:bg-red-600 transition-colors">Eliminar</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Define los datos de ejemplo directamente en JavaScript para cargarlos en el formulario
        const examples = {
            healthy: {
                peso: 70,
                altura: 170,
                presion_arterial: '120/80',
                frecuencia_cardiaca: 65,
                nivel_glucosa: 85,
                descripcion_resultado: ''
            },
            hypertension: {
                peso: 90,
                altura: 175,
                presion_arterial: '150/95',
                frecuencia_cardiaca: 88,
                nivel_glucosa: 110,
                descripcion_resultado: ''
            },
            diabetic: {
                peso: 85,
                altura: 168,
                presion_arterial: '135/85',
                frecuencia_cardiaca: 75,
                nivel_glucosa: 180,
                descripcion_resultado: ''
            }
        };

        // Función para cargar los datos de ejemplo en el formulario
        function loadExample(type) {
            const example = examples[type];
            if (example) {
                const form = document.getElementById('biometricForm');
                form.querySelector('input[name="peso"]').value = example.peso;
                form.querySelector('input[name="altura"]').value = example.altura;
                form.querySelector('input[name="presion_arterial"]').value = example.presion_arterial;
                form.querySelector('input[name="frecuencia_cardiaca"]').value = example.frecuencia_cardiaca;
                form.querySelector('input[name="nivel_glucosa"]').value = example.nivel_glucosa;
                form.querySelector('input[name="descripcion_resultado"]').value = example.descripcion_resultado;
            }
        }

        // Manejar selección de elementos del historial
        <?php if (isset($_GET['select'])): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const selectedItem = document.querySelector('.cursor-pointer.bg-gray-100');
                if (selectedItem) {
                    selectedItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        <?php endif; ?>

        // Ocultar notificaciones después de 5 segundos
        <?php if ($notification): ?>
            setTimeout(() => {
                const notification = document.querySelector('.mb-4.p-4.rounded-lg');
                if (notification) {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>