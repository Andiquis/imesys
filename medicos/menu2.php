<?php
session_start();

// Verificar si el usuario está logueado (opcional)
$loggedin = isset($_SESSION['loggedin']);
$medico_id = $_SESSION['id_medico'] ?? null;

// Obtener información del médico si está logueado
if ($loggedin) {
    require 'conexion.php';
    
    $stmt = $conexion->prepare("SELECT m.nombre, m.apellido, m.correo, m.foto, e.nombre_especialidad 
                               FROM medicos m
                               JOIN especialidades e ON m.id_especialidad = e.id_especialidad
                               WHERE m.id_medico = ?");
    $stmt->bind_param("i", $medico_id);
    $stmt->execute();
    $stmt->bind_result($nombre, $apellido, $correo, $foto, $especialidad);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Título de la Página</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="estilos_inicio.css">
    <style>
        /* Estilos para el layout básico */
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background-color: #f8fafc; 
            min-height: 100vh;
        }
        
        .content-area {
            margin-left: 0;
            transition: margin-left 0.3s;
            padding-top: 80px;
            min-height: calc(100vh - 160px);
        }
        
        .sidebar {
            width: 280px;
            position: fixed;
            top: 0;
            left: -280px;
            height: 100%;
            background-color: #1e3a8a;
            transition: left 0.3s;
            z-index: 1000;
            color: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar.active {
            left: 0;
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .menu-item {
            color: #e2e8f0;
            transition: all 0.2s;
        }
        
        .menu-item:hover, .menu-item.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .menu-title {
            letter-spacing: 0.05em;
            color: #93c5fd;
        }
        
        .footer {
            background-color: #f1f5f9;
            padding: 20px 0;
            margin-top: 40px;
            border-top: 1px solid #e2e8f0;
        }
        
        @media (min-width: 768px) {
            .sidebar {
                left: 0;
            }
            
            .content-area {
                margin-left: 280px;
            }
        }
        
        /* Colores personalizados */
        .bg-primary {
            background-color: #1e3a8a;
        }
        
        .bg-primary-dark {
            background-color: #1e40af;
        }
        
        .text-primary {
            color: #1e3a8a;
        }
        
        .border-primary {
            border-color: #1e3a8a;
        }
        
        .hover\:bg-primary-dark:hover {
            background-color: #1e40af;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Barra superior -->
    <nav class="bg-primary p-4 text-white flex justify-between items-center fixed top-0 left-0 right-0 z-50 shadow-md">
        <div class="flex items-center">
            <button id="menuToggle" class="menu-toggle mr-4 text-white md:hidden">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <div class="flex items-center">
                <img src="img/logo.png" alt="Logo IMESYS" class="h-10 mr-3">
                <span class="font-bold text-xl">IMESYS</span>
            </div>
        </div>
        
        <?php if($loggedin): ?>
        <div class="flex items-center">
            <?php 
            // Definir URL base
            $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/proyectoimesys/';
            
            // Procesar ruta de la foto
            $ruta_foto = '';
            $imagen_existe = false;

            if ($foto) {
                $nombre_archivo = basename($foto);
                $ruta_foto = $base_url . 'uploads/medicos/' . $nombre_archivo;
                $ruta_absoluta = $_SERVER['DOCUMENT_ROOT'] . '/proyectoimesys/uploads/medicos/' . $nombre_archivo;
                $imagen_existe = file_exists($ruta_absoluta);
            }
            ?>

            <?php if($foto && $imagen_existe): ?>
                <img src="<?= $ruta_foto ?>" alt="Foto de perfil" class="user-avatar mr-2">
            <?php else: ?>
                <div class="user-avatar bg-white text-primary flex items-center justify-center">
                    <i class="fas fa-user-md"></i>
                </div>
            <?php endif; ?>

            <div class="hidden md:inline text-right mr-3">
                <span>Bienvenido, Dr. <?= htmlspecialchars($nombre . ' ' . $apellido); ?></span>
                <div class="text-xs text-gray-200"><?= htmlspecialchars($especialidad); ?></div>
            </div>
            <a href="logout_medico.php" class="boton-outline text-white border-white hover:bg-white hover:text-primary px-4 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
            </a>
        </div>
        <?php endif; ?>
    </nav>

    <!-- Overlay -->
    <div id="overlay" class="overlay"></div>

    <!-- Sidebar -->
    <?php if($loggedin): ?>
    <div id="sidebar" class="sidebar">
        <div class="p-6 h-full flex flex-col">
            <!-- Información de usuario -->
            <div class="user-info mb-8 flex flex-col items-center text-center">
                <?php if($foto && $imagen_existe): ?>
                    <img src="<?= $ruta_foto ?>" alt="Foto de perfil" class="user-avatar w-20 h-20 mb-4">
                <?php else: ?>
                    <div class="user-avatar bg-white text-primary flex items-center justify-center w-20 h-20 mb-4 text-3xl">
                        <i class="fas fa-user-md"></i>
                    </div>
                <?php endif; ?>
                <h3 class="text-white font-semibold">Dr. <?php echo htmlspecialchars($nombre . ' ' . $apellido); ?></h3>
                <p class="text-gray-300 text-sm mt-1"><?php echo htmlspecialchars($especialidad); ?></p>
                <p class="text-gray-300 text-xs mt-1"><?php echo htmlspecialchars($correo); ?></p>
            </div>

            <!-- Sección de menú -->
            <div class="mb-6 flex-grow">
                <h2 class="menu-title font-semibold uppercase text-xs mb-4">Panel Médico</h2>
                <div class="space-y-1">
                    <a href="imesys_ai.php" class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-comment-alt mr-3"></i>
                        <span>Chat IA</span>
                    </a>
                    <a href="datos_bio.php" class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-heartbeat mr-3"></i>
                        <span>IA Datos Biométricos</span>
                    </a>
                    <a href="#" class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-x-ray mr-3"></i>
                        <span>Análisis Imágenes Médicas</span>
                    </a>
                    <a href="administrar_citas.php" class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-calendar-alt mr-3"></i>
                        <span>Agenda de Citas</span>
                    </a>
                    <a href="#" class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-users mr-3"></i>
                        <span>Mis Pacientes</span>
                    </a>
                    <a href="#" class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-file-medical mr-3"></i>
                        <span>Historiales Médicos</span>
                    </a>
                    <a href="#" class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-prescription mr-3"></i>
                        <span>Recetas Electrónicas</span>
                    </a>
                    <a href="#" class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-chart-line mr-3"></i>
                        <span>Estadísticas</span>
                    </a>
                    <a href="perfil_medico.php" class="menu-item active flex items-center p-3 rounded">
                        <i class="fas fa-user-cog mr-3"></i>
                        <span>Perfil Profesional</span>
                    </a>
                    <a href="#" class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-cog mr-3"></i>
                        <span>Configuración</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Contenido principal -->
    <div id="contentArea" class="content-area">
        <div class="container mx-auto px-4 py-6">
            <!-- ******************************************* -->
            <!-- AQUÍ VA EL CONTENIDO ESPECÍFICO DE TU PÁGINA -->
            <!-- ******************************************* -->
            
            <h1 class="text-2xl font-bold mb-6 text-gray-800">Título de la Página</h1>
            
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <!-- Tu contenido aquí -->
                <?php if(isset($mensaje)): ?>
                    <div class="mb-4 p-4 bg-blue-50 text-blue-700 rounded border border-blue-100">
                        <?= htmlspecialchars($mensaje) ?>
                    </div>
                <?php endif; ?>
                
                <p class="text-gray-700">Este es un espacio para el contenido específico de cada página.</p>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="footer">
            <div class="container mx-auto max-w-4xl px-4">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-4 md:mb-0">
                        <img src="img/logo.png" alt="Logo IMESYS" class="h-10">
                        <p class="mt-2 text-sm text-gray-600">Sistema de gestión médica integral</p>
                    </div>
                    <div class="text-center md:text-right">
                        <p class="text-sm text-gray-600">© <?= date('Y') ?> IMESYS. Todos los derechos reservados.</p>
                        <p class="text-sm text-gray-500 mt-1">soporte@imesys.com | Tel: +1 234 567 890</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // Toggle del menú sidebar
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const contentArea = document.getElementById('contentArea');
        
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        });
        
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        });
    </script>
</body>
</html>