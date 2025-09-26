<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "admin123";
$dbname = "BD_imesys";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Procesar datos del formulario
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$correo = $_POST['correo'];
$contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT); // Encriptar contraseña
$telefono = $_POST['telefono'] ?? null;
$id_especialidad = $_POST['id_especialidad'];
$numero_colegiatura = $_POST['numero_colegiatura'];
$direccion_consultorio = $_POST['direccion_consultorio'] ?? null;

// Procesar la foto
$foto = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/medicos/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileExtension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '.' . $fileExtension;
    $uploadFile = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadFile)) {
        $foto = $uploadFile;
    }
}

// Insertar datos en la tabla medicos
$sql = "INSERT INTO medicos (nombre, apellido, correo, contrasena, telefono, id_especialidad, numero_colegiatura, foto, direccion_consultorio)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssisss", $nombre, $apellido, $correo, $contrasena, $telefono, $id_especialidad, $numero_colegiatura, $foto, $direccion_consultorio);

if ($stmt->execute()) {
    // Registro exitoso
    header("Location: registro_exitoso.html");
    exit();
} else {
    // Error en el registro
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$stmt->close();
$conn->close();
?>