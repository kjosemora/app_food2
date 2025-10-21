<?php
require_once __DIR__ . '/../../config/config.php';
requireAuth();

if (!hasRole('gerente')) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Configuracion.php';

$database = new Database();
$db = $database->getConnection();
$config = new Configuracion($db);

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tasa_cambio = floatval($_POST['tasa_cambio'] ?? 0);
    $actualizar_precios = isset($_POST['actualizar_precios']);
    
    if ($tasa_cambio > 0) {
        if ($config->setTasaCambio($tasa_cambio)) {
            // Si se marcó la opción, actualizar precios de productos
            if ($actualizar_precios) {
                require_once __DIR__ . '/../../models/Producto.php';
                $producto = new Producto($db);
                $producto->updatePricesByExchangeRate($tasa_cambio);
                $mensaje = 'Tasa de cambio actualizada y precios de productos recalculados';
            } else {
                $mensaje = 'Tasa de cambio actualizada correctamente';
            }
            $tipo_mensaje = 'success';
            logAuditoria('ACTUALIZAR_TASA_CAMBIO', 'configuracion', null);
        } else {
            $mensaje = 'Error al actualizar la tasa de cambio';
            $tipo_mensaje = 'danger';
        }
    } else {
        $mensaje = 'La tasa de cambio debe ser mayor a 0';
        $tipo_mensaje = 'warning';
    }
}

$tasa_actual = $config->getTasaCambio();

$page_title = 'Configuración';
include __DIR__ . '/../../include/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-12">
            <h2><i class="bi bi-gear me-2"></i>Configuración del Sistema</h2>
        </div>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-<?php echo $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-currency-exchange me-2"></i>Tasa de Cambio</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Tasa de Cambio (USD a BS)</label>
                            <div class="input-group">
                                <span class="input-group-text">1 USD =</span>
                                <input type="number" 
                                       class="form-control" 
                                       name="tasa_cambio" 
                                       step="0.01" 
                                       min="0.01"
                                       value="<?php echo number_format($tasa_actual, 2, '.', ''); ?>" 
                                       required>
                                <span class="input-group-text">Bs</span>
                            </div>
                            <small class="form-text text-muted">
                                Tasa actual: 1 USD = <?php echo number_format($tasa_actual, 2); ?> Bs
                            </small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="actualizar_precios" 
                                       id="actualizar_precios"
                                       checked>
                                <label class="form-check-label" for="actualizar_precios">
                                    <strong>Recalcular precios de productos en Bs</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Si activa esta opción, todos los precios en bolívares de los productos se actualizarán automáticamente con la nueva tasa.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-save me-2"></i>Actualizar Tasa de Cambio
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 mt-3 mt-lg-0">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información del Sistema</h5>
                </div>
                <div class="card-body">
                    <p><strong>Nombre de la Aplicación:</strong> <?php echo APP_NAME; ?></p>
                    <p><strong>Versión:</strong> 1.0.0</p>
                    <p><strong>Base de Datos:</strong> MySQL</p>
                    <p class="mb-0"><strong>Última actualización:</strong> <?php echo date('d/m/Y H:i'); ?></p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="bi bi-tools me-2"></i>Otras Configuraciones</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Funcionalidades adicionales en desarrollo:</p>
                    <ul class="text-muted">
                        <li>Configuración de impresoras</li>
                        <li>Métodos de pago personalizados</li>
                        <li>Configuración de impuestos</li>
                        <li>Backup automático</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../include/footer.php'; ?>
