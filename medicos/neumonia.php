<?php
ob_start(); // Iniciar búfer de salida para evitar salida no deseada
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['id_medico'])) {
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
    <title>IMESYS - Detección de Neumonía con IA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="estilos_inicio.css">
    <style>
        /* Eliminar estilos redundantes, usar main-content para layout consistente */
    </style>
</head>
<body class="bg-white">
       <!-- Barra superior -->
    <?php include 'header_medico.php'; ?>

    

    <!-- Contenido principal -->
    <div id="contentArea" class="main-content">
        <?php include 'index_neumonia.php'; ?>
    </div>

    <!-- Footer -->
   <?php include 'footer_medico.php'; ?>

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
        if (chatButton) {
            chatButton.addEventListener('click', function() {
                // Crear notificación elegante adaptada al tema claro
                const notification = document.createElement('div');
                notification.className = 'fixed top-20 right-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-lg shadow-xl transform translate-x-full transition-transform duration-300 z-50 border border-blue-200';
                notification.innerHTML = '<i class="fas fa-brain mr-2"></i>Sistema de IA para Neumonía activo';
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.style.transform = 'translateX(0)';
                }, 100);
                
                setTimeout(() => {
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            });
        }
    </script>
</body>
</html>