<?php
/**
 * Sistema de Testes Automatizados - Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Executar testes automatizados do sistema completo
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/security_middleware.php';

class SystemTester 
{
    private $conn;
    private $results = [];
    private $passed = 0;
    private $failed = 0;
    private $warnings = 0;

    public function __construct() 
    {
        global $conn;
        $this->conn = $conn;
        
        // Headers para execução via web
        if (!isset($_SERVER['HTTP_HOST'])) {
            $_SERVER['HTTP_HOST'] = 'localhost';
        }
    }

    /**
     * Executar todos os testes
     */
    public function runAllTests() 
    {
        $this->log("=== INICIANDO TESTES DO SISTEMA MR. CARLOS BARBERSHOP ===", 'info');
        
        // Categoria 1: Configuração e Conectividade
        $this->testDatabaseConnection();
        $this->testRequiredDirectories();
        $this->testConfigurationFiles();
        $this->testPHPExtensions();
        
        // Categoria 2: Segurança
        $this->testSecurityFeatures();
        $this->testCSRFProtection();
        $this->testRateLimiting();
        $this->testInputSanitization();
        
        // Categoria 3: Funcionalidades Core
        $this->testAuthenticationSystem();
        $this->testBookingSystem();
        $this->testEmailSystem();
        
        // Categoria 4: APIs e Endpoints
        $this->testAPIEndpoints();
        $this->testAJAXFunctionality();
        
        // Categoria 5: Performance e Otimização
        $this->testDatabasePerformance();
        $this->testFilePermissions();
        
        // Relatório Final
        $this->generateReport();
    }

    /**
     * Teste de conexão com banco de dados
     */
    private function testDatabaseConnection() 
    {
        $this->log("Testando conexão com banco de dados...", 'test');
        
        try {
            if (!$this->conn) {
                throw new Exception("Conexão não estabelecida");
            }
            
            $result = $this->conn->query("SELECT 1");
            if ($result) {
                $this->pass("Conexão com banco de dados estabelecida");
            } else {
                $this->fail("Erro na query de teste: " . $this->conn->error);
            }
        } catch (Exception $e) {
            $this->fail("Conexão com banco falhou: " . $e->getMessage());
        }
    }

    /**
     * Teste de diretórios obrigatórios
     */
    private function testRequiredDirectories() 
    {
        $this->log("Verificando estrutura de diretórios...", 'test');
        
        $required_dirs = [
            __DIR__ . '/../config',
            __DIR__ . '/../includes',
            __DIR__ . '/../pages',
            __DIR__ . '/../admin',
            __DIR__ . '/../barbeiro',
            __DIR__ . '/../api',
            __DIR__ . '/../assets',
            __DIR__ . '/../cron',
            __DIR__ . '/../tools'
        ];
        
        foreach ($required_dirs as $dir) {
            if (is_dir($dir)) {
                $this->pass("Diretório existe: " . basename($dir));
            } else {
                $this->fail("Diretório ausente: " . basename($dir));
            }
        }
    }

    /**
     * Teste de arquivos de configuração
     */
    private function testConfigurationFiles() 
    {
        $this->log("Verificando arquivos de configuração...", 'test');
        
        $required_files = [
            'config/config.php' => 'Configuração principal',
            'includes/security.php' => 'Sistema de segurança',
            'includes/email.php' => 'Sistema de email',
            'includes/helpers.php' => 'Funções auxiliares',
            'includes/header.php' => 'Header padrão',
            'includes/footer.php' => 'Footer padrão'
        ];
        
        foreach ($required_files as $file => $description) {
            $full_path = __DIR__ . '/../' . $file;
            if (file_exists($full_path)) {
                $size = filesize($full_path);
                $this->pass("$description ($size bytes)");
            } else {
                $this->fail("Arquivo ausente: $file");
            }
        }
    }

    /**
     * Teste de extensões PHP
     */
    private function testPHPExtensions() 
    {
        $this->log("Verificando extensões PHP...", 'test');
        
        $required_extensions = [
            'mysqli' => 'Conexão MySQL',
            'session' => 'Gerenciamento de sessão',
            'json' => 'Manipulação JSON',
            'filter' => 'Filtragem de dados',
            'hash' => 'Funções de hash',
            'openssl' => 'Criptografia SSL',
            'curl' => 'Requisições HTTP',
            'mbstring' => 'Strings multibyte'
        ];
        
        foreach ($required_extensions as $ext => $description) {
            if (extension_loaded($ext)) {
                $this->pass("Extensão $ext carregada ($description)");
            } else {
                $this->fail("Extensão ausente: $ext - $description");
            }
        }
        
        // Verificar versão PHP
        $php_version = PHP_VERSION;
        if (version_compare($php_version, '7.4.0', '>=')) {
            $this->pass("Versão PHP adequada: $php_version");
        } else {
            $this->fail("Versão PHP inadequada: $php_version (mín: 7.4.0)");
        }
    }

    /**
     * Teste de recursos de segurança
     */
    private function testSecurityFeatures() 
    {
        $this->log("Testando recursos de segurança...", 'test');
        
        // Teste de classe SecurityManager
        try {
            $security = SecurityManager::getInstance();
            $this->pass("SecurityManager inicializado");
            
            // Teste geração CSRF
            $token = $security->generateCSRFToken('test');
            if (!empty($token)) {
                $this->pass("Geração de token CSRF funcionando");
                
                // Teste validação CSRF
                if ($security->validateCSRFToken($token, 'test')) {
                    $this->pass("Validação de token CSRF funcionando");
                } else {
                    $this->fail("Validação de token CSRF falhou");
                }
            } else {
                $this->fail("Geração de token CSRF falhou");
            }
            
        } catch (Exception $e) {
            $this->fail("Erro no SecurityManager: " . $e->getMessage());
        }
    }

    /**
     * Teste de proteção CSRF
     */
    private function testCSRFProtection() 
    {
        $this->log("Testando proteção CSRF...", 'test');
        
        try {
            // Simular sessão para teste
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $token1 = csrf_token('form1');
            $token2 = csrf_token('form2');
            
            if ($token1 !== $token2) {
                $this->pass("Tokens CSRF únicos por formulário");
            } else {
                $this->warning("Tokens CSRF podem não ser únicos suficientemente");
            }
            
            // Teste função helper
            $field_html = csrf_field('test_form');
            if (strpos($field_html, 'csrf_token') !== false && strpos($field_html, 'hidden') !== false) {
                $this->pass("Helper csrf_field() funcionando");
            } else {
                $this->fail("Helper csrf_field() com problema");
            }
            
        } catch (Exception $e) {
            $this->fail("Erro nos testes CSRF: " . $e->getMessage());
        }
    }

    /**
     * Teste de rate limiting
     */
    private function testRateLimiting() 
    {
        $this->log("Testando rate limiting...", 'test');
        
        try {
            $security = SecurityManager::getInstance();
            
            // Teste verificação de limite
            $can_proceed = $security->checkRateLimit('test_action');
            if ($can_proceed) {
                $this->pass("Rate limiting permite ação inicial");
                
                // Registrar tentativa
                $security->recordAttempt('test_action', 'test@test.com', true);
                $this->pass("Registro de tentativa funcionando");
            } else {
                $this->warning("Rate limiting muito restritivo para teste");
            }
            
        } catch (Exception $e) {
            $this->fail("Erro no rate limiting: " . $e->getMessage());
        }
    }

    /**
     * Teste de sanitização de entrada
     */
    private function testInputSanitization() 
    {
        $this->log("Testando sanitização de dados...", 'test');
        
        try {
            $security = SecurityManager::getInstance();
            
            $test_cases = [
                ['<script>alert("xss")</script>', 'string', ''],
                ['test@example.com', 'email', 'test@example.com'],
                ['(11) 99999-9999', 'phone', '11999999999'],
                ['João da Silva', 'name', 'João da Silva'],
                ['123.45', 'float', '123.45'],
                ['https://example.com', 'url', 'https://example.com']
            ];
            
            foreach ($test_cases as $case) {
                $result = $security->sanitizeInput($case[0], $case[1]);
                if ($result === $case[2]) {
                    $this->pass("Sanitização '{$case[1]}' correta");
                } else {
                    $this->fail("Sanitização '{$case[1]}' falhou: esperado '{$case[2]}', obtido '$result'");
                }
            }
            
        } catch (Exception $e) {
            $this->fail("Erro na sanitização: " . $e->getMessage());
        }
    }

    /**
     * Teste do sistema de autenticação
     */
    private function testAuthenticationSystem() 
    {
        $this->log("Testando sistema de autenticação...", 'test');
        
        try {
            // Verificar se funções existem
            if (function_exists('is_logged_in')) {
                $this->pass("Função is_logged_in() disponível");
                
                // Teste com usuário não logado
                $logged = is_logged_in();
                if ($logged === false) {
                    $this->pass("Detecção de usuário não logado");
                } else {
                    $this->warning("Usuário pode estar logado durante teste");
                }
            } else {
                $this->fail("Função is_logged_in() não encontrada");
            }
            
            if (function_exists('password_hash') && function_exists('password_verify')) {
                $hash = password_hash('test123', PASSWORD_DEFAULT);
                if (password_verify('test123', $hash)) {
                    $this->pass("Sistema de hash de senha funcionando");
                } else {
                    $this->fail("Sistema de hash de senha com problema");
                }
            } else {
                $this->fail("Funções de hash de senha não disponíveis");
            }
            
        } catch (Exception $e) {
            $this->fail("Erro no sistema de autenticação: " . $e->getMessage());
        }
    }

    /**
     * Teste do sistema de agendamento
     */
    private function testBookingSystem() 
    {
        $this->log("Testando sistema de agendamento...", 'test');
        
        try {
            // Verificar tabelas necessárias
            $tables = ['agendamentos', 'barbeiros', 'servicos', 'clientes'];
            foreach ($tables as $table) {
                $result = $this->conn->query("SHOW TABLES LIKE '$table'");
                if ($result && $result->num_rows > 0) {
                    $this->pass("Tabela '$table' existe");
                } else {
                    $this->fail("Tabela '$table' ausente");
                }
            }
            
            // Verificar se há barbeiros ativos
            $barbeiros = $this->conn->query("SELECT COUNT(*) as count FROM barbeiros WHERE ativo = 1");
            if ($barbeiros) {
                $count = $barbeiros->fetch_assoc()['count'];
                if ($count > 0) {
                    $this->pass("$count barbeiro(s) ativo(s) no sistema");
                } else {
                    $this->warning("Nenhum barbeiro ativo cadastrado");
                }
            }
            
            // Verificar se há serviços ativos
            $servicos = $this->conn->query("SELECT COUNT(*) as count FROM servicos WHERE ativo = 1");
            if ($servicos) {
                $count = $servicos->fetch_assoc()['count'];
                if ($count > 0) {
                    $this->pass("$count serviço(s) ativo(s) no sistema");
                } else {
                    $this->warning("Nenhum serviço ativo cadastrado");
                }
            }
            
        } catch (Exception $e) {
            $this->fail("Erro no sistema de agendamento: " . $e->getMessage());
        }
    }

    /**
     * Teste do sistema de email
     */
    private function testEmailSystem() 
    {
        $this->log("Testando sistema de email...", 'test');
        
        try {
            // Verificar se arquivo existe
            $email_file = __DIR__ . '/../includes/email.php';
            if (file_exists($email_file)) {
                $this->pass("Arquivo de email existe");
                
                require_once $email_file;
                
                // Verificar se classe EmailService existe
                if (class_exists('EmailService')) {
                    $this->pass("Classe EmailService carregada");
                    
                    // Verificar configurações SMTP
                    if (defined('SMTP_HOST') && defined('SMTP_USERNAME')) {
                        if (!empty(SMTP_HOST) && !empty(SMTP_USERNAME)) {
                            $this->pass("Configurações SMTP definidas");
                        } else {
                            $this->warning("Configurações SMTP vazias");
                        }
                    } else {
                        $this->warning("Constantes SMTP não definidas");
                    }
                } else {
                    $this->fail("Classe EmailService não encontrada");
                }
            } else {
                $this->fail("Arquivo de email não encontrado");
            }
            
        } catch (Exception $e) {
            $this->fail("Erro no sistema de email: " . $e->getMessage());
        }
    }

    /**
     * Teste de endpoints da API
     */
    private function testAPIEndpoints() 
    {
        $this->log("Testando endpoints da API...", 'test');
        
        $api_files = [
            'api/get_availability.php' => 'API de disponibilidade',
            'api/create_booking.php' => 'API de criação de agendamento',
            'api/barbeiro_events.php' => 'API de eventos do barbeiro',
            'api/barbeiro_toggle_block.php' => 'API de bloqueio de horários',
            'api/barbeiro_update_status.php' => 'API de status do barbeiro'
        ];
        
        foreach ($api_files as $file => $description) {
            $full_path = __DIR__ . '/../' . $file;
            if (file_exists($full_path)) {
                $this->pass("$description existe");
                
                // Verificar se tem proteção básica
                $content = file_get_contents($full_path);
                if (strpos($content, '$_SERVER[\'REQUEST_METHOD\']') !== false) {
                    $this->pass("$description tem verificação de método");
                } else {
                    $this->warning("$description sem verificação de método");
                }
            } else {
                $this->fail("$description ausente: $file");
            }
        }
    }

    /**
     * Teste de funcionalidade AJAX
     */
    private function testAJAXFunctionality() 
    {
        $this->log("Testando funcionalidades AJAX...", 'test');
        
        // Verificar se jQuery está sendo carregado
        $header_file = __DIR__ . '/../includes/header.php';
        if (file_exists($header_file)) {
            $content = file_get_contents($header_file);
            if (strpos($content, 'jquery') !== false || strpos($content, 'jQuery') !== false) {
                $this->pass("jQuery incluído no header");
            } else {
                $this->warning("jQuery pode não estar incluído");
            }
            
            if (strpos($content, 'Content-Security-Policy') !== false) {
                $this->pass("Headers CSP configurados");
            } else {
                $this->warning("Headers CSP podem não estar configurados");
            }
        }
    }

    /**
     * Teste de performance do banco de dados
     */
    private function testDatabasePerformance() 
    {
        $this->log("Testando performance do banco...", 'test');
        
        try {
            // Teste de query simples
            $start = microtime(true);
            $result = $this->conn->query("SELECT COUNT(*) FROM agendamentos");
            $end = microtime(true);
            $time = ($end - $start) * 1000; // ms
            
            if ($time < 100) {
                $this->pass("Query rápida: {$time}ms");
            } else {
                $this->warning("Query lenta: {$time}ms");
            }
            
            // Verificar índices importantes
            $indexes_check = [
                "SHOW INDEX FROM agendamentos WHERE Key_name != 'PRIMARY'",
                "SHOW INDEX FROM barbeiros WHERE Key_name != 'PRIMARY'",
                "SHOW INDEX FROM servicos WHERE Key_name != 'PRIMARY'"
            ];
            
            foreach ($indexes_check as $query) {
                $result = $this->conn->query($query);
                if ($result && $result->num_rows > 0) {
                    $table = explode(' ', $query)[3];
                    $this->pass("Índices configurados na tabela $table");
                }
            }
            
        } catch (Exception $e) {
            $this->fail("Erro no teste de performance: " . $e->getMessage());
        }
    }

    /**
     * Teste de permissões de arquivos
     */
    private function testFilePermissions() 
    {
        $this->log("Testando permissões de arquivos...", 'test');
        
        $writable_dirs = [
            __DIR__ . '/../uploads' => 'Diretório de uploads',
            __DIR__ . '/../logs' => 'Diretório de logs'
        ];
        
        foreach ($writable_dirs as $dir => $description) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            if (is_writable($dir)) {
                $this->pass("$description é gravável");
            } else {
                $this->warning("$description não é gravável");
            }
        }
        
        // Teste de arquivo de configuração
        $config_file = __DIR__ . '/../config/config.php';
        if (is_readable($config_file)) {
            $this->pass("Arquivo de configuração é legível");
            
            $perms = substr(sprintf('%o', fileperms($config_file)), -4);
            if ($perms === '0644' || $perms === '0600') {
                $this->pass("Permissões do config adequadas ($perms)");
            } else {
                $this->warning("Permissões do config podem ser inseguras ($perms)");
            }
        }
    }

    /**
     * Registrar resultado de teste com sucesso
     */
    private function pass($message) 
    {
        $this->results[] = ['status' => 'PASS', 'message' => $message, 'time' => date('H:i:s')];
        $this->passed++;
        echo "[PASS] $message\n";
    }

    /**
     * Registrar resultado de teste com falha
     */
    private function fail($message) 
    {
        $this->results[] = ['status' => 'FAIL', 'message' => $message, 'time' => date('H:i:s')];
        $this->failed++;
        echo "[FAIL] $message\n";
    }

    /**
     * Registrar aviso
     */
    private function warning($message) 
    {
        $this->results[] = ['status' => 'WARN', 'message' => $message, 'time' => date('H:i:s')];
        $this->warnings++;
        echo "[WARN] $message\n";
    }

    /**
     * Log de informação
     */
    private function log($message, $type = 'info') 
    {
        echo "[INFO] $message\n";
    }

    /**
     * Gerar relatório final
     */
    private function generateReport() 
    {
        $total = $this->passed + $this->failed + $this->warnings;
        $success_rate = $total > 0 ? round(($this->passed / $total) * 100, 2) : 0;
        
        echo "\n";
        echo "=== RELATÓRIO FINAL DOS TESTES ===\n";
        echo "Total de testes: $total\n";
        echo "Sucessos: {$this->passed}\n";
        echo "Falhas: {$this->failed}\n";
        echo "Avisos: {$this->warnings}\n";
        echo "Taxa de sucesso: $success_rate%\n";
        
        if ($this->failed == 0) {
            echo "STATUS: ✅ TODOS OS TESTES CRÍTICOS PASSARAM\n";
        } else {
            echo "STATUS: ❌ ALGUNS TESTES FALHARAM - AÇÃO NECESSÁRIA\n";
        }
        
        // Salvar relatório em arquivo
        $this->saveReportToFile();
    }

    /**
     * Salvar relatório em arquivo
     */
    private function saveReportToFile() 
    {
        $report_content = "RELATÓRIO DE TESTES - MR. CARLOS BARBERSHOP\n";
        $report_content .= "Data/Hora: " . date('d/m/Y H:i:s') . "\n";
        $report_content .= "Versão PHP: " . PHP_VERSION . "\n\n";
        
        foreach ($this->results as $result) {
            $report_content .= "[{$result['status']}] {$result['time']} - {$result['message']}\n";
        }
        
        $report_content .= "\nRESUMO:\n";
        $report_content .= "Sucessos: {$this->passed}\n";
        $report_content .= "Falhas: {$this->failed}\n";
        $report_content .= "Avisos: {$this->warnings}\n";
        
        $report_file = __DIR__ . '/test_report_' . date('Y-m-d_H-i-s') . '.txt';
        file_put_contents($report_file, $report_content);
        
        echo "Relatório salvo em: $report_file\n";
    }
}

// Executar testes se chamado diretamente
if (basename($_SERVER['PHP_SELF']) === 'test_system.php') {
    echo "Content-Type: text/plain\n\n";
    
    $tester = new SystemTester();
    $tester->runAllTests();
}