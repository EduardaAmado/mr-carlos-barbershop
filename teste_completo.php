<?php
// Teste completo do sistema
echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n";
echo "<meta charset='UTF-8'>\n";
echo "<title>Teste Completo - Mr. Carlos Barbershop</title>\n";
echo "</head>\n<body>\n";

echo "<h1>Teste Completo do Sistema</h1>\n";

// 1. Teste de CSS
echo "<h2>1. Teste de CSS</h2>\n";
$css_file = 'assets/css/styles.css';
if (file_exists($css_file)) {
    $css_size = filesize($css_file);
    echo "<p>✅ CSS encontrado: {$css_size} bytes</p>\n";
    
    // Teste de acesso HTTP
    $base_url = 'http://localhost/mr-carlos-barbershop/';
    $css_url = $base_url . $css_file;
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'method' => 'HEAD'
        ]
    ]);
    
    $headers = @get_headers($css_url, 1, $context);
    if ($headers && strpos($headers[0], '200') !== false) {
        echo "<p>✅ CSS acessível via HTTP</p>\n";
    } else {
        echo "<p>❌ CSS não acessível via HTTP</p>\n";
    }
} else {
    echo "<p>❌ CSS não encontrado</p>\n";
}

// 2. Teste de Banco de Dados
echo "<h2>2. Teste de Banco de Dados</h2>\n";
try {
    require_once 'config/database.php';
    $pdo = Database::getInstance()->getConnection();
    echo "<p>✅ Conexão com banco estabelecida</p>\n";
    
    // Teste de tabelas
    $tables = ['usuarios', 'barbeiros', 'servicos', 'agendamentos', 'admin'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM {$table}");
            $count = $stmt->fetchColumn();
            echo "<p>✅ Tabela {$table}: {$count} registros</p>\n";
        } catch (Exception $e) {
            echo "<p>❌ Tabela {$table}: erro - " . $e->getMessage() . "</p>\n";
        }
    }
} catch (Exception $e) {
    echo "<p>❌ Erro de banco: " . $e->getMessage() . "</p>\n";
}

// 3. Teste de Páginas
echo "<h2>3. Teste de Páginas</h2>\n";
$pages = [
    'index.php' => 'Página Principal',
    'pages/login.php' => 'Login Unificado',
    'barbeiro/dashboard.php' => 'Dashboard Barbeiro',
    'admin/index.php' => 'Admin Dashboard'
];

foreach ($pages as $page => $title) {
    if (file_exists($page)) {
        echo "<p>✅ {$title}: arquivo existe</p>\n";
        
        // Verificar se a página inclui o header corretamente
        $content = file_get_contents($page);
        if (strpos($content, 'includes/header.php') !== false) {
            echo "<p>✅ {$title}: inclui header</p>\n";
        } else {
            echo "<p>⚠️ {$title}: não inclui header</p>\n";
        }
    } else {
        echo "<p>❌ {$title}: arquivo não encontrado</p>\n";
    }
}

// 4. Teste de Security Manager
echo "<h2>4. Teste de Security Manager</h2>\n";
try {
    require_once 'includes/SecurityManager.php';
    $security = SecurityManager::getInstance();
    echo "<p>✅ SecurityManager carregado</p>\n";
} catch (Exception $e) {
    echo "<p>❌ SecurityManager erro: " . $e->getMessage() . "</p>\n";
}

// 5. Verificação de CSP
echo "<h2>5. Teste de CSP</h2>\n";
if (file_exists('includes/security_middleware.php')) {
    $content = file_get_contents('includes/security_middleware.php');
    if (strpos($content, 'Content-Security-Policy') !== false) {
        echo "<p>✅ CSP configurado</p>\n";
    } else {
        echo "<p>❌ CSP não encontrado</p>\n";
    }
} else {
    echo "<p>❌ Security middleware não encontrado</p>\n";
}

echo "</body>\n</html>\n";
?>