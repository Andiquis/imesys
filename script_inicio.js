document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.getElementById('menuToggle');
    const overlay = document.getElementById('overlay');
    const menuItems = document.querySelectorAll('.menu-item');
    const chatButton = document.getElementById('chatButton');
    const contentArea = document.getElementById('contentArea');
    
    // Estado del menú
    let menuOpen = false;
    
    // Función para alternar el menú
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
    
    // Eventos
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
    
    // Evento para el botón del chat IA
    chatButton.addEventListener('click', function() {
        // Aquí puedes implementar la lógica para abrir el chat IA
        alert('Chat IA se abrirá aquí');
        // Ejemplo: window.location.href = 'chat_ia.php';
    });
    
    // Detectar tamaño de pantalla y ajustar el menú
    function handleResize() {
        if (window.innerWidth >= 1024) {
            sidebar.classList.add('desktop');
            if (menuOpen) {
                toggleMenu();
            }
        } else {
            sidebar.classList.remove('desktop');
        }
    }
    
    // Ejecutar al cargar y al redimensionar
    window.addEventListener('resize', handleResize);
    handleResize();
});