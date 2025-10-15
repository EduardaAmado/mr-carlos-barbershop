# Manual de Instalação - Mr. Carlos Barbershop

## Índice
1. [Requisitos do Sistema](#requisitos-do-sistema)
2. [Instalação Passo a Passo](#instalação-passo-a-passo)
3. [Configuração do Banco de Dados](#configuração-do-banco-de-dados)
4. [Configuração do Servidor Web](#configuração-do-servidor-web)
5. [Configuração de Email](#configuração-de-email)
6. [Configuração de Segurança](#configuração-de-segurança)
7. [Verificação da Instalação](#verificação-da-instalação)
8. [Problemas Comuns](#problemas-comuns)

---

## Requisitos do Sistema

### Requisitos Mínimos
- **PHP**: 7.4 ou superior (recomendado 8.0+)
- **MySQL**: 5.7 ou superior (ou MariaDB 10.2+)
- **Servidor Web**: Apache 2.4+ ou Nginx 1.18+
- **Memória**: 128MB mínimo (recomendado 512MB)
- **Espaço em Disco**: 500MB mínimo
- **SSL**: Certificado SSL válido (obrigatório para produção)

### Extensões PHP Obrigatórias
```bash
# Verificar extensões instaladas
php -m | grep -E "(mysqli|session|json|filter|hash|openssl|curl|mbstring|zip|gd)"
```

- `mysqli` - Conexão com MySQL
- `session` - Gerenciamento de sessões
- `json` - Manipulação JSON
- `filter` - Filtragem de dados
- `hash` - Funções de hash
- `openssl` - Criptografia SSL
- `curl` - Requisições HTTP
- `mbstring` - Strings multibyte
- `zip` - Compactação de arquivos
- `gd` - Manipulação de imagens (opcional)

---

## Instalação Passo a Passo

### 1. Download e Preparação

```bash
# Clone ou baixe o projeto
git clone https://github.com/seu-usuario/mr-carlos-barbershop.git
cd mr-carlos-barbershop

# Ou baixe e extraia o ZIP
unzip mr-carlos-barbershop.zip
cd mr-carlos-barbershop
```

### 2. Estrutura de Diretórios
Certifique-se de que a estrutura está correta:

```
mr-carlos-barbershop/
├── admin/              # Dashboard administrativo
├── api/               # Endpoints da API
├── assets/            # CSS, JS, imagens
├── barbeiro/          # Dashboard do barbeiro
├── config/            # Configurações
├── cron/              # Scripts automatizados
├── database/          # Scripts SQL
├── docs/              # Documentação
├── includes/          # Arquivos compartilhados
├── pages/             # Páginas públicas
├── tools/             # Ferramentas de manutenção
├── uploads/           # Arquivos enviados (criar)
├── logs/              # Logs do sistema (criar)
├── cache/             # Cache do sistema (criar)
├── index.php          # Página inicial
└── .htaccess          # Configurações Apache
```

### 3. Permissões de Arquivos

```bash
# No Linux/macOS
chmod 755 -R .
chmod 644 *.php
chmod 755 uploads/ logs/ cache/ tools/
chmod 600 config/config.php

# Criar diretórios necessários
mkdir -p uploads logs cache tools/backups
chown -R www-data:www-data uploads/ logs/ cache/ tools/backups/
```

```cmd
# No Windows (como administrador)
icacls uploads /grant IIS_IUSRS:(OI)(CI)F
icacls logs /grant IIS_IUSRS:(OI)(CI)F
icacls cache /grant IIS_IUSRS:(OI)(CI)F
icacls tools\backups /grant IIS_IUSRS:(OI)(CI)F
```

---

## Configuração do Banco de Dados

### 1. Criar Banco de Dados

```sql
-- Conectar no MySQL como root
mysql -u root -p

-- Criar banco e usuário
CREATE DATABASE mr_carlos_barbershop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'barbershop_user'@'localhost' IDENTIFIED BY 'sua_senha_segura_aqui';
GRANT ALL PRIVILEGES ON mr_carlos_barbershop.* TO 'barbershop_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. Importar Schema

```bash
# Importar estrutura do banco
mysql -u barbershop_user -p mr_carlos_barbershop < database/schema.sql
```

### 3. Verificar Instalação do Banco

```sql
-- Conectar e verificar tabelas
mysql -u barbershop_user -p mr_carlos_barbershop

SHOW TABLES;
-- Deve mostrar: agendamentos, barbeiros, clientes, servicos, security_logs, failed_login_attempts

-- Verificar dados iniciais
SELECT * FROM barbeiros;
SELECT * FROM servicos;
```

---

## Configuração do Servidor Web

### Apache (.htaccess)
O arquivo `.htaccess` já está configurado com:

```apache
# Segurança
Options -Indexes -ExecCGI
ServerSignature Off

# Reescrita de URLs
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([^/]+)/?$ pages/$1.php [L,QSA]

# Headers de Segurança
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options SAMEORIGIN
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

# Compressão
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css text/javascript application/javascript application/json
</IfModule>

# Cache de Assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/* "access plus 6 months"
</IfModule>

# Proteção de Arquivos Sensíveis
<FilesMatch "\.(sql|log|md|json)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

<Directory "config">
    Order Allow,Deny
    Deny from all
</Directory>
```

### Nginx (nginx.conf)
Para Nginx, adicionar ao arquivo de configuração:

```nginx
server {
    listen 80;
    server_name seu-dominio.com www.seu-dominio.com;
    root /var/www/mr-carlos-barbershop;
    index index.php;

    # Segurança
    server_tokens off;
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options SAMEORIGIN;
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    # PHP
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # URLs amigáveis
    location / {
        try_files $uri $uri/ /pages/$uri.php;
    }

    # Proteção de arquivos
    location ~* \.(sql|log|md|json)$ {
        deny all;
    }

    location ^~ /config/ {
        deny all;
    }

    location ^~ /tools/ {
        deny all;
    }

    # Cache de assets
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1M;
        add_header Cache-Control "public, immutable";
    }

    # Redirecionamento HTTPS (opcional)
    # return 301 https://$server_name$request_uri;
}
```

---

## Configuração do Sistema

### 1. Arquivo de Configuração Principal

Editar `config/config.php`:

```php
<?php
// === CONFIGURAÇÕES DO BANCO DE DADOS ===
define('DB_HOST', 'localhost');
define('DB_USER', 'barbershop_user');
define('DB_PASS', 'sua_senha_aqui');
define('DB_NAME', 'mr_carlos_barbershop');

// === CONFIGURAÇÕES DO SITE ===
define('SITE_NAME', 'Mr. Carlos Barbershop');
define('SITE_URL', 'https://seu-dominio.com');
define('SITE_EMAIL', 'contato@seu-dominio.com');

// === CONFIGURAÇÕES DE EMAIL ===
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'seu-email@gmail.com');
define('SMTP_PASSWORD', 'sua-senha-app');
define('SMTP_ENCRYPTION', 'tls');
define('FROM_EMAIL', 'noreply@seu-dominio.com');
define('FROM_NAME', 'Mr. Carlos Barbershop');

// === CONFIGURAÇÕES DE SEGURANÇA ===
define('SECURITY_SALT', 'gere_uma_string_aleatoria_de_64_caracteres_aqui');
define('SESSION_LIFETIME', 3600); // 1 hora
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutos

// === CONFIGURAÇÕES DE TIMEZONE ===
define('TIMEZONE', 'America/Sao_Paulo');
date_default_timezone_set(TIMEZONE);
```

### 2. Gerar Chave de Segurança

```bash
# Gerar string aleatória para SECURITY_SALT
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
```

### 3. Configurar Email

#### Gmail/Google Workspace:
1. Ativar autenticação de 2 fatores
2. Gerar senha específica para aplicativo
3. Usar essa senha no `SMTP_PASSWORD`

#### Outros Provedores:
```php
// Outlook/Hotmail
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);

// Yahoo
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);

// Servidor próprio
define('SMTP_HOST', 'mail.seu-dominio.com');
define('SMTP_PORT', 587);
```

---

## Configuração de Segurança

### 1. SSL/HTTPS (Obrigatório para Produção)

```bash
# Let's Encrypt (gratuito)
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d seu-dominio.com -d www.seu-dominio.com

# Ou para Nginx
sudo certbot --nginx -d seu-dominio.com -d www.seu-dominio.com
```

### 2. Firewall

```bash
# Ubuntu/Debian
sudo ufw enable
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https
sudo ufw allow mysql

# CentOS/RHEL
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --permanent --add-service=mysql
sudo firewall-cmd --reload
```

### 3. Backup do Banco de Dados

```bash
# Criar script de backup automático
sudo crontab -e

# Adicionar linha para backup diário às 2h
0 2 * * * /usr/bin/php /var/www/mr-carlos-barbershop/tools/backup.php > /dev/null 2>&1
```

---

## Verificação da Instalação

### 1. Teste Básico

Acesse: `https://seu-dominio.com/tools/test_system.php`

Ou execute via linha de comando:
```bash
cd /var/www/mr-carlos-barbershop
php tools/test_system.php
```

### 2. Checklist de Verificação

- [ ] Site carrega na página inicial
- [ ] Formulário de contato funciona
- [ ] Registro de novo cliente funciona
- [ ] Login de cliente funciona
- [ ] Dashboard admin acessível (`/admin`)
- [ ] Dashboard barbeiro acessível (`/barbeiro`)
- [ ] Sistema de agendamento funciona
- [ ] Emails são enviados corretamente
- [ ] Backup funciona
- [ ] Testes passam sem erros críticos

### 3. Criar Usuário Admin Inicial

```sql
-- Conectar no banco e inserir admin
mysql -u barbershop_user -p mr_carlos_barbershop

INSERT INTO admins (nome, email, password_hash, ativo, created_at) 
VALUES ('Administrador', 'admin@seu-dominio.com', 
        '$2y$10$hash_da_senha_aqui', 1, NOW());
```

Para gerar hash da senha:
```bash
php -r "echo password_hash('sua_senha_admin', PASSWORD_DEFAULT) . PHP_EOL;"
```

---

## Problemas Comuns

### Erro de Conexão com Banco

**Sintoma**: "Connection refused" ou "Access denied"

**Soluções**:
```bash
# Verificar se MySQL está rodando
sudo systemctl status mysql
sudo systemctl start mysql

# Verificar usuário e senha
mysql -u barbershop_user -p

# Verificar permissões
SHOW GRANTS FOR 'barbershop_user'@'localhost';
```

### Erro de Permissões de Arquivo

**Sintoma**: "Permission denied" ou "Failed to open stream"

**Soluções**:
```bash
# Corrigir permissões
sudo chown -R www-data:www-data /var/www/mr-carlos-barbershop
sudo chmod -R 755 /var/www/mr-carlos-barbershop
sudo chmod -R 777 uploads/ logs/ cache/
```

### Emails Não Enviados

**Sintoma**: Erro SMTP ou emails não chegam

**Soluções**:
1. Verificar configurações SMTP
2. Testar conexão: `telnet smtp.gmail.com 587`
3. Verificar logs: `tail -f logs/email.log`
4. Usar ferramenta de teste: `/tools/test_email.php`

### Session ou CSRF Errors

**Sintoma**: "Token CSRF inválido" repetidamente

**Soluções**:
```bash
# Verificar permissões de sessão
ls -la /tmp/sess_*
sudo chown www-data:www-data /var/lib/php/sessions/

# Limpar sessões antigas
sudo rm /var/lib/php/sessions/sess_*
```

### Performance Lenta

**Sintoma**: Site carrega devagar

**Soluções**:
1. Verificar índices do banco: Execute `/tools/test_system.php`
2. Ativar cache do PHP: `opcache.enable=1` no php.ini
3. Otimizar banco: Execute `/tools/maintenance.php`
4. Verificar recursos: `htop` ou `top`

### Erro 500 Internal Server Error

**Sintomas**: Página em branco ou erro 500

**Soluções**:
```bash
# Verificar logs do Apache/Nginx
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/log/nginx/error.log

# Verificar logs do PHP
sudo tail -f /var/log/php/error.log

# Ativar display de erros temporariamente
echo "ini_set('display_errors', 1);" >> config/config.php
```

---

## Configuração de Produção

### 1. Otimizações de Segurança

```php
// Em config/config.php - apenas produção
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
```

### 2. Configurações do PHP (php.ini)

```ini
; Produção
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log

; Performance
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 2
opcache.fast_shutdown = 1

; Segurança
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off
```

### 3. Monitoramento

```bash
# Criar script de monitoramento
#!/bin/bash
# /usr/local/bin/monitor_barbershop.sh

# Verificar se site responde
curl -f https://seu-dominio.com > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "Site não responde!" | mail -s "Alerta: Mr. Carlos Barbershop" admin@seu-dominio.com
fi

# Verificar espaço em disco
df -h | awk '$5 > 80 {print $0}' | mail -s "Alerta: Disco Cheio" admin@seu-dominio.com

# Executar no cron a cada 5 minutos
# */5 * * * * /usr/local/bin/monitor_barbershop.sh
```

---

## Suporte

### Logs do Sistema
- **Aplicação**: `logs/application.log`
- **Segurança**: `logs/security.log`  
- **Email**: `logs/email.log`
- **Backup**: `tools/backup.log`
- **Manutenção**: `tools/maintenance.log`

### Contato
- **Email**: suporte@seu-dominio.com
- **Documentação**: https://docs.seu-dominio.com
- **GitHub**: https://github.com/seu-usuario/mr-carlos-barbershop

---

*Documento atualizado em: 14 de Outubro de 2025*