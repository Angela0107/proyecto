<?php
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'diseño_ayudas';

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insertar un nuevo usuario a la base de datos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cedula = $_POST['cedula'];
    $nom_usu = $_POST['nom_usu'];
    $contr_usu = $_POST['contr_usu'];
    $contr = md5 ($contr_usu);
    $id_rol = '2';
    $id_estatus = $_POST['id_estatus'];
    $nombre = $_POST['nombre'];

    $sql = "INSERT INTO `usuarios`(`cedula`, `nom_usu`, `contr_usu`, `id_rol`, `id_estatus`, `nombre`) 
            VALUES ('$cedula','$nom_usu','$contr','$id_rol','$id_estatus','$nombre')";

    if ($conn->query($sql) === TRUE) {
        header('location: aprobar.php');
    } else {
        echo "<div class='alert alert-danger'>Error: " . $sql . "<br>" . $conn->error . "</div>";
    }
}

include 'nav/index.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Agregar usuarios</title>
    <style>
        .wrapper .top_navbar .hamburger {
            width: 220px;
            height: 100%;
            background: #2e4ead;
            padding: 15px 17px;
            border-top-left-radius: 20px;
            cursor: pointer;
        }

        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-left: 30%;
            margin-top: 73px;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #343a40;
            /* Un gris oscuro */
            margin-bottom: 20px;
        }

        .btn-custom {
            background-color: #007bff;
            color: white;
            width: 100%;
            font-size: 16px;
            margin-top: 10px;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        .wrapper .sidebar ul li a span.title {
      display: inline-block;
      font-size: 16px;
    }
    </style>
</head>

<body>

    <div class="container">
        <form action="" method="POST">
            <h1>Crear nueva usuario</h1>

            <div class="form-row">
            <div class="col-md-12">
                <label for="nombre" class="form-label">Nombre completo</label>
                <input type="text" class="form-control" name="nombre" id="nombre" required>
            </div></div>
<br>
            <div class="form-row">
              <div class="col-md-6">
                <label for="cedula" class="form-label">Cédula</label>
                <input type="text" class="form-control" name="cedula" id="cedula"  minlength="4" maxlength="9" required>
            </div>
           
              <div class="col-md-6">
                <label for="nom_usu" class="form-label">Nombre de usuario</label>
                <input type="text" class="form-control" name="nom_usu" id="nom_usu" required>
            </div>
            </div>
            <br>
            
            <div class="form-row">
              <div class="col-md-6">
                <label for="contr_usu" class="form-label">Contraseña</label>
                <input type="password" class="form-control" name="contr_usu" id="contr_usu" required>
            </div>
            
            <div class="col-md-6">
                <label for="id_rol" class="form-label">Roles</label>
                <div class="form-control" name="id_rol" id="id_rol" required>
                    <option value="2">Operador</option>
                </div>
            </div>
        </div>

              <div class="col-md-13">
                <label for="id_estatus" class="form-label">Tipo Usuario</label>
                <select class="form-control" name="id_estatus" id="id_estatus" required>
                    <option value="" disabled selected>Seleccione un estatus</option> <!-- Opción por defecto -->
                    <option value="1">Activo</option>
                    <option value="2">Inactivo</option>
                </select>
            </div>

            

            <input type="submit" value="Agregar usuario" class="btn btn-custom">
            <button type="button" onclick="window.history.back();" class="btn btn-secondary" style="margin-top: 10px;">Volver</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>