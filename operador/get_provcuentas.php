<?php
// get_provcuentas.php
header('Content-Type: application/json; charset=utf-8');

// Validar que exista el parámetro
if (!isset($_GET['ced_prv']) || empty(trim($_GET['ced_prv']))) {
    echo json_encode([]);
    exit;
}

include '../conexion2.php';

try {
    $ced_prv = trim($_GET['ced_prv']);
    
    // ✅ Consulta con campos consistentes y filtro explícito
    $query = "
    SELECT DISTINCT p.ids_prc, p.nro_cta, p.tip_cta, b.nom_ban 
    FROM provcuenta p 
    INNER JOIN bancos b ON p.cod_ban = b.cod_ban 
    WHERE p.ced_prv = :ced_prv 
    ORDER BY p.nro_cta 
    LIMIT 1
    ";
    
    $stmt = $conexion->prepare($query);
    
    // ✅ Binding explícito con tipo de dato (evita problemas de coincidencia)
    $stmt->bindParam(':ced_prv', $ced_prv, PDO::PARAM_STR);
    
    $stmt->execute();
    $provcuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($provcuentas, JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error en la consulta', 
        'detalle' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>