<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin'])) {
    header("Location: login_usuario.php");
    exit;
}

require 'conexion.php';

// Obtener información del usuario incluyendo la foto
$usuario_id = $_SESSION['id_usuario'];
$stmt = $conexion->prepare("SELECT nombre, apellido, correo, foto, telefono 
                           FROM usuarios
                           WHERE id_usuario = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($nombre, $apellido, $correo, $foto, $telefono);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Panel Usuario</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="estilos_inicio.css">
    <style>
        /* Header fijo y responsivo igual que médicos */
        nav.bg-gradient-to-r {
            box-shadow: 0 2px 12px 0 rgba(30,64,175,0.10);
            min-height: 64px;
            z-index: 1050;
            background: linear-gradient(to right, #2563eb, #1e40af, #0f172a) !important;
        }
        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #c7d2fe;
            background: #f1f5f9;
            box-shadow: 0 1px 4px 0 rgba(30,64,175,0.10);
            display: inline-block;
        }
        .user-info h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }
        .user-info p {
            font-size: 0.92rem;
            opacity: 0.85;
        }
        /* Sidebar responsivo igual que médicos */
        .sidebar {
            width: 270px;
            max-width: 90vw;
            background: linear-gradient(to bottom, #1e40af, #1e3a8a, #0f172a);
            color: white;
            position: fixed;
            height: 100vh;
            z-index: 1100;
            top: 0;
            left: 0;
            box-shadow: 2px 0 15px rgba(0,0,0,0.13);
            border-right: 1px solid rgba(59,130,246,0.18);
            overflow-y: auto;
            transition: transform 0.3s cubic-bezier(0.4,0,0.2,1), left 0.3s;
        }
        @media (max-width: 1023px) {
            .sidebar {
                left: -270px;
                transform: translateX(-100%);
                box-shadow: none;
                border-right: none;
                max-width: 90vw;
            }
            .sidebar.open {
                left: 0;
                transform: translateX(0);
                box-shadow: 2px 0 15px rgba(0,0,0,0.13);
            }
            #overlay {
                display: block !important;
                z-index: 1090;
            }
        }
        @media (max-width: 640px) {
            .sidebar {
                width: 90vw;
                min-width: 0;
                padding: 1rem 0.5rem;
            }
            .user-avatar {
                width: 48px !important;
                height: 48px !important;
            }
        }
        .overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.3);
            z-index: 1080;
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
        /* Botón hamburguesa solo en móvil/tablet */
        .menu-toggle {
            display: block !important;
            z-index: 1200 !important;
            position: relative !important;
            pointer-events: auto !important;
            background: none;
            border: none;
            outline: none;
        }
        @media (min-width: 1024px) {
            .menu-toggle {
                display: none !important;
            }
        }
        /* Ajuste para que el contenido no quede debajo del header */
        body {
            padding-top: 68px !important;
        }
        /* Hover en menú */
        .menu-item {
            transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
        }
        .menu-item:hover {
            background: rgba(255,255,255,0.13);
            transform: translateX(8px);
        }
        .menu-item.active {
            background: #2563eb !important;
            color: #fff !important;
        }
    </style>
    <script>
        // Sidebar responsivo para usuarios (igual que médicos)
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const menuToggle = document.getElementById('menuToggle');
            function isMobile() {
                return window.innerWidth < 1024;
            }
            function closeSidebar() {
                sidebar.classList.remove('open');
                overlay.classList.remove('show');
            }
            function openSidebar() {
                sidebar.classList.add('open');
                overlay.classList.add('show');
            }
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    if (sidebar.classList.contains('open')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                });
            }
            if (overlay) {
                overlay.addEventListener('click', closeSidebar);
            }
            window.addEventListener('resize', function() {
                if (!isMobile()) {
                    closeSidebar();
                }
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && isMobile() && sidebar.classList.contains('open')) {
                    closeSidebar();
                }
            });
        });
    </script>
