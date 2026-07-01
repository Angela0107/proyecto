<?php
$host = 'localhost'; 
$usuario = 'root'; 
$contrasena = ''; 
$base_datos = 'diseño_ayudas';

try {
    $conexion = new PDO("mysql:host=$host;dbname=$base_datos", $usuario, $contrasena);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>