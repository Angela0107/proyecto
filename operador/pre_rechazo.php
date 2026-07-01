<?php


session_start();

$id_usu = $_SESSION['id_usu'];

if(isset($_POST['monto'])){
 echo   $monto = $_POST['monto'];
  
}

if(isset($_POST['fecha'])){
 echo   $fechanueva = $_POST['fecha'];
    
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['puntoctaes'])) {

    echo    $puntoctaes = $_POST['puntoctaes'];
    }

}

include '../db.php';

 $sql_update_numsol = "UPDATE puntocta SET fec_pag = '$fechanueva' , usuario = '$id_usu' WHERE ids_pta IN ($puntoctaes)";
 if ($conn->query($sql_update_numsol) === TRUE) {


    $sql_numsol = "SELECT nrp_pta FROM puntocta WHERE ids_pta IN ($puntoctaes)"; 
    $result_numsol = $conn->query($sql_numsol);
    
    $nropto_array = []; 
    
    if ($result_numsol->num_rows > 0) {
        while ($row_numsol = $result_numsol->fetch_assoc()) {
            $nropto_array[] = $row_numsol['nrp_pta']; 
        }
    } else {
        $mensaje .= "Error al buscar número de solicitud: " . $conn->error;
    }
    
    $nropto_string = implode(',', $nropto_array);


    echo $nropto_string;
    
    $sql_update_numsol = "UPDATE `solicitud` SET `observa`='$monto', `fecpago`= '$fechanueva',`estatus`='5' , `cedusu` = '$id_usu'  WHERE `ptocuenta` IN ($nropto_string);"; // Incrementar numsol
 if ($conn->query($sql_update_numsol) === TRUE) {

    header('location: aprobar.php');

 } else {
    $mensaje .= "Erro al actualizar  " . $conn->error;
}

 } else {
     $mensaje .= " pero ocurrió un error al actualizar el número de solicitud: " . $conn->error;
 }