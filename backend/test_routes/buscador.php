<?php
// buscador.php

// Variables para mensajes y resultados
$results = [];
$error = null;
$query = '';

// Procesar la búsqueda cuando se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
    // Capturar la consulta
    $query = trim($_POST['query']);
    
    if (empty($query)) {
        $error = "Por favor ingresa un término de búsqueda.";
    } else {
        // Configuración de la solicitud al endpoint
        $apiUrl = 'http://localhost:5000/api/buscador';
        
        // Preparar los datos para enviar
        $postData = json_encode(['query' => $query]);
        
        // Configurar la solicitud curl
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData)
        ]);
        
        // Ejecutar la solicitud
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Verificar errores de curl
        if (curl_errno($ch)) {
            $error = "Error de conexión: " . curl_error($ch);
        } else if ($httpCode !== 200) {
            $error = "Error del servidor (código $httpCode)";
        } else {
            // Decodificar respuesta JSON
            $results = json_decode($response, true);
            
            // Verificar si hay un mensaje de error en la respuesta
            if (isset($results['error'])) {
                $error = $results['error'];
                $results = [];
            } else if (empty($results)) {
                $error = "No se encontraron resultados para tu búsqueda.";
            }
        }
        
        curl_close($ch);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscador</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <!-- Encabezado -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-indigo-800">Buscador Inteligente</h1>
                <p class="text-indigo-600 mt-2">Encuentra lo que necesitas en segundos</p>
            </div>
            
            <!-- Formulario de búsqueda -->
            <form method="POST" action="" class="mb-8">
                <div class="relative">
                    <input 
                        type="text" 
                        name="query" 
                        value="<?php echo htmlspecialchars($query); ?>"
                        placeholder="¿Qué estás buscando?" 
                        class="w-full px-5 py-4 pr-16 text-gray-700 bg-white border-2 border-indigo-300 rounded-full focus:outline-none focus:border-indigo-500 shadow-md"
                        required
                    >
                    <button 
                        type="submit" 
                        class="absolute right-0 h-full px-6 text-white bg-indigo-600 rounded-r-full hover:bg-indigo-700 transition-colors flex items-center justify-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </form>
            
            <!-- Resultados de búsqueda -->
            <?php if (!empty($results) || $error): ?>
                <div class="bg-white rounded-lg shadow-xl p-6 border border-gray-200">
                    <!-- Cabecera de resultados -->
                    <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-3">
                        <h2 class="text-xl font-semibold text-gray-800">
                            <?php echo empty($error) ? "Resultados para \"" . htmlspecialchars($query) . "\"" : "Búsqueda"; ?>
                        </h2>
                    </div>
                    
                    <!-- Mensaje de error -->
                    <?php if ($error): ?>
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <p class="text-red-700"><?php echo htmlspecialchars($error); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Lista de resultados -->
                    <?php if (!empty($results)): ?>
                        <div class="space-y-4 mt-2">
                            <?php foreach ($results as $result): ?>
                                <div class="border-b border-gray-100 pb-4 last:border-0">
                                    <a href="<?php echo htmlspecialchars($result['link']); ?>" target="_blank" class="block hover:bg-indigo-50 p-3 rounded transition-colors">
                                        <h3 class="text-lg font-medium text-indigo-700 hover:text-indigo-900"><?php echo htmlspecialchars($result['title']); ?></h3>
                                        <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($result['snippet']); ?></p>
                                        <span class="text-xs text-green-700 mt-2 block truncate"><?php echo htmlspecialchars($result['link']); ?></span>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Footer -->
            <div class="mt-8 text-center text-sm text-gray-500">
                <p>Este buscador utiliza la API de Google Custom Search para proporcionar los mejores resultados.</p>
            </div>
        </div>
    </div>
</body>
</html>