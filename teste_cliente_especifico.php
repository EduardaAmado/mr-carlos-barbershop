<?php
/**
 * Teste específico do login de cliente
 */

session_start();

echo "=== TESTE LOGIN CLIENTE ===\n";

// Simular POST request
$_POST['email'] = 'joao.cliente@teste.com';
$_POST['password'] = 'cliente123';
$_SERVER['REQUEST_METHOD'] = 'POST';

echo "POST dados:\n";
echo "Email: {$_POST['email']}\n";
echo "Password: {$_POST['password']}\n\n";

// Testar login sem incluir o arquivo completo
require_once 'config/config.php';
require_once 'includes/helpers.php';
require_once 'includes/security.php';

global $pdo;

try {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    echo "Dados limpos:\n";
    echo "Email: $email\n";
    echo "Password: $password\n\n";
    
    // Buscar cliente
    $stmt = $pdo->prepare("SELECT id, nome, email, password_hash, ativo FROM clientes WHERE email = ?");
    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        echo "✅ Cliente encontrado: {$row['nome']} (ID: {$row['id']})\n";
        echo "Ativo: " . ($row['ativo'] ? 'Sim' : 'Não') . "\n";
        
        if (!$row['ativo']) {
            echo "❌ Conta desativada\n";
        } elseif (password_verify($password, $row['password_hash'])) {
            echo "✅ Senha verificada com sucesso\n";
            
            // Criar sessão
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => $row['id'],
                'nome' => $row['nome'],
                'email' => $row['email'],
                'type' => 'cliente'
            ];
            
            echo "✅ Sessão criada:\n";
            echo "  - ID: " . $_SESSION['user']['id'] . "\n";
            echo "  - Nome: " . $_SESSION['user']['nome'] . "\n";
            echo "  - Email: " . $_SESSION['user']['email'] . "\n";
            echo "  - Tipo: " . $_SESSION['user']['type'] . "\n";
            
            // Atualizar último login
            $stmt = $pdo->prepare("UPDATE clientes SET ultimo_login = NOW() WHERE id = ?");
            $stmt->execute([$row['id']]);
            
            echo "✅ Último login atualizado\n";
            
            // Testar record_attempt
            if (function_exists('record_attempt')) {
                record_attempt('login', $email, true);
                echo "✅ Tentativa de login registrada\n";
            } else {
                echo "⚠️ Função record_attempt não encontrada\n";
            }
            
            echo "\n🎉 LOGIN DE CLIENTE BEM-SUCEDIDO!\n";
            
        } else {
            echo "❌ Senha incorreta\n";
        }
    } else {
        echo "❌ Cliente não encontrado\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM TESTE ===\n";
?>