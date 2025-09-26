<!-- Footer -->
    <footer class="footer" style="width:100%;">
        <div class="container mx-auto max-w-4xl px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <img src="img/logo.png" alt="Logo IMESYS" class="h-10">
                    <p class="mt-2 text-sm">Sistema de salud inteligente - Panel Médico</p>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-sm">© 2023 IMESYS. Todos los derechos reservados.</p>
                    <p class="text-sm mt-1">imesysapp@gmail.com | Tel: +1 234 567 890</p>
                </div>
            </div>
        </div>
    </footer>
    <style>
        @media (min-width: 1024px) {
            footer.footer {
                margin-left: 270px !important;
                width: calc(100% - 270px) !important;
            }
        }
    </style>

    <script>
        // Elementos del DOM
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menuToggle');
        const overlay = document.getElementById('overlay');
        const menuItems = document.querySelectorAll('.menu-item');
        
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
    </script>
    <!-- Nuevo Chatbase Bot -->
   <script>
(function(){if(!window.chatbase||window.chatbase("getState")!=="initialized"){window.chatbase=(...arguments)=>{if(!window.chatbase.q){window.chatbase.q=[]}window.chatbase.q.push(arguments)};window.chatbase=new Proxy(window.chatbase,{get(target,prop){if(prop==="q"){return target.q}return(...args)=>target(prop,...args)}})}const onLoad=function(){const script=document.createElement("script");script.src="https://www.chatbase.co/embed.min.js";script.id="ydGG5x5dC3AuKp4ZvGl8C";script.domain="www.chatbase.co";document.body.appendChild(script)};if(document.readyState==="complete"){onLoad()}else{window.addEventListener("load",onLoad)}})();
</script>
</body>
</html>