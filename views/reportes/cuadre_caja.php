<?php
require_once __DIR__ . '/../../config/config.php';
requireAuth();

if (!hasRole('gerente')) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$page_title = 'Cuadre de Caja';
include __DIR__ . '/../../include/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-12 col-md-6">
            <h2><i class="bi bi-cash-stack me-2"></i>Cuadre de Caja</h2>
        </div>
        <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
            <button onclick="window.print()" class="btn btn-info">
                <i class="bi bi-printer me-2"></i>Imprimir
            </button>
        </div>
    </div>

    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Funcionalidad en desarrollo:</strong> Esta vista permite realizar el cuadre de caja diario.
    </div>

    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Resumen del Día</h5>
                </div>
                <div class="card-body">
                    <p><strong>Fecha:</strong> <?php echo date('d/m/Y'); ?></p>
                    <p class="text-muted">Aquí se mostrará el resumen de ventas y pagos del día.</p>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Totales</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Efectivo USD:</strong>
                        <h4 class="text-success">$0.00</h4>
                    </div>
                    <div class="mb-3">
                        <strong>Efectivo BS:</strong>
                        <h4 class="text-success">0.00 Bs</h4>
                    </div>
                    <div class="mb-3">
                        <strong>Pago Móvil:</strong>
                        <h4 class="text-info">0.00 Bs</h4>
                    </div>
                    <div>
                        <strong>Tarjeta:</strong>
                        <h4 class="text-primary">$0.00</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../include/footer.php'; ?>
