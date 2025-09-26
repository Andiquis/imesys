<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

require 'conexion.php';
require 'puntos.php'; // Asegúrate de incluir el sistema de puntos


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['id_usuario'];
    $medico_id = $_POST['medico_id'];
    $puntuacion = $_POST['puntuacion'];
    $comentario = $_POST['comentario'];
    $anonimo = isset($_POST['anonimo']) ? 1 : 0;
    
    // Validar que el médico existe
    $stmt = $conexion->prepare("SELECT id_medico FROM medicos WHERE id_medico = ?");
    $stmt->bind_param("i", $medico_id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows == 0) {
        $_SESSION['error'] = "El médico seleccionado no existe.";
        header("Location: comentarios_medicos.php");
        exit;
    }
    $stmt->close();
    
    // Insertar el comentario
    $stmt = $conexion->prepare("INSERT INTO clasificacion_medicos (id_usuario, id_medico, puntuacion, comentario, anonimo) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisi", $user_id, $medico_id, $puntuacion, $comentario, $anonimo);
    if ($stmt->execute()) 
    // Obtener ID del comentario insertado
    $id_comentario = $stmt->insert_id;
    
    // Asignar puntos por comentario
    $sistemaPuntos = new SistemaPuntos($conexion);
    $sistemaPuntos->agregarPuntos(
        $user_id,
        $sistemaPuntos->obtenerConfig('puntos_por_comentario'),
        'Puntos por comentario médico',
        'comentario_'.$id_comentario
    );
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "¡Gracias por tu comentario!";
    } else {
        $_SESSION['error'] = "Error al enviar el comentario. Por favor intenta nuevamente.";
    }
    $stmt->close();
    
    header("Location: detalle_medico.php?id=" . $medico_id);
    exit;
} else {
    header("Location: comentarios_medicos.php");
    exit;
}