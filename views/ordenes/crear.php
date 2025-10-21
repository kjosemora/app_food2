<?php
require_once __DIR__ . '/../../config/config.php';
requireAuth();

if (!hasRole('mesero') && !hasRole('gerente')) {
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

$productos = $producto->getAll(true); // Solo disponibles
$tasa_cambio = $config->getTasaCambio();

// Procesar creación de orden
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_orden'])) {
    $cliente_id = !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null;
    $notas = sanitize($_POST['notas'] ?? '');
    $items = json_decode($_POST['items_json'] ?? '[]', true);
    $total_usd = floatval($_POST['total_usd'] ?? 0);
    $total_bs = floatval($_POST['total_bs'] ?? 0);
    
    if (!empty($items) && $total_usd > 0) {
        try {
            // Insertar orden
            $query = "INSERT INTO ordenes (cliente_id, mesero_id, total_usd, total_bs, notas, estado) 
                      VALUES (:cliente_id, :mesero_id, :total_usd, :total_bs, :notas, 'Pendiente')";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':cliente_id', $cliente_id);
            $stmt->bindParam(':mesero_id', $_SESSION['user_id']);
            $stmt->bindParam(':total_usd', $total_usd);
            $stmt->bindParam(':total_bs', $total_bs);
            $stmt->bindParam(':notas', $notas);
            $stmt->execute();
            
            $orden_id = $db->lastInsertId();
            
            // Insertar detalles
            $query_detalle = "INSERT INTO orden_detalles 
                             (orden_id, producto_id, cantidad, precio_unitario_usd, precio_unitario_bs, subtotal_usd, subtotal_bs) 
                             VALUES (:orden_id, :producto_id, :cantidad, :precio_usd, :precio_bs, :subtotal_usd, :subtotal_bs)";
            $stmt_detalle = $db->prepare($query_detalle);
            
            foreach ($items as $item) {
                $stmt_detalle->execute([
                    ':orden_id' => $orden_id,
                    ':producto_id' => $item['id'],
                    ':cantidad' => $item['cantidad'],
                    ':precio_usd' => $item['precio_usd'],
                    ':precio_bs' => $item['precio_bs'],
                    ':subtotal_usd' => $item['subtotal_usd'],
                    ':subtotal_bs' => $item['subtotal_bs']
                ]);
            }
            
            logAuditoria('CREAR_ORDEN', 'ordenes', $orden_id);
            $_SESSION['success_message'] = 'Orden #' . $orden_id . ' creada correctamente';
            header('Location: ver.php?id=' . $orden_id);
            exit();
        } catch (Exception $e) {
            $error = 'Error al crear la orden: ' . $e->getMessage();
        }
    } else {
        $error = 'Debe agregar al menos un producto a la orden';
    }
}

$page_title = 'Nueva Orden';
include __DIR__ . '/../../include/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-12">
            <h2><i class="bi bi-plus-circle me-2"></i>Nueva Orden</h2>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="" id="formOrden">
        <input type="hidden" name="crear_orden" value="1">
        <input type="hidden" name="items_json" id="items_json">
        <input type="hidden" name="total_usd" id="total_usd_hidden">
        <input type="hidden" name="total_bs" id="total_bs_hidden">

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-box me-2"></i>Productos Disponibles</h5>
                    </div>
                    <div class="card-body">
                        <div class="row" id="productos-grid">
                            <?php if (!empty($productos)): ?>
                                <?php foreach ($productos as $prod): ?>
                                    <div class="col-6 col-md-4 col-lg-3 mb-3">
                                        <div class="card h-100 producto-card" 
                                             data-id="<?php echo $prod['id']; ?>"
                                             data-nombre="<?php echo htmlspecialchars($prod['nombre']); ?>"
                                             data-precio-usd="<?php echo $prod['precio_usd']; ?>"
                                             data-precio-bs="<?php echo $prod['precio_bs']; ?>"
                                             style="cursor: pointer;">
                                            <div class="card-body p-2 text-center">
                                                <h6 class="card-title small mb-1"><?php echo htmlspecialchars($prod['nombre']); ?></h6>
                                                <p class="mb-0">
                                                    <strong class="text-primary"><?php echo formatCurrency($prod['precio_usd'], 'USD'); ?></strong>
                                                </p>
                                                <small class="text-muted"><?php echo formatCurrency($prod['precio_bs'], 'BS'); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <p class="text-muted text-center">No hay productos disponibles</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card mb-3 sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-cart me-2"></i>Resumen de Orden</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Cliente</label>
                            <select class="form-select" name="cliente_id">
                                <option value="">Cliente General</option>
                                <option value="2">Carlos Rodríguez</option>
                                <option value="3">Ana Martínez</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notas</label>
                            <textarea class="form-control" name="notas" rows="2" placeholder="Notas adicionales..."></textarea>
                        </div>

                        <hr>

                        <div id="carrito-items" class="mb-3" style="max-height: 300px; overflow-y: auto;">
                            <p class="text-muted text-center small">
                                <i class="bi bi-cart-x fs-3 d-block mb-2"></i>
                                Haz clic en un producto para agregarlo
                            </p>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Total USD:</span>
                            <strong id="total_usd">$0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Total BS:</span>
                            <strong id="total_bs">0.00 Bs</strong>
                        </div>

                        <button type="submit" class="btn btn-success w-100" id="btn-crear-orden" disabled>
                            <i class="bi bi-check-circle me-2"></i>Crear Orden
                        </button>
                        <a href="index.php" class="btn btn-secondary w-100 mt-2">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Carrito de compras
