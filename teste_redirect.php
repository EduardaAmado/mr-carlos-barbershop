<?php
session_start();

// Simular um login bem-sucedido
$_SESSION['user'] = [
    'id' => 1,
    'nome' => 'João Silva',
    'email' => 'joao.cliente@teste.com',
    'type' => 'cliente'
];

echo "Sessão criada. Testando redirecionamento...\n";

require_once 'includes/helpers.php';

// Testar is_logged_in
if (is_logged_in('cliente')) {
    echo "✅ is_logged_in('cliente') retorna true\n";
} else {
    echo "❌ is_logged_in('cliente') retorna false\n";
}

// Testar safe_redirect (comentado para ver o comportamento)
echo "Tentando redirecionamento...\n";
// safe_redirect(get_base_url('pages/perfil.php'));

echo "Se chegou aqui, o redirecionamento não funcionou\n";
?>