<?php
/**
 * Teste direto de login de cliente com POST
 */

session_start();

echo "=== TESTE DIRETO CLIENTE LOGIN ===\n\n";

// Simular POST request
$_POST['email'] = 'joao.cliente@teste.com';
$_POST['password'] = 'cliente123';
$_SERVER['REQUEST_METHOD'] = 'POST';

echo "Simulando POST:\n";
echo "Email: {$_POST['email']}\n";
echo "Password: {$_POST['password']}\n\n";

// Capturar output
ob_start();

try {
    include 'pages/login.php';
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}

$output = ob_get_clean();

echo "Estado da sessão após login:\n";
if (isset($_SESSION['user_id'])) {
    echo "✅ LOGIN CLIENTE BEM-SUCEDIDO!\n";
    echo "Cliente ID: " . $_SESSION['user_id'] . "\n";
    echo "Cliente Nome: " . $_SESSION['user_name'] . "\n";
    echo "Cliente Email: " . $_SESSION['user_email'] . "\n";
} else {
    echo "❌ LOGIN CLIENTE FALHOU\n";
    
    // Verificar se tem erro no output
    if (strpos($output, 'Fatal') !== false || strpos($output, 'Error') !== false) {
        echo "\nErros encontrados no output:\n";
        echo $output . "\n";
    }
}

echo "\n=== FIM TESTE ===\n";
?>