<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

require 'conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Inicio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .boton {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(to right, #0097A7, #7AC943);
            padding: 10px 20px;
            border-radius: 20px;
            color: black;
            font-weight: bold;
            text-decoration: none;
            font-family: Arial, sans-serif;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .boton:hover {
            transform: scale(1.05);
        }
        
        .boton-outline {
            background: transparent;
            border: 2px solid #0097A7;
            color: #0097A7;
        }
        
        .sidebar {
            transition: all 0.3s;
            width: 250px;
        }
        
        .sidebar.collapsed {
            width: 70px;
        }
        
        .sidebar.collapsed .sidebar-text {
            display: none;
        }
        
        .sidebar.collapsed .logo-text {
            display: none;
        }
        
        .sidebar.collapsed .pin-icon {
            transform: rotate(-90deg);
        }
        
        .content-area {
            transition: margin-left 0.3s;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -250px;
                z-index: 1000;
                height: 100vh;
            }
            
            .sidebar.mobile-show {
                left: 0;
            }
            
            .content-area {
                margin-left: 0 !important;
            }
            
            .mobile-menu-btn {
                display: block;
            }
        }
        
        .health-card {
            transition: all 0.3s ease;
        }
        
        .health-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Barra superior -->
    <nav class="bg-gradient-to-r from-[#5DD9FC] to-[#0052CC] p-4 text-white flex justify-between items-center">
        <div class="flex items-center">
        <button id="mobileMenuBtn" class="mobile-menu-btn mr-4 text-white md:hidden">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <img src="img/logo.png" alt="Logo IMESYS" class="h-10 mr-3">
            <span class="font-bold text-xl">IMESYS HEALTH ASSISTANCE</span>
        </div>
        <div class="flex items-center space-x-4">
            <span class="hidden md:inline">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
            <a href="logout.php" class="boton-outline text-white border-white hover:bg-white hover:text-[#0052CC]">
                <i class="fas fa-sign-out-alt mr-2"></i><span class="hidden md:inline">Cerrar Sesión</span>
            </a>
        </div>
    </nav>

    <!-- Sidebar simplificado -->
    <div id="sidebar" class="sidebar bg-white shadow-lg fixed h-full overflow-y-auto">
        <div class="p-4 flex items-center justify-between border-b">
            <div class="flex items-center">
                <img src="img/logo.png" alt="Logo IMESYS" class="h-8 mr-2">
                <span class="logo-text font-bold">IMESYS</span>
            </div>
            <button id="pinSidebar" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-thumbtack pin-icon"></i>
            </button>
        </div>
        
        <div class="p-4">
            <div class="mb-6">
                <h2 class="font-semibold mb-4 text-lg">Menú</h2>
                <div class="space-y-3">
                    <a href="#" class="block p-2 hover:bg-blue-50 rounded-lg">
                        <i class="fas fa-user-edit mr-2"></i>
                        <span class="sidebar-text">Editar perfil</span>
                    </a>
                    <a href="#" class="block p-2 hover:bg-blue-50 rounded-lg">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <span class="sidebar-text">Mis Citas</span>
                    </a>
                    <a href="#" class="block p-2 hover:bg-blue-50 rounded-lg">
                        <i class="fas fa-robot mr-2"></i>
                        <span class="sidebar-text">Asistente Virtual</span>
                    </a>
                    <a href="#" class="block p-2 hover:bg-blue-50 rounded-lg">
                        <i class="fas fa-file-medical mr-2"></i>
                        <span class="sidebar-text">Historial Médico</span>
                    </a>
                    <a href="#" class="block p-2 hover:bg-blue-50 rounded-lg">
                        <i class="fas fa-pills mr-2"></i>
                        <span class="sidebar-text">Medicamentos</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido principal -->
    <div id="contentArea" class="content-area md:ml-64 p-4 pt-20">
        <div class="container mx-auto max-w-4xl">
            <!-- Sección de bienvenida y buscador -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">BIENVENIDO <?php echo strtoupper(htmlspecialchars($_SESSION['nombre'])); ?></h1>
                <p class="text-gray-600 mb-4">¿Cómo podemos ayudarte hoy?</p>
                
                <div class="relative">
                    <input type="text" placeholder="Escribe tu consulta..." class="w-full p-4 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button class="absolute right-3 top-3 text-gray-400 hover:text-blue-500">
                        <i class="fas fa-search text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Consejos de salud -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="health-card bg-blue-50 p-5 rounded-lg shadow-md">
                    <h3 class="font-bold text-blue-800 text-lg mb-2">Sistemas de la Gastritis</h3>
                    <p class="text-gray-600 mb-3">¿Qué son las sistemas de la gastritis y la gastropatía?</p>
                    <a href="#" class="text-blue-500 font-medium inline-flex items-center">
                        Revisa Aquí <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>
                
                <div class="health-card bg-green-50 p-5 rounded-lg shadow-md">
                    <h3 class="font-bold text-green-800 text-lg mb-2">Últimos avances tecnológicos</h3>
                    <p class="text-gray-600 mb-3">Descubre las últimas innovaciones en medicina.</p>
                    <a href="#" class="text-green-500 font-medium inline-flex items-center">
                        Más información <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>
                
                <div class="health-card bg-purple-50 p-5 rounded-lg shadow-md">
                    <h3 class="font-bold text-purple-800 text-lg mb-2">Sistema de salud integrado</h3>
                    <p class="text-gray-600 mb-3">Conoce cómo funciona nuestro sistema de atención.</p>
                    <a href="#" class="text-purple-500 font-medium inline-flex items-center">
                        Más información <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>
                
                <div class="health-card bg-yellow-50 p-5 rounded-lg shadow-md">
                    <h3 class="font-bold text-yellow-800 text-lg mb-2">Especialista del mes</h3>
                    <p class="text-gray-600 mb-1"><strong>Dr. Carlos Benavides</strong></p>
                    <p class="text-sm text-gray-600 mb-3">Especialista con 25 años de experiencia en gastroenterología.</p>
                    <div class="flex space-x-2 text-green-500">
                        <span>✅</span>
                        <span>✅</span>
                        <span>✅</span>
                    </div>
                </div>
            </div>
            
            <!-- Sección de contacto -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Contacto con nosotros</h2>
                <p class="text-gray-600 mb-4">¿Tienes alguna duda o necesitas ayuda adicional?</p>
                <button class="boton">
                    <i class="fas fa-envelope mr-2"></i> Contáctanos
                </button>
            </div>
        </div>
    </div>

    <script>
        // Variables de estado
        let sidebarPinned = false;
        
        // Elementos del DOM
        const sidebar = document.getElementById('sidebar');
        const contentArea = document.getElementById('contentArea');
        const pinButton = document.getElementById('pinSidebar');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        
        // Función para manejar el redimensionamiento de la pantalla
        function handleResize() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('collapsed');
                contentArea.style.marginLeft = '0';
            } else {
                if (sidebarPinned) {
                    sidebar.classList.remove('collapsed');
                    contentArea.style.marginLeft = '250px';
                } else {
                    sidebar.classList.add('collapsed');
                    contentArea.style.marginLeft = '70px';
                }
            }
        }
        
        // Evento para anclar/desanclar el sidebar
        pinButton.addEventListener('click', () => {
            sidebarPinned = !sidebarPinned;
            if (window.innerWidth > 768) {
                if (sidebarPinned) {
                    sidebar.classList.remove('collapsed');
                    contentArea.style.marginLeft = '250px';
                } else {
                    sidebar.classList.add('collapsed');
                    contentArea.style.marginLeft = '70px';
                }
            }
        });
        
        // Evento para mostrar/ocultar el menú en móviles
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-show');
        });
        
        // Cerrar menú al hacer clic fuera en móviles
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && !sidebar.contains(e.target) && e.target !== mobileMenuBtn) {
                sidebar.classList.remove('mobile-show');
            }
        });
        
        // Inicialización y listeners
        window.addEventListener('resize', handleResize);
        handleResize(); // Ejecutar al cargar la página
    </script>
</body>
</html>