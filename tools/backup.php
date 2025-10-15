<?php
/**
 * Sistema de Backup Automatizado - Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Criar backups completos do banco de dados e arquivos do sistema
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/security_middleware.php';

class BackupManager 
{
    private $conn;
    private $backup_dir;
    private $max_backups = 10; // Manter últimos 10 backups
    
    public function __construct() 
    {
        global $conn;
        $this->conn = $conn;
        $this->backup_dir = __DIR__ . '/backups';
        
        // Criar diretório de backup se não existir
        if (!is_dir($this->backup_dir)) {
            mkdir($this->backup_dir, 0755, true);
        }
    }

    /**
     * Executar backup completo
     */
    public function createFullBackup() 
    {
        $timestamp = date('Y-m-d_H-i-s');
        $backup_name = "mr_carlos_backup_$timestamp";
        
        $this->log("Iniciando backup completo: $backup_name");
        
        try {
            // 1. Backup do banco de dados
            $db_file = $this->backupDatabase($backup_name);
            
            // 2. Backup dos arquivos do sistema
            $files_archive = $this->backupFiles($backup_name);
            
            // 3. Criar arquivo de informações
            $info_file = $this->createInfoFile($backup_name, $db_file, $files_archive);
            
            // 4. Limpar backups antigos
            $this->cleanupOldBackups();
            
            $this->log("Backup completo criado com sucesso!");
            
            return [
                'success' => true,
                'backup_name' => $backup_name,
                'database_file' => $db_file,
                'files_archive' => $files_archive,
                'info_file' => $info_file,
                'timestamp' => $timestamp
            ];
            
        } catch (Exception $e) {
            $this->log("Erro no backup: " . $e->getMessage(), 'error');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Backup do banco de dados
     */
    private function backupDatabase($backup_name) 
    {
        $this->log("Criando backup do banco de dados...");
        
        $db_file = $this->backup_dir . "/{$backup_name}_database.sql";
        
        // Obter lista de tabelas
        $tables = [];
        $result = $this->conn->query("SHOW TABLES");
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
        
        $sql_content = "";
        $sql_content .= "-- Backup do Banco de Dados Mr. Carlos Barbershop\n";
        $sql_content .= "-- Data: " . date('d/m/Y H:i:s') . "\n";
        $sql_content .= "-- Versão MySQL: " . $this->conn->server_info . "\n\n";
        $sql_content .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql_content .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql_content .= "SET AUTOCOMMIT = 0;\n";
        $sql_content .= "START TRANSACTION;\n\n";
        
        foreach ($tables as $table) {
            $this->log("Fazendo backup da tabela: $table");
            
            // Estrutura da tabela
            $create_result = $this->conn->query("SHOW CREATE TABLE `$table`");
            $create_row = $create_result->fetch_array();
            
            $sql_content .= "-- Estrutura da tabela `$table`\n";
            $sql_content .= "DROP TABLE IF EXISTS `$table`;\n";
            $sql_content .= $create_row[1] . ";\n\n";
            
            // Dados da tabela
            $data_result = $this->conn->query("SELECT * FROM `$table`");
            if ($data_result->num_rows > 0) {
                $sql_content .= "-- Dados da tabela `$table`\n";
                $sql_content .= "INSERT INTO `$table` VALUES\n";
                
                $rows = [];
                while ($row = $data_result->fetch_assoc()) {
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = "'" . $this->conn->real_escape_string($value) . "'";
                        }
                    }
                    $rows[] = "(" . implode(", ", $values) . ")";
                }
                
                $sql_content .= implode(",\n", $rows) . ";\n\n";
            }
        }
        
        $sql_content .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $sql_content .= "COMMIT;\n";
        
        file_put_contents($db_file, $sql_content);
        
        $this->log("Backup do banco salvo: " . basename($db_file) . " (" . $this->formatFileSize(filesize($db_file)) . ")");
        
        return $db_file;
    }

    /**
     * Backup dos arquivos do sistema
     */
    private function backupFiles($backup_name) 
    {
        $this->log("Criando backup dos arquivos do sistema...");
        
        $archive_file = $this->backup_dir . "/{$backup_name}_files.zip";
        $root_dir = dirname(__DIR__);
        
        $zip = new ZipArchive();
        if ($zip->open($archive_file, ZipArchive::CREATE) !== TRUE) {
            throw new Exception("Não foi possível criar arquivo ZIP");
        }
        
        // Diretórios e arquivos para incluir no backup
        $include_dirs = [
            'config',
            'includes',
            'pages',
            'admin',
            'barbeiro',
            'api',
            'assets',
            'cron'
        ];
        
        $include_files = [
            'index.php',
            '.htaccess'
        ];
        
        // Excluir arquivos temporários e sensíveis
        $exclude_patterns = [
            'tools/backups/',
            'logs/',
            'uploads/',
            '.git/',
            'node_modules/',
            '*.log',
            '*.tmp',
            'config.local.php'
        ];
        
        foreach ($include_dirs as $dir) {
            $dir_path = $root_dir . '/' . $dir;
            if (is_dir($dir_path)) {
                $this->addDirectoryToZip($zip, $dir_path, $dir);
            }
        }
        
        foreach ($include_files as $file) {
            $file_path = $root_dir . '/' . $file;
            if (file_exists($file_path)) {
                $zip->addFile($file_path, $file);
            }
        }
        
        $zip->close();
        
        $this->log("Backup dos arquivos salvo: " . basename($archive_file) . " (" . $this->formatFileSize(filesize($archive_file)) . ")");
        
        return $archive_file;
    }

    /**
     * Adicionar diretório ao ZIP recursivamente
     */
    private function addDirectoryToZip($zip, $dir_path, $zip_path) 
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir_path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            $file_path = $file->getRealPath();
            $relative_path = $zip_path . '/' . substr($file_path, strlen($dir_path) + 1);
            
            if ($file->isDir()) {
                $zip->addEmptyDir(str_replace('\\', '/', $relative_path));
            } else {
                $zip->addFile($file_path, str_replace('\\', '/', $relative_path));
            }
        }
    }

    /**
     * Criar arquivo de informações do backup
     */
    private function createInfoFile($backup_name, $db_file, $files_archive) 
    {
        $info_file = $this->backup_dir . "/{$backup_name}_info.json";
        
        $info = [
            'backup_name' => $backup_name,
            'timestamp' => date('c'),
            'created_by' => 'Mr. Carlos Barbershop Backup System',
            'php_version' => PHP_VERSION,
            'mysql_version' => $this->conn->server_info,
            'files' => [
                'database' => [
                    'file' => basename($db_file),
                    'size' => filesize($db_file),
                    'size_formatted' => $this->formatFileSize(filesize($db_file))
                ],
                'system_files' => [
                    'file' => basename($files_archive),
                    'size' => filesize($files_archive),
                    'size_formatted' => $this->formatFileSize(filesize($files_archive))
                ]
            ],
            'system_info' => [
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
                'php_extensions' => get_loaded_extensions(),
                'backup_tool_version' => '1.0'
            ]
        ];
        
        file_put_contents($info_file, json_encode($info, JSON_PRETTY_PRINT));
        
        return $info_file;
    }

    /**
     * Limpar backups antigos
     */
    private function cleanupOldBackups() 
    {
        $this->log("Limpando backups antigos...");
        
        $backups = glob($this->backup_dir . '/mr_carlos_backup_*_info.json');
        
        if (count($backups) > $this->max_backups) {
            // Ordenar por data (mais antigo primeiro)
            usort($backups, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // Remover os mais antigos
            $to_remove = array_slice($backups, 0, count($backups) - $this->max_backups);
            
            foreach ($to_remove as $info_file) {
                $backup_name = str_replace(['_info.json'], '', basename($info_file));
                $this->removeBackup($backup_name);
            }
        }
    }

    /**
     * Remover backup específico
     */
    private function removeBackup($backup_name) 
    {
        $files_to_remove = [
            $this->backup_dir . "/{$backup_name}_database.sql",
            $this->backup_dir . "/{$backup_name}_files.zip",
            $this->backup_dir . "/{$backup_name}_info.json"
        ];
        
        foreach ($files_to_remove as $file) {
            if (file_exists($file)) {
                unlink($file);
                $this->log("Removido: " . basename($file));
            }
        }
    }

    /**
     * Listar backups disponíveis
     */
    public function listBackups() 
    {
        $backups = [];
        $info_files = glob($this->backup_dir . '/mr_carlos_backup_*_info.json');
        
        foreach ($info_files as $info_file) {
            $info = json_decode(file_get_contents($info_file), true);
            $backups[] = $info;
        }
        
        // Ordenar por data (mais recente primeiro)
        usort($backups, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        return $backups;
    }

    /**
     * Restaurar backup
     */
    public function restoreBackup($backup_name) 
    {
        $this->log("Iniciando restauração do backup: $backup_name");
        
        try {
            $db_file = $this->backup_dir . "/{$backup_name}_database.sql";
            
            if (!file_exists($db_file)) {
                throw new Exception("Arquivo de backup não encontrado: $db_file");
            }
            
            // Ler e executar SQL
            $sql_content = file_get_contents($db_file);
            
            // Executar em transação
            $this->conn->autocommit(false);
            
            if ($this->conn->multi_query($sql_content)) {
                do {
                    if ($result = $this->conn->store_result()) {
                        $result->free();
                    }
                } while ($this->conn->next_result());
            }
            
            if ($this->conn->error) {
                throw new Exception("Erro na restauração: " . $this->conn->error);
            }
            
            $this->conn->commit();
            $this->conn->autocommit(true);
            
            $this->log("Backup restaurado com sucesso!");
            
            return ['success' => true, 'message' => 'Backup restaurado com sucesso'];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            $this->conn->autocommit(true);
            
            $this->log("Erro na restauração: " . $e->getMessage(), 'error');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Agendar backup automático
     */
    public function scheduleAutoBackup($frequency = 'daily') 
    {
        $cron_file = __DIR__ . '/../cron/auto_backup.php';
        
        $cron_content = "<?php\n";
        $cron_content .= "// Backup automático - Mr. Carlos Barbershop\n";
        $cron_content .= "// Frequência: $frequency\n\n";
        $cron_content .= "require_once __DIR__ . '/../tools/backup.php';\n\n";
        $cron_content .= "\$backup_manager = new BackupManager();\n";
        $cron_content .= "\$result = \$backup_manager->createFullBackup();\n\n";
        $cron_content .= "if (\$result['success']) {\n";
        $cron_content .= "    error_log('Backup automático criado: ' . \$result['backup_name']);\n";
        $cron_content .= "} else {\n";
        $cron_content .= "    error_log('Erro no backup automático: ' . \$result['error']);\n";
        $cron_content .= "}\n";
        
        file_put_contents($cron_file, $cron_content);
        
        $this->log("Backup automático configurado: $cron_file");
        
        return $cron_file;
    }

    /**
     * Formatar tamanho de arquivo
     */
    private function formatFileSize($bytes) 
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Log de mensagens
     */
    private function log($message, $level = 'info') 
    {
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] [$level] $message\n";
        
        echo $log_message;
        
        // Salvar em arquivo de log
        $log_file = __DIR__ . '/backup.log';
        file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
    }
}

// Interface web simples
if (basename($_SERVER['PHP_SELF']) === 'backup.php' && isset($_SERVER['HTTP_HOST'])) {
    $action = $_GET['action'] ?? 'list';
    $backup_manager = new BackupManager();
    
    header('Content-Type: application/json');
    
    switch ($action) {
        case 'create':
            echo json_encode($backup_manager->createFullBackup());
            break;
            
        case 'list':
            echo json_encode($backup_manager->listBackups());
            break;
            
        case 'restore':
            $backup_name = $_POST['backup_name'] ?? '';
            if ($backup_name) {
                echo json_encode($backup_manager->restoreBackup($backup_name));
            } else {
                echo json_encode(['success' => false, 'error' => 'Nome do backup não informado']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Ação inválida']);
    }
    exit;
}

// Executar se chamado via linha de comando
if (basename($_SERVER['PHP_SELF']) === 'backup.php' && !isset($_SERVER['HTTP_HOST'])) {
    echo "=== SISTEMA DE BACKUP MR. CARLOS BARBERSHOP ===\n\n";
    
    $backup_manager = new BackupManager();
    $result = $backup_manager->createFullBackup();
    
    if ($result['success']) {
        echo "✅ Backup criado com sucesso!\n";
        echo "Nome: {$result['backup_name']}\n";
        echo "Timestamp: {$result['timestamp']}\n";
    } else {
        echo "❌ Erro no backup: {$result['error']}\n";
    }
}