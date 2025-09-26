<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Selección de Perfil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card {
            transition: all 0.3s ease;
            transform: translateY(0);
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<!-- Botón en la esquina superior izquierda -->
  <a href="index.php" 
     class="fixed top-4 left-4 z-50 inline-flex items-center justify-center w-10 h-10 border border-white rounded-full text-white hover:bg-white hover:text-blue-700 transition-all duration-300">
      <i class="fas fa-arrow-left"></i>
  </a>
<br><br>
<body class="bg-gradient-to-r from-[#5DD9FC] to-[#0052CC] min-h-screen flex flex-col items-center justify-center p-4">
    <div class="text-center mb-8">
        <img src="img/logo.png" alt="Logo IMESYS" class="w-24 h-24 mx-auto mb-4 rounded-full border-0  shadow-lg">
        <h1 class="text-4xl font-bold text-white">IMESYS</h1>
        <p class="text-white opacity-90 mt-2">Plataforma Integral de Salud</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl w-full">
        <!-- Tarjeta Usuario -->
        <a href="login_usuario.php" class="card bg-white rounded-xl p-8 text-center shadow-md flex flex-col items-center cursor-pointer">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-user text-blue-600 text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">Desea iniciar sesión como Usuario</h2>
            <p class="text-gray-600">Accede a tus servicios médicos y gestiona tu salud</p>
            <div class="mt-6 w-full">
                <div class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-[#5DD9FC] to-[#0052CC] text-white rounded-full">
                    Acceder <i class="fas fa-arrow-right ml-2"></i>
                </div>
            </div>
        </a>

        <!-- Tarjeta Médico -->
        <a href="login_medico.php" class="card bg-white rounded-xl p-8 text-center shadow-md flex flex-col items-center cursor-pointer">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-user-md text-green-600 text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">Desea iniciar sesión como Médico</h2>
            <p class="text-gray-600">Accede al panel profesional y gestiona tus pacientes</p>
            <div class="mt-6 w-full">
                <div class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-[#5DD9FC] to-[#0052CC] text-white rounded-full">
                    Acceder <i class="fas fa-arrow-right ml-2"></i>
                </div>
            </div>
        </a>
    </div><br>


    <div class="mt-12 text-center text-white opacity-80">
        <p>Acceder como <a href="farmacias/login_farmacia.php" class="underline">Farmacia </a></p>
    </div><br>
   

</body>
</html>