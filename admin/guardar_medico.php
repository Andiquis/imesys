<?php
require '../conexion.php';

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$correo = $_POST['correo'];
$contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
$telefono = $_POST['telefono'];
$id_especialidad = $_POST['id_especialidad'];
$numero_colegiatura = $_POST['numero_colegiatura'];
$direccion = $_POST['direccion_consultorio'];

$nombreFoto = $_FILES['foto']['name'];
$rutaTemporal = $_FILES['foto']['tmp_name'];
$rutaDestino = "../uploads/medicos/" . $nombreFoto;

if (move_uploaded_file($rutaTemporal, $rutaDestino)) {
    $stmt = $conexion->prepare("INSERT INTO medicos (nombre, apellido, correo, contrasena, telefono, id_especialidad, numero_colegiatura, foto, direccion_consultorio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssiiss", $nombre, $apellido, $correo, $contrasena, $telefono, $id_especialidad, $numero_colegiatura, $rutaDestino, $direccion);
    
    if ($stmt->execute()) {
        echo "Médico registrado correctamente.";
    } else {
        echo "Error al registrar médico: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Error al subir la imagen.";
}

$conexion->close();
?>
