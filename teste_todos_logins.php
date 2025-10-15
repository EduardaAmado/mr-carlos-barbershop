<?php
/**
 * Teste completo dos sistemas de login
 */

echo "=== TESTE COMPLETO DOS SISTEMAS DE LOGIN ===\n\n";

// Contas de teste
$contas_cliente = [
    ['email' => 'joao.cliente@teste.com', 'senha' => 'cliente123'],
    ['email' => 'maria.cliente@teste.com', 'senha' => 'cliente123'],
    ['email' => 'pedro.cliente@teste.com', 'senha' => 'cliente123']
];

$contas_barbeiro = [
    ['email' => 'carlos.barbeiro@teste.com', 'senha' => 'barbeiro123'],
    ['email' => 'antonio.barbeiro@teste.com', 'senha' => 'barbeiro123'],
    ['email' => 'miguel.barbeiro@teste.com', 'senha' => 'barbeiro123']
];

$contas_admin = [
    ['email' => 'super@teste.com', 'senha' => 'admin123'],
    ['email' => 'admin@teste.com', 'senha' => 'admin123'],
    ['email' => 'gestor@teste.com', 'senha' => 'admin123']
];

function testar_login($url, $email, $senha, $tipo) {
    echo "Testando login $tipo: $email\n";
    
    $postData = http_build_query([
        'email' => $email,
        'senha' => $senha
    ]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postData,
            'timeout' => 10
        ]
    ]);
    
    $result = file_get_contents($url, false, $context);
    
    if ($result === false) {
        echo "  ❌ ERRO: Não foi possível conectar\n";
        return false;
    }
    
    // Verificar se houve redirecionamento (login bem-sucedido)
    if (strpos($result, 'Location:') !== false || strpos($result, 'dashboard') !== false) {
        echo "  ✅ LOGIN REALIZADO COM SUCESSO\n";
        return true;
    } else if (strpos($result, 'incorretos') !== false || strpos($result, 'erro') !== false) {
        echo "  ❌ FALHA: Credenciais incorretas ou erro\n";
        return false;
    } else {
        echo "  ⚠️  RESPOSTA INESPERADA (pode indicar erro no código)\n";
        return false;
    }
}

// URLs de teste
$base_url = 'http://localhost/mr-carlos-barbershop';

echo "1. TESTANDO LOGINS DE CLIENTES:\n";
echo "================================\n";
foreach ($contas_cliente as $conta) {
    testar_login("$base_url/pages/login.php", $conta['email'], $conta['senha'], 'CLIENTE');
    echo "\n";
}

echo "2. TESTANDO LOGINS DE BARBEIROS:\n";
echo "=================================\n";
foreach ($contas_barbeiro as $conta) {
    testar_login("$base_url/barbeiro/login.php", $conta['email'], $conta['senha'], 'BARBEIRO');
    echo "\n";
}

echo "3. TESTANDO LOGINS DE ADMINISTRADORES:\n";
echo "=======================================\n";
foreach ($contas_admin as $conta) {
    testar_login("$base_url/admin/login.php", $conta['email'], $conta['senha'], 'ADMIN');
    echo "\n";
}

echo "=== TESTE CONCLUÍDO ===\n";
?>