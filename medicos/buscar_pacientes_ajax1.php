<?php
require 'conexion.php';

header('Content-Type: application/json');

if (!isset($_GET['q']) || empty($_GET['q'])) {
    echo json_encode(['error' => 'Término de búsqueda no proporcionado']);
    exit;
}

$termino = '%' . $_GET['q'] . '%';

$query = "SELECT id_usuario, nombre, apellido, dni 
          FROM usuarios 
          WHERE dni LIKE ? OR nombre LIKE ? OR apellido LIKE ? OR CONCAT(nombre, ' ', apellido) LIKE ?
          ORDER BY nombre, apellido
          LIMIT 10";

$stmt = $conexion->prepare($query);
$stmt->bind_param("ssss", $termino, $termino, $termino, $termino);
$stmt->execute();
$result = $stmt->get_result();

$pacientes = [];
while ($row = $result->fetch_assoc()) {
    $pacientes[] = $row;
}

$stmt->close();
$conexion->close();

echo json_encode($pacientes);
?>