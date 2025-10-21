<?php
require_once __DIR__ . '/../../config/config.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$orden_id = (int)$_POST['orden_id'];
$total_usd = floatval($_POST['total_usd']);
$total_bs = floatval($_POST['total_bs']);
$tasa_cambio = floatval($_POST['tasa_cambio']);
$pagos_json = $_POST['pagos_json'];

if (!$orden_id || !$pagos_json) {
    $_SESSION['error_message'] = 'Datos de pago incompletos';
    header('Location: ver.php?id=' . $orden_id);
    exit();
}

$pagos = json_decode($pagos_json, true);

require_once __DIR__ . '/../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Iniciar transacción
    $db->beginTransaction();
    
    // Actualizar estado de la orden
    $query = "UPDATE ordenes SET estado = 'Pagada' WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $orden_id);
    $stmt->execute();
    
    // Registrar pagos en la tabla pagos
    $query_pago = "INSERT INTO pagos 
                   (orden_id, metodo_pago, monto, moneda, referencia) 
                   VALUES (:orden_id, :metodo, :monto, :moneda, :referencia)";
    $stmt_pago = $db->prepare($query_pago);
    
    // Registrar cada método de pago usado
    if ($pagos['efectivo_usd'] > 0) {
        $stmt_pago->execute([
            ':orden_id' => $orden_id,
            ':metodo' => 'Efectivo USD',
            ':monto' => $pagos['efectivo_usd'],
            ':moneda' => 'USD',
            ':referencia' => 'Tasa: ' . $tasa_cambio
        ]);
    }
    
    if ($pagos['efectivo_bs'] > 0) {
        $stmt_pago->execute([
            ':orden_id' => $orden_id,
            ':metodo' => 'Efectivo BS',
            ':monto' => $pagos['efectivo_bs'],
            ':moneda' => 'BS',
            ':referencia' => 'Tasa: ' . $tasa_cambio
        ]);
    }
    
    if ($pagos['tarjeta_bs'] > 0) {
        $stmt_pago->execute([
            ':orden_id' => $orden_id,
            ':metodo' => 'Tarjeta',
            ':monto' => $pagos['tarjeta_bs'],
            ':moneda' => 'BS',
            ':referencia' => 'Tasa: ' . $tasa_cambio
        ]);
    }
    
    if ($pagos['pago_movil'] > 0) {
        $stmt_pago->execute([
            ':orden_id' => $orden_id,
            ':metodo' => 'Pago Móvil',
            ':monto' => $pagos['pago_movil'],
            ':moneda' => 'BS',
            ':referencia' => 'Tasa: ' . $tasa_cambio
        ]);
    }
    
    // Registrar cambio si existe (como nota en la orden)
    if ($pagos['cambio_usd'] > 0 || $pagos['cambio_bs'] > 0) {
        $cambio_info = '';
        if ($pagos['cambio_usd'] > 0) {
            $cambio_info .= 'Cambio USD: $' . number_format($pagos['cambio_usd'], 2);
        }
        if ($pagos['cambio_bs'] > 0) {
            if ($cambio_info) $cambio_info .= ' | ';
            $cambio_info .= 'Cambio BS: ' . number_format($pagos['cambio_bs'], 2) . ' Bs';
        }
        
        // Actualizar notas de la orden con el cambio
        $query_update = "UPDATE ordenes SET notas = CONCAT(COALESCE(notas, ''), '\n[CAMBIO: ', :cambio, ']') WHERE id = :id";
        $stmt_update = $db->prepare($query_update);
        $stmt_update->execute([
            ':cambio' => $cambio_info,
            ':id' => $orden_id
        ]);
    }
    
    // Confirmar transacción
    $db->commit();
    
    logAuditoria('PAGAR_ORDEN', 'ordenes', $orden_id);
    
    $_SESSION['success_message'] = 'Pago procesado correctamente. Orden #' . $orden_id . ' pagada.';
    
    // Redirigir a la vista de la orden
    header('Location: ver.php?id=' . $orden_id);
    exit();
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    $_SESSION['error_message'] = 'Error al procesar el pago: ' . $e->getMessage();
    header('Location: ver.php?id=' . $orden_id);
    exit();
}
?>
