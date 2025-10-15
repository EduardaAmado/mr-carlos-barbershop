<?php
/**
 * Sistema de Segurança Avançada - Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Implementar proteção CSRF, rate limiting, sanitização avançada e logs de segurança
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class SecurityManager 
{
    private static $instance = null;
    private $conn;
    private $security_logs_table = 'security_logs';
    private $failed_attempts_table = 'failed_login_attempts';
    
    // Configurações de rate limiting
    private $rate_limits = [
        'login' => ['attempts' => 5, 'window' => 900], // 5 tentativas em 15 minutos
        'contact_form' => ['attempts' => 3, 'window' => 300], // 3 envios em 5 minutos
        'booking' => ['attempts' => 10, 'window' => 600], // 10 agendamentos em 10 minutos
        'api' => ['attempts' => 30, 'window' => 300], // 30 requests em 5 minutos
        'password_reset' => ['attempts' => 3, 'window' => 3600] // 3 tentativas em 1 hora
    ];

    private function __construct() 
    {
        global $pdo;
        $this->conn = $pdo;
        
        // Verificar se a conexão está disponível
        if ($this->conn === null) {
            error_log("SecurityManager: Conexão PDO não disponível");
            return;
        }
        
        $this->initializeTables();
    }

    public static function getInstance() 
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Inicializar tabelas de segurança se não existirem
     */
    private function initializeTables() 
    {
        // Criar tabela de logs de segurança
        $security_logs_sql = "
            CREATE TABLE IF NOT EXISTS {$this->security_logs_table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_type ENUM('login_attempt', 'csrf_violation', 'rate_limit', 'xss_attempt', 'sql_injection', 'file_access', 'suspicious_activity') NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT,
                user_id INT NULL,
                details TEXT NOT NULL,
                severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_event_type (event_type),
                INDEX idx_ip_address (ip_address),
                INDEX idx_created_at (created_at),
                INDEX idx_severity (severity)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        // Criar tabela de tentativas de login falhas
        $failed_attempts_sql = "
            CREATE TABLE IF NOT EXISTS {$this->failed_attempts_table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(45) NOT NULL,
                email VARCHAR(255),
                attempt_type VARCHAR(50) NOT NULL,
                attempts_count INT DEFAULT 1,
                first_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                blocked_until TIMESTAMP NULL,
                INDEX idx_ip_address (ip_address),
                INDEX idx_email (email),
                INDEX idx_attempt_type (attempt_type),
                INDEX idx_blocked_until (blocked_until)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        try {
            if ($this->conn !== null) {
                $this->conn->query($security_logs_sql);
                $this->conn->query($failed_attempts_sql);
            }
        } catch (Exception $e) {
            error_log("Erro ao criar tabelas de segurança: " . $e->getMessage());
        }
    }

    /**
     * Gerar token CSRF
     */
    public function generateCSRFToken($form_name = 'default') 
    {
        if (!isset($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_tokens'][$form_name] = [
            'token' => $token,
            'expires' => time() + 3600 // 1 hora
        ];
        
        return $token;
    }

    /**
     * Validar token CSRF
     */
    public function validateCSRFToken($token, $form_name = 'default') 
    {
        if (!isset($_SESSION['csrf_tokens'][$form_name])) {
            $this->logSecurityEvent('csrf_violation', 'Token CSRF não encontrado para: ' . $form_name, 'high');
            return false;
        }

        $stored_token = $_SESSION['csrf_tokens'][$form_name];
        
        // Verificar se o token expirou
        if (time() > $stored_token['expires']) {
            unset($_SESSION['csrf_tokens'][$form_name]);
            $this->logSecurityEvent('csrf_violation', 'Token CSRF expirado para: ' . $form_name, 'medium');
            return false;
        }

        // Verificar se o token é válido
        if (!hash_equals($stored_token['token'], $token)) {
            $this->logSecurityEvent('csrf_violation', 'Token CSRF inválido para: ' . $form_name, 'high');
            return false;
        }

        // Token válido, remover após uso
        unset($_SESSION['csrf_tokens'][$form_name]);
        return true;
    }

    /**
     * Verificar rate limiting
     */
    public function checkRateLimit($action, $identifier = null) 
    {
        if (!isset($this->rate_limits[$action])) {
            return true; // Ação não configurada, permitir
        }

        $limit_config = $this->rate_limits[$action];
        $ip_address = $this->getClientIP();
        $identifier = $identifier ?: $ip_address;

        try {
            // Limpar tentativas antigas
            $cutoff_time = date('Y-m-d H:i:s', time() - $limit_config['window']);
            $cleanup_sql = "DELETE FROM {$this->failed_attempts_table} 
                           WHERE attempt_type = ? AND first_attempt < ? AND blocked_until IS NULL";
            $stmt = $this->conn->prepare($cleanup_sql);
            $stmt->execute([$action, $cutoff_time]);

            // Verificar tentativas atuais
            $check_sql = "SELECT attempts_count, blocked_until 
                         FROM {$this->failed_attempts_table} 
                         WHERE (ip_address = ? OR email = ?) AND attempt_type = ?
                         ORDER BY last_attempt DESC LIMIT 1";
            $stmt = $this->conn->prepare($check_sql);
            $stmt->execute([$ip_address, $identifier, $action]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Verificar se ainda está bloqueado
                if ($row['blocked_until'] && strtotime($row['blocked_until']) > time()) {
                    $remaining_time = strtotime($row['blocked_until']) - time();
                    $this->logSecurityEvent('rate_limit', "Tentativa durante bloqueio: $action. Tempo restante: {$remaining_time}s", 'medium');
                    return false;
                }

                // Verificar se excedeu o limite
                if ($row['attempts_count'] >= $limit_config['attempts']) {
                    $this->blockUser($action, $identifier, $limit_config['window']);
                    return false;
                }
            }

            return true;

        } catch (Exception $e) {
            error_log("Erro no rate limiting: " . $e->getMessage());
            return true; // Em caso de erro, permitir (fail-open)
        }
    }

    /**
     * Registrar tentativa para rate limiting
     */
    public function recordAttempt($action, $identifier = null, $success = false) 
    {
        if (!isset($this->rate_limits[$action])) {
            return;
        }

        $ip_address = $this->getClientIP();
        $identifier = $identifier ?: $ip_address;

        try {
            if ($success) {
                // Limpar tentativas em caso de sucesso
                $delete_sql = "DELETE FROM {$this->failed_attempts_table} 
                              WHERE (ip_address = ? OR email = ?) AND attempt_type = ?";
                $stmt = $this->conn->prepare($delete_sql);
                $stmt->execute([$ip_address, $identifier, $action]);
            } else {
                // Registrar tentativa falhada
                $insert_sql = "INSERT INTO {$this->failed_attempts_table} 
                              (ip_address, email, attempt_type, attempts_count, first_attempt, last_attempt)
                              VALUES (?, ?, ?, 1, NOW(), NOW())
                              ON DUPLICATE KEY UPDATE 
                              attempts_count = attempts_count + 1, 
                              last_attempt = NOW()";
                $stmt = $this->conn->prepare($insert_sql);
                $stmt->execute([$ip_address, $identifier, $action]);
            }
        } catch (Exception $e) {
            error_log("Erro ao registrar tentativa: " . $e->getMessage());
        }
    }

    /**
     * Bloquear usuário temporariamente
     */
    private function blockUser($action, $identifier, $duration) 
    {
        $ip_address = $this->getClientIP();
        $blocked_until = date('Y-m-d H:i:s', time() + $duration);

        try {
            $update_sql = "UPDATE {$this->failed_attempts_table} 
                          SET blocked_until = ? 
                          WHERE (ip_address = ? OR email = ?) AND attempt_type = ?";
            $stmt = $this->conn->prepare($update_sql);
            $stmt->execute([$blocked_until, $ip_address, $identifier, $action]);

            $this->logSecurityEvent('rate_limit', 
                "Usuário bloqueado por $duration segundos. Ação: $action, Identifier: $identifier", 
                'high');

        } catch (Exception $e) {
            error_log("Erro ao bloquear usuário: " . $e->getMessage());
        }
    }

    /**
     * Sanitização avançada de dados
     */
    public function sanitizeInput($data, $type = 'string') 
    {
        if (is_array($data)) {
            return array_map(function($item) use ($type) {
                return $this->sanitizeInput($item, $type);
            }, $data);
        }

        // Remover caracteres de controle e espaços extras
        $data = trim($data);
        $data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $data);

        switch ($type) {
            case 'email':
                $data = filter_var($data, FILTER_SANITIZE_EMAIL);
                if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
                    $this->logSecurityEvent('suspicious_activity', 'Email inválido fornecido: ' . $data, 'low');
                    return false;
                }
                break;

            case 'phone':
                $data = preg_replace('/[^0-9+\-\(\)\s]/', '', $data);
                break;

            case 'name':
                $data = preg_replace('/[^a-zA-ZÀ-ÿ\s\'-]/', '', $data);
                $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
                break;

            case 'alphanumeric':
                $data = preg_replace('/[^a-zA-Z0-9]/', '', $data);
                break;

            case 'integer':
                $data = filter_var($data, FILTER_SANITIZE_NUMBER_INT);
                if (!is_numeric($data)) {
                    return 0;
                }
                break;

            case 'float':
                $data = filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                break;

            case 'url':
                $data = filter_var($data, FILTER_SANITIZE_URL);
                if (!filter_var($data, FILTER_VALIDATE_URL)) {
                    return false;
                }
                break;

            case 'html':
                // Permitir apenas tags HTML seguras
                $allowed_tags = '<p><br><strong><em><u><ul><ol><li><h1><h2><h3><h4><h5><h6>';
                $data = strip_tags($data, $allowed_tags);
                $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
                break;

            default: // 'string'
                $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
                break;
        }

        // Detectar tentativas de XSS
        $xss_patterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i'
        ];

        foreach ($xss_patterns as $pattern) {
            if (preg_match($pattern, $data)) {
                $this->logSecurityEvent('xss_attempt', 'Tentativa de XSS detectada: ' . $data, 'critical');
                return '';
            }
        }

        // Detectar tentativas de SQL injection
        $sql_patterns = [
            '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC)\b)/i',
            '/(UNION|OR|AND)\s+\d+\s*=\s*\d+/i',
            '/\b(OR|AND)\s+1\s*=\s*1\b/i',
            '/\';\s*(DROP|INSERT|DELETE)/i'
        ];

        foreach ($sql_patterns as $pattern) {
            if (preg_match($pattern, $data)) {
                $this->logSecurityEvent('sql_injection', 'Tentativa de SQL injection detectada: ' . $data, 'critical');
                return '';
            }
        }

        return $data;
    }

    /**
     * Validar força da senha
     */
    public function validatePasswordStrength($password) 
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'A senha deve ter pelo menos 8 caracteres.';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'A senha deve conter pelo menos uma letra maiúscula.';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'A senha deve conter pelo menos uma letra minúscula.';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'A senha deve conter pelo menos um número.';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'A senha deve conter pelo menos um caractere especial.';
        }
        
        // Verificar senhas comuns
        $common_passwords = [
            '12345678', 'password', 'senha123', '123456789', 'qwerty', 
            'abc123', 'password123', '123123', 'admin', 'letmein'
        ];
        
        if (in_array(strtolower($password), $common_passwords)) {
            $errors[] = 'Esta senha é muito comum. Escolha uma senha mais segura.';
        }
        
        return $errors;
    }

    /**
     * Obter IP do cliente com suporte a proxies
     */
    public function getClientIP() 
    {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) && !empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Registrar evento de segurança
     */
    public function logSecurityEvent($event_type, $details, $severity = 'medium', $user_id = null) 
    {
        $ip_address = $this->getClientIP();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO {$this->security_logs_table} 
                (event_type, ip_address, user_agent, user_id, details, severity) 
                VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$event_type, $ip_address, $user_agent, $user_id, $details, $severity]);

            // Log crítico também no arquivo de sistema
            if ($severity === 'critical') {
                error_log("SECURITY CRITICAL: [$event_type] IP: $ip_address - $details");
            }

        } catch (Exception $e) {
            error_log("Erro ao registrar log de segurança: " . $e->getMessage());
        }
    }

    /**
     * Verificar se IP está em blacklist
     */
    public function isBlacklisted($ip = null) 
    {
        $ip = $ip ?: $this->getClientIP();
        
        // Lista de IPs conhecidamente maliciosos (pode ser expandida)
        $blacklisted_ips = [
            '127.0.0.2', // Exemplo
        ];
        
        if (in_array($ip, $blacklisted_ips)) {
            $this->logSecurityEvent('suspicious_activity', 'Acesso de IP em blacklist: ' . $ip, 'critical');
            return true;
        }
        
        // Verificar IPs com muitas tentativas recentes
        try {
            $stmt = $this->conn->prepare(
                "SELECT COUNT(*) as violations 
                FROM {$this->security_logs_table} 
                WHERE ip_address = ? 
                AND severity IN ('high', 'critical') 
                AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)"
            );
            $stmt->execute([$ip]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['violations'] > 10) {
                $this->logSecurityEvent('suspicious_activity', 
                    'IP com muitas violações recentes: ' . $ip . ' (' . $result['violations'] . ' violações)', 
                    'critical');
                return true;
            }
        } catch (Exception $e) {
            error_log("Erro ao verificar blacklist: " . $e->getMessage());
        }
        
        return false;
    }

    /**
     * Limpar logs antigos de segurança
     */
    public function cleanupSecurityLogs($days = 90) 
    {
        try {
            $stmt = $this->conn->prepare(
                "DELETE FROM {$this->security_logs_table} 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)"
            );
            $stmt->execute([$days]);
            
            $stmt = $this->conn->prepare(
                "DELETE FROM {$this->failed_attempts_table} 
                WHERE first_attempt < DATE_SUB(NOW(), INTERVAL ? DAY) 
                AND blocked_until IS NULL"
            );
            $stmt->execute([$days]);
            
        } catch (Exception $e) {
            error_log("Erro na limpeza de logs: " . $e->getMessage());
        }
    }

    /**
     * Gerar relatório de segurança
     */
    public function getSecurityReport($days = 7) 
    {
        try {
            $report = [];
            
            // Eventos por tipo
            $stmt = $this->conn->prepare(
                "SELECT event_type, COUNT(*) as count 
                FROM {$this->security_logs_table} 
                WHERE created_at > DATE_SUB(NOW(), INTERVAL ? DAY) 
                GROUP BY event_type 
                ORDER BY count DESC"
            );
            $stmt->execute([$days]);
            $report['events_by_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // IPs mais ativos
            $stmt = $this->conn->prepare(
                "SELECT ip_address, COUNT(*) as count 
                FROM {$this->security_logs_table} 
                WHERE created_at > DATE_SUB(NOW(), INTERVAL ? DAY) 
                GROUP BY ip_address 
                ORDER BY count DESC 
                LIMIT 10"
            );
            $stmt->execute([$days]);
            $report['top_ips'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Eventos críticos
            $stmt = $this->conn->prepare(
                "SELECT * FROM {$this->security_logs_table} 
                WHERE severity = 'critical' 
                AND created_at > DATE_SUB(NOW(), INTERVAL ? DAY) 
                ORDER BY created_at DESC 
                LIMIT 20"
            );
            $stmt->execute([$days]);
            $report['critical_events'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $report;
            
        } catch (Exception $e) {
            error_log("Erro ao gerar relatório de segurança: " . $e->getMessage());
            return [];
        }
    }
}

// Funções helper globais para facilitar o uso
function security() 
{
    return SecurityManager::getInstance();
}

function csrf_token($form_name = 'default') 
{
    return security()->generateCSRFToken($form_name);
}

function csrf_field($form_name = 'default') 
{
    $token = csrf_token($form_name);
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

function validate_csrf($token = null, $form_name = 'default') 
{
    $token = $token ?: ($_POST['csrf_token'] ?? '');
    return security()->validateCSRFToken($token, $form_name);
}

function sanitize($data, $type = 'string') 
{
    return security()->sanitizeInput($data, $type);
}

function check_rate_limit($action, $identifier = null) 
{
    return security()->checkRateLimit($action, $identifier);
}

function record_attempt($action, $identifier = null, $success = false) 
{
    security()->recordAttempt($action, $identifier, $success);
}

function log_security($event_type, $details, $severity = 'medium', $user_id = null) 
{
    security()->logSecurityEvent($event_type, $details, $severity, $user_id);
}

function is_ip_blacklisted($ip = null) 
{
    return security()->isBlacklisted($ip);
}

// Verificação automática de IP em blacklist para todas as requisições
// COMENTADO: Essas verificações devem ser chamadas explicitamente quando necessário
// para evitar problemas de inicialização da conexão PDO
/*
if (is_ip_blacklisted()) {
    http_response_code(403);
    die('Acesso negado.');
}

// Middleware de segurança automático
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar rate limiting para POSTs
    $action = basename($_SERVER['PHP_SELF'], '.php');
    if (!check_rate_limit($action)) {
        http_response_code(429);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Muitas tentativas. Tente novamente mais tarde.']);
        exit;
    }
}
*/