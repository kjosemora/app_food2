<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/Usuario.php';

// Si ya está autenticado, redirigir
if (isAuthenticated()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Por favor ingrese email y contraseña';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        $usuario = new Usuario($db);

        if ($usuario->login($email, $password)) {
            $_SESSION['user_id'] = $usuario->id;
            $_SESSION['user_nombre'] = $usuario->nombre;
            $_SESSION['user_email'] = $usuario->email;
            $_SESSION['user_rol'] = $usuario->rol;
            $_SESSION['last_activity'] = time();
            
            logAuditoria('LOGIN', 'usuarios', $usuario->id);
            
            header('Location: index.php');
            exit();
        } else {
            $error = 'Credenciales incorrectas';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-body {
            padding: 30px;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            width: 100%;
            padding: 12px;
            font-weight: 600;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-shop fs-1"></i>
            <h2 class="mt-2 mb-0"><?php echo APP_NAME; ?></h2>
            <p class="mb-0">Sistema de Punto de Venta</p>
        </div>
        <div class="login-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['timeout'])): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-clock me-2"></i>Su sesión ha expirado. Por favor inicie sesión nuevamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-envelope me-2"></i>Email
                    </label>
                    <input type="email" class="form-control" name="email" required autofocus
                           value="<?php echo htmlspecialchars($email ?? ''); ?>">
                </div>

                <div class="mb-4">
                    <label class="form-label">
                        <i class="bi bi-lock me-2"></i>Contraseña
                    </label>
                    <input type="password" class="form-control" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                </button>
            </form>

            <div class="mt-4 text-center text-muted small">
                <p class="mb-1"><strong>Usuarios de prueba:</strong></p>
                <p class="mb-0">Gerente: admin@restaurant.com</p>
                <p class="mb-0">Mesero: mesero@restaurant.com</p>
                <p class="mb-0">Cocina: cocina@restaurant.com</p>
                <p class="mb-0 mt-2"><em>Contraseña para todos: admin123</em></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>