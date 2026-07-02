<?php
require_once "../db.php.php";

// Limpieza de variables para evitar fallos por comillas o caracteres extraños
$nac_prv   = mysqli_real_escape_string($conn, $_POST['nac_prv']);
$ced_prv   = mysqli_real_escape_string($conn, $_POST['ced_prv']);
$nom_prv   = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['nom_prv']))); 
$codban    = mysqli_real_escape_string($conn, $_POST['cod_ban']);
$nrocuenta = mysqli_real_escape_string($conn, $_POST['nro_cta']);
$tipcuenta = mysqli_real_escape_string($conn, $_POST['tip_cta']);

$sql_proveedor = "INSERT INTO proveedor (`nac_prv`, `ced_prv`, `nom_prv`) VALUES ('$nac_prv', '$ced_prv', '$nom_prv')";

if ($conn->query($sql_proveedor) === TRUE) {
    $proveedor_id = $conn->insert_id;

    $sql_cuenta = "INSERT INTO provcuenta (`cod_ban`, `nro_cta`, `tip_cta`, `nac_prv`, `ced_prv`) VALUES ('$codban', '$nrocuenta', '$tipcuenta', '$nac_prv', '$ced_prv')";

    if ($conn->query($sql_cuenta) === TRUE) {
        // Redirigir de vuelta al panel de proveedores de forma exitosa
        header("Location: proveedor.php");
        exit();
    } else {
        echo "Error al registrar la cuenta: " . $conn->error;
    }
} else {
    echo "Error al registrar el proveedor: " . $conn->error;
}

$conn->close();
?>
