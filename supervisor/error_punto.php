<?php
include 'nav/index.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprobado</title>
    <style>
        .wrapper .top_navbar .hamburger {
            width: 220px;
            height: 100%;
            background: #2e4ead;
            padding: 15px 17px;
            border-top-left-radius: 20px;
            cursor: pointer;
        }

        .container {
            background-color:rgb(167, 53, 53);
            /* Color verde */
            color: white;
            /* Texto blanco */
            padding: 60px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            font-size: 24px;
            height: 151px;
            width: 1040px;
            margin-left: 264px;
            margin-top: 200px;
        }

        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: 0 auto;
            text-align: center;
        }

        .btn-primary {
            background-color: rgb(253, 128, 134);
            border-color: rgb(243, 72, 72);
            transition: background-color 0.3s, border-color 0.3s;
        }

        .btn-primary:hover {
            background-color: rgb(253, 128, 134);
            border-color:rgb(243, 72, 72);
        }

        .btn-sm {

            padding: 10px 20px;
            font-size: 14px;
            width: 100.66666px;
            height: 44.66666px;
            margin-left: 200px;
            margin-top: 20px;
            border: 2px solid #0098ff;
            /* Color del borde */
            border-radius: 5px;
            /* Bordes redondeados */
            background-color: #0098ff;
            color: white;
            /* Color del texto */
            transition: background-color 0.3s, border-color 0.3s, color 0.3s;
            /* Transiciones suaves */
        }
    </style>
 <script>
        // Redirigir después de 5 segundos
        setTimeout(function() {
            window.location.href = 'estadisticas.php';
        }, 5000); // 1000 milisegundos = 1 segundos
    </script> 
</head>

<?php
if (!isset($_SESSION['puntoctaes'])) {
    $_SESSION['puntoctaes'] = []; // Inicializar el array de puntoctaes
} 
if (!isset($_SESSION['solicitudes'])) {
    $_SESSION['solicitudes'] = []; // Inicializar el array de puntoctaes
} 
$puntoctaes = [];
$_SESSION['puntoctaes'] = [];
$_SESSION['solicitudes'] = [];
?>

<body>

    <div class="container">
        <?php
        // Mensaje que se mostrará
        echo "Error debido a que no existe ese punto en el sistema SISSA";
        ?>
    </div>

    <form action='estadisticas.php' method='post' style='display:inline;'>
        <input type='hidden' name='id' value=''>
        <center><button type='submit' class='btn btn-primary btn-sm'>Volver</button></center>
    </form>
</body>

</html>