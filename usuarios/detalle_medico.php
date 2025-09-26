<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

require 'conexion.php';

// Obtener ID del médico de la URL
$medico_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Obtener información del médico
$medico = [];
$stmt_medico = $conexion->prepare("
    SELECT m.id_medico, m.nombre, m.apellido, m.foto, m.direccion_consultorio, m.telefono, m.correo,
           e.nombre_especialidad, 
           AVG(c.puntuacion) as promedio, COUNT(c.id_clasificacion) as total_valoraciones
    FROM medicos m
    JOIN especialidades e ON m.id_especialidad = e.id_especialidad
    LEFT JOIN clasificacion_medicos c ON m.id_medico = c.id_medico
    WHERE m.id_medico = ?
    GROUP BY m.id_medico
");
$stmt_medico->bind_param("i", $medico_id);
$stmt_medico->execute();
$medico = $stmt_medico->get_result()->fetch_assoc();
$stmt_medico->close();

// Obtener comentarios del médico
$comentarios_medico = [];
$stmt_comentarios = $conexion->prepare("
    SELECT c.puntuacion, c.comentario, c.fecha_clasificacion, c.anonimo,
           IF(c.anonimo = 1, 'Anónimo', CONCAT(u.nombre, ' ', u.apellido)) as nombre_usuario,
           u.foto as usuario_foto
    FROM clasificacion_medicos c
    LEFT JOIN usuarios u ON c.id_usuario = u.id_usuario
    WHERE c.id_medico = ?
    ORDER BY c.fecha_clasificacion DESC
");
$stmt_comentarios->bind_param("i", $medico_id);
$stmt_comentarios->execute();
$comentarios_medico = $stmt_comentarios->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_comentarios->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Detalle Médico</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .star-rating {
            direction: rtl;
            display: inline-block;
        }
        .star-rating input[type=radio] {
            display: none;
        }
        .star-rating label {
            color: #bbb;
            font-size: 24px;
            padding: 0 2px;
            cursor: pointer;
        }
        .star-rating input[type=radio]:checked ~ label {
            color: #f2b01e;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #f2b01e;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Barra superior -->
    <?php include 'header_usuarios.php'; ?>

    <!-- Contenido principal -->
    <div id="contentArea" class="content-area">
        <div class="container mx-auto px-4 py-8">
            <?php if ($medico): ?>
                <!-- Información del médico -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <div class="flex flex-col md:flex-row">
                        <div class="md:w-1/4 mb-6 md:mb-0">
                            <?php if ($medico['foto']): ?>
                                <img src="uploads/medicos/<?php echo htmlspecialchars($medico['foto']); ?>" 
                                     alt="<?php echo htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']); ?>" 
                                     class="w-full rounded-lg">
                            <?php else: ?>
                                <div class="bg-gray-200 rounded-lg flex items-center justify-center" style="height: 200px;">
                                    <i class="fas fa-user-md text-5xl text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="md:w-3/4 md:pl-8">
                            <h1 class="text-2xl font-bold text-gray-800 mb-2">
                                <?php echo htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']); ?>
                            </h1>
                            <p class="text-lg text-blue-600 font-medium mb-4">
                                <?php echo htmlspecialchars($medico['nombre_especialidad']); ?>
                            </p>
                            
                            <div class="flex items-center mb-4">
                                <div class="text-yellow-400 mr-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?php echo $i > round($medico['promedio'] ?? 0) ? '-half-alt' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-gray-600">
                                    <?php echo number_format($medico['promedio'] ?? 0, 1); ?> (<?php echo $medico['total_valoraciones'] ?? 0; ?> valoraciones)
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div>
                                    <p class="text-gray-600"><i class="fas fa-map-marker-alt text-blue-500 mr-2"></i> 
                                        <?php echo htmlspecialchars($medico['direccion_consultorio']); ?>
                                    </p>
                                    <p class="text-gray-600"><i class="fas fa-phone text-blue-500 mr-2"></i> 
                                        <?php echo htmlspecialchars($medico['telefono']); ?>
                                    </p>
                                    <p class="text-gray-600"><i class="fas fa-envelope text-blue-500 mr-2"></i> 
                                        <?php echo htmlspecialchars($medico['correo']); ?>
                                    </p>
                                </div>
                                <div>
                                    <a href="agendar_cita.php?medico=<?php echo $medico['id_medico']; ?>" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-300">
                                        <i class="fas fa-calendar-check mr-2"></i> Agendar Cita
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Comentarios del médico -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800">Comentarios y Valoraciones</h2>
                    
                    <!-- Formulario para dejar comentario -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-8 border border-gray-200">
                        <h3 class="font-medium text-gray-800 mb-3">Deja tu comentario</h3>
                        <form method="POST" action="procesar_comentario.php">
                            <input type="hidden" name="medico_id" value="<?php echo $medico['id_medico']; ?>">
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 font-medium mb-2">Calificación:</label>
                                <div class="star-rating">
                                    <input type="radio" id="star5-<?php echo $medico['id_medico']; ?>" name="puntuacion" value="5" required />
                                    <label for="star5-<?php echo $medico['id_medico']; ?>" title="5 estrellas"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star4-<?php echo $medico['id_medico']; ?>" name="puntuacion" value="4" />
                                    <label for="star4-<?php echo $medico['id_medico']; ?>" title="4 estrellas"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star3-<?php echo $medico['id_medico']; ?>" name="puntuacion" value="3" />
                                    <label for="star3-<?php echo $medico['id_medico']; ?>" title="3 estrellas"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star2-<?php echo $medico['id_medico']; ?>" name="puntuacion" value="2" />
                                    <label for="star2-<?php echo $medico['id_medico']; ?>" title="2 estrellas"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star1-<?php echo $medico['id_medico']; ?>" name="puntuacion" value="1" />
                                    <label for="star1-<?php echo $medico['id_medico']; ?>" title="1 estrella"><i class="fas fa-star"></i></label>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="comentario" class="block text-gray-700 font-medium mb-2">Comentario:</label>
                                <textarea id="comentario" name="comentario" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Describe tu experiencia con este médico..." required></textarea>
                            </div>
                            
                            <div class="mb-4 flex items-center">
                                <input type="checkbox" id="anonimo" name="anonimo" class="mr-2">
                                <label for="anonimo" class="text-gray-700">Publicar como anónimo</label>
                            </div>
                            <!-- Añade esto dentro de la sección del formulario de comentario, antes del botón de enviar -->
<div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
    <i class="fas fa-coins text-blue-600 mr-2"></i>
    ¡Ganarás <?php 
        require_once 'puntos.php'; 
        $sistemaPuntos = new SistemaPuntos($conexion);
        echo $sistemaPuntos->obtenerConfig('puntos_por_comentario'); 
    ?> puntos por dejar tu comentario!
</div>
                            
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-300">
                                Enviar Comentario
                            </button>
                        </form>
                    </div>
                    
                    <!-- Lista de comentarios -->
                    <?php if (!empty($comentarios_medico)): ?>
                        <div class="space-y-4">
                            <?php foreach ($comentarios_medico as $comentario): ?>
                                <div class="border-b border-gray-200 pb-4">
                                    <div class="flex items-start mb-2">
                                        <?php if (!$comentario['anonimo'] && $comentario['usuario_foto']): ?>
                                            <img src="uploads/usuarios/<?php echo htmlspecialchars($comentario['usuario_foto']); ?>" 
                                                 alt="<?php echo htmlspecialchars($comentario['nombre_usuario']); ?>" 
                                                 class="w-10 h-10 rounded-full mr-3">
                                        <?php else: ?>
                                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                                <i class="fas fa-user text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <h3 class="font-medium text-gray-800">
                                                    <?php echo htmlspecialchars($comentario['nombre_usuario']); ?>
                                                </h3>
                                                <span class="text-gray-500 text-sm">
                                                    <?php echo date('d/m/Y H:i', strtotime($comentario['fecha_clasificacion'])); ?>
                                                </span>
                                            </div>
                                            <div class="text-yellow-400 mb-1">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star<?php echo $i > $comentario['puntuacion'] ? '-half-alt' : ''; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-comment-slash text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">Este médico aún no tiene comentarios. Sé el primero en opinar.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow-md p-6 text-center">
                    <i class="fas fa-user-md text-5xl text-gray-300 mb-4"></i>
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Médico no encontrado</h2>
                    <p class="text-gray-600">El médico que buscas no existe o no está disponible.</p>
                    <a href="index.php" class="inline-block mt-4 text-blue-600 hover:text-blue-800 font-medium">
                        <i class="fas fa-arrow-left mr-2"></i> Volver al inicio
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

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
</body>
</html>