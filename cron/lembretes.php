<?php
/**
 * CRON - Processamento automático de lembretes
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Enviar lembretes de agendamento automaticamente
 * 
 * EXECUÇÃO: Configure no crontab para executar diariamente:
 * 0 18 * * * /usr/bin/php /caminho/para/projeto/cron/lembretes.php
 */

// Evitar execução via browser por segurança
if (php_sapi_name() !== 'cli' && !defined('CRON_ALLOWED')) {
    http_response_code(403);
    die('❌ Acesso negado. Este script deve ser executado via linha de comando ou CRON apenas.');
}

// Configurar timezone
date_default_timezone_set('America/Sao_Paulo');

// Incluir sistema de email
require_once __DIR__ . '/../includes/email.php';

echo "🚀 Iniciando processamento de lembretes - " . date('Y-m-d H:i:s') . "\n";
echo "==========================================\n";

try {
    // Processar lembretes automáticos
    $emails_enviados = processar_lembretes_automaticos();
    
    if ($emails_enviados !== false) {
        echo "✅ Processamento concluído com sucesso!\n";
        echo "📧 Total de lembretes enviados: {$emails_enviados}\n";
        
        if ($emails_enviados > 0) {
            echo "📊 Detalhes:\n";
            echo "   - Data dos agendamentos: " . date('d/m/Y', strtotime('+1 day')) . "\n";
            echo "   - Horário do processamento: " . date('H:i:s') . "\n";
        } else {
            echo "ℹ️  Nenhum agendamento encontrado para amanhã.\n";
        }
        
    } else {
        echo "❌ Erro durante o processamento de lembretes.\n";
        echo "🔍 Verifique os logs para mais detalhes.\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "💥 ERRO CRÍTICO: " . $e->getMessage() . "\n";
    echo "📝 Trace: " . $e->getTraceAsString() . "\n";
    
    // Log do erro
    error_log("CRON LEMBRETES - Erro crítico: " . $e->getMessage());
    exit(1);
}

echo "==========================================\n";
echo "🏁 Processamento finalizado - " . date('Y-m-d H:i:s') . "\n";

// Opcional: Limpar agendamentos antigos (mais de 6 meses)
function limpar_agendamentos_antigos() {
    try {
        $data_limite = date('Y-m-d', strtotime('-6 months'));
        
        $result = execute_prepared_query(
            "DELETE FROM agendamentos WHERE data_hora < ? AND status IN ('cancelado', 'falta')",
            [$data_limite],
            's'
        );
        
        if ($result) {
            echo "🧹 Limpeza automática: agendamentos antigos removidos\n";
            return true;
        }
        
    } catch (Exception $e) {
        error_log("Erro na limpeza automática: " . $e->getMessage());
    }
    
    return false;
}

// Executar limpeza apenas às sextas-feiras
if (date('w') == 5) { // 5 = sexta-feira
    echo "\n🧹 Executando limpeza semanal...\n";
    limpar_agendamentos_antigos();
}
?>