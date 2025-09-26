<?php
require 'conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $contrasena = $_POST['contrasena'];

    // Validar campos vacíos
    if (empty($email) || empty($contrasena)) {
        header("Location: login_medico.php?error=campos_vacios");
        exit;
    }

    $sql = "SELECT id_medico, nombre, apellido, correo, contrasena FROM medicos WHERE correo = ?";
    $stmt = $conexion->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows == 1) {
            $medico = $resultado->fetch_assoc();
            
            if (password_verify($contrasena, $medico['contrasena'])) {
                $_SESSION['id_medico'] = $medico['id_medico'];
                $_SESSION['nombre'] = $medico['nombre'];
                $_SESSION['apellido'] = $medico['apellido'];
                $_SESSION['correo'] = $medico['correo'];
                $_SESSION['tipo_usuario'] = 'medico';
                $_SESSION['loggedin'] = true;
                
                header("Location: inicio_medicos.php");
                exit;
            }
        }
        
        $stmt->close();
    }
    
    // Si llega aquí es porque las credenciales son incorrectas
    header("Location: login_medico.php?error=credenciales");
    exit;
} else {
    header("Location: login_medico.php");
    exit;
}

$conexion->close();
?>