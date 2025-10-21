<?php
require_once __DIR__ . '/../../config/config.php';
requireAuth();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Producto.php';

$database = new Database();
$db = $database->getConnection();
$producto = new Producto($db);

$productos = $producto->getAll();

$page_title = 'Productos';
include __DIR__ . '/../../include/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-12 col-md-6">
            <h2><i class="bi bi-box me-2"></i>Productos</h2>
        </div>
        <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
            <?php if (hasRole('gerente')): ?>
                <a href="crear.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Nuevo Producto
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <?php if (!empty($productos)): ?>
            <?php foreach ($productos as $prod): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                    <div class="card h-100">
                        <?php if (!empty($prod['imagen'])): ?>
                            <img src="<?php echo BASE_URL . '/' . $prod['imagen']; ?>" 
                                 class="card-img-top producto-img" 
                                 alt="<?php echo htmlspecialchars($prod['nombre']); ?>">
                        <?php else: ?>
                            <div class="card-img-top producto-img bg-light d-flex align-items-center justify-content-center">
                                <i class="bi bi-image fs-1 text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($prod['nombre']); ?></h5>
                            <p class="card-text small text-muted">
                                <?php echo htmlspecialchars(substr($prod['descripcion'] ?? '', 0, 60)); ?>
                                <?php if (strlen($prod['descripcion'] ?? '') > 60) echo '...'; ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-primary fw-bold">
                                    <?php echo formatCurrency($prod['precio_usd'], 'USD'); ?>
                                </span>
                                <small class="text-muted">
                                    <?php echo formatCurrency($prod['precio_bs'], 'BS'); ?>
                                </small>
                            </div>
                            <span class="badge bg-<?php echo $prod['disponible'] ? 'success' : 'danger'; ?>">
                                <?php echo $prod['disponible'] ? 'Disponible' : 'No disponible'; ?>
                            </span>
                        </div>
                        <?php if (hasRole('gerente')): ?>
                            <div class="card-footer">
                                <div class="btn-group w-100" role="group">
                                    <a href="editar.php?id=<?php echo $prod['id']; ?>" 
                                       class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                    <button class="btn btn-sm btn-danger" 
                                            onclick="confirmarEliminar(<?php echo $prod['id']; ?>)">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                        <p class="text-muted">No hay productos registrados</p>
                        <?php if (hasRole('gerente')): ?>
                            <a href="crear.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Crear Primer Producto
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmarEliminar(id) {
    if (confirm('¿Está seguro de eliminar este producto?')) {
        window.location.href = 'eliminar.php?id=' + id;
    }
}
</script>

<?php include __DIR__ . '/../../include/footer.php'; ?>
