<?php
require_once "../db.php.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $des_tpo = $_POST['des_tpo'];

    $sql =  $sql = "INSERT INTO `tiposolicitud`(`des_tpo`) VALUES ('$des_tpo')";

    if ($conn->query($sql) === TRUE) {
       $mensaje = "Registro exitoso";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

include 'nav/index.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar ayuda</title>
</head>
<body>
    <div class="container">
        <form action="" method="POST">
            <h1>Crear nueva ayuda</h1>

            <input type="text" id="des_tpo" name="des_tpo" placeholder="Tipo ayudas" required>

            <input type="submit" value="Agregar ayuda">
            <?php 
            echo $mensaje
            ?>
        </form>


    </div>
</body>
</html>
<style>
.wrapper .sidebar ul li a span.title {
      display: inline-block;
      font-size: 16px;
    }

.container {
    max-width: 400px; 
    margin: 50px auto; 
    padding: 20px; 
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
    margin-top: 250px;
}

.btn-volver{
        height: 37px;
        width: 118px;
        margin-top: 1px;
        margin-left: 109px;
        margin-bottom: 1px;
        background-color: #2e4ead;
        color: white;
        border: none;
        padding: 10px 20px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        cursor: pointer;
        border-radius: 5px;
    }


    h1 {
        text-align: center;
        color: #333;
    }

    input[type="text"] {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }

    input[type="submit"] {
        background-color: #28a745;
        color: white;
        padding: 10px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        width: 100%;
        font-size: 16px;
    }

    input[type="submit"]:hover {
        background-color: #218838;
    }
</style>