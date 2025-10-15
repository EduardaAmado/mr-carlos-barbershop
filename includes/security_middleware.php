<?php
/**
 * Middleware de Segurança para Formulários - Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Aplicar automaticamente as verificações de segurança em formulários
 */

// Garantir que a configuração está carregada antes da segurança
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../config/config.php';
}

require_once __DIR__ . '/security.php';

/**
 * Middleware para proteger formulários automaticamente
 */
function secure_form_handler($form_name, $required_fields = [], $custom_validations = []) 
{
    // Verificar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['success' => false, 'error' => 'Método não permitido.'];
    }

    // Verificar CSRF
    if (!validate_csrf($_POST['csrf_token'] ?? '', $form_name)) {
        return ['success' => false, 'error' => 'Token de segurança inválido. Recarregue a página e tente novamente.'];
    }

    // Verificar rate limiting
    if (!check_rate_limit($form_name)) {
        return ['success' => false, 'error' => 'Muitas tentativas. Aguarde alguns minutos e tente novamente.'];
    }

    // Sanitizar e validar campos obrigatórios
    $sanitized_data = [];
    $errors = [];

    foreach ($required_fields as $field => $type) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $errors[] = "O campo '{$field}' é obrigatório.";
            continue;
        }

        $sanitized_value = sanitize($_POST[$field], $type);
        if ($sanitized_value === false) {
            $errors[] = "O campo '{$field}' contém dados inválidos.";
            continue;
        }

        $sanitized_data[$field] = $sanitized_value;
    }

    // Aplicar validações customizadas
    foreach ($custom_validations as $field => $validation) {
        if (isset($sanitized_data[$field])) {
            $validation_result = $validation($sanitized_data[$field]);
            if ($validation_result !== true) {
                $errors[] = $validation_result;
            }
        }
    }

    if (!empty($errors)) {
        record_attempt($form_name, null, false);
        return ['success' => false, 'errors' => $errors];
    }

    return ['success' => true, 'data' => $sanitized_data];
}

/**
 * Validações customizadas comuns
 */
class FormValidators 
{
    public static function password($password) 
    {
        $errors = security()->validatePasswordStrength($password);
        return empty($errors) ? true : implode(' ', $errors);
    }

    public static function phone($phone) 
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleaned) < 10 || strlen($cleaned) > 11) {
            return 'Telefone deve ter 10 ou 11 dígitos.';
        }
        return true;
    }

    public static function cpf($cpf) 
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return 'CPF inválido.';
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return 'CPF inválido.';
            }
        }
        
        return true;
    }

    public static function date($date) 
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return ($d && $d->format('Y-m-d') === $date) ? true : 'Data inválida.';
    }

    public static function time($time) 
    {
        return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time) ? true : 'Horário inválido.';
    }

    public static function future_date($date) 
    {
        if (!self::date($date)) return 'Data inválida.';
        return (strtotime($date) >= strtotime('today')) ? true : 'A data deve ser hoje ou no futuro.';
    }

    public static function business_hours($time) 
    {
        if (!self::time($time)) return 'Horário inválido.';
        
        $hour = (int)substr($time, 0, 2);
        return ($hour >= 8 && $hour <= 18) ? true : 'Horário deve ser entre 08:00 e 18:00.';
    }
}

/**
 * Proteger uploads de arquivos
 */
function secure_file_upload($file, $allowed_types = ['image/jpeg', 'image/png', 'image/gif'], $max_size = 5242880) 
{
    // Verificar se arquivo foi enviado
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Erro no upload do arquivo.'];
    }

    // Verificar tamanho
    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'Arquivo muito grande. Máximo: ' . ($max_size / 1024 / 1024) . 'MB'];
    }

    // Verificar tipo MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        log_security('file_access', 'Tentativa de upload de arquivo não permitido: ' . $mime_type, 'medium');
        return ['success' => false, 'error' => 'Tipo de arquivo não permitido.'];
    }

    // Verificar extensão
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($ext, $allowed_extensions)) {
        return ['success' => false, 'error' => 'Extensão de arquivo não permitida.'];
    }

    // Gerar nome seguro
    $safe_name = uniqid() . '.' . $ext;
    
    return ['success' => true, 'safe_name' => $safe_name, 'mime_type' => $mime_type];
}

/**
 * Verificar permissões de acesso por papel
 */
function check_role_access($required_roles, $user_role) 
{
    if (!is_array($required_roles)) {
        $required_roles = [$required_roles];
    }
    
    if (!in_array($user_role, $required_roles)) {
        log_security('suspicious_activity', 
            "Tentativa de acesso não autorizado. Papel: $user_role, Requerido: " . implode(',', $required_roles), 
            'high', 
            $_SESSION['user_id'] ?? null
        );
        return false;
    }
    
    return true;
}

/**
 * Middleware para APIs
 */
function secure_api_handler($required_method = 'POST', $rate_limit_action = 'api') 
{
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== $required_method) {
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        exit;
    }

    // Verificar rate limiting
    if (!check_rate_limit($rate_limit_action)) {
        http_response_code(429);
        echo json_encode(['error' => 'Muitas requisições. Tente novamente mais tarde.']);
        exit;
    }

    // Verificar Content-Type para POSTs
    if ($required_method === 'POST') {
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($content_type, 'application/json') === false && 
            strpos($content_type, 'application/x-www-form-urlencoded') === false &&
            strpos($content_type, 'multipart/form-data') === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Content-Type inválido']);
            exit;
        }
    }

    // Headers de segurança
    header('Content-Type: application/json');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
}

/**
 * Sanitizar dados de entrada JSON
 */
function sanitize_json_input() 
{
    $input = file_get_contents('php://input');
    if (empty($input)) {
        return [];
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'JSON inválido']);
        exit;
    }

    // Sanitizar recursivamente
    function sanitize_recursive($data) {
        if (is_array($data)) {
            return array_map('sanitize_recursive', $data);
        }
        return sanitize($data);
    }

    return sanitize_recursive($data);
}

/**
 * Headers de segurança padrão
 */
function set_security_headers() 
{
    // Prevenir clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // Prevenir MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // XSS Protection
    header('X-XSS-Protection: 1; mode=block');
    
    // HTTPS apenas (descomente em produção com HTTPS)
    // header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    
    // Política de referrer
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy (CSP) básico
    $csp = "default-src 'self'; " .
           "style-src 'self' 'unsafe-inline' fonts.googleapis.com cdn.jsdelivr.net unpkg.com cdnjs.cloudflare.com; " .
           "font-src 'self' fonts.gstatic.com cdnjs.cloudflare.com data:; " .
           "script-src 'self' 'unsafe-inline' cdn.jsdelivr.net unpkg.com cdn.tailwindcss.com cdnjs.cloudflare.com; " .
           "img-src 'self' data:; " .
           "connect-src 'self';";
    header("Content-Security-Policy: $csp");
}

// Aplicar headers de segurança automaticamente
set_security_headers();