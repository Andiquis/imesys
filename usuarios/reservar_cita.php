<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: login_usuario.php");
    exit;
}

require 'conexion.php';

$id_medico = isset($_GET['id_medico']) ? intval($_GET['id_medico']) : 0;
$id_usuario = $_SESSION['id_usuario'];

// Obtener información del usuario
$stmt_usuario = $conexion->prepare("SELECT nombre, apellido FROM usuarios WHERE id_usuario = ?");
$stmt_usuario->bind_param("i", $id_usuario);
$stmt_usuario->execute();
$usuario = $stmt_usuario->get_result()->fetch_assoc();
$stmt_usuario->close();

// Procesar búsqueda de médicos
$medicos = [];
if (isset($_GET['busqueda_medico'])) {
    $busqueda = "%" . trim($_GET['busqueda_medico']) . "%";
    $stmt_busqueda = $conexion->prepare("
        SELECT m.id_medico, m.nombre, m.apellido, m.foto, e.nombre_especialidad 
        FROM medicos m
        JOIN especialidades e ON m.id_especialidad = e.id_especialidad
        WHERE CONCAT(m.nombre, ' ', m.apellido) LIKE ? OR e.nombre_especialidad LIKE ?
        ORDER BY m.nombre, m.apellido
        LIMIT 10
    ");
    $stmt_busqueda->bind_param("ss", $busqueda, $busqueda);
    $stmt_busqueda->execute();
    $medicos = $stmt_busqueda->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_busqueda->close();
}

// Si se seleccionó un médico, obtener su información
$medico_seleccionado = null;
if ($id_medico > 0) {
    $stmt_medico = $conexion->prepare("
        SELECT m.id_medico, m.nombre, m.apellido, m.foto, m.direccion_consultorio, 
               e.nombre_especialidad 
        FROM medicos m
        JOIN especialidades e ON m.id_especialidad = e.id_especialidad
        WHERE m.id_medico = ?
    ");
    $stmt_medico->bind_param("i", $id_medico);
    $stmt_medico->execute();
    $medico_seleccionado = $stmt_medico->get_result()->fetch_assoc();
    $stmt_medico->close();
}

// Obtener fechas disponibles y no disponibles
$fechas_disponibles = [];
$fechas_no_disponibles = [];
if ($medico_seleccionado) {
    $fecha_actual = date('Y-m-d');
    
    // Fechas con disponibilidad
    $stmt_disponibles = $conexion->prepare("
        SELECT DISTINCT DATE(fecha_hora) as fecha
        FROM agenda_medico 
        WHERE id_medico = ? 
        AND fecha_hora >= ?
        AND estado = 'Disponible'
        ORDER BY fecha
    ");
    $stmt_disponibles->bind_param("is", $id_medico, $fecha_actual);
    $stmt_disponibles->execute();
    $result = $stmt_disponibles->get_result();
    while ($row = $result->fetch_assoc()) {
        $fechas_disponibles[] = $row['fecha'];
    }
    $stmt_disponibles->close();
    
    // Fechas sin disponibilidad
    $stmt_no_disponibles = $conexion->prepare("
        SELECT DISTINCT DATE(fecha_hora) as fecha
        FROM agenda_medico 
        WHERE id_medico = ? 
        AND fecha_hora >= ?
        AND estado = 'No disponible'
        ORDER BY fecha
    ");
    $stmt_no_disponibles->bind_param("is", $id_medico, $fecha_actual);
    $stmt_no_disponibles->execute();
    $result = $stmt_no_disponibles->get_result();
    while ($row = $result->fetch_assoc()) {
        $fechas_no_disponibles[] = $row['fecha'];
    }
    $stmt_no_disponibles->close();
}

// Procesar el formulario de reserva
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $motivo = trim($_POST['motivo']);
    $id_agenda = $_POST['id_agenda'];
    
    // Manejar horarios nuevos (no existentes en la agenda)
    if (strpos($id_agenda, 'nuevo_') === 0) {
        $fecha_hora = substr($id_agenda, 6);
        
        // Insertar nuevo horario en agenda
        $stmt_agenda = $conexion->prepare("
            INSERT INTO agenda_medico (id_medico, fecha_hora, estado)
            VALUES (?, ?, 'No disponible')
        ");
        $stmt_agenda->bind_param("is", $id_medico, $fecha_hora);
        $stmt_agenda->execute();
        $id_agenda = $conexion->insert_id;
        $stmt_agenda->close();
    } else {
        $id_agenda = intval($id_agenda);
        
        // Verificar que el horario siga disponible
        $stmt_verificar = $conexion->prepare("
            SELECT id_agenda FROM agenda_medico 
            WHERE id_agenda = ? AND estado = 'Disponible'
        ");
        $stmt_verificar->bind_param("i", $id_agenda);
        $stmt_verificar->execute();
        $disponible = $stmt_verificar->get_result()->fetch_assoc();
        $stmt_verificar->close();
        
        if (!$disponible) {
            $error = "El horario seleccionado ya no está disponible. Por favor, elige otro.";
        }
    }
    
    if (!isset($error)) {
        // Insertar la cita
        $stmt_insert = $conexion->prepare("
            INSERT INTO citas (id_usuario, id_medico, fecha_cita, estado, motivo)
            SELECT ?, id_medico, fecha_hora, 'Pendiente', ?
            FROM agenda_medico
            WHERE id_agenda = ?
        ");
        $stmt_insert->bind_param("isi", $id_usuario, $motivo, $id_agenda);
        $stmt_insert->execute();
        $id_cita = $conexion->insert_id;
        $stmt_insert->close();
        
        // Actualizar el horario como no disponible
        $stmt_update = $conexion->prepare("
            UPDATE agenda_medico SET estado = 'No disponible' WHERE id_agenda = ?
        ");
        $stmt_update->bind_param("i", $id_agenda);
        $stmt_update->execute();
        $stmt_update->close();
        
        // Redirigir a confirmación
        header("Location: confirmacion_cita.php?id=" . $id_cita);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Reservar Cita</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <style>
        .fc-day-disponible {
            background-color: #e6ffed !important;
            cursor: pointer;
            position: relative;
        }
        .fc-day-disponible:hover {
            background-color: #c1f0d1 !important;
        }
        .fc-day-disponible::after {
            content: '';
            position: absolute;
            bottom: 2px;
            left: 50%;
            transform: translateX(-50%);
            width: 6px;
            height: 6px;
            background-color: #28a745;
            border-radius: 50%;
        }
        .fc-day-nodisponible {
            background-color: #ffebee !important;
            cursor: not-allowed;
        }
        .fc-day-nodisponible::after {
            content: '';
            position: absolute;
            bottom: 2px;
            left: 50%;
            transform: translateX(-50%);
            width: 6px;
            height: 6px;
            background-color: #dc3545;
            border-radius: 50%;
        }
        .fc-day-past {
            background-color: #f5f5f5 !important;
            cursor: not-allowed;
            color: #999 !important;
        }
        .fc-day-sinregistro {
            background-color:rgb(205, 255, 209) !important;
            cursor: not-allowed;
        }
        .fc-day-sinregistro::after {
            content: '';
            position: absolute;
            bottom: 2px;
            left: 50%;
            transform: translateX(-50%);
            width: 6px;
            height: 6px;
            background-color:rgb(85, 255, 7);
            border-radius: 50%;
        }
        #horarios-container {
            transition: all 0.3s ease;
            display: none;
        }
    </style>
</head>
<body class="bg-gray-100">
    

    <!-- Contenido principal -->
    <div id="contentArea" class="content-area pt-20">
        <div class="container mx-auto px-4 py-8 max-w-4xl">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 text-white">
                    <h1 class="text-2xl font-bold">Reservar Cita Médica</h1>
                    <p class="mt-2">Complete los datos para agendar su cita</p>
                </div>
                
                <div class="p-6">
                    <!-- Búsqueda de médicos (si no hay médico seleccionado) -->
                    <?php if (!$medico_seleccionado): ?>
                        <div class="mb-8">
                            <h2 class="text-xl font-semibold text-gray-800 mb-4">Buscar médico:</h2>
                            <form method="GET" action="reservar_cita.php" class="flex gap-2">
                                <input type="text" name="busqueda_medico" placeholder="Nombre o especialidad" 
                                       value="<?= isset($_GET['busqueda_medico']) ? htmlspecialchars($_GET['busqueda_medico']) : '' ?>"
                                       class="flex-grow rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </form>
                            
                            <?php if (!empty($medicos)): ?>
                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <?php foreach ($medicos as $medico): ?>
                                        <a href="reservar_cita.php?id_medico=<?= $medico['id_medico'] ?>" 
                                           class="bg-white border border-gray-200 rounded-lg p-4 hover:bg-blue-50 transition flex items-center gap-3">
                                            <?php if ($medico['foto']): ?>
                                                <img src="uploads/medicos/<?= htmlspecialchars($medico['foto']) ?>" 
                                                     alt="<?= htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']) ?>" 
                                                     class="w-12 h-12 rounded-full object-cover">
                                            <?php else: ?>
                                                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-400">
                                                    <i class="fas fa-user-md"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']) ?></h3>
                                                <p class="text-sm text-blue-600"><?= htmlspecialchars($medico['nombre_especialidad']) ?></p>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif (isset($_GET['busqueda_medico'])): ?>
                                <div class="mt-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4">
                                    <p>No se encontraron médicos con ese criterio de búsqueda.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Información del médico seleccionado -->
                    <?php if ($medico_seleccionado): ?>
                        <div class="flex flex-col md:flex-row gap-6 mb-8 p-4 bg-blue-50 rounded-lg">
    <div class="flex-shrink-0">
        <?php 
        // Definir la URL base del sitio
        $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/proyectoimesys/';
        
        $ruta_foto = '';
        $imagen_existe = false;

        if (!empty($medico_seleccionado['foto'])) {
            // Asegurar que sea solo el nombre del archivo
            $nombre_archivo = basename($medico_seleccionado['foto']);
            $ruta_foto = $base_url . 'uploads/medicos/' . $nombre_archivo;
            $ruta_absoluta = $_SERVER['DOCUMENT_ROOT'] . '/proyectoimesys/uploads/medicos/' . $nombre_archivo;
            $imagen_existe = file_exists($ruta_absoluta);
        }
        ?>

        <?php if (!empty($medico_seleccionado['foto']) && $imagen_existe): ?>
            <img src="<?= $ruta_foto ?>" 
                 alt="<?= htmlspecialchars($medico_seleccionado['nombre'] . ' ' . $medico_seleccionado['apellido']) ?>" 
                 class="w-24 h-24 rounded-full object-cover border-4 border-white shadow">
        <?php else: ?>
            <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center text-4xl text-gray-400 border-4 border-white shadow">
                <i class="fas fa-user-md"></i>
            </div>
        <?php endif; ?>
    </div>

                            <div>
                                <h2 class="text-xl font-bold text-gray-800">
                                    <?= htmlspecialchars($medico_seleccionado['nombre'] . ' ' . $medico_seleccionado['apellido']) ?>
                                    <a href="reservar_cita.php" class="text-sm font-normal text-blue-600 hover:text-blue-800 ml-2">
                                        <i class="fas fa-times"></i> Cambiar médico
                                    </a>
                                </h2>
                                <p class="text-blue-600 font-medium">
                                    <?= htmlspecialchars($medico_seleccionado['nombre_especialidad']) ?>
                                </p>
                                <p class="text-gray-600 mt-2">
                                    <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                                    <?= htmlspecialchars($medico_seleccionado['direccion_consultorio']) ?>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Calendario de disponibilidad -->
                        <div class="mb-8">
                            <h2 class="text-xl font-semibold text-gray-800 mb-4">Seleccione una fecha disponible:</h2>
                            <div id="calendar" class="mb-4"></div>
                            <div id="fecha-seleccionada" class="text-center font-medium text-blue-600"></div>
                        </div>
                        
                        <!-- Horarios disponibles -->
                        <div id="horarios-container" class="mb-6">
                            <h2 class="text-xl font-semibold text-gray-800 mb-4">Horarios disponibles:</h2>
                            <div id="horarios-disponibles" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3"></div>
                        </div>
                        
                        <!-- Formulario de reserva -->
                        <form method="POST" id="form-reserva">
                            <input type="hidden" id="id_agenda" name="id_agenda" required>
                            
                            <div class="mb-6">
                                <label for="motivo" class="block text-gray-700 font-medium mb-2">Motivo de la consulta:</label>
                                <textarea id="motivo" name="motivo" rows="3" required
                                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm"
                                          placeholder="Describa brevemente el motivo de su consulta"></textarea>
                            </div>
                            
                            <div class="flex justify-end gap-4">
                                <a href="<?= $id_medico ? 'inicio_imesys.php?id_medico='.$id_medico : 'inicio_imesys.php' ?>" 
                                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-medium transition">
                                    Cancelar
                                </a>
                                
                                <button type="submit" id="btn-reservar" disabled
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                                    Confirmar Reserva
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container mx-auto max-w-4xl px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <img src="img/logo.png" alt="Logo IMESYS" class="h-10">
                    <p class="mt-2 text-sm">Sistema de salud inteligente</p>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-sm">© 2023 IMESYS. Todos los derechos reservados.</p>
                    <p class="text-sm mt-1">contacto@imesys.com | Tel: +1 234 567 890</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.min.js'></script>
    <script>
        // Manejo del sidebar
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menuToggle');
        const overlay = document.getElementById('overlay');
        const menuItems = document.querySelectorAll('.menu-item');
        
        let menuOpen = false;
        
        function toggleMenu() {
            menuOpen = !menuOpen;
            
            if (menuOpen) {
                sidebar.classList.add('open');
                overlay.classList.add('show');
                menuToggle.classList.add('rotated');
            } else {
                sidebar.classList.remove('open');
                overlay.classList.remove('show');
                menuToggle.classList.remove('rotated');
            }
        }
        
        menuToggle.addEventListener('click', toggleMenu);
        
        // Configuración del calendario
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const fechaSeleccionadaEl = document.getElementById('fecha-seleccionada');
            const horariosContainer = document.getElementById('horarios-container');
            const horariosDisponibles = document.getElementById('horarios-disponibles');
            const idAgendaInput = document.getElementById('id_agenda');
            const btnReservar = document.getElementById('btn-reservar');
            
            // Datos desde PHP
            const fechasDisponibles = <?= json_encode($fechas_disponibles) ?>;
            const fechasNoDisponibles = <?= json_encode($fechas_no_disponibles) ?>;
            const idMedico = <?= $id_medico ?>;
            
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth'
                },
                dayCellDidMount: function(arg) {
                    const dateStr = arg.date.toISOString().split('T')[0];
                    const isPast = arg.date < new Date(new Date().setHours(0,0,0,0));
                    
                    if (isPast) {
                        arg.el.classList.add('fc-day-past');
                        arg.el.title = "Fecha pasada";
                    } else if (fechasDisponibles.includes(dateStr)) {
                        arg.el.classList.add('fc-day-disponible');
                        arg.el.title = "Disponible para cita";
                    } else if (fechasNoDisponibles.includes(dateStr)) {
                        arg.el.classList.add('fc-day-nodisponible');
                        arg.el.title = "No disponible";
                    } else {
                        arg.el.classList.add('fc-day-sinregistro');
                        arg.el.title = "Sin horarios registrados";
                    }
                },
                dateClick: function(info) {
                    const dateStr = info.dateStr;
                    const isPast = info.date < new Date(new Date().setHours(0,0,0,0));
                    
                    if (isPast) {
                        fechaSeleccionadaEl.textContent = 'No puedes seleccionar una fecha pasada';
                        horariosContainer.style.display = 'none';
                        btnReservar.disabled = true;
                        return;
                    }
                    
                    // Mostrar fecha seleccionada
                    fechaSeleccionadaEl.textContent = 'Fecha seleccionada: ' + formatDate(info.date);
                    
                    // Cargar horarios disponibles para esta fecha
                    cargarHorariosDisponibles(dateStr);
                }
            });
            
            calendar.render();
            
            function cargarHorariosDisponibles(fecha) {
                fetch(`get_horarios.php?id_medico=${idMedico}&fecha=${fecha}`)
                    .then(response => response.json())
                    .then(data => {
                        horariosDisponibles.innerHTML = '';
                        
                        if (data.length === 0) {
                            horariosDisponibles.innerHTML = `
                                <div class="col-span-4 text-center py-4">
                                    <i class="fas fa-calendar-times text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-gray-500">No hay horarios disponibles para este día</p>
                                </div>
                            `;
                            btnReservar.disabled = true;
                            return;
                        }
                        
                        data.forEach(horario => {
                            const hora = document.createElement('button');
                            hora.type = 'button';
                            hora.className = 'bg-blue-100 hover:bg-blue-200 text-blue-800 px-4 py-2 rounded-lg text-center transition';
                            hora.innerHTML = `
                                <span class="block font-medium">${formatTime(horario.fecha_hora)}</span>
                                <span class="text-xs text-blue-600">${horario.id_agenda ? 'Disponible' : 'Nuevo horario'}</span>
                            `;
                            hora.onclick = function() {
                                // Remover selección previa
                                document.querySelectorAll('#horarios-disponibles button').forEach(btn => {
                                    btn.classList.remove('bg-blue-600', 'text-white');
                                    btn.classList.add('bg-blue-100', 'text-blue-800');
                                });
                                
                                // Marcar como seleccionado
                                this.classList.remove('bg-blue-100', 'text-blue-800');
                                this.classList.add('bg-blue-600', 'text-white');
                                
                                // Actualizar formulario
                                idAgendaInput.value = horario.id_agenda ? horario.id_agenda : 'nuevo_' + horario.fecha_hora;
                                btnReservar.disabled = false;
                            };
                            horariosDisponibles.appendChild(hora);
                        });
                        
                        horariosContainer.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        horariosDisponibles.innerHTML = `
                            <div class="col-span-4 text-center py-4">
                                <i class="fas fa-exclamation-triangle text-3xl text-red-400 mb-2"></i>
                                <p class="text-red-500">Error al cargar horarios</p>
                            </div>
                        `;
                    });
            }
            
            function formatDate(date) {
                return date.toLocaleDateString('es-ES', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
            }
            
            function formatTime(dateTimeStr) {
                const date = new Date(dateTimeStr);
                return date.toLocaleTimeString('es-ES', { 
                    hour: '2-digit', 
                    minute: '2-digit',
                    hour12: true
                });
            }
        });
    </script>
</body>
</html>