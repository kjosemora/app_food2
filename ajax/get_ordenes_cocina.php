<?php
require_once __DIR__ . '/../config/config.php';
requireAuth();

if ($_SESSION['user_rol'] !== 'cocina') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Orden.php';

$database = new Database();
$db = $database->getConnection();
$orden = new Orden($db);

$ordenes_cocina = $orden->getOrdenesParaCocina();

// Generar HTML
ob_start();
if (!empty($ordenes_cocina)):
?>
<div class="row">
    <?php foreach ($ordenes_cocina as $orden_item): ?>
        <div class="col-12 col-md-6 col-lg-4 mb-3">
            <div class="card orden-card border-<?php echo $orden_item['estado'] === 'Pendiente' ? 'danger' : 'warning'; ?>">
                <div class="card-header bg-<?php echo $orden_item['estado'] === 'Pendiente' ? 'danger' : 'warning'; ?> text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>Orden #<?php echo $orden_item['id']; ?></strong>
                        <small><?php echo date('H:i', strtotime($orden_item['fecha'])); ?></small>
                    </div>
                    <small>Mesero: <?php echo htmlspecialchars($orden_item['mesero_nombre']); ?></small>
                </div>
                <div class="card-body">
                    <h6>Productos:</h6>
                    <ul class="list-unstyled mb-2">
                        <?php if (!empty($orden_item['detalles'])): ?>
                            <?php foreach ($orden_item['detalles'] as $detalle): ?>
                                <li>
                                    <strong><?php echo $detalle['cantidad']; ?>x</strong> 
                                    <?php echo htmlspecialchars($detalle['producto_nombre']); ?>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <?php if (!empty($orden_item['notas'])): ?>
                        <div class="alert alert-info py-2 small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            <?php echo htmlspecialchars($orden_item['notas']); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <?php if ($orden_item['estado'] === 'Pendiente'): ?>
                        <button class="btn btn-warning btn-sm w-100 btn-cambiar-estado" 
                                data-orden-id="<?php echo $orden_item['id']; ?>" 
                                data-estado="En Preparación">
                            <i class="bi bi-clock me-1"></i>Iniciar Preparación
                        </button>
                    <?php else: ?>
                        <button class="btn btn-success btn-sm w-100 btn-cambiar-estado" 
                                data-orden-id="<?php echo $orden_item['id']; ?>" 
                                data-estado="Lista">
                            <i class="bi bi-check-circle me-1"></i>Marcar como Lista
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php
else:
?>
<div class="text-center py-5">
    <i class="bi bi-check-circle fs-1 text-success"></i>
    <p class="text-muted mt-3">No hay órdenes pendientes en cocina</p>
</div>
<?php
endif;

$html = ob_get_clean();

echo json_encode([
    'success' => true,
    'html' => $html,
    'nuevas_ordenes' => 0 // Implementar lógica para detectar nuevas órdenes
]);
?>
