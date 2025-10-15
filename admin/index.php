<?php
/**
 * Dashboard Principal do Administrador - Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Painel principal de administração com estatísticas e visão geral
 */

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Verificar se está logado como admin
if (!is_logged_in('admin')) {
    safe_redirect('/mr-carlos-barbershop/pages/login.php');
}

$admin = get_logged_user('admin');
$page_title = 'Dashboard Administrativo';

global $pdo;

try {
    // === ESTATÍSTICAS GERAIS ===
    
    // Agendamentos hoje
    $stmt = $pdo->prepare("SELECT COUNT(*) as total, 
                SUM(CASE WHEN status = 'agendado' THEN 1 ELSE 0 END) as agendados,
                SUM(CASE WHEN status = 'confirmado' THEN 1 ELSE 0 END) as confirmados,
                SUM(CASE WHEN status = 'concluido' THEN 1 ELSE 0 END) as concluidos
         FROM agendamentos 
         WHERE DATE(data_hora) = CURDATE()");
    $stmt->execute();
    $stats_hoje = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0, 'agendados' => 0, 'confirmados' => 0, 'concluidos' => 0];
    
    // Receita do mês
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(s.preco), 0) as receita_mes
         FROM agendamentos a
         LEFT JOIN servicos s ON a.servico_id = s.id
         WHERE YEAR(a.data_hora) = YEAR(CURDATE()) 
         AND MONTH(a.data_hora) = MONTH(CURDATE())
         AND a.status = 'concluido'");
    $stmt->execute();
    $receita_mes = $stmt->fetch(PDO::FETCH_ASSOC)['receita_mes'] ?? 0;
    
    // Receita do mês anterior para comparação
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(s.preco), 0) as receita_anterior
         FROM agendamentos a
         LEFT JOIN servicos s ON a.servico_id = s.id
         WHERE YEAR(a.data_hora) = YEAR(CURDATE() - INTERVAL 1 MONTH) 
         AND MONTH(a.data_hora) = MONTH(CURDATE() - INTERVAL 1 MONTH)
         AND a.status = 'concluido'");
    $stmt->execute();
    $receita_anterior = $stmt->fetch(PDO::FETCH_ASSOC)['receita_anterior'] ?? 0;
    
    // Calcular crescimento
    $crescimento_receita = 0;
    if ($receita_anterior > 0) {
        $crescimento_receita = (($receita_mes - $receita_anterior) / $receita_anterior) * 100;
    }
    
    // Total de clientes ativos
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM clientes WHERE ativo = 1");
    $stmt->execute();
    $total_clientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Total de barbeiros ativos
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM barbeiros WHERE ativo = 1");
    $stmt->execute();
    $total_barbeiros = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // === PRÓXIMOS AGENDAMENTOS ===
    $stmt = $pdo->prepare("SELECT a.*, c.nome as cliente_nome, c.telefone as cliente_telefone,
                b.nome as barbeiro_nome, s.nome as servico_nome, s.preco
         FROM agendamentos a
         LEFT JOIN clientes c ON a.cliente_id = c.id
         LEFT JOIN barbeiros b ON a.barbeiro_id = b.id
         LEFT JOIN servicos s ON a.servico_id = s.id
         WHERE a.data_hora >= NOW()
         AND a.status IN ('agendado', 'confirmado')
         ORDER BY a.data_hora ASC
         LIMIT 10");
    $stmt->execute();
    $proximos_agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // === BARBEIROS E SUAS ESTATÍSTICAS ===
    $stmt = $pdo->prepare("SELECT b.id, b.nome, b.especialidades,
                COUNT(a.id) as agendamentos_mes,
                COALESCE(SUM(s.preco), 0) as receita_barbeiro
         FROM barbeiros b
         LEFT JOIN agendamentos a ON b.id = a.barbeiro_id 
             AND YEAR(a.data_hora) = YEAR(CURDATE()) 
             AND MONTH(a.data_hora) = MONTH(CURDATE())
             AND a.status = 'concluido'
         LEFT JOIN servicos s ON a.servico_id = s.id
         WHERE b.ativo = 1
         GROUP BY b.id, b.nome, b.especialidades
         ORDER BY receita_barbeiro DESC");
    $stmt->execute();
    $barbeiros_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Erro no dashboard admin: " . $e->getMessage());
}

include_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <!-- Header Administrativo Moderno -->
    <nav class="bg-gradient-to-r from-gray-900 via-gray-800 to-slate-900 shadow-2xl border-b-4 border-indigo-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <!-- Logo e Título -->
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <div class="h-12 w-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-shield-alt text-white text-xl"></i>
                        </div>
                        <div class="absolute -top-1 -right-1 w-4 h-4 bg-green-400 rounded-full animate-pulse"></div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">Painel Administrativo</h1>
                        <p class="text-indigo-200 flex items-center">
                            <i class="fas fa-user-tie mr-2"></i>
                            Olá, <?= htmlspecialchars($admin['nome']) ?>
                        </p>
                    </div>
                </div>
                
                <!-- Navegação e Perfil -->
                <div class="flex items-center space-x-6">
                    <!-- Menu Principal -->
                    <div class="hidden md:flex items-center space-x-6">
                        <a href="servicos.php" class="flex items-center px-4 py-2 text-gray-300 hover:text-white hover:bg-white hover:bg-opacity-10 rounded-lg transition duration-200">
                            <i class="fas fa-cut mr-2"></i>
                            Serviços
                        </a>
                        <a href="barbeiros.php" class="flex items-center px-4 py-2 text-gray-300 hover:text-white hover:bg-white hover:bg-opacity-10 rounded-lg transition duration-200">
                            <i class="fas fa-users mr-2"></i>
                            Barbeiros
                        </a>
                        <a href="reports.php" class="flex items-center px-4 py-2 text-gray-300 hover:text-white hover:bg-white hover:bg-opacity-10 rounded-lg transition duration-200">
                            <i class="fas fa-chart-line mr-2"></i>
                            Relatórios
                        </a>
                    </div>
                    
                    <!-- Perfil Admin -->
                    <div class="relative">
                        <button onclick="toggleUserMenu()" class="flex items-center space-x-3 px-4 py-2 bg-white bg-opacity-10 rounded-xl hover:bg-opacity-20 transition duration-200">
                            <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center">
                                <span class="text-white font-semibold text-sm"><?= strtoupper(substr($admin['nome'], 0, 1)) ?></span>
                            </div>
                            <div class="hidden sm:block text-left">
                                <p class="text-white text-sm font-medium"><?= htmlspecialchars($admin['nome']) ?></p>
                                <p class="text-indigo-200 text-xs">Nível <?= htmlspecialchars($admin['nivel']) ?></p>
                            </div>
                            <i class="fas fa-chevron-down text-gray-300"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div id="userMenu" class="hidden absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-2xl border border-gray-100 z-50 overflow-hidden">
                            <div class="px-4 py-3 bg-gradient-to-r from-indigo-500 to-purple-600">
                                <p class="text-white font-semibold"><?= htmlspecialchars($admin['nome']) ?></p>
                                <p class="text-indigo-100 text-xs">Administrador • Nível <?= htmlspecialchars($admin['nivel']) ?></p>
                            </div>
                            
                            <div class="py-2">
                                <a href="barbeiros.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors">
                                    <i class="fas fa-users mr-3 text-indigo-500"></i>
                                    <span>Gerenciar Barbeiros</span>
                                </a>
                                <a href="servicos.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors">
                                    <i class="fas fa-cut mr-3 text-indigo-500"></i>
                                    <span>Gerenciar Serviços</span>
                                </a>
                                <a href="reports.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors">
                                    <i class="fas fa-chart-bar mr-3 text-indigo-500"></i>
                                    <span>Relatórios</span>
                                </a>
                                
                                <div class="border-t border-gray-100 mt-2 pt-2">
                                    <a href="logout.php" class="flex items-center px-4 py-3 text-red-600 hover:bg-red-50 transition-colors">
                                        <i class="fas fa-sign-out-alt mr-3"></i>
                                        <span>Sair do Sistema</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Conteúdo Principal -->
    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Cabeçalho da Seção -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Visão Geral do Sistema</h2>
            <p class="text-gray-600">Acompanhe as métricas principais da barbearia em tempo real</p>
        </div>
        
        <!-- Cards de Métricas Principais -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <!-- Card: Agendamentos Hoje -->
            <div class="group bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 border border-blue-100 hover:border-blue-200 transform hover:-translate-y-2">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Hoje</h3>
                            <p class="text-3xl font-bold text-gray-900 mt-2"><?= $stats_hoje['total'] ?></p>
                            <p class="text-xs text-gray-500 mt-1">Agendamentos</p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition duration-300">
                            <i class="fas fa-calendar-day text-white text-2xl"></i>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <div class="grid grid-cols-2 gap-4 text-center">
                            <div>
                                <p class="text-lg font-bold text-green-600"><?= $stats_hoje['confirmados'] ?></p>
                                <p class="text-xs text-gray-500">Confirmados</p>
                            </div>
                            <div>
                                <p class="text-lg font-bold text-purple-600"><?= $stats_hoje['concluidos'] ?></p>
                                <p class="text-xs text-gray-500">Concluídos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Receita do Mês -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 hover:shadow-xl transition-all duration-300">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Receita do Mês</dt>
                                <dd class="text-2xl font-bold text-gray-900">R$ <?= number_format($receita_mes, 2, ',', '.') ?></dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4">
                        <?php if ($crescimento_receita > 0): ?>
                            <div class="flex items-center text-sm text-green-600">
                                <i class="fas fa-arrow-up mr-1"></i>
                                +<?= number_format($crescimento_receita, 1) ?>% vs mês anterior
                            </div>
                        <?php elseif ($crescimento_receita < 0): ?>
                            <div class="flex items-center text-sm text-red-600">
                                <i class="fas fa-arrow-down mr-1"></i>
                                <?= number_format($crescimento_receita, 1) ?>% vs mês anterior
                            </div>
                        <?php else: ?>
                            <div class="text-sm text-gray-500">
                                Mesmo valor do mês anterior
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Total de Clientes -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 hover:shadow-xl transition-all duration-300">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-friends text-purple-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Clientes Ativos</dt>
                                <dd class="text-2xl font-bold text-gray-900"><?= $total_clientes ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total de Barbeiros -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 hover:shadow-xl transition-all duration-300">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 bg-dourado bg-opacity-20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-cut text-dourado text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Barbeiros Ativos</dt>
                                <dd class="text-2xl font-bold text-gray-900"><?= $total_barbeiros ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Próximos Agendamentos -->
            <div class="bg-white shadow-lg rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-clock mr-2 text-dourado"></i>
                        Próximos Agendamentos
                    </h3>
                </div>
                <div class="p-6">
                    <?php if (empty($proximos_agendamentos)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-calendar-times text-3xl mb-4"></i>
                            <p>Nenhum agendamento próximo</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4 max-h-96 overflow-y-auto">
                            <?php foreach ($proximos_agendamentos as $agendamento): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-1">
                                            <span class="font-medium text-gray-900"><?= htmlspecialchars($agendamento['cliente_nome']) ?></span>
                                            <span class="ml-2 px-2 py-1 text-xs rounded-full bg-<?= $agendamento['status'] === 'confirmado' ? 'green' : 'blue' ?>-100 text-<?= $agendamento['status'] === 'confirmado' ? 'green' : 'blue' ?>-800">
                                                <?= ucfirst($agendamento['status']) ?>
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            <?= htmlspecialchars($agendamento['servico_nome']) ?> • 
                                            <?= htmlspecialchars($agendamento['barbeiro_nome']) ?>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= date('d/m H:i', strtotime($agendamento['data_hora'])) ?>
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            R$ <?= number_format($agendamento['preco'], 2, ',', '.') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Performance dos Barbeiros -->
            <div class="bg-white shadow-lg rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-trophy mr-2 text-dourado"></i>
                        Performance dos Barbeiros (Este Mês)
                    </h3>
                </div>
                <div class="p-6">
                    <?php if (empty($barbeiros_stats)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-users-slash text-3xl mb-4"></i>
                            <p>Nenhum barbeiro ativo</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($barbeiros_stats as $barbeiro): ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 bg-gradient-to-r from-dourado to-dourado_escuro rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900"><?= htmlspecialchars($barbeiro['nome']) ?></div>
                                            <div class="text-sm text-gray-600"><?= htmlspecialchars($barbeiro['especialidades']) ?></div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-gray-900">R$ <?= number_format($barbeiro['receita_barbeiro'], 2, ',', '.') ?></div>
                                        <div class="text-sm text-gray-600"><?= $barbeiro['agendamentos_mes'] ?> atendimentos</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ações Rápidas</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="barbeiros.php" class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 hover:shadow-xl hover:border-dourado transition-all duration-300 group">
                    <div class="flex items-center">
                        <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-dourado group-hover:text-white transition-all duration-300">
                            <i class="fas fa-users text-blue-600 text-xl group-hover:text-white"></i>
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold text-gray-900 group-hover:text-dourado transition-colors">Gerenciar Barbeiros</h4>
                            <p class="text-sm text-gray-600">Adicionar, editar ou remover barbeiros</p>
                        </div>
                    </div>
                </a>

                <a href="servicos.php" class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 hover:shadow-xl hover:border-dourado transition-all duration-300 group">
                    <div class="flex items-center">
                        <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-dourado group-hover:text-white transition-all duration-300">
                            <i class="fas fa-cut text-green-600 text-xl group-hover:text-white"></i>
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold text-gray-900 group-hover:text-dourado transition-colors">Gerenciar Serviços</h4>
                            <p class="text-sm text-gray-600">Configurar preços e durações</p>
                        </div>
                    </div>
                </a>

                <a href="reports.php" class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 hover:shadow-xl hover:border-dourado transition-all duration-300 group">
                    <div class="flex items-center">
                        <div class="h-12 w-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-dourado group-hover:text-white transition-all duration-300">
                            <i class="fas fa-chart-bar text-purple-600 text-xl group-hover:text-white"></i>
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold text-gray-900 group-hover:text-dourado transition-colors">Relatórios</h4>
                            <p class="text-sm text-gray-600">Análises e estatísticas detalhadas</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function toggleUserMenu() {
    const menu = document.getElementById('userMenu');
    menu.classList.toggle('hidden');
}

// Fechar menu ao clicar fora
document.addEventListener('click', function(event) {
    const userMenu = document.getElementById('userMenu');
    const userButton = event.target.closest('button');
    
    if (!userButton || !userButton.onclick) {
        userMenu.classList.add('hidden');
    }
});

// Auto-refresh da página a cada 5 minutos para manter dados atualizados
setTimeout(() => {
    location.reload();
}, 300000);
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>