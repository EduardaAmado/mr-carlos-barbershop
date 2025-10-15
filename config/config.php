<?php
/**
 * Ficheiro de configuração principal
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Definir constantes de configuração da aplicação e carregar conexão à base de dados
 */

// Configuração da base de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mr_carlos_barbershop');

// Configurações gerais
define('BASE_PATH', __DIR__ . '/..');
define('BASE_URL', 'http://localhost/mr-carlos-barbershop/');
define('SITE_NAME', 'Mr. Carlos Barbershop');
define('ADMIN_EMAIL', 'admin@mrcarlosbarbershop.pt');
define('ENVIRONMENT', 'development'); // development ou production

// Configurações de segurança
define('SESSION_TIMEOUT', 3600); // 1 hora em segundos
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutos de bloqueio

// Configurações de email (para usar com PHPMailer)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'seu-email@gmail.com'); // ⚠️ ALTERE AQUI
define('SMTP_PASSWORD', 'sua-senha-de-app'); // ⚠️ ALTERE AQUI (use senha de app, não senha normal)
define('SMTP_FROM_EMAIL', SMTP_USERNAME);
define('SMTP_FROM_NAME', SITE_NAME);

// Fuso horário
date_default_timezone_set('Europe/Lisbon');
define('TIMEZONE_OFFSET', '+01:00'); // Offset para Portugal

// Inicializar sessão de forma segura
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Incluir conexão à base de dados
require_once __DIR__ . '/db_connect.php';

// Função para obter URL base
function get_base_url($path = '') {
    return BASE_URL . ltrim($path, '/');
}

// Função para redirecionar com segurança
function safe_redirect($url) {
    // Validar se é URL interno
    if (strpos($url, BASE_URL) === 0 || strpos($url, '/') === 0) {
        header("Location: $url");
        exit();
    }
}