<?php
echo "=== TESTE DE CSS ===\n";

// Verificar se as constantes estão definidas
require_once 'config/config.php';

echo "BASE_URL: " . BASE_URL . "\n";

// Testar a função get_base_url
$css_url = get_base_url('assets/css/style.css');
echo "URL do CSS: " . $css_url . "\n";

// Verificar se o arquivo existe
$css_path = __DIR__ . '/assets/css/style.css';
echo "Caminho físico: " . $css_path . "\n";
echo "Arquivo existe: " . (file_exists($css_path) ? "SIM" : "NÃO") . "\n";

if (file_exists($css_path)) {
    echo "Tamanho do arquivo: " . filesize($css_path) . " bytes\n";
    echo "Permissões: " . substr(sprintf('%o', fileperms($css_path)), -4) . "\n";
}

// Testar se o CSS pode ser acessado via HTTP
$context = stream_context_create([
    'http' => [
        'timeout' => 5
    ]
]);

echo "\nTestando acesso HTTP...\n";
$http_response = @file_get_contents($css_url, false, $context);
if ($http_response !== false) {
    echo "✅ CSS acessível via HTTP\n";
    echo "Tamanho da resposta: " . strlen($http_response) . " bytes\n";
} else {
    echo "❌ Erro ao acessar CSS via HTTP\n";
    echo "Possível erro de servidor ou configuração\n";
}

echo "\n=== TESTE CONCLUÍDO ===\n";
?>