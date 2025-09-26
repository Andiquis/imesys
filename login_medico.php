<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Acceso Médicos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/estilos_login.css">
    <style>
        .contact-icon {
            transition: all 0.3s ease;
            color: #0052CC;
            font-size: 1.5rem;
            margin: 0 10px;
        }
        .contact-icon:hover {
            transform: scale(1.2);
            color: #0097A7;
        }
        .info-panel {
            background-color: #f8fafc;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
        }
    </style>
</head>
<!-- Botón en la esquina superior izquierda -->
  <a href="prelogin.php" 
     class="fixed top-4 left-4 z-50 inline-flex items-center justify-center w-10 h-10 border border-white rounded-full text-white hover:bg-white hover:text-blue-700 transition-all duration-300">
      <i class="fas fa-arrow-left"></i>
  </a>
<body>
    <div class="login-container">
        <!-- Mostrar mensajes de error -->
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <p class="font-bold">Error</p>
                <p>
                    <?php 
                    switch($_GET['error']) {
                        case 'credenciales':
                            echo "Correo o contraseña incorrectos";
                            break;
                        default:
                            echo "Ocurrió un error";
                    }
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Contenedor de formularios -->
        <div class="flex w-[200%]">
            <!-- Formulario de Login -->
            <div class="form-container w-1/2 px-8 py-10">
                <div class="text-center mb-4">
                    <h1 class="text-3xl font-bold text-[#0052CC] mb-2">IMESYS MÉDICOS</h1>
                    <center><img src="img/login.png" alt="Logo IMESYS" width="250" height="150"></center>
                    <p class="text-degradado">PRECISIÓN Y TECNOLOGÍA PARA TU PRÁCTICA MÉDICA</p>
                </div>
                
                <form action="medicos/validar_login_medico.php" method="POST">
                    <div class="mb-6">
                        <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Correo Electrónico</label>
                        <input type="email" id="email" name="email" class="input-field" placeholder="Ingresa tu correo registrado" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Contraseña</label>
                        <input type="password" id="password" name="contrasena" class="input-field" placeholder="Ingresa tu contraseña" required>
                    </div>
                    
                    <div class="text-right mb-6">
                        <a href="#" class="text-sm text-[#0052CC] hover:underline">¿Olvidaste tu contraseña?</a>
                    </div>
                    
                    <button type="submit" class="login-btn">
                        <i class="fas fa-user-md mr-2"></i> Acceder como Médico
                    </button>
                    
                    <div class="divider">
                        <div class="divider-line"></div>
                        <span class="divider-text">o</span>
                        <div class="divider-line"></div>
                    </div>
                    
                    <div class="text-center">
                        <button type="button" onclick="toggleForm()" class="toggle-form">
                            Solicitar acceso
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Panel de Información de Registro -->
            <div class="form-container register-container w-1/2 px-8 py-10">
                <div class="text-center mb-4">
                    <h1 class="text-3xl font-bold text-[#0052CC] mb-2">SOLICITUD DE ACCESO</h1>
                    <center><img src="img/logo.png" alt="Logo IMESYS" width="200" height="100"></center>
                    <p class="text-degradado">ÚNETE A NUESTRA PLATAFORMA MÉDICA</p>
                </div>
                
                <div class="info-panel">
                    <p class="text-gray-700 mb-6 text-lg">
                        Por la seguridad del sistema el acceso es solo para nuestros médicos asociados. 
                        Si deseas unirte a nosotros puedes contactarnos para ayudarte a registrarte y 
                        unirte a nuestra familia maravillosa de Imesys.
                    </p>
                    
                    <!-- Iconos de contacto -->
                    <div class="flex justify-center mt-8">
                        <a href="https://wa.me/51930173314?text=Me%20gustaría%20obtener%20acceso%20a%20IMESYS%20como%20Médico..." 
   class="contact-icon" 
   title="WhatsApp"
   target="_blank" 
   rel="noopener noreferrer">
   <i class="fab fa-whatsapp"></i>
</a>
                        <a href="#" class="contact-icon" title="Facebook">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="tel:+51930173314" class="contact-icon" title="Llamar">
                            <i class="fas fa-phone"></i>
                        </a>
                        <a href="mailto:imesysapp@gmail.com" class="contact-icon" title="Email">
                            <i class="fas fa-envelope"></i>
                        </a>
                        
                    </div>
                    
                    <div class="mt-8">
                        <button onclick="toggleForm()" class="toggle-form text-lg">
                            <i class="fas fa-arrow-left mr-2"></i> Volver al login
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleForm() {
            const loginContainer = document.querySelector('.form-container:not(.register-container)');
            const registerContainer = document.querySelector('.register-container');
            
            loginContainer.classList.toggle('slide-left');
            registerContainer.classList.toggle('slide-right');
            
            // Mover el contenedor principal
            const formsContainer = document.querySelector('.flex.w-\\[200\\%\\]');
            if (loginContainer.classList.contains('slide-left')) {
                formsContainer.style.transform = 'translateX(-50%)';
            } else {
                formsContainer.style.transform = 'translateX(0%)';
            }
        }
    </script>
</body>
</html>