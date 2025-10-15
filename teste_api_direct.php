<?php
echo "=== TESTE DIRETO DA API ===\n";

// Testar diretamente via URL com POST simulado
$url = 'http://localhost/mr-carlos-barbershop/api/barbeiro_events.php';
$data = json_encode([
    'barbeiro_id' => 1,
    'start' => '2025-10-01',
    'end' => '2025-10-31'
]);

$options = [
    'http' => [
        'header' => [
            'Content-Type: application/json',
            'X-Requested-With: XMLHttpRequest'
        ],
        'method' => 'POST',
        'content' => $data
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    echo "❌ Erro ao fazer requisição\n";
} else {
    echo "✅ Resposta recebida\n";
    echo "Response: $result\n";
    
    $json = json_decode($result, true);
    if ($json !== null) {
        echo "✅ JSON válido\n";
        if (isset($json['success'])) {
            echo "Success: " . ($json['success'] ? 'true' : 'false') . "\n";
        }
        if (isset($json['message'])) {
            echo "Message: " . $json['message'] . "\n";
        }
    } else {
        echo "❌ JSON inválido\n";
    }
}

echo "\n=== TESTE CONCLUÍDO ===\n";
?>