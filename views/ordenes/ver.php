<?php
require_once __DIR__ . '/../../config/config.php';
requireAuth();

$orden_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$orden_id) {
    header('Location: index.php');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Orden.php';
require_once __DIR__ . '/../../models/Configuracion.php';

$database = new Database();
$db = $database->getConnection();
$orden = new Orden($db);
$config = new Configuracion($db);

// Obtener datos de la orden
$query = "SELECT o.*, u.nombre as mesero_nombre, c.nombre as cliente_nombre 
          FROM ordenes o 
          LEFT JOIN usuarios u ON o.mesero_id = u.id 
          LEFT JOIN clientes c ON o.cliente_id = c.id 
          WHERE o.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $orden_id);
$stmt->execute();
$orden_data = $stmt->fetch();

if (!$orden_data) {
    $_SESSION['error_message'] = 'Orden no encontrada';
    header('Location: index.php');
    exit();
}

// Obtener detalles
$query_detalles = "SELECT od.*, p.nombre as producto_nombre 
                   FROM orden_detalles od 
                   LEFT JOIN productos p ON od.producto_id = p.id 
                   WHERE od.orden_id = :orden_id";
$stmt_detalles = $db->prepare($query_detalles);
$stmt_detalles->bindParam(':orden_id', $orden_id);
$stmt_detalles->execute();
$detalles = $stmt_detalles->fetchAll();

$tasa_cambio = $config->getTasaCambio();

