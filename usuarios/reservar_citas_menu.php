<?php
ob_start(); // Iniciar búfer de salida para evitar salida no deseada
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Detección de Neumonía</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="estilos_inicio.css">
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
            <?php if($foto): ?>
                <img src="Uploads/<?php echo htmlspecialchars($foto); ?>" alt="Foto de perfil" class="user-avatar mr-2">
            <?php else: ?>
                <div class="user-avatar bg-white text-blue-600 flex items-center justify-center">
                    <i class="fas fa-user"></i>
                </div>
            <?php endif; ?>
            <span class="hidden md:inline">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido']); ?></span>
            <a href="logout.php" class="boton-outline text-white border-white hover:bg-white hover:text-[#0052CC]">
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
            <div class="user-info mb-8">
                <?php if($foto): ?>
                    <img src="Uploads/<?php echo htmlspecialchars($foto); ?>" alt="Foto de perfil" class="user-avatar mr-2">
                <?php else: ?>
                    <div class="user-avatar bg-white text-blue-600 flex items-center justify-center">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
                <p class="text-gray-100 text-sm mt-1"><?php echo htmlspecialchars($_SESSION['correo']); ?></p>
            </div>

            <!-- Sección de menú -->
            <div class="mb-6 flex-grow">
                <h2 class="menu-title font-semibold text-gray-100 uppercase text-xs mb-4">Predicción Médica IA</h2>
                <div class="space-y-2">
                    <a href="imesys_ai.php" class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-comment-alt mr-3 text-white"></i>
                        <span>Chat IA</span>
                    </a>
                    <a href="datos_bio.php" class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-comment-alt mr-3 text-white"></i>
                        <span>IA Datos Biométricos</span>
                    </a>
                    <a href="listamedicosmenu.php" class="menu-item  flex items-center p-3 rounded">
                        <i class="fas fa-user-md mr-3 text-white"></i>
                        <span>Lista de Especialistas</span>
                    </a>
                    <a href="reservar_cita.php" class="menu-item active  flex items-center p-3 rounded">
                    <i class="fas fa-calendar-check mr-3 text-white"></i>
                        <span>Reservar Citas</span>
                    </a>
                    <a href="#" class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-pills mr-3 text-white"></i>
                        <span>Descuentos en Medicamentos</span>
                    </a>
                    <a href="mis_citas.php" class="menu-item  flex items-center p-3 rounded">
                        <i class="fas fa-image mr-3 text-white"></i>
                        <span>Mis citas</span>
                    </a>
                    
                    <a href="neumonia.php " class="menu-item flex items-center p-3 rounded">
                        <i class="fas fa-cog mr-3 text-white"></i>
                        <span>Ajustes</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón flotante -->
    <div class="floating-chat" id="chatButton">
        <i class="fas fa-lungs text-white text-2xl"></i>
    </div>

    <!-- Contenido principal -->
    <div id="contentArea" class="content-area">
        <?php include 'reservar_cita.php'; ?>
    </div>

    <!-- Footer -->
    <footer class="footer bg-gray-800 text-white py-6">
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

    <script>
        // Elementos del DOM para el menú
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menuToggle');
        const overlay = document.getElementById('overlay');
        const menuItems = document.querySelectorAll('.menu-item');
        const chatButton = document.getElementById('chatButton');

        // Estado del menú
        let menuOpen = false;

        // Verificar que los elementos del menú existen
        if (!sidebar || !menuToggle || !overlay) {
            console.error('Error: No se encontraron los elementos sidebar, menuToggle o overlay');
        }

        // Función para alternar el menú
        function toggleMenu() {
            menuOpen = !menuOpen;
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
            menuToggle.classList.toggle('rotated');
        }

        // Eventos del menú
        menuToggle.addEventListener('click', toggleMenu);
        overlay.addEventListener('click', toggleMenu);

        // Evento para items del menú
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                menuItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                toggleMenu();
            });
        });

        // Evento para el botón flotante
        chatButton.addEventListener('click', function() {
            alert('Detección de neumonía ya está abierto');
        });
    </script>
</body>
</html>