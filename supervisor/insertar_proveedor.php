<?php
require_once "./db.php";

$nac_prv = $_POST['nac_prv'];
$ced_prv = $_POST['ced_prv'];
$nom_prv = strtoupper(trim($_POST['nom_prv'])); 
$codban = $_POST['cod_ban'];
$nrocuenta = $_POST['nro_cta'];
$tipcuenta = $_POST['tip_cta'];

$sql_proveedor = "INSERT INTO proveedor (`nac_prv`, `ced_prv`, `nom_prv`) VALUES ('$nac_prv', '$ced_prv', '$nom_prv')";

if ($conn->query($sql_proveedor) === TRUE) {
    $proveedor_id = $conn->insert_id;

    $sql_cuenta = "INSERT INTO provcuenta (`cod_ban`, `nro_cta`, `tip_cta`, `nac_prv`, `ced_prv`) VALUES ('$codban', '$nrocuenta', '$tipcuenta', '$nac_prv', '$ced_prv')";

    if ($conn->query($sql_cuenta) === TRUE) {
        header("Location: aprobar.php");
        exit();
    } else {
        echo "Error al registrar la cuenta: " . $conn->error;
    }
} else {
    echo "Error al registrar el proveedor: " . $conn->error;
}

$conn->close();
?>