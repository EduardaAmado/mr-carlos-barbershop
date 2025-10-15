<?php
/**
 * Painel de Monitoramento de Segurança - Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop  
 * Data: 14 de Outubro de 2025
 * Finalidade: Interface administrativa para monitoramento de segurança
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

$page_title = 'Monitoramento de Segurança - ' . SITE_NAME;

// Processar ações
$action = $_GET['action'] ?? '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && validate_csrf($_POST['csrf_token'] ?? '', 'security_admin')) {
    switch ($action) {
        case 'cleanup_logs':
            $days = (int)($_POST['days'] ?? 90);
            security()->cleanupSecurityLogs($days);
            $message = "Logs de segurança anteriores a {$days} dias foram removidos.";
            break;
            
        case 'unblock_ip':
            $ip = sanitize($_POST['ip'], 'string');
            if ($ip) {
                try {
                    $stmt = $conn->prepare("DELETE FROM failed_login_attempts WHERE ip_address = ?");
                    $stmt->bind_param('s', $ip);
                    $stmt->execute();
                    log_security('suspicious_activity', "IP desbloqueado manualmente: $ip", 'medium', $_SESSION['user']['id']);
                    $message = "IP {$ip} foi desbloqueado.";
                } catch (Exception $e) {
                    $message = "Erro ao desbloquear IP: " . $e->getMessage();
                }
            }
            break;
    }
}

// Obter dados para dashboard
$security_report = security()->getSecurityReport(7);

// Obter tentativas bloqueadas
try {
    $blocked_query = "SELECT ip_address, email, attempt_type, attempts_count, blocked_until 
                     FROM failed_login_attempts 
                     WHERE blocked_until > NOW() 
                     ORDER BY blocked_until DESC";
    $blocked_result = $conn->query($blocked_query);
    $blocked_attempts = [];
    if ($blocked_result) {
        while ($row = $blocked_result->fetch_assoc()) {
            $blocked_attempts[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Erro ao buscar tentativas bloqueadas: " . $e->getMessage());
    $blocked_attempts = [];
}

// Obter estatísticas gerais
try {
    $stats_queries = [
        'total_events_today' => "SELECT COUNT(*) as count FROM security_logs WHERE DATE(created_at) = CURDATE()",
        'critical_events_week' => "SELECT COUNT(*) as count FROM security_logs WHERE severity = 'critical' AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)",
        'blocked_ips' => "SELECT COUNT(DISTINCT ip_address) as count FROM failed_login_attempts WHERE blocked_until > NOW()",
        'failed_logins_today' => "SELECT COUNT(*) as count FROM security_logs WHERE event_type = 'login_attempt' AND DATE(created_at) = CURDATE()"
    ];
    
    $stats = [];
    foreach ($stats_queries as $key => $query) {
        $result = $conn->query($query);
        $stats[$key] = $result ? $result->fetch_assoc()['count'] : 0;
    }
} catch (Exception $e) {
    error_log("Erro ao buscar estatísticas: " . $e->getMessage());
    $stats = array_fill_keys(array_keys($stats_queries), 0);
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
                        <i class="fas fa-shield-alt text-red-600 mr-2"></i>
                        Monitoramento de Segurança
                    </h1>
                    <p class="text-gray-600 mt-1">Sistema de logs e proteção avançada</p>
                </div>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar ao Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <!-- Mensagem -->
        <?php if ($message): ?>
            <?php echo show_message($message, 'success'); ?>
        <?php endif; ?>

        <!-- Estatísticas Rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-calendar-day text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Eventos Hoje</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['total_events_today'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Eventos Críticos (7d)</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['critical_events_week'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-ban text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">IPs Bloqueados</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['blocked_ips'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-sign-in-alt text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Logins Falhados Hoje</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['failed_logins_today'] ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- IPs Bloqueados -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-ban text-red-600 mr-2"></i>
                        IPs Bloqueados Atualmente
                    </h2>
                </div>
                <div class="p-6">
                    <?php if (empty($blocked_attempts)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-check-circle text-4xl mb-4 text-green-500"></i>
                            <p>Nenhum IP bloqueado no momento</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($blocked_attempts as $attempt): ?>
                                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            <?= htmlspecialchars($attempt['ip_address']) ?>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Tipo: <?= htmlspecialchars($attempt['attempt_type']) ?> | 
                                            Tentativas: <?= $attempt['attempts_count'] ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Bloqueado até: <?= date('d/m/Y H:i', strtotime($attempt['blocked_until'])) ?>
                                        </p>
                                    </div>
                                    <form method="POST" action="?action=unblock_ip" class="inline">
                                        <?= csrf_field('security_admin') ?>
                                        <input type="hidden" name="ip" value="<?= htmlspecialchars($attempt['ip_address']) ?>">
                                        <button type="submit" 
                                                class="text-xs bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700"
                                                onclick="return confirm('Desbloquear este IP?')">
                                            Desbloquear
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Eventos por Tipo -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-chart-pie text-blue-600 mr-2"></i>
                        Eventos por Tipo (7 dias)
                    </h2>
                </div>
                <div class="p-6">
                    <?php if (empty($security_report['events_by_type'])): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-chart-line text-4xl mb-4"></i>
                            <p>Nenhum evento registrado</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($security_report['events_by_type'] as $event): ?>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">
                                        <?= ucfirst(str_replace('_', ' ', $event['event_type'])) ?>
                                    </span>
                                    <span class="bg-gray-100 text-gray-800 text-sm px-2 py-1 rounded">
                                        <?= $event['count'] ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Eventos Críticos Recentes -->
        <?php if (!empty($security_report['critical_events'])): ?>
        <div class="mt-8 bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-exclamation-circle text-red-600 mr-2"></i>
                    Eventos Críticos Recentes
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data/Hora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Detalhes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($security_report['critical_events'] as $event): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= date('d/m/Y H:i:s', strtotime($event['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    <?= ucfirst(str_replace('_', ' ', $event['event_type'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                                <?= htmlspecialchars($event['ip_address']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 max-w-md">
                                <div class="truncate" title="<?= htmlspecialchars($event['details']) ?>">
                                    <?= htmlspecialchars(substr($event['details'], 0, 100)) ?>
                                    <?= strlen($event['details']) > 100 ? '...' : '' ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- IPs Mais Ativos -->
        <?php if (!empty($security_report['top_ips'])): ?>
        <div class="mt-8 bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-network-wired text-green-600 mr-2"></i>
                    IPs Mais Ativos (7 dias)
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach (array_slice($security_report['top_ips'], 0, 10) as $ip): ?>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                            <span class="font-mono text-sm"><?= htmlspecialchars($ip['ip_address']) ?></span>
                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                <?= $ip['count'] ?> eventos
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Ferramentas de Manutenção -->
        <div class="mt-8 bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-tools text-yellow-600 mr-2"></i>
                    Ferramentas de Manutenção
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Limpeza de Logs -->
                    <div class="border rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">Limpeza de Logs</h3>
                        <p class="text-sm text-gray-600 mb-4">Remove logs de segurança antigos para otimizar o banco de dados.</p>
                        <form method="POST" action="?action=cleanup_logs">
                            <?= csrf_field('security_admin') ?>
                            <div class="flex items-center space-x-2">
                                <select name="days" class="form-select text-sm">
                                    <option value="30">30 dias</option>
                                    <option value="60">60 dias</option>
                                    <option value="90" selected>90 dias</option>
                                    <option value="180">180 dias</option>
                                </select>
                                <button type="submit" 
                                        class="bg-yellow-600 text-white px-4 py-2 rounded text-sm hover:bg-yellow-700"
                                        onclick="return confirm('Confirmar limpeza de logs?')">
                                    <i class="fas fa-trash mr-1"></i>
                                    Limpar
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Status do Sistema -->
                    <div class="border rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">Status do Sistema</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Proteção CSRF:</span>
                                <span class="text-green-600"><i class="fas fa-check"></i> Ativa</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Rate Limiting:</span>
                                <span class="text-green-600"><i class="fas fa-check"></i> Ativo</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Logs de Segurança:</span>
                                <span class="text-green-600"><i class="fas fa-check"></i> Funcionando</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Headers de Segurança:</span>
                                <span class="text-green-600"><i class="fas fa-check"></i> Configurados</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>