<?php
/**
 * Sistema de Manutenção - Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Ferramentas de manutenção, limpeza e otimização do sistema
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/security_middleware.php';

class MaintenanceManager 
{
    private $conn;
    private $maintenance_log;
    
    public function __construct() 
    {
        global $conn;
        $this->conn = $conn;
        $this->maintenance_log = __DIR__ . '/maintenance.log';
    }

    /**
     * Executar manutenção completa do sistema
     */
    public function runFullMaintenance() 
    {
        $this->log("=== INICIANDO MANUTENÇÃO COMPLETA DO SISTEMA ===");
        
        $tasks = [
            'cleanExpiredSessions' => 'Limpeza de sessões expiradas',
            'cleanSecurityLogs' => 'Limpeza de logs de segurança antigos',
            'cleanTempFiles' => 'Limpeza de arquivos temporários',
            'optimizeDatabase' => 'Otimização do banco de dados',
            'cleanExpiredBookings' => 'Limpeza de agendamentos antigos',
            'updateStatistics' => 'Atualização de estatísticas',
            'checkSystemHealth' => 'Verificação de saúde do sistema',
            'cleanOldBackups' => 'Limpeza de backups antigos'
        ];
        
        $results = [];
        $total_time = 0;
        
        foreach ($tasks as $method => $description) {
            $this->log("Executando: $description");
            $start_time = microtime(true);
            
            try {
                $result = $this->$method();
                $execution_time = round((microtime(true) - $start_time) * 1000, 2);
                $total_time += $execution_time;
                
                $results[$method] = [
                    'status' => 'success',
                    'message' => $result['message'] ?? 'Concluído',
                    'details' => $result['details'] ?? [],
                    'execution_time' => $execution_time
                ];
                
                $this->log("✅ $description concluído ({$execution_time}ms)");
                
            } catch (Exception $e) {
                $execution_time = round((microtime(true) - $start_time) * 1000, 2);
                $total_time += $execution_time;
                
                $results[$method] = [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'execution_time' => $execution_time
                ];
                
                $this->log("❌ Erro em $description: " . $e->getMessage());
            }
        }
        
        $this->log("=== MANUTENÇÃO COMPLETA FINALIZADA ===");
        $this->log("Tempo total: " . round($total_time, 2) . "ms");
        
        return [
            'success' => true,
            'total_time' => round($total_time, 2),
            'tasks' => $results,
            'summary' => $this->generateMaintenanceSummary($results)
        ];
    }

    /**
     * Limpeza de sessões expiradas
     */
    private function cleanExpiredSessions() 
    {
        $session_lifetime = ini_get('session.gc_maxlifetime') ?: 1440;
        $cutoff_time = time() - $session_lifetime;
        
        // Limpeza de arquivos de sessão (se usando arquivo)
        $session_path = session_save_path() ?: sys_get_temp_dir();
        $session_files = glob($session_path . '/sess_*');
        $removed_count = 0;
        
        foreach ($session_files as $file) {
            if (filemtime($file) < $cutoff_time) {
                if (unlink($file)) {
                    $removed_count++;
                }
            }
        }
        
        return [
            'message' => "Removidas $removed_count sessões expiradas",
            'details' => [
                'session_lifetime' => $session_lifetime,
                'removed_files' => $removed_count
            ]
        ];
    }

    /**
     * Limpeza de logs de segurança antigos
     */
    private function cleanSecurityLogs() 
    {
        $days_to_keep = 90; // Manter logs dos últimos 90 dias
        
        try {
            // Logs de segurança
            $stmt = $this->conn->prepare(
                "DELETE FROM security_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)"
            );
            $stmt->bind_param('i', $days_to_keep);
            $stmt->execute();
            $security_removed = $stmt->affected_rows;
            
            // Tentativas de login falhadas
            $stmt = $this->conn->prepare(
                "DELETE FROM failed_login_attempts 
                 WHERE first_attempt < DATE_SUB(NOW(), INTERVAL ? DAY) 
                 AND blocked_until IS NULL"
            );
            $stmt->bind_param('i', $days_to_keep);
            $stmt->execute();
            $attempts_removed = $stmt->affected_rows;
            
            return [
                'message' => "Removidos $security_removed logs de segurança e $attempts_removed tentativas antigas",
                'details' => [
                    'days_kept' => $days_to_keep,
                    'security_logs_removed' => $security_removed,
                    'failed_attempts_removed' => $attempts_removed
                ]
            ];
            
        } catch (Exception $e) {
            throw new Exception("Erro na limpeza de logs: " . $e->getMessage());
        }
    }

    /**
     * Limpeza de arquivos temporários
     */
    private function cleanTempFiles() 
    {
        $temp_dirs = [
            __DIR__ . '/../uploads/temp',
            __DIR__ . '/../cache',
            sys_get_temp_dir() . '/mr_carlos_*'
        ];
        
        $removed_files = 0;
        $freed_space = 0;
        
        foreach ($temp_dirs as $dir_pattern) {
            if (strpos($dir_pattern, '*') !== false) {
                $files = glob($dir_pattern);
            } else {
                $files = is_dir($dir_pattern) ? glob($dir_pattern . '/*') : [];
            }
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    // Remover arquivos com mais de 24 horas
                    if (filemtime($file) < (time() - 86400)) {
                        $size = filesize($file);
                        if (unlink($file)) {
                            $removed_files++;
                            $freed_space += $size;
                        }
                    }
                }
            }
        }
        
        return [
            'message' => "Removidos $removed_files arquivos temporários (" . $this->formatFileSize($freed_space) . " liberados)",
            'details' => [
                'files_removed' => $removed_files,
                'space_freed' => $freed_space,
                'space_freed_formatted' => $this->formatFileSize($freed_space)
            ]
        ];
    }

    /**
     * Otimização do banco de dados
     */
    private function optimizeDatabase() 
    {
        $tables_optimized = 0;
        $tables_with_issues = [];
        
        try {
            // Obter lista de tabelas
            $result = $this->conn->query("SHOW TABLES");
            $tables = [];
            while ($row = $result->fetch_array()) {
                $tables[] = $row[0];
            }
            
            foreach ($tables as $table) {
                // Verificar e reparar tabela se necessário
                $check_result = $this->conn->query("CHECK TABLE `$table`");
                $check_row = $check_result->fetch_assoc();
                
                if ($check_row['Msg_text'] !== 'OK') {
                    $tables_with_issues[] = $table;
                    $this->conn->query("REPAIR TABLE `$table`");
                }
                
                // Otimizar tabela
                $this->conn->query("OPTIMIZE TABLE `$table`");
                $tables_optimized++;
            }
            
            // Atualizar estatísticas das tabelas
            $this->conn->query("ANALYZE TABLE " . implode(', ', array_map(function($t) { return "`$t`"; }, $tables)));
            
            return [
                'message' => "Otimizadas $tables_optimized tabelas" . (count($tables_with_issues) > 0 ? " (" . count($tables_with_issues) . " reparadas)" : ""),
                'details' => [
                    'tables_optimized' => $tables_optimized,
                    'tables_repaired' => count($tables_with_issues),
                    'repaired_tables' => $tables_with_issues
                ]
            ];
            
        } catch (Exception $e) {
            throw new Exception("Erro na otimização do banco: " . $e->getMessage());
        }
    }

    /**
     * Limpeza de agendamentos antigos
     */
    private function cleanExpiredBookings() 
    {
        try {
            // Remover agendamentos cancelados há mais de 6 meses
            $stmt = $this->conn->prepare(
                "DELETE FROM agendamentos 
                 WHERE status = 'cancelado' 
                 AND updated_at < DATE_SUB(NOW(), INTERVAL 6 MONTH)"
            );
            $stmt->execute();
            $cancelled_removed = $stmt->affected_rows;
            
            // Marcar agendamentos não comparecidos como 'nao_compareceu'
            $stmt = $this->conn->prepare(
                "UPDATE agendamentos 
                 SET status = 'nao_compareceu' 
                 WHERE status = 'agendado' 
                 AND CONCAT(data_agendamento, ' ', hora_agendamento) < DATE_SUB(NOW(), INTERVAL 2 HOUR)"
            );
            $stmt->execute();
            $no_show_updated = $stmt->affected_rows;
            
            return [
                'message' => "Removidos $cancelled_removed agendamentos cancelados antigos, $no_show_updated marcados como não compareceram",
                'details' => [
                    'cancelled_removed' => $cancelled_removed,
                    'no_show_updated' => $no_show_updated
                ]
            ];
            
        } catch (Exception $e) {
            throw new Exception("Erro na limpeza de agendamentos: " . $e->getMessage());
        }
    }

    /**
     * Atualização de estatísticas do sistema
     */
    private function updateStatistics() 
    {
        try {
            $stats = [];
            
            // Estatísticas gerais
            $queries = [
                'total_clients' => "SELECT COUNT(*) FROM clientes WHERE ativo = 1",
                'total_barbers' => "SELECT COUNT(*) FROM barbeiros WHERE ativo = 1",
                'total_services' => "SELECT COUNT(*) FROM servicos WHERE ativo = 1",
                'bookings_today' => "SELECT COUNT(*) FROM agendamentos WHERE DATE(data_agendamento) = CURDATE()",
                'bookings_this_month' => "SELECT COUNT(*) FROM agendamentos WHERE YEAR(data_agendamento) = YEAR(CURDATE()) AND MONTH(data_agendamento) = MONTH(CURDATE())",
                'revenue_this_month' => "SELECT COALESCE(SUM(preco_total), 0) FROM agendamentos WHERE status = 'concluido' AND YEAR(data_agendamento) = YEAR(CURDATE()) AND MONTH(data_agendamento) = MONTH(CURDATE())"
            ];
            
            foreach ($queries as $key => $query) {
                $result = $this->conn->query($query);
                $stats[$key] = $result->fetch_array()[0];
            }
            
            // Salvar estatísticas em cache (se implementado)
            $stats_file = __DIR__ . '/../cache/system_stats.json';
            if (!is_dir(dirname($stats_file))) {
                mkdir(dirname($stats_file), 0755, true);
            }
            
            $stats['updated_at'] = date('c');
            file_put_contents($stats_file, json_encode($stats, JSON_PRETTY_PRINT));
            
            return [
                'message' => "Estatísticas atualizadas",
                'details' => $stats
            ];
            
        } catch (Exception $e) {
            throw new Exception("Erro na atualização de estatísticas: " . $e->getMessage());
        }
    }

    /**
     * Verificação de saúde do sistema
     */
    private function checkSystemHealth() 
    {
        $health_checks = [];
        
        // Verificar conexão do banco
        $health_checks['database'] = $this->conn->ping() ? 'OK' : 'ERROR';
        
        // Verificar espaço em disco
        $free_space = disk_free_space(__DIR__);
        $total_space = disk_total_space(__DIR__);
        $usage_percent = round((($total_space - $free_space) / $total_space) * 100, 2);
        $health_checks['disk_usage'] = $usage_percent < 90 ? 'OK' : 'WARNING';
        
        // Verificar permissões de diretórios críticos
        $critical_dirs = [
            __DIR__ . '/../uploads',
            __DIR__ . '/../logs',
            __DIR__ . '/../cache'
        ];
        
        $permissions_ok = true;
        foreach ($critical_dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            if (!is_writable($dir)) {
                $permissions_ok = false;
                break;
            }
        }
        $health_checks['permissions'] = $permissions_ok ? 'OK' : 'ERROR';
        
        // Verificar configuração PHP
        $php_checks = [
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size')
        ];
        $health_checks['php_config'] = 'OK';
        
        return [
            'message' => "Verificação de saúde concluída",
            'details' => [
                'health_status' => $health_checks,
                'disk_usage_percent' => $usage_percent,
                'free_space' => $this->formatFileSize($free_space),
                'php_config' => $php_checks
            ]
        ];
    }

    /**
     * Limpeza de backups antigos
     */
    private function cleanOldBackups() 
    {
        $backup_dir = __DIR__ . '/backups';
        $max_backups = 10;
        $removed_count = 0;
        
        if (is_dir($backup_dir)) {
            $backup_files = glob($backup_dir . '/mr_carlos_backup_*_info.json');
            
            if (count($backup_files) > $max_backups) {
                // Ordenar por data de modificação
                usort($backup_files, function($a, $b) {
                    return filemtime($a) - filemtime($b);
                });
                
                // Remover os mais antigos
                $to_remove = array_slice($backup_files, 0, count($backup_files) - $max_backups);
                
                foreach ($to_remove as $info_file) {
                    $backup_name = str_replace('_info.json', '', basename($info_file));
                    
                    $files_to_delete = [
                        $backup_dir . "/{$backup_name}_database.sql",
                        $backup_dir . "/{$backup_name}_files.zip",
                        $info_file
                    ];
                    
                    foreach ($files_to_delete as $file) {
                        if (file_exists($file) && unlink($file)) {
                            $removed_count++;
                        }
                    }
                }
            }
        }
        
        return [
            'message' => "Removidos $removed_count arquivos de backup antigos",
            'details' => [
                'max_backups_kept' => $max_backups,
                'files_removed' => $removed_count
            ]
        ];
    }

    /**
     * Gerar resumo da manutenção
     */
    private function generateMaintenanceSummary($results) 
    {
        $successful = 0;
        $errors = 0;
        $total_time = 0;
        
        foreach ($results as $result) {
            if ($result['status'] === 'success') {
                $successful++;
            } else {
                $errors++;
            }
            $total_time += $result['execution_time'];
        }
        
        return [
            'total_tasks' => count($results),
            'successful_tasks' => $successful,
            'failed_tasks' => $errors,
            'success_rate' => round(($successful / count($results)) * 100, 2),
            'total_execution_time' => round($total_time, 2)
        ];
    }

    /**
     * Modo de manutenção (ativar/desativar)
     */
    public function setMaintenanceMode($enabled = true) 
    {
        $maintenance_file = __DIR__ . '/../.maintenance';
        
        if ($enabled) {
            $content = json_encode([
                'enabled' => true,
                'message' => 'Sistema em manutenção. Voltamos em breve!',
                'started_at' => date('c'),
                'started_by' => $_SESSION['user']['nome'] ?? 'Sistema'
            ]);
            
            file_put_contents($maintenance_file, $content);
            $this->log("Modo de manutenção ATIVADO");
            
        } else {
            if (file_exists($maintenance_file)) {
                unlink($maintenance_file);
            }
            $this->log("Modo de manutenção DESATIVADO");
        }
        
        return ['success' => true, 'maintenance_mode' => $enabled];
    }

    /**
     * Verificar se está em modo de manutenção
     */
    public static function isInMaintenanceMode() 
    {
        $maintenance_file = __DIR__ . '/../.maintenance';
        return file_exists($maintenance_file);
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
     * Log de manutenção
     */
    private function log($message) 
    {
        $log_entry = "[" . date('Y-m-d H:i:s') . "] $message\n";
        echo $log_entry;
        file_put_contents($this->maintenance_log, $log_entry, FILE_APPEND | LOCK_EX);
    }
}

