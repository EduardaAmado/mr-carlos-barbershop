<?php
/**
 * Sistema de Monitoramento - Mr. Carlos Barbershop
 * 
 * Sistema para monitoramento de performance, saúde do sistema e alertas
 * Executa verificações automáticas e gera relatórios de status
 * 
 * @author Sistema Mr. Carlos Barbershop
 * @version 1.0
 * @since 2025-10-14
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/cache.php';
require_once __DIR__ . '/../includes/optimizer.php';

class SystemMonitor 
{
    private $metrics = [];
    private $alerts = [];
    private $config;
    
    // Thresholds de alerta
    const CPU_THRESHOLD = 80.0;
    const MEMORY_THRESHOLD = 85.0;
    const DISK_THRESHOLD = 90.0;
    const RESPONSE_TIME_THRESHOLD = 2.0; // segundos
    const ERROR_RATE_THRESHOLD = 5.0; // %
    
    public function __construct() 
    {
        $this->config = [
            'enable_email_alerts' => true,
            'alert_email' => 'admin@mrcarbos.com',
            'check_interval' => 300, // 5 minutos
            'log_retention' => 30, // dias
            'critical_services' => [
                'database',
                'web_server',
                'php_fpm',
                'cache'
            ]
        ];
    }
    
    /**
     * Executar monitoramento completo
     */
    public function runFullCheck(): array 
    {
        $this->log("Iniciando verificação completa do sistema...");
        
        $checks = [
            'system_resources' => $this->checkSystemResources(),
            'database_health' => $this->checkDatabaseHealth(),
            'web_server' => $this->checkWebServer(),
            'application_health' => $this->checkApplicationHealth(),
            'security_status' => $this->checkSecurityStatus(),
            'cache_performance' => $this->checkCachePerformance(),
            'disk_space' => $this->checkDiskSpace(),
            'service_availability' => $this->checkServiceAvailability()
        ];
        
        $this->generateSummaryReport($checks);
        $this->processAlerts();
        
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'checks' => $checks,
            'alerts' => $this->alerts,
            'status' => $this->getOverallStatus($checks)
        ];
    }
    
    /**
     * Verificar recursos do sistema
     */
    private function checkSystemResources(): array 
    {
        $metrics = [];
        
        // CPU Usage
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $cpu_usage = $load[0] * 100 / 4; // Assumindo 4 cores
            $metrics['cpu_usage'] = round($cpu_usage, 2);
            
            if ($cpu_usage > self::CPU_THRESHOLD) {
                $this->addAlert('high_cpu', "CPU usage alto: {$cpu_usage}%", 'warning');
            }
        }
        
        // Memory Usage
        $memory = $this->getMemoryUsage();
        $metrics['memory'] = $memory;
        
        if ($memory['usage_percent'] > self::MEMORY_THRESHOLD) {
            $this->addAlert('high_memory', "Uso de memória alto: {$memory['usage_percent']}%", 'warning');
        }
        
        // PHP Memory
        $metrics['php_memory'] = [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => ini_get('memory_limit')
        ];
        
        return [
            'status' => 'ok',
            'metrics' => $metrics,
            'timestamp' => time()
        ];
    }
    
    /**
     * Verificar saúde do banco de dados
     */
    private function checkDatabaseHealth(): array 
    {
        $metrics = [];
        
        try {
            global $pdo;
            
            // Testar conexão
            $start = microtime(true);
            $pdo->query("SELECT 1");
            $connection_time = microtime(true) - $start;
            
            $metrics['connection_time'] = $connection_time;
            
            if ($connection_time > 1.0) {
                $this->addAlert('slow_db', "Conexão lenta com BD: {$connection_time}s", 'warning');
            }
            
            // Estatísticas do MySQL
            $stats = optimizer()->getPerformanceStats();
            $metrics['performance'] = $stats;
            
            // Verificar queries lentas
            $slowQueries = optimizer()->analyzeSlowQueries();
            $metrics['slow_queries'] = count($slowQueries);
            
            if (count($slowQueries) > 10) {
                $this->addAlert('slow_queries', count($slowQueries) . " queries lentas detectadas", 'warning');
            }
            
            // Verificar tamanho do banco
            $stmt = $pdo->query("
                SELECT 
                    SUM(data_length + index_length) / 1024 / 1024 as size_mb 
                FROM information_schema.tables 
                WHERE table_schema = '" . DB_NAME . "'
            ");
            $db_size = $stmt->fetchColumn();
            $metrics['database_size_mb'] = round($db_size, 2);
            
            return [
                'status' => 'ok',
                'metrics' => $metrics,
                'timestamp' => time()
            ];
            
        } catch (Exception $e) {
            $this->addAlert('database_error', "Erro no banco de dados: " . $e->getMessage(), 'critical');
            
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
                'timestamp' => time()
            ];
        }
    }
    
    /**
     * Verificar servidor web
     */
    private function checkWebServer(): array 
    {
        $metrics = [];
        
        // Verificar se o servidor está respondendo
        $urls = [
            BASE_URL,
            BASE_URL . '/pages/login.php',
            BASE_URL . '/api/services'
        ];
        
        foreach ($urls as $url) {
            $start = microtime(true);
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'method' => 'GET'
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            $response_time = microtime(true) - $start;
            
            $metrics['urls'][$url] = [
                'response_time' => $response_time,
                'status' => $response !== false ? 'ok' : 'error'
            ];
            
            if ($response_time > self::RESPONSE_TIME_THRESHOLD) {
                $this->addAlert('slow_response', "Resposta lenta em {$url}: {$response_time}s", 'warning');
            }
            
            if ($response === false) {
                $this->addAlert('url_error', "Erro ao acessar {$url}", 'critical');
            }
        }
        
        // Verificar logs de erro
        $error_count = $this->countRecentErrors();
        $metrics['recent_errors'] = $error_count;
        
        if ($error_count > 50) {
            $this->addAlert('high_errors', "Muitos erros recentes: {$error_count}", 'warning');
        }
        
        return [
            'status' => 'ok',
            'metrics' => $metrics,
            'timestamp' => time()
        ];
    }
    
    /**
     * Verificar saúde da aplicação
     */
    private function checkApplicationHealth(): array 
    {
        $metrics = [];
        
        try {
            global $pdo;
            
            // Estatísticas de agendamentos
            $stmt = $pdo->query("
                SELECT 
                    COUNT(CASE WHEN DATE(booking_date) = CURDATE() THEN 1 END) as today_bookings,
                    COUNT(CASE WHEN status = 'agendado' AND booking_date >= CURDATE() THEN 1 END) as pending_bookings,
                    COUNT(CASE WHEN status = 'cancelado' AND DATE(created_at) = CURDATE() THEN 1 END) as today_cancellations
                FROM bookings
                WHERE booking_date >= CURDATE() - INTERVAL 7 DAY
            ");
            
            $booking_stats = $stmt->fetch(PDO::FETCH_ASSOC);
            $metrics['bookings'] = $booking_stats;
            
            // Verificar taxa de cancelamento
            if ($booking_stats['today_bookings'] > 0) {
                $cancel_rate = ($booking_stats['today_cancellations'] / $booking_stats['today_bookings']) * 100;
                $metrics['cancel_rate'] = round($cancel_rate, 2);
                
                if ($cancel_rate > 20) {
                    $this->addAlert('high_cancellation', "Taxa de cancelamento alta: {$cancel_rate}%", 'warning');
                }
            }
            
            // Verificar barbeiros ativos
            $stmt = $pdo->query("SELECT COUNT(*) FROM barbers WHERE active = 1");
            $active_barbers = $stmt->fetchColumn();
            $metrics['active_barbers'] = $active_barbers;
            
            if ($active_barbers == 0) {
                $this->addAlert('no_barbers', "Nenhum barbeiro ativo", 'critical');
            }
            
            // Verificar sessões ativas
            if (session_status() === PHP_SESSION_ACTIVE) {
                $session_count = $this->countActiveSessions();
                $metrics['active_sessions'] = $session_count;
            }
            
            return [
                'status' => 'ok',
                'metrics' => $metrics,
                'timestamp' => time()
            ];
            
        } catch (Exception $e) {
            $this->addAlert('app_error', "Erro na aplicação: " . $e->getMessage(), 'critical');
            
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
                'timestamp' => time()
            ];
        }
    }
    
    /**
     * Verificar status de segurança
     */
    private function checkSecurityStatus(): array 
    {
        $metrics = [];
        
        // Verificar tentativas de login falharam
        $failed_logins = $this->countFailedLogins();
        $metrics['failed_logins_24h'] = $failed_logins;
        
        if ($failed_logins > 100) {
            $this->addAlert('security_threat', "Muitas tentativas de login falhas: {$failed_logins}", 'warning');
        }
        
        // Verificar atualizações de segurança
        $metrics['security_headers'] = $this->checkSecurityHeaders();
        
        // Verificar permissões de arquivos
        $metrics['file_permissions'] = $this->checkFilePermissions();
        
        // Verificar configurações SSL
        $metrics['ssl_status'] = $this->checkSSLStatus();
        
        return [
            'status' => 'ok',
            'metrics' => $metrics,
            'timestamp' => time()
        ];
    }
    
    /**
     * Verificar performance do cache
     */
    private function checkCachePerformance(): array 
    {
        $metrics = [];
        
        try {
            $cache = cache();
            $stats = $cache->getStats();
            $metrics = $stats;
            
            // Verificar hit rate
            if (isset($stats['hits']) && isset($stats['misses'])) {
                $total = $stats['hits'] + $stats['misses'];
                if ($total > 0) {
                    $hit_rate = ($stats['hits'] / $total) * 100;
                    $metrics['hit_rate'] = round($hit_rate, 2);
                    
                    if ($hit_rate < 70) {
                        $this->addAlert('low_cache_hit', "Cache hit rate baixo: {$hit_rate}%", 'warning');
                    }
                }
            }
            
            return [
                'status' => 'ok',
                'metrics' => $metrics,
                'timestamp' => time()
            ];
            
        } catch (Exception $e) {
            $this->addAlert('cache_error', "Erro no cache: " . $e->getMessage(), 'warning');
            
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
                'timestamp' => time()
            ];
        }
    }
    
    /**
     * Verificar espaço em disco
     */
    private function checkDiskSpace(): array 
    {
        $metrics = [];
        
        $total = disk_total_space('/');
        $free = disk_free_space('/');
        $used = $total - $free;
        $usage_percent = ($used / $total) * 100;
        
        $metrics = [
            'total_gb' => round($total / (1024**3), 2),
            'used_gb' => round($used / (1024**3), 2),
            'free_gb' => round($free / (1024**3), 2),
            'usage_percent' => round($usage_percent, 2)
        ];
        
        if ($usage_percent > self::DISK_THRESHOLD) {
            $this->addAlert('high_disk_usage', "Uso de disco alto: {$usage_percent}%", 'warning');
        }
        
        return [
            'status' => 'ok',
            'metrics' => $metrics,
            'timestamp' => time()
        ];
    }
    
    /**
     * Verificar disponibilidade dos serviços
     */
    private function checkServiceAvailability(): array 
    {
        $services = [];
        
        foreach ($this->config['critical_services'] as $service) {
            $services[$service] = $this->checkService($service);
        }
        
        return [
            'status' => 'ok',
            'services' => $services,
            'timestamp' => time()
        ];
    }
    
    /**
     * Verificar serviço específico
     */
    private function checkService(string $service): array 
    {
        switch ($service) {
            case 'database':
                try {
                    global $pdo;
                    $pdo->query("SELECT 1");
                    return ['status' => 'running', 'message' => 'Database responding'];
                } catch (Exception $e) {
                    return ['status' => 'error', 'message' => $e->getMessage()];
                }
                
            case 'web_server':
                $response = @file_get_contents(BASE_URL, false, stream_context_create([
                    'http' => ['timeout' => 5]
                ]));
                return $response !== false ? 
                    ['status' => 'running', 'message' => 'Web server responding'] :
                    ['status' => 'error', 'message' => 'Web server not responding'];
                
            case 'cache':
                try {
                    cache()->set('health_check', 'ok', 60);
                    $result = cache()->get('health_check');
                    return $result === 'ok' ?
                        ['status' => 'running', 'message' => 'Cache working'] :
                        ['status' => 'error', 'message' => 'Cache not working'];
                } catch (Exception $e) {
                    return ['status' => 'error', 'message' => $e->getMessage()];
                }
                
            default:
                return ['status' => 'unknown', 'message' => 'Service check not implemented'];
        }
    }
    
    /**
     * Obter uso de memória
     */
    private function getMemoryUsage(): array 
    {
        if (function_exists('memory_get_usage')) {
            $free = shell_exec('free');
            if ($free) {
                $free = (string)trim($free);
                $free_arr = explode("\n", $free);
                $mem = explode(" ", $free_arr[1]);
                $mem = array_filter($mem);
                $mem = array_merge($mem);
                
                $total = $mem[1];
                $used = $mem[2];
                $usage_percent = ($used / $total) * 100;
                
                return [
                    'total' => $total,
                    'used' => $used,
                    'free' => $total - $used,
                    'usage_percent' => round($usage_percent, 2)
                ];
            }
        }
        
        return ['error' => 'Cannot get memory usage'];
    }
    
    /**
     * Contar erros recentes
     */
    private function countRecentErrors(): int 
    {
        $log_file = __DIR__ . '/../storage/logs/error.log';
        
        if (!file_exists($log_file)) {
            return 0;
        }
        
        $count = 0;
        $since = time() - 86400; // últimas 24h
        
        $handle = fopen($log_file, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
                    $timestamp = strtotime($matches[1]);
                    if ($timestamp >= $since) {
                        $count++;
                    }
                }
            }
            fclose($handle);
        }
        
        return $count;
    }
    
    /**
     * Contar sessões ativas
     */
    private function countActiveSessions(): int 
    {
        $session_path = session_save_path() ?: sys_get_temp_dir();
        $files = glob($session_path . '/sess_*');
        
        $active = 0;
        $now = time();
        
        foreach ($files as $file) {
            if (($now - filemtime($file)) < ini_get('session.gc_maxlifetime')) {
                $active++;
            }
        }
        
        return $active;
    }
    
    /**
     * Contar logins falharam
     */
    private function countFailedLogins(): int 
    {
        try {
            global $pdo;
            
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM admin_logs 
                WHERE action = 'login_failed' 
                AND created_at >= NOW() - INTERVAL 24 HOUR
            ");
            
            $stmt->execute();
            return $stmt->fetchColumn();
            
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Verificar headers de segurança
     */
    private function checkSecurityHeaders(): array 
    {
        $headers = [];
        $url = BASE_URL;
        
        $context = stream_context_create([
            'http' => [
                'method' => 'HEAD',
                'timeout' => 5
            ]
        ]);
        
        @get_headers($url, true, $context);
        
        $required_headers = [
            'X-Content-Type-Options',
            'X-Frame-Options',
            'X-XSS-Protection',
            'Strict-Transport-Security'
        ];
        
        foreach ($required_headers as $header) {
            $headers[$header] = isset($http_response_header) && 
                in_array($header, array_map('trim', $http_response_header));
        }
        
        return $headers;
    }
    
    /**
     * Verificar permissões de arquivos
     */
    private function checkFilePermissions(): array 
    {
        $issues = [];
        
        $sensitive_files = [
            'config/config.php' => '644',
            '.env' => '600',
            'storage/logs/' => '755',
            'storage/cache/' => '755'
        ];
        
        foreach ($sensitive_files as $file => $expected) {
            $full_path = __DIR__ . '/../' . $file;
            if (file_exists($full_path)) {
                $perms = substr(sprintf('%o', fileperms($full_path)), -3);
                if ($perms !== $expected) {
                    $issues[] = "File {$file} has {$perms}, expected {$expected}";
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * Verificar status SSL
     */
    private function checkSSLStatus(): array 
    {
        $url = str_replace('http://', 'https://', BASE_URL);
        
        $context = stream_context_create([
            "ssl" => [
                "capture_peer_cert" => true,
            ],
            "http" => [
                "timeout" => 5
            ]
        ]);
        
        $result = @stream_context_get_params($context);
        
        if (isset($result['options']['ssl']['peer_certificate'])) {
            $cert = openssl_x509_parse($result['options']['ssl']['peer_certificate']);
            return [
                'enabled' => true,
                'valid_from' => date('Y-m-d', $cert['validFrom_time_t']),
                'valid_to' => date('Y-m-d', $cert['validTo_time_t']),
                'issuer' => $cert['issuer']['CN'] ?? 'Unknown'
            ];
        }
        
        return ['enabled' => false];
    }
    
    /**
     * Adicionar alerta
     */
    private function addAlert(string $type, string $message, string $level): void 
    {
        $this->alerts[] = [
            'type' => $type,
            'message' => $message,
            'level' => $level,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Processar alertas
     */
    private function processAlerts(): void 
    {
        if (empty($this->alerts)) {
            return;
        }
        
        $critical_alerts = array_filter($this->alerts, function($alert) {
            return $alert['level'] === 'critical';
        });
        
        if (!empty($critical_alerts) && $this->config['enable_email_alerts']) {
            $this->sendAlertEmail($critical_alerts);
        }
        
        // Log de todos os alertas
        $this->logAlerts($this->alerts);
    }
    
    /**
     * Enviar email de alerta
     */
    private function sendAlertEmail(array $alerts): void 
    {
        $subject = "ALERTA CRÍTICO - Sistema Mr. Carlos Barbershop";
        $body = "Os seguintes alertas críticos foram detectados:\n\n";
        
        foreach ($alerts as $alert) {
            $body .= "- [{$alert['level']}] {$alert['message']} ({$alert['timestamp']})\n";
        }
        
        $body .= "\nVerifique o sistema imediatamente.";
        
        // Usar sistema de email existente
        if (function_exists('sendSystemEmail')) {
            sendSystemEmail($this->config['alert_email'], $subject, $body);
        }
    }
    
    /**
     * Log de alertas
     */
    private function logAlerts(array $alerts): void 
    {
        $log_file = __DIR__ . '/../storage/logs/monitor.log';
        
        foreach ($alerts as $alert) {
            $log_line = "[{$alert['timestamp']}] {$alert['level']}: {$alert['message']}\n";
            file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
        }
    }
    
    /**
     * Obter status geral
     */
    private function getOverallStatus(array $checks): string 
    {
        foreach ($checks as $check) {
            if (isset($check['status']) && $check['status'] === 'error') {
                return 'critical';
            }
        }
        
        return count($this->alerts) > 0 ? 'warning' : 'ok';
    }
    
    /**
     * Gerar relatório resumido
     */
    private function generateSummaryReport(array $checks): void 
    {
        $this->log("=== RELATÓRIO DE MONITORAMENTO ===");
        $this->log("Timestamp: " . date('Y-m-d H:i:s'));
        $this->log("Status geral: " . $this->getOverallStatus($checks));
        $this->log("Total de alertas: " . count($this->alerts));
        
        foreach ($checks as $name => $check) {
            $status = $check['status'] ?? 'unknown';
            $this->log("{$name}: {$status}");
        }
        
        if (!empty($this->alerts)) {
            $this->log("\nAlertas:");
            foreach ($this->alerts as $alert) {
                $this->log("- [{$alert['level']}] {$alert['message']}");
            }
        }
        
        $this->log("=== FIM DO RELATÓRIO ===\n");
    }
    
    /**
     * Log de mensagem
     */
    private function log(string $message): void 
    {
        $log_file = __DIR__ . '/../storage/logs/monitor.log';
        $log_line = "[" . date('Y-m-d H:i:s') . "] {$message}\n";
        file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
        
        // Output para console se executado via CLI
        if (php_sapi_name() === 'cli') {
            echo $log_line;
        }
    }
}

// Executar se chamado diretamente via CLI
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $monitor = new SystemMonitor();
    $result = $monitor->runFullCheck();
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
    // Exit code baseado no status
    exit($result['status'] === 'critical' ? 1 : 0);
}