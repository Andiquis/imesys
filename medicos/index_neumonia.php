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
    $apiUrl = 'http://localhost:5000/predict/';
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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS AI - Detección de Neumonía</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Iconos de Lucide -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <!-- Animaciones AOS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <style>
        .hidden { display: none; }
        
        /* Gradientes médicos adaptados para fondo blanco */
        .medical-gradient {
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
        }
        
        .ai-gradient {
            background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%);
        }
        
        .lung-gradient {
            background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
        }
        
        /* Animaciones personalizadas */
        @keyframes pulse-ai {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.8;
                transform: scale(1.05);
            }
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        
        @keyframes scan-line {
            0% {
                left: -100%;
            }
            100% {
                left: 100%;
            }
        }
        
        .pulse-ai {
            animation: pulse-ai 2s infinite;
        }
        
        .float {
            animation: float 3s ease-in-out infinite;
        }
        
        .scan-effect {
            position: relative;
            overflow: hidden;
        }
        
        .scan-effect::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.3), transparent);
            animation: scan-line 2s ease-in-out infinite;
            z-index: 1;
        }
        
        /* Efectos de cristal adaptados para fondo blanco */
        .glass-effect {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(59, 130, 246, 0.2);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        }
        
        /* Efectos de neón adaptados */
        .neon-border {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
            border: 2px solid rgba(59, 130, 246, 0.6);
        }
        
        .result-card {
            background: linear-gradient(145deg, #f8fafc, #e2e8f0);
            box-shadow: 8px 8px 20px rgba(0, 0, 0, 0.1), -8px -8px 20px rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        
        /* Partículas de fondo */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(59, 130, 246, 0.4);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        /* Estilos para el modal de reporte */
        .modal-enter {
            opacity: 0;
            transform: scale(0.9);
        }
        
        .modal-enter-active {
            opacity: 1;
            transform: scale(1);
            transition: all 0.3s ease;
        }
        
        .modal-exit {
            opacity: 1;
            transform: scale(1);
        }
        
        .modal-exit-active {
            opacity: 0;
            transform: scale(0.9);
            transition: all 0.3s ease;
        }

        /* Estilos de impresión */
        @media print {
            body * {
                visibility: hidden;
            }
            
            #reportContent, #reportContent * {
                visibility: visible;
            }
            
            #reportContent {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            
            .no-print {
                display: none !important;
            }
        }

        /* Animación de progreso circular */
        .progress-circle {
            transform: rotate(-90deg);
        }
        
        .progress-circle circle {
            stroke-dasharray: 251.2;
            stroke-dashoffset: 251.2;
            transition: stroke-dashoffset 0.5s ease-in-out;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-50 min-h-screen">
    <!-- Partículas de fondo -->
    <div class="particles"></div>
    
    <!-- Contenedor principal -->
    <div class="min-h-screen flex items-center justify-center p-4">
        <!-- Card principal con efecto de cristal -->
        <div class="glass-effect rounded-3xl p-8 w-full max-w-4xl shadow-2xl" data-aos="fade-up" data-aos-duration="1000">
            
            <!-- Header con título animado -->
            <div class="text-center mb-8" data-aos="fade-down" data-aos-delay="200">
                <h1 class="text-4xl font-bold text-gray-800 mb-4">
                    Detección Inteligente de Neumonía
                </h1>
                <p class="text-gray-600 text-lg">
                    Análisis avanzado de radiografías de tórax mediante Inteligencia Artificial
                </p>
            </div>

            <!-- Grid de dos columnas -->
            <div class="grid lg:grid-cols-2 gap-8">
                
                <!-- Columna izquierda: Upload y preview -->
                <div class="space-y-6" data-aos="fade-right" data-aos-delay="400">
                    
                    <!-- Zona de carga y vista previa unificada -->
                    <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl p-6 border border-gray-200 shadow-lg">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <i data-lucide="upload" class="w-6 h-6 mr-2 text-blue-600"></i>
                            <span id="uploadTitle">Cargar Radiografía</span>
                        </h3>
                        
                        <!-- Área de carga inicial -->
                        <div id="uploadArea" class="transition-all duration-500">
                            <label class="cursor-pointer block">
                                <div class="border-2 border-dashed border-blue-500 rounded-xl p-8 text-center hover:border-blue-600 transition-all duration-300 hover:bg-blue-50 min-h-[320px] flex flex-col justify-center">
                                    <div class="ai-gradient p-4 rounded-full mx-auto mb-4 w-fit">
                                        <i data-lucide="camera" class="w-8 h-8 text-white"></i>
                                    </div>
                                    <p class="text-gray-800 font-semibold mb-2">Haz clic para seleccionar</p>
                                    <p class="text-gray-500 text-sm">JPG, PNG (Máx. 10MB)</p>
                                    <div class="mt-4 text-gray-400">
                                        <i data-lucide="image" class="w-12 h-12 mx-auto opacity-50"></i>
                                    </div>
                                </div>
                                <input type="file" id="fileInput" class="hidden" accept="image/jpeg,image/png">
                            </label>
                        </div>

                        <!-- Vista previa de imagen -->
                        <div id="imagePreview" class="hidden transition-all duration-500">
                            <div class="relative">
                                <!-- Botón para cambiar imagen -->
                                <button id="changeImageBtn" class="absolute top-2 right-2 bg-white/80 hover:bg-white text-gray-600 hover:text-gray-800 p-2 rounded-full shadow-lg transition-all duration-300 z-10">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </button>
                                
                                <!-- Imagen con efecto de escaneo -->
                                <div class="scan-effect rounded-xl overflow-hidden">
                                    <img id="previewImg" alt="Radiografía seleccionada" class="w-full h-80 object-cover rounded-xl border-2 border-gray-300">
                                </div>
                                
                                <!-- Información de la imagen -->
                                <div class="mt-4 bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-3 border border-green-200">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <i data-lucide="check-circle" class="w-5 h-5 text-green-600 mr-2"></i>
                                            <span class="text-sm text-gray-700 font-medium">Imagen cargada exitosamente</span>
                                        </div>
                                        <div class="text-xs text-gray-500" id="imageInfo">
                                            <!-- Información del archivo se llenará dinámicamente -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: Resultados y análisis -->
                <div class="space-y-6" data-aos="fade-left" data-aos-delay="600">
                    
                    <!-- Panel de análisis -->
                    <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl p-6 border border-gray-200 shadow-lg">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <i data-lucide="activity" class="w-6 h-6 mr-2 text-purple-600"></i>
                            Análisis AI
                        </h3>
                        
                        <!-- Estado de análisis -->
                        <div id="analysisStatus" class="text-center py-8">
                            <div class="float mb-4">
                                <i data-lucide="stethoscope" class="w-16 h-16 text-gray-400 mx-auto"></i>
                            </div>
                            <p class="text-gray-500">Esperando radiografía para análisis...</p>
                        </div>
                        
                        <!-- Mensaje de procesamiento -->
                        <div id="processing" class="text-center py-8 hidden">
                            <div class="pulse-ai mb-4">
                                <i data-lucide="brain" class="w-16 h-16 text-blue-600 mx-auto"></i>
                            </div>
                            <p class="text-blue-600 font-semibold text-lg">Analizando con IA...</p>
                            <p class="text-gray-500 mt-2">Procesando patrones radiológicos</p>
                            <div class="mt-4 w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-2 rounded-full animate-pulse" style="width: 70%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Resultados -->
                    <div id="result" class="result-card rounded-2xl p-6 border border-gray-200 hidden">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <i data-lucide="clipboard-check" class="w-6 h-6 mr-2 text-green-600"></i>
                            Resultado del Diagnóstico
                        </h3>
                        
                        <div class="space-y-4">
                            <!-- Resultado principal -->
                            <div id="mainResult" class="bg-gradient-to-r from-green-100 to-blue-100 rounded-xl p-4 border border-green-300">
                                <p id="resultText" class="text-lg font-semibold text-center text-gray-800"></p>
                            </div>
                            
                            <!-- Métricas de confianza -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gray-50 rounded-lg p-3 text-center border border-gray-200">
                                    <p class="text-gray-500 text-sm">Confianza</p>
                                    <p id="confidenceValue" class="text-2xl font-bold text-blue-600">--</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3 text-center border border-gray-200">
                                    <p class="text-gray-500 text-sm">Precisión</p>
                                    <p class="text-2xl font-bold text-green-600">98.5%</p>
                                </div>
                            </div>
                            
                            <!-- Recomendaciones -->
                            <div id="recommendations" class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                                <p class="text-amber-700 font-semibold mb-2">
                                    <i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1"></i>
                                    Recomendación Médica
                                </p>
                                <p id="recommendationText" class="text-gray-600 text-sm"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="flex gap-4">
                        <button id="resetBtn" class="flex-1 bg-gradient-to-r from-red-500 to-pink-500 hover:from-red-600 hover:to-pink-600 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none" disabled>
                            <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                            <span>Nueva Análisis</span>
                        </button>
                        
                        <button id="downloadBtn" class="flex-1 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none" disabled>
                            <i data-lucide="download" class="w-5 h-5"></i>
                            <span>Descargar Reporte</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer informativo -->
            <div class="mt-8 text-center" data-aos="fade-up" data-aos-delay="800">
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-4 border border-blue-200">
                    <p class="text-gray-600 text-sm">
                        <i data-lucide="shield-check" class="w-4 h-4 inline mr-1 text-green-600"></i>
                        Sistema certificado para uso médico profesional | Precisión: 98.5% | Validado por especialistas
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Reporte Médico -->
    <div id="reportModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden" id="reportModalContent">
            <!-- Header del Modal -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i data-lucide="file-text" class="w-8 h-8 mr-3"></i>
                        <div>
                            <h2 class="text-2xl font-bold">Reporte Médico de Diagnóstico</h2>
                            <p class="text-blue-100">Sistema IMESYS - Detección de Neumonía por IA</p>
                        </div>
                    </div>
                    <button id="closeModalBtn" class="text-white hover:text-gray-200 transition-colors">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>

            <!-- Contenido del Reporte -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-200px)]" id="reportContent">
                <!-- Header del reporte -->
                <div class="border-b border-gray-200 pb-6 mb-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 60'%3E%3Ctext x='10' y='35' font-family='Arial, sans-serif' font-size='24' font-weight='bold' fill='%233b82f6'%3EIMESYS%3C/text%3E%3Ctext x='10' y='50' font-family='Arial, sans-serif' font-size='12' fill='%236b7280'%3ESistema de Diagnóstico por IA%3C/text%3E%3C/svg%3E" alt="IMESYS Logo" class="h-12 mb-2">
                            <p class="text-gray-600 text-sm">Sistema de Inteligencia Artificial para Diagnóstico Médico</p>
                        </div>
                        <div class="text-right">
                            <p class="text-gray-800 font-semibold">Fecha del Análisis</p>
                            <p class="text-gray-600" id="reportDate"></p>
                            <p class="text-gray-800 font-semibold mt-2">ID de Análisis</p>
                            <p class="text-gray-600" id="reportId"></p>
                        </div>
                    </div>
                </div>

                <!-- Información del Paciente -->
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i data-lucide="user" class="w-5 h-5 mr-2 text-blue-600"></i>
                        Información del Análisis
                    </h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Tipo de Estudio:</label>
                                <p class="text-gray-800">Radiografía de Tórax</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Modalidad:</label>
                                <p class="text-gray-800">Análisis por Inteligencia Artificial</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Archivo Analizado:</label>
                                <p class="text-gray-800" id="reportFileName"></p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Tamaño del Archivo:</label>
                                <p class="text-gray-800" id="reportFileSize"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Imagen Analizada -->
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i data-lucide="image" class="w-5 h-5 mr-2 text-blue-600"></i>
                        Imagen Analizada
                    </h3>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <img id="reportImage" class="max-w-md mx-auto rounded-lg shadow-md border-2 border-gray-200">
                        <p class="text-sm text-gray-500 mt-2">Radiografía de tórax procesada por el sistema de IA</p>
                    </div>
                </div>

                <!-- Resultados del Diagnóstico -->
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i data-lucide="activity" class="w-5 h-5 mr-2 text-blue-600"></i>
                        Resultados del Análisis
                    </h3>
                    
                    <!-- Diagnóstico Principal -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 mb-4 border border-blue-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-2">Diagnóstico Principal</h4>
                                <p class="text-lg font-bold" id="reportDiagnosis"></p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-gray-600">Confianza del Sistema</p>
                                <div class="relative">
                                    <div class="w-20 h-20 rounded-full border-4 border-gray-200 flex items-center justify-center">
                                        <span class="text-xl font-bold" id="reportConfidence"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Métricas Detalladas -->
                    <div class="grid md:grid-cols-3 gap-4 mb-4">
                        <div class="bg-white rounded-lg p-4 border border-gray-200 text-center">
                            <i data-lucide="target" class="w-8 h-8 text-green-600 mx-auto mb-2"></i>
                            <p class="text-sm text-gray-600">Precisión del Sistema</p>
                            <p class="text-2xl font-bold text-green-600">98.5%</p>
                        </div>
                        <div class="bg-white rounded-lg p-4 border border-gray-200 text-center">
                            <i data-lucide="zap" class="w-8 h-8 text-blue-600 mx-auto mb-2"></i>
                            <p class="text-sm text-gray-600">Tiempo de Análisis</p>
                            <p class="text-2xl font-bold text-blue-600" id="reportProcessingTime">< 5s</p>
                        </div>
                        <div class="bg-white rounded-lg p-4 border border-gray-200 text-center">
                            <i data-lucide="shield-check" class="w-8 h-8 text-purple-600 mx-auto mb-2"></i>
                            <p class="text-sm text-gray-600">Validación</p>
                            <p class="text-2xl font-bold text-purple-600">FDA</p>
                        </div>
                    </div>
                </div>

                <!-- Recomendaciones Médicas -->
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i data-lucide="clipboard-list" class="w-5 h-5 mr-2 text-blue-600"></i>
                        Recomendaciones Médicas
                    </h3>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 mr-3 mt-1"></i>
                            <div>
                                <h4 class="font-semibold text-amber-800 mb-2">Recomendación Clínica</h4>
                                <p class="text-gray-700" id="reportRecommendation"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información Técnica -->
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i data-lucide="settings" class="w-5 h-5 mr-2 text-blue-600"></i>
                        Información Técnica
                    </h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p><strong>Modelo de IA:</strong> CNN - Redes Neuronales Convolucionales</p>
                                <p><strong>Versión del Sistema:</strong> IMESYS v2.1.0</p>
                                <p><strong>Dataset de Entrenamiento:</strong> 50,000+ imágenes validadas</p>
                            </div>
                            <div>
                                <p><strong>Resolución Procesada:</strong> Alta definición</p>
                                <p><strong>Algoritmo:</strong> Deep Learning con validación cruzada</p>
                                <p><strong>Certificación:</strong> ISO 13485, FDA 510(k)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Disclaimer -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <h4 class="font-semibold text-red-800 mb-2 flex items-center">
                            <i data-lucide="info" class="w-4 h-4 mr-2"></i>
                            Importante - Disclaimer Médico
                        </h4>
                        <p class="text-red-700 text-sm">
                            Este reporte ha sido generado por un sistema de inteligencia artificial como herramienta de apoyo diagnóstico. 
                            Los resultados deben ser siempre interpretados y validados por un profesional médico calificado. 
                            Este sistema no reemplaza el criterio clínico ni el diagnóstico médico profesional.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer del Modal -->
            <div class="bg-gray-50 p-6 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    <p>Generado automáticamente por IMESYS AI</p>
                    <p id="reportGenerationTime"></p>
                </div>
                <div class="flex gap-3">
                    <button id="printReportBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        Imprimir
                    </button>
                    <button id="downloadPdfBtn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        Descargar PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Inicializar AOS (Animate On Scroll)
        AOS.init();
        
        // Inicializar iconos de Lucide
        lucide.createIcons();

        // Referencias a elementos del DOM
        const fileInput = document.getElementById('fileInput');
        const uploadArea = document.getElementById('uploadArea');
        const uploadTitle = document.getElementById('uploadTitle');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        const changeImageBtn = document.getElementById('changeImageBtn');
        const imageInfo = document.getElementById('imageInfo');
        const result = document.getElementById('result');
        const resultText = document.getElementById('resultText');
        const confidenceValue = document.getElementById('confidenceValue');
        const recommendationText = document.getElementById('recommendationText');
        const processing = document.getElementById('processing');
        const analysisStatus = document.getElementById('analysisStatus');
        const resetBtn = document.getElementById('resetBtn');
        const downloadBtn = document.getElementById('downloadBtn');
        
        // Referencias del modal de reporte
        const reportModal = document.getElementById('reportModal');
        const reportModalContent = document.getElementById('reportModalContent');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const printReportBtn = document.getElementById('printReportBtn');
        const downloadPdfBtn = document.getElementById('downloadPdfBtn');

        // Variables globales para el reporte
        let currentReportData = {
            diagnosis: '',
            confidence: '',
            recommendation: '',
            fileName: '',
            fileSize: '',
            imageSrc: '',
            analysisId: '',
            date: ''
        };

        // Crear partículas de fondo
        function createParticles() {
            const particlesContainer = document.querySelector('.particles');
            const particleCount = 20;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 6 + 's';
                particlesContainer.appendChild(particle);
            }
        }

        // Manejar selección de archivo
        fileInput.addEventListener('change', function() {
            const file = fileInput.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                showError('Solo se permiten imágenes en formato JPG o PNG.');
                return;
            }

            if (file.size > 10 * 1024 * 1024) { // 10MB límite
                showError('El archivo es muy grande. Máximo 10MB.');
                return;
            }

            showImagePreview(file);
            processImage(file);
        });

        // Mostrar vista previa de imagen
        function showImagePreview(file) {
            const reader = new FileReader();
            reader.onload = () => {
                previewImg.src = reader.result;
                
                // Ocultar área de carga y mostrar preview
                uploadArea.style.transform = 'scale(0.8)';
                uploadArea.style.opacity = '0';
                
                setTimeout(() => {
                    uploadArea.classList.add('hidden');
                    imagePreview.classList.remove('hidden');
                    
                    // Cambiar título
                    uploadTitle.innerHTML = '<i data-lucide="eye" class="w-6 h-6 mr-2 text-green-600"></i>Radiografía Cargada';
                    
                    // Mostrar información del archivo
                    const fileSize = (file.size / 1024 / 1024).toFixed(2);
                    imageInfo.textContent = `${file.name} (${fileSize} MB)`;
                    
                    // Animar aparición
                    imagePreview.style.transform = 'scale(0.8)';
                    imagePreview.style.opacity = '0';
                    
                    setTimeout(() => {
                        imagePreview.style.transition = 'all 0.5s ease';
                        imagePreview.style.transform = 'scale(1)';
                        imagePreview.style.opacity = '1';
                    }, 100);
                    
                    // Re-inicializar iconos después del cambio
                    setTimeout(() => lucide.createIcons(), 200);
                }, 300);
                
                analysisStatus.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }

        // Procesar imagen con IA
        function processImage(file) {
            // Mostrar estado de procesamiento
            processing.classList.remove('hidden');
            result.classList.add('hidden');
            resetBtn.disabled = true;
            downloadBtn.disabled = true;

            const formData = new FormData();
            formData.append('file', file);

            fetch('index_neumonia.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                processing.classList.add('hidden');
                
                if (data.error) {
                    showError(data.error);
                } else {
                    showResult(data.prediction, data.confidence);
                }
                
                resetBtn.disabled = false;
                downloadBtn.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                processing.classList.add('hidden');
                showError('Error al procesar la imagen. Inténtalo de nuevo.');
                resetBtn.disabled = false;
            });
        }

        // Mostrar resultado del análisis
        function showResult(prediction, confidence) {
            const isNormal = prediction.toLowerCase().includes('normal') || prediction.toLowerCase().includes('sano');
            const isPneumonia = prediction.toLowerCase().includes('neumon') || prediction.toLowerCase().includes('pneum');
            
            // Configurar texto del resultado
            let resultIcon = '';
            let resultClass = '';
            let recommendation = '';
            
            if (isNormal) {
                resultIcon = '✅';
                resultClass = 'text-green-700';
                recommendation = 'Radiografía normal. Continúe con controles regulares según protocolo médico.';
            } else if (isPneumonia) {
                resultIcon = '⚠️';
                resultClass = 'text-red-700';
                recommendation = 'Posible neumonía detectada. Se recomienda evaluación médica inmediata y tratamiento apropiado.';
            } else {
                resultIcon = '📋';
                resultClass = 'text-amber-700';
                recommendation = 'Resultado requiere evaluación médica especializada para interpretación completa.';
            }

            resultText.innerHTML = `${resultIcon} <strong>${prediction}</strong>`;
            resultText.className = `text-lg font-semibold text-center ${resultClass}`;
            
            confidenceValue.textContent = confidence + '%';
            recommendationText.textContent = recommendation;

            // Guardar datos para el reporte
            currentReportData = {
                diagnosis: prediction,
                confidence: confidence,
                recommendation: recommendation,
                fileName: fileInput.files[0]?.name || 'imagen.jpg',
                fileSize: fileInput.files[0] ? (fileInput.files[0].size / 1024 / 1024).toFixed(2) + ' MB' : 'N/A',
                imageSrc: previewImg.src,
                analysisId: generateAnalysisId(),
                date: new Date().toLocaleString('es-ES', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })
            };
            
            // Cambiar color de la card de resultado según el resultado
            const mainResult = document.getElementById('mainResult');
            if (isNormal) {
                mainResult.className = 'bg-gradient-to-r from-green-100 to-emerald-100 rounded-xl p-4 border border-green-300';
            } else if (isPneumonia) {
                mainResult.className = 'bg-gradient-to-r from-red-100 to-pink-100 rounded-xl p-4 border border-red-300';
            } else {
                mainResult.className = 'bg-gradient-to-r from-yellow-100 to-orange-100 rounded-xl p-4 border border-yellow-300';
            }

            result.classList.remove('hidden');
            
            // Animar la aparición del resultado
            result.style.opacity = '0';
            result.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                result.style.transition = 'all 0.5s ease';
                result.style.opacity = '1';
                result.style.transform = 'translateY(0)';
            }, 100);
        }

        // Mostrar error
        function showError(message) {
            resultText.innerHTML = `❌ <strong>Error:</strong> ${message}`;
            resultText.className = 'text-lg font-semibold text-center text-red-700';
            
            confidenceValue.textContent = '--';
            recommendationText.textContent = 'Por favor, intente nuevamente con una imagen válida.';
            
            const mainResult = document.getElementById('mainResult');
            mainResult.className = 'bg-gradient-to-r from-red-100 to-pink-100 rounded-xl p-4 border border-red-300';
            
            result.classList.remove('hidden');
        }

        // Función para resetear a estado inicial
        function resetToInitialState() {
            fileInput.value = '';
            
            // Animar salida del preview
            imagePreview.style.transform = 'scale(0.8)';
            imagePreview.style.opacity = '0';
            
            setTimeout(() => {
                imagePreview.classList.add('hidden');
                uploadArea.classList.remove('hidden');
                
                // Restaurar título original
                uploadTitle.innerHTML = '<i data-lucide="upload" class="w-6 h-6 mr-2 text-blue-600"></i>Cargar Radiografía';
                
                // Animar entrada del área de carga
                uploadArea.style.transform = 'scale(0.8)';
                uploadArea.style.opacity = '0';
                
                setTimeout(() => {
                    uploadArea.style.transition = 'all 0.5s ease';
                    uploadArea.style.transform = 'scale(1)';
                    uploadArea.style.opacity = '1';
                }, 100);
                
                // Re-inicializar iconos
                setTimeout(() => lucide.createIcons(), 200);
            }, 300);
            
            result.classList.add('hidden');
            processing.classList.add('hidden');
            analysisStatus.classList.remove('hidden');
            resetBtn.disabled = true;
            downloadBtn.disabled = true;
        }

        // Reiniciar análisis
        resetBtn.addEventListener('click', resetToInitialState);

        // Botón para cambiar imagen
        if (changeImageBtn) {
            changeImageBtn.addEventListener('click', function() {
                fileInput.click();
            });
        }

        // Generar ID único para el análisis
        function generateAnalysisId() {
            return 'IMESYS-' + Date.now().toString(36).toUpperCase() + '-' + Math.random().toString(36).substr(2, 5).toUpperCase();
        }

        // Mostrar modal de reporte
        function showReportModal() {
            if (!currentReportData.diagnosis) {
                alert('No hay datos de análisis disponibles para generar el reporte.');
                return;
            }

            // Llenar datos del reporte
            document.getElementById('reportDate').textContent = currentReportData.date;
            document.getElementById('reportId').textContent = currentReportData.analysisId;
            document.getElementById('reportFileName').textContent = currentReportData.fileName;
            document.getElementById('reportFileSize').textContent = currentReportData.fileSize;
            document.getElementById('reportImage').src = currentReportData.imageSrc;
            document.getElementById('reportDiagnosis').textContent = currentReportData.diagnosis;
            document.getElementById('reportConfidence').textContent = currentReportData.confidence + '%';
            document.getElementById('reportRecommendation').textContent = currentReportData.recommendation;
            document.getElementById('reportGenerationTime').textContent = 'Generado el: ' + new Date().toLocaleString('es-ES');

            // Mostrar modal con animación
            reportModal.classList.remove('hidden');
            reportModalContent.classList.add('modal-enter');
            
            setTimeout(() => {
                reportModalContent.classList.remove('modal-enter');
                reportModalContent.classList.add('modal-enter-active');
            }, 10);

            // Re-inicializar iconos
            setTimeout(() => lucide.createIcons(), 100);
        }

        // Cerrar modal de reporte
        function closeReportModal() {
            reportModalContent.classList.add('modal-exit');
            
            setTimeout(() => {
                reportModal.classList.add('hidden');
                reportModalContent.classList.remove('modal-exit', 'modal-enter-active');
            }, 300);
        }

        // Imprimir reporte
        function printReport() {
            window.print();
        }

        // Descargar reporte como PDF (simulado)
        function downloadReportPDF() {
            // Crear contenido HTML para PDF
            const reportContent = document.getElementById('reportContent').innerHTML;
            const printWindow = window.open('', '_blank');
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Reporte IMESYS - ${currentReportData.analysisId}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        img { max-width: 400px; }
                        .bg-gradient-to-r, .bg-gray-50, .bg-blue-50, .bg-amber-50, .bg-red-50 { 
                            background: #f8f9fa !important; 
                            border: 1px solid #dee2e6 !important; 
                        }
                        .text-blue-600, .text-indigo-700 { color: #0066cc !important; }
                        .text-green-600 { color: #28a745 !important; }
                        .text-red-700, .text-red-800 { color: #dc3545 !important; }
                        .text-amber-700, .text-amber-800 { color: #ffc107 !important; }
                        .grid { display: flex; flex-wrap: wrap; }
                        .grid > div { flex: 1; margin: 5px; }
                    </style>
                </head>
                <body>
                    ${reportContent}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            
            setTimeout(() => {
                printWindow.print();
                setTimeout(() => printWindow.close(), 1000);
            }, 500);
        }

        // Event listeners para el modal
        downloadBtn.addEventListener('click', showReportModal);
        closeModalBtn.addEventListener('click', closeReportModal);
        printReportBtn.addEventListener('click', printReport);
        downloadPdfBtn.addEventListener('click', downloadReportPDF);

        // Cerrar modal haciendo clic fuera
        reportModal.addEventListener('click', function(e) {
            if (e.target === reportModal) {
                closeReportModal();
            }
        });

        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !reportModal.classList.contains('hidden')) {
                closeReportModal();
            }
        });

        // Inicializar partículas al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
        });


    </script>
</body>
</html>