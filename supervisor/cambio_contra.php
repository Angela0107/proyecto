<?php
// Configuración de la base de datos
$servername = 'localhost'; 
$username = 'root'; 
$password = ''; 
$dbname = 'diseño_ayudas'; 

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Variable para almacenar mensajes
$message = "";

// Verificar si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $cedula = isset($_POST['cedula']) ? $_POST['cedula'] : '';
    $contr_usu = isset($_POST['contr_usu']) ? $_POST['contr_usu'] : ''; // Contraseña actual
    $contranueva = isset($_POST['contranueva']) ? $_POST['contranueva'] : ''; // Nueva contraseña

    // Verificar si la cédula y la contraseña actual son correctas
    $stmt = $conn->prepare("SELECT contr_usu FROM usuarios WHERE cedula=?");
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        // Verificar la contraseña actual usando MD5
        if (md5($contr_usu) === $hashed_password) {
            // Hashear la nueva contraseña usando MD5
            $contranueva_hashed = md5($contranueva);

            // Actualizar la nueva contraseña
            $update_stmt = $conn->prepare("UPDATE usuarios SET contr_usu=? WHERE cedula=?");
            $update_stmt->bind_param("ss", $contranueva_hashed, $cedula);

            if ($update_stmt->execute()) {
                // Redirigir a login.php después de cambiar la contraseña
                header("Location: ../logout.php?message=Se ha cambiado correctamente la contraseña");
                exit();
            } else {
                $message = "Modificación incorrecta: " . $update_stmt->error;
            }

            $update_stmt->close();
        } else {
            $message = "La contraseña actual es incorrecta.";
        }
    } else {
        $message = "La cédula no existe.";
    }

    // Cerrar la declaración
    $stmt->close();
}

include 'nav/index.php';

// Cerrar la conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <script>
        function showModal() {
            document.getElementById('modal').style.display = 'block';
        }

        function hideModal() {
        document.getElementById('modal').style.display = 'none';
        location.reload(); // Recargar la página
        }

        function confirmChangePassword() {
            document.getElementById('changePasswordForm').submit();
        }
    </script>
</head>
<body>
    <h2>Cambiar Contraseña</h2>
    <form id="changePasswordForm" method="POST" onsubmit="event.preventDefault(); showModal();">
        <label for="cedula">Cédula:</label>
        <input type="text" id="cedula" name="cedula" required><br><br>
        
        <label for="contr_usu">Contraseña Actual:</label>
        <input type="password" id="contr_usu" name="contr_usu" required><br><br>

        <label for="contranueva">Nueva Contraseña:</label>
        <input type="password" id="contranueva" name="contranueva" required><br><br>
        
        <input type="submit" value="Cambiar Contraseña">
    </form>

    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideModal()">&times;</span>
            <h2>Confirmar Cambio de Contraseña</h2>
            <p>¿Está seguro de que desea cambiar la contraseña?</p>
            <button onclick="confirmChangePassword()">Sí</button>
            <button onclick="hideModal()">No</button>
        </div>
    </div>

    <?php
    // Mostrar mensaje si existe
    if (!empty($message)) {
        echo "<p>$message</p>";
    }
    ?>
</body>
<style>
body {
    font-family: 'Arial', sans-serif;
    background-color: #f8f9fa; /* Color de fondo más suave */
    margin: 0;
    padding: 0;
}

h2 {
    text-align: center;
    color: #343a40;
    margin-top: 50px;
    font-size: 2em; /* Tamaño de fuente más grande */
}

form {
    max-width: 400px;
    margin: 50px auto;
    padding: 20px;
    background: #ffffff;
    border-radius: 10px; /* Bordes más redondeados */
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); /* Sombra más suave */
    transition: transform 0.3s; /* Transición suave al hacer hover */
}

form:hover {
    transform: scale(1.02); /* Efecto de zoom al pasar el mouse */
}

