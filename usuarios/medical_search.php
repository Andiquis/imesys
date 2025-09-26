<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: login_usuario.php");
    exit;
}

require 'conexion.php';

// Configuración de la API
define('API_KEY', 'AIzaSyC410TATtejgU633r2ummEkH2TP-Nx9lyE');
define('SEARCH_ENGINE_ID', 'f05c3413b666740ee');
define('MEDICAL_DOMAINS', 'webmd.com,mayoclinic.org,medlineplus.gov,nih.gov,who.int,healthline.com');

function isMedicalQuery($query) {
    $medicalKeywords = [
        'medic', 'enfermedad', 'síntoma', 'tratamiento', 'diagnóstico', 
        'paciente', 'hospital', 'clínica', 'doctor', 'médico', 'cirugía',
        'fármaco', 'medicamento', 'receta', 'salud', 'terapia', 'cáncer',
        'diabetes', 'cardio', 'neur', 'pediatr', 'ginecolog', 'oftalmolog',
        'dermatolog', 'psiquiatr', 'fisioterap', 'enfermer', 'farmac',
        'anatom', 'fisiol', 'patolog', 'epidemiol', 'traumatolog'
    ];
    
    $query = strtolower($query);
    
    foreach ($medicalKeywords as $keyword) {
        if (strpos($query, $keyword) !== false) return true;
    }
    
    return false;
}

function searchMedicalInfo($query) {
    if (!isMedicalQuery($query)) {
        return [
            'error' => 'Lo siento, solo puedo proporcionar información sobre temas médicos.'
        ];
    }
    
    $url = "https://www.googleapis.com/customsearch/v1?q=".urlencode($query).
           "&key=".API_KEY."&cx=".SEARCH_ENGINE_ID."&siteSearch=".MEDICAL_DOMAINS;
    
    // Configuración mejorada de cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_FAILONERROR => true
    ]);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        return ['error' => 'Error de conexión: '.curl_error($ch)];
    }
    
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    // Depuración (descomenta para ver errores)
    // file_put_contents('api_response.txt', print_r($data, true));
    
    if (isset($data['error'])) {
        $errorMsg = $data['error']['message'] ?? 'Error desconocido de la API';
        return ['error' => "Error de API: $errorMsg"];
    }
    
    return isset($data['items']) ? array_slice($data['items'], 0, 5) : [
        'error' => 'No se encontraron resultados médicos relevantes.'
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
    $query = trim($_POST['query']);
    
    // Validación básica
    if (empty($query)) {
        echo json_encode(['error' => 'Por favor ingresa un término de búsqueda']);
        exit;
    }
    
    $results = searchMedicalInfo($query);
    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}
?>