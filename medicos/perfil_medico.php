<?php
session_start();
require_once 'conexion.php'; // Archivo con la conexión a la base de datos

// Verificar si el usuario está logueado y es médico
if (!isset($_SESSION['id_medico'])) {
    header("Location: login_medico.php");
    exit();
}

$id_medico = $_SESSION['id_medico'];

// Obtener información del médico
$query_medico = "SELECT m.*, e.nombre_especialidad 
                FROM medicos m 
                JOIN especialidades e ON m.id_especialidad = e.id_especialidad 
                WHERE m.id_medico = ?";
$stmt_medico = $conexion->prepare($query_medico);
$stmt_medico->bind_param("i", $id_medico);
$stmt_medico->execute();
$result_medico = $stmt_medico->get_result();
$medico = $result_medico->fetch_assoc();

// Obtener valoración promedio
$query_valoracion = "SELECT AVG(puntuacion) as promedio, COUNT(*) as total 
                    FROM clasificacion_medicos 
                    WHERE id_medico = ?";
$stmt_valoracion = $conexion->prepare($query_valoracion);
$stmt_valoracion->bind_param("i", $id_medico);
$stmt_valoracion->execute();
$result_valoracion = $stmt_valoracion->get_result();
$valoracion = $result_valoracion->fetch_assoc();

// Obtener comentarios recientes
$query_comentarios = "SELECT cm.puntuacion, cm.comentario, cm.fecha_clasificacion, 
                     IF(cm.anonimo = 1, 'Anónimo', CONCAT(u.nombre, ' ', u.apellido)) as nombre_paciente
                     FROM clasificacion_medicos cm
                     JOIN usuarios u ON cm.id_usuario = u.id_usuario
                     WHERE cm.id_medico = ?
                     ORDER BY cm.fecha_clasificacion DESC
                     LIMIT 5";
$stmt_comentarios = $conexion->prepare($query_comentarios);
$stmt_comentarios->bind_param("i", $id_medico);
$stmt_comentarios->execute();
$result_comentarios = $stmt_comentarios->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil Médico - <?php echo $medico['nombre'] . ' ' . $medico['apellido']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-img {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 50%;
        }
        .rating-stars {
            color: #FFD700;
            font-size: 1.5rem;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .specialty-badge {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
     <!-- Barra superior -->
    <?php include 'header_medico.php'; ?>
    
    <!-- Contenido principal con clases responsivas -->
    <div class="main-content min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-100">
        <div class="container mx-auto px-4 lg:px-6 py-6 lg:py-8">
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <img src="<?php echo $medico['foto'] ? '../uploads/medicos/'.$medico['foto'] : 'img/persona.jpg'; ?>" 
                             alt="Foto de perfil" class="profile-img mb-3">
                        <h3><?php echo $medico['nombre'] . ' ' . $medico['apellido']; ?></h3>
                        <span class="badge specialty-badge mb-3"><?php echo $medico['nombre_especialidad']; ?></span>
                        
                        <div class="mb-3">
                            <?php
                            $promedio = round($valoracion['promedio'], 1);
                            $estrellas_llenas = floor($promedio);
                            $media_estrella = ($promedio - $estrellas_llenas) >= 0.5;
                            
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $estrellas_llenas) {
                                    echo '<i class="fas fa-star rating-stars"></i>';
                                } elseif ($media_estrella && $i == $estrellas_llenas + 1) {
                                    echo '<i class="fas fa-star-half-alt rating-stars"></i>';
                                } else {
                                    echo '<i class="far fa-star rating-stars"></i>';
                                }
                            }
                            ?>
                            <span>(<?php echo $promedio; ?>/5 - <?php echo $valoracion['total']; ?> valoraciones)</span>
                        </div>
                        
                        <p><i class="fas fa-id-card"></i> Colegiatura: <?php echo $medico['numero_colegiatura']; ?></p>
                        <p><i class="fas fa-envelope"></i> <?php echo $medico['correo']; ?></p>
                        <p><i class="fas fa-phone"></i> <?php echo $medico['telefono']; ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo $medico['direccion_consultorio']; ?></p>
                        
                        <a href="editar_perfil_medico.php" class="btn btn-primary mt-3">
                            <i class="fas fa-edit"></i> Editar Perfil
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Acerca de mí</h4>
                    </div>
                    <div class="card-body">
                        <p>Médico especializado en <?php echo $medico['nombre_especialidad']; ?> con colegiatura profesional <?php echo $medico['numero_colegiatura']; ?>.</p>
                        <p>Registrado en el sistema desde <?php echo date('d/m/Y', strtotime($medico['fecha_registro'])); ?>.</p>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Valoraciones y Comentarios</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($result_comentarios->num_rows > 0): ?>
                            <?php while ($comentario = $result_comentarios->fetch_assoc()): ?>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <h5><?php echo $comentario['nombre_paciente']; ?></h5>
                                        <div>
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $comentario['puntuacion']) {
                                                    echo '<i class="fas fa-star text-warning"></i>';
                                                } else {
                                                    echo '<i class="far fa-star text-warning"></i>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <p class="text-muted"><?php echo date('d/m/Y H:i', strtotime($comentario['fecha_clasificacion'])); ?></p>
                                    <p><?php echo $comentario['comentario']; ?></p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>Aún no tienes valoraciones.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        </div> <!-- Cierre del container -->
    </div> <!-- Cierre del main-content -->
    <?php include 'footer_medico.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>