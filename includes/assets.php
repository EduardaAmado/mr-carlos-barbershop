<?php
/**
 * Sistema de Otimização de Assets - Mr. Carlos Barbershop
 * 
 * Sistema para minificação, compressão e otimização de CSS, JS e imagens
 * Inclui caching de assets, CDN support e otimização automática
 * 
 * @author Sistema Mr. Carlos Barbershop
 * @version 1.0
 * @since 2025-10-14
 */

if (!defined('BASE_PATH')) {
    die('Acesso direto não permitido');
}

class AssetOptimizer 
{
    private $config;
    private $cache;
    private static $instance = null;
    
    // Configurações
    const CACHE_VERSION = '1.0';
    const ASSET_CACHE_TTL = 86400; // 24 horas
    const ENABLE_MINIFICATION = true;
    const ENABLE_COMPRESSION = true;
    const ENABLE_CDN = false;
    
    private function __construct() 
    {
        $this->config = [
            'assets_path' => BASE_PATH . '/assets/',
            'cache_path' => BASE_PATH . '/storage/assets/',
            'public_path' => '/assets/',
            'cdn_url' => $_ENV['CDN_URL'] ?? null,
            'enable_minification' => self::ENABLE_MINIFICATION,
            'enable_compression' => self::ENABLE_COMPRESSION,
            'enable_gzip' => true,
            'enable_brotli' => false,
            'image_quality' => 85,
            'css_minify' => true,
            'js_minify' => true,
            'version' => self::CACHE_VERSION
        ];
        
        $this->cache = cache();
        $this->initializeDirectories();
    }
    
