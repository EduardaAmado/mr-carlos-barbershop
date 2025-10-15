<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db_connect.php';

try {
    $email = 'admin@mrcarlos.pt';
    $stmt = $pdo->prepare("SELECT * FROM administradores WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo "Admin Data:\n";
        print_r($admin);
    } else {
        echo "No admin found with email: $email\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