// Interface de execução
if (basename($_SERVER['PHP_SELF']) === 'maintenance.php') {
    $action = $_GET['action'] ?? 'full';
    $maintenance = new MaintenanceManager();
    
    // Se for requisição web
    if (isset($_SERVER['HTTP_HOST'])) {
        header('Content-Type: application/json');
        
        switch ($action) {
            case 'full':
                echo json_encode($maintenance->runFullMaintenance());
                break;
                
            case 'enable':
                echo json_encode($maintenance->setMaintenanceMode(true));
                break;
                
            case 'disable':
                echo json_encode($maintenance->setMaintenanceMode(false));
                break;
                
            case 'status':
                echo json_encode([
                    'maintenance_mode' => MaintenanceManager::isInMaintenanceMode()
                ]);
                break;
                
            default:
                echo json_encode(['error' => 'Ação inválida']);
        }
    } else {
        // Execução via linha de comando
        echo "=== SISTEMA DE MANUTENÇÃO MR. CARLOS BARBERSHOP ===\n\n";
        
        $result = $maintenance->runFullMaintenance();
        
        echo "\n=== RESUMO ===\n";
        echo "Tarefas executadas: {$result['summary']['total_tasks']}\n";
        echo "Sucessos: {$result['summary']['successful_tasks']}\n";
        echo "Falhas: {$result['summary']['failed_tasks']}\n";
        echo "Taxa de sucesso: {$result['summary']['success_rate']}%\n";
        echo "Tempo total: {$result['summary']['total_execution_time']}ms\n";
    }
}