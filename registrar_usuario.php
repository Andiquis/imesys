<?php
require 'conexion.php';
require 'usuarios/puntos.php'; // Asegúrate de incluir el sistema de puntos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $dni = trim($_POST['dni']);
    $email = trim($_POST['email']);
    $contrasena = $_POST['contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];
    $foto = ''; // Valor por defecto

    // Procesar foto (código existente)
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fotoTemp = $_FILES['foto']['tmp_name'];
        $fotoNombre = uniqid() . '_' . basename($_FILES['foto']['name']);
        $uploadDir = 'uploads/usuarios/'; // Ruta relativa
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        if (move_uploaded_file($fotoTemp, $uploadDir . $fotoNombre)) {
            $foto = $uploadDir . $fotoNombre;
        } else {
            header("Location: login_usuario.php?error=subida_imagen");
            exit;
        }
    }

    // Validaciones (código existente)
    if (empty($nombre) || empty($apellido) || empty($dni) || empty($email) || empty($contrasena) || empty($confirmar_contrasena)) {
        header("Location: login_usuario.php?error=campos_vacios");
        exit;
    }

    if (!preg_match('/^\d{8}$/', $dni)) {
        header("Location: login_usuario.php?error=dni_invalido");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: login_usuario.php?error=email_invalido");
        exit;
    }

    if ($contrasena !== $confirmar_contrasena) {
        header("Location: login_usuario.php?error=contrasena");
        exit;
    }

    // Verificar si el email o DNI ya existen
    $sql = "SELECT id_usuario FROM usuarios WHERE correo = ? OR dni = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $email, $dni);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        header("Location: login_usuario.php?error=email_o_dni_existente");
        exit;
    }
    $stmt->close();

    // Hash de la contraseña
    $contrasena_hasheada = password_hash($contrasena, PASSWORD_DEFAULT);

    // Insertar nuevo usuario
    $sql = "INSERT INTO usuarios (nombre, apellido, dni, correo, contrasena, foto) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ssssss", $nombre, $apellido, $dni, $email, $contrasena_hasheada, $foto);
        
        if ($stmt->execute()) {
            $id_usuario = $stmt->insert_id; // Obtenemos el ID del nuevo usuario
            
            // Asignar 50 puntos por registro
            $sistemaPuntos = new SistemaPuntos($conexion);
            $sistemaPuntos->agregarPuntos(
                $id_usuario,
                50, // 50 puntos por registro
                'Puntos por registro inicial',
                'registro'
            );
            
            // Redirigir con mensaje de éxito
            header("Location: login_usuario.php?success=registro");
            exit;
        } else {
            // Eliminar la imagen si el registro falló
            if (!empty($foto) && file_exists($foto)) {
                unlink($foto);
            }
        }
    }
    
    // Si hay algún error
    header("Location: login_usuario.php?error=registro");
    exit;
} else {
    header("Location: login_usuario.php");
    exit;
}

$conexion->close();
?>