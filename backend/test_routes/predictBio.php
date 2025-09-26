<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis Biométrico con IA</title>

</head>
<body>
    <div class="container">
        <h1>Análisis Biométrico con IA</h1>
        
        <div class="nav-tabs">
            <div class="nav-tab active" data-tab="form-tab">Formulario</div>
            <div class="nav-tab" data-tab="json-tab">JSON</div>
        </div>
        
        <!-- Formulario de entrada -->
        <div id="form-tab" class="tab-content active">
            <form id="biometric-form">
                <div class="form-group">
                    <label for="peso">Peso (kg):</label>
                    <input type="number" id="peso" placeholder="Ej: 70" step="0.1" required>
                </div>
                
                <div class="form-group">
                    <label for="altura">Altura (cm):</label>
                    <input type="number" id="altura" placeholder="Ej: 170" required>
                </div>
                
                <div class="form-group">
                    <label for="presion_arterial">Presión Arterial (mmHg):</label>
                    <input type="text" id="presion_arterial" placeholder="Ej: 120/80" required>
                </div>
                
                <div class="form-group">
                    <label for="frecuencia_cardiaca">Frecuencia Cardíaca (bpm):</label>
                    <input type="number" id="frecuencia_cardiaca" placeholder="Ej: 72" required>
                </div>
                
                <div class="form-group">
                    <label for="nivel_glucosa">Nivel de Glucosa (mg/dL):</label>
                    <input type="number" id="nivel_glucosa" placeholder="Ej: 95" required>
                </div>
                
                <button type="submit" class="btn" id="analyze-btn">Analizar Datos</button>
            </form>
            
            <div class="examples">
                <h3>Ejemplos:</h3>
                <p>Prueba con estos ejemplos predefinidos:</p>
                <button class="example-btn" data-example="healthy">Persona Saludable</button>
                <button class="example-btn" data-example="hypertension">Hipertensión</button>
                <button class="example-btn" data-example="diabetic">Diabético</button>
            </div>
        </div>
        
        <!-- Formato JSON -->
        <div id="json-tab" class="tab-content">
            <div class="form-group">
                <label for="json-input">Datos en formato JSON:</label>
                <textarea id="json-input" rows="10" style="width: 100%; padding: 10px; font-family: monospace;" placeholder='{"peso": 70, "altura": 170, "presion_arterial": "120/80", "frecuencia_cardiaca": 72, "nivel_glucosa": 95}'></textarea>
            </div>
            <button class="btn" id="analyze-json-btn">Analizar JSON</button>
        </div>
        
        <!-- Contenedor de resultados -->
        <div class="result-container" style="display: none;">
            <h2>Resultado del Análisis:</h2>
            <div class="result" id="result-text"></div>
        </div>
        
        <!-- Indicador de carga -->
        <div class="loading" id="loading-indicator" style="display: none;">
            Analizando datos biométricos...
        </div>
        
        <!-- Mensaje de error -->
        <div class="error" id="error-message" style="display: none;"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Elementos DOM
            const biometricForm = document.getElementById('biometric-form');
            const analyzeBtn = document.getElementById('analyze-btn');
            const analyzeJsonBtn = document.getElementById('analyze-json-btn');
            const jsonInput = document.getElementById('json-input');
            const resultContainer = document.querySelector('.result-container');
            const resultText = document.getElementById('result-text');
            const loadingIndicator = document.getElementById('loading-indicator');
            const errorMessage = document.getElementById('error-message');
            const exampleBtns = document.querySelectorAll('.example-btn');
            const navTabs = document.querySelectorAll('.nav-tab');
            const tabContents = document.querySelectorAll('.tab-content');
            
            // URL de la API
            const API_URL = 'http://localhost:5000/api/prediccion_bio';
            
            // Ejemplos predefinidos
            const examples = {
                healthy: {
                    peso: 70,
                    altura: 170,
                    presion_arterial: "120/80",
                    frecuencia_cardiaca: 65,
                    nivel_glucosa: 85
                },
                hypertension: {
                    peso: 90,
                    altura: 175,
                    presion_arterial: "150/95",
                    frecuencia_cardiaca: 88,
                    nivel_glucosa: 110
                },
                diabetic: {
                    peso: 85,
                    altura: 168,
                    presion_arterial: "135/85",
                    frecuencia_cardiaca: 75,
                    nivel_glucosa: 180
                }
            };
            
            // Función para cambiar entre pestañas
            navTabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const tabId = tab.getAttribute('data-tab');
                    
                    // Cambiar la pestaña activa
                    navTabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    
                    // Mostrar el contenido correspondiente
                    tabContents.forEach(content => {
                        content.classList.remove('active');
                        if (content.id === tabId) {
                            content.classList.add('active');
                        }
                    });
                });
            });
            
            // Función para llenar el formulario con ejemplos
            exampleBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const exampleType = btn.getAttribute('data-example');
                    const data = examples[exampleType];
                    
                    if (data) {
                        document.getElementById('peso').value = data.peso;
                        document.getElementById('altura').value = data.altura;
                        document.getElementById('presion_arterial').value = data.presion_arterial;
                        document.getElementById('frecuencia_cardiaca').value = data.frecuencia_cardiaca;
                        document.getElementById('nivel_glucosa').value = data.nivel_glucosa;
                    }
                });
            });
            
            // Función para analizar datos
            async function analyzeData(biometricData) {
                showLoading(true);
                hideError();
                
                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ message: biometricData })
                    });
                    
                    if (!response.ok) {
                        throw new Error(`Error en la respuesta del servidor: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    showResult(data.response);
                } catch (error) {
                    console.error('Error:', error);
                    showError(`Error al conectar con el servicio: ${error.message}`);
                } finally {
                    showLoading(false);
                }
            }
            
            // Eventos de formulario
            biometricForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const biometricData = {
                    peso: document.getElementById('peso').value,
                    altura: document.getElementById('altura').value,
                    presion_arterial: document.getElementById('presion_arterial').value,
                    frecuencia_cardiaca: document.getElementById('frecuencia_cardiaca').value,
                    nivel_glucosa: document.getElementById('nivel_glucosa').value
                };
                
                await analyzeData(biometricData);
            });
            
            // Evento para analizar JSON
            analyzeJsonBtn.addEventListener('click', async () => {
                try {
                    const jsonData = JSON.parse(jsonInput.value);
                    await analyzeData(jsonData);
                } catch (error) {
                    showError(`Error en el formato JSON: ${error.message}`);
                }
            });
            
            // Funciones auxiliares
            function showLoading(isLoading) {
                loadingIndicator.style.display = isLoading ? 'block' : 'none';
                analyzeBtn.disabled = isLoading;
                analyzeJsonBtn.disabled = isLoading;
            }
            
            function showResult(result) {
                resultText.textContent = result;
                resultContainer.style.display = 'block';
            }
            
            function showError(message) {
                errorMessage.textContent = message;
                errorMessage.style.display = 'block';
            }
            
            function hideError() {
                errorMessage.style.display = 'none';
            }
        });
    </script>
</body>
</html>