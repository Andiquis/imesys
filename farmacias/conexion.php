<?php
$host = "localhost"; // Servidor de la base de datos
$usuario = "root"; // Usuario de la base de datos
$contrasena = "admin942"; // Contraseña del usuario
$base_datos = "BD_imesys"; // Nombre de la base de datos

// Crear conexión
$conexion = new mysqli($host, $usuario, $contrasena, $base_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Configurar charset
$conexion->set_charset("utf8");
?>