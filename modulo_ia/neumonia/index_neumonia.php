<?php
// Handle file upload and API request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    header('Content-Type: application/json');

    // Validate file
    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'No file uploaded or upload error.']);
        exit;
    }

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png'];
    $fileType = mime_content_type($_FILES['file']['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Only JPG or PNG images are allowed.']);
        exit;
    }

    // Prepare file for cURL
    $apiUrl = 'http://127.0.0.1:8000/predict/';
    $tmpFilePath = $_FILES['file']['tmp_name'];
    $filename = $_FILES['file']['name'];
    $curlFile = new CURLFile($tmpFilePath, $fileType, $filename);

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => $curlFile]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // Execute cURL request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Handle errors
    if ($curlError || $httpCode !== 200) {
        http_response_code(500);
        echo json_encode(['error' => 'Error processing the image. Please try again.']);
        exit;
    }

    // Parse API response
    $apiResponse = json_decode($response, true);
    if (!$apiResponse || !isset($apiResponse['prediction'], $apiResponse['confidence'])) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid API response.']);
        exit;
    }

    // Return success response
    echo json_encode([
        'prediction' => $apiResponse['prediction'],
        'confidence' => $apiResponse['confidence']
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detección de Neumonía</title>
    <!-- Tailwind CSS CDN (replace with local build in production) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .hidden { display: none; }
        body { background-color: #f3f4f6; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex items-center justify-center min-h-screen text-gray-900">
        <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md text-center border border-gray-300 flex flex-col items-center">
            <h2 class="text-3xl font-bold mb-4 text-blue-500">Detección de Neumonía</h2>

            <!-- File upload button -->
            <label class="cursor-pointer bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition-all flex items-center gap-2">
                📁 <span>Subir Imagen</span>
                <input type="file" id="fileInput" class="hidden" accept="image/jpeg,image/png">
            </label>

            <!-- Image preview -->
            <div id="imagePreview" class="mt-4 flex justify-center hidden">
                <img id="previewImg" alt="Imagen seleccionada" class="w-64 h-64 object-cover rounded-lg shadow-md border border-gray-300">
            </div>

            <!-- Result display -->
            <div id="result" class="mt-4 p-4 bg-gray-900 text-white rounded-lg shadow-lg text-center w-80 flex flex-col items-center hidden">
                <p id="resultText" class="text-lg font-semibold text-green-400 flex items-center gap-2"></p>
            </div>

            <!-- Processing message -->
            <p id="processing" class="mt-2 text-yellow-500 hidden">Procesando imagen... ⏳</p>

            <!-- Reset button -->
            <button id="resetBtn" class="mt-4 bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition-all flex items-center gap-2" disabled>
                🔄 <span>Reiniciar</span>
            </button>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('fileInput');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        const result = document.getElementById('result');
        const resultText = document.getElementById('resultText');
        const processing = document.getElementById('processing');
        const resetBtn = document.getElementById('resetBtn');

        fileInput.addEventListener('change', function() {
            const file = fileInput.files[0];
            if (!file || !file.type.startsWith('image/')) {
                showResult('⚠️ Solo se permiten imágenes en formato JPG o PNG.', false);
                return;
            }

            // Show image preview
            const reader = new FileReader();
            reader.onload = () => {
                previewImg.src = reader.result;
                imagePreview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);

            // Process image
            processImage(file);
        });

        function processImage(file) {
            processing.classList.remove('hidden');
            result.classList.add('hidden');
            resetBtn.disabled = true;

            const formData = new FormData();
            formData.append('file', file);

            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showResult(`⚠️ ${data.error}`, false);
                } else {
                    showResult(`🩺 Resultado: ${data.prediction} - 📊 Confianza: ${data.confidence}%`, true);
                }
                processing.classList.add('hidden');
                resetBtn.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                showResult('⚠️ Error al procesar la imagen. Inténtalo de nuevo.', false);
                processing.classList.add('hidden');
                resetBtn.disabled = false;
            });
        }

        function showResult(message, isSuccess) {
            resultText.textContent = message;
            result.classList.remove('hidden');
            resultText.classList.toggle('text-green-400', isSuccess);
            resultText.classList.toggle('text-red-400', !isSuccess);
        }

        resetBtn.addEventListener('click', function() {
            fileInput.value = '';
            imagePreview.classList.add('hidden');
            result.classList.add('hidden');
            processing.classList.add('hidden');
            resetBtn.disabled = true;
        });
    </script>
</body>
</html>