<?php
require_once __DIR__ . '/../../config/config.php';
requireAuth();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Orden.php';

$database = new Database();
$db = $database->getConnection();
$orden = new Orden($db);

// Filtros
$filtros = [];
if ($_SESSION['user_rol'] === 'mesero') {
    $filtros['mesero_id'] = $_SESSION['user_id'];
}

$ordenes = $orden->getAll($filtros);

$page_title = 'Órdenes';
include __DIR__ . '/../../include/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-12 col-md-6">
            <h2><i class="bi bi-receipt me-2"></i>Órdenes</h2>
        </div>
        <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
            <?php if (hasRole('mesero') || hasRole('gerente')): ?>
                <a href="crear.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Nueva Orden
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Orden</th>
                            <th class="d-none d-md-table-cell">Fecha</th>
                            <?php if ($_SESSION['user_rol'] !== 'mesero'): ?>
                                <th class="d-none d-lg-table-cell">Mesero</th>
                            <?php endif; ?>
                            <th class="d-none d-lg-table-cell">Cliente</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($ordenes)): ?>
                            <?php foreach ($ordenes as $orden_item): ?>
                                <tr>
                                    <td><strong>#<?php echo $orden_item['id']; ?></strong></td>
                                    <td class="d-none d-md-table-cell">
                                        <?php echo date('d/m/Y H:i', strtotime($orden_item['fecha'])); ?>
                                    </td>
                                    <?php if ($_SESSION['user_rol'] !== 'mesero'): ?>
                                        <td class="d-none d-lg-table-cell">
                                            <?php echo htmlspecialchars($orden_item['mesero_nombre']); ?>
                                        </td>
                                    <?php endif; ?>
                                    <td class="d-none d-lg-table-cell">
                                        <?php echo htmlspecialchars($orden_item['cliente_nombre'] ?? 'N/A'); ?>
                                    </td>
                                    <td>
                                        <strong><?php echo formatCurrency($orden_item['total_usd'], 'USD'); ?></strong>
                                        <br class="d-md-none">
                                        <small class="text-muted"><?php echo formatCurrency($orden_item['total_bs'], 'BS'); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $orden_item['estado'] === 'Pagada' ? 'success' : 
                                                ($orden_item['estado'] === 'Pendiente' ? 'danger' : 
                                                ($orden_item['estado'] === 'Lista' ? 'info' : 'warning')); 
                                        ?>">
                                            <?php echo $orden_item['estado']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="ver.php?id=<?php echo $orden_item['id']; ?>" 
                                               class="btn btn-info" title="Ver">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($orden_item['estado'] !== 'Pagada'): ?>
                                                <a href="editar.php?id=<?php echo $orden_item['id']; ?>" 
                                                   class="btn btn-warning" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bi bi-inbox fs-3 text-muted d-block mb-2"></i>
                                    <p class="text-muted">No hay órdenes registradas</p>
                                    <?php if (hasRole('mesero') || hasRole('gerente')): ?>
                                        <a href="crear.php" class="btn btn-primary">
                                            <i class="bi bi-plus-circle me-2"></i>Crear Primera Orden
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../include/footer.php'; ?>
