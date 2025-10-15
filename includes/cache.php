<?php
/**
 * Sistema de Cache - Mr. Carlos Barbershop
 * 
 * Sistema flexível de cache suportando múltiplos drivers:
 * - File Cache (padrão)
 * - Redis (recomendado para produção)
 * - APCu (cache em memória)
 * - Database Cache (fallback)
 * 
 * @author Sistema Mr. Carlos Barbershop
 * @version 1.0
 * @since 2025-10-14
 */

if (!defined('BASE_PATH')) {
    die('Acesso direto não permitido');
}

class CacheManager 
{
    private $driver;
    private $config;
    private static $instance = null;
    
    // Constantes de configuração
    const DEFAULT_TTL = 3600; // 1 hora
    const MAX_KEY_LENGTH = 250;
    const CACHE_VERSION = '1.0';
    
    // TTLs específicos por tipo de conteúdo
    const TTL_SERVICES = 86400;     // 24 horas - serviços mudam pouco
    const TTL_BARBERS = 3600;       // 1 hora - disponibilidade muda
    const TTL_AVAILABILITY = 300;   // 5 minutos - precisa ser atual
    const TTL_STATS = 1800;         // 30 minutos - relatórios
    const TTL_CONFIG = 43200;       // 12 horas - configurações
    const TTL_SESSION = 7200;       // 2 horas - dados de sessão
    
    private function __construct() 
    {
        $this->config = [
            'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
            'ttl' => self::DEFAULT_TTL,
            'prefix' => 'mrcb_',
            'file_path' => BASE_PATH . '/storage/cache/',
            'redis_host' => $_ENV['REDIS_HOST'] ?? 'localhost',
            'redis_port' => $_ENV['REDIS_PORT'] ?? 6379,
            'redis_password' => $_ENV['REDIS_PASSWORD'] ?? null,
            'compression' => true,
            'serialization' => 'json'
        ];
        
        $this->initializeDriver();
    }
    
    public static function getInstance(): CacheManager 
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function initializeDriver(): void 
    {
        switch ($this->config['driver']) {
            case 'redis':
                $this->driver = new RedisCacheDriver($this->config);
                break;
            case 'apcu':
                $this->driver = new APCuCacheDriver($this->config);
                break;
            case 'database':
                $this->driver = new DatabaseCacheDriver($this->config);
                break;
            case 'file':
            default:
                $this->driver = new FileCacheDriver($this->config);
                break;
        }
    }
    
    /**
     * Obter item do cache
     */
    public function get(string $key, $default = null) 
    {
        $key = $this->normalizeKey($key);
        
        try {
            $data = $this->driver->get($key);
            
            if ($data === null) {
                return $default;
            }
            
            // Verificar expiração
            if (isset($data['expires']) && $data['expires'] < time()) {
                $this->delete($key);
                return $default;
            }
            
            return $data['value'] ?? $default;
            
        } catch (Exception $e) {
            error_log("Cache get error for key '$key': " . $e->getMessage());
            return $default;
        }
    }
    
