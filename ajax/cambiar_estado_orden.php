<?php
require_once __DIR__ . '/../config/config.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['orden_id']) || !isset($input['estado'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

$orden_id = (int)$input['orden_id'];
$nuevo_estado = $input['estado'];

// Validar estados permitidos
$estados_validos = ['Pendiente', 'En Preparación', 'Lista', 'Entregada', 'Pagada'];
if (!in_array($nuevo_estado, $estados_validos)) {
    echo json_encode(['success' => false, 'message' => 'Estado no válido']);
    exit();
}

require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "UPDATE ordenes SET estado = :estado WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':estado', $nuevo_estado);
    $stmt->bindParam(':id', $orden_id);
    
    if ($stmt->execute()) {
        logAuditoria('CAMBIO_ESTADO_ORDEN', 'ordenes', $orden_id);
        echo json_encode([
            'success' => true, 
            'message' => 'Estado actualizado correctamente',
            'nuevo_estado' => $nuevo_estado
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
