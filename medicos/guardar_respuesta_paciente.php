<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: login_usuario.php");
    exit;
}

require 'conexion.php';

$id_cita = intval($_POST['id_cita']);
$respuesta_paciente = trim($_POST['respuesta_paciente']);
$id_usuario = $_SESSION['id_usuario'];

// Verificar que la cita pertenece al paciente
$stmt_verificar = $conexion->prepare("SELECT id_cita FROM citas WHERE id_cita = ? AND id_usuario = ?");
$stmt_verificar->bind_param("ii", $id_cita, $id_usuario);
$stmt_verificar->execute();
$stmt_verificar->store_result();

if ($stmt_verificar->num_rows == 0) {
    $_SESSION['error'] = "No tienes permiso para modificar esta cita.";
    header("Location: mis_citas.php"); // Cambié a mis_citas.php que parece más correcto
    exit;
}

// Opción A: Usar la columna respuesta para ambas partes (médico y paciente)
$stmt_actualizar = $conexion->prepare("UPDATE citas SET respuesta = CONCAT(IFNULL(respuesta, ''), '\n[Paciente]: ', ?) WHERE id_cita = ?");
$stmt_actualizar->bind_param("si", $respuesta_paciente, $id_cita);

// Opción B: Si prefieres separar los mensajes (requiere agregar columna)
// $stmt_actualizar = $conexion->prepare("UPDATE citas SET respuesta_paciente = ? WHERE id_cita = ?");
// $stmt_actualizar->bind_param("si", $respuesta_paciente, $id_cita);

if ($stmt_actualizar->execute()) {
    $_SESSION['mensaje'] = "Tu respuesta ha sido enviada al médico.";
} else {
    $_SESSION['error'] = "Error al guardar tu respuesta: " . $conexion->error;
}

header("Location: ver_citas_paciente.php");
exit;