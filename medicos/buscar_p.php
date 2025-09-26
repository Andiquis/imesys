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
    // Buscar pacientes con más datos incluyendo la foto
    $sql = "SELECT 
                u.id_usuario, 
                u.nombre, 
                u.apellido, 
                u.dni, 
                u.telefono, 
                u.genero, 
                u.foto,
                u.fecha_nacimiento,
                TIMESTAMPDIFF(YEAR, u.fecha_nacimiento, CURDATE()) AS edad,
                COUNT(c.id_cita) AS total_citas
            FROM usuarios u
            LEFT JOIN citas c ON u.id_usuario = c.id_usuario
            WHERE u.dni LIKE ? 
               OR CONCAT(u.nombre, ' ', u.apellido) LIKE ? 
               OR u.nombre LIKE ? 
               OR u.apellido LIKE ?
            GROUP BY u.id_usuario
            ORDER BY 
                CASE 
                    WHEN u.dni LIKE ? THEN 1
                    WHEN CONCAT(u.nombre, ' ', u.apellido) LIKE ? THEN 2
                    WHEN u.nombre LIKE ? THEN 3
                    ELSE 4
                END,
                u.nombre
            LIMIT 20";
    
    $stmt = $conexion->prepare($sql);
    $searchTerm = "%$query%";
    $stmt->bind_param("sssssss", 
        $searchTerm, $searchTerm, $searchTerm, $searchTerm,
        $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $patients = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode($patients);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>