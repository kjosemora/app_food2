<?php
/**
 * Resetea las contraseñas de los usuarios de prueba a 'admin123'
 * Ejecutar: php scripts/reset_test_passwords.php
 */
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

$emails = ['admin@restaurant.com', 'mesero@restaurant.com', 'cocina@restaurant.com'];
$new = password_hash('admin123', PASSWORD_DEFAULT);

foreach ($emails as $email) {
    $stmt = $db->prepare('UPDATE usuarios SET password_hash = :hash WHERE email = :email');
    $stmt->bindParam(':hash', $new);
    $stmt->bindParam(':email', $email);
    $ok = $stmt->execute();
    echo ($ok ? "Actualizado: $email\n" : "Error actualizando: $email\n");
}

?>
