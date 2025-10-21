<?php
require_once __DIR__ . '/../../config/config.php';
requireAuth();

$page_title = 'Clientes';
include __DIR__ . '/../../include/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-12 col-md-6">
            <h2><i class="bi bi-people me-2"></i>Clientes</h2>
        </div>
        <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
            <a href="crear.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Nuevo Cliente
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Funcionalidad en desarrollo:</strong> Esta vista muestra el listado de clientes registrados.
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th class="d-none d-md-table-cell">Teléfono</th>
                            <th class="d-none d-lg-table-cell">Dirección</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="bi bi-inbox fs-3 text-muted d-block mb-2"></i>
                                <p class="text-muted">No hay clientes registrados</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../include/footer.php'; ?>
