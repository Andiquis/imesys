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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Inicio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="estilos_inicio.css">
</head>
<body class="bg-gray-100">
    <!-- Barra superior -->
    <nav class="navbar">
        <div class="nav-left">
            <button id="menuToggle" class="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="logo-container">
                <img src="img/logo.png" alt="Logo IMESYS" class="logo">
                <span class="logo-text">IMESYS</span>
            </div>
        </div>
        <div class="nav-right">
            <div class="user-info">
                <?php if($foto): ?>
                    <img src="uploads/<?php echo htmlspecialchars($foto); ?>" alt="Foto de perfil" class="user-avatar">
                <?php else: ?>
                    <div class="default-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
                <span class="user-name"><?php echo htmlspecialchars($nombre . ' ' . $apellido); ?></span>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>Cerrar Sesión
            </a>
        </div>
    </nav>

    <!-- Overlay -->
    <div id="overlay" class="overlay"></div>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar">
        <div class="sidebar-content">
            <!-- Información de usuario -->
            <div class="user-profile">
                <h1><?php echo htmlspecialchars($nombre); ?></h1>
                <p><?php echo htmlspecialchars($correo); ?></p>
            </div>
            
            <!-- Sección de menú -->
            <div class="menu-section">
                <h2>Predicción Médica IA</h2>
                <div class="menu-items">
                    <a href="#" class="menu-item active">
                        <i class="fas fa-image"></i>
                        <span>Predicción Avanzada por Imágenes IA</span>
                    </a>
                    <a href="#" class="menu-item">
                        <i class="fas fa-comment-alt"></i>
                        <span>Chat IA</span>
                    </a>
                    <a href="#" class="menu-item">
                        <i class="fas fa-user-md"></i>
                        <span>Lista de Especialistas</span>
                    </a>
                    <a href="#" class="menu-item">
                        <i class="fas fa-calendar-check"></i>
                        <span>Reservas de Citas</span>
                    </a>
                    <a href="#" class="menu-item">
                        <i class="fas fa-pills"></i>
                        <span>Descuentos en Medicamentos</span>
                    </a>
                    <a href="#" class="menu-item">
                        <i class="fas fa-cog"></i>
                        <span>Ajustes</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón flotante del Chat IA -->
    <div class="floating-chat" id="chatButton">
        <i class="fas fa-comment-dots"></i>
    </div>

    <!-- Contenido principal -->
    <div id="contentArea" class="content-area">
        <div class="container">
            <!-- Sección de bienvenida y buscador -->
            <div class="search-container">
                <div class="search-inner">
                    <div class="welcome-section">
                        <h1>BIENVENIDO <?php echo strtoupper(htmlspecialchars($nombre)); ?></h1>
                        <p>¿Cómo podemos ayudarte hoy?</p>
                        <div class="search-bar">
                            <input type="text" placeholder="Escribe tu consulta...">
                            <button>
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Consejos de salud -->
            <div class="health-cards">
                <div class="health-card blue-card">
                    <h3>Sistemas de la Gastritis</h3>
                    <img src="img/gastritis.jpg" alt="Sistemas de la Gastritis">
                    <p>¿Qué son las sistemas de la gastritis y la gastropatía?</p>
                    <a href="#" class="card-link">
                        Revisa Aquí <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
                
                <div class="health-card green-card">
                    <h3>Últimos avances tecnológicos</h3>
                    <img src="img/tecnologia-medica.jpg" alt="Avances tecnológicos">
                    <p>Descubre las últimas innovaciones en medicina.</p>
                    <a href="#" class="card-link">
                        Más información <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
                
                <div class="health-card purple-card">
                    <h3>Sistema de salud integrado</h3>
                    <img src="img/sistema-salud.jpg" alt="Sistema de salud">
                    <p>Conoce cómo funciona nuestro sistema de atención.</p>
                    <a href="#" class="card-link">
                        Más información <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
                
                <div class="health-card yellow-card">
                    <h3>Especialista del mes</h3>
                    <img src="img/especialista-mes.jpg" alt="Especialista del mes">
                    <p class="doctor-name"><strong>Julio Cortzan Rivera</strong></p>
                    <p class="doctor-info">Especialista en Oncología con más de 25 años de experiencia, trabaja en el sector público y cuenta con un consultorio privado.</p>
                    <div class="doctor-stats">
                        <span>256 consultas</span>
                        <span>354 valoraciones</span>
                    </div>
                </div>
            </div>
            
            <!-- Sección de especialistas destacados -->
            <div class="specialists-section">
                <h2>Mejores Especialistas del Mes</h2>
                <div class="doctors-grid">
                    <div class="doctor-card">
                        <div class="doctor-header">
                            <img src="img/doctor1.jpg" alt="Dra. María Fernández" class="doctor-photo">
                            <div class="doctor-title">
                                <h3>Dra. María Fernández</h3>
                                <p>Cardióloga</p>
                            </div>
                        </div>
                        <p class="doctor-description">15 años de experiencia, especialista en intervenciones coronarias.</p>
                        <div class="doctor-footer">
                            <span class="rating">⭐ 4.9 (128)</span>
                            <a href="#" class="profile-link">Ver perfil</a>
                        </div>
                    </div>
                    
                    <div class="doctor-card">
                        <div class="doctor-header">
                            <img src="img/doctor2.jpg" alt="Dr. Carlos Mendoza" class="doctor-photo">
                            <div class="doctor-title">
                                <h3>Dr. Carlos Mendoza</h3>
                                <p>Pediatra</p>
                            </div>
                        </div>
                        <p class="doctor-description">20 años de experiencia, especialista en neonatología.</p>
                        <div class="doctor-footer">
                            <span class="rating">⭐ 4.8 (95)</span>
                            <a href="#" class="profile-link">Ver perfil</a>
                        </div>
                    </div>
                    
                    <div class="doctor-card">
                        <div class="doctor-header">
                            <img src="img/doctor3.jpg" alt="Dra. Laura Jiménez" class="doctor-photo">
                            <div class="doctor-title">
                                <h3>Dra. Laura Jiménez</h3>
                                <p>Dermatóloga</p>
                            </div>
                        </div>
                        <p class="doctor-description">12 años de experiencia, especialista en dermatología estética.</p>
                        <div class="doctor-footer">
                            <span class="rating">⭐ 4.7 (87)</span>
                            <a href="#" class="profile-link">Ver perfil</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sección de contacto -->
            <div class="contact-section">
                <h2>Contacta con nosotros</h2>
                <p>¿Tienes alguna duda o necesitas ayuda adicional?</p>
                <button class="contact-btn">
                    <i class="fas fa-envelope"></i> Contáctanos
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-left">
                <img src="img/logo.png" alt="Logo IMESYS" class="footer-logo">
                <p>Sistema de salud inteligente</p>
            </div>
            <div class="footer-right">
                <p>© 2023 IMESYS. Todos los derechos reservados.</p>
                <p>contacto@imesys.com | Tel: +1 234 567 890</p>
            </div>
        </div>
    </footer>

    <script src="script_inicio.js"></script>
</body>
</html>