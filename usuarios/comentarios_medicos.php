<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

require 'conexion.php';

// Obtener información del usuario
$user_id = $_SESSION['id_usuario'];
$stmt_user = $conexion->prepare("SELECT nombre, apellido, foto FROM usuarios WHERE id_usuario = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$stmt_user->bind_result($user_nombre, $user_apellido, $user_foto);
$stmt_user->fetch();
$stmt_user->close();

// Procesar el formulario de comentarios si se envió
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_comment'])) {
    $medico_id = $_POST['medico_id'];
    $puntuacion = $_POST['puntuacion'];
    $comentario = $_POST['comentario'];
    $anonimo = isset($_POST['anonimo']) ? 1 : 0;
    
    $stmt = $conexion->prepare("INSERT INTO clasificacion_medicos (id_usuario, id_medico, puntuacion, comentario, anonimo) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisi", $user_id, $medico_id, $puntuacion, $comentario, $anonimo);
    
    if ($stmt->execute()) {
        $success_msg = "¡Gracias por tu comentario!";
    } else {
        $error_msg = "Error al enviar el comentario. Por favor intenta nuevamente.";
    }
    $stmt->close();
}

// Obtener lista de médicos para el select
$medicos = [];
$stmt_medicos = $conexion->prepare("
    SELECT m.id_medico, m.nombre, m.apellido, e.nombre_especialidad 
    FROM medicos m
    JOIN especialidades e ON m.id_especialidad = e.id_especialidad
    ORDER BY m.apellido, m.nombre
");
$stmt_medicos->execute();
$result = $stmt_medicos->get_result();
while ($row = $result->fetch_assoc()) {
    $medicos[] = $row;
}
$stmt_medicos->close();

// Obtener últimos comentarios
$ultimos_comentarios = [];
$stmt_comentarios = $conexion->prepare("
    SELECT c.id_clasificacion, c.puntuacion, c.comentario, c.fecha_clasificacion, c.anonimo,
           IF(c.anonimo = 1, 'Anónimo', CONCAT(u.nombre, ' ', u.apellido)) as nombre_usuario,
           m.nombre as medico_nombre, m.apellido as medico_apellido, m.foto as medico_foto,
           e.nombre_especialidad
    FROM clasificacion_medicos c
    JOIN medicos m ON c.id_medico = m.id_medico
    JOIN especialidades e ON m.id_especialidad = e.id_especialidad
    LEFT JOIN usuarios u ON c.id_usuario = u.id_usuario
    ORDER BY c.fecha_clasificacion DESC
    LIMIT 10
");
$stmt_comentarios->execute();
$ultimos_comentarios = $stmt_comentarios->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_comentarios->close();

// Obtener ranking de médicos (top 10)
$ranking_medicos = [];
$stmt_ranking = $conexion->prepare("
    SELECT m.id_medico, m.nombre, m.apellido, m.foto, e.nombre_especialidad,
           AVG(c.puntuacion) as promedio, COUNT(c.id_clasificacion) as total_valoraciones
    FROM medicos m
    JOIN especialidades e ON m.id_especialidad = e.id_especialidad
    LEFT JOIN clasificacion_medicos c ON m.id_medico = c.id_medico
    GROUP BY m.id_medico
    HAVING COUNT(c.id_clasificacion) > 0
    ORDER BY promedio DESC, total_valoraciones DESC
    LIMIT 10
");
$stmt_ranking->execute();
$ranking_medicos = $stmt_ranking->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_ranking->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Comentarios y Calificaciones</title>
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
        .comment-card {
            transition: all 0.3s ease;
        }
        .comment-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        @media (min-width: 1024px) {
            .content-area {
                margin-left: 270px !important;
            }
            footer.footer {
                margin-left: 270px !important;
                width: calc(100% - 270px) !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Barra superior -->
    <?php include 'header_usuarios.php'; ?>

    <!-- Contenido principal -->
    <div id="contentArea" class="content-area">
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Comentarios y Calificaciones</h1>
            
            <!-- Sección para dejar comentario -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Deja tu comentario</h2>
                
                <?php if (isset($success_msg)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_msg)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $error_msg; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="comentarios_medicos.php">
                    <div class="mb-4">
                        <label for="medico_id" class="block text-gray-700 font-medium mb-2">Selecciona un médico:</label>
                        <select id="medico_id" name="medico_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">-- Selecciona un médico --</option>
                            <?php foreach ($medicos as $medico): ?>
                                <option value="<?php echo $medico['id_medico']; ?>">
                                    <?php echo htmlspecialchars($medico['apellido'] . ', ' . $medico['nombre'] . ' - ' . $medico['nombre_especialidad']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Calificación:</label>
                        <div class="star-rating">
                            <input type="radio" id="star5" name="puntuacion" value="5" required />
                            <label for="star5" title="5 estrellas"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star4" name="puntuacion" value="4" />
                            <label for="star4" title="4 estrellas"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star3" name="puntuacion" value="3" />
                            <label for="star3" title="3 estrellas"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star2" name="puntuacion" value="2" />
                            <label for="star2" title="2 estrellas"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star1" name="puntuacion" value="1" />
                            <label for="star1" title="1 estrella"><i class="fas fa-star"></i></label>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="comentario" class="block text-gray-700 font-medium mb-2">Comentario:</label>
                        <textarea id="comentario" name="comentario" rows="4" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Describe tu experiencia con este médico..." required></textarea>
                    </div>
                    
                    <div class="mb-4 flex items-center">
                        <input type="checkbox" id="anonimo" name="anonimo" class="mr-2">
                        <label for="anonimo" class="text-gray-700">Publicar como anónimo</label>
                    </div>
                    
                    <button type="submit" name="submit_comment" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-300">
                        Enviar Comentario
                    </button>
                </form>
            </div>
            
            <!-- Sección de últimos comentarios -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Últimos Comentarios</h2>
                
                <?php if (!empty($ultimos_comentarios)): ?>
                    <div class="space-y-4">
                        <?php foreach ($ultimos_comentarios as $comentario): ?>
                            <div class="comment-card bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <div class="flex items-start mb-2">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-1">
                                            <div class="text-yellow-400 mr-2">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star<?php echo $i > $comentario['puntuacion'] ? '-half-alt' : ''; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="text-gray-500 text-sm ml-2">
                                                <?php echo date('d/m/Y H:i', strtotime($comentario['fecha_clasificacion'])); ?>
                                            </span>
                                        </div>
                                        <h3 class="font-semibold text-gray-800">
                                            <?php echo htmlspecialchars($comentario['medico_nombre'] . ' ' . $comentario['medico_apellido']); ?>
                                            <span class="text-sm text-gray-500 ml-2">(<?php echo htmlspecialchars($comentario['nombre_especialidad']); ?>)</span>
                                        </h3>
                                        <p class="text-gray-600 mt-2"><?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-500">Por: <?php echo htmlspecialchars($comentario['nombre_usuario']); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-comment-slash text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">Aún no hay comentarios. Sé el primero en opinar.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sección de ranking de médicos -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Top Médicos Mejor Calificados</h2>
                
                <?php if (!empty($ranking_medicos)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($ranking_medicos as $medico): ?>
                            <div class="comment-card bg-gray-50 rounded-lg p-4 border border-gray-200 hover:border-blue-300">
                                <div class="flex items-center mb-3">
                                    <?php if ($medico['foto']): ?>
                                        <img src="uploads/medicos/<?php echo htmlspecialchars($medico['foto']); ?>" 
                                             alt="<?php echo htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']); ?>" 
                                             class="w-16 h-16 rounded-full object-cover mr-4">
                                    <?php else: ?>
                                        <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center mr-4">
                                            <i class="fas fa-user-md text-2xl text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <h3 class="font-semibold text-gray-800">
                                            <?php echo htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']); ?>
                                        </h3>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($medico['nombre_especialidad']); ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="text-yellow-400 mr-2">
                                            <i class="fas fa-star"></i>
                                            <span class="text-gray-800 ml-1"><?php echo number_format($medico['promedio'], 1); ?></span>
                                        </div>
                                        <span class="text-sm text-gray-500">(<?php echo $medico['total_valoraciones']; ?> valoraciones)</span>
                                    </div>
                                    <a href="detalle_medico.php?id=<?php echo $medico['id_medico']; ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Ver perfil <i class="fas fa-chevron-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-user-md text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">Aún no hay suficientes valoraciones para mostrar el ranking.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer_usuario.php'; ?>
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