    /**
     * Armazenar item no cache
     */
    public function set(string $key, $value, ?int $ttl = null): bool 
    {
        $key = $this->normalizeKey($key);
        $ttl = $ttl ?? $this->config['ttl'];
        
        $data = [
            'value' => $value,
            'created' => time(),
            'expires' => time() + $ttl,
            'version' => self::CACHE_VERSION
        ];
        
        try {
            return $this->driver->set($key, $data, $ttl);
        } catch (Exception $e) {
            error_log("Cache set error for key '$key': " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Deletar item do cache
     */
    public function delete(string $key): bool 
    {
        $key = $this->normalizeKey($key);
        
        try {
            return $this->driver->delete($key);
        } catch (Exception $e) {
            error_log("Cache delete error for key '$key': " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar se item existe no cache
     */
    public function exists(string $key): bool 
    {
        return $this->get($key) !== null;
    }
    
    /**
     * Limpar todo o cache
     */
    public function clear(): bool 
    {
        try {
            return $this->driver->clear();
        } catch (Exception $e) {
            error_log("Cache clear error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obter múltiplos itens
     */
    public function getMultiple(array $keys, $default = null): array 
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }
    
    /**
     * Armazenar múltiplos itens
     */
    public function setMultiple(array $values, ?int $ttl = null): bool 
    {
        $success = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }
    
    /**
     * Incrementar valor numérico
     */
    public function increment(string $key, int $value = 1): int 
    {
        $current = (int) $this->get($key, 0);
        $new = $current + $value;
        $this->set($key, $new);
        return $new;
    }
    
    /**
     * Decrementar valor numérico
     */
    public function decrement(string $key, int $value = 1): int 
    {
        return $this->increment($key, -$value);
    }
    
    /**
     * Cache com callback - busca no cache ou executa função
     */
    public function remember(string $key, callable $callback, ?int $ttl = null) 
    {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }
    
    /**
     * Invalidar cache por tags/padrões
     */
    public function invalidateByPattern(string $pattern): bool 
    {
        return $this->driver->invalidateByPattern($this->config['prefix'] . $pattern);
    }
    
    /**
     * Obter estatísticas do cache
     */
    public function getStats(): array 
    {
        $stats = $this->driver->getStats();
        $stats['driver'] = $this->config['driver'];
        $stats['prefix'] = $this->config['prefix'];
        return $stats;
    }
    
    /**
     * Normalizar chave do cache
     */
    private function normalizeKey(string $key): string 
    {
        // Adicionar prefixo
        $key = $this->config['prefix'] . $key;
        
        // Substituir caracteres inválidos
        $key = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $key);
        
        // Limitar tamanho
        if (strlen($key) > self::MAX_KEY_LENGTH) {
            $key = substr($key, 0, self::MAX_KEY_LENGTH - 32) . md5($key);
        }
        
        return $key;
    }
}

/**
 * Driver de Cache em Arquivos
 */
class FileCacheDriver 
{
    private $path;
    private $config;
    
    public function __construct(array $config) 
    {
        $this->config = $config;
        $this->path = $config['file_path'];
        
        // Criar diretório se não existe
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
        
        // Criar .htaccess para segurança
        $htaccess = $this->path . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Deny from all\n");
        }
    }
    
    public function get(string $key) 
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return null;
        }
        
        $content = file_get_contents($file);
        
        if ($this->config['compression']) {
            $content = gzuncompress($content);
        }
        
        return json_decode($content, true);
    }
    
    public function set(string $key, $data, int $ttl): bool 
    {
        $file = $this->getFilePath($key);
        $dir = dirname($file);
        
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $content = json_encode($data);
        
        if ($this->config['compression']) {
            $content = gzcompress($content);
        }
        
        return file_put_contents($file, $content, LOCK_EX) !== false;
    }
    
    public function delete(string $key): bool 
    {
        $file = $this->getFilePath($key);
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return true;
    }
    
    public function clear(): bool 
    {
        return $this->deleteDirectory($this->path . '/data/');
    }
    
    public function invalidateByPattern(string $pattern): bool 
    {
        $files = glob($this->path . '/data/' . str_replace('*', '**', $pattern) . '.cache');
        
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }
    
    public function getStats(): array 
    {
        $files = glob($this->path . '/data/**/*.cache');
        $totalSize = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
        }
        
        return [
            'type' => 'file',
            'total_files' => count($files),
            'total_size' => $totalSize,
            'cache_dir' => $this->path
        ];
    }
    
    private function getFilePath(string $key): string 
    {
        // Criar estrutura de diretórios baseada na chave
        $hash = md5($key);
        $subdir = substr($hash, 0, 2) . '/' . substr($hash, 2, 2);
        
        return $this->path . '/data/' . $subdir . '/' . $key . '.cache';
    }
    
    private function deleteDirectory(string $dir): bool 
    {
        if (!is_dir($dir)) {
            return true;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        
        return rmdir($dir);
    }
}

/**
 * Driver de Cache Redis
 */
class RedisCacheDriver 
{
    private $redis;
    private $config;
    
    public function __construct(array $config) 
    {
        $this->config = $config;
        
        if (!extension_loaded('redis')) {
            throw new Exception('Redis extension not loaded');
        }
        
        $this->redis = new Redis();
        $this->redis->connect($config['redis_host'], $config['redis_port']);
        
        if ($config['redis_password']) {
            $this->redis->auth($config['redis_password']);
        }
    }
    
    public function get(string $key) 
    {
        $data = $this->redis->get($key);
        
        if ($data === false) {
            return null;
        }
        
        return json_decode($data, true);
    }
    
    public function set(string $key, $data, int $ttl): bool 
    {
        $content = json_encode($data);
        return $this->redis->setex($key, $ttl, $content);
    }
    
    public function delete(string $key): bool 
    {
        return $this->redis->del($key) > 0;
    }
    
    public function clear(): bool 
    {
        return $this->redis->flushDB();
    }
    
    public function invalidateByPattern(string $pattern): bool 
    {
        $keys = $this->redis->keys($pattern);
        
        if (!empty($keys)) {
            return $this->redis->del($keys) > 0;
        }
        
        return true;
    }
    
    public function getStats(): array 
    {
        $info = $this->redis->info();
        
        return [
            'type' => 'redis',
            'used_memory' => $info['used_memory'] ?? 0,
            'total_keys' => $info['db0']['keys'] ?? 0,
            'hits' => $info['keyspace_hits'] ?? 0,
            'misses' => $info['keyspace_misses'] ?? 0
        ];
    }
}

/**
 * Driver de Cache APCu
 */
class APCuCacheDriver 
{
    private $config;
    
    public function __construct(array $config) 
    {
        $this->config = $config;
        
        if (!extension_loaded('apcu')) {
            throw new Exception('APCu extension not loaded');
        }
    }
    
    public function get(string $key) 
    {
        $data = apcu_fetch($key, $success);
        
        if (!$success) {
            return null;
        }
        
        return json_decode($data, true);
    }
    
    public function set(string $key, $data, int $ttl): bool 
    {
        $content = json_encode($data);
        return apcu_store($key, $content, $ttl);
    }
    
    public function delete(string $key): bool 
    {
        return apcu_delete($key);
    }
    
    public function clear(): bool 
    {
        return apcu_clear_cache();
    }
    
    public function invalidateByPattern(string $pattern): bool 
    {
        $iterator = new APCUIterator('/^' . preg_quote($pattern, '/') . '/');
        
        foreach ($iterator as $key => $value) {
            apcu_delete($key);
        }
        
        return true;
    }
    
    public function getStats(): array 
    {
        $info = apcu_cache_info();
        
        return [
            'type' => 'apcu',
            'memory_size' => $info['memory_type'] ?? 0,
            'num_entries' => $info['num_entries'] ?? 0,
            'hits' => $info['num_hits'] ?? 0,
            'misses' => $info['num_misses'] ?? 0
        ];
    }
}

/**
 * Driver de Cache em Banco de Dados
 */
class DatabaseCacheDriver 
{
    private $pdo;
    private $config;
    
    public function __construct(array $config) 
    {
        $this->config = $config;
        global $pdo;
        $this->pdo = $pdo;
        
        $this->createTableIfNotExists();
    }
    
    public function get(string $key) 
    {
        $stmt = $this->pdo->prepare("
            SELECT data FROM cache_entries 
            WHERE cache_key = ? AND expires_at > NOW()
        ");
        
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        
        if ($result === false) {
            return null;
        }
        
        return json_decode($result, true);
    }
    
    public function set(string $key, $data, int $ttl): bool 
    {
        $expires = date('Y-m-d H:i:s', time() + $ttl);
        $content = json_encode($data);
        
        $stmt = $this->pdo->prepare("
            INSERT INTO cache_entries (cache_key, data, expires_at) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            data = VALUES(data), 
            expires_at = VALUES(expires_at),
            updated_at = NOW()
        ");
        
        return $stmt->execute([$key, $content, $expires]);
    }
    
    public function delete(string $key): bool 
    {
        $stmt = $this->pdo->prepare("DELETE FROM cache_entries WHERE cache_key = ?");
        return $stmt->execute([$key]);
    }
    
    public function clear(): bool 
    {
        $stmt = $this->pdo->prepare("DELETE FROM cache_entries");
        return $stmt->execute();
    }
    
    public function invalidateByPattern(string $pattern): bool 
    {
        $stmt = $this->pdo->prepare("DELETE FROM cache_entries WHERE cache_key LIKE ?");
        return $stmt->execute([$pattern]);
    }
    
    public function getStats(): array 
    {
        $stmt = $this->pdo->query("
            SELECT 
                COUNT(*) as total_entries,
                SUM(LENGTH(data)) as total_size,
                COUNT(CASE WHEN expires_at > NOW() THEN 1 END) as active_entries
            FROM cache_entries
        ");
        
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['type'] = 'database';
        
        return $stats;
    }
    
    private function createTableIfNotExists(): void 
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS cache_entries (
                cache_key VARCHAR(255) PRIMARY KEY,
                data LONGTEXT NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_expires (expires_at)
            ) ENGINE=InnoDB
        ");
    }
}

/**
 * Funções auxiliares de cache
 */
function cache(): CacheManager 
{
    return CacheManager::getInstance();
}

function cache_remember(string $key, callable $callback, ?int $ttl = null) 
{
    return cache()->remember($key, $callback, $ttl);
}

function cache_services(): array 
{
    return cache_remember('services.active', function() {
        global $pdo;
        $stmt = $pdo->query("SELECT * FROM services WHERE active = 1 ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }, CacheManager::TTL_SERVICES);
}

function cache_barbers(): array 
{
    return cache_remember('barbers.active', function() {
        global $pdo;
        $stmt = $pdo->query("SELECT * FROM barbers WHERE active = 1 ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }, CacheManager::TTL_BARBERS);
}

function cache_barber_availability(int $barberId, string $date): array 
{
    $key = "availability.barber_{$barberId}.{$date}";
    
    return cache_remember($key, function() use ($barberId, $date) {
        // Lógica de cálculo de disponibilidade
        require_once BASE_PATH . '/api/get_availability.php';
        return calculateAvailability($barberId, $date);
    }, CacheManager::TTL_AVAILABILITY);
}

function cache_invalidate_barber(int $barberId): void 
{
    cache()->invalidateByPattern("barber_{$barberId}.*");
    cache()->invalidateByPattern("availability.barber_{$barberId}.*");
    cache()->delete('barbers.active');
}

function cache_invalidate_services(): void 
{
    cache()->invalidateByPattern('services.*');
}

/**
 * Middleware de cache para páginas
 */
function cache_page_start(string $key, int $ttl = 3600): bool 
{
    $content = cache()->get("page.{$key}");
    
    if ($content !== null) {
        echo $content;
        return true;
    }
    
    ob_start();
    return false;
}

function cache_page_end(string $key, int $ttl = 3600): void 
{
    $content = ob_get_contents();
    ob_end_flush();
    
    cache()->set("page.{$key}", $content, $ttl);
}

// Limpar cache expirado automaticamente
register_shutdown_function(function() {
    // Executar limpeza apenas 1% das vezes para não impactar performance
    if (rand(1, 100) === 1) {
        cache()->invalidateByPattern('expired.*');
    }
});