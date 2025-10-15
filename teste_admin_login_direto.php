<?php
/**
 * Teste direto de login de administrador com POST
 */

session_start();

echo "=== TESTE DIRETO ADMIN LOGIN ===\n\n";

// Simular POST request
$_POST['email'] = 'admin@teste.com';
$_POST['senha'] = 'admin123';

echo "Simulando POST:\n";
echo "Email: {$_POST['email']}\n";
echo "Senha: {$_POST['senha']}\n\n";

// Capturar output
ob_start();

try {
    include 'admin/login.php';
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}

$output = ob_get_clean();

echo "Estado da sessão após login:\n";
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    echo "✅ LOGIN ADMIN BEM-SUCEDIDO!\n";
    echo "Admin ID: " . $_SESSION['admin_id'] . "\n";
    echo "Admin Nome: " . $_SESSION['admin_nome'] . "\n";
    echo "Admin Email: " . $_SESSION['admin_email'] . "\n";
    echo "Admin Nível: " . $_SESSION['admin_nivel'] . "\n";
} else {
    echo "❌ LOGIN ADMIN FALHOU\n";
    
    // Mostrar parte do output para debug
    if (strlen($output) > 1000) {
        echo "\nPrimeiros 500 chars do output:\n";
        echo substr($output, 0, 500) . "...\n";
        
        echo "\nÚltimos 500 chars do output:\n";
        echo "..." . substr($output, -500) . "\n";
    } else {
        echo "\nOutput completo:\n";
        echo $output . "\n";
    }
}

echo "\n=== FIM TESTE ===\n";
?>