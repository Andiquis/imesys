<?php
session_start();

if (!isset($_SESSION['admin_loggedin'])) {
    header("Location: login_admin.php");
    exit;
}

require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "admin123", "BD_imesys");

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Procesar datos del formulario si se envió
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo = trim($_POST['correo']);
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    $telefono = trim($_POST['telefono']);
    $numero_colegiatura = trim($_POST['numero_colegiatura']);
    $id_especialidad = $_POST['id_especialidad'];
    $direccion_consultorio = trim($_POST['direccion_consultorio']);
    $foto = '';

    // Procesar foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fotoTemp = $_FILES['foto']['tmp_name'];
        $fotoNombre = uniqid() . '_' . basename($_FILES['foto']['name']);
        $uploadDir = '../Uploads/medicos/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        if (move_uploaded_file($fotoTemp, $uploadDir . $fotoNombre)) {
            $foto = $uploadDir . $fotoNombre;
        }
    }

    // Insertar médico en la base de datos
    $sql = "INSERT INTO medicos (nombre, apellido, correo, contrasena, telefono, numero_colegiatura, id_especialidad, direccion_consultorio, foto) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssiss", $nombre, $apellido, $correo, $contrasena, $telefono, $numero_colegiatura, $id_especialidad, $direccion_consultorio, $foto);

    if ($stmt->execute()) {
        // Obtener información de la especialidad para el correo
        $especialidadQuery = $conn->prepare("SELECT nombre_especialidad FROM especialidades WHERE id_especialidad = ?");
        $especialidadQuery->bind_param("i", $id_especialidad);
        $especialidadQuery->execute();
        $especialidadResult = $especialidadQuery->get_result();
        $especialidad = $especialidadResult->fetch_assoc()['nombre_especialidad'];
        
        // Enviar correo de bienvenida
        $mail = new PHPMailer(true);
        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'imesysapp@gmail.com';
            $mail->Password = 'qufpkzfnkinwkufs';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Remitente y destinatario
            $mail->setFrom('imesysapp@gmail.com', 'IMESYS Salud');
            $mail->addAddress($correo, $nombre . ' ' . $apellido);
            $mail->addReplyTo('imesysapp@gmail.com', 'Soporte IMESYS');

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = '¡Bienvenido/a a IMESYS, Dr./Dra. ' . $apellido . '!';
            $mail->Body = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #1a73e8; padding: 20px; color: white; text-align: center; border-radius: 8px 8px 0 0; }
                    .content { background-color: white; padding: 25px; border-radius: 0 0 8px 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
                    .info-card { background-color: #f8f9fa; border-left: 4px solid #1a73e8; padding: 15px; margin: 20px 0; border-radius: 4px; }
                    .button { display: inline-block; background-color: #1a73e8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
                    .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 14px; color: #777; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h2>¡Bienvenido/a a IMESYS!</h2>
                </div>
                
                <div class="content">
                    <p>Estimado/a Dr./Dra. ' . $apellido . ',</p>
                    
                    <p>Nos complace darle la bienvenida a IMESYS, la plataforma de salud inteligente donde podrá:</p>
                    
                    <div class="info-card">
                        <p><strong>Su cuenta ha sido creada con éxito:</strong></p>
                        <p>👨‍⚕️ <strong>Nombre:</strong> ' . $nombre . ' ' . $apellido . '</p>
                        <p>🏥 <strong>Especialidad:</strong> ' . $especialidad . '</p>
                        <p>📧 <strong>Correo:</strong> ' . $correo . '</p>
                    </div>
                    
                    <p><strong>Beneficios de IMESYS para profesionales:</strong></p>
                    <ul>
                        <li>Gestionar su agenda de consultas de manera eficiente</li>
                        <li>Acceder a pacientes de nuestra red de salud</li>
                        <li>Recibir notificaciones de consultas urgentes</li>
                        <li>Participar en nuestro programa de referidos</li>
                        <li>Y mucho más!</li>
                    </ul>
                    
                    <p style="text-align: center; margin: 25px 0;">
                        <a href="https://imesys.com/login" class="button">Acceder a mi cuenta</a>
                    </p>
                    
                    <div class="footer">
                        <p>Si necesita ayuda, nuestro equipo está disponible en <a href="mailto:imesysapp@gmail.com">imesysapp@gmail.com</a></p>
                        <p>Saludos cordiales,<br><strong>Equipo IMESYS</strong></p>
                    </div>
                </div>
            </body>
            </html>
            ';
            
            $mail->AltBody = "Estimado/a Dr./Dra. $apellido,\n\n"
                ."¡Bienvenido/a a IMESYS!\n\n"
                ."Su cuenta ha sido creada con éxito:\n"
                ."Nombre: $nombre $apellido\n"
                ."Especialidad: $especialidad\n"
                ."Correo: $correo\n\n"
                ."Con IMESYS podrá gestionar su agenda de consultas, acceder a pacientes de nuestra red, recibir notificaciones de consultas urgentes y participar en nuestro programa de referidos.\n\n"
                ."Acceda a su cuenta aquí: https://imesys.com/login\n\n"
                ."Saludos cordiales,\n"
                ."Equipo IMESYS";

            $mail->send();
            $_SESSION['success_message'] = "Médico registrado exitosamente. Se ha enviado un correo de bienvenida al Dr./Dra. $apellido.";
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $mail->ErrorInfo);
            $_SESSION['success_message'] = "Médico registrado exitosamente, pero no se pudo enviar el correo de bienvenida.";
        }
        
        header("Location: registro_medico.php");
        exit;
    } else {
        // Eliminar la imagen si el registro falló
        if (!empty($foto) && file_exists($foto)) {
            unlink($foto);
        }
        $_SESSION['error_message'] = "Error al registrar el médico: " . $conn->error;
        header("Location: registro_medico.php");
        exit;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Registro de Médico</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            transition: all 0.3s ease;
        }
        .sidebar-item:hover {
            background-color: #ebf4ff;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="sidebar w-64 bg-blue-800 text-white">
            <div class="p-4 border-b border-blue-700">
                <h1 class="text-xl font-bold">IMESYS Admin</h1>
                <p class="text-sm text-blue-200">Panel de Administración</p>
            </div>
           <nav class="space-y-2">
                <a href="dashboard_admin.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg sidebar-item">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                    
                     <a href="administrar_medico.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg sidebar-item">
                        <i class="fas fa-user-md"></i>
                        <span>Médicos</span>
                    </a>
                    <a href="administrar_farmacia.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg sidebar-item">
                        <i class="fas fa-cog"></i>
                        <span>Farmacias</span>
                    </a>
                    <a href="logout_admin.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg sidebar-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </a>
            </nav>
            <div class="p-4 border-t border-blue-700 absolute bottom-0 w-64">
                <p class="text-xs text-blue-200">Sistema IMESYS v1.0</p>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="flex-1 overflow-auto">
            <!-- Barra superior -->
            <header class="bg-white shadow-sm">
                <div class="flex justify-between items-center px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">Registro de Médico</h2>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <i class="fas fa-bell text-gray-500"></i>
                            <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
                        </div>
                        <div class="flex items-center">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['admin_nombre']) ?>&background=3B82F6&color=fff" 
                                 alt="Admin" class="h-8 w-8 rounded-full">
                            <span class="ml-2 text-sm font-medium"><?= htmlspecialchars($_SESSION['admin_nombre']) ?></span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Contenido del formulario -->
            <main class="p-6">
                <!-- Mostrar mensajes de éxito/error -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?= $_SESSION['success_message'] ?></span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                            <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <title>Cerrar</title>
                                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                            </svg>
                        </span>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?= $_SESSION['error_message'] ?></span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                            <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <title>Cerrar</title>
                                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                            </svg>
                        </span>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <div class="bg-white p-8 rounded-2xl shadow-md w-full max-w-2xl mx-auto">
                    <h2 class="text-2xl font-bold mb-6 text-center">Registro de Médico</h2>
                    <form action="registro_medico.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" name="nombre" placeholder="Nombre" required class="border p-2 rounded w-full">
                            <input type="text" name="apellido" placeholder="Apellido" required class="border p-2 rounded w-full">
                            <input type="email" name="correo" placeholder="Correo" required class="border p-2 rounded w-full">
                            <input type="password" name="contrasena" placeholder="Contraseña" required class="border p-2 rounded w-full">
                            <input type="text" name="telefono" placeholder="Teléfono" class="border p-2 rounded w-full">
                            <input type="text" name="numero_colegiatura" placeholder="Número de Colegiatura" required class="border p-2 rounded w-full">
                        </div>

                        <div>
                            <label class="block font-medium">Especialidad</label>
                            <select name="id_especialidad" required class="border p-2 rounded w-full">
                                <?php
                                $query = "SELECT id_especialidad, nombre_especialidad FROM especialidades";
                                $result = $conn->query($query);
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['id_especialidad']}'>{$row['nombre_especialidad']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div>
                            <label class="block font-medium">Dirección del Consultorio</label>
                            <input type="text" name="direccion_consultorio" class="border p-2 rounded w-full">
                        </div>

                        <div>
                            <label class="block font-medium">Foto</label>
                            <input type="file" name="foto" accept="image/*" class="border p-2 rounded w-full">
                        </div>

                        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition duration-300">Registrar Médico</button>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>