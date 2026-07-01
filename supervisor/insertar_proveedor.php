<?php
$servername = getenv("DB_HOST")     ?: "localhost";
$username   = getenv("DB_USER")     ?: "root";
$password   = getenv("DB_PASSWORD") ?: "";
$dbname     = getenv("DB_NAME")     ?: "diseño_ayudas";
$port       = getenv("DB_PORT")     ?: "3306";

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

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