<?php
// Iniciar la sesión
session_start();

// Destruir todas las variables de sesión
session_unset();

// Destruir la sesión
session_destroy();

// Redirigir al usuario al login o a la página principal
header("Location: ../prelogin.php");
exit();
?>
