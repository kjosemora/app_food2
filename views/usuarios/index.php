<?php
require_once __DIR__ . '/../../config/config.php';
requireAuth();

if (!hasRole('gerente')) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Usuario.php';

$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);

$usuarios = $usuario->getAll();

$page_title = 'Usuarios';
include __DIR__ . '/../../include/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-12 col-md-6">
            <h2><i class="bi bi-people me-2"></i>Usuarios</h2>
        </div>
        <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
            <a href="crear.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Nuevo Usuario
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th class="d-none d-md-table-cell">Email</th>
                            <th>Rol</th>
                            <th class="d-none d-lg-table-cell">Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($usuarios)): ?>
                            <?php foreach ($usuarios as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['nombre']); ?></td>
                                    <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $user['rol'] === 'gerente' ? 'danger' : 
                                                ($user['rol'] === 'mesero' ? 'primary' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($user['rol']); ?>
                                        </span>
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        <span class="badge bg-<?php echo $user['activo'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $user['activo'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="editar.php?id=<?php echo $user['id']; ?>" 
                                               class="btn btn-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <button class="btn btn-danger" 
                                                        onclick="confirmarEliminar(<?php echo $user['id']; ?>)"
                                                        title="Desactivar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-inbox fs-3 text-muted d-block mb-2"></i>
                                    <p class="text-muted">No hay usuarios registrados</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarEliminar(id) {
    if (confirm('¿Está seguro de desactivar este usuario?')) {
        window.location.href = 'eliminar.php?id=' + id;
    }
}
</script>

<?php include __DIR__ . '/../../include/footer.php'; ?>
