<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Verificar autenticación
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['id_medico'])) {
    header("Location: login_medico.php");
    exit;
}

require_once 'conexion.php';
if (!$conexion || $conexion->connect_error) {
    die("Conexión fallida desde header_medico.php: " . $conexion->connect_error);
}


// Obtener información del médico (misma lógica que perfil_medico.php)
$id_medico = $_SESSION['id_medico'];

// Obtener información del médico
$query_medico = "SELECT m.*, e.nombre_especialidad 
                FROM medicos m 
                JOIN especialidades e ON m.id_especialidad = e.id_especialidad 
                WHERE m.id_medico = ?";
$stmt_medico = $conexion->prepare($query_medico);
$stmt_medico->bind_param("i", $id_medico);
$stmt_medico->execute();
$result_medico = $stmt_medico->get_result();
$medico = $result_medico->fetch_assoc();

// Extraer variables individuales para compatibilidad
$nombre = $medico['nombre'];
$apellido = $medico['apellido'];
$correo = $medico['correo'];
$foto = $medico['foto'];
$especialidad = $medico['nombre_especialidad'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="estilos_inicio.css">
    <style>
        /* Sobrescribir estilos externos para el perfil del médico en sidebar */
        .sidebar .user-info {
            display: block !important; /* Sobrescribir flex */
            background: transparent !important; /* Remover background glassmorphism */
            backdrop-filter: none !important; /* Remover blur */
            border-radius: 0 !important; /* Remover border-radius */
            padding: 0 !important; /* Remover padding extra */
            margin-bottom: 1.5rem !important; /* Controlar margen */
            border: none !important; /* Remover bordes */
            text-align: center;
        }
        
        .sidebar .user-info img {
            transition: all 0.3s ease;
            margin-right: 0 !important; /* Centrar avatar */
        }
        
        .sidebar .user-info img:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }
        
        .sidebar .user-info h3 {
            font-size: 0.875rem !important; /* text-sm */
            font-weight: 600 !important;
            margin-bottom: 0.5rem !important;
            text-shadow: none !important;
            line-height: 1.25 !important;
        }
        
        .sidebar .user-info p {
            font-size: 0.75rem !important; /* text-xs */
            opacity: 1 !important;
            text-shadow: none !important;
            margin-bottom: 0.5rem !important;
        }
        
        /* Animación para el indicador de estado */
        .user-info .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }
        /* Sidebar responsivo mejorado */
        .sidebar {
            width: 280px;
            max-width: 90vw;
            background: linear-gradient(to bottom, #1e40af, #1e3a8a, #0f172a);
            color: white;
            position: fixed;
            height: 100vh;
            z-index: 1000;
            top: 0;
            left: 0;
            box-shadow: 2px 0 15px rgba(0,0,0,0.3);
            border-right: 1px solid rgba(59,130,246,0.3);
            overflow-y: auto;
            transition: transform 0.3s cubic-bezier(0.4,0,0.2,1), left 0.3s;
        }
        @media (max-width: 1023px) {
            .sidebar {
                left: -280px;
                transform: translateX(-100%);
                box-shadow: none;
                border-right: none;
                max-width: 90vw;
            }
            .sidebar.open {
                left: 0;
                transform: translateX(0);
                box-shadow: 2px 0 15px rgba(0,0,0,0.3);
            }
            #overlay {
                display: block !important;
                z-index: 999;
            }
            .main-layout {
                padding-left: 0 !important;
            }
        }
        @media (max-width: 640px) {
            .sidebar {
                width: 90vw;
                min-width: 0;
                padding: 1rem 0.5rem;
                left: -90vw;
                max-width: 90vw;
            }
            .sidebar.open {
                left: 0;
            }
            .main-layout {
                padding-left: 0 !important;
            }
            #topNavbar {
                left: 0 !important;
                width: 100vw !important;
                right: 0 !important;
                min-width: 0 !important;
            }
        }
        @media (max-width: 480px) {
            .sidebar {
                width: 100vw;
                max-width: 100vw;
                left: -100vw;
            }
            .sidebar.open {
                left: 0;
            }
            #topNavbar {
                width: 100vw !important;
            }
        }
        /* Overlay para sidebar móvil */
        .overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.4);
            z-index: 900;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }
        .overlay.show {
            opacity: 1;
            visibility: visible;
        }
        @media (min-width: 1024px) {
            .overlay { display: none !important; }
        }
        /* Ajuste header para que no se superponga */
        @media (max-width: 1023px) {
            #topNavbar {
                left: 0 !important;
                width: 100% !important;
                right: 0 !important;
            }
        }
        /* Mostrar botón hamburguesa solo en móvil/tablet */
        .menu-toggle {
            display: block !important;
            z-index: 1200 !important;
            position: relative !important;
            pointer-events: auto !important;
        }
        @media (min-width: 1024px) {
            .menu-toggle {
                display: none !important;
            }
        }
        /* Forzar z-index del sidebar y overlay */
        .sidebar {
            z-index: 1100 !important;
        }
        .overlay {
            z-index: 1090 !important;
        }
    </style>
