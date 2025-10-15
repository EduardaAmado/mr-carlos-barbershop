<?php
echo "=== TESTE DO BARBEIRO DASHBOARD ===\n";

// Simular sessão de barbeiro
$_SESSION['barbeiro'] = ['id' => 1, 'nome' => 'João Silva'];

echo "Sessão barbeiro simulada\n";
echo "Carregando barbeiro/dashboard.php...\n\n";

// Capturar output e erros
ob_start();
try {
    include 'barbeiro/dashboard.php';
    $output = ob_get_contents();
    echo "✅ Arquivo carregado sem erros fatais\n";
    echo "Tamanho do output: " . strlen($output) . " bytes\n";
    
    if (strpos($output, '<title>') !== false) {
        echo "✅ HTML gerado corretamente\n";
    }
    
    if (strpos($output, 'Dashboard') !== false || strpos($output, 'Barbeiro') !== false) {
        echo "✅ Título da página encontrado\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} finally {
    ob_end_clean();
}

echo "\n=== TESTE CONCLUÍDO ===\n";
?>