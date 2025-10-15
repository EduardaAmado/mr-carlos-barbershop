<?php
/**
 * Relatórios e Análises - Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Relatórios detalhados e análises de performance
 */

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Verificar se está logado como admin
if (!is_logged_in('admin')) {
    safe_redirect('/mr-carlos-barbershop/admin/login.php');
}

$admin = get_logged_user('admin');
$page_title = 'Relatórios e Análises';

// Parâmetros de filtro
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01'); // Início do mês atual
$data_fim = $_GET['data_fim'] ?? date('Y-m-t'); // Fim do mês atual
$barbeiro_id = intval($_GET['barbeiro_id'] ?? 0);
$servico_id = intval($_GET['servico_id'] ?? 0);

try {
    // === RESUMO GERAL ===
    $where_conditions = ["a.data_hora BETWEEN ? AND ?"];
    $params = [$data_inicio . ' 00:00:00', $data_fim . ' 23:59:59'];
    $types = 'ss';
    
    if ($barbeiro_id) {
        $where_conditions[] = "a.barbeiro_id = ?";
        $params[] = $barbeiro_id;
        $types .= 'i';
    }
    
    if ($servico_id) {
        $where_conditions[] = "a.servico_id = ?";
        $params[] = $servico_id;
        $types .= 'i';
    }
    
    $where_clause = "WHERE " . implode(' AND ', $where_conditions);
    
    // Resumo geral
    $resumo_result = execute_prepared_query(
        "SELECT 
            COUNT(*) as total_agendamentos,
            COUNT(CASE WHEN status = 'agendado' THEN 1 END) as agendados,
            COUNT(CASE WHEN status = 'confirmado' THEN 1 END) as confirmados,
            COUNT(CASE WHEN status = 'concluido' THEN 1 END) as concluidos,
            COUNT(CASE WHEN status = 'cancelado' THEN 1 END) as cancelados,
            COUNT(CASE WHEN status = 'falta' THEN 1 END) as faltas,
            COALESCE(SUM(CASE WHEN status = 'concluido' THEN s.preco END), 0) as receita_total,
            AVG(CASE WHEN status = 'concluido' THEN s.preco END) as ticket_medio
         FROM agendamentos a
         LEFT JOIN servicos s ON a.servico_id = s.id
         {$where_clause}",
        $params,
        $types
    );
    
    $resumo = $resumo_result ? $resumo_result->fetch_assoc() : [
        'total_agendamentos' => 0, 'agendados' => 0, 'confirmados' => 0, 
        'concluidos' => 0, 'cancelados' => 0, 'faltas' => 0,
        'receita_total' => 0, 'ticket_medio' => 0
    ];
    
    // === PERFORMANCE POR BARBEIRO ===
    $barbeiro_where = $where_clause;
    if (!$barbeiro_id) {
        $barbeiro_where .= " AND b.ativo = 1";
    }
    
    $barbeiros_performance_result = execute_prepared_query(
        "SELECT 
            b.id, b.nome,
            COUNT(a.id) as total_agendamentos,
            COUNT(CASE WHEN a.status = 'concluido' THEN 1 END) as concluidos,
            COUNT(CASE WHEN a.status = 'cancelado' THEN 1 END) as cancelados,
            COUNT(CASE WHEN a.status = 'falta' THEN 1 END) as faltas,
            COALESCE(SUM(CASE WHEN a.status = 'concluido' THEN s.preco END), 0) as receita
         FROM barbeiros b
         LEFT JOIN agendamentos a ON b.id = a.barbeiro_id AND a.data_hora BETWEEN ? AND ?
         LEFT JOIN servicos s ON a.servico_id = s.id
         WHERE b.ativo = 1" . ($barbeiro_id ? " AND b.id = {$barbeiro_id}" : "") . "
         GROUP BY b.id, b.nome
         ORDER BY receita DESC",
        [$data_inicio . ' 00:00:00', $data_fim . ' 23:59:59'],
        'ss'
    );
    
    $barbeiros_performance = [];
    if ($barbeiros_performance_result) {
        while ($row = $barbeiros_performance_result->fetch_assoc()) {
            $barbeiros_performance[] = $row;
        }
    }
    
    // === PERFORMANCE POR SERVIÇO ===
    $servicos_performance_result = execute_prepared_query(
        "SELECT 
            s.id, s.nome, s.preco,
            COUNT(a.id) as total_agendamentos,
            COUNT(CASE WHEN a.status = 'concluido' THEN 1 END) as concluidos,
            COALESCE(SUM(CASE WHEN a.status = 'concluido' THEN s.preco END), 0) as receita_total
         FROM servicos s
         LEFT JOIN agendamentos a ON s.id = a.servico_id AND a.data_hora BETWEEN ? AND ?
         WHERE s.ativo = 1" . ($servico_id ? " AND s.id = {$servico_id}" : "") . "
         GROUP BY s.id, s.nome, s.preco
         HAVING total_agendamentos > 0
         ORDER BY receita_total DESC",
        [$data_inicio . ' 00:00:00', $data_fim . ' 23:59:59'],
        'ss'
    );
    
    $servicos_performance = [];
    if ($servicos_performance_result) {
        while ($row = $servicos_performance_result->fetch_assoc()) {
            $servicos_performance[] = $row;
        }
    }
    
    // === RECEITA POR DIA (PARA GRÁFICO) ===
    $receita_diaria_result = execute_prepared_query(
        "SELECT 
            DATE(a.data_hora) as data,
            COUNT(CASE WHEN a.status = 'concluido' THEN 1 END) as atendimentos,
            COALESCE(SUM(CASE WHEN a.status = 'concluido' THEN s.preco END), 0) as receita
         FROM agendamentos a
         LEFT JOIN servicos s ON a.servico_id = s.id
         {$where_clause}
         GROUP BY DATE(a.data_hora)
         ORDER BY data ASC",
        $params,
        $types
    );
    
    $receita_diaria = [];
    if ($receita_diaria_result) {
        while ($row = $receita_diaria_result->fetch_assoc()) {
            $receita_diaria[] = $row;
        }
    }
    
    // === HORÁRIOS MAIS MOVIMENTADOS ===
    $horarios_result = execute_prepared_query(
        "SELECT 
            HOUR(a.data_hora) as hora,
            COUNT(*) as total_agendamentos,
            COUNT(CASE WHEN a.status = 'concluido' THEN 1 END) as concluidos
         FROM agendamentos a
         {$where_clause}
         GROUP BY HOUR(a.data_hora)
         ORDER BY total_agendamentos DESC",
        $params,
        $types
    );
    
    $horarios_movimento = [];
    if ($horarios_result) {
        while ($row = $horarios_result->fetch_assoc()) {
            $horarios_movimento[] = $row;
        }
    }
    
    // === LISTAS PARA FILTROS ===
    $barbeiros_lista_result = execute_prepared_query(
        "SELECT id, nome FROM barbeiros WHERE ativo = 1 ORDER BY nome",
        [],
        ''
    );
    $barbeiros_lista = [];
    if ($barbeiros_lista_result) {
        while ($row = $barbeiros_lista_result->fetch_assoc()) {
            $barbeiros_lista[] = $row;
        }
    }
    
    $servicos_lista_result = execute_prepared_query(
        "SELECT id, nome FROM servicos WHERE ativo = 1 ORDER BY nome",
        [],
        ''
    );
    $servicos_lista = [];
    if ($servicos_lista_result) {
        while ($row = $servicos_lista_result->fetch_assoc()) {
            $servicos_lista[] = $row;
        }
    }
    
} catch (Exception $e) {
    error_log("Erro nos relatórios: " . $e->getMessage());
    $erro = "Erro ao carregar relatórios.";
}

