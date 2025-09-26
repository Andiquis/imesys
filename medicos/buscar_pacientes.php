<?php
session_start();
require 'conexion.php';

header('Content-Type: application/json');

$query = isset($_POST['query']) ? trim($_POST['query']) : '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

try {
    // Buscar pacientes por DNI o nombre/apellido (insensible a mayúsculas)
    $sql = "SELECT id_usuario, nombre, apellido, dni, telefono, genero, fecha_nacimiento, 
            TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad
            FROM usuarios 
            WHERE dni LIKE ? 
            OR CONCAT(nombre, ' ', apellido) LIKE ? 
            OR nombre LIKE ? 
            OR apellido LIKE ?
            LIMIT 10";
    
    $stmt = $conexion->prepare($sql);
    $searchTerm = "%$query%";
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $patients = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode($patients);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>