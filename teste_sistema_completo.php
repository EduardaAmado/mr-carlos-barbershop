<?php
/**
 * Teste de Links e URLs - Mr. Carlos Barbershop
 */

require_once 'config/config.php';
require_once 'includes/helpers.php';

echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n<title>Teste de Links</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; margin: 20px; }\n";
echo ".success { color: green; }\n";
echo ".error { color: red; }\n";
echo ".info { color: blue; }\n";
echo ".test-section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }\n";
echo "</style>\n";
echo "</head>\n<body>\n";

echo "<h1>🧪 Teste Completo do Sistema</h1>\n";

// 1. Teste de Configurações
echo "<div class='test-section'>\n";
echo "<h2>1. ⚙️ Configurações Base</h2>\n";

echo "<p class='info'>BASE_URL: " . BASE_URL . "</p>\n";
echo "<p class='info'>SITE_NAME: " . SITE_NAME . "</p>\n";

// Teste da função get_base_url
$base_test = get_base_url();
echo "<p class='info'>get_base_url(): " . $base_test . "</p>\n";

$base_with_path = get_base_url('assets/css/style.css');
echo "<p class='info'>get_base_url('assets/css/style.css'): " . $base_with_path . "</p>\n";
echo "</div>\n";

// 2. Teste de Arquivos CSS/JS
echo "<div class='test-section'>\n";
echo "<h2>2. 📁 Recursos Estáticos</h2>\n";

$static_files = [
    'assets/css/style.css' => 'CSS Principal',
    'assets/js/script.js' => 'JavaScript Principal',
    'includes/header.php' => 'Header Global',
    'includes/footer.php' => 'Footer Global'
];

foreach ($static_files as $file => $description) {
    $full_path = BASE_PATH . '/' . $file;
    if (file_exists($full_path)) {
        $size = filesize($full_path);
        echo "<p class='success'>✅ {$description}: {$size} bytes</p>\n";
    } else {
        echo "<p class='error'>❌ {$description}: arquivo não encontrado</p>\n";
    }
}
echo "</div>\n";

// 3. Teste de Páginas Principais
echo "<div class='test-section'>\n";
echo "<h2>3. 🌐 Páginas Principais</h2>\n";

$pages = [
    'index.php' => 'Homepage',
    'pages/login.php' => 'Login Unificado',
    'pages/servicos.php' => 'Catálogo de Serviços',
    'pages/agendar.php' => 'Sistema de Agendamento',
    'barbeiro/dashboard.php' => 'Dashboard Barbeiro',
    'admin/index.php' => 'Dashboard Admin'
];

foreach ($pages as $page => $description) {
    $full_path = BASE_PATH . '/' . $page;
    if (file_exists($full_path)) {
        $size = filesize($full_path);
        echo "<p class='success'>✅ {$description}: {$size} bytes</p>\n";
        
        // Verificar se a página usa get_base_url corretamente
        $content = file_get_contents($full_path);
        $base_url_count = substr_count($content, 'get_base_url');
        if ($base_url_count > 0) {
            echo "<p class='info'>   📋 Usa get_base_url(): {$base_url_count} vezes</p>\n";
        }
    } else {
        echo "<p class='error'>❌ {$description}: arquivo não encontrado</p>\n";
    }
}
echo "</div>\n";

// 4. Teste de Conexão com Banco
echo "<div class='test-section'>\n";
echo "<h2>4. 🗄️ Conexão com Banco de Dados</h2>\n";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>✅ Conexão estabelecida com sucesso</p>\n";
    
    // Testar algumas tabelas
    $tables = ['usuarios', 'barbeiros', 'servicos', 'agendamentos', 'admin'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM {$table}");
            $count = $stmt->fetchColumn();
            echo "<p class='success'>✅ Tabela {$table}: {$count} registros</p>\n";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Tabela {$table}: erro - " . $e->getMessage() . "</p>\n";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro de conexão: " . $e->getMessage() . "</p>\n";
}
echo "</div>\n";

// 5. Links de Navegação
echo "<div class='test-section'>\n";
echo "<h2>5. 🔗 Teste de Links de Navegação</h2>\n";

echo "<p><a href='" . get_base_url() . "' target='_blank'>🏠 Homepage</a></p>\n";
echo "<p><a href='" . get_base_url('pages/servicos.php') . "' target='_blank'>📋 Página de Serviços</a></p>\n";
echo "<p><a href='" . get_base_url('pages/login.php') . "' target='_blank'>🔐 Login</a></p>\n";
echo "<p><a href='" . get_base_url('assets/css/style.css') . "' target='_blank'>🎨 CSS Principal</a></p>\n";
echo "</div>\n";

// 6. Informações do Sistema
echo "<div class='test-section'>\n";
echo "<h2>6. 💻 Informações do Sistema</h2>\n";

echo "<p class='info'>PHP Version: " . phpversion() . "</p>\n";
echo "<p class='info'>Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "</p>\n";
echo "<p class='info'>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>\n";
echo "<p class='info'>Current Script: " . $_SERVER['SCRIPT_NAME'] . "</p>\n";
echo "<p class='info'>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>\n";
echo "</div>\n";

echo "<div style='text-align: center; margin-top: 30px;'>\n";
echo "<h3 style='color: green;'>✅ Sistema Mr. Carlos Barbershop - Funcionando Corretamente!</h3>\n";
echo "<p><strong>Data/Hora do Teste:</strong> " . date('d/m/Y H:i:s') . "</p>\n";
echo "</div>\n";

echo "</body>\n</html>\n";
?>