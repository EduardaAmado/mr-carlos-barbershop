<?php
/**
 * Teste detalhado da estrutura e autenticação
 */

require_once 'config/config.php';

echo "=== TESTE DETALHADO DE AUTENTICAÇÃO ===\n\n";

try {
    // Verificar estrutura da tabela administradores
    echo "1. ESTRUTURA DA TABELA ADMINISTRADORES:\n";
    $stmt = $pdo->query("DESCRIBE administradores");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "  - {$column['Field']} ({$column['Type']})\n";
    }
    
    echo "\n2. TESTANDO ADMIN LOGIN:\n";
    
    // Buscar admin
    $email = 'admin@teste.com';
    $senha = 'admin123';
    
    $stmt = $pdo->prepare("SELECT * FROM administradores WHERE email = ? AND ativo = 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "✅ Admin encontrado: {$admin['nome']} (ID: {$admin['id']})\n";
        echo "Email: {$admin['email']}\n";
        echo "Nível: {$admin['nivel']}\n";
        echo "Hash senha: " . substr($admin['password_hash'], 0, 20) . "...\n";
        
        // Testar verificação de senha
        if (password_verify($senha, $admin['password_hash'])) {
            echo "✅ SENHA VERIFICADA COM SUCESSO!\n";
        } else {
            echo "❌ SENHA INCORRETA!\n";
            
            // Criar novo hash para comparação
            $novo_hash = password_hash($senha, PASSWORD_DEFAULT);
            echo "Hash esperado: " . substr($novo_hash, 0, 20) . "...\n";
        }
    } else {
        echo "❌ Admin não encontrado\n";
    }
    
    echo "\n3. TESTANDO BARBEIRO LOGIN:\n";
    
    // Verificar estrutura da tabela barbeiros
    echo "Estrutura da tabela barbeiros:\n";
    $stmt = $pdo->query("DESCRIBE barbeiros");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "  - {$column['Field']} ({$column['Type']})\n";
    }
    
    // Buscar barbeiro
    $email = 'carlos.barbeiro@teste.com';
    $senha = 'barbeiro123';
    
    $stmt = $pdo->prepare("SELECT * FROM barbeiros WHERE email = ? AND ativo = 1");
    $stmt->execute([$email]);
    $barbeiro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($barbeiro) {
        echo "✅ Barbeiro encontrado: {$barbeiro['nome']} (ID: {$barbeiro['id']})\n";
        echo "Email: {$barbeiro['email']}\n";
        echo "Hash senha: " . substr($barbeiro['password_hash'], 0, 20) . "...\n";
        
        // Testar verificação de senha
        if (password_verify($senha, $barbeiro['password_hash'])) {
            echo "✅ SENHA VERIFICADA COM SUCESSO!\n";
        } else {
            echo "❌ SENHA INCORRETA!\n";
        }
    } else {
        echo "❌ Barbeiro não encontrado\n";
    }
    
    echo "\n4. TESTANDO CLIENTE LOGIN:\n";
    
    // Verificar estrutura da tabela clientes
    echo "Estrutura da tabela clientes:\n";
    $stmt = $pdo->query("DESCRIBE clientes");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "  - {$column['Field']} ({$column['Type']})\n";
    }
    
    // Buscar cliente
    $email = 'joao.cliente@teste.com';
    $senha = 'cliente123';
    
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE email = ? AND ativo = 1");
    $stmt->execute([$email]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cliente) {
        echo "✅ Cliente encontrado: {$cliente['nome']} (ID: {$cliente['id']})\n";
        echo "Email: {$cliente['email']}\n";
        echo "Hash senha: " . substr($cliente['password_hash'], 0, 20) . "...\n";
        
        // Testar verificação de senha
        if (password_verify($senha, $cliente['password_hash'])) {
            echo "✅ SENHA VERIFICADA COM SUCESSO!\n";
        } else {
            echo "❌ SENHA INCORRETA!\n";
        }
    } else {
        echo "❌ Cliente não encontrado\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}
?>