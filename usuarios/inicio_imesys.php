<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

require 'conexion.php';

// Obtener información del usuario incluyendo la foto
$user_id = $_SESSION['id_usuario'];
$stmt = $conexion->prepare("SELECT nombre, apellido, correo, foto FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($nombre, $apellido, $correo, $foto);
$stmt->fetch();
$stmt->close();

// Obtener el especialista del mes (mejor calificado)
$especialista_mes = [];
$stmt_especialista = $conexion->prepare("
    SELECT m.id_medico, m.nombre, m.apellido, m.foto, e.nombre_especialidad, 
           AVG(c.puntuacion) as promedio, COUNT(c.id_clasificacion) as valoraciones
    FROM medicos m
    JOIN especialidades e ON m.id_especialidad = e.id_especialidad
    JOIN clasificacion_medicos c ON m.id_medico = c.id_medico
    GROUP BY m.id_medico
    ORDER BY promedio DESC, valoraciones DESC
    LIMIT 1
");
$stmt_especialista->execute();
$especialista_mes = $stmt_especialista->get_result()->fetch_assoc();
$stmt_especialista->close();

// Obtener los 3 mejores especialistas (excluyendo al especialista del mes si está en el top)
$mejores_especialistas = [];
$stmt_mejores = $conexion->prepare("
    SELECT m.id_medico, m.nombre, m.apellido, m.foto, e.nombre_especialidad, 
           AVG(c.puntuacion) as promedio, COUNT(c.id_clasificacion) as valoraciones
    FROM medicos m
    JOIN especialidades e ON m.id_especialidad = e.id_especialidad
    JOIN clasificacion_medicos c ON m.id_medico = c.id_medico
    WHERE m.id_medico != ?
    GROUP BY m.id_medico
    ORDER BY promedio DESC, valoraciones DESC
    LIMIT 3
");
$especialista_id = $especialista_mes ? $especialista_mes['id_medico'] : 0;
$stmt_mejores->bind_param("i", $especialista_id);
$stmt_mejores->execute();
$mejores_especialistas = $stmt_mejores->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_mejores->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Inicio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="estilos_inicio.css">
    <style>
        @media (min-width: 1024px) {
            .content-area {
                margin-left: 270px !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Barra superior -->
    <?php include 'header_usuarios.php'; ?>

    <!-- Contenido principal -->
    <div id="contentArea" class="content-area">
        <div class="container">
            <!-- Sección de bienvenida y buscador -->
            <div class="search-container">
                <div class="search-inner">
                    <div class="welcome-section">
                        <h1>BIENVENIDO <?php echo strtoupper(htmlspecialchars($nombre)); ?></h1>
                        <p>¿Cómo podemos ayudarte hoy?</p>
                        <div class="search-bar relative">
                            <input type="text" id="generalSearchInput" placeholder="Buscar en la web..." class="w-full">
                            <button id="generalSearchButton">
                                <i class="fas fa-search"></i>
                            </button>
                            <div id="searchResults" class="hidden absolute z-10 w-full mt-2 bg-white rounded-lg shadow-lg max-h-96 overflow-y-auto"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Consejos de salud -->
            <div class="health-cards">
                <div class="health-card blue-card">
                    <h3>Sintomas de la Gastritis</h3>
                    <img src="img/gastritis.png" alt="Sistemas de la Gastritis">
                    <p>¿Cuáles son los sintomas de la gastritis y la gastropatía?</p>
                    <a href="https://www.niddk.nih.gov/health-information/informacion-de-la-salud/enfermedades-digestivas/gastritis-gastropatia/sintomas-causas" class="card-link">
                        Revisa Aquí <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
                
                <div class="health-card green-card">
                    <h3>Últimos avances tecnológicos</h3>
                    <img src="img/tec.jpg" alt="Avances tecnológicos">
                    <p>Descubre las últimas innovaciones en medicina.</p>
                    <a href="https://www.rocheplus.es/innovacion/tecnologia/tendencias-2024.html" class="card-link">
                        Más información <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
                
                <div class="health-card purple-card">
                    <h3>Sistema de salud integrado</h3>
                    <img src="img/sis.png" alt="Sistema de salud">
                    <p>Conoce cómo funciona nuestro sistema de atención.</p>
                    <a href="https://www.gob.pe/sis" class="card-link">
                        Más información <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
                
                <!-- Especialista del mes (dinámico) -->
                <div class="health-card yellow-card">
                    <?php if ($especialista_mes): ?>
                        <h3>Especialista del Mes</h3>
                        <?php 
                        // Definir la URL base
                        $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/imesys/';
                        
                        // Procesar la ruta de la foto
                        if ($especialista_mes['foto']) {
                            // Extraer solo el nombre del archivo por si la BD contiene rutas completas
                            $nombre_archivo = basename($especialista_mes['foto']);
                            $ruta_foto = $base_url . 'uploads/medicos/' . $nombre_archivo;
                            
                            // Verificar si el archivo existe físicamente
                            $ruta_absoluta = $_SERVER['DOCUMENT_ROOT'] . '/imesys/uploads/medicos/' . $nombre_archivo;
                            $imagen_existe = file_exists($ruta_absoluta);
                        }
                        ?>
                        
                        <?php if ($especialista_mes['foto'] && $imagen_existe): ?>
                            <img src="<?= $ruta_foto ?>" 
                                 alt="<?= htmlspecialchars($especialista_mes['nombre'] . ' ' . $especialista_mes['apellido']) ?>"
                                 class="h-32 w-full object-cover">
                        <?php else: ?>
                            <div class="bg-gray-200 text-gray-400 flex items-center justify-center" style="height: 120px;">
                                <i class="fas fa-user-md text-4xl"></i>
                                <?php if ($especialista_mes['foto'] && !$imagen_existe): ?>
                                    <p class="text-xs mt-2">Imagen no encontrada</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <p class="doctor-name"><strong><?= htmlspecialchars($especialista_mes['nombre'] . ' ' . $especialista_mes['apellido']) ?></strong></p>
                        <p class="doctor-info">Especialista en <?= htmlspecialchars($especialista_mes['nombre_especialidad']) ?></p>
                        <div class="doctor-stats">
                            <span>⭐ <?= number_format($especialista_mes['promedio'], 1) ?> (<?= $especialista_mes['valoraciones'] ?>)</span>
                        </div>
                        <a href="detalle_medico.php?id=<?= $especialista_mes['id_medico'] ?>" class="card-link">
                            Ver perfil <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <h3>Especialista del Mes</h3>
                        <div class="bg-gray-200 text-gray-400 flex items-center justify-center" style="height: 120px;">
                            <i class="fas fa-user-md text-4xl"></i>
                        </div>
                        <p class="doctor-info">No hay datos de especialistas aún</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Sección de especialistas destacados -->
            <div class="specialists-section">
                <h2>Top 3 - Mejores Especialistas</h2>
                <div class="doctors-grid">
                    <?php if (!empty($mejores_especialistas)): ?>
                        <?php foreach ($mejores_especialistas as $medico): ?>
                            <div class="doctor-card">
                                <div class="doctor-header">
                                    <?php if ($medico['foto']): ?>
                                        <img src="/imesys/uploads/medicos/<?= htmlspecialchars($medico['foto']) ?>" 
                                             alt="<?= htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']) ?>" 
                                             class="doctor-photo">
                                    <?php else: ?>
                                        <div class="doctor-photo bg-gray-200 text-gray-400 flex items-center justify-center">
                                            <i class="fas fa-user-md text-3xl"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="doctor-title">
                                        <h3><?= htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']) ?></h3>
                                        <p><?= htmlspecialchars($medico['nombre_especialidad']) ?></p>
                                    </div>
                                </div>
                                <p class="doctor-description">Calificación promedio: <?= number_format($medico['promedio'], 1) ?> estrellas</p>
                                <div class="doctor-footer">
                                    <span class="rating">⭐ <?= number_format($medico['promedio'], 1) ?> (<?= $medico['valoraciones'] ?>)</span>
                                    <a href="detalle_medico.php?id=<?= $medico['id_medico'] ?>" class="profile-link">Ver perfil</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-3 text-center py-8">
                            <i class="fas fa-user-md text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">No hay datos de especialistas disponibles</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Sección de contacto -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Contacta con nosotros</h2>
                <p class="text-gray-600 mb-4">¿Tienes alguna duda o necesitas ayuda adicional?</p>
                <a href="https://wa.me/51930173314" target="_blank">
                <button class="boton">
                    <i class="fas fa-phone mr-2"></i> Contáctanos
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer_usuario.php'; ?>

    <script>
        // Elementos del DOM
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menuToggle');
        const overlay = document.getElementById('overlay');
        const menuItems = document.querySelectorAll('.menu-item');
        const chatButton = document.getElementById('chatButton');

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
        if (menuToggle && overlay && sidebar) {
            menuToggle.addEventListener('click', toggleMenu);
            overlay.addEventListener('click', toggleMenu);
        } else {
            console.warn('Falta uno o más elementos del menú: sidebar, menuToggle o overlay');
        }

        // Evento para items del menú
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                menuItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                toggleMenu();
            });
        });

        // Evento para el botón del chat IA
        if (chatButton) {
            chatButton.addEventListener('click', function() {
                alert('Chat IA se abrirá aquí'); // Reemplazar con la funcionalidad real
            });
        } else {
            console.warn('El elemento chatButton no se encontró en el DOM');
        }

        // Buscador general
        document.addEventListener('DOMContentLoaded', function () {
            const generalSearchButton = document.getElementById('generalSearchButton');
            const generalSearchInput = document.getElementById('generalSearchInput');
            const searchResults = document.getElementById('searchResults');

            if (!generalSearchButton || !generalSearchInput || !searchResults) {
                console.warn('Falta uno o más elementos del buscador en el DOM');
                return;
            }

            generalSearchButton.addEventListener('click', performGeneralSearch);
            generalSearchInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    performGeneralSearch();
                }
            });

            function performGeneralSearch() {
                const query = generalSearchInput.value.trim();

                if (!query) {
                    searchResults.innerHTML = `
                        <div class="p-4 text-gray-600">
                            <i class="fas fa-info-circle mr-2"></i>
                            Por favor, ingrese un término de búsqueda
                        </div>
                    `;
                    searchResults.classList.remove('hidden');
                    return;
                }

                searchResults.innerHTML = '<div class="p-4 text-center">Buscando información...</div>';
                searchResults.classList.remove('hidden');

                fetch('general_search.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'query=' + encodeURIComponent(query)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la solicitud: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.error) {
                        searchResults.innerHTML = `
                            <div class="p-4 text-red-600">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                ${data.error}
                            </div>
                        `;
                    } else if (data.length === 0) {
                        searchResults.innerHTML = `
                            <div class="p-4 text-gray-600">
                                <i class="fas fa-info-circle mr-2"></i>
                                No se encontraron resultados para "${query}"
                            </div>
                        `;
                    } else {
                        let html = '';
                        data.forEach(item => {
                            html += `
                                <a href="${item.link}" target="_blank" class="block p-4 hover:bg-gray-100 border-b border-gray-200">
                                    <h3 class="font-semibold text-blue-600">${item.title}</h3>
                                    <p class="text-sm text-gray-600">${item.snippet}</p>
                                    <p class="text-xs text-gray-400 mt-1">${new URL(item.link).hostname}</p>
                                </a>
                            `;
                        });
                        searchResults.innerHTML = html;
                    }
                })
                .catch(error => {
                    console.error('Error en la búsqueda:', error);
                    searchResults.innerHTML = `
                        <div class="p-4 text-red-600">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Error al realizar la búsqueda: ${error.message}. Por favor, intenta nuevamente.
                        </div>
                    `;
                });
            }

            // Cerrar resultados al hacer clic fuera
            document.addEventListener('click', function(e) {
                if (!searchResults.contains(e.target) && e.target !== generalSearchInput && e.target !== generalSearchButton) {
                    searchResults.classList.add('hidden');
                }
            });
        });

        // Desactiva temporalmente los logs de error para que no se muestren en consola (solo visualmente)
        console.error = function() {};
        console.warn = function() {};
    </script>
    <!-- Nuevo Chatbase Bot -->
   <script>
(function(){if(!window.chatbase||window.chatbase("getState")!=="initialized"){window.chatbase=(...arguments)=>{if(!window.chatbase.q){window.chatbase.q=[]}window.chatbase.q.push(arguments)};window.chatbase=new Proxy(window.chatbase,{get(target,prop){if(prop==="q"){return target.q}return(...args)=>target(prop,...args)}})}const onLoad=function(){const script=document.createElement("script");script.src="https://www.chatbase.co/embed.min.js";script.id="ydGG5x5dC3AuKp4ZvGl8C";script.domain="www.chatbase.co";document.body.appendChild(script)};if(document.readyState==="complete"){onLoad()}else{window.addEventListener("load",onLoad)}})();
</script>
</body>
</html>