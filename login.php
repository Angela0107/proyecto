<?php

$usuarios = [
    1 => ['estado' => 'activo'],
    2 => ['estado' => 'inactivo'],
];

if (empty($_GET['id_usu'])) {
    echo "<script>window.close();</script>";
} else {
    $id = $_GET['id_usu'];


    if (array_key_exists($id, $usuarios)) {

        if ($usuarios[$id]['estado'] === 'inactivo') {
            echo "<script>alert('Usuario inactivo. No se puede ingresar.'); window.close();</script>";

        } elseif ($usuarios[$id]['estado'] === 'activo') {
    
            echo "<script>alert('Usuario activo. No se puede ingresar.'); window.close();</script>";
        }
    } else {
        echo "<script>alert('Usuario no encontrado.'); window.close();</script>";
    }
}
?>

<?php

$alert = '';
session_start();
if (!empty($_POST)) {
    if (empty($_POST['nom_usu']) || empty($_POST['contr_usu'])) {

        $alert = 'Ingrese nombre y clave';
    } else {

        require_once "db.php";

        $user = mysqli_real_escape_string($conn, $_POST['nom_usu']);
        $pass = md5(mysqli_real_escape_string($conn, $_POST['contr_usu']));

        $query = mysqli_query($conn, "SELECT * FROM `usuarios` WHERE nom_usu = '$user' AND contr_usu = '$pass'");

        $result = mysqli_num_rows($query);

        if ($result === 1) {
            $data = mysqli_fetch_array($query);
            $roles = $data['id_rol'];
            if ($roles == "4") {
                $_SESSION['active'] = true;
                $_SESSION['id_rol'] = $data['id_rol'];
                $_SESSION['nom_usu'] = $data['nom_usu'];
                $_SESSION['contr_usu'] = $data['contr_usu'];
                $_SESSION['cedula'] = $data['cedula'];
                $_SESSION['estatus'] = $data['id_estatus'];
                $_SESSION['id_usu'] = $data['id_usu'];
                $_SESSION['usu_usu_logueado'] = "SI";

                header('location: supervisor/estadisticas.php');
            } else if ($roles == "3") {

                $_SESSION['active'] = true;
                $_SESSION['id_rol'] = $data['id_rol'];
                $_SESSION['nom_usu'] = $data['nom_usu'];
                $_SESSION['contr_usu'] = $data['contr_usu'];
                $_SESSION['cedula'] = $data['cedula'];
                $_SESSION['estatus'] = $data['id_estatus'];
                $_SESSION['id_usu'] = $data['id_usu'];
                $_SESSION['usu_usu_logueado'] = "SI";


                header('location: auditor/index.php');
            } else if ($roles == "2") {
                $_SESSION['active'] = true;
                $_SESSION['id_rol'] = $roles;
                $_SESSION['nom_usu'] = $data['nom_usu'];
                $_SESSION['contr_usu'] = $data['contr_usu'];
                $_SESSION['cedula'] = $data['cedula'];
                $_SESSION['estatus'] = $data['id_estatus'];
                $_SESSION['id_usu'] = $data['id_usu'];
                $_SESSION['usu_usu_logueado'] = "SI";

                header('location: operador/beneficiario.php');
            } 
        } else {

            $alert = 'El usuario o la clave son incorrectos';

            session_destroy();
        }
    }
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
 
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">

    <style media="screen">
      *,
*:before,
*:after{
    padding: 0;
    margin: 0;
    box-sizing: border-box;
}
body{
    background-color: #080710;
}
.background{
    width: 430px;
    height: 520px;
    position: absolute;
    transform: translate(-50%,-50%);
    left: 50%;
    top: 50%;
}
.background .shape{
    height: 200px;
    width: 200px;
    position: absolute;
    border-radius: 50%;
}
.shape:first-child{
    background: linear-gradient(#1876ad, #a623f6);
    left: -80px;
    top: -80px;
}
.shape:last-child{
    background:linear-gradient(to right, #ff2ff0ab, #f01940);
    right: -30px;
    bottom: -80px;
}
form{
    height: 520px;
    width: 400px;
    background-color: rgba(255,255,255,0.13);
    position: absolute;
    transform: translate(-50%,-50%);
    top: 50%;
    left: 50%;
    border-radius: 10px;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255,255,255,0.1);
    box-shadow: 0 0 40px rgba(8,7,16,0.6);
    padding: 50px 35px;
}
form *{
    font-family: 'Poppins',sans-serif;
    color: #ffffff;
    letter-spacing: 0.5px;
    outline: none;
    border: none;
}
form h3{
    font-size: 32px;
    font-weight: 500;
    line-height: 42px;
    text-align: center;
}

label{
    display: block;
    margin-top: 30px;
    font-size: 16px;
    font-weight: 500;
}
input{
    display: block;
    height: 50px;
    width: 100%;
    background-color: rgba(255,255,255,0.07);
    border-radius: 3px;
    padding: 0 10px;
    margin-top: 8px;
    font-size: 14px;
    font-weight: 300;
}
::placeholder{
    color: #e5e5e5;
}
button{
    margin-top: 50px;
    width: 100%;
    background-color: #ffffff;
    color: #080710;
    padding: 15px 0;
    font-size: 18px;
    font-weight: 600;
    border-radius: 5px;
    cursor: pointer;
}
.social{
  margin-top: 30px;
  display: flex;
}
.social div{
  background: red;
  width: 150px;
  border-radius: 3px;
  padding: 5px 10px 10px 5px;
  background-color: rgba(255,255,255,0.27);
  color: #eaf0fb;
  text-align: center;
}
.social div:hover{
  background-color: rgba(255,255,255,0.47);
}
.social .fb{
  margin-left: 25px;
}
.social i{
  margin-right: 4px;
}

    </style>
</head>
<body>
    <div class="background">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    <form method="post">
        <h3>Login</h3>

        <label for="username">Usuario</label>
        <input type="text" id="nom_usu" name="nom_usu" onKeyUP="this.value=this.value.toUpperCase();" placeholder="Usuario">

        <label for="password">Contraseña</label>
        <input type="password" id="contr_usu" name="contr_usu" placeholder="Password">

        <button>Ingresar</button>
    </form>
</body>
</html>
