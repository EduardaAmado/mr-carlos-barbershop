<?php
/**
 * Teste direto de login de barbeiro com POST
 */

session_start();

echo "=== TESTE DIRETO BARBEIRO LOGIN ===\n\n";

// Simular POST request
$_POST['email'] = 'carlos.barbeiro@teste.com';
$_POST['password'] = 'barbeiro123';
$_SERVER['REQUEST_METHOD'] = 'POST';

echo "Simulando POST:\n";
echo "Email: {$_POST['email']}\n";
echo "Password: {$_POST['password']}\n\n";

// Capturar output
ob_start();

try {
    include 'barbeiro/login.php';
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}

$output = ob_get_clean();

echo "Estado da sessão após login:\n";
if (isset($_SESSION['barbeiro_logged_in']) && $_SESSION['barbeiro_logged_in']) {
    echo "✅ LOGIN BARBEIRO BEM-SUCEDIDO!\n";
    echo "Barbeiro ID: " . $_SESSION['barbeiro_id'] . "\n";
    echo "Barbeiro Nome: " . $_SESSION['barbeiro_nome'] . "\n";
    echo "Barbeiro Email: " . $_SESSION['barbeiro_email'] . "\n";
} else {
    echo "❌ LOGIN BARBEIRO FALHOU\n";
    
    // Verificar se tem erro no output
    if (strpos($output, 'Fatal') !== false || strpos($output, 'Error') !== false) {
        echo "\nErros encontrados no output:\n";
        echo $output . "\n";
    }
}

echo "\n=== FIM TESTE ===\n";
?>