    public static function getInstance(): AssetOptimizer 
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Inicializar diretórios necessários
     */
    private function initializeDirectories(): void 
    {
        $dirs = [
            $this->config['cache_path'],
            $this->config['cache_path'] . 'css/',
            $this->config['cache_path'] . 'js/',
            $this->config['cache_path'] . 'images/',
            $this->config['cache_path'] . 'fonts/'
        ];
        
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    /**
     * Processar e otimizar arquivo CSS
     */
    public function processCSS(string $filename): string 
    {
        $sourcePath = $this->config['assets_path'] . 'css/' . $filename;
        $cacheKey = 'css_' . md5($filename . filemtime($sourcePath));
        
        // Verificar cache
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        if (!file_exists($sourcePath)) {
            throw new Exception("CSS file not found: {$filename}");
        }
        
        $content = file_get_contents($sourcePath);
        
        // Processar imports
        $content = $this->processImports($content, dirname($sourcePath));
        
        // Minificar se habilitado
        if ($this->config['css_minify']) {
            $content = $this->minifyCSS($content);
        }
        
        // Processar URLs de assets
        $content = $this->processAssetUrls($content);
        
        // Gerar arquivo otimizado
        $optimizedPath = $this->config['cache_path'] . 'css/' . $filename;
        file_put_contents($optimizedPath, $content);
        
        // Gerar versões comprimidas
        if ($this->config['enable_compression']) {
            $this->createCompressedVersions($optimizedPath, $content);
        }
        
        $url = $this->getAssetUrl('css/' . $filename);
        
        // Cachear resultado
        $this->cache->set($cacheKey, $url, self::ASSET_CACHE_TTL);
        
        return $url;
    }
    
    /**
     * Processar e otimizar arquivo JavaScript
     */
    public function processJS(string $filename): string 
    {
        $sourcePath = $this->config['assets_path'] . 'js/' . $filename;
        $cacheKey = 'js_' . md5($filename . filemtime($sourcePath));
        
        // Verificar cache
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        if (!file_exists($sourcePath)) {
            throw new Exception("JS file not found: {$filename}");
        }
        
        $content = file_get_contents($sourcePath);
        
        // Minificar se habilitado
        if ($this->config['js_minify']) {
            $content = $this->minifyJS($content);
        }
        
        // Gerar arquivo otimizado
        $optimizedPath = $this->config['cache_path'] . 'js/' . $filename;
        file_put_contents($optimizedPath, $content);
        
        // Gerar versões comprimidas
        if ($this->config['enable_compression']) {
            $this->createCompressedVersions($optimizedPath, $content);
        }
        
        $url = $this->getAssetUrl('js/' . $filename);
        
        // Cachear resultado
        $this->cache->set($cacheKey, $url, self::ASSET_CACHE_TTL);
        
        return $url;
    }
    
    /**
     * Processar e otimizar imagem
     */
    public function processImage(string $filename): string 
    {
        $sourcePath = $this->config['assets_path'] . 'images/' . $filename;
        $cacheKey = 'img_' . md5($filename . filemtime($sourcePath));
        
        // Verificar cache
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        if (!file_exists($sourcePath)) {
            throw new Exception("Image file not found: {$filename}");
        }
        
        $optimizedPath = $this->config['cache_path'] . 'images/' . $filename;
        
        // Otimizar imagem
        $this->optimizeImage($sourcePath, $optimizedPath);
        
        // Gerar formatos modernos
        $this->generateModernFormats($optimizedPath);
        
        $url = $this->getAssetUrl('images/' . $filename);
        
        // Cachear resultado
        $this->cache->set($cacheKey, $url, self::ASSET_CACHE_TTL);
        
        return $url;
    }
    
    /**
     * Combinar múltiplos arquivos CSS
     */
    public function combineCSS(array $files, string $outputName): string 
    {
        $cacheKey = 'css_combined_' . md5(implode('|', $files) . $outputName);
        
        // Verificar cache
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        $combined = '';
        
        foreach ($files as $file) {
            $path = $this->config['assets_path'] . 'css/' . $file;
            if (file_exists($path)) {
                $content = file_get_contents($path);
                $content = $this->processImports($content, dirname($path));
                $combined .= "\n/* {$file} */\n" . $content . "\n";
            }
        }
        
        // Minificar combinado
        if ($this->config['css_minify']) {
            $combined = $this->minifyCSS($combined);
        }
        
        // Processar URLs de assets
        $combined = $this->processAssetUrls($combined);
        
        // Salvar arquivo combinado
        $outputPath = $this->config['cache_path'] . 'css/' . $outputName;
        file_put_contents($outputPath, $combined);
        
        // Gerar versões comprimidas
        if ($this->config['enable_compression']) {
            $this->createCompressedVersions($outputPath, $combined);
        }
        
        $url = $this->getAssetUrl('css/' . $outputName);
        
        // Cachear resultado
        $this->cache->set($cacheKey, $url, self::ASSET_CACHE_TTL);
        
        return $url;
    }
    
    /**
     * Combinar múltiplos arquivos JavaScript
     */
    public function combineJS(array $files, string $outputName): string 
    {
        $cacheKey = 'js_combined_' . md5(implode('|', $files) . $outputName);
        
        // Verificar cache
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        $combined = '';
        
        foreach ($files as $file) {
            $path = $this->config['assets_path'] . 'js/' . $file;
            if (file_exists($path)) {
                $content = file_get_contents($path);
                $combined .= "\n/* {$file} */\n" . $content . "\n";
            }
        }
        
        // Minificar combinado
        if ($this->config['js_minify']) {
            $combined = $this->minifyJS($combined);
        }
        
        // Salvar arquivo combinado
        $outputPath = $this->config['cache_path'] . 'js/' . $outputName;
        file_put_contents($outputPath, $combined);
        
        // Gerar versões comprimidas
        if ($this->config['enable_compression']) {
            $this->createCompressedVersions($outputPath, $combined);
        }
        
        $url = $this->getAssetUrl('js/' . $outputName);
        
        // Cachear resultado
        $this->cache->set($cacheKey, $url, self::ASSET_CACHE_TTL);
        
        return $url;
    }
    
    /**
     * Minificar CSS
     */
    private function minifyCSS(string $content): string 
    {
        // Remover comentários
        $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
        
        // Remover espaços desnecessários
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Remover espaços em volta de símbolos
        $content = preg_replace('/\s*([{}:;,>+~])\s*/', '$1', $content);
        
        // Remover ponto e vírgula desnecessário
        $content = preg_replace('/;}/', '}', $content);
        
        // Remover zero à esquerda
        $content = preg_replace('/(:|\s)0+\.(\d+)/', '$1.$2', $content);
        
        return trim($content);
    }
    
    /**
     * Minificar JavaScript
     */
    private function minifyJS(string $content): string 
    {
        // Remover comentários de linha única
        $content = preg_replace('#^\s*//.+$#m', '', $content);
        
        // Remover comentários multilinhas
        $content = preg_replace('#/\*.*?\*/#s', '', $content);
        
        // Remover espaços e quebras de linha desnecessárias
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Remover espaços em volta de operadores
        $content = preg_replace('/\s*([=+\-*\/{}();,])\s*/', '$1', $content);
        
        return trim($content);
    }
    
    /**
     * Processar imports CSS
     */
    private function processImports(string $content, string $basePath): string 
    {
        return preg_replace_callback(
            '/@import\s+["\']([^"\']+)["\'];?/',
            function($matches) use ($basePath) {
                $importFile = $basePath . '/' . $matches[1];
                if (file_exists($importFile)) {
                    return file_get_contents($importFile);
                }
                return $matches[0];
            },
            $content
        );
    }
    
    /**
     * Processar URLs de assets no CSS
     */
    private function processAssetUrls(string $content): string 
    {
        return preg_replace_callback(
            '/url\(["\']?([^)]+?)["\']?\)/',
            function($matches) {
                $url = $matches[1];
                
                // Se já é uma URL completa, não processar
                if (preg_match('/^(https?:)?\/\//', $url)) {
                    return $matches[0];
                }
                
                // Converter para URL otimizada
                $optimizedUrl = $this->getAssetUrl($url);
                return "url('{$optimizedUrl}')";
            },
            $content
        );
    }
    
    /**
     * Otimizar imagem
     */
    private function optimizeImage(string $sourcePath, string $outputPath): void 
    {
        $info = getimagesize($sourcePath);
        
        if (!$info) {
            copy($sourcePath, $outputPath);
            return;
        }
        
        $type = $info[2];
        $quality = $this->config['image_quality'];
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($sourcePath);
                imagejpeg($image, $outputPath, $quality);
                break;
                
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($sourcePath);
                // PNG: comprimir apenas se for maior que original
                imagepng($image, $outputPath, 9);
                if (filesize($outputPath) > filesize($sourcePath)) {
                    copy($sourcePath, $outputPath);
                }
                break;
                
            case IMAGETYPE_GIF:
                copy($sourcePath, $outputPath);
                break;
                
            case IMAGETYPE_WEBP:
                $image = imagecreatefromwebp($sourcePath);
                imagewebp($image, $outputPath, $quality);
                break;
                
            default:
                copy($sourcePath, $outputPath);
                break;
        }
        
        if (isset($image)) {
            imagedestroy($image);
        }
    }
    
