<?php
session_start();
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['loggedin']) || ($_SESSION['tipo_usuario'] != 'medico' && $_SESSION['tipo_usuario'] != 'admin')) {
    header("Location: login.php");
    exit;
}

$id_cita = intval($_POST['id_cita']);
$estado = $_POST['estado'];
$respuesta = $_POST['respuesta'];

$stmt = $conexion->prepare("UPDATE citas SET estado = ?, respuesta = ? WHERE id_cita = ?");
$stmt->bind_param("ssi", $estado, $respuesta, $id_cita);

if ($stmt->execute()) {
    $_SESSION['mensaje'] = "Cita actualizada correctamente.";
} else {
    $_SESSION['error'] = "Error al actualizar la cita.";
}

$stmt->close();
header("Location: administrar_citas.php");
exit;