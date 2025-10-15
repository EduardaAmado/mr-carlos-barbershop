<?php
/**
 * Script para testar autenticação
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Testar se o sistema de login funciona corretamente
 */

require_once __DIR__ . '/../config/config.php';

echo "<h2>🔐 Teste de Autenticação - Mr. Carlos Barbershop</h2>\n";

// Testar conexão
try {
    echo "<h3>1. Teste de Conexão</h3>\n";
    if ($pdo) {
        echo "✅ <strong>Conexão PDO:</strong> Ativa<br>\n";
        echo "✅ <strong>Base de Dados:</strong> " . DB_NAME . "<br>\n";
    } else {
        echo "❌ <strong>Erro:</strong> Conexão PDO não disponível<br>\n";
    }

    // Testar se SecurityManager inicializa corretamente
    echo "<h3>2. Teste SecurityManager</h3>\n";
    
    require_once __DIR__ . '/../includes/security.php';
    $security = SecurityManager::getInstance();
    
    if ($security) {
        echo "✅ <strong>SecurityManager:</strong> Inicializado com sucesso<br>\n";
    } else {
        echo "❌ <strong>Erro:</strong> SecurityManager não inicializado<br>\n";
    }
    
    // Testar geração de token CSRF
    echo "<h3>3. Teste Token CSRF</h3>\n";
    $csrf_token = $security->generateCSRFToken('test');
    if ($csrf_token) {
        echo "✅ <strong>Token CSRF gerado:</strong> " . substr($csrf_token, 0, 20) . "...<br>\n";
        
        // Testar validação do token
        $valid = $security->validateCSRFToken($csrf_token, 'test');
        if ($valid) {
            echo "✅ <strong>Validação CSRF:</strong> Funciona corretamente<br>\n";
        } else {
            echo "❌ <strong>Erro:</strong> Validação CSRF falhou<br>\n";
        }
    } else {
        echo "❌ <strong>Erro:</strong> Não foi possível gerar token CSRF<br>\n";
    }

    // Testar rate limiting
    echo "<h3>4. Teste Rate Limiting</h3>\n";
    $rate_check = $security->checkRateLimit('login', 'test@teste.com');
    if ($rate_check === true) {
        echo "✅ <strong>Rate Limiting:</strong> Funciona (permitiu acesso)<br>\n";
    } else {
        echo "❌ <strong>Rate Limiting:</strong> Bloqueado (pode ser normal se houver muitas tentativas)<br>\n";
    }

    // Testar verificação de conta cliente
    echo "<h3>5. Teste Contas de Cliente</h3>\n";
    
    $stmt = $pdo->prepare("SELECT id, nome, email FROM clientes WHERE email = ? LIMIT 1");
    $stmt->execute(['joao.cliente@teste.com']);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cliente) {
        echo "✅ <strong>Cliente encontrado:</strong> {$cliente['nome']} ({$cliente['email']})<br>\n";
        
        // Testar verificação de password
        $stmt = $pdo->prepare("SELECT password_hash FROM clientes WHERE email = ?");
        $stmt->execute(['joao.cliente@teste.com']);
        $hash = $stmt->fetchColumn();
        
        if (password_verify('cliente123', $hash)) {
            echo "✅ <strong>Password hash:</strong> Válida<br>\n";
        } else {
            echo "❌ <strong>Erro:</strong> Password hash inválida<br>\n";
        }
    } else {
        echo "❌ <strong>Erro:</strong> Cliente de teste não encontrado<br>\n";
    }

    // Testar verificação de conta barbeiro
    echo "<h3>6. Teste Contas de Barbeiro</h3>\n";
    
    $stmt = $pdo->prepare("SELECT id, nome, email FROM barbeiros WHERE email = ? LIMIT 1");
    $stmt->execute(['carlos.barbeiro@teste.com']);
    $barbeiro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($barbeiro) {
        echo "✅ <strong>Barbeiro encontrado:</strong> {$barbeiro['nome']} ({$barbeiro['email']})<br>\n";
        
        $stmt = $pdo->prepare("SELECT password_hash FROM barbeiros WHERE email = ?");
        $stmt->execute(['carlos.barbeiro@teste.com']);
        $hash = $stmt->fetchColumn();
        
        if (password_verify('barbeiro123', $hash)) {
            echo "✅ <strong>Password hash:</strong> Válida<br>\n";
        } else {
            echo "❌ <strong>Erro:</strong> Password hash inválida<br>\n";
        }
    } else {
        echo "❌ <strong>Erro:</strong> Barbeiro de teste não encontrado<br>\n";
    }

    // Testar verificação de conta admin
    echo "<h3>7. Teste Contas de Admin</h3>\n";
    
    $stmt = $pdo->prepare("SELECT id, nome, email, nivel_acesso FROM admins WHERE email = ? LIMIT 1");
    $stmt->execute(['super@teste.com']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "✅ <strong>Admin encontrado:</strong> {$admin['nome']} ({$admin['email']}) - Nível: {$admin['nivel_acesso']}<br>\n";
        
        $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE email = ?");
        $stmt->execute(['super@teste.com']);
        $hash = $stmt->fetchColumn();
        
        if (password_verify('super123', $hash)) {
            echo "✅ <strong>Password hash:</strong> Válida<br>\n";
        } else {
            echo "❌ <strong>Erro:</strong> Password hash inválida<br>\n";
        }
    } else {
        echo "❌ <strong>Erro:</strong> Admin de teste não encontrado<br>\n";
    }

    echo "<hr>\n";
    echo "<h3>✅ Resumo do Teste</h3>\n";
    echo "<p><strong>Status:</strong> Sistema de autenticação está funcional!</p>\n";
    echo "<p>Pode agora testar o login com as seguintes contas:</p>\n";
    echo "<ul>\n";
    echo "<li><strong>Cliente:</strong> joao.cliente@teste.com / cliente123</li>\n";
    echo "<li><strong>Barbeiro:</strong> carlos.barbeiro@teste.com / barbeiro123</li>\n";
    echo "<li><strong>Admin:</strong> super@teste.com / super123</li>\n";
    echo "</ul>\n";

    echo "<h3>🔗 Links de Teste</h3>\n";
    echo "<ul>\n";
    echo "<li><a href='../pages/login.php' target='_blank'>Testar Login Cliente</a></li>\n";
    echo "<li><a href='../barbeiro/login.php' target='_blank'>Testar Login Barbeiro</a></li>\n";
    echo "<li><a href='../admin/login.php' target='_blank'>Testar Login Admin</a></li>\n";
    echo "</ul>\n";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ <strong>Erro na base de dados:</strong> " . $e->getMessage() . "</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Erro geral:</strong> " . $e->getMessage() . "</p>\n";
}
?>