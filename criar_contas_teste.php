<?php
require_once __DIR__ . '/config/config.php';

echo "=== CRIANDO CONTAS DE TESTE ===\n\n";

try {
    // 1. Criar tabela administradores se não existir
    echo "1. Criando tabela administradores...\n";
    $sql = "CREATE TABLE IF NOT EXISTS administradores (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        nivel ENUM('super_admin', 'admin', 'gestor') DEFAULT 'admin',
        ativo TINYINT(1) DEFAULT 1,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ultimo_login TIMESTAMP NULL,
        INDEX idx_email (email),
        INDEX idx_ativo (ativo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "   ✅ Tabela administradores criada/verificada\n";
    
    // 2. Verificar passwords dos barbeiros
    echo "\n2. Verificando passwords dos barbeiros...\n";
    $barbeiros_teste = [
        3 => ['email' => 'carlos.barbeiro@teste.com', 'password' => 'barbeiro123'],
        4 => ['email' => 'antonio.barbeiro@teste.com', 'password' => 'barbeiro123'],
        5 => ['email' => 'miguel.barbeiro@teste.com', 'password' => 'barbeiro123']
    ];
    
    foreach ($barbeiros_teste as $id => $data) {
        $stmt = $pdo->prepare("SELECT password_hash FROM barbeiros WHERE id = ? AND email = ?");
        $stmt->execute([$id, $data['email']]);
        $barbeiro = $stmt->fetch();
        
        if ($barbeiro && password_verify($data['password'], $barbeiro['password_hash'])) {
            echo "   ✅ Barbeiro {$data['email']} - password OK\n";
        } else {
            // Atualizar password
            $hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE barbeiros SET password_hash = ? WHERE id = ?");
            $stmt->execute([$hash, $id]);
            echo "   🔧 Barbeiro {$data['email']} - password atualizada\n";
        }
    }
    
    // 3. Criar contas de administradores
    echo "\n3. Criando contas de administradores...\n";
    $administradores = [
        ['nome' => 'Super Admin', 'email' => 'super@teste.com', 'password' => 'super123', 'nivel' => 'super_admin'],
        ['nome' => 'Admin Principal', 'email' => 'admin@teste.com', 'password' => 'admin123', 'nivel' => 'admin'],
        ['nome' => 'Gestor Loja', 'email' => 'gestor@teste.com', 'password' => 'gestor123', 'nivel' => 'gestor']
    ];
    
    foreach ($administradores as $admin) {
        // Verificar se já existe
        $stmt = $pdo->prepare("SELECT id FROM administradores WHERE email = ?");
        $stmt->execute([$admin['email']]);
        
        if ($stmt->fetch()) {
            // Atualizar password
            $hash = password_hash($admin['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE administradores SET password_hash = ?, nivel = ? WHERE email = ?");
            $stmt->execute([$hash, $admin['nivel'], $admin['email']]);
            echo "   🔧 Admin {$admin['email']} - atualizado\n";
        } else {
            // Criar novo
            $hash = password_hash($admin['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO administradores (nome, email, password_hash, nivel) VALUES (?, ?, ?, ?)");
            $stmt->execute([$admin['nome'], $admin['email'], $hash, $admin['nivel']]);
            echo "   ✅ Admin {$admin['email']} - criado\n";
        }
    }
    
    echo "\n=== RESUMO DAS CONTAS ===\n";
    echo "BARBEIROS (password: barbeiro123):\n";
    foreach ($barbeiros_teste as $data) {
        echo "  - {$data['email']}\n";
    }
    
    echo "\nADMINISTRADORES:\n";
    foreach ($administradores as $admin) {
        echo "  - {$admin['email']} (password: {$admin['password']})\n";
    }
    
    echo "\n✅ Todas as contas estão prontas para uso!\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>