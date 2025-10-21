<?php
// Configuración básica de la aplicación y helpers globales
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Constantes básicas (ajusta según tu entorno)
define('APP_NAME', 'App Food');
define('BASE_URL', 'http://localhost/app_food');

// Helper: marca una página que requiere autenticación
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
}

function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

function sanitize($value) {
    return trim(filter_var($value, FILTER_SANITIZE_STRING));
}

function formatCurrency($amount, $currency = 'USD') {
    if ($currency === 'USD') {
        return '$' . number_format((float)$amount, 2);
    }
    // Asumir BS
    return number_format((float)$amount, 2) . ' Bs';
}

function hasRole($role) {
    return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === $role;
}

function logAuditoria($accion, $tabla = null, $registro_id = null) {
    // Implementación mínima: escribir en archivo de log
    $msg = sprintf("[%s] %s - tabla:%s id:%s IP:%s\n",
        date('Y-m-d H:i:s'), $accion, $tabla ?? '-', $registro_id ?? '-', $_SERVER['REMOTE_ADDR'] ?? 'CLI');
    error_log($msg, 3, __DIR__ . '/../logs/auditoria.log');
}

?>
