<?php
/**
 * Teste do Sistema de Email - Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Testar funcionalidades do sistema de email
 * 
 * INSTRUÇÕES:
 * 1. Configure as credenciais SMTP no config/config.php
 * 2. Instale o PHPMailer via Composer ou manualmente
 * 3. Execute este arquivo via browser ou linha de comando
 * 4. Verifique sua caixa de email
 */

// Permitir execução em debug
define('CRON_ALLOWED', true);

// Headers para output no browser
if (php_sapi_name() !== 'cli') {
    echo "<html><head><meta charset='UTF-8'><title>Teste de Email</title>";
    echo "<style>body{font-family:Arial,sans-serif;margin:40px;} .success{color:green;} .error{color:red;} .info{color:blue;} pre{background:#f5f5f5;padding:15px;border-radius:5px;}</style></head><body>";
    echo "<h1>🧪 Teste do Sistema de Email</h1>";
}

echo "📧 Iniciando testes do sistema de email...\n\n";

try {
    // Verificar se PHPMailer está disponível
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        echo "✅ PHPMailer encontrado via Composer\n";
        require_once __DIR__ . '/vendor/autoload.php';
    } elseif (file_exists(__DIR__ . '/phpmailer/src/PHPMailer.php')) {
        echo "✅ PHPMailer encontrado via instalação manual\n";
        require_once __DIR__ . '/phpmailer/src/Exception.php';
        require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/phpmailer/src/SMTP.php';
    } else {
        throw new Exception("❌ PHPMailer não encontrado! Instale via Composer ou baixe manualmente.");
    }
    
    // Incluir sistema de email
    require_once __DIR__ . '/includes/email.php';
    echo "✅ Sistema de email carregado\n";
    
    // Verificar configurações SMTP
    if (!defined('SMTP_HOST') || !defined('SMTP_USERNAME') || !defined('SMTP_PASSWORD')) {
        throw new Exception("❌ Configurações SMTP não encontradas no config.php!");
    }
    
    echo "✅ Configurações SMTP encontradas\n";
    echo "   Host: " . SMTP_HOST . "\n";
    echo "   Usuário: " . SMTP_USERNAME . "\n";
    echo "   Porta: " . SMTP_PORT . "\n\n";
    
    // ALTERE ESTE EMAIL PARA SEUS TESTES!
    $email_teste = 'seu-email-de-teste@gmail.com'; // ⚠️ IMPORTANTE: Mude aqui!
    
    if ($email_teste === 'seu-email-de-teste@gmail.com') {
        echo "⚠️  ATENÇÃO: Altere a variável \$email_teste na linha 32 antes de executar!\n\n";
    }
    
    // Dados de teste
    $dados_teste = [
        'cliente_nome' => 'João Silva (TESTE)',
        'cliente_email' => $email_teste,
        'barbeiro_nome' => 'Carlos Pereira',
        'servico_nome' => 'Corte Completo + Barba',
        'data_hora' => date('Y-m-d H:i:s', strtotime('tomorrow 14:00')),
        'preco' => 45.00
    ];
    
    echo "🧪 TESTE 1: Email de confirmação de agendamento\n";
    echo "📤 Enviando para: {$email_teste}\n";
    
    $resultado1 = enviar_email_agendamento('confirmacao', $dados_teste);
    
    if ($resultado1) {
        echo "✅ Email de confirmação enviado com sucesso!\n";
    } else {
        echo "❌ Falha no envio do email de confirmação\n";
    }
    
    echo "\n" . str_repeat('-', 50) . "\n\n";
    
    echo "🧪 TESTE 2: Email de lembrete de agendamento\n";
    echo "📤 Enviando para: {$email_teste}\n";
    
    $resultado2 = enviar_email_agendamento('lembrete', $dados_teste);
    
    if ($resultado2) {
        echo "✅ Email de lembrete enviado com sucesso!\n";
    } else {
        echo "❌ Falha no envio do email de lembrete\n";
    }
    
    echo "\n" . str_repeat('-', 50) . "\n\n";
    
    echo "🧪 TESTE 3: Email de cancelamento\n";
    echo "📤 Enviando para: {$email_teste}\n";
    
    $resultado3 = enviar_email_agendamento('cancelamento', $dados_teste);
    
    if ($resultado3) {
        echo "✅ Email de cancelamento enviado com sucesso!\n";
    } else {
        echo "❌ Falha no envio do email de cancelamento\n";
    }
    
    echo "\n" . str_repeat('-', 50) . "\n\n";
    
    echo "🧪 TESTE 4: Email de contato\n";
    
    $dados_contato = [
        'nome' => 'Maria Santos (TESTE)',
        'email' => $email_teste,
        'telefone' => '(11) 99999-9999',
        'assunto' => 'Teste do sistema de contato',
        'mensagem' => 'Esta é uma mensagem de teste do sistema de contato do Mr. Carlos Barbershop. Se você está recebendo este email, o sistema está funcionando corretamente!'
    ];
    
    echo "📤 Enviando email de contato...\n";
    
    $resultado4 = enviar_email_contato($dados_contato);
    
    if ($resultado4) {
        echo "✅ Email de contato enviado com sucesso!\n";
    } else {
        echo "❌ Falha no envio do email de contato\n";
    }
    
    echo "\n" . str_repeat('=', 50) . "\n\n";
    
    // Resumo dos testes
    $sucessos = array_sum([$resultado1, $resultado2, $resultado3, $resultado4]);
    $total = 4;
    
    echo "📊 RESUMO DOS TESTES:\n";
    echo "✅ Sucessos: {$sucessos}/{$total}\n";
    echo "❌ Falhas: " . ($total - $sucessos) . "/{$total}\n\n";
    
    if ($sucessos === $total) {
        echo "🎉 PARABÉNS! Todos os testes passaram!\n";
        echo "📧 Verifique sua caixa de email (inclusive spam) para ver os 4 emails enviados.\n";
    } elseif ($sucessos > 0) {
        echo "⚠️  Alguns testes falharam. Verifique as configurações SMTP.\n";
    } else {
        echo "💥 Todos os testes falharam. Verifique:\n";
        echo "   1. Configurações SMTP no config.php\n";
        echo "   2. Credenciais de email (senha de app para Gmail)\n";
        echo "   3. Conexão com a internet\n";
        echo "   4. Logs de erro para mais detalhes\n";
    }
    
    echo "\n📝 Dica: Verifique os logs de erro para detalhes sobre falhas.\n";
    
} catch (Exception $e) {
    echo "💥 ERRO CRÍTICO: " . $e->getMessage() . "\n";
    echo "\n🔍 Possíveis soluções:\n";
    echo "   1. Instale o PHPMailer: composer require phpmailer/phpmailer\n";
    echo "   2. Configure as constantes SMTP no config/config.php\n";
    echo "   3. Verifique se as credenciais estão corretas\n";
    echo "   4. Para Gmail, use senha de app, não a senha normal\n";
}

// Fechar HTML se executado no browser
if (php_sapi_name() !== 'cli') {
    echo "</body></html>";
}
?>