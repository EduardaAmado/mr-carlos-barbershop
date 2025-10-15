<?php
require_once __DIR__ . '/config/config.php';

echo "=== VERIFICAÇÃO DE CONTAS DE TESTE ===\n\n";

try {
    // Verificar barbeiros
    echo "BARBEIROS:\n";
    $stmt = $pdo->query("SELECT id, nome, email, ativo FROM barbeiros ORDER BY id");
    $barbeiros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($barbeiros)) {
        echo "❌ Nenhum barbeiro encontrado na base de dados\n";
    } else {
        foreach ($barbeiros as $b) {
            $status = $b['ativo'] ? '✅' : '❌';
            echo "$status ID: {$b['id']} | {$b['nome']} | {$b['email']}\n";
        }
    }
    
    echo "\nADMINISTRADORES:\n";
    $stmt = $pdo->query("SELECT id, nome, email, nivel, ativo FROM administradores ORDER BY id");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($admins)) {
        echo "❌ Nenhum administrador encontrado na base de dados\n";
    } else {
        foreach ($admins as $a) {
            $status = $a['ativo'] ? '✅' : '❌';
            echo "$status ID: {$a['id']} | {$a['nome']} | {$a['email']} | Nível: {$a['nivel']}\n";
        }
    }
    
    echo "\nCLIENTES (para comparação):\n";
    $stmt = $pdo->query("SELECT id, nome, email, ativo FROM clientes ORDER BY id LIMIT 5");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($clientes)) {
        echo "❌ Nenhum cliente encontrado na base de dados\n";
    } else {
        foreach ($clientes as $c) {
            $status = $c['ativo'] ? '✅' : '❌';
            echo "$status ID: {$c['id']} | {$c['nome']} | {$c['email']}\n";
        }
    }
    
    // Verificar estrutura das tabelas
    echo "\n=== ESTRUTURA DAS TABELAS ===\n";
    
    echo "Tabela barbeiros:\n";
    $stmt = $pdo->query("DESCRIBE barbeiros");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }
    
    echo "\nTabela administradores:\n";
    $stmt = $pdo->query("DESCRIBE administradores");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>