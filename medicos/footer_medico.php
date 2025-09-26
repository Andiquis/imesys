    <!-- Footer -->
    <footer class="footer">
        <style>
            /* Sobrescribir completamente los estilos del footer */
            footer.footer {
                background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
                padding: 2rem 1rem !important;
                margin-top: 2rem !important;
                border-top: 1px solid rgba(0, 0, 0, 0.1) !important;
                position: relative !important;
                width: 100% !important;
                margin-left: 0 !important;
                transition: all 0.3s ease !important;
            }
            
            /* Desktop: Ajustar footer para sidebar con mayor especificidad */
            @media (min-width: 1024px) {
                body footer.footer {
                    margin-left: 280px !important; /* Ancho del sidebar */
                    width: calc(100% - 280px) !important;
                    padding: 2rem !important;
                }
            }
            
            /* Móvil y tablet: Footer completo con mayor especificidad */
            @media (max-width: 1023px) {
                body footer.footer {
                    margin-left: 0 !important;
                    width: 100% !important;
                    padding: 1.5rem 1rem !important;
                }
            }
            
            /* Contenedor del footer */
            footer.footer .container {
                max-width: 100% !important;
                margin: 0 auto !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            
            /* Asegurar que no haya conflictos con otros estilos */
            footer.footer * {
                box-sizing: border-box !important;
            }
        </style>
        <div class="container">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <img src="img/logo.png" alt="Logo IMESYS" class="h-10">
                    <p class="mt-2 text-sm text-gray-600">Sistema de salud inteligente - Panel Médico</p>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-sm text-gray-700">© 2025 IMESYS. Todos los derechos reservados.</p>
                    <p class="text-sm mt-1 text-gray-600">imesysapp@gmail.com | Tel: +51 930173314</p>
                </div>
            </div>
        </div>
    </footer>

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