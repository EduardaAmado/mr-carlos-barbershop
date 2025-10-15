<?php
/**
 * Teste direto do login admin
 */

// Simular POST request
$_POST['email'] = 'admin1@barbershop.com';
$_POST['senha'] = 'admin123';

// Inicializar sessão
session_start();

// Incluir o arquivo de login admin
ob_start();
include 'admin/login.php';
$output = ob_get_clean();

echo "=== TESTE DIRETO LOGIN ADMIN ===\n";
echo "Email: admin1@barbershop.com\n";
echo "Senha: admin123\n\n";

echo "Saída do script:\n";
echo $output . "\n\n";

if (isset($_SESSION['admin_logged_in'])) {
    echo "✅ SESSÃO ADMIN CRIADA COM SUCESSO!\n";
    echo "Admin ID: " . $_SESSION['admin_id'] . "\n";
    echo "Admin Nome: " . $_SESSION['admin_nome'] . "\n";
    echo "Admin Email: " . $_SESSION['admin_email'] . "\n";
} else {
    echo "❌ SESSÃO ADMIN NÃO FOI CRIADA\n";
}

?>