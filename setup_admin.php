<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db_connect.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS administradores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        nivel INT DEFAULT 1,
        ativo BOOLEAN DEFAULT TRUE,
        ultimo_login TIMESTAMP NULL,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_ativo (ativo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sql);
    echo "Table `administradores` created successfully.\n";

    $stmt = $pdo->prepare("INSERT INTO administradores (nome, email, password_hash, nivel, ativo) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        'Admin',
        'admin@mrcarlos.pt',
        password_hash('admin123', PASSWORD_BCRYPT),
        1,
        true
    ]);

    echo "Admin user created successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
