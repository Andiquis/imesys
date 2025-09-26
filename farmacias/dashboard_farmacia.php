<?php
session_start();

if (!isset($_SESSION['farmacia_loggedin'])) {
    header("Location: login_farmacia.php");
    exit;
}

// Puedes añadir estadísticas o resumen para el dashboard
header("Location: validar_codigo.php"); // Redirigir a la página principal por ahora
exit;
?>