$page_title = 'Orden #' . $orden_id;
include __DIR__ . '/../../include/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-12 col-md-6">
            <h2><i class="bi bi-receipt me-2"></i>Orden #<?php echo $orden_id; ?></h2>
        </div>
        <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
            <a href="index.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Volver
            </a>
            <button onclick="window.print()" class="btn btn-info">
                <i class="bi bi-printer me-2"></i>Imprimir
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Detalles de la Orden</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Cant.</th>
                                    <th>Producto</th>
                                    <th class="text-end">P. Unit USD</th>
                                    <th class="text-end d-none d-md-table-cell">P. Unit BS</th>
                                    <th class="text-end">Subtotal USD</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detalles as $detalle): ?>
                                    <tr>
                                        <td><?php echo $detalle['cantidad']; ?></td>
                                        <td><?php echo htmlspecialchars($detalle['producto_nombre']); ?></td>
                                        <td class="text-end"><?php echo formatCurrency($detalle['precio_unitario_usd'], 'USD'); ?></td>
                                        <td class="text-end d-none d-md-table-cell"><?php echo formatCurrency($detalle['precio_unitario_bs'], 'BS'); ?></td>
                                        <td class="text-end"><strong><?php echo formatCurrency($detalle['subtotal_usd'], 'USD'); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (!empty($orden_data['notas'])): ?>
                        <div class="alert alert-info mt-3 mb-0">
                            <strong>Notas:</strong> <?php echo htmlspecialchars($orden_data['notas']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información</h5>
                </div>
                <div class="card-body">
                    <p><strong>Estado:</strong> 
                        <span class="badge bg-<?php 
                            echo $orden_data['estado'] === 'Pagada' ? 'success' : 
                                ($orden_data['estado'] === 'Pendiente' ? 'danger' : 'warning'); 
                        ?>">
                            <?php echo $orden_data['estado']; ?>
                        </span>
                    </p>
                    <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($orden_data['fecha'])); ?></p>
                    <p><strong>Mesero:</strong> <?php echo htmlspecialchars($orden_data['mesero_nombre']); ?></p>
                    <p class="mb-0"><strong>Cliente:</strong> <?php echo htmlspecialchars($orden_data['cliente_nombre'] ?? 'General'); ?></p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-cash me-2"></i>Totales</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total USD:</span>
                        <h4 class="text-success mb-0"><?php echo formatCurrency($orden_data['total_usd'], 'USD'); ?></h4>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Total BS:</span>
                        <h4 class="text-success mb-0"><?php echo formatCurrency($orden_data['total_bs'], 'BS'); ?></h4>
                    </div>
                </div>
            </div>

            <?php if ($orden_data['estado'] !== 'Pagada'): ?>
                <button type="button" class="btn btn-primary w-100 btn-lg" data-bs-toggle="modal" data-bs-target="#modalPago">
                    <i class="bi bi-credit-card me-2"></i>Procesar Pago
                </button>
            <?php else: ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>Orden pagada correctamente
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de Pago -->
<div class="modal fade" id="modalPago" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-credit-card me-2"></i>Procesar Pago - Orden #<?php echo $orden_id; ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formPago" method="POST" action="procesar_pago.php">
                    <input type="hidden" name="orden_id" value="<?php echo $orden_id; ?>">
                    <input type="hidden" name="total_usd" value="<?php echo $orden_data['total_usd']; ?>">
                    <input type="hidden" name="total_bs" value="<?php echo $orden_data['total_bs']; ?>">
                    <input type="hidden" name="tasa_cambio" value="<?php echo $tasa_cambio; ?>">
                    <input type="hidden" name="pagos_json" id="pagos_json">

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <div class="row">
                                    <div class="col-6">
                                        <strong>Total a Pagar:</strong><br>
                                        <h4 class="mb-0"><?php echo formatCurrency($orden_data['total_usd'], 'USD'); ?></h4>
                                        <small><?php echo formatCurrency($orden_data['total_bs'], 'BS'); ?></small>
                                    </div>
                                    <div class="col-6">
                                        <strong>Pendiente:</strong><br>
                                        <h4 class="mb-0 text-danger" id="pendiente_usd">$<?php echo number_format($orden_data['total_usd'], 2); ?></h4>
                                        <small id="pendiente_bs"><?php echo number_format($orden_data['total_bs'], 2); ?> Bs</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="mb-3"><i class="bi bi-wallet2 me-2"></i>Métodos de Pago</h6>

                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Efectivo USD</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control metodo-pago" id="efectivo_usd" step="0.01" min="0" value="0">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Efectivo BS</label>
                            <div class="input-group">
                                <input type="number" class="form-control metodo-pago" id="efectivo_bs" step="0.01" min="0" value="0">
                                <span class="input-group-text">Bs</span>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Tarjeta BS</label>
                            <div class="input-group">
                                <input type="number" class="form-control metodo-pago" id="tarjeta_bs" step="0.01" min="0" value="0">
                                <span class="input-group-text">Bs</span>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Pago Móvil</label>
                            <div class="input-group">
                                <input type="number" class="form-control metodo-pago" id="pago_movil" step="0.01" min="0" value="0">
                                <span class="input-group-text">Bs</span>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div id="cambio-section" style="display: none;">
                        <div class="alert alert-success">
                            <h6><i class="bi bi-arrow-return-left me-2"></i>Cambio a Devolver</h6>
                            <div class="row">
                                <div class="col-6">
                                    <strong>Cambio USD:</strong>
                                    <h5 class="mb-0" id="cambio_usd">$0.00</h5>
                                </div>
                                <div class="col-6">
                                    <strong>Cambio BS:</strong>
                                    <h5 class="mb-0" id="cambio_bs">0.00 Bs</h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg" id="btn-confirmar-pago" disabled>
                            <i class="bi bi-check-circle me-2"></i>Confirmar Pago
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Constantes
const TOTAL_USD = <?php echo $orden_data['total_usd']; ?>;
const TOTAL_BS = <?php echo $orden_data['total_bs']; ?>;
const TASA_CAMBIO = <?php echo $tasa_cambio; ?>;

