<?php
// Leer variables de entorno de Render, o usar los valores locales por defecto si no existen
$servername = getenv('DB_HOST')     ?: "localhost"; 
$username   = getenv('DB_USER')     ?: "root"; 
$password   = getenv('DB_PASSWORD') ?: ""; 
$dbname     = getenv('DB_NAME')     ?: "diseño_ayudas"; 
$port       = getenv('DB_PORT')     ?: "3306"; // Las bases de datos en la nube suelen usar puertos distintos

// Crear conexión (ahora incluyendo la variable de puerto)
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
