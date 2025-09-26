<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

require 'conexion.php';

// Configuración de la API de búsqueda (Google Custom Search)
define('API_KEY', 'AIzaSyC410TATtejgU633r2ummEkH2TP-Nx9lyE');
define('SEARCH_ENGINE_ID', 'f05c3413b666740ee');

function searchGeneralInfo($query) {
    $url = "https://www.googleapis.com/customsearch/v1?q=" . urlencode($query) . 
           "&key=" . API_KEY . 
           "&cx=" . SEARCH_ENGINE_ID;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    $response = curl_exec($ch);
    
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['error' => 'Error en la solicitud a la API: ' . $error];
    }
    
    curl_close($ch);
    $data = json_decode($response, true);
    
    if (isset($data['error'])) {
        return ['error' => 'Error de la API: ' . ($data['error']['message'] ?? 'Error desconocido')];
    }
    
    if (isset($data['items'])) {
        $results = [];
        foreach (array_slice($data['items'], 0, 5) as $item) {
            $results[] = [
                'title' => $item['title'] ?? 'Sin título',
                'snippet' => $item['snippet'] ?? 'Sin descripción',
                'link' => $item['link'] ?? '#'
            ];
        }
        return $results;
    }
    
    return ['error' => 'No se encontraron resultados relevantes. Por favor, intenta con términos diferentes.'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
    $query = trim($_POST['query']);
    
    if (empty($query)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Por favor, ingrese un término de búsqueda']);
        exit;
    }
    
    $results = searchGeneralInfo($query);
    
    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['error' => 'Método no permitido']);
?>