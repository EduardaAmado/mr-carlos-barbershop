#!/bin/bash
#
# Script de Deploy - Mr. Carlos Barbershop
# 
# Script automatizado para deploy em produção com verificações de segurança,
# backup automático, otimizações e configurações de servidor
#
# Uso: ./deploy.sh [environment] [version]
# Exemplo: ./deploy.sh production v1.0.0
#
# @author Sistema Mr. Carlos Barbershop
# @version 1.0
# @since 2025-10-14

set -e  # Parar em caso de erro

# Configurações
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
ENVIRONMENT="${1:-staging}"
VERSION="${2:-$(date +%Y%m%d-%H%M%S)}"

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configurações por ambiente
case $ENVIRONMENT in
    "production")
        DOMAIN="mrcarbos.com"
        DB_NAME="mrcarbos_prod"
        BACKUP_RETENTION=30
        ;;
    "staging")
        DOMAIN="staging.mrcarbos.com"
        DB_NAME="mrcarbos_staging"
        BACKUP_RETENTION=7
        ;;
    *)
        echo -e "${RED}Ambiente inválido. Use: production ou staging${NC}"
        exit 1
        ;;
esac

# Funções auxiliares
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

success() {
    echo -e "${GREEN}✓ $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

error() {
    echo -e "${RED}✗ $1${NC}"
    exit 1
}

# Verificar pré-requisitos
check_requirements() {
    log "Verificando pré-requisitos..."
    
    # Verificar comandos necessários
    local commands=("php" "mysql" "composer" "npm" "git")
    for cmd in "${commands[@]}"; do
        if ! command -v $cmd &> /dev/null; then
            error "Comando '$cmd' não encontrado"
        fi
    done
    
    # Verificar PHP versão
    local php_version=$(php -r "echo PHP_VERSION;")
    if [[ $(echo "$php_version" | cut -d. -f1) -lt 7 ]] || [[ $(echo "$php_version" | cut -d. -f2) -lt 4 ]]; then
        error "PHP 7.4+ é obrigatório. Versão atual: $php_version"
    fi
    
    # Verificar extensões PHP
    local extensions=("pdo" "pdo_mysql" "mbstring" "openssl" "json" "curl" "gd" "zip")
    for ext in "${extensions[@]}"; do
        if ! php -m | grep -q $ext; then
            error "Extensão PHP '$ext' não encontrada"
        fi
    done
    
    success "Pré-requisitos verificados"
}

# Fazer backup antes do deploy
create_backup() {
    log "Criando backup..."
    
    local backup_dir="/var/backups/mr-carlos-barbershop"
    local backup_name="backup_${ENVIRONMENT}_${VERSION}"
    local backup_path="${backup_dir}/${backup_name}"
    
    # Criar diretório de backup
    sudo mkdir -p "$backup_dir"
    
    # Backup do banco de dados
    log "Backup do banco de dados..."
    mysqldump -u root -p "${DB_NAME}" > "${backup_path}_database.sql"
    
    # Backup dos arquivos
    log "Backup dos arquivos..."
    tar -czf "${backup_path}_files.tar.gz" -C "$(dirname "$PROJECT_ROOT")" "$(basename "$PROJECT_ROOT")"
    
    # Limpar backups antigos
    find "$backup_dir" -name "backup_${ENVIRONMENT}_*" -mtime +$BACKUP_RETENTION -delete
    
    success "Backup criado: $backup_name"
}

# Atualizar código
update_code() {
    log "Atualizando código..."
    
    cd "$PROJECT_ROOT"
    
    # Git pull (se usando git)
    if [ -d ".git" ]; then
        git fetch --all
        git reset --hard "origin/main"
    fi
    
    # Instalar dependências PHP
    if [ -f "composer.json" ]; then
        composer install --no-dev --optimize-autoloader
    fi
    
    # Instalar dependências Node.js (se houver)
    if [ -f "package.json" ]; then
        npm ci --production
    fi
    
    success "Código atualizado"
}

# Configurar ambiente
setup_environment() {
    log "Configurando ambiente ${ENVIRONMENT}..."
    
    # Copiar arquivo de configuração
    local env_file="${PROJECT_ROOT}/config/.env.${ENVIRONMENT}"
    if [ -f "$env_file" ]; then
        cp "$env_file" "${PROJECT_ROOT}/.env"
    else
        warning "Arquivo .env.${ENVIRONMENT} não encontrado"
    fi
    
    # Configurar permissões
    setup_permissions
    
    success "Ambiente configurado"
}

# Configurar permissões
setup_permissions() {
    log "Configurando permissões..."
    
    cd "$PROJECT_ROOT"
    
    # Definir proprietário
    sudo chown -R www-data:www-data .
    
    # Permissões de diretórios
    find . -type d -exec chmod 755 {} \;
    
    # Permissões de arquivos
    find . -type f -exec chmod 644 {} \;
    
    # Permissões especiais
    chmod -R 775 storage/
    chmod -R 775 storage/cache/
    chmod -R 775 storage/assets/
    chmod -R 775 storage/logs/
    chmod -R 775 storage/backups/
    
    # Scripts executáveis
    chmod +x deploy/*.sh
    chmod +x cron/*.php
    
    success "Permissões configuradas"
}

# Executar migrações do banco de dados
run_migrations() {
    log "Executando migrações do banco..."
    
    # Verificar se o banco existe
    if ! mysql -u root -p -e "USE ${DB_NAME}"; then
        log "Criando banco de dados ${DB_NAME}..."
        mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    fi
    
    # Executar schema se for primeira instalação
    if ! mysql -u root -p "${DB_NAME}" -e "SELECT 1 FROM clients LIMIT 1;" &>/dev/null; then
        log "Executando schema inicial..."
        mysql -u root -p "${DB_NAME}" < "${PROJECT_ROOT}/database/schema.sql"
    fi
    
    # Executar migrações (se houver)
    if [ -d "${PROJECT_ROOT}/database/migrations" ]; then
        for migration in "${PROJECT_ROOT}/database/migrations"/*.sql; do
            if [ -f "$migration" ]; then
                log "Executando migração: $(basename "$migration")"
                mysql -u root -p "${DB_NAME}" < "$migration"
            fi
        done
    fi
    
    success "Migrações executadas"
}

# Otimizar assets
optimize_assets() {
    log "Otimizando assets..."
    
    cd "$PROJECT_ROOT"
    
    # Limpar cache anterior
    rm -rf storage/assets/*
    
    # Executar otimizações via PHP
    php -r "
        require_once 'config/config.php';
        require_once 'includes/cache.php';
        require_once 'includes/assets.php';
        require_once 'includes/optimizer.php';
        
        \$optimizer = AssetOptimizer::getInstance();
        \$optimizer->clearCache();
        
        \$queryOptimizer = QueryOptimizer::getInstance();
        \$queryOptimizer->createOptimizedIndexes();
        \$queryOptimizer->optimizeTables();
        
        echo 'Assets otimizados' . PHP_EOL;
    "
    
    success "Assets otimizados"
}

# Configurar cron jobs
setup_cron() {
    log "Configurando cron jobs..."
    
    # Criar arquivo de cron temporário
    local cron_file="/tmp/mr-carlos-cron"
    
    cat > "$cron_file" << EOF
# Mr. Carlos Barbershop - Cron Jobs
# Executar lembretes de email a cada 15 minutos
*/15 * * * * cd ${PROJECT_ROOT} && php cron/lembretes.php >> storage/logs/cron.log 2>&1

# Backup automático diário às 02:00
0 2 * * * cd ${PROJECT_ROOT} && php tools/backup.php >> storage/logs/backup.log 2>&1

# Limpeza de logs antigos às 03:00
0 3 * * * find ${PROJECT_ROOT}/storage/logs -name "*.log" -mtime +30 -delete

# Limpeza de cache expirado a cada hora
0 * * * * cd ${PROJECT_ROOT} && php -r "require_once 'includes/cache.php'; cache()->clear();"

# Monitoramento do sistema a cada 5 minutos
*/5 * * * * cd ${PROJECT_ROOT} && php tools/monitor.php >> storage/logs/monitor.log 2>&1
EOF
    
    # Instalar cron jobs
    sudo crontab -u www-data "$cron_file"
    rm "$cron_file"
    
    success "Cron jobs configurados"
}

# Configurar servidor web
configure_webserver() {
    log "Configurando servidor web..."
    
    # Configuração do Apache
    if command -v apache2 &> /dev/null; then
        configure_apache
    fi
    
    # Configuração do Nginx
    if command -v nginx &> /dev/null; then
        configure_nginx
    fi
    
    success "Servidor web configurado"
}

# Configurar Apache
configure_apache() {
    log "Configurando Apache..."
    
    local site_config="/etc/apache2/sites-available/${DOMAIN}.conf"
    
    sudo tee "$site_config" > /dev/null << EOF
<VirtualHost *:80>
    ServerName ${DOMAIN}
    DocumentRoot ${PROJECT_ROOT}
    
    # Redirecionar para HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    ServerName ${DOMAIN}
    DocumentRoot ${PROJECT_ROOT}
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/${DOMAIN}/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/${DOMAIN}/privkey.pem
    
    # Security Headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Cache Control
    <LocationMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2)$">
        ExpiresActive On
        ExpiresDefault "access plus 1 year"
        Header append Cache-Control "public, immutable"
    </LocationMatch>
    
    # Compression
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
    </IfModule>
    
    # Directory Configuration
    <Directory ${PROJECT_ROOT}>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Block access to sensitive files
        <FilesMatch "\.(env|sql|log|bak)$">
            Require all denied
        </FilesMatch>
        
        <DirectoryMatch "(config|includes|database|deploy|tools)">
            Require all denied
        </DirectoryMatch>
    </Directory>
    
    # Error and Access Logs
    ErrorLog \${APACHE_LOG_DIR}/${DOMAIN}_error.log
    CustomLog \${APACHE_LOG_DIR}/${DOMAIN}_access.log combined
</VirtualHost>
EOF
    
    # Habilitar site
    sudo a2ensite "${DOMAIN}.conf"
    
    # Habilitar módulos necessários
    sudo a2enmod rewrite ssl headers expires deflate
    
    # Testar configuração
    sudo apache2ctl configtest
    
    # Recarregar Apache
    sudo systemctl reload apache2
}