</head>
<body class="bg-gray-100">
    <!-- Barra superior -->
    <nav class="bg-gradient-to-r from-[#5DD9FC] to-[#0052CC] p-4 text-white flex justify-between items-center fixed top-0 left-0 right-0 z-50">
        <div class="flex items-center">
            <button id="menuToggle" class="menu-toggle mr-4 text-white">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <div class="flex items-center">
                <img src="img/logo.png" alt="Logo IMESYS" class="h-10 mr-3">
                <span class="font-bold text-xl">IMESYS</span>
            </div>
        </div>
        
        <div class="flex items-center">
            <?php 
            // Definir URL base
            $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/imesys/';
            
            // Procesar ruta de la foto
            $ruta_foto = '';
            $imagen_existe = false;

            if ($foto) {
                $nombre_archivo = basename($foto);
                $ruta_foto = $base_url . 'uploads/usuarios/' . $nombre_archivo;
                $ruta_absoluta = $_SERVER['DOCUMENT_ROOT'] . '/imesys/uploads/usuarios/' . $nombre_archivo;
                $imagen_existe = file_exists($ruta_absoluta);
            }
            ?>

            <?php if($foto && $imagen_existe): ?>
                <img src="<?= $ruta_foto ?>" alt="Foto de perfil" class="user-avatar mr-2">
            <?php else: ?>
                <div class="user-avatar bg-white text-blue-600 flex items-center justify-center">
                    <i class="fas fa-user"></i>
                </div>
            <?php endif; ?>

            <div class="hidden md:inline text-right mr-3">
                <span><?= htmlspecialchars($nombre . ' ' . $apellido); ?></span>
                <div class="text-xs"><?= htmlspecialchars($telefono); ?></div>
            </div>
            <a href="logout_usuario.php" class="flex items-center text-white border border-white px-4 py-2 rounded-lg hover:bg-white hover:text-blue-700 transition-all duration-300">
                <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
            </a>
            

        </div>
    </nav>

    

    <!-- Overlay -->
    <div id="overlay" class="overlay"></div>

    
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar">
        <div class="p-6 h-full flex flex-col">
            <!-- Información de usuario -->
            <div class="user-info text-center mb-8">
                <div class="relative inline-block mb-3">
                    <?php if($foto && $imagen_existe): ?>
                        <img src="<?= $ruta_foto ?>" alt="Foto de perfil" class="user-avatar mx-auto block shadow-xl border-4 border-blue-300/70" style="width:76px !important;height:76px !important;object-fit:cover !important;border-radius:50% !important;box-shadow:0 6px 24px 0 rgba(30,64,175,0.18) !important;border:4px solid #60a5fa !important;">
                    <?php else: ?>
                        <div class="user-avatar bg-white text-blue-600 flex items-center justify-center mx-auto" style="width:76px !important;height:76px !important;border-radius:50% !important;box-shadow:0 6px 24px 0 rgba(30,64,175,0.18) !important;border:4px solid #60a5fa !important;font-size:2.2rem !important;">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <h3 class="text-white font-semibold leading-tight text-base mb-1" style="font-size:1.13rem !important;line-height:1.2 !important;letter-spacing:0.01em !important;max-width:90%;margin:0 auto !important;white-space:nowrap !important;overflow:hidden !important;text-overflow:ellipsis !important;">
                    <?php echo htmlspecialchars($nombre . ' ' . $apellido); ?>
                </h3>
                <p class="text-blue-200 text-sm truncate px-1 mb-1" style="font-size:1.01rem !important;max-width:90%;margin:0 auto !important;white-space:nowrap !important;overflow:hidden !important;text-overflow:ellipsis !important;">
                    <?php echo htmlspecialchars($correo); ?>
                </p>
                <div class="h-px bg-blue-400/30 mt-4 mb-2" style="height:1.5px !important;background:rgba(59,130,246,0.32) !important;"></div>
            </div>

            
             <!-- Sección de menú -->
            <div class="mb-6 flex-grow">
                <h2 class="menu-title font-semibold text-gray-100 uppercase text-xs mb-4">Predicción Médica IA</h2>
                <div class="space-y-2">
                    <?php $pagina = basename($_SERVER['PHP_SELF']); ?>
                    <a href="inicio_imesys.php" class="menu-item flex items-center p-3 rounded<?php if($pagina=="inicio_imesys.php") echo ' active'; ?>">
                        <i class="fas fa-home mr-3 text-white"></i>
                        <span>Inicio</span>
                    </a>
                    <a href="imesys_ai.php" class="menu-item flex items-center p-3 rounded<?php if($pagina=="imesys_ai.php") echo ' active'; ?>">
                        <i class="fas fa-comment-alt mr-3 text-white"></i>
                        <span>Chat IA</span>
                    </a>
                    <a href="datos_bio.php" class="menu-item flex items-center p-3 rounded<?php if($pagina=="datos_bio.php") echo ' active'; ?>">
                       <i class="fas fa-user-md mr-3 text-white"></i>
                        <span>IA Datos Biométricos</span>
                    </a>
                    <a href="modulo_citas.php" class="menu-item flex items-center p-3 rounded<?php if($pagina=="modulo_citas.php") echo ' active'; ?>">
                    <i class="fas fa-calendar-check mr-3 text-white"></i>
                        <span>Especialistas y Citas</span>
                    </a>
                    <a href="recompensas.php" class="menu-item flex items-center p-3 rounded<?php if($pagina=="recompensas.php") echo ' active'; ?>">
                        <i class="fas fa-pills mr-3 text-white"></i>
                        <span>Descuentos en Medicamentos</span>
                    </a>
                    <a href="mis_citas.php" class="menu-item flex items-center p-3 rounded<?php if($pagina=="mis_citas.php") echo ' active'; ?>">
                        <i class="fas fa-clock mr-3 text-white"></i>
                        <span>Mis citas</span>
                    </a>
                    <a href="historial_paciente.php" class="menu-item flex items-center p-3 rounded<?php if($pagina=="historial_paciente.php") echo ' active'; ?>">
                        <i class="fas fa-file-medical mr-3 text-white"></i>
                        <span>Mi Historial Médico</span>
                    </a>
                    <a href="comentarios_medicos.php" class="menu-item flex items-center p-3 rounded<?php if($pagina=="comentarios_medicos.php") echo ' active'; ?>">
                        <i class="fas fa-comment-dots mr-3 text-white"></i>
                        <span>Comentarios</span>
                    </a>
                    <a href="perfil_usuario.php" class="menu-item flex items-center p-3 rounded<?php if($pagina=="perfil_usuario.php") echo ' active'; ?>">
                        <i class="fas fa-cog mr-3 text-white"></i>
                        <span>Ajustes</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
<br>
    <?php if (isset($_SESSION['puntos_ganados'])): ?>
    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
        ¡Has ganado <?php echo $_SESSION['puntos_ganados']; ?> puntos por iniciar sesión hoy!
    </div>
    <?php unset($_SESSION['puntos_ganados']); ?>
<?php endif; ?>
