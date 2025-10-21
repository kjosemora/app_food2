<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

$emails = ['admin@restaurant.com', 'mesero@restaurant.com', 'cocina@restaurant.com'];

foreach ($emails as $email) {
    $stmt = $db->prepare('SELECT id, nombre, email, password_hash FROM usuarios WHERE email = :email LIMIT 1');
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo "Email: {$row['email']} | ID: {$row['id']} | Hash: {$row['password_hash']}\n";
    } else {
        echo "Usuario {$email} no encontrado\n";
    }
}

?>