let carrito = [];
const TASA_CAMBIO = <?php echo $tasa_cambio; ?>;

// Agregar producto al carrito
document.querySelectorAll('.producto-card').forEach(card => {
    card.addEventListener('click', function() {
        const id = parseInt(this.dataset.id);
        const nombre = this.dataset.nombre;
        const precioUsd = parseFloat(this.dataset.precioUsd);
        const precioBs = parseFloat(this.dataset.precioBs);
        
        // Verificar si ya existe en el carrito
        const existe = carrito.find(item => item.id === id);
        
        if (existe) {
            existe.cantidad++;
        } else {
            carrito.push({
                id: id,
                nombre: nombre,
                precio_usd: precioUsd,
                precio_bs: precioBs,
                cantidad: 1
            });
        }
        
        actualizarCarrito();
        
        // Efecto visual
        this.style.transform = 'scale(0.95)';
        setTimeout(() => {
            this.style.transform = 'scale(1)';
        }, 100);
    });
});

// Actualizar vista del carrito
function actualizarCarrito() {
    const carritoDiv = document.getElementById('carrito-items');
    
    if (carrito.length === 0) {
        carritoDiv.innerHTML = `
            <p class="text-muted text-center small">
                <i class="bi bi-cart-x fs-3 d-block mb-2"></i>
                Haz clic en un producto para agregarlo
            </p>
        `;
        document.getElementById('btn-crear-orden').disabled = true;
    } else {
        let html = '';
        carrito.forEach((item, index) => {
            const subtotalUsd = item.cantidad * item.precio_usd;
            const subtotalBs = item.cantidad * item.precio_bs;
            
            html += `
                <div class="cart-item mb-2 p-2 border rounded">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <strong class="small">${item.nombre}</strong>
                        <button type="button" class="btn btn-sm btn-danger btn-remove" data-index="${index}" style="padding: 0.1rem 0.4rem;">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary btn-minus" data-index="${index}">-</button>
                            <button type="button" class="btn btn-outline-secondary" disabled>${item.cantidad}</button>
                            <button type="button" class="btn btn-outline-secondary btn-plus" data-index="${index}">+</button>
                        </div>
                        <div class="text-end">
                            <div class="small"><strong>$${subtotalUsd.toFixed(2)}</strong></div>
                            <div class="text-muted" style="font-size: 0.75rem;">${subtotalBs.toFixed(2)} Bs</div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        carritoDiv.innerHTML = html;
        document.getElementById('btn-crear-orden').disabled = false;
        
        // Agregar event listeners a los botones
        document.querySelectorAll('.btn-remove').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                carrito.splice(index, 1);
                actualizarCarrito();
            });
        });
        
        document.querySelectorAll('.btn-minus').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                if (carrito[index].cantidad > 1) {
                    carrito[index].cantidad--;
                    actualizarCarrito();
                }
            });
        });
        
        document.querySelectorAll('.btn-plus').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                carrito[index].cantidad++;
                actualizarCarrito();
            });
        });
    }
    
    calcularTotales();
}

// Calcular totales
function calcularTotales() {
    let totalUsd = 0;
    let totalBs = 0;
    
    const carritoConSubtotales = carrito.map(item => {
        const subtotalUsd = item.cantidad * item.precio_usd;
        const subtotalBs = item.cantidad * item.precio_bs;
        totalUsd += subtotalUsd;
        totalBs += subtotalBs;
        
        return {
            ...item,
            subtotal_usd: subtotalUsd,
            subtotal_bs: subtotalBs
        };
    });
    
    document.getElementById('total_usd').textContent = '$' + totalUsd.toFixed(2);
    document.getElementById('total_bs').textContent = totalBs.toFixed(2) + ' Bs';
    
    // Actualizar campos ocultos
    document.getElementById('total_usd_hidden').value = totalUsd.toFixed(2);
    document.getElementById('total_bs_hidden').value = totalBs.toFixed(2);
    document.getElementById('items_json').value = JSON.stringify(carritoConSubtotales);
}

// Validar antes de enviar
document.getElementById('formOrden').addEventListener('submit', function(e) {
    if (carrito.length === 0) {
        e.preventDefault();
        alert('Debe agregar al menos un producto a la orden');
    }
});
</script>

<?php include __DIR__ . '/../../include/footer.php'; ?>