label {
    display: block;
    margin-bottom: 8px;
    color: #495057;
    font-weight: bold;
}

input[type="text"],
input[type="password"] {
    width: 100%;
    padding: 12px;
    margin-bottom: 20px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    box-sizing: border-box;
    transition: border-color 0.3s, box-shadow 0.3s; /* Transiciones suaves */
}

input[type="text"]:focus,
input[type="password"]:focus {
    border-color: #80bdff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Sombra al enfocar */
    outline: none;
}

input[type="submit"] {
    background-color: #007bff;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    width: 100%;
    font-size: 16px;
    transition: background-color 0.3s, transform 0.3s; /* Transiciones suaves */
}

input[type="submit"]:hover {
    background-color: #0056b3;
    transform: translateY(-2px); /* Efecto de elevación al pasar el mouse */
}

.modal {
    display: none; 
    position: fixed; 
    z-index: 1000; 
    left: 0;
    top: 0;
    width: 100%; 
    height: 100%; 
    overflow: auto; 
    background-color: rgba(0,0,0,0.5); /* Fondo más oscuro para el modal */
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto; 
    padding: 20px;
    border: 1px solid #888;
    width: 80%; 
    max-width: 400px;
    text-align: center;
    border-radius: 8px; /* Bordes redondeados para el modal */
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2); /* Sombra más suave */
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: #dc3545; /* Color rojo al pasar el mouse */
    text-decoration: none;
    cursor: pointer;
}

p {
    text-align: center;
    color: #dc3545; /* Color para mensajes de error */
    font-weight: bold;
    margin-top: 20px;
}

.modal {
    display: none; /* Oculto por defecto */
    position: fixed; /* Fijo en la pantalla */
    z-index: 1000; /* Asegúrate de que esté por encima de otros elementos */
    left: 0;
    top: 0;
    width: 100%; /* Ancho completo */
    height: 100%; /* Alto completo */
    overflow: auto; /* Permitir desplazamiento si es necesario */
    background-color: rgba(0, 0, 0, 0.7); /* Fondo oscuro con opacidad */
    transition: opacity 0.3s ease; /* Transición suave para la opacidad */
}

.modal-content {
    background-color: #ffffff; /* Fondo blanco para el contenido */
    margin: 15% auto; /* Margen superior y centrado horizontalmente */
    padding: 20px;
    border: 1px solid #888; /* Borde gris claro */
    border-radius: 8px; /* Bordes redondeados */
    width: 80%; /* Ancho del modal */
    max-width: 400px; /* Ancho máximo */
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* Sombra suave */
    text-align: center; /* Centrar texto */
    animation: fadeIn 0.3s; /* Animación de entrada */
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.close {
    color: #aaa; /* Color gris claro para el botón de cerrar */
    float: right; /* Alinear a la derecha */
    font-size: 28px; /* Tamaño de fuente grande */
    font-weight: bold; /* Negrita */
}

.close:hover,
.close:focus {
    color: #dc3545; /* Color rojo al pasar el mouse */
    text-decoration: none; /* Sin subrayado */
    cursor: pointer; /* Cambiar cursor a puntero */
}

h2 {
    color: #343a40; /* Color del título */
    margin-bottom: 15px; /* Espacio inferior */
}

p {
    color: red; /* Color del texto del párrafo */
    margin-bottom: 20px; /* Espacio inferior */
}

button {
    background-color: #007bff; /* Color de fondo azul */
    color: white; /* Color del texto */
    padding: 10px 20px; /* Espaciado interno */
    border: none; /* Sin borde */
    border-radius: 4px; /* Bordes redondeados */
    cursor: pointer; /* Cambiar cursor a puntero */
    font-size: 16px; /* Tamaño de fuente */
    margin: 5px; /* Margen entre botones */
    transition: background-color 0.3s; /* Transición suave para el color de fondo */
}

button:hover {
    background-color: #0056b3; /* Color más oscuro al pasar el mouse */
}
</style>
</html>