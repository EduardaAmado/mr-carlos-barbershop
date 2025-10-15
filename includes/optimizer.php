<?php
/**
 * Sistema de Otimização de Queries - Mr. Carlos Barbershop
 * 
 * Sistema para otimização, profiling e análise de performance de queries SQL
 * Inclui índices otimizados, query profiler e ferramentas de análise
 * 
 * @author Sistema Mr. Carlos Barbershop
 * @version 1.0
 * @since 2025-10-14
 */

if (!defined('BASE_PATH')) {
    die('Acesso direto não permitido');
}

class QueryOptimizer 
{
    private $pdo;
    private $profiler;
    private $config;
    private static $instance = null;
    
    // Configurações de otimização
    const SLOW_QUERY_THRESHOLD = 0.1; // 100ms
    const ENABLE_PROFILING = true;
    const LOG_SLOW_QUERIES = true;
    
    private function __construct() 
    {
        global $pdo;
        $this->pdo = $pdo;
        $this->profiler = new QueryProfiler();
        
        $this->config = [
            'enable_profiling' => self::ENABLE_PROFILING,
            'slow_threshold' => self::SLOW_QUERY_THRESHOLD,
            'log_slow_queries' => self::LOG_SLOW_QUERIES,
            'cache_prepared' => true,
            'use_indexes' => true
        ];
        
        $this->initializeOptimizations();
    }
    
    public static function getInstance(): QueryOptimizer 
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Inicializar otimizações do MySQL
     */
    private function initializeOptimizations(): void 
    {
        // Configurações otimizadas do MySQL
        $this->executeQueries([
            "SET SESSION query_cache_type = ON",
            "SET SESSION query_cache_size = 67108864", // 64MB
            "SET SESSION tmp_table_size = 33554432",   // 32MB
            "SET SESSION max_heap_table_size = 33554432", // 32MB
            "SET SESSION sort_buffer_size = 2097152",  // 2MB
            "SET SESSION read_buffer_size = 131072",   // 128KB
            "SET SESSION innodb_buffer_pool_size = 134217728" // 128MB (se possível)
        ]);
    }
    
    /**
     * Criar todos os índices otimizados
     */
    public function createOptimizedIndexes(): array 
    {
        $indexes = [];
        
        // Índices para tabela clients
        $indexes[] = $this->createIndex('clients', 'idx_clients_email', ['email'], true);
        $indexes[] = $this->createIndex('clients', 'idx_clients_phone', ['phone']);
        $indexes[] = $this->createIndex('clients', 'idx_clients_active', ['active']);
        $indexes[] = $this->createIndex('clients', 'idx_clients_created', ['created_at']);
        
        // Índices para tabela barbers
        $indexes[] = $this->createIndex('barbers', 'idx_barbers_active', ['active']);
        $indexes[] = $this->createIndex('barbers', 'idx_barbers_status', ['status']);
        
        // Índices para tabela services
        $indexes[] = $this->createIndex('services', 'idx_services_active', ['active']);
        $indexes[] = $this->createIndex('services', 'idx_services_category', ['category']);
        $indexes[] = $this->createIndex('services', 'idx_services_price', ['price']);
        
        // Índices para tabela bookings (críticos para performance)
        $indexes[] = $this->createIndex('bookings', 'idx_bookings_client', ['client_id']);
        $indexes[] = $this->createIndex('bookings', 'idx_bookings_barber', ['barber_id']);
        $indexes[] = $this->createIndex('bookings', 'idx_bookings_date', ['booking_date']);
        $indexes[] = $this->createIndex('bookings', 'idx_bookings_status', ['status']);
        $indexes[] = $this->createIndex('bookings', 'idx_bookings_datetime', ['booking_date', 'start_time']);
        $indexes[] = $this->createIndex('bookings', 'idx_bookings_barber_date', ['barber_id', 'booking_date']);
        $indexes[] = $this->createIndex('bookings', 'idx_bookings_client_date', ['client_id', 'booking_date']);
        $indexes[] = $this->createIndex('bookings', 'idx_bookings_created', ['created_at']);
        
        // Índices para tabela booking_services
        $indexes[] = $this->createIndex('booking_services', 'idx_booking_services_booking', ['booking_id']);
        $indexes[] = $this->createIndex('booking_services', 'idx_booking_services_service', ['service_id']);
        
        // Índices para tabela barber_schedule
        $indexes[] = $this->createIndex('barber_schedule', 'idx_schedule_barber', ['barber_id']);
        $indexes[] = $this->createIndex('barber_schedule', 'idx_schedule_date', ['date']);
        $indexes[] = $this->createIndex('barber_schedule', 'idx_schedule_barber_date', ['barber_id', 'date']);
        
        // Índices para tabela barber_blocked_times
        $indexes[] = $this->createIndex('barber_blocked_times', 'idx_blocked_barber', ['barber_id']);
        $indexes[] = $this->createIndex('barber_blocked_times', 'idx_blocked_date', ['date']);
        $indexes[] = $this->createIndex('barber_blocked_times', 'idx_blocked_barber_date', ['barber_id', 'date']);
        
        // Índices para logs e auditoria
        $indexes[] = $this->createIndex('admin_logs', 'idx_logs_admin', ['admin_id']);
        $indexes[] = $this->createIndex('admin_logs', 'idx_logs_date', ['created_at']);
        $indexes[] = $this->createIndex('admin_logs', 'idx_logs_action', ['action']);
        
        return $indexes;
    }
    
