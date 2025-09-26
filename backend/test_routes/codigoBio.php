<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración y Análisis Biométrico - Imesys</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        body {
            background-color: #F5F5F5;
            color: #333;
            line-height: 1.6;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background-color: #FFFFFF;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #007C91;
            font-size: 24px;
            margin-bottom: 30px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        h2.form-title {
            color: #007C91;
            font-size: 20px;
            margin-bottom: 20px;
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #E0E0E0;
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            background-color: #F5F5F5;
            border: 1px solid #E0E0E0;
            border-bottom: none;
            border-radius: 5px 5px 0 0;
            margin-right: 5px;
            color: #333;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .tab.active {
            background-color: #B2EBF2;
            color: #007C91;
            border-color: #B2EBF2;
        }

        .tab:hover:not(.active) {
            background-color: #E0F7FA;
            color: #005F73;
        }

        .tab-content {
            display: none;
            padding: 20px;
            border: 1px solid #E0E0E0;
            border-top: none;
            border-radius: 0 0 5px 5px;
            background-color: #FFFFFF;
        }

        .tab-content.active {
            display: block;
        }

        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        input, select, Editora {
            width: 100%;
            padding: 12px;
            background-color: #F9F9F9;
            border: 1px solid #E0E0E0;
            border-radius: 4px;
            color: #333;
            font-size: 16px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #B2EBF2;
            box-shadow: 0 0 5px rgba(178, 235, 242, 0.5);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        button {
            background-color: #007C91;
            color: #FFFFFF;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #005F73;
        }

        button.secondary {
            background-color: #0288D1;
        }

        button.secondary:hover {
            background-color: #0277BD;
        }

        button.danger {
            background-color: #D32F2F;
        }

        button.danger:hover {
            background-color: #B71C1C;
        }

        .table-container {
            overflow-x: auto;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #E0E0E0;
        }

        th {
            background-color: #E0F7FA;
            color: #007C91;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
        }

        td {
            color: #333;
        }

        th.descripcion, td.descripcion,
        th.resultado, td.resultado {
            min-width: 200px;
        }

        tr:hover {
            background-color: #F9F9F9;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-bar select, .search-bar input {
            flex: 1;
        }

        .notification {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 14px;
            display: none;
        }

        .success {
            background-color: #E8F5E9;
            color: #2E7D32;
            border: 1px solid #C8E6C9;
        }

        .error {
            background-color: #FFEBEE;
            color: #C62828;
            border: 1px solid #FFCDD2;
        }

        .loading {
            text-align: center;
            padding: 20px;
            display: none;
            color: #007C91;
        }

        .spinner {
            width: 36px;
            height: 36px;
            border: 4px solid #E0F7FA;
            border-top-color: #007C91;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #FFFFFF;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #E0E0E0;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .close-button {
            color: #666;
            float: right;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close-button:hover {
            color: #007C91;
        }

        .examples {
            margin-top: 20px;
            padding: 10px;
            background-color: #F9F9F9;
            border-radius: 4px;
        }

        .example-btn {
            background-color: #4CAF50;
            margin:5px;
            padding: 8px 15px;
        }

        .example-btn:hover {
            background-color: #45a049;
        }

        .result-container {
            margin-top: 20px;
            padding: 15px;
            background-color: #E8F5E9;
            border-radius: 4px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-heartbeat"></i> Administración y Análisis Biométrico</h1>
        
        <div id="notification" class="notification"></div>
        
        <div class="tabs">
            <div class="tab active" data-tab="listar">Listar Datos</div>
            <div class="tab" data-tab="crear">Crear Dato</div>
            <div class="tab" data-tab="buscar">Buscar Datos</div>
        </div>
        
        <div id="listar" class="tab-content active">
            <h2 class="form-title">Todos los Datos Biométricos</h2>
            <div class="loading" id="loading-list">
                <div class="spinner"></div>
                <p>Cargando datos...</p>
            </div>
            <div class="table-container">
                <table id="datos-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario ID</th>
                            <th>Peso (kg)</th>
                            <th>Altura (cm)</th>
                            <th>Presión Arterial</th>
                            <th>Frec. Cardíaca</th>
                            <th>Nivel Glucosa</th>
                            <th class="descripcion">Descripción</th>
                            <th class="resultado">Resultado Predicción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="datos-body"></tbody>
                </table>
            </div>
        </div>
        
        <div id="crear" class="tab-content">
            <h2 class="form-title">Registrar Nuevo Dato Biométrico</h2>
            <div class="form-container">
                <form id="crear-form">
                    <div class="form-group">
                        <label for="id_usuario">ID de Usuario</label>
                        <input type="number" id="id_usuario" name="id_usuario" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label for="peso">Peso (kg)</label>
                        <input type="number" id="peso" name="peso" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="altura">Altura (cm)</label>
                        <input type="number" id="altura" name="altura" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="presion_arterial">Presión Arterial</label>
                        <input type="text" id="presion_arterial" name="presion_arterial" placeholder="120/80" required>
                    </div>
                    <div class="form-group">
                        <label for="frecuencia_cardiaca">Frecuencia Cardíaca (lpm)</label>
                        <input type="number" id="frecuencia_cardiaca" name="frecuencia_cardiaca" required>
                    </div>
                    <div class="form-group">
                        <label for="nivel_glucosa">Nivel de Glucosa (mg/dL)</label>
                        <input type="number" id="nivel_glucosa" name="nivel_glucosa" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcion_resultado">Descripción del Resultado</label>
                        <textarea id="descripcion_resultado" name="descripcion_resultado" rows="3"></textarea>
                    </div>
                    <button type="submit">Analizar y Guardar</button>
                </form>
                <div class="examples">
                    <h3>Ejemplos:</h3>
                    <p>Prueba con estos ejemplos predefinidos:</p>
                    <button class="example-btn" data-example="healthy">Persona Saludable</button>
                    <button class="example-btn" data-example="hypertension">Hipertensión</button>
                    <button class="example-btn" data-example="diabetic">Diabético</button>
                </div>
                <div class="result-container" id="prediction-result">
                    <h2>Resultado del Análisis:</h2>
                    <div class="result" id="result-text"></div>
                </div>
            </div>
        </div>
        
        <div id="buscar" class="tab-content">
            <h2 class="form-title">Buscar Datos Biométricos</h2>
            <div class="search-bar">
                <select id="search-type">
                    <option value="id_dato">Buscar por ID de Dato</option>
                    <option value="id_usuario">Buscar por ID de Usuario</option>
                </select>
                <input type="number" id="search-input" placeholder="Ingrese ID" autocomplete="off">
                <button id="search-button">Buscar</button>
            </div>
            <div class="loading" id="loading-search">
                <div class="spinner"></div>
                <p>Buscando datos...</p>
            </div>
            <div class="table-container">
                <div id="search-results"></div>
            </div>
        </div>
        
        <div id="edit-modal" class="modal">
            <div class="modal-content">
                <span class="close-button" id="close-modal">×</span>
                <h2 class="form-title">Editar Dato Biométrico</h2>
                <form id="edit-form" class="form-container">
                    <input type="hidden" id="edit-id">
                    <input type="hidden" id="edit-id-usuario">
                    <div class="form-group">
                        <label for="edit-peso">Peso (kg)</label>
                        <input type="number" id="edit-peso" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-altura">Altura (cm)</label>
                        <input type="number" id="edit-altura" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-presion">Presión Arterial</label>
                        <input type="text" id="edit-presion" placeholder="120/80" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-frecuencia">Frecuencia Cardíaca (lpm)</label>
                        <input type="number" id="edit-frecuencia" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-glucosa">Nivel de Glucosa (mg/dL)</label>
                        <input type="number" id="edit-glucosa" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-descripcion">Descripción del Resultado</label>
                        <textarea id="edit-descripcion" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit-prediccion">Resultado de Predicción</label>
                        <input type="text" id="edit-prediccion">
                    </div>
                    <button type="submit" class="secondary">Actualizar Datos</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const API_URL_BIOMETRIC = 'http://localhost:5000/api/datos_biometricos';
        const API_URL_PREDICTION = 'http://localhost:5000/api/prediccion_bio';

        const tabButtons = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');
        const datosBody = document.getElementById('datos-body');
        const crearForm = document.getElementById('crear-form');
        const searchType = document.getElementById('search-type');
        const searchInput = document.getElementById('search-input');
        const searchButton = document.getElementById('search-button');
        const searchResults = document.getElementById('search-results');
        const editModal = document.getElementById('edit-modal');
        const closeModal = document.getElementById('close-modal');
        const editForm = document.getElementById('edit-form');
        const notification = document.getElementById('notification');
        const loadingList = document.getElementById('loading-list');
        const loadingSearch = document.getElementById('loading-search');
        const resultContainer = document.getElementById('prediction-result');
        const resultText = document.getElementById('result-text');
        const exampleBtns = document.querySelectorAll('.example-btn');

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

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                button.classList.add('active');
                const tabId = button.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
                
                if (tabId === 'listar') {
                    cargarDatos();
                }
            });
        });

        function mostrarNotificacion(mensaje, tipo) {
            notification.textContent = mensaje;
            notification.className = `notification ${tipo}`;
            notification.style.display = 'block';
            setTimeout(() => {
                notification.style.display = 'none';
            }, 5000);
        }

        async function cargarDatos() {
            try {
                loadingList.style.display = 'block';
                datosBody.innerHTML = '';
                
                const response = await fetch(API_URL_BIOMETRIC);
                
                if (!response.ok) {
                    throw new Error('Error al cargar los datos');
                }
                
                const datos = await response.json();
                
                datos.forEach(dato => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${dato.id_dato}</td>
                        <td>${dato.id_usuario}</td>
                        <td>${dato.peso}</td>
                        <td>${dato.altura}</td>
                        <td>${dato.presion_arterial}</td>
                        <td>${dato.frecuencia_cardiaca}</td>
                        <td>${dato.nivel_glucosa}</td>
                        <td class="descripcion">${dato.descripcion_resultado || ''}</td>
                        <td class="resultado">${dato.resultado_prediccion || ''}</td>
                        <td class="actions">
                            <button class="secondary btn-editar" data-id="${dato.id_dato}">Editar</button>
                            <button class="danger btn-eliminar" data-id="${dato.id_dato}">Eliminar</button>
                        </td>
                    `;
                    datosBody.appendChild(row);
                });
                
                document.querySelectorAll('.btn-editar').forEach(btn => {
                    btn.addEventListener('click', () => abrirModalEditar(btn.getAttribute('data-id')));
                });
                
                document.querySelectorAll('.btn-eliminar').forEach(btn => {
                    btn.addEventListener('click', () => eliminarDato(btn.getAttribute('data-id')));
                });
                
            } catch (error) {
                mostrarNotificacion('Error: ' + error.message, 'error');
            } finally {
                loadingList.style.display = 'none';
            }
        }

        async function obtenerPrediccion(biometricData) {
            try {
                const response = await fetch(API_URL_PREDICTION, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ message: biometricData })
                });
                
                if (!response.ok) {
                    throw new Error('Error en la predicción');
                }
                
                const data = await response.json();
                return data.response;
            } catch (error) {
                throw new Error(`Error en la predicción: ${error.message}`);
            }
        }

        crearForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const biometricData = {
                id_usuario: parseInt(document.getElementById('id_usuario').value),
                peso: parseFloat(document.getElementById('peso').value),
                altura: parseFloat(document.getElementById('altura').value),
                presion_arterial: document.getElementById('presion_arterial').value,
                frecuencia_cardiaca: parseInt(document.getElementById('frecuencia_cardiaca').value),
                nivel_glucosa: parseInt(document.getElementById('nivel_glucosa').value),
                descripcion_resultado: document.getElementById('descripcion_resultado').value
            };
            
            try {
                // Obtener predicción
                const prediccion = await obtenerPrediccion({
                    peso: biometricData.peso,
                    altura: biometricData.altura,
                    presion_arterial: biometricData.presion_arterial,
                    frecuencia_cardiaca: biometricData.frecuencia_cardiaca,
                    nivel_glucosa: biometricData.nivel_glucosa
                });
                
                // Mostrar resultado de la predicción
                resultText.textContent = prediccion;
                resultContainer.style.display = 'block';
                
                // Agregar predicción al objeto de datos
                biometricData.resultado_prediccion = prediccion;
                
                // Guardar datos en la base de datos
                const response = await fetch(API_URL_BIOMETRIC, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(biometricData)
                });
                
                if (!response.ok) {
                    throw new Error('Error al guardar los datos biométricos');
                }
                
                const result = await response.json();
                mostrarNotificacion('Dato biométrico guardado con ID: ' + result.id, 'success');
                crearForm.reset();
                resultContainer.style.display = 'none';
                
                document.querySelector('[data-tab="listar"]').click();
                
            } catch (error) {
                mostrarNotificacion('Error: ' + error.message, 'error');
            }
        });

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

        searchButton.addEventListener('click', async () => {
            const tipo = searchType.value;
            const id = searchInput.value.trim();
            
            if (!id) {
                mostrarNotificacion('Por favor, ingrese un ID válido', 'error');
                return;
            }
            
            try {
                loadingSearch.style.display = 'block';
                searchResults.innerHTML = '';
                
                let url = tipo === 'id_dato' ? `${API_URL_BIOMETRIC}/${id}` : `${API_URL_BIOMETRIC}/usuario/${id}`;
                
                const response = await fetch(url);
                
                if (!response.ok) {
                    if (response.status === 404) {
                        searchResults.innerHTML = '<p class="error">No se encontraron resultados.</p>';
                        return;
                    }
                    throw new Error('Error en la búsqueda');
                }
                
                const datos = await response.json();
                const datosArray = Array.isArray(datos) ? datos : [datos];
                
                const resultTable = document.createElement('table');
                resultTable.innerHTML = `
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario ID</th>
                            <th>Peso (kg)</th>
                            <th>Altura (cm)</th>
                            <th>Presión Arterial</th>
                            <th>Frec. Cardíaca</th>
                            <th>Nivel Glucosa</th>
                            <th class="descripcion">Descripción</th>
                            <th class="resultado">Resultado Predicción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="search-results-body"></tbody>
                `;
                
                searchResults.appendChild(resultTable);
                const resultsBody = document.getElementById('search-results-body');
                
                datosArray.forEach(dato => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${dato.id_dato}</td>
                        <td>${dato.id_usuario}</td>
                        <td>${dato.peso}</td>
                        <td>${dato.altura}</td>
                        <td>${dato.presion_arterial}</td>
                        <td>${dato.frecuencia_cardiaca}</td>
                        <td>${dato.nivel_glucosa}</td>
                        <td class="descripcion">${dato.descripcion_resultado || ''}</td>
                        <td class="resultado">${dato.resultado_prediccion || ''}</td>
                        <td class="actions">
                            <button class="secondary btn-editar" data-id="${dato.id_dato}">Editar</button>
                            <button class="danger btn-eliminar" data-id="${dato.id_dato}">Eliminar</button>
                        </td>
                    `;
                    resultsBody.appendChild(row);
                });
                
                document.querySelectorAll('#search-results .btn-editar').forEach(btn => {
                    btn.addEventListener('click', () => abrirModalEditar(btn.getAttribute('data-id')));
                });
                
                document.querySelectorAll('#search-results .btn-eliminar').forEach(btn => {
                    btn.addEventListener('click', () => eliminarDato(btn.getAttribute('data-id')));
                });
                
            } catch (error) {
                mostrarNotificacion('Error: ' + error.message, 'error');
            } finally {
                loadingSearch.style.display = 'none';
            }
        });

        async function abrirModalEditar(id) {
            try {
                const response = await fetch(`${API_URL_BIOMETRIC}/${id}`);
                
                if (!response.ok) {
                    throw new Error('Error al obtener los datos para editar');
                }
                
                const dato = await response.json();
                
                document.getElementById('edit-id').value = dato.id_dato;
                document.getElementById('edit-id-usuario').value = dato.id_usuario;
                document.getElementById('edit-peso').value = dato.peso;
                document.getElementById('edit-altura').value = dato.altura;
                document.getElementById('edit-presion').value = dato.presion_arterial;
                document.getElementById('edit-frecuencia').value = dato.frecuencia_cardiaca;
                document.getElementById('edit-glucosa').value = dato.nivel_glucosa;
                document.getElementById('edit-descripcion').value = dato.descripcion_resultado;
                document.getElementById('edit-prediccion').value = dato.resultado_prediccion;
                
                editModal.style.display = 'block';
                
            } catch (error) {
                mostrarNotificacion('Error: ' + error.message, 'error');
            }
        }

        closeModal.addEventListener('click', () => {
            editModal.style.display = 'none';
        });

        window.addEventListener('click', (e) => {
            if (e.target === editModal) {
                editModal.style.display = 'none';
            }
        });

        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const id = document.getElementById('edit-id').value;
            const datoActualizado = {
                peso: parseFloat(document.getElementById('edit-peso').value),
                altura: parseFloat(document.getElementById('edit-altura').value),
                presion_arterial: document.getElementById('edit-presion').value,
                frecuencia_cardiaca: parseInt(document.getElementById('edit-frecuencia').value),
                nivel_glucosa: parseInt(document.getElementById('edit-glucosa').value),
                descripcion_resultado: document.getElementById('edit-descripcion').value,
                resultado_prediccion: document.getElementById('edit-prediccion').value
            };
            
            try {
                const response = await fetch(`${API_URL_BIOMETRIC}/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(datoActualizado)
                });
                
                if (!response.ok) {
                    throw new Error('Error al actualizar el dato biométrico');
                }
                
                mostrarNotificacion('Dato biométrico actualizado correctamente', 'success');
                editModal.style.display = 'none';
                
                if (document.querySelector('[data-tab="listar"]').classList.contains('active')) {
                    cargarDatos();
                } else if (document.querySelector('[data-tab="buscar"]').classList.contains('active')) {
                    searchButton.click();
                }
                
            } catch (error) {
                mostrarNotificacion('Error: ' + error.message, 'error');
            }
        });

        async function eliminarDato(id) {
            if (!confirm('¿Está seguro de que desea eliminar este dato biométrico?')) {
                return;
            }
            
            try {
                const response = await fetch(`${API_URL_BIOMETRIC}/${id}`, {
                    method: 'DELETE'
                });
                
                if (!response.ok) {
                    throw new Error('Error al eliminar el dato biométrico');
                }
                
                mostrarNotificacion('Dato biométrico eliminado correctamente', 'success');
                
                if (document.querySelector('[data-tab="listar"]').classList.contains('active')) {
                    cargarDatos();
                } else if (document.querySelector('[data-tab="buscar"]').classList.contains('active')) {
                    searchButton.click();
                }
                
            } catch (error) {
                mostrarNotificacion('Error: ' + error.message, 'error');
            }
        }

        document.addEventListener('DOMContentLoaded', cargarDatos);
    </script>
</body>
</html>

