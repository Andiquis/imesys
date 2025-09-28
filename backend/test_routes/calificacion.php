<?php
// Función para hacer solicitudes GET a la API
function getApiData($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        curl_close($ch);
        return ['error' => 'Error en la solicitud a la API: ' . curl_error($ch)];
    }
    
    curl_close($ch);
    return json_decode($response, true);
}

// URLs de los endpoints de la API (ajusta el puerto si es diferente)
$apiBaseUrl = 'http://localhost:5000/api/rating';
$especialistaDelMesUrl = "$apiBaseUrl/reports/especialista-del-mes";
$top3EspecialistasUrl = "$apiBaseUrl/reports/top-3-doctors";

// Obtener datos de la API
$especialistaDelMes = getApiData($especialistaDelMesUrl);
$top3Especialistas = getApiData($top3EspecialistasUrl);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Especialistas Destacados - IMESYS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-8 text-center">Especialistas Destacados</h1>

        <!-- Especialista del Mes -->
        <div class="bg-white shadow-lg rounded-xl p-6 mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Especialista del Mes</h2>
            <?php if (isset($especialistaDelMes['error'])): ?>
                <p class="text-red-600 text-center"><?php echo htmlspecialchars($especialistaDelMes['error']); ?></p>
            <?php elseif (!empty($especialistaDelMes)): ?>
                <div class="bg-indigo-50 rounded-lg p-4 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <?php echo htmlspecialchars($especialistaDelMes['nombre'] . ' ' . $especialistaDelMes['apellido']); ?>
                    </h3>
                    <p class="text-gray-600"><?php echo htmlspecialchars($especialistaDelMes['nombre_especialidad']); ?></p>
                    <p class="text-indigo-600 font-bold">
                        Puntuación: <?php echo number_format($especialistaDelMes['promedio'], 2); ?> / 5
                    </p>
                    <p class="text-gray-500 text-sm">
                        Basado en <?php echo $especialistaDelMes['total_calificaciones']; ?> calificaciones
                    </p>
                </div>
            <?php else: ?>
                <p class="text-gray-600 text-center">No hay especialistas calificados este mes.</p>
            <?php endif; ?>
        </div>

        <!-- Top 3 Especialistas -->
        <div class="bg-white shadow-lg rounded-xl p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Top 3 Especialistas</h2>
            <div class="grid sm:grid-cols-3 gap-6">
                <?php if (isset($top3Especialistas['error'])): ?>
                    <p class="text-red-600 text-center col-span-3"><?php echo htmlspecialchars($top3Especialistas['error']); ?></p>
                <?php elseif (!empty($top3Especialistas)): ?>
                    <?php foreach ($top3Especialistas as $index => $especialista): ?>
                        <div class="bg-indigo-50 rounded-lg p-4 text-center">
                            <div class="text-yellow-500 text-2xl font-bold mb-2">#<?php echo $index + 1; ?></div>
                            <h3 class="text-lg font-semibold text-gray-800">
                                <?php echo htmlspecialchars($especialista['nombre'] . ' ' . $especialista['apellido']); ?>
                            </h3>
                            <p class="text-gray-600"><?php echo htmlspecialchars($especialista['nombre_especialidad']); ?></p>
                            <p class="text-indigo-600 font-bold">
                                Puntuación: <?php echo number_format($especialista['promedio'], 2); ?> / 5
                            </p>
                            <p class="text-gray-500 text-sm">
                                Basado en <?php echo $especialista['total_calificaciones'] ?? 'N/A'; ?> calificaciones
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-600 text-center col-span-3">No hay especialistas calificados disponibles.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>