include_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-gray-50">
    <!-- Header do Admin -->
    <nav class="bg-white shadow-lg border-b-2 border-dourado">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="index.php" class="h-10 w-10 bg-gradient-to-r from-dourado to-dourado_escuro rounded-full flex items-center justify-center mr-4 hover:shadow-lg transition-all">
                        <i class="fas fa-arrow-left text-white"></i>
                    </a>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Relatórios e Análises</h1>
                        <p class="text-sm text-gray-600">Performance detalhada da barbearia</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <button onclick="window.print()" class="text-gray-600 hover:text-dourado transition-colors" title="Imprimir relatório">
                        <i class="fas fa-print text-xl"></i>
                    </button>
                    <a href="index.php" class="text-gray-600 hover:text-dourado transition-colors">
                        <i class="fas fa-home text-xl"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Filtros -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-filter mr-2 text-dourado"></i>
                    Filtros do Relatório
                </h3>
            </div>
            <div class="p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <!-- Período -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
                        <input type="date" name="data_inicio" value="<?= $data_inicio ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                        <input type="date" name="data_fim" value="<?= $data_fim ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                    </div>
                    
                    <!-- Barbeiro -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Barbeiro</label>
                        <select name="barbeiro_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                            <option value="">Todos os barbeiros</option>
                            <?php foreach ($barbeiros_lista as $barbeiro): ?>
                                <option value="<?= $barbeiro['id'] ?>" <?= $barbeiro_id == $barbeiro['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($barbeiro['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Serviço -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Serviço</label>
                        <select name="servico_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                            <option value="">Todos os serviços</option>
                            <?php foreach ($servicos_lista as $servico): ?>
                                <option value="<?= $servico['id'] ?>" <?= $servico_id == $servico['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($servico['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Botão -->
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-dourado text-white px-4 py-2 rounded-lg hover:bg-dourado_escuro transition-colors">
                            <i class="fas fa-search mr-2"></i>
                            Gerar Relatório
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Cards de Resumo -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Total de Agendamentos -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total de Agendamentos</p>
                        <p class="text-3xl font-bold text-gray-900"><?= $resumo['total_agendamentos'] ?></p>
                    </div>
                    <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Atendimentos Concluídos -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Concluídos</p>
                        <p class="text-3xl font-bold text-green-600"><?= $resumo['concluidos'] ?></p>
                        <?php if ($resumo['total_agendamentos'] > 0): ?>
                            <p class="text-sm text-gray-500"><?= number_format(($resumo['concluidos'] / $resumo['total_agendamentos']) * 100, 1) ?>% do total</p>
                        <?php endif; ?>
                    </div>
                    <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Receita Total -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Receita Total</p>
                        <p class="text-3xl font-bold text-dourado">R$ <?= number_format($resumo['receita_total'], 2, ',', '.') ?></p>
                    </div>
                    <div class="h-12 w-12 bg-dourado bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-dourado text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Ticket Médio -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Ticket Médio</p>
                        <p class="text-3xl font-bold text-purple-600">R$ <?= number_format($resumo['ticket_medio'], 2, ',', '.') ?></p>
                    </div>
                    <div class="h-12 w-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Status dos Agendamentos -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-pie mr-2 text-dourado"></i>
                        Status dos Agendamentos
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <div class="h-3 w-3 bg-green-500 rounded-full mr-2"></div>
                                <span class="text-sm text-gray-700">Concluídos</span>
                            </div>
                            <div class="text-right">
                                <span class="font-semibold"><?= $resumo['concluidos'] ?></span>
                                <?php if ($resumo['total_agendamentos'] > 0): ?>
                                    <span class="text-sm text-gray-500 ml-2">(<?= number_format(($resumo['concluidos'] / $resumo['total_agendamentos']) * 100, 1) ?>%)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <div class="h-3 w-3 bg-blue-500 rounded-full mr-2"></div>
                                <span class="text-sm text-gray-700">Confirmados</span>
                            </div>
                            <div class="text-right">
                                <span class="font-semibold"><?= $resumo['confirmados'] ?></span>
                                <?php if ($resumo['total_agendamentos'] > 0): ?>
                                    <span class="text-sm text-gray-500 ml-2">(<?= number_format(($resumo['confirmados'] / $resumo['total_agendamentos']) * 100, 1) ?>%)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <div class="h-3 w-3 bg-yellow-500 rounded-full mr-2"></div>
                                <span class="text-sm text-gray-700">Agendados</span>
                            </div>
                            <div class="text-right">
                                <span class="font-semibold"><?= $resumo['agendados'] ?></span>
                                <?php if ($resumo['total_agendamentos'] > 0): ?>
                                    <span class="text-sm text-gray-500 ml-2">(<?= number_format(($resumo['agendados'] / $resumo['total_agendamentos']) * 100, 1) ?>%)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <div class="h-3 w-3 bg-red-500 rounded-full mr-2"></div>
                                <span class="text-sm text-gray-700">Cancelados</span>
                            </div>
                            <div class="text-right">
                                <span class="font-semibold"><?= $resumo['cancelados'] ?></span>
                                <?php if ($resumo['total_agendamentos'] > 0): ?>
                                    <span class="text-sm text-gray-500 ml-2">(<?= number_format(($resumo['cancelados'] / $resumo['total_agendamentos']) * 100, 1) ?>%)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <div class="h-3 w-3 bg-gray-500 rounded-full mr-2"></div>
                                <span class="text-sm text-gray-700">Faltas</span>
                            </div>
                            <div class="text-right">
                                <span class="font-semibold"><?= $resumo['faltas'] ?></span>
                                <?php if ($resumo['total_agendamentos'] > 0): ?>
                                    <span class="text-sm text-gray-500 ml-2">(<?= number_format(($resumo['faltas'] / $resumo['total_agendamentos']) * 100, 1) ?>%)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Horários Mais Movimentados -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-clock mr-2 text-dourado"></i>
                        Horários Mais Movimentados
                    </h3>
                </div>
                <div class="p-6">
                    <?php if (empty($horarios_movimento)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-clock text-3xl mb-2"></i>
                            <p>Nenhum agendamento no período</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3 max-h-80 overflow-y-auto">
                            <?php foreach ($horarios_movimento as $horario): ?>
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="font-medium text-gray-900">
                                        <?= sprintf('%02d:00 - %02d:59', $horario['hora'], $horario['hora']) ?>
                                    </span>
                                    <div class="text-right">
                                        <div class="text-sm font-semibold text-gray-900"><?= $horario['total_agendamentos'] ?> agendamentos</div>
                                        <div class="text-xs text-gray-600"><?= $horario['concluidos'] ?> concluídos</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Performance dos Barbeiros -->
        <?php if (!empty($barbeiros_performance)): ?>
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-users mr-2 text-dourado"></i>
                    Performance dos Barbeiros
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barbeiro</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agendamentos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concluídos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taxa Sucesso</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receita</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($barbeiros_performance as $barbeiro): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 bg-gradient-to-r from-dourado to-dourado_escuro rounded-full flex items-center justify-center mr-3">
                                            <span class="text-white text-sm font-medium">
                                                <?= strtoupper(substr($barbeiro['nome'], 0, 1)) ?>
                                            </span>
                                        </div>
                                        <span class="font-medium text-gray-900"><?= htmlspecialchars($barbeiro['nome']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $barbeiro['total_agendamentos'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $barbeiro['concluidos'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php if ($barbeiro['total_agendamentos'] > 0): ?>
                                        <?= number_format(($barbeiro['concluidos'] / $barbeiro['total_agendamentos']) * 100, 1) ?>%
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-dourado">
                                    R$ <?= number_format($barbeiro['receita'], 2, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Performance dos Serviços -->
        <?php if (!empty($servicos_performance)): ?>
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-cut mr-2 text-dourado"></i>
                    Performance dos Serviços
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serviço</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agendamentos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concluídos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receita Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($servicos_performance as $servico): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900"><?= htmlspecialchars($servico['nome']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">R$ <?= number_format($servico['preco'], 2, ',', '.') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $servico['total_agendamentos'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $servico['concluidos'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-dourado">
                                    R$ <?= number_format($servico['receita_total'], 2, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Receita por Dia -->
        <?php if (!empty($receita_diaria)): ?>
        <div class="bg-white rounded-xl shadow-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-chart-line mr-2 text-dourado"></i>
                    Receita Diária
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-3 max-h-80 overflow-y-auto">
                    <?php foreach ($receita_diaria as $dia): ?>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="font-medium text-gray-900">
                                <?= date('d/m/Y (D)', strtotime($dia['data'])) ?>
                            </span>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-dourado">R$ <?= number_format($dia['receita'], 2, ',', '.') ?></div>
                                <div class="text-xs text-gray-600"><?= $dia['atendimentos'] ?> atendimentos</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Configurar impressão para melhor layout
window.addEventListener('beforeprint', function() {
    document.body.classList.add('print-mode');
});

window.addEventListener('afterprint', function() {
    document.body.classList.remove('print-mode');
});
</script>

<style>
@media print {
    .print-mode nav,
    .print-mode button,
    .print-mode .no-print {
        display: none !important;
    }
    
    .print-mode {
        background: white !important;
    }
    
    .print-mode .bg-gray-50 {
        background: white !important;
    }
    
    .print-mode .shadow-lg {
        box-shadow: none !important;
        border: 1px solid #e5e7eb !important;
    }
}
</style>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>