</head>
<body class="bg-gray-100 overflow-x-hidden">
    <!-- Contenedor principal para el layout responsivo -->
    <div id="mainLayout" class="main-layout">
    <!-- Barra superior responsiva -->
    <nav id="topNavbar" class="bg-gradient-to-r from-blue-600 via-blue-700 to-blue-900 p-4 text-white flex justify-between items-center fixed top-0 z-50 transition-all duration-300 shadow-lg">
        <div class="flex items-center">
            <!-- Botón hamburguesa (solo visible en móvil) -->
            <button id="menuToggle" style="z-index:1100; position:relative;" class="menu-toggle mr-4 text-white lg:hidden hover:bg-white/20 hover:text-blue-200 p-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-300">
                <i class="fas fa-bars text-xl"></i>
            </button>
            
            <!-- Logo y título -->
            <div class="flex items-center">
                <img src="img/logo.png" alt="Logo IMESYS" class="h-8 lg:h-10 mr-2 lg:mr-3">
                <span class="font-bold text-lg lg:text-xl">IMESYS</span>
            </div>
        </div>
        
        <!-- Información del usuario simplificada -->
        <div class="flex items-center gap-3">
            <!-- Avatar del usuario -->
            <img src="<?php echo $medico['foto'] ? '../uploads/medicos/'.$medico['foto'] : 'img/persona.jpg'; ?>" 
                 alt="Foto de perfil" 
                 class="user-avatar w-8 h-8 lg:w-10 lg:h-10 rounded-full object-cover border-2 border-blue-200/50 hover:border-blue-200 hover:scale-105 transition-all duration-300 shadow-sm"
                 onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23ffffff\'%3E%3Cpath d=\'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z\'/%3E%3C/svg%3E'; this.className += ' bg-blue-500 p-1';">
            
            <!-- Botón cerrar sesión (solo icono) -->
            <a href="logout_medico.php" class="flex items-center justify-center text-white border-0 border-blue-300/60 w-10 h-10 lg:w-12 lg:h-12 rounded-lg hover:bg-blue-200 hover:text-blue-900 hover:border-blue-200 transition-all duration-300 shadow-sm" title="Cerrar Sesión">
                <i class="fas fa-sign-out-alt text-lg lg:text-xl"></i>
            </a>
        </div>
    </nav>

    <!-- Overlay -->
    <div id="overlay" class="overlay"></div>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar">
        <div class="p-6 h-full flex flex-col">
            <!-- Información de usuario -->
            <div class="user-info">
                <!-- Avatar principal centrado -->
                <div class="relative inline-block mb-3">
                    <img src="<?php echo $medico['foto'] ? '../uploads/medicos/'.$medico['foto'] : 'img/persona.jpg'; ?>" 
                         alt="Foto de perfil" 
                         class="w-16 h-16 rounded-full object-cover border-3 border-blue-300/60 shadow-xl mx-auto block"
                         onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23ffffff\'%3E%3Cpath d=\'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z\'/%3E%3C/svg%3E'; this.className = this.className.replace('object-cover', '') + ' bg-blue-500 p-2';">
                    <!-- Indicador de estado online -->
                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 border-2 border-white rounded-full">
                        <div class="w-1.5 h-1.5 bg-white rounded-full animate-pulse mx-auto mt-0.5"></div>
                    </div>
                </div>
                
                <!-- Nombre del médico -->
                <h3 class="text-white font-semibold leading-tight">
                    Dr. <?php echo htmlspecialchars($nombre . ' ' . $apellido); ?>
                </h3>
                
                <!-- Especialidad -->
                <p class="text-blue-200 flex items-center justify-center">
                    <i class="fas fa-stethoscope mr-1"></i>
                    <?php echo htmlspecialchars($especialidad); ?>
                </p>
                
                <!-- Email -->
                <p class="text-blue-300 truncate px-1">
                    <?php echo htmlspecialchars($correo); ?>
                </p>
                
                <!-- Línea separadora sutil -->
                <div class="h-px bg-blue-400/30 mt-4"></div>
            </div>

            <!-- Sección de menú -->
            <div class="mb-6 flex-grow">
                <h2 class="menu-title font-semibold text-blue-200 uppercase text-xs mb-4 border-b border-blue-400/30 pb-2">Panel Médico</h2>
                <div class="space-y-2">
                    <?php
                    // Obtener el nombre del archivo actual
                    $current_page = basename($_SERVER['PHP_SELF']);
                    
                    // Función para determinar si un enlace está activo
                    function isActive($page, $current) {
                        // Manejar casos especiales para páginas relacionadas
                        if ($page === 'neumonia.php' && ($current === 'index_neumonia.php' || $current === 'neumonia.php')) {
                            return 'menu-item active';
                        }
                        return ($page === $current) ? 'menu-item active' : 'menu-item';
                    }
                    ?>
                
                    <a href="inicio_medicos.php" class="<?= isActive('inicio_medicos.php', $current_page) ?> flex items-center p-3 rounded">
                        <i class="fas fa-home mr-3 text-white"></i>
                        <span>Inicio</span>
                    </a>
                    <a href="imesys_ai.php" class="<?= isActive('imesys_ai.php', $current_page) ?> flex items-center p-3 rounded">
                        <i class="fas fa-comment-alt mr-3 text-white"></i>
                        <span>Chat IA</span>
                    </a>
                    <a href="neumonia.php" class="<?= isActive('neumonia.php', $current_page) ?> flex items-center p-3 rounded">
                        <i class="fas fa-lungs mr-3 text-white"></i>
                        <span>Análisis Imágenes Médicas IA</span>
                    </a>
                    <a href="modulo_citas.php" class="<?= isActive('modulo_citas.php', $current_page) ?> flex items-center p-3 rounded">
                        <i class="fas fa-calendar-alt mr-3 text-white"></i>
                        <span>Agenda de Citas</span>
                    </a>
                    <a href="buscador_pacientes.php" class="<?= isActive('buscador_pacientes.php', $current_page) ?> flex items-center p-3 rounded">
                        <i class="fas fa-users mr-3 text-white"></i>
                        <span>Mis Pacientes</span>
                    </a>
                    <a href="buscar_historiales.php" class="<?= isActive('buscar_historiales.php', $current_page) ?> flex items-center p-3 rounded">
                        <i class="fas fa-file-medical mr-3 text-white"></i>
                        <span>Historiales Médicos</span>
                    </a>
                    <a href="buscar_pacientes_receta.php" class="<?= isActive('buscar_pacientes_receta.php', $current_page) ?> flex items-center p-3 rounded">
                        <i class="fas fa-prescription mr-3 text-white"></i>
                        <span>Recetas Electrónicas</span>
                    </a>
                    <a href="dashboard_medico.php" class="<?= isActive('dashboard_medico.php', $current_page) ?> flex items-center p-3 rounded">
                        <i class="fas fa-chart-line mr-3 text-white"></i>
                        <span>Estadísticas</span>
                    </a>
                    <a href="perfil_medico.php" class="<?= isActive('perfil_medico.php', $current_page) ?> flex items-center p-3 rounded">
                        <i class="fas fa-user-cog mr-3 text-white"></i>
                        <span>Perfil Profesional</span>
                    </a>
                    <a href="editar_perfil_medico.php" class="<?= isActive('editar_perfil_medico.php', $current_page) ?> flex items-center p-3 rounded">
                        <i class="fas fa-cog mr-3 text-white"></i>
                        <span>Configuración</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sistema de Sidebar Responsivo
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const menuToggle = document.getElementById('menuToggle');
            const topNavbar = document.getElementById('topNavbar');
            
            // Función para determinar si estamos en móvil
            function isMobile() {
                return window.innerWidth < 1024; // lg breakpoint en Tailwind
            }
            
            // Función para ajustar elementos según el tamaño de pantalla
            function adjustLayout() {
                const body = document.body;
                // Siempre cerrar sidebar y overlay al cargar o redimensionar
                sidebar.classList.remove('open');
                overlay.classList.remove('show');
                if (!isMobile()) {
                    body.classList.add('desktop-layout');
                } else {
                    body.classList.remove('desktop-layout');
                }
            }
            
            // Función para manejar el toggle del sidebar en móvil
            function toggleSidebar() {
                if (isMobile()) {
                    const isOpen = sidebar.classList.contains('open');
                    
                    if (isOpen) {
                        sidebar.classList.remove('open');
                        overlay.classList.remove('show');
                    } else {
                        sidebar.classList.add('open');
                        overlay.classList.add('show');
                    }
                }
            }
            
            // Event listeners
            if (menuToggle) {
                menuToggle.addEventListener('click', toggleSidebar);
            }
            
            if (overlay) {
                overlay.addEventListener('click', function() {
                    if (isMobile()) {
                        sidebar.classList.remove('open');
                        overlay.classList.remove('show');
                    }
                });
            }
            
            // Cerrar sidebar al hacer clic en un enlace en móvil
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (isMobile()) {
                        sidebar.classList.remove('open');
                        overlay.classList.remove('show');
                    }
                });
                
                // Efectos hover mejorados (solo para elementos no activos)
                if (!item.classList.contains('active')) {
                    item.addEventListener('mouseenter', function() {
                        this.style.transform = 'translateX(8px)';
                        this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                        this.style.backgroundColor = 'rgba(255, 255, 255, 0.15)';
                    });
                    
                    item.addEventListener('mouseleave', function() {
                        this.style.transform = 'translateX(0)';
                        this.style.backgroundColor = '';
                    });
                }
            });
            
            // Manejar redimensionado de ventana
            window.addEventListener('resize', function() {
                adjustLayout();
                
                // En desktop, asegurar que el sidebar esté siempre visible
                if (!isMobile()) {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('show');
                } else {
                    // En móvil, cerrar el sidebar si está abierto al rotar
                    if (sidebar.classList.contains('open')) {
                        sidebar.classList.remove('open');
                        overlay.classList.remove('show');
                    }
                }
            });
            
            // Manejar tecla ESC para cerrar sidebar en móvil
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && isMobile() && sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('show');
                }
            });
            
            // Inicialización
            adjustLayout();
            
            // Animación suave al cargar la página
            setTimeout(function() {
                sidebar.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                topNavbar.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            }, 100);
        });
    </script>
    
    </div> <!-- Cierre del mainLayout -->
</body>
</html>