<?php
require_once __DIR__ . '/../../config/config.php';
requireAuth();

if (!hasRole('gerente')) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$producto_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$producto_id) {
    $_SESSION['error_message'] = 'ID de producto inválido';
    header('Location: index.php');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Producto.php';

$database = new Database();
$db = $database->getConnection();
$producto = new Producto($db);

$producto->id = $producto_id;

if ($producto->delete()) {
    $_SESSION['success_message'] = 'Producto eliminado correctamente';
    logAuditoria('ELIMINAR_PRODUCTO', 'productos', $producto_id);
} else {
    $_SESSION['error_message'] = 'Error al eliminar el producto';
}

header('Location: index.php');
exit();
?>
