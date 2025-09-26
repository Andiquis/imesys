<?php
session_start();
require 'conexion.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: mis_citas.php");
    exit;
}

$id_cita = intval($_GET['id']);
$id_usuario = $_SESSION['id_usuario'];

// Obtener información de la cita
$stmt = $conexion->prepare("SELECT estado FROM citas WHERE id_cita = ? AND id_usuario = ?");
$stmt->bind_param("ii", $id_cita, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$cita = $result->fetch_assoc();

// Solo permitir si está confirmada
if (!$cita || $cita['estado'] != 'Confirmada') {
    header("Location: mis_citas.php");
    exit;
}

// Redirigir al nuevo archivo de vista
header("Location: ver_cita_confirmada.php?id=".$id_cita);
exit;