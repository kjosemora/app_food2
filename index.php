<?php
require_once 'config/config.php';
requireAuth();

require_once 'config/database.php';
require_once 'models/Orden.php';
require_once 'models/Configuracion.php';

$database = new Database();
$db = $database->getConnection();

$orden = new Orden($db);
$config = new Configuracion($db);

// Obtener estadísticas según el rol
$hoy = date('Y-m-d');
$estadisticas = [];

if ($_SESSION['user_rol'] === 'mesero') {
    $estadisticas = $orden->getEstadisticas($hoy, $hoy, $_SESSION['user_id']);
    $ordenes_activas = $orden->getAll([
        'mesero_id' => $_SESSION['user_id'],
        'limite' => 5
    ]);
} elseif ($_SESSION['user_rol'] === 'cocina') {
    $ordenes_cocina = $orden->getOrdenesParaCocina();
} else { // gerente
    $estadisticas = $orden->getEstadisticas($hoy, $hoy);
    $ordenes_recientes = $orden->getAll(['limit' => 10]);
}

$tasa_cambio = $config->getTasaCambio();

include 'includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard - <?php echo ucfirst($_SESSION['user_rol']); ?>
            </h2>
        </div>
    </div>

    <?php if ($_SESSION['user_rol'] === 'mesero'): ?>
        <!-- Dashboard Mesero -->
        <div class="row">
            <div class="col-6 col-md-3 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="card-title"><i class="bi bi-receipt me-2"></i>Órdenes Hoy</h6>
                        <h2 class="mb-0"><?php echo $estadisticas['total_ordenes'] ?? 0; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-title"><i class="bi bi-cash me-2"></i>Ventas USD</h6>
                        <h2 class="mb-0"><?php echo formatCurrency($estadisticas['total_usd'] ?? 0, 'USD'); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="card-title"><i class="bi bi-cash-coin me-2"></i>Total BS</h6>
                        <h2 class="mb-0"><?php echo formatCurrency($estadisticas['total_bs'] ?? 0, 'BS'); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h6 class="card-title"><i class="bi bi-graph-up me-2"></i>Ticket Promedio</h6>
                        <h2 class="mb-0"><?php echo formatCurrency($estadisticas['promedio_usd'] ?? 0, 'USD'); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-8 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Mis Órdenes Activas</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Orden</th>
                                        <th>Mesero</th>
                                        <th>Cliente</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ordenes_recientes as $orden_item): ?>
                                        <tr>
                                            <td><strong>#<?php echo $orden_item['id']; ?></strong></td>
                                            <td><?php echo htmlspecialchars($orden_item['mesero_nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($orden_item['cliente_nombre']); ?></td>
                                            <td><?php echo formatCurrency($orden_item['total_usd'], 'USD'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $orden_item['estado'] === 'Pagada' ? 'success' : 
                                                        ($orden_item['estado'] === 'Pendiente' ? 'danger' : 'warning'); 
                                                ?>">
                                                    <?php echo $orden_item['estado']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="views/ordenes/ver.php?id=<?php echo $orden_item['id']; ?>" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4 mb-3">
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="bi bi-currency-exchange me-2"></i>Tasa de Cambio</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Tasa de Cambio:</strong></p>
                        <h3 class="text-primary">1 USD = <?php echo number_format($tasa_cambio, 2); ?> Bs</h3>
                        <a href="views/configuracion/index.php" class="btn btn-primary btn-sm mt-2">
                            <i class="bi bi-gear me-2"></i>Configurar
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>Accesos Rápidos</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="views/productos/index.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-box me-2"></i>Productos
                        </a>
                        <a href="views/usuarios/index.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-people me-2"></i>Usuarios
                        </a>
                        <a href="views/reportes/ventas.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-file-earmark-bar-graph me-2"></i>Reportes
                        </a>
                        <a href="views/reportes/cuadre_caja.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-cash-stack me-2"></i>Cuadre de Caja
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($_SESSION['user_rol'] === 'cocina'): ?>
<script>
// Auto-actualizar órdenes de cocina cada 30 segundos
setInterval(function() {
    fetch('ajax/get_ordenes_cocina.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('ordenes-cocina').innerHTML = data.html;
                // Reproducir sonido si hay nuevas órdenes
                if (data.nuevas_ordenes > 0) {
                    reproducirNotificacion();
                }
            }
        });
}, 30000);

// Cambiar estado de orden
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-cambiar-estado') || e.target.closest('.btn-cambiar-estado')) {
        const btn = e.target.classList.contains('btn-cambiar-estado') ? e.target : e.target.closest('.btn-cambiar-estado');
        const ordenId = btn.dataset.ordenId;
        const nuevoEstado = btn.dataset.estado;
        
        if (confirm('¿Cambiar estado a "' + nuevoEstado + '"?')) {
            fetch('ajax/cambiar_estado_orden.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ orden_id: ordenId, estado: nuevoEstado })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }
});

function reproducirNotificacion() {
    // Notificación de navegador
    if ("Notification" in window && Notification.permission === "granted") {
        new Notification("Nueva Orden", {
            body: "Hay nuevas órdenes pendientes en cocina",
            icon: "assets/images/logo.png"
        });
    }
}

// Solicitar permiso para notificaciones
if ("Notification" in window && Notification.permission === "default") {
    Notification.requestPermission();
}
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>