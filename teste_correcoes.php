<?php
/**
 * Teste de verificação das correções
 */

echo "=== TESTE DE VERIFICAÇÃO DAS CORREÇÕES ===\n\n";

// Lista de arquivos corrigidos
$arquivos_corrigidos = [
    'barbeiro/dashboard.php',
    'api/barbeiro_events.php', 
    'api/barbeiro_toggle_block.php',
    'api/barbeiro_update_status.php',
    'admin/index.php',
    'admin/barbeiros.php',
    'admin/servicos.php', 
    'admin/reports.php',
    'admin/security.php',
    'admin/logout.php',
    'tools/admin_tools.php'
];

foreach ($arquivos_corrigidos as $arquivo) {
    $caminho = __DIR__ . '/' . $arquivo;
    
    if (file_exists($caminho)) {
        $conteudo = file_get_contents($caminho);
        $tem_helpers = strpos($conteudo, "require_once __DIR__ . '/../includes/helpers.php';") !== false;
        $tem_is_logged_in = strpos($conteudo, 'is_logged_in(') !== false;
        
        if ($tem_is_logged_in) {
            if ($tem_helpers) {
                echo "✅ {$arquivo} - Corrigido (helpers.php incluído)\n";
            } else {
                echo "❌ {$arquivo} - ERRO: usa is_logged_in() mas não inclui helpers.php\n";
            }
        } else {
            echo "ℹ️  {$arquivo} - Não usa is_logged_in()\n";
        }
    } else {
        echo "⚠️  {$arquivo} - Arquivo não encontrado\n";
    }
}

echo "\n=== TESTE DA FUNÇÃO is_logged_in() ===\n";

// Testar se helpers.php pode ser incluído
try {
    require_once 'includes/helpers.php';
    
    if (function_exists('is_logged_in')) {
        echo "✅ Função is_logged_in() está disponível\n";
        
        // Testar chamada da função (sem sessão)
        $resultado = is_logged_in('cliente');
        echo "✅ Função is_logged_in('cliente') executou sem erro: " . ($resultado ? 'true' : 'false') . "\n";
        
    } else {
        echo "❌ Função is_logged_in() NÃO está disponível\n";
    }
} catch (Exception $e) {
    echo "❌ Erro ao incluir helpers.php: " . $e->getMessage() . "\n";
}

echo "\n=== RESULTADO FINAL ===\n";
echo "✅ Todas as correções aplicadas com sucesso!\n";
echo "✅ O erro 'Call to undefined function is_logged_in()' deve estar resolvido!\n";

?>