    /**
     * Gerar formatos modernos de imagem
     */
    private function generateModernFormats(string $imagePath): void 
    {
        $info = getimagesize($imagePath);
        
        if (!$info || !function_exists('imagewebp')) {
            return;
        }
        
        $type = $info[2];
        $image = null;
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($imagePath);
                break;
        }
        
        if ($image) {
            $webpPath = preg_replace('/\.(jpe?g|png)$/i', '.webp', $imagePath);
            imagewebp($image, $webpPath, $this->config['image_quality']);
            imagedestroy($image);
        }
    }
    
    /**
     * Criar versões comprimidas (gzip, brotli)
     */
    private function createCompressedVersions(string $filePath, string $content): void 
    {
        // Gzip
        if ($this->config['enable_gzip']) {
            $gzipPath = $filePath . '.gz';
            file_put_contents($gzipPath, gzencode($content, 9));
        }
        
        // Brotli (se disponível)
        if ($this->config['enable_brotli'] && function_exists('brotli_compress')) {
            $brotliPath = $filePath . '.br';
            file_put_contents($brotliPath, brotli_compress($content, 11));
        }
    }
    
    /**
     * Obter URL do asset
     */
    private function getAssetUrl(string $path): string 
    {
        $path = ltrim($path, '/');
        
        // Se CDN estiver habilitado
        if ($this->config['cdn_url']) {
            return rtrim($this->config['cdn_url'], '/') . '/storage/assets/' . $path;
        }
        
        // URL local com versioning
        $version = $this->config['version'];
        return "/storage/assets/{$path}?v={$version}";
    }
    
    /**
     * Limpar cache de assets
     */
    public function clearCache(): bool 
    {
        $this->cache->invalidateByPattern('css_*');
        $this->cache->invalidateByPattern('js_*');
        $this->cache->invalidateByPattern('img_*');
        
        // Limpar arquivos físicos
        $this->clearDirectory($this->config['cache_path']);
        
        return true;
    }
    
    /**
     * Limpar diretório recursivamente
     */
    private function clearDirectory(string $dir): void 
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->clearDirectory($path) : unlink($path);
        }
    }
    
    /**
     * Obter estatísticas de otimização
     */
    public function getStats(): array 
    {
        $stats = [
            'cached_files' => 0,
            'total_size' => 0,
            'original_size' => 0,
            'compression_ratio' => 0
        ];
        
        $cacheDir = $this->config['cache_path'];
        $originalDir = $this->config['assets_path'];
        
        // Contar arquivos e tamanhos
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cacheDir));
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $stats['cached_files']++;
                $stats['total_size'] += $file->getSize();
            }
        }
        
        // Tamanho original
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($originalDir));
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $stats['original_size'] += $file->getSize();
            }
        }
        
        // Ratio de compressão
        if ($stats['original_size'] > 0) {
            $stats['compression_ratio'] = round(
                (1 - ($stats['total_size'] / $stats['original_size'])) * 100, 
                2
            );
        }
        
        return $stats;
    }
}

