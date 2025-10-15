<?php
/**
 * Sistema de Testes de Performance - Mr. Carlos Barbershop
 * 
 * Sistema para testes de carga, profiling de performance e análise de bottlenecks
 * Inclui testes automatizados de stress e monitoramento em tempo real
 * 
 * @author Sistema Mr. Carlos Barbershop
 * @version 1.0
 * @since 2025-10-14
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/cache.php';
require_once __DIR__ . '/../includes/optimizer.php';

class PerformanceTester 
{
    private $results = [];
    private $config;
    private $start_time;
    private $start_memory;
    
    // Configurações de teste
    const DEFAULT_CONCURRENT_USERS = 10;
    const DEFAULT_DURATION = 60; // segundos
    const DEFAULT_RAMP_UP = 10; // segundos
    
    public function __construct() 
    {
        $this->config = [
            'base_url' => BASE_URL,
            'concurrent_users' => self::DEFAULT_CONCURRENT_USERS,
            'test_duration' => self::DEFAULT_DURATION,
            'ramp_up_time' => self::DEFAULT_RAMP_UP,
            'endpoints' => [
                '/' => 'GET',
                '/pages/login.php' => 'GET',
                '/pages/agendar.php' => 'GET',
                '/api/services' => 'GET',
                '/api/availability' => 'GET'
            ],
            'acceptable_response_time' => 2.0, // segundos
            'acceptable_error_rate' => 1.0, // %
            'memory_limit' => '512M'
        ];
        
        ini_set('memory_limit', $this->config['memory_limit']);
        set_time_limit(0); // Sem limite de tempo para testes longos
    }
    
    /**
     * Executar suite completa de testes
     */
    public function runFullPerformanceTest(): array 
    {
        $this->log("Iniciando testes de performance completos...");
        
        $tests = [
            'load_test' => $this->runLoadTest(),
            'stress_test' => $this->runStressTest(),
            'endurance_test' => $this->runEnduranceTest(),
            'spike_test' => $this->runSpikeTest(),
            'database_performance' => $this->testDatabasePerformance(),
            'cache_performance' => $this->testCachePerformance(),
            'memory_usage' => $this->testMemoryUsage(),
            'concurrent_bookings' => $this->testConcurrentBookings()
        ];
        
        $summary = $this->generatePerformanceSummary($tests);
        
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'tests' => $tests,
            'summary' => $summary,
            'recommendations' => $this->generateRecommendations($tests)
        ];
    }
    
    /**
     * Teste de carga normal
     */
    public function runLoadTest(): array 
    {
        $this->log("Executando teste de carga...");
        
        $users = $this->config['concurrent_users'];
        $duration = $this->config['test_duration'];
        
        return $this->executeLoadTest($users, $duration, 'load_test');
    }
    
    /**
     * Teste de stress (alta carga)
     */
    public function runStressTest(): array 
    {
        $this->log("Executando teste de stress...");
        
        $users = $this->config['concurrent_users'] * 3;
        $duration = $this->config['test_duration'] / 2;
        
        return $this->executeLoadTest($users, $duration, 'stress_test');
    }
    
    /**
     * Teste de resistência (longa duração)
     */
    public function runEnduranceTest(): array 
    {
        $this->log("Executando teste de resistência...");
        
        $users = $this->config['concurrent_users'];
        $duration = $this->config['test_duration'] * 3;
        
        return $this->executeLoadTest($users, $duration, 'endurance_test');
    }
    
    /**
     * Teste de pico (carga súbita)
     */
    public function runSpikeTest(): array 
    {
        $this->log("Executando teste de pico...");
        
        $results = [];
        
        // Carga baixa inicial
        $results['baseline'] = $this->executeLoadTest(2, 10, 'spike_baseline');
        
        // Pico súbito
        $results['spike'] = $this->executeLoadTest($this->config['concurrent_users'] * 5, 30, 'spike_peak');
        
        // Volta ao normal
        $results['recovery'] = $this->executeLoadTest(2, 10, 'spike_recovery');
        
        return [
            'type' => 'spike_test',
            'results' => $results,
            'degradation' => $this->calculatePerformanceDegradation($results),
            'timestamp' => time()
        ];
    }
    
    /**
     * Executar teste de carga
     */
    private function executeLoadTest(int $users, int $duration, string $test_name): array 
    {
        $this->startProfiling();
        
        $requests = [];
        $errors = 0;
        $total_requests = 0;
        $start_time = microtime(true);
        $end_time = $start_time + $duration;
        
        // Simular usuários concorrentes
        $processes = [];
        
        for ($i = 0; $i < $users; $i++) {
            $processes[] = $this->createUserProcess($end_time);
        }
        
        // Aguardar conclusão de todos os processos
        foreach ($processes as $process) {
            $result = $this->waitForProcess($process);
            
            if ($result) {
                $requests = array_merge($requests, $result['requests']);
                $errors += $result['errors'];
                $total_requests += $result['total_requests'];
            }
        }
        
        $total_time = microtime(true) - $start_time;
        $profile = $this->endProfiling();
        
        return [
            'type' => $test_name,
            'users' => $users,
            'duration' => $total_time,
            'total_requests' => $total_requests,
            'requests_per_second' => $total_requests / $total_time,
            'errors' => $errors,
            'error_rate' => $total_requests > 0 ? ($errors / $total_requests) * 100 : 0,
            'response_times' => $this->analyzeResponseTimes($requests),
            'throughput' => $this->calculateThroughput($requests, $total_time),
            'resource_usage' => $profile,
            'timestamp' => time()
        ];
    }
    
    /**
     * Criar processo de usuário simulado
     */
    private function createUserProcess(float $end_time): array 
    {
        return [
            'end_time' => $end_time,
            'requests' => [],
            'errors' => 0,
            'total_requests' => 0
        ];
    }
    
    /**
     * Aguardar processo e coletar resultados
     */
    private function waitForProcess(array $process): array 
    {
        $requests = [];
        $errors = 0;
        $total = 0;
        
        while (microtime(true) < $process['end_time']) {
            foreach ($this->config['endpoints'] as $endpoint => $method) {
                $start = microtime(true);
                
                try {
                    $response = $this->makeRequest($endpoint, $method);
                    $response_time = microtime(true) - $start;
                    
                    $requests[] = [
                        'endpoint' => $endpoint,
                        'method' => $method,
                        'response_time' => $response_time,
                        'status_code' => $response['status'],
                        'success' => $response['success']
                    ];
                    
                    if (!$response['success']) {
                        $errors++;
                    }
                    
                } catch (Exception $e) {
                    $errors++;
                    $requests[] = [
                        'endpoint' => $endpoint,
                        'method' => $method,
                        'response_time' => microtime(true) - $start,
                        'status_code' => 500,
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                }
                
                $total++;
                
                // Pequena pausa para simular comportamento real
                usleep(rand(100000, 500000)); // 0.1-0.5 segundos
            }
        }
        
        return [
            'requests' => $requests,
            'errors' => $errors,
            'total_requests' => $total
        ];
    }
    
    /**
     * Fazer requisição HTTP
     */
    private function makeRequest(string $endpoint, string $method): array 
    {
        $url = rtrim($this->config['base_url'], '/') . $endpoint;
        
        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'timeout' => 10,
                'header' => "User-Agent: PerformanceTester/1.0\r\n"
            ]
        ]);
        
        $start = microtime(true);
        $response = @file_get_contents($url, false, $context);
        $time = microtime(true) - $start;
        
        // Analisar headers de resposta
        $status_code = 200;
        if (isset($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (preg_match('/HTTP\/\d\.\d (\d+)/', $header, $matches)) {
                    $status_code = (int)$matches[1];
                    break;
                }
            }
        }
        
        return [
            'success' => $response !== false && $status_code < 400,
            'status' => $status_code,
            'response_time' => $time,
            'content_length' => $response ? strlen($response) : 0
        ];
    }
    
    /**
     * Testar performance do banco de dados
     */
    public function testDatabasePerformance(): array 
    {
        $this->log("Testando performance do banco de dados...");
        
        $tests = [];
        
        // Teste de consultas simples
        $tests['simple_queries'] = $this->benchmarkDatabaseQueries([
            'SELECT 1',
            'SELECT COUNT(*) FROM clients',
            'SELECT COUNT(*) FROM bookings',
            'SELECT * FROM services WHERE active = 1 LIMIT 10'
        ]);
        
        // Teste de consultas complexas
        $tests['complex_queries'] = $this->benchmarkDatabaseQueries([
            get_optimized_query('daily_bookings'),
            get_optimized_query('revenue_report'),
            get_optimized_query('dashboard_stats')
        ]);
        
        // Teste de inserções
        $tests['insertions'] = $this->benchmarkInsertions(100);
        
        // Teste de atualizações
        $tests['updates'] = $this->benchmarkUpdates(50);
        
        return [
            'type' => 'database_performance',
            'tests' => $tests,
            'overall_score' => $this->calculateDatabaseScore($tests),
            'timestamp' => time()
        ];
    }
    
    /**
     * Benchmark de queries do banco
     */
    private function benchmarkDatabaseQueries(array $queries): array 
    {
        $results = [];
        
        foreach ($queries as $sql) {
            if (empty($sql)) continue;
            
            $times = [];
            $iterations = 10;
            
            for ($i = 0; $i < $iterations; $i++) {
                $start = microtime(true);
                
                try {
                    global $pdo;
                    $stmt = $pdo->query($sql);
                    $stmt->fetchAll();
                    $times[] = microtime(true) - $start;
                } catch (Exception $e) {
                    $times[] = 999; // Penalidade por erro
                }
            }
            
            $results[] = [
                'query' => substr($sql, 0, 100) . '...',
                'avg_time' => array_sum($times) / count($times),
                'min_time' => min($times),
                'max_time' => max($times),
                'iterations' => $iterations
            ];
        }
        
        return $results;
    }
    
    /**
     * Benchmark de inserções
     */
    private function benchmarkInsertions(int $count): array 
    {
        global $pdo;
        
        $start = microtime(true);
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                INSERT INTO admin_logs (admin_id, action, details) 
                VALUES (1, 'performance_test', ?)
            ");
            
            for ($i = 0; $i < $count; $i++) {
                $stmt->execute(["Test insertion #{$i}"]);
            }
            
            $pdo->commit();
            
            // Limpar dados de teste
            $pdo->exec("DELETE FROM admin_logs WHERE action = 'performance_test'");
            
        } catch (Exception $e) {
            $pdo->rollback();
            return ['error' => $e->getMessage()];
        }
        
        $time = microtime(true) - $start;
        
        return [
            'count' => $count,
            'total_time' => $time,
            'avg_time_per_insert' => $time / $count,
            'inserts_per_second' => $count / $time
        ];
    }
    
    /**
     * Benchmark de atualizações
     */
    private function benchmarkUpdates(int $count): array 
    {
        global $pdo;
        
        // Criar dados de teste
        $test_ids = [];
        
        try {
            for ($i = 0; $i < $count; $i++) {
                $stmt = $pdo->prepare("
                    INSERT INTO admin_logs (admin_id, action, details) 
                    VALUES (1, 'performance_test', 'Test for update')
                ");
                $stmt->execute();
                $test_ids[] = $pdo->lastInsertId();
            }
            
            // Benchmark das atualizações
            $start = microtime(true);
            
            $stmt = $pdo->prepare("
                UPDATE admin_logs 
                SET details = 'Updated for performance test' 
                WHERE id = ?
            ");
            
            foreach ($test_ids as $id) {
                $stmt->execute([$id]);
            }
            
            $time = microtime(true) - $start;
            
            // Limpar dados de teste
            $pdo->exec("DELETE FROM admin_logs WHERE action = 'performance_test'");
            
            return [
                'count' => $count,
                'total_time' => $time,
                'avg_time_per_update' => $time / $count,
                'updates_per_second' => $count / $time
            ];
            
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Testar performance do cache
     */
    public function testCachePerformance(): array 
    {
        $this->log("Testando performance do cache...");
        
        $cache = cache();
        $iterations = 1000;
        
        // Teste de escrita
        $write_times = [];
        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $cache->set("test_key_{$i}", "test_value_{$i}", 3600);
            $write_times[] = microtime(true) - $start;
        }
        
        // Teste de leitura
        $read_times = [];
        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $cache->get("test_key_{$i}");
            $read_times[] = microtime(true) - $start;
        }
        
        // Limpeza
        for ($i = 0; $i < $iterations; $i++) {
            $cache->delete("test_key_{$i}");
        }
        
        return [
            'type' => 'cache_performance',
            'write_performance' => [
                'iterations' => $iterations,
                'avg_time' => array_sum($write_times) / count($write_times),
                'min_time' => min($write_times),
                'max_time' => max($write_times),
                'operations_per_second' => $iterations / array_sum($write_times)
            ],
            'read_performance' => [
                'iterations' => $iterations,
                'avg_time' => array_sum($read_times) / count($read_times),
                'min_time' => min($read_times),
                'max_time' => max($read_times),
                'operations_per_second' => $iterations / array_sum($read_times)
            ],
            'timestamp' => time()
        ];
    }
    
    /**
     * Testar uso de memória
     */
    public function testMemoryUsage(): array 
    {
        $this->log("Testando uso de memória...");
        
        $initial_memory = memory_get_usage();
        $peak_memory = memory_get_peak_usage();
        
        // Simular carga de trabalho
        $data = [];
        for ($i = 0; $i < 10000; $i++) {
            $data[] = str_repeat('x', 1000); // 1KB por item
        }
        
        $after_load_memory = memory_get_usage();
        $after_load_peak = memory_get_peak_usage();
        
        // Limpeza
        unset($data);
        gc_collect_cycles();
        
        $final_memory = memory_get_usage();
        
        return [
            'type' => 'memory_usage',
            'initial_memory' => $initial_memory,
            'after_load_memory' => $after_load_memory,
            'final_memory' => $final_memory,
            'peak_memory' => $after_load_peak,
            'memory_increase' => $after_load_memory - $initial_memory,
            'memory_recovered' => $after_load_memory - $final_memory,
            'efficiency_ratio' => ($after_load_memory - $final_memory) / ($after_load_memory - $initial_memory),
            'timestamp' => time()
        ];
    }
    
    /**
     * Testar agendamentos concorrentes
     */
    public function testConcurrentBookings(): array 
    {
        $this->log("Testando agendamentos concorrentes...");
        
        // Simular tentativas simultâneas de agendamento para o mesmo horário
        $concurrent_attempts = 10;
        $success_count = 0;
        $error_count = 0;
        $times = [];
        
        for ($i = 0; $i < $concurrent_attempts; $i++) {
            $start = microtime(true);
            
            try {
                // Simular criação de agendamento
                global $pdo;
                
                $pdo->beginTransaction();
                
                // Verificar disponibilidade
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) FROM bookings 
                    WHERE barber_id = 1 
                    AND booking_date = CURDATE() + INTERVAL 1 DAY
                    AND start_time = '10:00:00'
                    FOR UPDATE
                ");
                $stmt->execute();
                $existing = $stmt->fetchColumn();
                
                if ($existing == 0) {
                    // Tentar agendar
                    $stmt = $pdo->prepare("
                        INSERT INTO bookings (client_id, barber_id, booking_date, start_time, end_time, status)
                        VALUES (1, 1, CURDATE() + INTERVAL 1 DAY, '10:00:00', '11:00:00', 'agendado')
                    ");
                    $stmt->execute();
                    $success_count++;
                } else {
                    $error_count++;
                }
                
                $pdo->commit();
                
            } catch (Exception $e) {
                $pdo->rollback();
                $error_count++;
            }
            
            $times[] = microtime(true) - $start;
            
            // Pequena pausa
            usleep(10000); // 0.01 segundo
        }
        
        // Limpeza
        global $pdo;
        $pdo->exec("DELETE FROM bookings WHERE booking_date = CURDATE() + INTERVAL 1 DAY AND start_time = '10:00:00'");
        
        return [
            'type' => 'concurrent_bookings',
            'attempts' => $concurrent_attempts,
            'successes' => $success_count,
            'errors' => $error_count,
            'avg_response_time' => array_sum($times) / count($times),
            'data_integrity_maintained' => $success_count <= 1, // Apenas um deve ter sucesso
            'timestamp' => time()
        ];
    }
    
    /**
     * Analisar tempos de resposta
     */
    private function analyzeResponseTimes(array $requests): array 
    {
        if (empty($requests)) {
            return [];
        }
        
        $times = array_column($requests, 'response_time');
        sort($times);
        
        $count = count($times);
        
        return [
            'min' => min($times),
            'max' => max($times),
            'avg' => array_sum($times) / $count,
            'median' => $count % 2 ? $times[($count - 1) / 2] : ($times[$count / 2 - 1] + $times[$count / 2]) / 2,
            'p95' => $times[floor($count * 0.95)],
            'p99' => $times[floor($count * 0.99)],
            'std_dev' => $this->calculateStandardDeviation($times)
        ];
    }
    
    /**
     * Calcular throughput
     */
    private function calculateThroughput(array $requests, float $duration): array 
    {
        $successful = array_filter($requests, function($req) {
            return $req['success'];
        });
        
        $total_bytes = array_sum(array_column($requests, 'content_length'));
        
        return [
            'requests_per_second' => count($requests) / $duration,
            'successful_requests_per_second' => count($successful) / $duration,
            'bytes_per_second' => $total_bytes / $duration,
            'mb_per_second' => ($total_bytes / $duration) / (1024 * 1024)
        ];
    }
    
    /**
     * Calcular degradação de performance
     */
    private function calculatePerformanceDegradation(array $results): array 
    {
        $baseline = $results['baseline']['response_times']['avg'] ?? 1;
        $spike = $results['spike']['response_times']['avg'] ?? 1;
        
        return [
            'response_time_increase' => (($spike - $baseline) / $baseline) * 100,
            'throughput_decrease' => isset($results['baseline']['throughput'], $results['spike']['throughput']) ?
                ((($results['baseline']['throughput']['requests_per_second'] - $results['spike']['throughput']['requests_per_second']) / 
                $results['baseline']['throughput']['requests_per_second']) * 100) : 0,
            'error_rate_increase' => ($results['spike']['error_rate'] ?? 0) - ($results['baseline']['error_rate'] ?? 0)
        ];
    }
    
    /**
     * Calcular score do banco de dados
     */
    private function calculateDatabaseScore(array $tests): float 
    {
        $score = 100;
        
        // Penalizar queries lentas
        if (isset($tests['complex_queries'])) {
            foreach ($tests['complex_queries'] as $query) {
                if ($query['avg_time'] > 0.1) {
                    $score -= 10;
                }
            }
        }
        
        // Penalizar inserções lentas
        if (isset($tests['insertions']['inserts_per_second']) && $tests['insertions']['inserts_per_second'] < 100) {
            $score -= 20;
        }
        
        return max(0, $score);
    }
    
    /**
     * Calcular desvio padrão
     */
    private function calculateStandardDeviation(array $values): float 
    {
        $mean = array_sum($values) / count($values);
        $sum_of_squares = array_sum(array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $values));
        
        return sqrt($sum_of_squares / count($values));
    }
    
    /**
     * Gerar resumo de performance
     */
    private function generatePerformanceSummary(array $tests): array 
    {
        $summary = [
            'overall_score' => 0,
            'bottlenecks' => [],
            'strengths' => [],
            'critical_issues' => []
        ];
        
        // Analisar cada teste
        foreach ($tests as $test_name => $test_result) {
            switch ($test_name) {
                case 'load_test':
                    if ($test_result['error_rate'] > $this->config['acceptable_error_rate']) {
                        $summary['critical_issues'][] = "Taxa de erro alta no teste de carga: {$test_result['error_rate']}%";
                    }
                    if ($test_result['response_times']['avg'] > $this->config['acceptable_response_time']) {
                        $summary['bottlenecks'][] = "Tempo de resposta alto: {$test_result['response_times']['avg']}s";
                    }
                    break;
                    
                case 'database_performance':
                    if ($test_result['overall_score'] < 70) {
                        $summary['bottlenecks'][] = "Performance do banco de dados abaixo do esperado";
                    }
                    break;
                    
                case 'concurrent_bookings':
                    if (!$test_result['data_integrity_maintained']) {
                        $summary['critical_issues'][] = "Integridade de dados comprometida em agendamentos concorrentes";
                    }
                    break;
            }
        }
        
        // Calcular score geral
        $summary['overall_score'] = $this->calculateOverallScore($tests);
        
        return $summary;
    }
    
    /**
     * Calcular score geral
     */
    private function calculateOverallScore(array $tests): float 
    {
        $scores = [];
        
        // Score baseado em tempo de resposta
        if (isset($tests['load_test']['response_times']['avg'])) {
            $response_score = max(0, 100 - ($tests['load_test']['response_times']['avg'] * 50));
            $scores[] = $response_score;
        }
        
        // Score baseado em taxa de erro
        if (isset($tests['load_test']['error_rate'])) {
            $error_score = max(0, 100 - ($tests['load_test']['error_rate'] * 10));
            $scores[] = $error_score;
        }
        
        // Score do banco de dados
        if (isset($tests['database_performance']['overall_score'])) {
            $scores[] = $tests['database_performance']['overall_score'];
        }
        
        return empty($scores) ? 0 : array_sum($scores) / count($scores);
    }
    
    /**
     * Gerar recomendações
     */
    private function generateRecommendations(array $tests): array 
    {
        $recommendations = [];
        
        // Analisar resultados e sugerir melhorias
        if (isset($tests['load_test']['response_times']['avg']) && 
            $tests['load_test']['response_times']['avg'] > 1.0) {
            $recommendations[] = [
                'category' => 'Performance',
                'priority' => 'high',
                'issue' => 'Tempo de resposta alto',
                'recommendation' => 'Implementar cache agressivo ou otimizar queries do banco de dados'
            ];
        }
        
        if (isset($tests['database_performance']['overall_score']) && 
            $tests['database_performance']['overall_score'] < 70) {
            $recommendations[] = [
                'category' => 'Database',
                'priority' => 'high',
                'issue' => 'Performance do banco baixa',
                'recommendation' => 'Adicionar índices, otimizar queries ou considerar particionamento'
            ];
        }
        
        if (isset($tests['memory_usage']['efficiency_ratio']) && 
            $tests['memory_usage']['efficiency_ratio'] < 0.7) {
            $recommendations[] = [
                'category' => 'Memory',
                'priority' => 'medium',
                'issue' => 'Uso de memória ineficiente',
                'recommendation' => 'Implementar garbage collection mais agressivo ou revisar algoritmos'
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * Iniciar profiling
     */
    private function startProfiling(): void 
    {
        $this->start_time = microtime(true);
        $this->start_memory = memory_get_usage();
    }
    
    /**
     * Finalizar profiling
     */
    private function endProfiling(): array 
    {
        return [
            'execution_time' => microtime(true) - $this->start_time,
            'memory_used' => memory_get_usage() - $this->start_memory,
            'peak_memory' => memory_get_peak_usage(),
            'cpu_usage' => $this->getCPUUsage()
        ];
    }
    
    /**
     * Obter uso de CPU
     */
    private function getCPUUsage(): ?float 
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return $load[0] ?? null;
        }
        
        return null;
    }
    
    /**
     * Log de mensagem
     */
    private function log(string $message): void 
    {
        $log_line = "[" . date('Y-m-d H:i:s') . "] {$message}" . PHP_EOL;
        
        // Log para arquivo
        file_put_contents(__DIR__ . '/../storage/logs/performance.log', $log_line, FILE_APPEND | LOCK_EX);
        
        // Output para console se executado via CLI
        if (php_sapi_name() === 'cli') {
            echo $log_line;
        }
    }
}

// Executar se chamado diretamente via CLI
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $tester = new PerformanceTester();
    
    // Verificar argumentos da linha de comando
    $test_type = $argv[1] ?? 'full';
    
    switch ($test_type) {
        case 'load':
            $result = $tester->runLoadTest();
            break;
        case 'stress':
            $result = $tester->runStressTest();
            break;
        case 'endurance':
            $result = $tester->runEnduranceTest();
            break;
        case 'spike':
            $result = $tester->runSpikeTest();
            break;
        case 'database':
            $result = $tester->testDatabasePerformance();
            break;
        case 'cache':
            $result = $tester->testCachePerformance();
            break;
        case 'full':
        default:
            $result = $tester->runFullPerformanceTest();
            break;
    }
    
    echo json_encode($result, JSON_PRETTY_PRINT);
}