# Configurar Nginx
configure_nginx() {
    log "Configurando Nginx..."
    
    local site_config="/etc/nginx/sites-available/${DOMAIN}"
    
    sudo tee "$site_config" > /dev/null << EOF
# HTTP - Redirect to HTTPS
server {
    listen 80;
    server_name ${DOMAIN};
    return 301 https://\$server_name\$request_uri;
}

# HTTPS
server {
    listen 443 ssl http2;
    server_name ${DOMAIN};
    
    root ${PROJECT_ROOT};
    index index.php index.html;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/${DOMAIN}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/${DOMAIN}/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    
    # Security Headers
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options DENY;
    add_header X-XSS-Protection "1; mode=block";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin";
    
    # Cache Control
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Compression
    gzip on;
    gzip_types text/html text/plain text/xml text/css text/javascript application/javascript application/json;
    
    # PHP Processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Block access to sensitive files
    location ~ /\.(env|sql|log|bak)$ {
        deny all;
    }
    
    location ~ /(config|includes|database|deploy|tools)/ {
        deny all;
    }
    
    # Asset optimization
    location /storage/assets/ {
        try_files \$uri \$uri.gz \$uri.br =404;
    }
    
    # Error pages
    error_page 404 /404.html;
    error_page 500 502 503 504 /50x.html;
    
    # Logs
    access_log /var/log/nginx/${DOMAIN}_access.log;
    error_log /var/log/nginx/${DOMAIN}_error.log;
}
EOF
    
    # Habilitar site
    sudo ln -sf "$site_config" "/etc/nginx/sites-enabled/"
    
    # Testar configuração
    sudo nginx -t
    
    # Recarregar Nginx
    sudo systemctl reload nginx
}

