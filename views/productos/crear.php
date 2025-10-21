<?php
require_once __DIR__ . '/../../config/config.php';
requireAuth();

if (!hasRole('gerente')) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Configuracion.php';

$database = new Database();
$db = $database->getConnection();
$producto = new Producto($db);
$config = new Configuracion($db);

$tasa_cambio = $config->getTasaCambio();

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = sanitize($_POST['nombre'] ?? '');
    $descripcion = sanitize($_POST['descripcion'] ?? '');
    $precio_usd = floatval($_POST['precio_usd'] ?? 0);
    $categoria_id = !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    
    if (empty($nombre) || $precio_usd <= 0) {
        $mensaje = 'El nombre y precio son obligatorios';
        $tipo_mensaje = 'warning';
    } else {
        $producto->nombre = $nombre;
        $producto->descripcion = $descripcion;
        $producto->precio_usd = $precio_usd;
        $producto->precio_bs = $precio_usd * $tasa_cambio;
        $producto->categoria_id = $categoria_id;
        $producto->disponible = $disponible;
        
        if ($producto->create()) {
            $_SESSION['success_message'] = 'Producto creado correctamente';
            logAuditoria('CREAR_PRODUCTO', 'productos', $db->lastInsertId());
            header('Location: index.php');
            exit();
        } else {
            $mensaje = 'Error al crear el producto';
            $tipo_mensaje = 'danger';
        }
    }
}

$page_title = 'Nuevo Producto';
include __DIR__ . '/../../include/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-12">
            <h2><i class="bi bi-plus-circle me-2"></i>Nuevo Producto</h2>
        </div>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-box me-2"></i>Información del Producto</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="formProducto">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">Nombre del Producto *</label>
                                <input type="text" 
                                       class="form-control" 
                                       name="nombre" 
                                       placeholder="Ej: Hamburguesa Clásica"
                                       required>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" 
                                          name="descripcion" 
                                          rows="3"
                                          placeholder="Descripción del producto..."></textarea>
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Precio en USD *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control" 
                                           name="precio_usd" 
                                           id="precio_usd"
                                           step="0.01" 
                                           min="0.01"
                                           placeholder="0.00"
                                           required>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Precio en BS (Calculado)</label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           id="precio_bs"
                                           readonly
                                           placeholder="0.00">
                                    <span class="input-group-text">Bs</span>
                                </div>
                                <small class="text-muted">Tasa: 1 USD = <?php echo number_format($tasa_cambio, 2); ?> Bs</small>
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Categoría</label>
                                <select class="form-select" name="categoria_id">
                                    <option value="">Sin categoría</option>
                                    <option value="1">Hamburguesas</option>
                                    <option value="2">Bebidas</option>
                                    <option value="3">Postres</option>
                                    <option value="4">Extras</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Estado</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="disponible" 
                                           id="disponible"
                                           checked>
                                    <label class="form-check-label" for="disponible">
                                        Disponible para venta
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Guardar Producto
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 mt-3 mt-lg-0">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información</h5>
                </div>
                <div class="card-body">
                    <p><strong>Tasa de Cambio Actual:</strong></p>
                    <h4 class="text-primary">1 USD = <?php echo number_format($tasa_cambio, 2); ?> Bs</h4>
                    <hr>
                    <p class="small text-muted mb-0">
                        <i class="bi bi-lightbulb me-1"></i>
                        El precio en bolívares se calculará automáticamente usando la tasa de cambio actual.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Calcular precio en BS automáticamente
document.getElementById('precio_usd').addEventListener('input', function() {
    const precioUsd = parseFloat(this.value) || 0;
    const tasaCambio = <?php echo $tasa_cambio; ?>;
    const precioBs = precioUsd * tasaCambio;
    document.getElementById('precio_bs').value = precioBs.toFixed(2);
});
</script>

<?php include __DIR__ . '/../../include/footer.php'; ?>
