<?php


session_start();

$id_usu = $_SESSION['id_usu'];

if(isset($_POST['monto'])){
   $monto = $_POST['monto'];
  
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica si la variable 'solicitudes' está definida
    if (isset($_POST['solicitudes'])) {
        // Obtiene la cadena
  $solicitudes = $_POST['solicitudes'];
    }

}

include '.../db.php';

 // Actualizar la tabla numsoli para incrementar el número de solicitud
 $sql_update_numsol = "UPDATE `solicitud` SET `observa`='$monto' , `estatus`='4' WHERE idsolicitud IN ($solicitudes)"; // Incrementar numsol
 if ($conn->query($sql_update_numsol) === TRUE) {


    header('location: aprobar.php');

 } else {
    $mensaje .= "Erro al actualizar  " . $conn->error;
}