# Configurar SSL com Let's Encrypt
setup_ssl() {
    log "Configurando SSL..."
    
    # Instalar certbot se não existir
    if ! command -v certbot &> /dev/null; then
        sudo apt-get update
        sudo apt-get install -y certbot
        
        if command -v apache2 &> /dev/null; then
            sudo apt-get install -y python3-certbot-apache
        fi
        
        if command -v nginx &> /dev/null; then
            sudo apt-get install -y python3-certbot-nginx
        fi
    fi
    
    # Gerar certificado
    if [ "$ENVIRONMENT" = "production" ]; then
        if command -v apache2 &> /dev/null; then
            sudo certbot --apache -d "$DOMAIN" --non-interactive --agree-tos --email "admin@${DOMAIN}"
        elif command -v nginx &> /dev/null; then
            sudo certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos --email "admin@${DOMAIN}"
        fi
    else
        warning "SSL não configurado para ambiente ${ENVIRONMENT}"
    fi
    
    success "SSL configurado"
}

# Verificar saúde do sistema
health_check() {
    log "Verificando saúde do sistema..."
    
    # Verificar conexão com banco
    if ! php -r "
        require_once '${PROJECT_ROOT}/config/config.php';
        try {
            \$pdo = new PDO(\"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME, DB_USER, DB_PASS);
            echo 'Database: OK' . PHP_EOL;
        } catch (Exception \$e) {
            echo 'Database: ERRO - ' . \$e->getMessage() . PHP_EOL;
            exit(1);
        }
    "; then
        error "Falha na conexão com o banco de dados"
    fi
    
    # Verificar URLs principais
    local urls=(
        "https://${DOMAIN}/"
        "https://${DOMAIN}/pages/login.php"
        "https://${DOMAIN}/barbeiro/login.php"
        "https://${DOMAIN}/admin/login.php"
    )
    
    for url in "${urls[@]}"; do
        if curl -sf "$url" > /dev/null; then
            success "URL OK: $url"
        else
            warning "URL com problema: $url"
        fi
    done
    
    success "Verificação de saúde concluída"
}

# Função principal
main() {
    echo -e "${BLUE}"
    echo "=================================================="
    echo "      Deploy Mr. Carlos Barbershop System       "
    echo "=================================================="
    echo -e "${NC}"
    echo "Ambiente: ${ENVIRONMENT}"
    echo "Versão: ${VERSION}"
    echo "Domínio: ${DOMAIN}"
    echo ""
    
    check_requirements
    create_backup
    update_code
    setup_environment
    run_migrations
    optimize_assets
    setup_cron
    configure_webserver
    
    if [ "$ENVIRONMENT" = "production" ]; then
        setup_ssl
    fi
    
    health_check
    
    echo ""
    success "Deploy concluído com sucesso!"
    echo "Sistema disponível em: https://${DOMAIN}"
}

# Verificar se está executando como root para algumas operações
if [[ $EUID -ne 0 ]]; then
    warning "Algumas operações podem requerer sudo"
fi

# Executar função principal
main "$@"