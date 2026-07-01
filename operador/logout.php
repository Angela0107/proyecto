<?php
session_start(); // Inicia la sesión

// Verifica si hay una sesión activa
if (isset($_SESSION['nom_usu'])) {
    // Destruye la sesión
    session_unset(); // Libera todas las variables de sesión
    session_destroy(); // Destruye la sesión

    // Redirige a la página de inicio o a la página de login
    header("Location: ../login.php"); // Cambia 'login.php' por la página que desees
    exit();
} else {
    // Si no hay sesión activa, redirige a la página de login
    header("Location: ../login.php");
    exit();
}
?>