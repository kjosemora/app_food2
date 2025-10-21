<?php
require_once __DIR__ . '/../../config/config.php';
requireAuth();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Usuario.php';

$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);

$user_data = $usuario->getById($_SESSION['user_id']);

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = sanitize($_POST['nombre'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password_actual = $_POST['password_actual'] ?? '';
    $password_nueva = $_POST['password_nueva'] ?? '';
    $password_confirmar = $_POST['password_confirmar'] ?? '';
    
    if (empty($nombre) || empty($email)) {
        $mensaje = 'El nombre y email son obligatorios';
        $tipo_mensaje = 'warning';
    } else {
        $usuario->id = $_SESSION['user_id'];
        $usuario->nombre = $nombre;
        $usuario->email = $email;
        $usuario->rol = $user_data['rol'];
        $usuario->activo = $user_data['activo'];
        
        // Si se quiere cambiar la contraseña
        if (!empty($password_nueva)) {
            if ($password_nueva !== $password_confirmar) {
                $mensaje = 'Las contraseñas nuevas no coinciden';
                $tipo_mensaje = 'danger';
            } else {
                $usuario->password_hash = $password_nueva;
            }
        }
        
        if (empty($mensaje)) {
            if ($usuario->update()) {
                $_SESSION['user_nombre'] = $nombre;
                $_SESSION['user_email'] = $email;
                $mensaje = 'Perfil actualizado correctamente';
                $tipo_mensaje = 'success';
                $user_data = $usuario->getById($_SESSION['user_id']);
            } else {
                $mensaje = 'Error al actualizar el perfil';
                $tipo_mensaje = 'danger';
            }
        }
    }
}

$page_title = 'Mi Perfil';
include __DIR__ . '/../../include/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-12">
            <h2><i class="bi bi-person-circle me-2"></i>Mi Perfil</h2>
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
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Editar Información</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Nombre Completo</label>
                                <input type="text" 
                                       class="form-control" 
                                       name="nombre" 
                                       value="<?php echo htmlspecialchars($user_data['nombre']); ?>" 
                                       required>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($user_data['email']); ?>" 
                                       required>
                            </div>
                        </div>

                        <hr>
                        <h6 class="mb-3">Cambiar Contraseña (opcional)</h6>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">Contraseña Actual</label>
                                <input type="password" class="form-control" name="password_actual">
                                <small class="form-text text-muted">Dejar en blanco si no desea cambiar la contraseña</small>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" name="password_nueva">
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Confirmar Nueva Contraseña</label>
                                <input type="password" class="form-control" name="password_confirmar">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Guardar Cambios
                        </button>
                        <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 mt-3 mt-lg-0">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información de la Cuenta</h5>
                </div>
                <div class="card-body">
                    <p><strong>Rol:</strong> 
                        <span class="badge bg-<?php 
                            echo $user_data['rol'] === 'gerente' ? 'danger' : 
                                ($user_data['rol'] === 'mesero' ? 'primary' : 'warning'); 
                        ?>">
                            <?php echo ucfirst($user_data['rol']); ?>
                        </span>
                    </p>
                    <p><strong>Estado:</strong> 
                        <span class="badge bg-success">Activo</span>
                    </p>
                    <p class="mb-0"><strong>Fecha de Registro:</strong><br>
                        <?php echo date('d/m/Y', strtotime($user_data['fecha_creacion'])); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../include/footer.php'; ?>