// Calcular pagos y cambio
function calcularPagos() {
    // Obtener valores de los métodos de pago
    const efectivoUsd = parseFloat(document.getElementById('efectivo_usd').value) || 0;
    const efectivoBs = parseFloat(document.getElementById('efectivo_bs').value) || 0;
    const tarjetaBs = parseFloat(document.getElementById('tarjeta_bs').value) || 0;
    const pagoMovil = parseFloat(document.getElementById('pago_movil').value) || 0;
    
    // Convertir todo a USD para facilitar cálculos
    const efectivoBsEnUsd = efectivoBs / TASA_CAMBIO;
    const tarjetaBsEnUsd = tarjetaBs / TASA_CAMBIO;
    const pagoMovilEnUsd = pagoMovil / TASA_CAMBIO;
    
    // Total pagado en USD
    const totalPagadoUsd = efectivoUsd + efectivoBsEnUsd + tarjetaBsEnUsd + pagoMovilEnUsd;
    
    // Calcular pendiente
    const pendienteUsd = TOTAL_USD - totalPagadoUsd;
    const pendienteBs = pendienteUsd * TASA_CAMBIO;
    
    // Actualizar pendiente
    document.getElementById('pendiente_usd').textContent = '$' + pendienteUsd.toFixed(2);
    document.getElementById('pendiente_bs').textContent = pendienteBs.toFixed(2) + ' Bs';
    
    // Cambiar color según pendiente
    if (pendienteUsd > 0.01) {
        document.getElementById('pendiente_usd').className = 'mb-0 text-danger';
        document.getElementById('btn-confirmar-pago').disabled = true;
    } else if (pendienteUsd < -0.01) {
        document.getElementById('pendiente_usd').className = 'mb-0 text-success';
        document.getElementById('btn-confirmar-pago').disabled = false;
        
        // Calcular cambio
        const cambioTotalUsd = Math.abs(pendienteUsd);
        
        // Priorizar devolver cambio en la misma moneda que se recibió
        let cambioUsd = 0;
        let cambioBs = 0;
        
        // Si pagó con efectivo USD, devolver cambio en USD
        if (efectivoUsd > 0) {
            cambioUsd = Math.min(cambioTotalUsd, efectivoUsd - (efectivoUsd > TOTAL_USD ? TOTAL_USD : efectivoUsd));
            if (cambioUsd < 0) cambioUsd = 0;
        }
        
        // El resto del cambio en BS
        const cambioRestanteUsd = cambioTotalUsd - cambioUsd;
        cambioBs = cambioRestanteUsd * TASA_CAMBIO;
        
        // Si solo pagó en BS, todo el cambio en BS
        if (efectivoUsd === 0 && (efectivoBs > 0 || tarjetaBs > 0 || pagoMovil > 0)) {
            cambioUsd = 0;
            cambioBs = cambioTotalUsd * TASA_CAMBIO;
        }
        
        // Mostrar cambio
        document.getElementById('cambio_usd').textContent = '$' + cambioUsd.toFixed(2);
        document.getElementById('cambio_bs').textContent = cambioBs.toFixed(2) + ' Bs';
        document.getElementById('cambio-section').style.display = 'block';
    } else {
        // Pago exacto
        document.getElementById('pendiente_usd').className = 'mb-0 text-success';
        document.getElementById('btn-confirmar-pago').disabled = false;
        document.getElementById('cambio-section').style.display = 'none';
    }
    
    // Preparar JSON de pagos
    const pagos = {
        efectivo_usd: efectivoUsd,
        efectivo_bs: efectivoBs,
        tarjeta_bs: tarjetaBs,
        pago_movil: pagoMovil,
        total_pagado_usd: totalPagadoUsd,
        cambio_usd: parseFloat(document.getElementById('cambio_usd').textContent.replace('$', '')) || 0,
        cambio_bs: parseFloat(document.getElementById('cambio_bs').textContent.replace(' Bs', '')) || 0
    };
    
    document.getElementById('pagos_json').value = JSON.stringify(pagos);
}

// Event listeners para los inputs
document.querySelectorAll('.metodo-pago').forEach(input => {
    input.addEventListener('input', calcularPagos);
});

// Botón de pago rápido (total exacto)
document.getElementById('efectivo_usd').addEventListener('dblclick', function() {
    this.value = TOTAL_USD.toFixed(2);
    calcularPagos();
});

document.getElementById('efectivo_bs').addEventListener('dblclick', function() {
    this.value = TOTAL_BS.toFixed(2);
    calcularPagos();
});

// Validar antes de enviar
document.getElementById('formPago').addEventListener('submit', function(e) {
    const pendienteUsd = parseFloat(document.getElementById('pendiente_usd').textContent.replace('$', ''));
    
    if (pendienteUsd > 0.01) {
        e.preventDefault();
        alert('El monto pagado es insuficiente. Pendiente: $' + pendienteUsd.toFixed(2));
    }
});

// Inicializar
calcularPagos();
</script>

<?php include __DIR__ . '/../../include/footer.php'; ?>

