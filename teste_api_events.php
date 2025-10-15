<?php
echo "=== TESTE DA API BARBEIRO EVENTS ===\n";

// Simular sessão de barbeiro
$_SESSION['barbeiro'] = ['id' => 1, 'nome' => 'João Silva'];

// Simular dados POST
$_POST = json_encode([
    'barbeiro_id' => 1,
    'start' => '2025-10-01',
    'end' => '2025-10-31'
]);

// Simular cabeçalho AJAX
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

echo "Sessão barbeiro simulada\n";
echo "Chamando API barbeiro_events.php...\n\n";

// Capturar output
ob_start();
try {
    include 'api/barbeiro_events.php';
    $output = ob_get_contents();
    echo "✅ API executada sem erros fatais\n";
    echo "Output: " . $output . "\n";
    
    // Verificar se é JSON válido
    $json = json_decode($output, true);
    if ($json !== null) {
        echo "✅ JSON válido retornado\n";
        echo "Success: " . ($json['success'] ? 'true' : 'false') . "\n";
        if (isset($json['events'])) {
            echo "Eventos: " . count($json['events']) . "\n";
        }
    } else {
        echo "❌ JSON inválido\n";
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