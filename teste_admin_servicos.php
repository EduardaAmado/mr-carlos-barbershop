<?php
/**
 * Teste do admin/servicos.php
 */

echo "=== TESTE DO ADMIN SERVICOS ===\n";

// Simular sessão de admin
session_start();
$_SESSION['admin'] = [
    'id' => 2,
    'nome' => 'Admin Principal', 
    'email' => 'admin@teste.com',
    'nivel' => 'admin',
    'logged_in' => true
];

echo "Sessão admin simulada\n";
echo "Carregando admin/servicos.php...\n\n";

// Capturar output e erros
ob_start();
try {
    include 'admin/servicos.php';
    $output = ob_get_clean();
    echo "✅ Arquivo carregado sem erros fatais\n";
    echo "Tamanho do output: " . strlen($output) . " bytes\n";
    
    if (strpos($output, '<!DOCTYPE') !== false) {
        echo "✅ HTML gerado corretamente\n";
    } else {
        echo "⚠️ Output não parece ser HTML completo\n";
    }
    
    if (strpos($output, 'Gestão de Serviços') !== false) {
        echo "✅ Título da página encontrado\n";
    }
    
} catch (Exception $e) {
    $error_output = ob_get_clean();
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    if ($error_output) {
        echo "Output capturado: " . substr($error_output, 0, 500) . "...\n";
    }
}

echo "\n=== TESTE CONCLUÍDO ===\n";
?>