/**
 * Funções auxiliares para templates
 */
function asset_css(string $filename): string 
{
    return AssetOptimizer::getInstance()->processCSS($filename);
}

function asset_js(string $filename): string 
{
    return AssetOptimizer::getInstance()->processJS($filename);
}

function asset_img(string $filename): string 
{
    return AssetOptimizer::getInstance()->processImage($filename);
}

function combine_css(array $files, string $output = 'combined.css'): string 
{
    return AssetOptimizer::getInstance()->combineCSS($files, $output);
}

function combine_js(array $files, string $output = 'combined.js'): string 
{
    return AssetOptimizer::getInstance()->combineJS($files, $output);
}

/**
 * Middleware para servir assets otimizados
 */
function serve_optimized_asset(): void 
{
    if (!isset($_SERVER['REQUEST_URI'])) {
        return;
    }
    
    $uri = $_SERVER['REQUEST_URI'];
    
    // Verificar se é requisição de asset
    if (!preg_match('#^/storage/assets/(.+)#', $uri, $matches)) {
        return;
    }
    
    $assetPath = BASE_PATH . '/storage/assets/' . $matches[1];
    
    if (!file_exists($assetPath)) {
        http_response_code(404);
        exit;
    }
    
    // Verificar se cliente suporta compressão
    $acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
    
    if (strpos($acceptEncoding, 'br') !== false && file_exists($assetPath . '.br')) {
        $assetPath .= '.br';
        header('Content-Encoding: br');
    } elseif (strpos($acceptEncoding, 'gzip') !== false && file_exists($assetPath . '.gz')) {
        $assetPath .= '.gz';
        header('Content-Encoding: gzip');
    }
    
    // Headers de cache
    $etag = md5_file($assetPath);
    $lastModified = filemtime($assetPath);
    
    header('ETag: "' . $etag . '"');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
    header('Cache-Control: public, max-age=31536000'); // 1 ano
    
    // Verificar cache do cliente
    $ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
    $ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';
    
    if ($ifNoneMatch === '"' . $etag . '"' || 
        ($ifModifiedSince && strtotime($ifModifiedSince) >= $lastModified)) {
        http_response_code(304);
        exit;
    }
    
    // Determinar tipo de conteúdo
    $extension = pathinfo($matches[1], PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'otf' => 'font/otf'
    ];
    
    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
    header('Content-Type: ' . $mimeType);
    
    // Servir arquivo
    readfile($assetPath);
    exit;
}

// Registrar middleware se não for CLI
if (php_sapi_name() !== 'cli') {
    serve_optimized_asset();
}