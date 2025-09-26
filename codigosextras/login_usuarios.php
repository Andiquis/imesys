<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Inicio de Sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 82, 204, 0.2);
            overflow: hidden;
            position: relative;
        }
        
        .form-container {
            transition: transform 0.5s ease-in-out;
        }
        
        .register-container {
            transform: translateX(100%);
        }
        
        .slide-left {
            transform: translateX(-100%);
        }
        
        .slide-right {
            transform: translateX(0%);
        }
        
        .input-field {
            border-bottom: 2px solid #e2e8f0;
            transition: all 0.3s;
        }
        
        .input-field:focus {
            border-bottom-color: #0052CC;
        }
        
        .login-btn {
            background: linear-gradient(to right, #5DD9FC, #0052CC);
            transition: all 0.3s;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 82, 204, 0.3);
        }
        
        .toggle-form {
            color: #0052CC;
            transition: all 0.3s;
        }
        
        .toggle-form:hover {
            text-decoration: underline;
        }
        
        .logoi {
    display: block;
    margin: auto;
    max-width: 100%;
    height: auto;
}

.text-degradado {
    background: linear-gradient(90deg, #5A5EAB, #2CA7D5); /* Morado a azul */
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: bold;
}

    </style>
</head>
<body class="bg-gradient-to-r from-[#5DD9FC] to-[#0052CC] min-h-screen flex items-center justify-center p-4">
    <div class="login-container w-full max-w-md">
        <!-- Contenedor de formularios -->
        <div class="flex w-[200%]">
            <!-- Formulario de Login -->
            <div class="form-container w-1/2 px-8 py-10">
                <div class="text-center mb-4">
                    <h1 class="text-3xl font-bold text-[#0052CC] mb-2">IMESYS</h1>
                    <!-- Imagen circular agregada aquí -->
                    <img src="img/logologin.png" alt="Logo IMESYS" >
                    <p class="text-degradado">PRECISIÓN Y TECNOLOGÍA PARA TU BIENESTAR</p>
                </div>
                
                <form>
                    <div class="mb-6">
                        <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                        <input type="email" id="email" class="input-field w-full px-3 py-2 focus:outline-none" placeholder="Ingresa tu email">
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Contraseña</label>
                        <input type="password" id="password" class="input-field w-full px-3 py-2 focus:outline-none" placeholder="Ingresa tu contraseña">
                    </div>
                    
                    <div class="text-right mb-6">
                        <a href="#" class="text-sm text-[#0052CC] hover:underline">Forgot Password?</a>
                    </div>
                    
                    <button type="button" class="login-btn w-full py-3 rounded-lg text-white font-semibold mb-6">
                        Iniciar Sesión
                    </button>
                    
                    <div class="relative flex items-center justify-center mb-6">
                        <div class="flex-grow border-t border-gray-300"></div>
                        <span class="mx-4 text-gray-500">or</span>
                        <div class="flex-grow border-t border-gray-300"></div>
                    </div>
                    
                    <div class="text-center">
                        <button type="button" onclick="toggleForm()" class="toggle-form text-sm font-medium">
                            Create an account
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Formulario de Registro (también con imagen circular) -->
            <div class="form-container register-container w-1/2 px-8 py-10">
                <div class="text-center mb-4">
                   
                    <!-- Imagen circular agregada aquí -->
                    <center><img src="img/logo.png" alt="Logoi" width="200" height="100"></center>

                    <p class="text-degradado">PRECISIÓN Y TECNOLOGÍA PARA TU BIENESTAR</p>

                </div>
                
                <form>
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 text-sm font-medium mb-2">Nombre Completo</label>
                        <input type="text" id="name" class="input-field w-full px-3 py-2 focus:outline-none" placeholder="Ingresa tu nombre">
                    </div>
                    
                    <div class="mb-4">
                        <label for="reg-email" class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                        <input type="email" id="reg-email" class="input-field w-full px-3 py-2 focus:outline-none" placeholder="Ingresa tu email">
                    </div>
                    
                    <div class="mb-4">
                        <label for="reg-password" class="block text-gray-700 text-sm font-medium mb-2">Contraseña</label>
                        <input type="password" id="reg-password" class="input-field w-full px-3 py-2 focus:outline-none" placeholder="Crea una contraseña">
                    </div>
                    
                    <div class="mb-6">
                        <label for="confirm-password" class="block text-gray-700 text-sm font-medium mb-2">Confirmar Contraseña</label>
                        <input type="password" id="confirm-password" class="input-field w-full px-3 py-2 focus:outline-none" placeholder="Repite tu contraseña">
                    </div>
                    
                    <button type="button" class="login-btn w-full py-3 rounded-lg text-white font-semibold mb-6">
                        Registrarse
                    </button>
                    
                    <div class="relative flex items-center justify-center mb-6">
                        <div class="flex-grow border-t border-gray-300"></div>
                        <span class="mx-4 text-gray-500">or</span>
                        <div class="flex-grow border-t border-gray-300"></div>
                    </div>
                    
                    <div class="text-center">
                        <button type="button" onclick="toggleForm()" class="toggle-form text-sm font-medium">
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