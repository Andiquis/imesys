<?php
session_start();
require_once 'conexion.php';

// Verificar si el usuario está logueado y es médico
if (!isset($_SESSION['id_medico'])) {
    header("Location: login.php");
    exit();
}

$id_medico = $_SESSION['id_medico'];

// Obtener información actual del médico
$query_medico = "SELECT * FROM medicos WHERE id_medico = ?";
$stmt_medico = $conexion->prepare($query_medico);
$stmt_medico->bind_param("i", $id_medico);
$stmt_medico->execute();
$result_medico = $stmt_medico->get_result();
$medico = $result_medico->fetch_assoc();

// Obtener especialidades para el select
$query_especialidades = "SELECT * FROM especialidades ORDER BY nombre_especialidad";
$result_especialidades = $conexion->query($query_especialidades);

// Procesar el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $id_especialidad = $_POST['id_especialidad'];
    $numero_colegiatura = $_POST['numero_colegiatura'];
    $direccion_consultorio = $_POST['direccion_consultorio'];
    
    // Procesar imagen
    $nombre_imagen = $medico['foto']; // Mantener la imagen actual por defecto
    
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $directorio = "../uploads/medicos/";
        $nombre_imagen = basename($_FILES['foto']['name']);
        $ruta_temporal = $_FILES['foto']['tmp_name'];
        $ruta_final = $directorio . $nombre_imagen;
        
        // Validar tipo de archivo
        $extension = strtolower(pathinfo($nombre_imagen, PATHINFO_EXTENSION));
        $extensiones_permitidas = array('jpg', 'jpeg', 'png', 'gif');
        
        if (in_array($extension, $extensiones_permitidas)) {
            if (move_uploaded_file($ruta_temporal, $ruta_final)) {
                // Eliminar la imagen anterior si existe y no es la predeterminada
                if ($medico['foto'] && $medico['foto'] != 'default-profile.jpg') {
                    @unlink($directorio . $medico['foto']);
                }
            } else {
                $error = "Error al subir la imagen.";
            }
        } else {
            $error = "Formato de imagen no permitido. Use JPG, JPEG, PNG o GIF.";
        }
    }
    
    // Actualizar datos en la base de datos
    $query_update = "UPDATE medicos SET 
                    nombre = ?, 
                    apellido = ?, 
                    correo = ?, 
                    telefono = ?, 
                    id_especialidad = ?, 
                    numero_colegiatura = ?, 
                    direccion_consultorio = ?, 
                    foto = ?
                    WHERE id_medico = ?";
    
    $stmt_update = $conexion->prepare($query_update);
    $stmt_update->bind_param("ssssisssi", 
                            $nombre, 
                            $apellido, 
                            $correo, 
                            $telefono, 
                            $id_especialidad, 
                            $numero_colegiatura, 
                            $direccion_consultorio, 
                            $nombre_imagen, 
                            $id_medico);
    
    if ($stmt_update->execute()) {
        $mensaje = "Perfil actualizado correctamente.";
        // Actualizar los datos en la sesión si es necesario
        $_SESSION['nombre_medico'] = $nombre;
        $_SESSION['foto_medico'] = $nombre_imagen;
        // Redirigir para ver los cambios
        header("Location: perfil_medico.php");
        exit();
    } else {
        $error = "Error al actualizar el perfil: " . $conexion->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil Médico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            cursor: pointer;
        }
        .profile-img-container {
            position: relative;
            display: inline-block;
        }
        .profile-img-overlay {
            position: absolute;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            color: white;
            width: 100%;
            text-align: center;
            padding: 5px;
            border-bottom-left-radius: 50%;
            border-bottom-right-radius: 50%;
        }
    </style>
</head>
<body>
     <!-- Barra superior -->
    <?php include 'header_medico.php'; ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Editar Perfil Médico</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($mensaje)): ?>
                            <div class="alert alert-success"><?php echo $mensaje; ?></div>
                        <?php endif; ?>
                        
                       <form action="editar_perfil_medico.php" method="post" enctype="multipart/form-data">
                            <div class="text-center mb-4">
                                <div class="profile-img-container">
                                    <img id="preview-img" src="<?php echo $medico['foto'] ? '../uploads/medicos/'.$medico['foto'] : 'assets/default-profile.jpg'; ?>" 
                                         alt="Foto de perfil" class="profile-img">
                                    <div class="profile-img-overlay">
                                        <i class="fas fa-camera"></i> Cambiar
                                    </div>
                                </div>
                                <input type="file" id="foto" name="foto" accept="image/*" style="display: none;">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?php echo htmlspecialchars($medico['nombre']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="apellido" class="form-label">Apellido</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" 
                                           value="<?php echo htmlspecialchars($medico['apellido']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="correo" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="correo" name="correo" 
                                       value="<?php echo htmlspecialchars($medico['correo']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       value="<?php echo htmlspecialchars($medico['telefono']); ?>">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="id_especialidad" class="form-label">Especialidad</label>
                                    <select class="form-select" id="id_especialidad" name="id_especialidad" required>
                                        <?php while ($especialidad = $result_especialidades->fetch_assoc()): ?>
                                            <option value="<?php echo $especialidad['id_especialidad']; ?>" 
                                                <?php if ($especialidad['id_especialidad'] == $medico['id_especialidad']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($especialidad['nombre_especialidad']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="numero_colegiatura" class="form-label">Número de Colegiatura</label>
                                    <input type="text" class="form-control" id="numero_colegiatura" name="numero_colegiatura" 
                                           value="<?php echo htmlspecialchars($medico['numero_colegiatura']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="direccion_consultorio" class="form-label">Dirección del Consultorio</label>
                                <textarea class="form-control" id="direccion_consultorio" name="direccion_consultorio" 
                                          rows="3"><?php echo htmlspecialchars($medico['direccion_consultorio']); ?></textarea>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Cambios
                                </button>
                                <a href="perfil_medico.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer_medico.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar vista previa de la imagen seleccionada
        document.getElementById('foto').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                var reader = new FileReader();
                
                reader.onload = function(event) {
                    document.getElementById('preview-img').src = event.target.result;
                }
                
                reader.readAsDataURL(e.target.files[0]);
            }
        });
        
        // Hacer clic en la imagen para abrir el selector de archivos
        document.querySelector('.profile-img-container').addEventListener('click', function() {
            document.getElementById('foto').click();
        });
    </script>
</body>
</html>