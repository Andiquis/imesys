<?php
$host = "opal18.opalstack.com"; // Servidor de la base de datos
$usuario = "imesys"; // Usuario de la base de datos
$contrasena = "oRzcc2DMgduNeYMb"; // Contraseña del usuario
$base_datos = "bd_imesys"; // Nombre de la base de datos

// Crear conexión
$conexion = new mysqli($host, $usuario, $contrasena, $base_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Configurar charset
$conexion->set_charset("utf8");
?>