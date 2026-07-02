<?php
include '../db';

$cedprov = isset($_GET['cedprov']) ? intval($_GET['cedprov']) : 0;

$sql = "SELECT id_provcuenta, nrocuenta FROM provcuenta WHERE cedprov = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cedprov);
$stmt->execute();
$result = $stmt->get_result();

$cuentas = [];
while ($row = $result->fetch_assoc()) {
    $cuentas[] = $row;
}

header('Content-Type: application/json');
echo json_encode($cuentas);

$stmt->close();
$conn->close();
?>