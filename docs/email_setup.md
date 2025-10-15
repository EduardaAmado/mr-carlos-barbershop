# Guia de Instalação do PHPMailer - Mr. Carlos Barbershop

## Instalação via Composer (Recomendado)

### 1. Instalar Composer
Se ainda não tem o Composer instalado, baixe em: https://getcomposer.org/

### 2. Instalar PHPMailer
No diretório raiz do projeto, execute:
```bash
composer require phpmailer/phpmailer
```

## Instalação Manual (Alternativa)

### 1. Baixar PHPMailer
- Acesse: https://github.com/PHPMailer/PHPMailer/releases
- Baixe a versão mais recente
- Extraia os arquivos para uma pasta `phpmailer` dentro do projeto

### 2. Incluir Manualmente
Se optar pela instalação manual, substitua esta linha no `includes/email.php`:
```php
require_once __DIR__ . '/../vendor/autoload.php';
```

Por estas linhas:
```php
require_once __DIR__ . '/../phpmailer/src/Exception.php';
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';
```

## Configuração de Email

### 1. Gmail (Recomendado para testes)
Para usar Gmail, você precisa:
1. Ativar a verificação em duas etapas na sua conta Google
2. Gerar uma "Senha de app" específica
3. Usar essas configurações no `config/config.php`:

```php
// Configurações de Email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'seu-email@gmail.com');
define('SMTP_PASSWORD', 'sua-senha-de-app-aqui'); // NÃO use a senha normal!
define('SMTP_FROM_EMAIL', 'seu-email@gmail.com');
define('SMTP_FROM_NAME', 'Mr. Carlos Barbershop');
```

### 2. Outros Provedores
#### Outlook/Hotmail
```php
define('SMTP_HOST', 'smtp.live.com');
define('SMTP_PORT', 587);
```

#### Yahoo
```php
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);
```

#### Servidor Próprio
```php
define('SMTP_HOST', 'mail.seudominio.com.br');
define('SMTP_PORT', 587); // ou 465 para SSL
define('SMTP_USERNAME', 'contato@seudominio.com.br');
define('SMTP_PASSWORD', 'sua-senha');
```

## Configuração do CRON para Lembretes

### 1. Criar arquivo CRON
Crie o arquivo `cron/lembretes.php`:

```php
<?php
// Evitar execução via browser
if (php_sapi_name() !== 'cli') {
    die('Este script deve ser executado via linha de comando apenas');
}

require_once __DIR__ . '/../includes/email.php';

echo "Iniciando processamento de lembretes...\n";
$resultado = processar_lembretes_automaticos();

if ($resultado !== false) {
    echo "Processamento concluído. {$resultado} lembretes enviados.\n";
} else {
    echo "Erro no processamento de lembretes.\n";
}
?>
```

### 2. Configurar CRON no servidor
Adicione esta linha ao crontab (executa diariamente às 18:00):
```bash
0 18 * * * /usr/bin/php /caminho/para/projeto/cron/lembretes.php
```

Para editar o crontab:
```bash
crontab -e
```

### 3. Testar localmente (Windows)
No Windows, você pode usar o Agendador de Tarefas:
1. Abra o "Agendador de Tarefas"
2. Criar Tarefa Básica
3. Configure para executar diariamente
4. Ação: Iniciar um programa
5. Programa: `C:\wamp64\bin\php\php8.x.x\php.exe`
6. Argumentos: `C:\wamp64\www\mr-carlos-barbershop\cron\lembretes.php`

## Teste do Sistema de Email

### 1. Teste Básico
Crie um arquivo `test_email.php` no diretório raiz:

```php
<?php
require_once 'includes/email.php';

// Dados de teste
$dados_teste = [
    'cliente_nome' => 'João Silva',
    'cliente_email' => 'seu-email-de-teste@gmail.com', // MUDE AQUI
    'barbeiro_nome' => 'Carlos',
    'servico_nome' => 'Corte Completo',
    'data_hora' => date('Y-m-d H:i:s', strtotime('+1 day 14:00')),
    'preco' => 35.00
];

echo "Testando envio de email...\n";
$resultado = enviar_email_agendamento('confirmacao', $dados_teste);

if ($resultado) {
    echo "✅ Email enviado com sucesso!\n";
} else {
    echo "❌ Erro no envio do email.\n";
}
?>
```

Execute via browser ou linha de comando:
```bash
php test_email.php
```

## Resolução de Problemas

### 1. "Could not authenticate"
- Verifique se está usando uma "Senha de app" e não a senha normal
- Confirme se a verificação em duas etapas está ativada no Gmail

### 2. "Connection failed"
- Verifique se as configurações de SMTP estão corretas
- Teste com porta 465 em vez de 587
- Verifique se o firewall não está bloqueando

### 3. "SMTP Error: Could not connect to SMTP host"
- Verifique a conexão com a internet
- Teste com outro provedor de email
- Verifique se o servidor permite conexões SMTP

### 4. Emails indo para SPAM
- Configure SPF, DKIM e DMARC no seu domínio
- Use um remetente com domínio próprio
- Evite palavras que acionam filtros de spam

## Monitoramento

### 1. Logs
Os emails são logados automaticamente. Verifique os logs em:
- Windows: `C:\wamp64\logs\php_error.log`
- Linux: `/var/log/apache2/error.log` ou `/var/log/php_errors.log`

### 2. Banco de Dados
Adicione uma tabela para tracking de emails (opcional):

```sql
CREATE TABLE email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agendamento_id INT,
    tipo_email VARCHAR(50),
    destinatario VARCHAR(255),
    status ENUM('enviado', 'erro') DEFAULT 'enviado',
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id)
);
```

## Segurança

### 1. Nunca commite senhas
Adicione ao `.gitignore`:
```
config/config.php
.env
```

### 2. Use variáveis de ambiente
Crie um arquivo `.env`:
```
SMTP_USERNAME=seu-email@gmail.com
SMTP_PASSWORD=sua-senha-de-app
```

E carregue no config.php:
```php
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    define('SMTP_USERNAME', $env['SMTP_USERNAME']);
    define('SMTP_PASSWORD', $env['SMTP_PASSWORD']);
}
```

## Funcionalidades Implementadas

✅ **Confirmação de Agendamento**: Email automático após criação
✅ **Lembretes**: Email 24h antes do agendamento
✅ **Cancelamentos**: Notificação de cancelamento
✅ **Contato**: Formulário de contato do site
✅ **Templates Responsivos**: Layouts que funcionam em todos os dispositivos
✅ **Processamento via CRON**: Lembretes automáticos
✅ **Sistema de Logs**: Rastreamento de envios
✅ **Tratamento de Erros**: Recuperação graceful de falhas