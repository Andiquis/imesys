<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Plataforma de Salud</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sticky-header {
            position: sticky;
            top: 0;
            background: linear-gradient(to right, #5DD9FC, #0052CC);
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            padding: 10px;
        }
        .logo {
            height: 50px;
            margin-right: 10px;
        }
        .hero-section {
            background: url('') no-repeat center center;
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }
        .mobile-menu {
            display: none;
        }
        @media (max-width: 768px) {
            .desktop-menu {
                display: none;
            }
            .mobile-menu {
                display: block;
            }
            .hero-section {
                height: 80vh;
                padding: 0 20px;
            }
            .hero-section h2 {
                font-size: 2rem;
            }
            .video-container iframe {
                width: 100%;
                height: 250px;
            }
        }
    </style>
</head>
<body class="m-0 p-0 bg-gradient-to-r from-[#5DD9FC] to-[#0052CC]">
    <header class="sticky-header flex justify-between items-center p-4">
        <div class="flex items-center">
            <img src="img/logo.png" alt="Logo" class="logo">
            <h1 class="text-xl md:text-2xl font-bold text-white"></h1>
        </div>
        
        <nav class="desktop-menu">
            <ul class="flex space-x-2 md:space-x-4">
                <li><a href="#inicio" class="text-white hover:text-gray-200 text-sm md:text-base">Inicio</a></li>
                <li><a href="#servicios" class="text-white hover:text-gray-200 text-sm md:text-base">Servicios</a></li>
                <li><a href="#quienes-somos" class="text-white hover:text-gray-200 text-sm md:text-base">Quiénes Somos</a></li>
                <li><a href="#contacto" class="text-white hover:text-gray-200 text-sm md:text-base">Contacto</a></li>
                <li><a href="prelogin.php" class="text-white hover:text-gray-200 text-sm md:text-base">Iniciar Sesión</a></li>
            </ul>
        </nav>
        
        <div class="mobile-menu">
            <button id="menu-toggle" class="text-white focus:outline-none">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>
    </header>
    
    <!-- Mobile Menu Dropdown -->
    <div id="mobile-dropdown" class="hidden bg-blue-600 w-full z-50">
        <ul class="flex flex-col space-y-2 p-4">
            <li><a href="#inicio" class="text-white hover:text-gray-200 block py-2">Inicio</a></li>
            <li><a href="#servicios" class="text-white hover:text-gray-200 block py-2">Servicios</a></li>
            <li><a href="#quienes-somos" class="text-white hover:text-gray-200 block py-2">Quiénes Somos</a></li>
            <li><a href="#contacto" class="text-white hover:text-gray-200 block py-2">Contacto</a></li>
            <li><a href="#inicio" class="text-white hover:text-gray-200 block py-2">Iniciar Sesión</a></li>
        </ul>
    </div>
    
    <section id="inicio" class="hero-section relative bg-cover bg-center bg-no-repeat text-white py-24 px-6" 
    style="background-image: url('img/medicos.avif');">
    <!-- Capa oscura -->
    <div class="absolute inset-0 bg-black bg-opacity-5"></div>

    <!-- Contenido centrado -->
    <div class="relative z-10 text-center max-w-2xl mx-auto">
        <h2 class="text-3xl md:text-4xl font-bold">Bienvenido a IMESYS</h2>
        <p class="text-lg mt-4">Tu plataforma digital</p>
        <br>
        <!-- Botones -->
        <div class="flex flex-col sm:flex-row justify-center gap-4 mb-10">
            <a href="#quienes-somos" class="bg-white text-blue-900 font-semibold px-6 py-3 rounded-md shadow-md hover:bg-blue-50 hover:text-blue-800 transition-colors border border-blue-100">
    Conocer más →
</a>
            <a href="#manual" class="border-2 border-blue-900 text-blue-900 font-semibold px-6 py-3 rounded-md hover:bg-blue-50 transition">
                ▶︎ Ver video
            </a>
        </div>
    </div>
</section>

    
    <!-- Sección "Quiénes Somos" -->
    <section id="quienes-somos" class="py-12 px-4 md:px-6 bg-gray-100">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center">
            <!-- Imágenes a la izquierda -->
            <div class="md:w-1/2 grid grid-cols-2 gap-2 md:gap-4 mb-6 md:mb-0">
                <img src="img/videoframe.png" alt="Equipo médico" class="rounded-lg shadow-lg w-full h-auto">
                <img src="img/centro.jpg" alt="Instalaciones" class="rounded-lg shadow-lg w-full h-auto">
                <img src="img/persona.jpg" alt="Paciente feliz" class="rounded-lg shadow-lg col-span-2 w-full h-auto">
            </div>

            <!-- Contenido a la derecha -->
            <div class="md:w-1/2 md:pl-6 lg:pl-12 px-2">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-800">🩺 Quiénes Somos</h2>
                <p class="text-gray-600 mt-4 text-sm md:text-base">
                    IMESYS (Intelligent Medical System) es una plataforma de salud digital inteligente, diseñada para optimizar la gestión clínica de médicos independientes y mejorar el acceso a servicios médicos para los usuarios. Integra tecnologías de inteligencia artificial para facilitar la gestión de citas, el registro de consultas, la consulta de historiales clínicos y el diagnóstico asistido por imágenes médicas.
Además, ofrece un asistente virtual para orientación médica básica y un espacio digital donde los pacientes pueden encontrar médicos, reservar citas y acceder a información confiable en un solo lugar. IMESYS promueve un ecosistema saludable que conecta médicos y pacientes de forma eficiente, segura y accesible.


                 </p>

                <!-- Objetivos -->
                <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8">
                    <!-- Sección para Usuarios -->
                    <div class="text-center">
                        <div class="bg-pink-200 p-2 md:p-4 rounded-full inline-block">
                            <svg class="w-6 h-6 md:w-8 md:h-8 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-sm md:text-lg font-semibold text-gray-800 mt-2">Asistente Virtual</h3>
                        <p class="text-gray-600 text-xs md:text-sm">IA que brinda información médica, consejos de salud y orientación sobre síntomas.</p>
                    </div>

                    <div class="text-center">
                        <div class="bg-blue-200 p-2 md:p-4 rounded-full inline-block">
                            <svg class="w-6 h-6 md:w-8 md:h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h3 class="text-sm md:text-lg font-semibold text-gray-800 mt-2">Gestión de Citas </h3>
                        <p class="text-gray-600 text-xs md:text-sm">Reservas de consultas médicas con especialistas de forma eficiente.</p>
                    </div>

                    <div class="text-center">
                        <div class="bg-teal-200 p-2 md:p-4 rounded-full inline-block">
                            <svg class="w-6 h-6 md:w-8 md:h-8 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20l-5-5h10l-5 5zm0-16l-5 5h10l-5-5z" />
                            </svg>
                        </div>
                        <h3 class="text-sm md:text-lg font-semibold text-gray-800 mt-2">Consejos y Ayuda</h3>
                        <p class="text-gray-600 text-xs md:text-sm">Artículos y consejos para la prevención y el cuidado de la salud.</p>
                    </div>

                    <div class="text-center">
                        <div class="bg-red-200 p-2 md:p-4 rounded-full inline-block">
                            <svg class="w-6 h-6 md:w-8 md:h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z" />
                            </svg>
                        </div>
                        <h3 class="text-sm md:text-lg font-semibold text-gray-800 mt-2">Recompensas</h3>
                        <p class="text-gray-600 text-xs md:text-sm">Fomento de hábitos saludables con descuentos en farmacias.</p>
                    </div>
                </div>

                <!-- Separador -->
                <div class="col-span-2 md:col-span-3 text-center mt-4">
                    <h3 class="text-lg md:text-xl font-semibold text-gray-900">Exclusivamente para Especialistas ▼</h3>
                </div>
                
               <!-- Objetivos -->
<div class="mt-6 grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-8">
    <!-- Gestión de Pacientes -->
    <div class="text-center">
        <div class="bg-green-200 p-2 md:p-4 rounded-full inline-block">
            <svg class="w-6 h-6 md:w-8 md:h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </div>
        <h3 class="text-sm md:text-lg font-semibold text-gray-800 mt-2">Gestión de Pacientes</h3>
        <p class="text-gray-600 text-xs md:text-sm">Sistema digital para registrar, organizar y administrar información médica.</p>
    </div>

    <!-- Predicción de Enfermedades -->
    <div class="text-center">
        <div class="bg-yellow-200 p-2 md:p-4 rounded-full inline-block">
            <svg class="w-6 h-6 md:w-8 md:h-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z" />
            </svg>
        </div>
        <h3 class="text-sm md:text-lg font-semibold text-gray-800 mt-2">Predicción de Enfermedades</h3>
        <p class="text-gray-600 text-xs md:text-sm">IA para evaluar riesgos de salud como diabetes basada en datos biométricos.</p>
    </div>

    <!-- Análisis de Imágenes -->
    <div class="text-center">
        <div class="bg-purple-200 p-2 md:p-4 rounded-full inline-block">
            <svg class="w-6 h-6 md:w-8 md:h-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 7V9" />
            </svg>
        </div>
        <h3 class="text-sm md:text-lg font-semibold text-gray-800 mt-2">Análisis de Imágenes</h3>
        <p class="text-gray-600 text-xs md:text-sm">Deep learning para detectar neumonía mediante imágenes médicas.</p>
    </div>

    <!-- Historial de Pacientes -->
    <div class="text-center">
        <div class="bg-blue-200 p-2 md:p-4 rounded-full inline-block">
            <svg class="w-6 h-6 md:w-8 md:h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <h3 class="text-sm md:text-lg font-semibold text-gray-800 mt-2">Historial de Pacientes</h3>
        <p class="text-gray-600 text-xs md:text-sm">Acceso y actualización del historial clínico para consultas más precisas.</p>
    </div>

    <!-- Estadísticas -->
    <div class="text-center">
        <div class="bg-red-200 p-2 md:p-4 rounded-full inline-block">
            <svg class="w-6 h-6 md:w-8 md:h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3v18M4 15l7-7 7 7" />
            </svg>
        </div>
        <h3 class="text-sm md:text-lg font-semibold text-gray-800 mt-2">Estadísticas</h3>
        <p class="text-gray-600 text-xs md:text-sm">Visualización de datos clínicos para análisis y toma de decisiones.</p>
    </div>

    <!-- Perfil Profesional -->
    <div class="text-center">
        <div class="bg-indigo-200 p-2 md:p-4 rounded-full inline-block">
            <svg class="w-6 h-6 md:w-8 md:h-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 7V9" />
            </svg>
        </div>
        <h3 class="text-sm md:text-lg font-semibold text-gray-800 mt-2">Perfil Profesional</h3>
        <p class="text-gray-600 text-xs md:text-sm">Gestión del perfil del especialista, trayectoria, y especialidades médicas.</p>
    </div>

    <!-- Agenda Médica -->
    <div class="text-center">
        <div class="bg-teal-200 p-2 md:p-4 rounded-full inline-block">
            <svg class="w-6 h-6 md:w-8 md:h-8 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3M16 7V3M4 11h16M4 19h16M4 15h16" />
            </svg>
        </div>
        <h3 class="text-sm md:text-lg font-semibold text-gray-800 mt-2">Agenda Médica</h3>
        <p class="text-gray-600 text-xs md:text-sm">Organización diaria de citas, alertas y planificación de actividades clínicas.</p>
    </div>
</div>

                
            </div>
        </div>
    </section>

    <section id="servicios" class="py-12 px-4 md:px-6 bg-gray-100">
        <div class="max-w-6xl mx-auto text-center">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-800 font-sans">NUESTROS SERVICIOS</h2>
            <p class="text-gray-600 mt-2 text-sm md:text-base">
                IMESYS ofrece soluciones avanzadas para pacientes y médicos, optimizando la atención médica con tecnología de IA.
            </p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mt-8 max-w-6xl mx-auto">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <img src="img/bot.png" alt="Asistente IA" class="w-full h-40 object-cover">
                <div class="p-4">
                    <h3 class="text-lg md:text-xl font-semibold text-gray-800">Asistente IA</h3>
                    <p class="text-gray-600 mt-2 text-sm">Obtén recomendaciones y respuestas inmediatas sobre tu salud con inteligencia artificial.</p>
                </div>
            </div>
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <img src="img/citas.jpg" alt="Gestión de Citas" class="w-full h-40 object-cover">
                <div class="p-4">
                    <h3 class="text-lg md:text-xl font-semibold text-gray-800">Gestión de Citas</h3>
                    <p class="text-gray-600 mt-2 text-sm">Reserva, gestiona y recibe recordatorios de citas médicas en un solo lugar.</p>
                </div>
            </div>
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <img src="img/entidades.png" alt="Búsqueda de Entidades de Salud" class="w-full h-40 object-cover">
                <div class="p-4">
                    <h3 class="text-lg md:text-xl font-semibold text-gray-800">Búsqueda de Entidades</h3>
                    <p class="text-gray-600 mt-2 text-sm">Encuentra hospitales, clínicas y farmacias cercanas de manera rápida y sencilla.</p>
                </div>
            </div>
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <img src="img/diagnosis.jpg" alt="Diagnóstico asistido con IA" class="w-full h-40 object-cover">
                <div class="p-4">
                    <h3 class="text-lg md:text-xl font-semibold text-gray-800">Diagnóstico Asistido</h3>
                    <p class="text-gray-600 mt-2 text-sm">Apoya a los médicos con análisis inteligentes de síntomas e imágenes médicas.</p>
                    <p class="text-gray-600 mt-2 text-sm"><b>Exclusivo para especialistas</b></p>
                </div>
            </div>
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <img src="img/descuentos.jpg" alt="Descuentos en Farmacias" class="w-full h-40 object-cover">
                <div class="p-4">
                    <h3 class="text-lg md:text-xl font-semibold text-gray-800">Descuentos en Farmacias</h3>
                    <p class="text-gray-600 mt-2 text-sm">Aprovecha beneficios exclusivos en farmacias afiliadas con nuestras alianzas.</p>
                </div>
            </div>
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <img src="img/aagenda.png" alt="Descuentos en Farmacias" class="w-full h-40 object-cover">
                <div class="p-4">
                    <h3 class="text-lg md:text-xl font-semibold text-gray-800">Agenda Medica</h3>
                    <p class="text-gray-600 mt-2 text-sm">Organización diaria de citas, alertas y planificación de actividades clínicas.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="w-full bg-white">
    <section class="flex flex-col lg:flex-row items-center py-16 px-4 md:px-6 max-w-screen-xl mx-auto gap-8">
        <!-- Text Content -->
        <div id="manual" class="lg:w-1/2 text-center lg:text-left px-6 space-y-6">
            <div class="space-y-2">
                <span class="inline-flex items-center gap-2 text-blue-600 font-semibold text-sm md:text-base uppercase tracking-wider">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Descubre cómo IMASYS mejora tu salud
                </span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 leading-tight">
                    Atención Médica Digital <br><span class="text-light-600">Rápida y Segura</span>
                </h2>
            </div>
            
            <p class="text-gray-600 text-base md:text-lg leading-relaxed">
                Con tecnología avanzada y profesionales de la salud, ofrecemos atención médica en minutos. 
                Accede a consultas virtuales y diagnóstico con inteligencia artificial sin necesidad de citas previas.
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Feature 1 -->
                <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-blue-50 transition-all">
                    <div class="bg-blue-100 p-2 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">Evaluación de síntomas</h4>
                        <p class="text-sm text-gray-500">Análisis preciso mediante IA</p>
                    </div>
                </div>
                
                <!-- Feature 2 -->
                <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-blue-50 transition-all">
                    <div class="bg-blue-100 p-2 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">Consultas médicas</h4>
                        <p class="text-sm text-gray-500">En línea las 24/7</p>
                    </div>
                </div>
                
                <!-- Feature 3 -->
                <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-blue-50 transition-all">
                    <div class="bg-blue-100 p-2 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">Análisis de imágenes</h4>
                        <p class="text-sm text-gray-500">Diagnóstico asistido por IA</p>
                    </div>
                </div>
                
                <!-- Feature 4 -->
                <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-blue-50 transition-all">
                    <div class="bg-blue-100 p-2 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">Recetas electrónicas</h4>
                        <p class="text-sm text-gray-500">Válidas en farmacias</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Video -->
        <div class="lg:w-1/2 flex justify-center relative px-6">
            <div class="w-full max-w-xl rounded-xl overflow-hidden shadow-lg border border-gray-100 transform hover:shadow-xl transition-all">
                <div class="aspect-w-16 aspect-h-9">
                    <iframe class="w-full h-64 md:h-80 lg:h-96" src="https://www.youtube.com/embed/U4w_mEv3mOU?si=DCzwv8smTrPZ2cUe" 
                        title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
                <div class="bg-white p-4 text-center">
                    <p class="text-sm text-gray-600 font-medium">Mira cómo funciona nuestra plataforma</p>
                </div>
            </div>
        </div>
    </section>
</div>



    <section class="py-12 bg-white text-center px-40">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-800">Conoce a los especialistas más recomendados</h2>
        <p class="text-gray-600 mt-2 text-sm md:text-base">Un equipo de profesionales comprometidos con tu salud</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mt-8">
            <div class="bg-gray-100 rounded-lg overflow-hidden shadow-lg flex flex-col">
                <img src="img/medico1.jpg" alt="Doctor 1" class="w-full h-48 md:h-60 object-cover">
                <div class="p-4">
                    <h3 class="font-bold text-lg">Dra. Rocio Vera</h3>
                    <p class="text-blue-500">Pediatría</p>
                    <p class="text-gray-600 text-xs md:text-sm mt-2">Especialista en atención infantil con más de 10 años de experiencia.</p>
                </div>
            </div>
            <div class="bg-gray-100 rounded-lg overflow-hidden shadow-lg flex flex-col">
                <img src="img/medico2.jpg" alt="Doctor 2" class="w-full h-48 md:h-60 object-cover">
                <div class="p-4">
                    <h3 class="font-bold text-lg">Dra. María López</h3>
                    <p class="text-blue-500">Cardiología</p>
                    <p class="text-gray-600 text-xs md:text-sm mt-2">Experta en enfermedades del corazón y cuidado preventivo.</p>
                </div>
            </div>
            <div class="bg-gray-100 rounded-lg overflow-hidden shadow-lg flex flex-col">
                <img src="img/medico3.jpg" alt="Doctor 3" class="w-full h-48 md:h-60 object-cover">
                <div class="p-4">
                    <h3 class="font-bold text-lg">Dr. Max Quispe</h3>
                    <p class="text-blue-500">Neurología</p>
                    <p class="text-gray-600 text-xs md:text-sm mt-2">Especialista en trastornos del sistema nervioso.</p>
                </div>
            </div>
            <div class="bg-gray-100 rounded-lg overflow-hidden shadow-lg flex flex-col">
                <img src="img/medico4.jpg" alt="Doctor 4" class="w-full h-48 md:h-60 object-cover">
                <div class="p-4">
                    <h3 class="font-bold text-lg">Dr. Ricardo Gómez</h3>
                    <p class="text-blue-500">Dermatología</p>
                    <p class="text-gray-600 text-xs md:text-sm mt-2">Especialista en el cuidado de la piel y tratamientos estéticos.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Sección de Farmacias Asociadas -->
    <section id="farmacias" class="py-12 bg-white px-20">
        <h2 class="text-2xl md:text-3xl font-bold text-center text-gray-800">Farmacias Asociadas</h2>
        <div class="mt-8 overflow-x-auto whitespace-nowrap flex space-x-4 py-2">
            <img src="img/farmacia1.jpg" alt="Farmacia 1" class="h-16 md:h-20 rounded-lg shadow-md">
            <img src="img/farmacia2.jpg" alt="Farmacia 2" class="h-16 md:h-20 rounded-lg shadow-md">
            <img src="img/farmacia3.jpg" alt="Farmacia 3" class="h-16 md:h-20 rounded-lg shadow-md">
            <img src="img/farmacia4.jpg" alt="Farmacia 4" class="h-16 md:h-20 rounded-lg shadow-md">
            <img src="img/farmacia5.jpg" alt="Farmacia 5" class="h-16 md:h-20 rounded-lg shadow-md">
            <img src="img/farmacia6.jpg" alt="Farmacia 5" class="h-16 md:h-20 rounded-lg shadow-md">
            <img src="img/farmacia1.jpg" alt="Farmacia 1" class="h-16 md:h-20 rounded-lg shadow-md">
            <img src="img/farmacia2.jpg" alt="Farmacia 2" class="h-16 md:h-20 rounded-lg shadow-md">
            <img src="img/farmacia4.jpg" alt="Farmacia 4" class="h-16 md:h-20 rounded-lg shadow-md">
        </div>
    </section>
    
    <footer class="bg-gray-900 text-white py-8 px-4">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-start">
            <!-- Sección de enlaces -->
            <div class="md:w-2/3 mb-6 md:mb-0">
                <h3 class="text-lg font-bold mb-2">Enlaces rápidos</h3>
                <ul class="space-y-2">
                    <li><a href="#inicio" class="text-gray-300 hover:text-white text-sm md:text-base">Inicio</a></li>
                    <li><a href="#servicios" class="text-gray-300 hover:text-white text-sm md:text-base">Servicios</a></li>
                    <li><a href="#quienes-somos" class="text-gray-300 hover:text-white text-sm md:text-base">Quiénes Somos</a></li>
                    <li><a href="#contacto" class="text-gray-300 hover:text-white text-sm md:text-base">Contacto</a></li>
                </ul>
            </div>

            <!-- Sección de Contacto -->
            <div id="contacto" class="md:w-1/3 bg-gray-800 p-4 md:p-6 rounded-lg shadow-lg w-full">
                <h3 class="text-lg font-bold mb-4">Contáctanos</h3>
                <p class="text-gray-300 text-xs md:text-sm mb-2">Correo: imesysapp@gmail.com</p>
                <p class="text-gray-300 text-xs md:text-sm mb-2">Teléfono: +51 930173314</p>
                <p class="text-gray-300 text-xs md:text-sm">Dirección: Wanchaq, Cusco</p>
                <div class="flex space-x-4 mt-4">
                    <a href="#" class="text-blue-400 hover:text-blue-600"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-blue-400 hover:text-blue-600"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-blue-400 hover:text-blue-600"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
        <div class="text-center text-gray-400 mt-6 text-xs md:text-sm">
            &copy; 2025 IMESYS - Plataforma de Salud. Todos los derechos reservados.
        </div>
    </footer>

    <script>
        // Toggle mobile menu
        document.getElementById('menu-toggle').addEventListener('click', function() {
            const menu = document.getElementById('mobile-dropdown');
            menu.classList.toggle('hidden');
        });
    </script>

    <script>
(function(){if(!window.chatbase||window.chatbase("getState")!=="initialized"){window.chatbase=(...arguments)=>{if(!window.chatbase.q){window.chatbase.q=[]}window.chatbase.q.push(arguments)};window.chatbase=new Proxy(window.chatbase,{get(target,prop){if(prop==="q"){return target.q}return(...args)=>target(prop,...args)}})}const onLoad=function(){const script=document.createElement("script");script.src="https://www.chatbase.co/embed.min.js";script.id="ydGG5x5dC3AuKp4ZvGl8C";script.domain="www.chatbase.co";document.body.appendChild(script)};if(document.readyState==="complete"){onLoad()}else{window.addEventListener("load",onLoad)}})();
</script>
</body>
</html>