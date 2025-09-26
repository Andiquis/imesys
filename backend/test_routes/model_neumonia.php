<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Predicción de Neumonía | Sistema de Diagnóstico por IA</title>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --text-color: #333;
            --bg-color: #f5f7fa;
            --card-bg: #ffffff;
            --border-radius: 10px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            font-size: 1.1rem;
        }

        .card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 20px;
        }

        .upload-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            padding: 30px;
            border: 2px dashed #ccc;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .upload-section.dragover {
            border-color: var(--primary-color);
            background-color: rgba(52, 152, 219, 0.1);
        }

        .upload-section.error {
            border-color: var(--danger-color);
            background-color: rgba(231, 76, 60, 0.1);
        }

        .upload-icon {
            font-size: 48px;
            color: #999;
        }

        .upload-text {
            font-size: 1.2rem;
            color: #666;
            text-align: center;
        }

        .browse-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .browse-btn:hover {
            background-color: #2980b9;
        }

        .file-input {
            display: none;
        }

        .preview-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }

        .image-preview {
            max-width: 100%;
            max-height: 350px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: none;
        }

        .predict-btn {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 12px 36px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            margin-top: 20px;
            transition: background-color 0.3s ease;
            display: none;
        }

        .predict-btn:hover {
            background-color: #27ae60;
        }

        .predict-btn:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
        }

        .result-section {
            display: none;
            margin-top: 30px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .result-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .result-title {
            font-size: 1.5rem;
            color: var(--text-color);
        }

        .result-card {
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
            margin-bottom: 20px;
            border-left: 5px solid var(--primary-color);
            background-color: var(--card-bg);
        }

        .prediction {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .confidence {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        .pneumonia-positive {
            color: var(--danger-color);
        }

        .pneumonia-negative {
            color: var(--secondary-color);
        }

        .meter-container {
            width: 100%;
            margin-bottom: 15px;
        }

        .meter-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .meter {
            height: 30px;
            position: relative;
            background: #e0e0e0;
            border-radius: 50px;
            padding: 5px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .meter span {
            display: block;
            height: 100%;
            border-radius: 50px;
            background-color: var(--primary-color);
            position: relative;
            overflow: hidden;
            transition: width 1.5s ease;
        }

        .pneumonia-meter span {
            background-color: var(--danger-color);
        }

        .normal-meter span {
            background-color: var(--secondary-color);
        }

        .confidence-value {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-weight: bold;
            z-index: 1;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s ease-in-out infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .error-message {
            color: var(--danger-color);
            text-align: center;
            padding: 10px;
            margin-top: 10px;
            border-radius: var(--border-radius);
            background-color: rgba(231, 76, 60, 0.1);
            display: none;
        }

        .btn-reset {
            background-color: #7f8c8d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
            display: none;
        }

        .btn-reset:hover {
            background-color: #6c7a89;
        }

        footer {
            text-align: center;
            margin-top: 50px;
            padding: 20px;
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .recommendations {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            border-left: 5px solid #3498db;
        }

        .recommendations h3 {
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .recommendations ul {
            padding-left: 20px;
        }

        .recommendations li {
            margin-bottom: 5px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .card {
                padding: 15px;
            }
            
            .upload-section {
                padding: 20px;
            }
            
            .prediction {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Sistema de Diagnóstico por IA</h1>
            <p class="subtitle">Detección Automática de Neumonía en Radiografías de Tórax</p>
        </header>

        <div class="card">
            <div id="upload-section" class="upload-section">
                <div class="upload-icon">📤</div>
                <p class="upload-text">Arrastre y suelte una imagen de rayos X de tórax<br>o</p>
                <button id="browse-btn" class="browse-btn">Seleccionar Imagen</button>
                <input type="file" id="file-input" class="file-input" accept="image/jpeg,image/png,image/jpg">
            </div>

            <div class="preview-section">
                <img id="image-preview" class="image-preview" alt="Vista previa de la imagen">
                <button id="predict-btn" class="predict-btn">Analizar Imagen</button>
            </div>

            <div id="error-message" class="error-message"></div>

            <div id="loading" class="loading">
                <div class="spinner"></div>
                <p>Analizando imagen, por favor espere...</p>
            </div>

            <div id="result-section" class="result-section">
                <div class="result-header">
                    <h2 class="result-title">Resultados del Análisis</h2>
                    <button id="reset-btn" class="btn-reset">Analizar otra imagen</button>
                </div>

                <div class="result-card">
                    <div id="prediction" class="prediction"></div>
                    <div id="confidence" class="confidence"></div>

                    <div class="meter-container">
                        <div class="meter-label">
                            <span>Neumonía</span>
                            <span id="pneumonia-value">0%</span>
                        </div>
                        <div class="meter pneumonia-meter">
                            <span id="pneumonia-meter" style="width: 0%"></span>
                        </div>
                    </div>

                    <div class="meter-container">
                        <div class="meter-label">
                            <span>Sin Neumonía</span>
                            <span id="normal-value">0%</span>
                        </div>
                        <div class="meter normal-meter">
                            <span id="normal-meter" style="width: 0%"></span>
                        </div>
                    </div>

                    <div id="recommendations" class="recommendations" style="display: none;">
                        <h3>Recomendaciones:</h3>
                        <ul id="recommendations-list"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>Este sistema es solo una herramienta de apoyo. Las predicciones deben ser validadas por un profesional médico.</p>
    </footer>

    <script>
        // Referencias a elementos del DOM
        const uploadSection = document.getElementById('upload-section');
        const browseBtn = document.getElementById('browse-btn');
        const fileInput = document.getElementById('file-input');
        const imagePreview = document.getElementById('image-preview');
        const predictBtn = document.getElementById('predict-btn');
        const loading = document.getElementById('loading');
        const resultSection = document.getElementById('result-section');
        const predictionEl = document.getElementById('prediction');
        const confidenceEl = document.getElementById('confidence');
        const pneumoniaValue = document.getElementById('pneumonia-value');
        const pneumoniaMeter = document.getElementById('pneumonia-meter');
        const normalValue = document.getElementById('normal-value');
        const normalMeter = document.getElementById('normal-meter');
        const resetBtn = document.getElementById('reset-btn');
        const errorMessage = document.getElementById('error-message');
        const recommendationsSection = document.getElementById('recommendations');
        const recommendationsList = document.getElementById('recommendations-list');

        // URL de la API
        const API_URL = 'http://localhost:5000/api/predict';

        // Variables globales
        let selectedFile = null;
        
        // Configuración de eventos para arrastrar y soltar
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadSection.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadSection.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadSection.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            uploadSection.classList.add('dragover');
        }

        function unhighlight() {
            uploadSection.classList.remove('dragover');
            uploadSection.classList.remove('error');
        }

        // Manejar la caída de archivos
        uploadSection.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                validateAndPreviewFile(files[0]);
            }
        }

        // Evento para abrir el selector de archivos
        browseBtn.addEventListener('click', () => {
            fileInput.click();
        });

        // Cuando se selecciona un archivo
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                validateAndPreviewFile(e.target.files[0]);
            }
        });

        // Validar y mostrar vista previa del archivo
        function validateAndPreviewFile(file) {
            // Validar tipo de archivo
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!validTypes.includes(file.type)) {
                showError('Por favor, seleccione una imagen válida (JPEG o PNG)');
                uploadSection.classList.add('error');
                return;
            }

            // Validar tamaño del archivo (máximo 5MB)
            if (file.size > 5 * 1024 * 1024) {
                showError('La imagen es demasiado grande. Tamaño máximo: 5MB');
                uploadSection.classList.add('error');
                return;
            }

            // Guardar el archivo seleccionado
            selectedFile = file;
            
            // Mostrar vista previa
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
                predictBtn.style.display = 'block';
                hideError();
            };
            reader.readAsDataURL(file);
        }

        // Evento de clic en el botón de predicción
        predictBtn.addEventListener('click', () => {
            if (!selectedFile) {
                showError('Por favor, seleccione una imagen primero');
                return;
            }

            // Mostrar cargando y ocultar otros elementos
            loading.style.display = 'block';
            predictBtn.style.display = 'none';
            resultSection.style.display = 'none';
            hideError();

            // Preparar los datos para enviar
            const formData = new FormData();
            formData.append('file', selectedFile);

            // Enviar solicitud a la API
            fetch(API_URL, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.error || 'Error en la solicitud');
                    });
                }
                return response.json();
            })
            .then(data => {
                displayResults(data);
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Error al procesar la imagen: ' + error.message);
            })
            .finally(() => {
                loading.style.display = 'none';
            });
        });

        // Mostrar resultados de la predicción
        function displayResults(data) {
            // Mostrar sección de resultados
            resultSection.style.display = 'block';
            resetBtn.style.display = 'block';

            // Formatear la predicción principal
            const isPneumonia = data.prediction === "Neumonía";
            const predictionClass = isPneumonia ? 'pneumonia-positive' : 'pneumonia-negative';
            
            predictionEl.textContent = data.prediction;
            predictionEl.className = `prediction ${predictionClass}`;
            
            // Mostrar confianza
            confidenceEl.textContent = `Confianza: ${data.confidence}%`;

            // Actualizar medidores de confianza
            const pneumoniaPercentage = data.confidence_scores["Neumonía"];
            const normalPercentage = data.confidence_scores["No Neumonía"];
            
            pneumoniaValue.textContent = `${pneumoniaPercentage}%`;
            pneumoniaMeter.style.width = `${pneumoniaPercentage}%`;
            
            normalValue.textContent = `${normalPercentage}%`;
            normalMeter.style.width = `${normalPercentage}%`;

            // Mostrar recomendaciones basadas en el resultado
            showRecommendations(isPneumonia);
        }

        // Mostrar recomendaciones
        function showRecommendations(isPneumonia) {
            recommendationsSection.style.display = 'block';
            recommendationsList.innerHTML = '';

            if (isPneumonia) {
                const recommendations = [
                    "Consulte a un médico lo antes posible para confirmar el diagnóstico.",
                    "Manténgase hidratado y descanse adecuadamente.",
                    "Monitoree su temperatura corporal regularmente.",
                    "Siga las indicaciones médicas para el tratamiento de antibióticos si se prescribe.",
                    "Evite el contacto cercano con otras personas para prevenir la propagación."
                ];
                
                recommendations.forEach(rec => {
                    const li = document.createElement('li');
                    li.textContent = rec;
                    recommendationsList.appendChild(li);
                });
            } else {
                const recommendations = [
                    "Mantenga hábitos saludables como una buena alimentación e hidratación.",
                    "Realice chequeos médicos regulares para monitorear su salud.",
                    "Si experimenta síntomas respiratorios, consulte a un médico.",
                    "Practique buena higiene respiratoria y lávese las manos frecuentemente.",
                    "Evite el contacto con personas enfermas para prevenir infecciones."
                ];
                
                recommendations.forEach(rec => {
                    const li = document.createElement('li');
                    li.textContent = rec;
                    recommendationsList.appendChild(li);
                });
            }
        }

        // Resetear para analizar otra imagen
        resetBtn.addEventListener('click', () => {
            // Limpiar vista previa
            imagePreview.src = '';
            imagePreview.style.display = 'none';
            
            // Resetear selección de archivo
            selectedFile = null;
            fileInput.value = '';
            
            // Ocultar secciones
            resultSection.style.display = 'none';
            resetBtn.style.display = 'none';
            
            // Mostrar sección de carga
            predictBtn.style.display = 'none';
            
            // Limpiar errores
            hideError();
        });

        // Mostrar mensaje de error
        function showError(message) {
            errorMessage.textContent = message;
            errorMessage.style.display = 'block';
            loading.style.display = 'none';
        }

        // Ocultar mensaje de error
        function hideError() {
            errorMessage.textContent = '';
            errorMessage.style.display = 'none';
        }

        // Verificar el estado del modelo al cargar la página
        window.addEventListener('DOMContentLoaded', checkModelStatus);

        function checkModelStatus() {
            fetch('http://localhost:5000/api/predict/status')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error al verificar el estado del modelo');
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.modelLoaded) {
                        showError('El modelo de predicción no está cargado. Por favor, inténtelo más tarde.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('No se pudo conectar con el servidor. Por favor, verifique la conexión.');
                });
        }
    </script>
</body>
</html>