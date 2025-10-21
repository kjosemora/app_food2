<?php
require_once __DIR__ . '/../../config/config.php';
requireAuth();

if (!hasRole('gerente')) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$page_title = 'Reporte de Ventas';
include __DIR__ . '/../../include/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-12 col-md-6">
            <h2><i class="bi bi-graph-up me-2"></i>Reporte de Ventas</h2>
        </div>
        <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
            <button onclick="window.print()" class="btn btn-info">
                <i class="bi bi-printer me-2"></i>Imprimir
            </button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtros</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label class="form-label">Fecha Desde</label>
                        <input type="date" class="form-control" name="desde" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label class="form-label">Fecha Hasta</label>
                        <input type="date" class="form-control" name="hasta" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-2"></i>Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Funcionalidad en desarrollo:</strong> Esta vista mostrará reportes detallados de ventas por período.
    </div>

    <div class="row">
        <div class="col-12 col-md-6 col-lg-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-receipt me-2"></i>Total Órdenes</h6>
                    <h2 class="mb-0">0</h2>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-cash me-2"></i>Ventas USD</h6>
                    <h2 class="mb-0">$0.00</h2>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-cash-coin me-2"></i>Ventas BS</h6>
                    <h2 class="mb-0">0.00 Bs</h2>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mb-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-graph-up me-2"></i>Promedio</h6>
                    <h2 class="mb-0">$0.00</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../include/footer.php'; ?>
