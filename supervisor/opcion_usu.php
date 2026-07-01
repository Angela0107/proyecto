<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['registrar'])) {
        // Redirigir a la ruta de registro
        header('Location: agregusu.php');
        exit();
    } elseif (isset($_POST['validar'])) {
        // Redirigir a la ruta de validación
        header('Location: validar_usuario.php');
        exit();
    }
}

include 'nav/index.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opciones de Usuario</title>
</head>
<body>
    <div class="container">
    <form method="post">
        <button type="submit" name="registrar">Registrar usuario nuevo</button>
        <button type="submit" name="validar">Validar usuario</button>
    </form>
    </div>
</body>

<style>
.container {
    display: flex;
    justify-content: center; /* Centra el contenido horizontalmente */
    align-items: center; /* Centra el contenido verticalmente */
    height: 100vh; /* Ocupa toda la altura de la ventana */
    background-color: #f0f0f0; /* Color de fondo suave */
}

/* Estilo del formulario */
form {
    background-color: #ffffff; /* Fondo blanco para el formulario */
    padding: 20px; /* Espaciado interno */
    border-radius: 8px; /* Bordes redondeados */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Sombra sutil */
    margin-top: 5px;
    width: 440px;
    height: 90px;
}

/* Estilo de los botones */
button {
    background-color: #007bff; /* Color de fondo azul */
    color: white; /* Color del texto */
    border: none; /* Sin borde */
    border-radius: 5px; /* Bordes redondeados */
    padding: 10px 20px; /* Espaciado interno */
    margin: 10px 0; /* Margen entre botones */
    cursor: pointer; /* Cambia el cursor al pasar el mouse */
    font-size: 16px; /* Tamaño de fuente */
    transition: background-color 0.3s; /* Transición suave para el color de fondo */
}

/* Efecto al pasar el mouse sobre los botones */
button:hover {
    background-color: #0056b3; /* Color de fondo más oscuro al pasar el mouse */
}

   
    </style>
</html>