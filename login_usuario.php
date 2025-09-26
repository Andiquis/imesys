<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Inicio de Sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/estilos_login.css">
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
                        case 'registro':
                            echo "Error al registrar. Inténtalo de nuevo";
                            break;
                        case 'email':
                            echo "El correo electrónico ya está registrado";
                            break;
                        case 'contrasena':
                            echo "Las contraseñas no coinciden";
                            break;
                        default:
                            echo "Ocurrió un error";
                    }
                    ?>
                </p>
            </div>
        <?php endif; ?>
        
        <!-- Mostrar mensajes de éxito -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 'registro'): ?>
            <div class="alert alert-success">
                <p class="font-bold">¡Registro exitoso!</p>
                <p>Ahora puedes iniciar sesión con tus credenciales</p>
            </div>
        <?php endif; ?>

        <!-- Contenedor de formularios -->
        <div class="flex w-[200%]">
            <!-- Formulario de Login -->
            <div class="form-container">
                <div class="text-center mb-4">
                    <h1 class="text-3xl font-bold text-[#0052CC] mb-2">IMESYS</h1>
                    <center><img src="img/login.png" alt="Logoi" width="250" height="150"></center>
                    <p class="text-degradado">PRECISIÓN Y TECNOLOGÍA PARA TU BIENESTAR</p>
                </div>
                
                <form action="validar_login.php" method="POST">
                    <div class="mb-6">
                        <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                        <input type="email" id="email" name="email" class="input-field" placeholder="Ingresa tu email" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Contraseña</label>
                        <input type="password" id="password" name="contrasena" class="input-field" placeholder="Ingresa tu contraseña" required>
                    </div>
                    
                    <div class="text-right mb-6">
                        <a href="#" class="text-sm text-[#0052CC] hover:underline">Forgot Password?</a>
                    </div>
                    
                    <button type="submit" class="login-btn">
                        Iniciar Sesión
                    </button>
                    
                    <div class="divider">
                        <div class="divider-line"></div>
                        <span class="divider-text">or</span>
                        <div class="divider-line"></div>
                    </div>
                    
                    <div class="text-center">
                        <button type="button" onclick="toggleForm()" class="toggle-form">
                            Crear una Cuenta
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Formulario de Registro -->
            <div class="form-container register-container">
                <div class="text-center mb-4">
                    <h1 class="text-3xl font-bold text-[#0052CC] mb-2"></h1>
                    <center><img src="img/logo.png" alt="Logoi" width="200" height="100"></center>
                    <p class="text-degradado">PRECISIÓN Y TECNOLOGÍA PARA TU BIENESTAR</p>
                </div>
                
                <form action="registrar_usuario.php" method="POST">
                    <div class="mb-4">
                        <label for="nombre" class="block text-gray-700 text-sm font-medium mb-2">Nombre</label>
                        <input type="text" id="nombre" name="nombre" class="input-field" placeholder="Ingresa tu nombre" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="apellido" class="block text-gray-700 text-sm font-medium mb-2">Apellido</label>
                        <input type="text" id="apellido" name="apellido" class="input-field" placeholder="Ingresa tu apellido" required>
                    </div>

                    <div class="mb-4">
                        <label for="dni" class="block text-gray-700 text-sm font-medium mb-2">Dni</label>
                        <input type="text" id="dni" name="dni" class="input-field" placeholder="Ingresa tu número de Dni" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="reg-email" class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                        <input type="email" id="reg-email" name="email" class="input-field" placeholder="Ingresa tu email" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="reg-password" class="block text-gray-700 text-sm font-medium mb-2">Contraseña</label>
                        <input type="password" id="reg-password" name="contrasena" class="input-field" placeholder="Crea una contraseña" required>
                    </div>
                    
                    <div class="mb-6">
                        <label for="confirm-password" class="block text-gray-700 text-sm font-medium mb-2">Confirmar Contraseña</label>
                        <input type="password" id="confirm-password" name="confirmar_contrasena" class="input-field" placeholder="Repite tu contraseña" required>
                    </div>

                    <div class="mb-4">
                                <label for="foto" class="block text-gray-700 font-medium mb-2">Foto de Perfil</label>
                                <input type="file" id="foto" name="foto" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-sm text-gray-500 mt-1">Formatos aceptados: JPG, PNG, GIF</p>
                    </div>
                    
                    <button type="submit" class="login-btn">
                        Registrarse
                    </button>
                    
                    <div class="divider">
                        <div class="divider-line"></div>
                        <span class="divider-text">or</span>
                        <div class="divider-line"></div>
                    </div>
                    
                    <div class="text-center">
                        <button type="button" onclick="toggleForm()" class="toggle-form">
                            Ya tengo una cuenta
                        </button>
                    </div>
                </form>
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