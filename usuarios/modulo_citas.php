<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}   

// Verificar si la sesión está iniciada y si el ID de usuario está disponible
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['id_usuario'])) {
    $user_id = $_SESSION['id_usuario'];
} else {
    header('Location: login_usuario.php');
    exit;
}

require 'conexion.php';

// Obtener información del usuario incluyendo la foto
$user_id = $_SESSION['id_usuario'];
$stmt = $conexion->prepare("SELECT nombre, apellido, correo, foto FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($nombre, $apellido, $correo, $foto);
$stmt->fetch();
$stmt->close();

// Simulación de AuthService
class AuthService {
    public static function getSession() {
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
            return [
                'id_usuario' => $_SESSION['id_usuario'],
                'nombre' => $_SESSION['nombre'],
                'apellido' => $_SESSION['apellido'],
                'correo' => $_SESSION['correo']
            ];
        }
        return null;
    }
}

// PHP Backend Logic
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
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    }
    return ['error' => 'Error en la solicitud a la API'];
}
?>





<?php
ob_start(); // Iniciar búfer de salida para evitar salida no deseada

// Verificar si el usuario está autenticado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}



// Inicialización de variables
$currentUser = AuthService::getSession();
$userHistory = [];
$selectedHistoryItem = null;
$notification = null;
$isLoadingHistory = false;
$predictionResult = null;
$errors = [];
$formData = [];

// Funciones auxiliares
function makeHttpRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data))
        ]);
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    }
    throw new Exception("Error en la solicitud HTTP: Código $httpCode");
}



function loadUserHistory($userId, &$userHistory, &$selectedHistoryItem) {
    global $API_URL_BIOMETRIC;
    try {
        $data = makeHttpRequest("$API_URL_BIOMETRIC/usuario/$userId");
        $userHistory = $data;
        usort($userHistory, function($a, $b) {
            $dateA = $a['fecha_registro'] ? strtotime($a['fecha_registro']) : 0;
            $dateB = $b['fecha_registro'] ? strtotime($b['fecha_registro']) : 0;
            return $dateB - $dateA;
        });
        if (!empty($userHistory)) {
            $selectId = isset($_GET['select']) ? (int)$_GET['select'] : null;
            foreach ($userHistory as $item) {
                if ($selectId && $item['id_dato'] === $selectId) {
                    $selectedHistoryItem = $item;
                    break;
                }
            }
            if (!$selectedHistoryItem) {
                $selectedHistoryItem = $userHistory[0];
            }
        }
    } catch (Exception $e) {
        return "Error al cargar el historial: " . $e->getMessage();
    }
    return null;
}

// Cargar historial al inicio
if ($currentUser && isset($currentUser['id_usuario'])) {
    $isLoadingHistory = true;
    $error = loadUserHistory($currentUser['id_usuario'], $userHistory, $selectedHistoryItem);
    if ($error) {
        $notification = ['message' => $error, 'type' => 'error'];
    }
    $isLoadingHistory = false;
} else {
    header("Location: login.php");
    exit;
}

?>








