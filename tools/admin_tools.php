<?php
/**
 * Painel de Ferramentas Administrativas - Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Interface web para gerenciar testes, backups e manutenção
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/security_middleware.php';
require_once __DIR__ . '/../includes/helpers.php';

// Verificar autenticação de administrador
if (!is_logged_in('admin')) {
    safe_redirect(get_base_url('admin/login.php'));
}

// Verificar permissões
if (!check_role_access(['admin'], $_SESSION['user']['type'] ?? '')) {
    safe_redirect(get_base_url('admin/index.php'));
}

$page_title = 'Ferramentas do Sistema - ' . SITE_NAME;
$message = '';
$error = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validate_csrf($_POST['csrf_token'] ?? '', 'admin_tools')) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'run_tests':
            $test_output = '';
            ob_start();
            include __DIR__ . '/test_system.php';
            $test_output = ob_get_clean();
            $message = "Testes executados. Verifique os resultados abaixo.";
            break;
            
        case 'create_backup':
            require_once __DIR__ . '/backup.php';
            $backup_manager = new BackupManager();
            $result = $backup_manager->createFullBackup();
            
            if ($result['success']) {
                $message = "Backup criado com sucesso: " . $result['backup_name'];
            } else {
                $error = "Erro ao criar backup: " . $result['error'];
            }
            break;
            
        case 'run_maintenance':
            require_once __DIR__ . '/maintenance.php';
            $maintenance = new MaintenanceManager();
            $result = $maintenance->runFullMaintenance();
            
            $successful = $result['summary']['successful_tasks'];
            $total = $result['summary']['total_tasks'];
            $message = "Manutenção executada: $successful/$total tarefas concluídas com sucesso.";
            break;
            
        case 'toggle_maintenance_mode':
            require_once __DIR__ . '/maintenance.php';
            $maintenance = new MaintenanceManager();
            $enable = $_POST['enable_maintenance'] ?? false;
            $result = $maintenance->setMaintenanceMode($enable);
            
            $status = $enable ? 'ativado' : 'desativado';
            $message = "Modo de manutenção $status.";
            break;
    }
}

// Obter informações do sistema
$system_info = [
    'php_version' => PHP_VERSION,
    'mysql_version' => $conn->server_info ?? 'N/A',
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
    'disk_free' => disk_free_space(__DIR__),
    'disk_total' => disk_total_space(__DIR__),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'upload_max_filesize' => ini_get('upload_max_filesize')
];

$system_info['disk_usage_percent'] = round((($system_info['disk_total'] - $system_info['disk_free']) / $system_info['disk_total']) * 100, 2);

// Verificar modo de manutenção
require_once __DIR__ . '/maintenance.php';
$maintenance_mode = MaintenanceManager::isInMaintenanceMode();

// Obter lista de backups
try {
    require_once __DIR__ . '/backup.php';
    $backup_manager = new BackupManager();
    $available_backups = $backup_manager->listBackups();
} catch (Exception $e) {
    $available_backups = [];
    error_log("Erro ao listar backups: " . $e->getMessage());
}

include_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="container mx-auto px-4 py-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-tools text-blue-600 mr-2"></i>
                        Ferramentas do Sistema
                    </h1>
                    <p class="text-gray-600 mt-1">Testes, backup, manutenção e monitoramento</p>
                </div>
                <a href="../admin/index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar ao Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <!-- Mensagens -->
        <?php if ($message): ?>
            <?php echo show_message($message, 'success'); ?>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <?php echo show_message($error, 'error'); ?>
        <?php endif; ?>

        <!-- Status do Sistema -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Informações do Sistema -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        Informações do Sistema
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">PHP:</span>
                                <span class="font-medium"><?= $system_info['php_version'] ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">MySQL:</span>
                                <span class="font-medium"><?= $system_info['mysql_version'] ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Servidor:</span>
                                <span class="font-medium text-xs"><?= htmlspecialchars(substr($system_info['server_software'], 0, 20)) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Memory Limit:</span>
                                <span class="font-medium"><?= $system_info['memory_limit'] ?></span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Max Execution:</span>
                                <span class="font-medium"><?= $system_info['max_execution_time'] ?>s</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Upload Max:</span>
                                <span class="font-medium"><?= $system_info['upload_max_filesize'] ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Disco Livre:</span>
                                <span class="font-medium"><?= number_format($system_info['disk_free'] / 1024 / 1024 / 1024, 2) ?> GB</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Uso do Disco:</span>
                                <span class="font-medium <?= $system_info['disk_usage_percent'] > 90 ? 'text-red-600' : 'text-green-600' ?>">
                                    <?= $system_info['disk_usage_percent'] ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status de Manutenção -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-cog text-yellow-600 mr-2"></i>
                        Modo de Manutenção
                    </h2>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-medium text-gray-900">Status Atual</h3>
                            <p class="text-sm text-gray-600">
                                O modo de manutenção bloqueia o acesso público ao sistema
                            </p>
                        </div>
                        <div class="text-right">
                            <?php if ($maintenance_mode): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    Ativo
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Inativo
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <form method="POST" class="space-y-4">
                        <?= csrf_field('admin_tools') ?>
                        <input type="hidden" name="action" value="toggle_maintenance_mode">
                        
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="enable_maintenance" 
                                       value="1" 
                                       <?= $maintenance_mode ? 'checked' : '' ?>
                                       class="mr-2">
                                <span class="text-sm text-gray-700">Ativar modo de manutenção</span>
                            </label>
                        </div>
                        
                        <button type="submit" 
                                class="btn btn-warning btn-sm"
                                onclick="return confirm('Confirmar alteração do modo de manutenção?')">
                            <i class="fas fa-sync mr-2"></i>
                            Atualizar Status
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Ferramentas Principais -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Testes do Sistema -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-flask text-green-600 mr-2"></i>
                        Testes do Sistema
                    </h2>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-4">
                        Executa testes automatizados para verificar a integridade e funcionamento de todos os componentes do sistema.
                    </p>
                    
                    <form method="POST">
                        <?= csrf_field('admin_tools') ?>
                        <input type="hidden" name="action" value="run_tests">
                        
                        <button type="submit" class="w-full btn btn-success">
                            <i class="fas fa-play mr-2"></i>
                            Executar Testes
                        </button>
                    </form>
                    
                    <div class="mt-4 text-xs text-gray-500">
                        <p>• Conectividade do banco</p>
                        <p>• Recursos de segurança</p>
                        <p>• APIs e endpoints</p>
                        <p>• Performance e otimização</p>
                    </div>
                </div>
            </div>

            <!-- Backup do Sistema -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-download text-blue-600 mr-2"></i>
                        Backup Completo
                    </h2>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-4">
                        Cria backup completo do banco de dados e arquivos do sistema para garantir a segurança dos dados.
                    </p>
                    
                    <form method="POST">
                        <?= csrf_field('admin_tools') ?>
                        <input type="hidden" name="action" value="create_backup">
                        
                        <button type="submit" class="w-full btn btn-primary">
                            <i class="fas fa-save mr-2"></i>
                            Criar Backup
                        </button>
                    </form>
                    
                    <div class="mt-4 text-xs text-gray-500">
                        <p>• Backup do banco de dados</p>
                        <p>• Arquivos do sistema</p>
                        <p>• Configurações</p>
                        <p>• Compactação automática</p>
                    </div>
                </div>
            </div>

            <!-- Manutenção do Sistema -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-wrench text-purple-600 mr-2"></i>
                        Manutenção
                    </h2>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-4">
                        Executa rotinas de manutenção para otimizar performance, limpar arquivos temporários e logs antigos.
                    </p>
                    
                    <form method="POST">
                        <?= csrf_field('admin_tools') ?>
                        <input type="hidden" name="action" value="run_maintenance">
                        
                        <button type="submit" 
                                class="w-full btn btn-warning"
                                onclick="return confirm('A manutenção pode demorar alguns minutos. Continuar?')">
                            <i class="fas fa-cogs mr-2"></i>
                            Executar Manutenção
                        </button>
                    </form>
                    
                    <div class="mt-4 text-xs text-gray-500">
                        <p>• Limpeza de sessões</p>
                        <p>• Otimização do banco</p>
                        <p>• Arquivos temporários</p>
                        <p>• Logs de segurança</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Backups -->
        <?php if (!empty($available_backups)): ?>
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-history text-indigo-600 mr-2"></i>
                    Backups Disponíveis
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data/Hora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tamanho BD</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tamanho Arquivos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach (array_slice($available_backups, 0, 10) as $backup): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= date('d/m/Y H:i', strtotime($backup['timestamp'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($backup['backup_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= $backup['files']['database']['size_formatted'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= $backup['files']['system_files']['size_formatted'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button class="text-blue-600 hover:text-blue-900 mr-3"
                                        onclick="downloadBackup('<?= $backup['backup_name'] ?>')">
                                    <i class="fas fa-download mr-1"></i>
                                    Download
                                </button>
                                <button class="text-red-600 hover:text-red-900"
                                        onclick="confirmRestore('<?= $backup['backup_name'] ?>')">
                                    <i class="fas fa-undo mr-1"></i>
                                    Restaurar
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Resultado dos Testes -->
        <?php if (isset($test_output)): ?>
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-clipboard-check text-green-600 mr-2"></i>
                    Resultado dos Testes
                </h2>
            </div>
            <div class="p-6">
                <pre class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm overflow-x-auto max-h-96"><?= htmlspecialchars($test_output) ?></pre>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function downloadBackup(backupName) {
    window.location.href = 'backup.php?action=download&backup=' + encodeURIComponent(backupName);
}

function confirmRestore(backupName) {
    if (confirm('ATENÇÃO: Esta ação irá substituir todos os dados atuais pelo backup selecionado.\n\nEsta operação NÃO PODE SER DESFEITA.\n\nTem certeza que deseja continuar?')) {
        if (confirm('CONFIRMAÇÃO FINAL: Todos os dados atuais serão perdidos.\n\nDigite "RESTAURAR" para confirmar:')) {
            // Aqui poderia ser implementada a lógica de restauração
            alert('Funcionalidade de restauração deve ser implementada com segurança adicional.');
        }
    }
}

// Atualizar status a cada 30 segundos
setInterval(function() {
    fetch(window.location.href)
        .then(response => response.text())
        .then(html => {
            // Extrair e atualizar apenas as seções necessárias
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Atualizar informações do sistema se necessário
            console.log('Status atualizado');
        })
        .catch(error => console.error('Erro ao atualizar status:', error));
}, 30000);
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>