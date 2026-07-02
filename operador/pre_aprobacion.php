<?php
session_start();

// Validar sesión de usuario
if (!isset($_SESSION['id_usu'])) {
    header('Location: login.php');
    exit;
}
$id_usu = intval($_SESSION['id_usu']);

// Validar método POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: aprobar.php');
    exit;
}

// Recibir y sanitizar datos
$monto = trim($_POST['monto'] ?? '');
$fecha = trim($_POST['fecha'] ?? '');
$puntoctaes_raw = $_POST['puntoctaes'] ?? '';

// Validar campos obligatorios
if (empty($monto) || empty($fecha) || empty($puntoctaes_raw)) {
    $_SESSION['error'] = "Todos los campos son obligatorios.";
    header('Location: aprobar.php');
    exit;
}

// Validar formato de fecha (año 4 dígitos)
if (!preg_match('/^[1-9]\d{3}$/', $fecha)) {
    $_SESSION['error'] = "Formato de fecha inválido.";
    header('Location: aprobar.php');
    exit;
}

// Convertir string "1, 2, 3" a array de enteros seguros
$ids_array = array_filter(
    array_map('intval', array_map('trim', explode(',', $puntoctaes_raw))),
    fn($id) => $id > 0
);

if (empty($ids_array)) {
    $_SESSION['error'] = "IDs de punto de cuenta inválidos.";
    header('Location: aprobar.php');
    exit;
}

include '../db';

if ($conn->connect_error) {
    error_log("Error de conexión: " . $conn->connect_error);
    header('Location: error.php');
    exit;
}

try {
    $conn->begin_transaction();

    // 1. Verificar que los puntos de cuenta existan y estén pendientes
    $placeholders = implode(',', array_fill(0, count($ids_array), '?'));
    
    $sql_check = "SELECT ids_pta, nrp_pta FROM puntocta 
                  WHERE ids_pta IN ($placeholders) AND fec_pag IS NULL";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param(str_repeat('i', count($ids_array)), ...$ids_array);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        throw new Exception("No se encontraron puntos de cuenta pendientes para aprobar.");
    }

    // Obtener nrp_pta para actualizar la tabla solicitud
    $nrp_pta_array = [];
    while ($row = $result_check->fetch_assoc()) {
        $nrp_pta_array[] = $row['nrp_pta'];
    }
    $stmt_check->close();

    // 2. Actualizar puntocta: marcar como pagado
    $sql_update_pta = "UPDATE puntocta 
                       SET fec_pag = ?, usuario = ? 
                       WHERE ids_pta IN ($placeholders)";
    $stmt_pta = $conn->prepare($sql_update_pta);
    $params_pta = array_merge([$fecha, $id_usu], $ids_array);
    $types_pta = "si" . str_repeat('i', count($ids_array));
    $stmt_pta->bind_param($types_pta, ...$params_pta);
    $stmt_pta->execute();
    
    // Verificar que se actualizaron filas
    if ($stmt_pta->affected_rows === 0) {
        error_log("⚠️ puntocta: 0 filas actualizadas. IDs: " . implode(',', $ids_array));
    }
    $stmt_pta->close();

    // 3. Actualizar solicitud relacionada (CORREGIDO)
    if (!empty($nrp_pta_array)) {
        $placeholders_sol = implode(',', array_fill(0, count($nrp_pta_array), '?'));
        
        // 🛡️ CORRECCIÓN: '3' es HARDCODED, NO es parámetro → tipos: ssi + s...
        $sql_update_sol = "UPDATE solicitud 
                           SET observa = ?, fecpago = ?, estatus = '3', cedusu = ? 
                           WHERE ptocuenta IN ($placeholders_sol)";
        $stmt_sol = $conn->prepare($sql_update_sol);
        
        // Parámetros: monto(s), fecha(s), id_usu(i) + nrp_pta_array(s...)
        $params_sol = array_merge([$monto, $fecha, $id_usu], $nrp_pta_array);
        $types_sol = "ssi" . str_repeat('s', count($nrp_pta_array)); // ✅ CORREGIDO: "ssi" no "sssi"
        
        $stmt_sol->bind_param($types_sol, ...$params_sol);
        $stmt_sol->execute();
        
        // Verificar actualización
        if ($stmt_sol->affected_rows === 0) {
            error_log("⚠️ solicitud: 0 filas actualizadas. ptocuenta: " . implode(',', $nrp_pta_array));
            error_log("🔍 Debug SQL: $sql_update_sol");
            error_log("🔍 Debug params: " . print_r($params_sol, true));
        }
        $stmt_sol->close();
    }

    $conn->commit();
    $_SESSION['success'] = "✅ Aprobación registrada correctamente.";
    header('Location: aprobar.php');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    error_log("❌ Error en aprobación: " . $e->getMessage());
    $_SESSION['error'] = "Error al procesar: " . $e->getMessage();
    header('Location: aprobar.php');
    exit;
} finally {
    if ($conn) $conn->close();
}
?>