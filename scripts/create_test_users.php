<?php
/**
 * Script para crear usuarios de prueba. Ejecutar desde la terminal:
 * php scripts/create_test_users.php
 *
 * IMPORTANTE: ejecuta solo en desarrollo.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/usuario.php';

$database = new Database();
$db = $database->getConnection();

$u = new Usuario($db);

$users = [
    ['nombre' => 'Admin', 'email' => 'admin@restaurant.com', 'password' => 'admin123', 'rol' => 'gerente'],
    ['nombre' => 'Mesero', 'email' => 'mesero@restaurant.com', 'password' => 'admin123', 'rol' => 'mesero'],
    ['nombre' => 'Cocina', 'email' => 'cocina@restaurant.com', 'password' => 'admin123', 'rol' => 'cocina'],
];

foreach ($users as $user) {
    if ($u->emailExists($user['email'])) {
        echo "Usuario {$user['email']} ya existe\n";
        continue;
    }

    $u->nombre = $user['nombre'];
    $u->email = $user['email'];
    $u->password_hash = $user['password'];
    $u->rol = $user['rol'];
    $u->activo = 1;

    $id = $u->create();
    if ($id) {
        echo "Creado usuario {$user['email']} (id={$id})\n";
    } else {
        echo "Error creando {$user['email']}\n";
    }
}

?>
