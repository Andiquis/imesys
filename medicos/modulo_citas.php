<?php
session_start();

// Desactivar salida de errores y habilitar registro
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');
error_reporting(E_ALL);

if (!isset($_SESSION['loggedin'])) {
    header("Location: login_medico.php");
    exit;
}

require 'conexion.php';

$medico_id = $_SESSION['id_medico'];

$stmt = $conexion->prepare("SELECT m.nombre, m.apellido, m.correo, m.foto, e.nombre_especialidad 
                           FROM medicos m
                           JOIN especialidades e ON m.id_especialidad = e.id_especialidad
                           WHERE m.id_medico = ?");
$stmt->bind_param("i", $medico_id);
$stmt->execute();
$stmt->bind_result($nombre, $apellido, $correo, $foto, $especialidad);
$stmt->fetch();
$stmt->close();

// Función para realizar solicitudes a la API
function makeApiRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headers = curl_getinfo($ch);
    
    // Registrar respuesta para depuración
    file_put_contents('debug_api.txt', "URL: $url\nMethod: $method\nHTTP Code: $httpCode\nResponse: $response\n\n", FILE_APPEND);
    
    curl_close($ch);
    
    // Verificar si la respuesta parece HTML
    if (strpos($response, '<br') !== false || strpos($response, '<b>') !== false) {
        throw new Exception('Respuesta no válida: se recibió HTML en lugar de JSON');
    }
    
    if ($httpCode >= 200 && $httpCode < 300) {
        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
        }
        return $decoded;
    }
    
    throw new Exception("Error en la solicitud a la API: HTTP $httpCode");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda de Citas Médicas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .estado-pendiente { background-color: #fefcbf; color: #b7791f; }
        .estado-confirmada { background-color: #c6f6d5; color: #2f855a; }
        .estado-cancelada { background-color: #fed7d7; color: #c53030; }
        .estado-atendida { background-color: #bee3f8; color: #2b6cb0; }
        .estado-no-disponible { background-color: #e2e8f0; color: #4a5568; }

        .sidebar.active {
            left: 0;
        }
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 30;
        }
        .overlay.active {
            display: block;
        }
        
        .error-message {
            background-color: #fed7d7;
            color: #c53030;
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            display: none;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include 'header_medico.php'; ?>
    <div class="main-content">
        <div class="container mx-auto p-6 space-y-6">
            <!-- Mensaje de error -->
            <div id="error-message" class="error-message"></div>

            <!-- Navigation Tabs -->
            <div class="border-b border-gray-200">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500">
                    <li class="mr-2">
                        <a id="tab-todas" class="inline-flex p-4 rounded-t-lg border-b-2 cursor-pointer border-blue-600 text-blue-600" onclick="cambiarVista('todas')">
                            Todas las citas
                        </a>
                    </li>
                    <li class="mr-2">
                        <a id="tab-pendientes" class="inline-flex p-4 rounded-t-lg border-b-2 cursor-pointer border-transparent hover:text-gray-600 hover:border-gray-300" onclick="cambiarVista('pendientes')">
                            Pendientes
                            <span id="count-pendientes" class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-yellow-500 rounded-full">0</span>
                        </a>
                    </li>
                    <li class="mr-2">
                        <a id="tab-confirmadas" class="inline-flex p-4 rounded-t-lg border-b-2 cursor-pointer border-transparent hover:text-gray-600 hover:border-gray-300" onclick="cambiarVista('confirmadas')">
                            Confirmadas
                            <span id="count-confirmadas" class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-blue-600 rounded-full">0</span>
                        </a>
                    </li>
                    <li class="mr-2">
                        <a id="tab-canceladas" class="inline-flex p-4 rounded-t-lg border-b-2 cursor-pointer border-transparent hover:text-gray-600 hover:border-gray-300" onclick="cambiarVista('canceladas')">
                            Canceladas
                            <span id="count-canceladas" class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full">0</span>
                        </a>
                    </li>
                    <li class="mr-2">
                        <a id="tab-atendidas" class="inline-flex p-4 rounded-t-lg border-b-2 cursor-pointer border-transparent hover:text-gray-600 hover:border-gray-300" onclick="cambiarVista('atendidas')">
                            Atendidas
                            <span id="count-atendidas" class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-green-600 rounded-full">0</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Left Panel: Availability and Block Dates -->
                <div class="lg:col-span-4 space-y-6">
                    <!-- Create Availability Form -->
                    <div class="bg-white shadow-md rounded-lg overflow-hidden">
                        <div class="bg-blue-600 text-white px-4 py-3">
                            <h2 class="text-lg font-semibold">Crear Horario de Atención</h2>
                        </div>
                        <div class="p-4">
                            <form id="form-disponibilidad" onsubmit="crearDisponibilidad(event)" class="space-y-4">
                                <div>
                                    <label for="fecha" class="block text-sm font-medium text-gray-700">Fecha</label>
                                    <input type="date" id="fecha" name="fecha" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="horaInicio" class="block text-sm font-medium text-gray-700">Hora inicio</label>
                                        <input type="time" id="horaInicio" name="horaInicio" value="08:00" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="horaFin" class="block text-sm font-medium text-gray-700">Hora fin</label>
                                        <input type="time" id="horaFin" name="horaFin" value="17:00" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    </div>
                                </div>
                                <div>
                                    <label for="intervalo" class="block text-sm font-medium text-gray-700">Intervalo (minutos)</label>
                                    <select id="intervalo" name="intervalo" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        <option value="15">15 minutos</option>
                                        <option value="30" selected>30 minutos</option>
                                        <option value="45">45 minutos</option>
                                        <option value="60">60 minutos</option>
                                    </select>
                                </div>
                                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-150">
                                    Crear Disponibilidad
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Block Dates Form -->
                    <div class="bg-white shadow-md rounded-lg overflow-hidden">
                        <div class="bg-red-600 text-white px-4 py-3">
                            <h2 class="text-lg font-semibold">Crear Actividades Personales</h2>
                        </div>
                        <div class="p-4">
                            <form id="form-bloqueo" onsubmit="bloquearFechas(event)" class="space-y-4">
                                <div>
                                    <label for="nuevaFecha" class="block text-sm font-medium text-gray-700">Seleccionar fechas</label>
                                    <div class="flex space-x-2">
                                        <input type="date" id="nuevaFecha" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        <button type="button" onclick="agregarFecha()" class="mt-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-150">
                                            Agregar
                                        </button>
                                    </div>
                                    <div id="fechas-lista" class="mt-2"></div>
                                    <div id="fechas-error" class="hidden text-red-600 text-sm mt-1">
                                        Debe seleccionar al menos una fecha.
                                    </div>
                                </div>
                                <div>
                                    <label for="etiqueta" class="block text-sm font-medium text-gray-700">Motivo (opcional)</label>
                                    <input type="text" id="etiqueta" name="etiqueta" placeholder="Ej. Vacaciones" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="recordatorio" class="block text-sm font-medium text-gray-700">Descripción (opcional)</label>
                                    <textarea id="recordatorio" name="recordatorio" placeholder="Ej. Reunión externa" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                                </div>
                                <button type="submit" class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 transition duration-150">
                                    Crear Actividad
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Calendar and Appointments -->
                <div class="lg:col-span-8 space-y-6">
                    <!-- Calendar View Selector -->
                    <div class="flex space-x-2">
                        <button id="vista-diaria" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-150" onclick="cambiarVistaCalendario('diaria')">
                            Vista Diaria
                        </button>
                        <button id="vista-semanal" class="bg-gray-200 text-gray-700 py-2 px-4 rounded-md hover:bg-blue-700 hover:text-white transition duration-150" onclick="cambiarVistaCalendario('semanal')">
                            Vista Semanal
                        </button>
                    </div>

                    <!-- Daily View -->
                    <div id="vista-diaria-section" class="bg-white shadow-md rounded-lg overflow-hidden">
                        <div class="bg-gray-100 px-6 py-4 rounded-t-lg shadow-sm flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <h2 id="fecha-titulo" class="text-xl font-semibold text-gray-800"></h2>
                                <input type="date" id="fechaSeleccionada" onchange="seleccionarFecha(this.value)" class="rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            </div>
                            <div class="flex-1">
                                <input type="text" id="filtroPaciente" oninput="aplicarFiltros()" placeholder="Nombre del paciente" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            </div>
                            <div class="flex-shrink-0">
                                <button onclick="limpiarFiltros()" class="w-full bg-red-100 text-red-600 font-medium px-4 py-2 rounded-lg hover:bg-red-200 transition duration-150">
                                    Limpiar filtros
                                </button>
                            </div>
                        </div>
                        <div class="p-4">
                            <div id="no-citas-diaria" class="hidden text-center py-8">
                                <p class="text-gray-500">No hay citas programadas para esta fecha</p>
                                <button onclick="setFechaDisponibilidad()" class="mt-2 inline-block bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-150">
                                    Crear disponibilidad para esta fecha
                                </button>
                            </div>
                            <div id="citas-diaria-table" class="overflow-x-auto hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="citas-diaria-body" class="bg-white divide-y divide-gray-200"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Weekly View -->
                    <div id="vista-semanal-section" class="space-y-6 hidden">
                        <div class="bg-white shadow-md rounded-lg overflow-hidden">
                            <div class="bg-gray-100 px-4 py-3 flex justify-between items-center">
                                <h2 id="semana-titulo" class="text-lg font-semibold text-gray-800"></h2>
                                <div class="flex space-x-2">
                                    <button onclick="semanaAnterior()" class="bg-gray-200 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-300 transition duration-150">
                                        <i class="fas fa-chevron-left mr-1"></i> Anterior
                                    </button>
                                    <button onclick="semanaSiguiente()" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-150">
                                        Siguiente <i class="fas fa-chevron-right ml-1"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-4">
                                <div id="dias-semana" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-4"></div>
                            </div>
                        </div>
                        <div class="bg-white shadow-md rounded-lg overflow-hidden">
                            <div class="bg-gray-100 px-4 py-3">
                                <h2 class="text-lg font-semibold text-gray-800">Citas programadas</h2>
                            </div>
                            <div class="p-4">
                                <div id="no-citas-semanal" class="hidden text-center py-8">
                                    <p class="text-gray-500">No hay citas programadas con los filtros seleccionados</p>
                                </div>
                                <div id="citas-semanal-table" class="overflow-x-auto hidden">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="citas-semanal-body" class="bg-white divide-y divide-gray-200"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'footer_medico.php'; ?>
<script>
    const apiUrl = 'http://localhost:5000/api';
    const idMedico = <?= json_encode($medico_id) ?>;
    let citasMedico = [];
    let citasFiltradas = [];
    let citasPendientes = [];
    let citasConfirmadas = [];
    let citasCanceladas = [];
    let citasAtendidas = [];
    let fechasDisponibles = [];
    let fechasNoDisponibles = [];
    let nuevaFecha = '';
    let filtroFecha = '';
    let filtroPaciente = '';
    let vistaActual = 'todas';
    let vistaCalendario = 'diaria';
    let fechaSeleccionada = new Date().toISOString().split('T')[0];
    let fechaInicioSemana = new Date();
    let fechaFinSemana = new Date();
    let diasSemana = [];
    const fechaActual = new Date().toISOString().split('T')[0];

    // Mostrar mensaje de error
    function mostrarError(mensaje) {
        const errorDiv = document.getElementById('error-message');
        errorDiv.textContent = mensaje;
        errorDiv.style.display = 'block';
        setTimeout(() => {
            errorDiv.style.display = 'none';
        }, 5000);
    }

    // Inicializar semana actual
    function calcularSemanaActual() {
        const hoy = new Date();
        const diaSemana = hoy.getDay();
        const primerDia = new Date(hoy);
        primerDia.setDate(hoy.getDate() - diaSemana);
        fechaInicioSemana = primerDia;
        const ultimoDia = new Date(primerDia);
        ultimoDia.setDate(primerDia.getDate() + 6);
        fechaFinSemana = ultimoDia;
    }

    // Inicializar
    document.addEventListener('DOMContentLoaded', () => {
        obtenerCitasMedico();
        obtenerDisponibilidadMedico();
        generarDiasSemana();
        document.getElementById('fecha').setAttribute('min', fechaActual);
        document.getElementById('nuevaFecha').setAttribute('min', fechaActual);
        document.getElementById('fechaSeleccionada').value = fechaSeleccionada;
        actualizarFechaTitulo();
    });

    // Obtener citas del médico
    async function obtenerCitasMedico() {
        try {
            const response = await fetch(`${apiUrl}/reservar-cita/doctor/${idMedico}/citas`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) {
                const text = await response.text();
                console.error('Error HTTP:', response.status, 'Contenido:', text);
                throw new Error(`Error HTTP: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Respuesta no es JSON:', text);
                throw new Error('Respuesta no es JSON válida');
            }
            citasMedico = await response.json();
            citasFiltradas = [...citasMedico];
            filtrarCitasPorEstado();
            aplicarFiltros();
        } catch (error) {
            console.error('Error al obtener citas:', error);
            mostrarError('Error al obtener las citas: ' + error.message);
        }
    }

    // Obtener disponibilidad del médico
    async function obtenerDisponibilidadMedico() {
        try {
            const response = await fetch(`${apiUrl}/reservar-cita/doctor/${idMedico}/dates`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) {
                const text = await response.text();
                console.error('Error HTTP:', response.status, 'Contenido:', text);
                throw new Error(`Error HTTP: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Respuesta no es JSON:', text);
                throw new Error('Respuesta no es JSON válida');
            }
            const disponibilidad = await response.json();
            fechasDisponibles = disponibilidad.available || [];
            fechasNoDisponibles = disponibilidad.unavailable || [];
            generarDiasSemana();
        } catch (error) {
            console.error('Error al obtener disponibilidad:', error);
            mostrarError('Error al obtener disponibilidad: ' + error.message);
        }
    }

    // Filtrar citas por estado
    function filtrarCitasPorEstado() {
        citasPendientes = citasMedico.filter(cita => cita.estado === 'Pendiente');
        citasConfirmadas = citasMedico.filter(cita => cita.estado === 'Confirmada');
        citasCanceladas = citasMedico.filter(cita => cita.estado === 'Cancelada');
        citasAtendidas = citasMedico.filter(cita => cita.estado === 'Atendida');
        document.getElementById('count-pendientes').textContent = citasPendientes.length;
        document.getElementById('count-confirmadas').textContent = citasConfirmadas.length;
        document.getElementById('count-canceladas').textContent = citasCanceladas.length;
        document.getElementById('count-atendidas').textContent = citasAtendidas.length;
    }

    // Cambiar vista (todas, pendientes, etc.)
    function cambiarVista(vista) {
        vistaActual = vista;
        document.querySelectorAll('#tab-todas, #tab-pendientes, #tab-confirmadas, #tab-canceladas, #tab-atendidas').forEach(tab => {
            tab.classList.remove('border-blue-600', 'text-blue-600', 'border-yellow-500', 'text-yellow-600', 'border-red-600', 'text-red-600', 'border-green-600', 'text-green-600');
            tab.classList.add('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
        });
        const tab = document.getElementById(`tab-${vista}`);
        if (vista === 'todas') {
            tab.classList.add('border-blue-600', 'text-blue-600');
        } else if (vista === 'pendientes') {
            tab.classList.add('border-yellow-500', 'text-yellow-600');
        } else if (vista === 'confirmadas') {
            tab.classList.add('border-blue-600', 'text-blue-600');
        } else if (vista === 'canceladas') {
            tab.classList.add('border-red-600', 'text-red-600');
        } else if (vista === 'atendidas') {
            tab.classList.add('border-green-600', 'text-green-600');
        }
        aplicarFiltros();
    }

    // Aplicar filtros
    function aplicarFiltros() {
        let citas = [...citasMedico];
        if (vistaActual !== 'todas') {
            const estadoMap = {
                'pendientes': 'Pendiente',
                'confirmadas': 'Confirmada',
                'canceladas': 'Cancelada',
                'atendidas': 'Atendida'
            };
            citas = citas.filter(cita => cita.estado === estadoMap[vistaActual]);
        }
        if (filtroFecha) {
            citas = citas.filter(cita => new Date(cita.fecha_cita).toISOString().split('T')[0] === filtroFecha);
        }
        if (filtroPaciente) {
            const terminoBusqueda = filtroPaciente.toLowerCase();
            citas = citas.filter(cita => {
                const nombreCompleto = `${cita.paciente || ''} ${cita.paciente_apellido || ''}`.toLowerCase();
                return nombreCompleto.includes(terminoBusqueda);
            });
        }
        citasFiltradas = citas;
        renderCitas();
    }

    // Limpiar filtros
    function limpiarFiltros() {
        filtroFecha = '';
        filtroPaciente = '';
        vistaActual = 'todas';
        document.getElementById('filtroPaciente').value = '';
        document.getElementById('fechaSeleccionada').value = fechaSeleccionada;
        cambiarVista('todas');
    }

    // Agregar fecha para bloqueo
    function agregarFecha() {
        nuevaFecha = document.getElementById('nuevaFecha').value;
        if (!nuevaFecha) return;
        const fechasLista = document.getElementById('fechas-lista');
        const fechas = JSON.parse(fechasLista.getAttribute('data-fechas') || '[]');
        if (!fechas.includes(nuevaFecha)) {
            fechas.push(nuevaFecha);
            fechasLista.setAttribute('data-fechas', JSON.stringify(fechas));
            renderFechasBloqueo();
            document.getElementById('nuevaFecha').value = '';
            document.getElementById('fechas-error').classList.add('hidden');
        }
    }

    // Eliminar fecha de bloqueo
    function eliminarFecha(index) {
        const fechasLista = document.getElementById('fechas-lista');
        const fechas = JSON.parse(fechasLista.getAttribute('data-fechas') || '[]');
        fechas.splice(index, 1);
        fechasLista.setAttribute('data-fechas', JSON.stringify(fechas));
        renderFechasBloqueo();
    }

    // Renderizar fechas de bloqueo
    function renderFechasBloqueo() {
        const fechasLista = document.getElementById('fechas-lista');
        const fechas = JSON.parse(fechasLista.getAttribute('data-fechas') || '[]');
        fechasLista.innerHTML = fechas.length > 0 ? `
            <ul class="space-y-2">
                ${fechas.map((fecha, i) => `
                    <li class="flex justify-between items-center bg-gray-100 p-2 rounded-md">
                        <span>${new Date(fecha).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' })}</span>
                        <button type="button" onclick="eliminarFecha(${i})" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </li>
                `).join('')}
            </ul>
        ` : '';
    }

    // Bloquear fechas
    async function bloquearFechas(event) {
        event.preventDefault();
        const fechasLista = document.getElementById('fechas-lista');
        const fechas = JSON.parse(fechasLista.getAttribute('data-fechas') || '[]');
        if (fechas.length === 0) {
            document.getElementById('fechas-error').classList.remove('hidden');
            return;
        }
        const etiqueta = document.getElementById('etiqueta').value || null;
        const recordatorio = document.getElementById('recordatorio').value || null;
        try {
            const response = await fetch(`${apiUrl}/reservar-cita/doctor/bloquear-fechas`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ id_medico: idMedico, fechas, etiqueta, recordatorio })
            });
            if (!response.ok) {
                const text = await response.text();
                console.error('Error HTTP:', response.status, 'Contenido:', text);
                throw new Error(`Error HTTP: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Respuesta no es JSON:', text);
                throw new Error('Respuesta no es JSON válida');
            }
            await response.json();
            obtenerDisponibilidadMedico();
            fechasLista.setAttribute('data-fechas', '[]');
            renderFechasBloqueo();
            document.getElementById('etiqueta').value = '';
            document.getElementById('recordatorio').value = '';
            document.getElementById('fechas-error').classList.add('hidden');
        } catch (error) {
            console.error('Error al bloquear fechas:', error);
            mostrarError('Error al bloquear fechas: ' + error.message);
        }
    }

    // Confirmar cita
    async function confirmarCita(idCita) {
        try {
            const response = await fetch(`${apiUrl}/reservar-cita/confirmar/${idCita}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({})
            });
            if (!response.ok) {
                const text = await response.text();
                console.error('Error HTTP:', response.status, 'Contenido:', text);
                throw new Error(`Error HTTP: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Respuesta no es JSON:', text);
                throw new Error('Respuesta no es JSON válida');
            }
            await response.json();
            const cita = citasMedico.find(c => c.id_cita === idCita);
            if (cita) {
                cita.estado = 'Confirmada';
                filtrarCitasPorEstado();
                aplicarFiltros();
            }
        } catch (error) {
            console.error('Error al confirmar cita:', error);
            mostrarError('Error al confirmar cita: ' + error.message);
        }
    }

    // Marcar cita como atendida
    async function marcarComoAtendida(idCita) {
        try {
            const response = await fetch(`${apiUrl}/reservar-cita/atendida/${idCita}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({})
            });
            if (!response.ok) {
                const text = await response.text();
                console.error('Error HTTP:', response.status, 'Contenido:', text);
                throw new Error(`Error HTTP: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Respuesta no es JSON:', text);
                throw new Error('Respuesta no es JSON válida');
            }
            await response.json();
            const cita = citasMedico.find(c => c.id_cita === idCita);
            if (cita) {
                cita.estado = 'Atendida';
                filtrarCitasPorEstado();
                aplicarFiltros();
            }
        } catch (error) {
            console.error('Error al marcar cita como atendida:', error);
            mostrarError('Error al marcar cita como atendida: ' + error.message);
        }
    }

    // Generar horas disponibles
    function generarHorasDisponibles(fecha, horaInicio, horaFin, intervalo) {
        const horasDisponibles = [];
        let horaActual = new Date(`2000-01-01T${horaInicio}:00`);
        const horaLimite = new Date(`2000-01-01T${horaFin}:00`);
        while (horaActual < horaLimite) {
            const hora = horaActual.toTimeString().slice(0, 5);
            horasDisponibles.push(hora);
            horaActual.setMinutes(horaActual.getMinutes() + parseInt(intervalo));
        }
        return horasDisponibles;
    }

    // Crear disponibilidad
    async function crearDisponibilidad(event) {
        event.preventDefault();
        const form = document.getElementById('form-disponibilidad');
        const fecha = form.querySelector('#fecha').value;
        const horaInicio = form.querySelector('#horaInicio').value;
        const horaFin = form.querySelector('#horaFin').value;
        const intervalo = form.querySelector('#intervalo').value;
        if (!fecha || !horaInicio || !horaFin || !intervalo) return;
        const horasDisponibles = generarHorasDisponibles(fecha, horaInicio, horaFin, intervalo);
        try {
            const promesas = horasDisponibles.map(hora => {
                const fechaHora = `${fecha}T${hora}:00`;
                return fetch(`${apiUrl}/reservar-cita/disponibilidad`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        id_medico: idMedico,
                        id_agenda: `nuevo_${Math.random().toString(36).substring(2, 11)}`,
                        fecha_hora: fechaHora
                    })
                }).then(async response => {
                    if (!response.ok) {
                        const text = await response.text();
                        console.error('Error HTTP:', response.status, 'Contenido:', text);
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        const text = await response.text();
                        console.error('Respuesta no es JSON:', text);
                        throw new Error('Respuesta no es JSON válida');
                    }
                    return response.json();
                });
            });
            await Promise.all(promesas);
            obtenerDisponibilidadMedico();
        } catch (error) {
            console.error('Error al crear disponibilidad:', error);
            mostrarError('Error al crear disponibilidad: ' + error.message);
        }
    }

    // Cambiar vista de calendario
    function cambiarVistaCalendario(vista) {
        vistaCalendario = vista;
        document.getElementById('vista-diaria').classList.toggle('bg-blue-600', vista === 'diaria');
        document.getElementById('vista-diaria').classList.toggle('text-white', vista === 'diaria');
        document.getElementById('vista-diaria').classList.toggle('bg-gray-200', vista !== 'diaria');
        document.getElementById('vista-diaria').classList.toggle('text-gray-700', vista !== 'diaria');
        document.getElementById('vista-semanal').classList.toggle('bg-blue-600', vista === 'semanal');
        document.getElementById('vista-semanal').classList.toggle('text-white', vista === 'semanal');
        document.getElementById('vista-semanal').classList.toggle('bg-gray-200', vista !== 'semanal');
        document.getElementById('vista-semanal').classList.toggle('text-gray-700', vista !== 'semanal');
        document.getElementById('vista-diaria-section').classList.toggle('hidden', vista !== 'diaria');
        document.getElementById('vista-semanal-section').classList.toggle('hidden', vista !== 'semanal');
        if (vista === 'semanal') {
            generarDiasSemana();
        }
        renderCitas();
    }

    // Semana anterior
    function semanaAnterior() {
        fechaInicioSemana.setDate(fechaInicioSemana.getDate() - 7);
        fechaFinSemana.setDate(fechaFinSemana.getDate() - 7);
        generarDiasSemana();
    }

    // Semana siguiente
    function semanaSiguiente() {
        fechaInicioSemana.setDate(fechaInicioSemana.getDate() + 7);
        fechaFinSemana.setDate(fechaFinSemana.getDate() + 7);
        generarDiasSemana();
    }

    // Generar días de la semana
    function generarDiasSemana() {
        diasSemana = [];
        const fechaInicio = new Date(fechaInicioSemana);
        for (let i = 0; i < 7; i++) {
            const fecha = new Date(fechaInicio);
            fecha.setDate(fechaInicio.getDate() + i);
            const fechaStr = fecha.toISOString().split('T')[0];
            const citasDia = citasMedico.filter(cita => {
                const fechaCita = new Date(cita.fecha_cita).toISOString().split('T')[0];
                return fechaCita === fechaStr;
            });
            diasSemana.push({
                fecha,
                fechaStr,
                citas: citasDia,
                esDisponible: fechasDisponibles.includes(fechaStr),
                esNoDisponible: fechasNoDisponibles.includes(fechaStr)
            });
        }
        renderSemana();
    }

    // Seleccionar fecha
    function seleccionarFecha(fecha) {
        fechaSeleccionada = fecha;
        filtroFecha = fecha;
        aplicarFiltros();
        actualizarFechaTitulo();
    }

    // Actualizar título de fecha
    function actualizarFechaTitulo() {
        const fecha = new Date(fechaSeleccionada);
        document.getElementById('fecha-titulo').textContent = `Citas del día ${fecha.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' })}`;
    }

    // Renderizar citas
    function renderCitas() {
        if (vistaCalendario === 'diaria') {
            const citasDiariaBody = document.getElementById('citas-diaria-body');
            citasDiariaBody.innerHTML = citasFiltradas.map(cita => `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${new Date(cita.fecha_cita).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${cita.paciente || 'N/A'} ${cita.paciente_apellido || ''}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">${cita.motivo || 'Sin motivo'}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium estado-${cita.estado.toLowerCase()}">
                            ${cita.estado}${cita.etiqueta ? ` (${cita.etiqueta})` : ''}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            ${cita.estado === 'Pendiente' ? `<button type="button" onclick="confirmarCita(${cita.id_cita})" class="text-blue-600 hover:text-blue-800">Confirmar</button>` : ''}
                            ${cita.estado === 'Confirmada' ? `<button type="button" onclick="marcarComoAtendida(${cita.id_cita})" class="text-green-600 hover:text-green-800">Atendida</button>` : ''}
                        </div>
                    </td>
                </tr>
            `).join('');
            document.getElementById('no-citas-diaria').classList.toggle('hidden', citasFiltradas.length > 0);
            document.getElementById('citas-diaria-table').classList.toggle('hidden', citasFiltradas.length === 0);
        } else {
            const citasSemanalBody = document.getElementById('citas-semanal-body');
            citasSemanalBody.innerHTML = citasFiltradas.map(cita => `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${new Date(cita.fecha_cita).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' })}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${new Date(cita.fecha_cita).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${cita.paciente || 'N/A'} ${cita.paciente_apellido || ''}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">${cita.motivo || 'Sin motivo'}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium estado-${cita.estado.toLowerCase()}">
                            ${cita.estado}${cita.etiqueta ? ` (${cita.etiqueta})` : ''}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            ${cita.estado === 'Pendiente' ? `<button type="button" onclick="confirmarCita(${cita.id_cita})" class="text-blue-600 hover:text-blue-800">Confirmar</button>` : ''}
                            ${cita.estado === 'Confirmada' ? `<button type="button" onclick="marcarComoAtendida(${cita.id_cita})" class="text-green-600 hover:text-green-800">Atendida</button>` : ''}
                        </div>
                    </td>
                </tr>
            `).join('');
            document.getElementById('no-citas-semanal').classList.toggle('hidden', citasFiltradas.length > 0);
            document.getElementById('citas-semanal-table').classList.toggle('hidden', citasFiltradas.length === 0);
        }
    }

    // Renderizar semana
    function renderSemana() {
        document.getElementById('semana-titulo').textContent = `Semana: ${fechaInicioSemana.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' })} - ${fechaFinSemana.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' })}`;
        const diasSemanaContainer = document.getElementById('dias-semana');
        diasSemanaContainer.innerHTML = diasSemana.map(dia => `
            <div class="bg-white border rounded-lg shadow-sm ${dia.esDisponible ? 'border-blue-500' : dia.esNoDisponible ? 'border-red-500' : ''} ${dia.fechaStr === fechaSeleccionada ? 'bg-blue-50' : ''}">
                <div class="text-center p-2 ${dia.fechaStr === fechaSeleccionada ? 'bg-blue-600 text-white' : 'bg-gray-100'}">
                    <div class="font-semibold">${dia.fecha.toLocaleDateString('es-ES', { weekday: 'short' }).toUpperCase()}</div>
                    <div>${dia.fecha.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' })}</div>
                </div>
                <div class="p-2 text-center">
                    ${dia.citas.length === 0 ? '<div class="text-gray-500 text-sm">Sin citas</div>' : `
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                            ${dia.citas.length} citas
                        </span>
                    `}
                </div>
                <div class="p-2 text-center">
                    <button type="button" onclick="seleccionarFecha('${dia.fechaStr}')" class="text-blue-600 hover:text-blue-800 text-sm">Ver</button>
                </div>
            </div>
        `).join('');
    }

    // Establecer fecha en el formulario de disponibilidad
    function setFechaDisponibilidad() {
        document.getElementById('fecha').value = fechaSeleccionada;
    }
</script>
</body>
</html>