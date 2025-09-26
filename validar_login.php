<?php
// Incluir archivo de conexión
require 'conexion.php';
require 'usuarios/puntos.php'; // Asegúrate de incluir el sistema de puntos

// Iniciar sesión
session_start();

// Verificar si se enviaron datos por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $contrasena = $_POST['contrasena'];

    // Consulta para verificar credenciales
    $sql = "SELECT id_usuario, nombre, apellido, correo, contrasena FROM usuarios WHERE correo = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();
        
        // Verificar contraseña (asumiendo que está hasheada con password_hash)
        if (password_verify($contrasena, $usuario['contrasena'])) {
            // Guardar datos del usuario en sesión
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['apellido'] = $usuario['apellido'];
            $_SESSION['correo'] = $usuario['correo'];
            $_SESSION['loggedin'] = true;
            
            // Sistema de puntos - Asignar 2 puntos por inicio de sesión (una vez al día)
            $sistemaPuntos = new SistemaPuntos($conexion);
            
            // Verificar si ya recibió puntos por inicio de sesión hoy
            $puntosHoy = $sistemaPuntos->verificarPuntosHoy($usuario['id_usuario'], 'inicio_sesion');
            
            if ($puntosHoy == 0) {
                // Asignar 2 puntos
                $sistemaPuntos->agregarPuntos(
                    $usuario['id_usuario'],
                    2, // 2 puntos por inicio de sesión
                    'Puntos por inicio de sesión',
                    'inicio_sesion_'.date('Y-m-d')
                );
                
                // Guardar en sesión para mostrar mensaje
                $_SESSION['puntos_ganados'] = 2;
            }
            
            // Redirigir a la página de inicio
            header("Location: usuarios/inicio_imesys.php");
            exit;
        } else {
            // Contraseña incorrecta
            header("Location: login_usuario.php?error=credenciales");
            exit;
        }
    } else {
        // Usuario no encontrado
        header("Location: login_usuario.php?error=credenciales");
        exit;
    }
    
    $stmt->close();
} else {
    // Si no es POST, redirigir al login
    header("Location: login.php");
    exit;
}

$conexion->close();
?>