    /**
     * Criar índice específico
     */
    private function createIndex(string $table, string $name, array $columns, bool $unique = false): array 
    {
        try {
            $type = $unique ? 'UNIQUE INDEX' : 'INDEX';
            $cols = implode(', ', $columns);
            
            $sql = "ALTER TABLE {$table} ADD {$type} {$name} ({$cols})";
            
            $start = microtime(true);
            $this->pdo->exec($sql);
            $time = microtime(true) - $start;
            
            return [
                'table' => $table,
                'name' => $name,
                'columns' => $columns,
                'unique' => $unique,
                'success' => true,
                'time' => $time
            ];
            
        } catch (PDOException $e) {
            // Índice já existe ou erro
            return [
                'table' => $table,
                'name' => $name,
                'columns' => $columns,
                'unique' => $unique,
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Análise de queries lentas
     */
    public function analyzeSlowQueries(): array 
    {
        // Habilitar log de queries lentas
        $this->pdo->exec("SET SESSION slow_query_log = 1");
        $this->pdo->exec("SET SESSION long_query_time = " . self::SLOW_QUERY_THRESHOLD);
        
        // Buscar queries lentas recentes
        $stmt = $this->pdo->query("
            SELECT 
                sql_text,
                exec_count,
                avg_timer_wait / 1000000000000 as avg_time_sec,
                sum_timer_wait / 1000000000000 as total_time_sec,
                sum_rows_examined,
                sum_rows_sent,
                sum_created_tmp_tables,
                sum_created_tmp_disk_tables
            FROM performance_schema.events_statements_summary_by_digest 
            WHERE avg_timer_wait > " . (self::SLOW_QUERY_THRESHOLD * 1000000000000) . "
            ORDER BY avg_timer_wait DESC 
            LIMIT 20
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Otimizar queries específicas do sistema
     */
    public function getOptimizedQueries(): array 
    {
        return [
            // Query otimizada para disponibilidade de barbeiro
            'barber_availability' => "
                SELECT 
                    bs.start_time,
                    bs.end_time,
                    bs.available
                FROM barber_schedule bs
                FORCE INDEX (idx_schedule_barber_date)
                WHERE bs.barber_id = ? 
                AND bs.date = ?
                AND bs.available = 1
            ",
            
            // Query otimizada para agendamentos do dia
            'daily_bookings' => "
                SELECT 
                    b.id,
                    b.start_time,
                    b.end_time,
                    b.status,
                    c.name as client_name,
                    c.phone as client_phone,
                    GROUP_CONCAT(s.name SEPARATOR ', ') as services
                FROM bookings b
                FORCE INDEX (idx_bookings_barber_date)
                INNER JOIN clients c ON c.id = b.client_id
                INNER JOIN booking_services bs ON bs.booking_id = b.id
                INNER JOIN services s ON s.id = bs.service_id
                WHERE b.barber_id = ?
                AND b.booking_date = ?
                AND b.status IN ('agendado', 'confirmado')
                GROUP BY b.id
                ORDER BY b.start_time
            ",
            
            // Query otimizada para relatórios
            'revenue_report' => "
                SELECT 
                    DATE(b.booking_date) as date,
                    COUNT(b.id) as total_bookings,
                    SUM(bs.price) as total_revenue,
                    AVG(bs.price) as avg_ticket
                FROM bookings b
                FORCE INDEX (idx_bookings_date)
                INNER JOIN booking_services bs ON bs.booking_id = b.id
                WHERE b.booking_date BETWEEN ? AND ?
                AND b.status = 'concluido'
                GROUP BY DATE(b.booking_date)
                ORDER BY date DESC
            ",
            
            // Query otimizada para busca de clientes
            'client_search' => "
                SELECT 
                    id,
                    name,
                    email,
                    phone,
                    created_at
                FROM clients
                FORCE INDEX (idx_clients_active)
                WHERE active = 1
                AND (
                    name LIKE ? OR 
                    email LIKE ? OR 
                    phone LIKE ?
                )
                ORDER BY name
                LIMIT 50
            ",
            
            // Query otimizada para estatísticas do dashboard
            'dashboard_stats' => "
                SELECT 
                    COUNT(CASE WHEN DATE(booking_date) = CURDATE() THEN 1 END) as today_bookings,
                    COUNT(CASE WHEN booking_date >= CURDATE() - INTERVAL 7 DAY THEN 1 END) as week_bookings,
                    COUNT(CASE WHEN booking_date >= CURDATE() - INTERVAL 30 DAY THEN 1 END) as month_bookings,
                    SUM(CASE WHEN DATE(booking_date) = CURDATE() AND status = 'concluido' 
                        THEN (SELECT SUM(price) FROM booking_services WHERE booking_id = b.id) 
                        ELSE 0 END) as today_revenue
                FROM bookings b
                FORCE INDEX (idx_bookings_date)
                WHERE booking_date >= CURDATE() - INTERVAL 30 DAY
            "
        ];
    }
    
    /**
     * Executar query com profiling
     */
    public function executeWithProfiling(string $sql, array $params = []): array 
    {
        if (!$this->config['enable_profiling']) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $this->profiler->profile($sql, $params, function($sql, $params) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        });
    }
    
    /**
     * Analisar plano de execução de uma query
     */
    public function explainQuery(string $sql, array $params = []): array 
    {
        $stmt = $this->pdo->prepare("EXPLAIN FORMAT=JSON " . $sql);
        $stmt->execute($params);
        
        $result = $stmt->fetchColumn();
        return json_decode($result, true);
    }
    
    /**
     * Otimizar tabelas
     */
    public function optimizeTables(): array 
    {
        $tables = ['clients', 'barbers', 'services', 'bookings', 'booking_services', 
                  'barber_schedule', 'barber_blocked_times', 'admin_logs'];
        
        $results = [];
        
        foreach ($tables as $table) {
            $start = microtime(true);
            
            try {
                // Analisar tabela
                $this->pdo->exec("ANALYZE TABLE {$table}");
                
                // Otimizar tabela
                $stmt = $this->pdo->query("OPTIMIZE TABLE {$table}");
                $optimization = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $time = microtime(true) - $start;
                
                $results[] = [
                    'table' => $table,
                    'success' => true,
                    'message' => $optimization['Msg_text'] ?? 'OK',
                    'time' => $time
                ];
                
            } catch (PDOException $e) {
                $results[] = [
                    'table' => $table,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Executar múltiplas queries
     */
    private function executeQueries(array $queries): void 
    {
        foreach ($queries as $query) {
            try {
                $this->pdo->exec($query);
            } catch (PDOException $e) {
                // Ignorar erros de configuração que podem não ser suportadas
                error_log("Query optimization warning: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Obter estatísticas de performance
     */
    public function getPerformanceStats(): array 
    {
        $stats = [];
        
        // Estatísticas gerais
        $stmt = $this->pdo->query("SHOW STATUS LIKE 'Questions'");
        $stats['total_queries'] = $stmt->fetchColumn(1);
        
        $stmt = $this->pdo->query("SHOW STATUS LIKE 'Uptime'");
        $uptime = $stmt->fetchColumn(1);
        $stats['queries_per_second'] = round($stats['total_queries'] / $uptime, 2);
        
        // Cache de queries
        $stmt = $this->pdo->query("SHOW STATUS LIKE 'Qcache_hits'");
        $cache_hits = $stmt->fetchColumn(1) ?: 0;
        
        $stmt = $this->pdo->query("SHOW STATUS LIKE 'Qcache_inserts'");
        $cache_inserts = $stmt->fetchColumn(1) ?: 0;
        
        $stats['cache_hit_ratio'] = $cache_inserts > 0 ? 
            round(($cache_hits / ($cache_hits + $cache_inserts)) * 100, 2) : 0;
        
        // Conexões
        $stmt = $this->pdo->query("SHOW STATUS LIKE 'Threads_connected'");
        $stats['active_connections'] = $stmt->fetchColumn(1);
        
        $stmt = $this->pdo->query("SHOW STATUS LIKE 'Max_used_connections'");
        $stats['max_connections'] = $stmt->fetchColumn(1);
        
        // Tabelas temporárias
        $stmt = $this->pdo->query("SHOW STATUS LIKE 'Created_tmp_disk_tables'");
        $disk_tmp = $stmt->fetchColumn(1) ?: 0;
        
        $stmt = $this->pdo->query("SHOW STATUS LIKE 'Created_tmp_tables'");
        $tmp_tables = $stmt->fetchColumn(1) ?: 0;
        
        $stats['tmp_disk_ratio'] = $tmp_tables > 0 ? 
            round(($disk_tmp / $tmp_tables) * 100, 2) : 0;
        
        return $stats;
    }
}

/**
 * Profiler de Queries
 */
class QueryProfiler 
{
    private $profiles = [];
    private $enabled = true;
    
    public function profile(string $sql, array $params, callable $executor): array 
    {
        if (!$this->enabled) {
            return $executor($sql, $params);
        }
        
        $start = microtime(true);
        $startMemory = memory_get_usage();
        
        // Executar query
        $result = $executor($sql, $params);
        
        $time = microtime(true) - $start;
        $memory = memory_get_usage() - $startMemory;
        
        // Salvar profile
        $this->profiles[] = [
            'sql' => $this->formatSql($sql),
            'params' => $params,
            'time' => $time,
            'memory' => $memory,
            'rows' => count($result),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Log se for query lenta
        if ($time > QueryOptimizer::SLOW_QUERY_THRESHOLD) {
            $this->logSlowQuery($sql, $params, $time);
        }
        
        return $result;
    }
    
    public function getProfiles(): array 
    {
        return $this->profiles;
    }
    
    public function getSlowQueries(): array 
    {
        return array_filter($this->profiles, function($profile) {
            return $profile['time'] > QueryOptimizer::SLOW_QUERY_THRESHOLD;
        });
    }
    
    public function getTotalTime(): float 
    {
        return array_sum(array_column($this->profiles, 'time'));
    }
    
    public function clear(): void 
    {
        $this->profiles = [];
    }
    
    private function formatSql(string $sql): string 
    {
        // Remover espaços extras e quebras de linha
        $sql = preg_replace('/\s+/', ' ', $sql);
        return trim($sql);
    }
    
    private function logSlowQuery(string $sql, array $params, float $time): void 
    {
        $log = [
            'timestamp' => date('Y-m-d H:i:s'),
            'execution_time' => $time,
            'sql' => $this->formatSql($sql),
            'params' => $params,
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
        ];
        
        error_log("SLOW QUERY: " . json_encode($log));
    }
}

/**
 * Funções auxiliares de otimização
 */
function optimizer(): QueryOptimizer 
{
    return QueryOptimizer::getInstance();
}

function execute_optimized(string $sql, array $params = []): array 
{
    return optimizer()->executeWithProfiling($sql, $params);
}

function get_optimized_query(string $key): string 
{
    $queries = optimizer()->getOptimizedQueries();
    return $queries[$key] ?? '';
}

/**
 * Classe para preparação de statements otimizadas
 */
class OptimizedStatement 
{
    private $pdo;
    private $stmt;
    private $sql;
    private static $prepared = [];
    
    public function __construct(PDO $pdo, string $sql) 
    {
        $this->pdo = $pdo;
        $this->sql = $sql;
        
        // Cache de prepared statements
        $hash = md5($sql);
        if (!isset(self::$prepared[$hash])) {
            self::$prepared[$hash] = $pdo->prepare($sql);
        }
        
        $this->stmt = self::$prepared[$hash];
    }
    
    public function execute(array $params = []): array 
    {
        return optimizer()->executeWithProfiling($this->sql, $params);
    }
    
    public function fetchAll(array $params = []): array 
    {
        $this->stmt->execute($params);
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function fetch(array $params = []): ?array 
    {
        $this->stmt->execute($params);
        $result = $this->stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    
    public function fetchColumn(array $params = [], int $column = 0) 
    {
        $this->stmt->execute($params);
        return $this->stmt->fetchColumn($column);
    }
}

/**
 * Preparar statement otimizada
 */
function prepare_optimized(string $sql): OptimizedStatement 
{
    global $pdo;
    return new OptimizedStatement($pdo, $sql);
}

/**
 * Middleware para análise automática de queries
 */
register_shutdown_function(function() {
    if (class_exists('QueryOptimizer')) {
        $profiler = optimizer()->profiler ?? null;
        
        if ($profiler) {
            $slowQueries = $profiler->getSlowQueries();
            
            if (!empty($slowQueries)) {
                error_log("Detected " . count($slowQueries) . " slow queries in this request");
            }
        }
    }
});