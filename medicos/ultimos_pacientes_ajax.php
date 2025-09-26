<?php
// ultimos_pacientes_ajax.php
include 'conexion.php'; // Asegúrate de incluir tu conexión

header('Content-Type: application/json');

try {
    $query = "SELECT * FROM usuarios ORDER BY fecha_registro DESC LIMIT 5";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($pacientes);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al obtener los últimos pacientes']);
}
?>