<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Citas Médicas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="estilos_inicio.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .estado-pendiente { background-color: #fefcbf; color: #b7791f; }
        .estado-confirmada { background-color: #c6f6d5; color: #2f855a; }
        .estado-cancelada { background-color: #fed7d7; color: #c53030; }
        .estado-atendida { background-color: #bee3f8; color: #2b6cb0; }
        @media (min-width: 1024px) {
            .container.main-content-area {
                margin-left: 270px !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <?php include 'header_usuarios.php'; ?>

    <div class="container main-content-area mx-auto px-4 py-8 max-w-7xl">
        <!-- Navigation Tabs -->
        <nav class="bg-white shadow-sm rounded-lg mb-6 overflow-hidden">
            <div class="flex border-b border-gray-200">
                <button id="tab-reservar" class="flex-1 px-6 py-4 font-medium text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition active-tab" onclick="changeTab('reservar')">
                    <i class="fas fa-calendar-plus mr-2"></i> Reservar Cita
                </button>
                <button id="tab-misCitas" class="flex-1 px-6 py-4 font-medium text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition" onclick="changeTab('misCitas')">
                    <i class="fas fa-list-alt mr-2"></i> Mis Citas
                </button>
            </div>
        </nav>

        <main class="bg-white rounded-lg shadow-md p-6">
            <!-- Error Message -->
            <div id="error-message" class="hidden bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded flex items-center">
                <i class="fas fa-exclamation-circle mr-3"></i>
                <span id="error-text"></span>
            </div>

            <!-- Loading Indicator -->
            <div id="loading" class="hidden flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-blue-600"></div>
                <span class="ml-4 text-gray-600 font-medium">Cargando...</span>
            </div>

            <!-- Reservar Cita Section -->
            <div id="reservar-section" class="space-y-8">
                <!-- Doctor List -->
                <div id="doctor-list" class="hidden">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Seleccione un Médico</h2>
                    <div id="doctors-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"></div>
                </div>

                <!-- Selected Doctor -->
                <div id="doctor-selected" class="hidden bg-blue-50 p-6 rounded-lg flex flex-col sm:flex-row gap-6">
                    <div class="flex-shrink-0" id="doctor-photo"></div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h2 id="doctor-name" class="text-2xl font-bold text-gray-800"></h2>
                            <button onclick="showDoctorList()" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                                <i class="fas fa-exchange-alt mr-1"></i> Cambiar médico
                            </button>
                        </div>
                        <p id="doctor-specialty" class="text-blue-600 font-medium mt-1"></p>
                        <p id="doctor-address" class="text-gray-600 mt-2 flex items-center">
                            <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                        </p>
                    </div>
                </div>

                <!-- Date Selector -->
                <div id="date-selector" class="hidden space-y-4">
                    <h2 class="text-xl font-semibold text-gray-800">Seleccione una Fecha</h2>
                    <input type="date" id="selected-date" class="w-full sm:w-64 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="onDateChange()">
                </div>

                <!-- Schedules -->
                <div id="schedules-section" class="hidden space-y-4">
                    <h2 class="text-xl font-semibold text-gray-800">Horarios Disponibles</h2>
                    <div id="schedules-container" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3"></div>
                </div>

                <!-- Appointment Form -->
                <form id="appointment-form" class="hidden space-y-6" onsubmit="submitAppointment(event)">
                    <div>
                        <label for="motivo" class="block text-gray-800 font-medium mb-2">Motivo de la Consulta</label>
                        <textarea id="motivo" name="motivo" rows="4" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-y" placeholder="Describa el motivo de su consulta"></textarea>
                    </div>
                    <div class="flex justify-end gap-4">
                        <button type="button" onclick="resetForm()" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition flex items-center">
                            <i class="fas fa-times mr-2"></i> Cancelar
                        </button>
                        <button type="submit" id="submit-button" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-check mr-2"></i> Confirmar Reserva
                        </button>
                    </div>
                </form>
            </div>

            <!-- Mis Citas Section -->
            <div id="misCitas-section" class="hidden space-y-6">
                <h2 class="text-2xl font-semibold text-gray-800">Mis Citas Médicas</h2>
                <div id="no-appointments" class="hidden text-center py-12 bg-gray-50 rounded-lg">
                    <i class="fas fa-calendar-times text-5xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600 mb-4">No tiene citas programadas</p>
                    <button onclick="changeTab('reservar')" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center mx-auto">
                        <i class="fas fa-plus mr-2"></i> Reservar una Cita
                    </button>
                </div>
                <div id="appointments-table" class="overflow-x-auto hidden">
                    <table class="w-full bg-white rounded-lg shadow-sm">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="py-4 px-6 text-left font-semibold">Médico</th>
                                <th class="py-4 px-6 text-left font-semibold">Fecha y Hora</th>
                                <th class="py-4 px-6 text-left font-semibold">Motivo</th>
                                <th class="py-4 px-6 text-left font-semibold">Estado</th>
                                <th class="py-4 px-6 text-center font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="appointments-body" class="divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        const apiUrl = 'http://localhost:5000/api';
        let doctor = null;
        let doctors = [];
        let schedules = [];
        let selectedSchedule = '';
        let motivo = '';
        let selectedDate = '';
        const today = new Date().toISOString().split('T')[0];
        let misCitas = [];
        let activeTab = 'reservar';
        const userId = <?php echo json_encode($user_id); ?>; // Obtener ID de usuario desde la sesión

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            if (userId === null) {
                showError('No se pudo iniciar la sesión. Por favor, inicia sesión para continuar.');
                return;
            }
            const urlParams = new URLSearchParams(window.location.search);
            const id_medico = urlParams.get('id_medico');
            if (id_medico) {
                loadDoctor(id_medico);
            } else {
                showDoctorList();
            }
            consultarMisCitas();
            document.getElementById('selected-date').setAttribute('min', today);
        });

        // Tab Switching
        function changeTab(tab) {
            activeTab = tab;
            document.getElementById('tab-reservar').classList.toggle('bg-blue-50', tab === 'reservar');
            document.getElementById('tab-reservar').classList.toggle('text-blue-600', tab === 'reservar');
            document.getElementById('tab-misCitas').classList.toggle('bg-blue-50', tab === 'misCitas');
            document.getElementById('tab-misCitas').classList.toggle('text-blue-600', tab === 'misCitas');
            document.getElementById('reservar-section').classList.toggle('hidden', tab !== 'reservar');
            document.getElementById('misCitas-section').classList.toggle('hidden', tab !== 'misCitas');
            if (tab === 'misCitas') {
                consultarMisCitas();
            }
        }

        // Error Handling
        function showError(message) {
            const errorDiv = document.getElementById('error-message');
            document.getElementById('error-text').textContent = message;
            errorDiv.classList.remove('hidden');
        }

        function clearError() {
            document.getElementById('error-message').classList.add('hidden');
        }

        // Loading State
        function setLoading(isLoading) {
            document.getElementById('loading').classList.toggle('hidden', !isLoading);
        }

        // Load Doctors
        async function loadDoctors() {
            setLoading(true);
            try {
                const response = await fetch(`${apiUrl}/especialistas/doctors`);
                if (!response.ok) throw new Error('Error al cargar la lista de médicos');
                doctors = await response.json();
                const container = document.getElementById('doctors-container');
                container.innerHTML = doctors.map(doc => `
                    <div class="bg-white border rounded-lg shadow-sm hover:shadow-lg transition cursor-pointer" data-id="${doc.id_medico}">
                        <div class="flex p-4 items-center">
                            <div class="flex-shrink-0">
                                ${doc.foto ? `<img src="http://localhost:5000/Uploads/medicos/${doc.foto.split('/').pop()}" alt="${doc.nombre} ${doc.apellido}" class="w-16 h-16 rounded-full object-cover border-2 border-blue-100">` : 
                                    `<div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center text-gray-400"><i class="fas fa-user-md text-2xl"></i></div>`}
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">${doc.nombre} ${doc.apellido}</h3>
                                <p class="text-blue-600 font-medium">${doc.nombre_especialidad}</p>
                                <p class="text-sm text-gray-600 mt-1 flex items-center">
                                    <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                                    ${doc.direccion_consultorio || 'Sin dirección'}
                                </p>
                            </div>
                        </div>
                    </div>
                `).join('');
                // Asignar eventos a los divs de médicos
                document.querySelectorAll('#doctors-container > div').forEach(div => {
                    div.addEventListener('click', () => {
                        const id_medico = div.getAttribute('data-id');
                        selectDoctor(id_medico);
                    });
                });
                document.getElementById('doctor-list').classList.remove('hidden');
            } catch (err) {
                showError('Error al cargar la lista de médicos');
            } finally {
                setLoading(false);
            }
        }

        // Load Doctor
        async function loadDoctor(id) {
            setLoading(true);
            try {
                const response = await fetch(`${apiUrl}/reservar-cita/doctor/${id}`);
                if (!response.ok) throw new Error('Error al cargar los detalles del médico');
                doctor = await response.json();
                document.getElementById('doctor-name').textContent = `${doctor.nombre} ${doctor.apellido}`;
                document.getElementById('doctor-specialty').textContent = doctor.nombre_especialidad;
                document.getElementById('doctor-address').innerHTML = `<i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>${doctor.direccion_consultorio || 'Sin dirección'}`;
                document.getElementById('doctor-photo').innerHTML = doctor.foto ? 
                    `<img src="http://localhost:5000/Uploads/medicos/${doctor.foto.split('/').pop()}" alt="${doctor.nombre} ${doctor.apellido}" class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-md">` :
                    `<div class="w-24 h-24 rounded-full bg-gray-100 flex items-center justify-center text-gray-400"><i class="fas fa-user-md text-4xl"></i></div>`;
                document.getElementById('doctor-selected').classList.remove('hidden');
                document.getElementById('date-selector').classList.remove('hidden');
                document.getElementById('doctor-list').classList.add('hidden');
            } catch (err) {
                showError('Error al cargar los detalles del médico');
            } finally {
                setLoading(false);
            }
        }

        // Select Doctor
        async function selectDoctor(id_medico) {
            setLoading(true);
            try {
                const response = await fetch(`${apiUrl}/reservar-cita/doctor/${id_medico}`);
                if (!response.ok) throw new Error('Error al cargar los detalles del médico');
                doctor = await response.json();
                document.getElementById('doctor-name').textContent = `${doctor.nombre} ${doctor.apellido}`;
                document.getElementById('doctor-specialty').textContent = doctor.nombre_especialidad;
                document.getElementById('doctor-address').innerHTML = `<i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>${doctor.direccion_consultorio || 'Sin dirección'}`;
                document.getElementById('doctor-photo').innerHTML = doctor.foto ? 
                    `<img src="http://localhost:5000/Uploads/medicos/${doctor.foto.split('/').pop()}" alt="${doctor.nombre} ${doctor.apellido}" class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-md">` :
                    `<div class="w-24 h-24 rounded-full bg-gray-100 flex items-center justify-center text-gray-400"><i class="fas fa-user-md text-4xl"></i></div>`;
                document.getElementById('doctor-selected').classList.remove('hidden');
                document.getElementById('date-selector').classList.remove('hidden');
                document.getElementById('doctor-list').classList.add('hidden');
            } catch (err) {
                showError('Error al cargar los detalles del médico');
            } finally {
                setLoading(false);
            }
        }

        // Show Doctor List
        function showDoctorList() {
            doctor = null;
            selectedDate = '';
            schedules = [];
            selectedSchedule = '';
            motivo = '';
            document.getElementById('selected-date').value = '';
            document.getElementById('schedules-container').innerHTML = '';
            document.getElementById('motivo').value = '';
            document.getElementById('doctor-selected').classList.add('hidden');
            document.getElementById('date-selector').classList.add('hidden');
            document.getElementById('schedules-section').classList.add('hidden');
            document.getElementById('appointment-form').classList.add('hidden');
            loadDoctors();
        }

        // Date Change
        async function onDateChange() {
            selectedDate = document.getElementById('selected-date').value;
            if (selectedDate < today) {
                showError('No puedes seleccionar una fecha pasada');
                schedules = [];
                selectedSchedule = '';
                document.getElementById('schedules-container').innerHTML = '';
                document.getElementById('schedules-section').classList.add('hidden');
                document.getElementById('appointment-form').classList.add('hidden');
                return;
            }
            setLoading(true);
            try {
                const response = await fetch(`${apiUrl}/reservar-cita/doctor/${doctor.id_medico}/schedules?fecha=${selectedDate}`);
                if (!response.ok) throw new Error('Error al cargar los horarios disponibles');
                schedules = await response.json();
                selectedSchedule = '';
                document.getElementById('schedules-container').innerHTML = schedules.length === 0 ? 
                    `<div class="col-span-full text-center py-8 bg-gray-50 rounded-lg">
                        <i class="fas fa-calendar-times text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-600">No hay horarios disponibles para este día</p>
                    </div>` :
                    schedules.map(schedule => `
                        <button type="button" class="${selectedSchedule === schedule.id_agenda.toString() ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-800'} p-3 rounded-lg text-center transition hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500" onclick="selectSchedule(${schedule.id_agenda})">
                            <span class="block font-medium">${new Date(schedule.fecha_hora).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })}</span>
                            <span class="text-xs ${selectedSchedule === schedule.id_agenda.toString() ? 'text-white' : 'text-blue-600'}">Disponible</span>
                        </button>
                    `).join('');
                document.getElementById('schedules-section').classList.remove('hidden');
                document.getElementById('appointment-form').classList.remove('hidden');
                clearError();
            } catch (err) {
                showError('Error al cargar los horarios disponibles');
                schedules = [];
                document.getElementById('schedules-container').innerHTML = '';
            } finally {
                setLoading(false);
            }
        }

        // Select Schedule
        function selectSchedule(id_agenda) {
            selectedSchedule = id_agenda.toString();
            document.querySelectorAll('#schedules-container button').forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-blue-100', 'text-blue-800');
                if (btn.onclick.toString().includes(id_agenda)) {
                    btn.classList.remove('bg-blue-100', 'text-blue-800');
                    btn.classList.add('bg-blue-600', 'text-white');
                    btn.querySelector('span.text-xs').classList.remove('text-blue-600');
                    btn.querySelector('span.text-xs').classList.add('text-white');
                }
            });
            document.getElementById('submit-button').disabled = !selectedSchedule || !document.getElementById('motivo').value;
        }

        // Submit Appointment
        async function submitAppointment(event) {
            event.preventDefault();
            motivo = document.getElementById('motivo').value;
            if (!doctor || !selectedSchedule || !motivo) {
                showError('Por favor, seleccione un horario y proporcione un motivo');
                return;
            }
            setLoading(true);
            try {
                const request = {
                    id_usuario: userId,
                    id_medico: doctor.id_medico,
                    id_agenda: selectedSchedule,
                    motivo: motivo,
                    fecha_hora: schedules.find(s => s.id_agenda.toString() === selectedSchedule)?.fecha_hora
                };
                const response = await fetch(`${apiUrl}/reservar-cita/book`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(request)
                });
                if (!response.ok) throw new Error('Error al reservar la cita');
                const data = await response.json();
                alert(`Cita reservada con éxito. ID de cita: ${data.id_cita}`);
                resetForm();
                consultarMisCitas();
                changeTab('misCitas');
            } catch (err) {
                showError(err.message || 'Error al reservar la cita');
            } finally {
                setLoading(false);
            }
        }

        // Reset Form
        function resetForm() {
            selectedDate = '';
            selectedSchedule = '';
            motivo = '';
            schedules = [];
            document.getElementById('selected-date').value = '';
            document.getElementById('schedules-container').innerHTML = '';
            document.getElementById('motivo').value = '';
            document.getElementById('schedules-section').classList.add('hidden');
            document.getElementById('appointment-form').classList.add('hidden');
            clearError();
        }

        // Consultar Mis Citas
        async function consultarMisCitas() {
            if (userId === null) {
                showError('No se pudo cargar las citas. Por favor, inicia sesión.');
                return;
            }
            setLoading(true);
            try {
                const response = await fetch(`${apiUrl}/reservar-cita/user/${userId}/citas`);
                if (!response.ok) throw new Error('Error al cargar las citas');
                misCitas = await response.json();
                const tableBody = document.getElementById('appointments-body');
                tableBody.innerHTML = misCitas.map(cita => `
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-4 px-6">Dr. ${cita.medico} ${cita.medico_apellido}</td>
                        <td class="py-4 px-6">${formatFecha(cita.fecha_cita)}</td>
                        <td class="py-4 px-6">${cita.motivo}</td>
                        <td class="py-4 px-6">
                            <span class="px-3 py-1 rounded-full text-sm font-medium ${getEstadoClass(cita.estado)}">${cita.estado}</span>
                        </td>
                        <td class="py-4 px-6 text-center space-x-3">
                            ${(cita.estado === 'Pendiente' || cita.estado === 'Confirmada') ? 
                                `<button onclick="cancelarCita(${cita.id_cita})" class="text-red-600 hover:text-red-800 transition" title="Cancelar cita"><span class="bg-red-100 text-red-800">Cancelar</span></button>` : ''}
                        </td>
                    </tr>
                `).join('');
                document.getElementById('no-appointments').classList.toggle('hidden', misCitas.length > 0);
                document.getElementById('appointments-table').classList.toggle('hidden', misCitas.length === 0);
            } catch (err) {
                showError('Error al cargar las citas');
            } finally {
                setLoading(false);
            }
        }

        // Cancel Appointment
        async function cancelarCita(citaId) {
            if (!confirm('¿Está seguro de cancelar esta cita?')) return;
            setLoading(true);
            try {
                const response = await fetch(`${apiUrl}/reservar-cita/cancelar/${citaId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({})
                });
                if (!response.ok) throw new Error('Error al cancelar la cita');
                alert('Cita cancelada con éxito');
                consultarMisCitas();
            } catch (err) {
                showError('Error al cancelar la cita');
            } finally {
                setLoading(false);
            }
        }

        // Format Date
        function formatFecha(fecha) {
            return new Date(fecha).toLocaleString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Get Estado Class
        function getEstadoClass(estado) {
            switch (estado.toLowerCase()) {
                case 'pendiente': return 'estado-pendiente';
                case 'confirmada': return 'estado-confirmada';
                case 'cancelada': return 'estado-cancelada';
                case 'atendida': return 'estado-atendida';
                default: return 'bg-gray-100 text-gray-800';
            }
        }
    </script>
    <?php include 'footer_usuario.php'; ?>
</body>
</html>