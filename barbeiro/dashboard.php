<?php
/**
 * Dashboard do Barbeiro
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Interface principal para barbeiros gerirem agendamentos e disponibilidade
 * 
 * FUNCIONALIDADES:
 * - Calendário interativo com FullCalendar
 * - Visualizar agendamentos do dia/semana/mês
 * - Marcar/desmarcar períodos de indisponibilidade
 * - Atualizar status dos agendamentos
 * - Estatísticas pessoais
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Verificar se barbeiro está logado
if (!is_logged_in('barbeiro')) {
    safe_redirect(get_base_url('pages/login.php'));
}

$barbeiro = get_logged_user('barbeiro');
global $pdo;

// Obter dados completos do barbeiro
try {
    $stmt = $pdo->prepare("SELECT * FROM barbeiros WHERE id = ?");
    $stmt->execute([$barbeiro['id']]);
    $barbeiro_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$barbeiro_data || !$barbeiro_data['ativo']) {
        throw new Exception("Barbeiro não encontrado ou inativo");
    }
} catch (Exception $e) {
    error_log("Erro ao obter dados do barbeiro: " . $e->getMessage());
    safe_redirect(get_base_url('pages/login.php'));
}

// Obter estatísticas do mês atual
$mes_atual = date('Y-m');
try {
    $stmt = $pdo->prepare("SELECT 
            COUNT(*) as total_agendamentos,
            COUNT(CASE WHEN status = 'concluido' THEN 1 END) as concluidos,
            COUNT(CASE WHEN status = 'cancelado' THEN 1 END) as cancelados,
            COUNT(CASE WHEN status = 'falta' THEN 1 END) as faltas,
            SUM(CASE WHEN status = 'concluido' THEN preco_pago ELSE 0 END) as receita
         FROM agendamentos 
         WHERE barbeiro_id = ? AND DATE_FORMAT(data_hora, '%Y-%m') = ?");
    $stmt->execute([$barbeiro['id'], $mes_atual]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Erro ao obter estatísticas: " . $e->getMessage());
    $stats = null;
}

// Obter agendamentos de hoje
$hoje = date('Y-m-d');
try {
    $stmt = $pdo->prepare("SELECT a.*, c.nome as cliente_nome, c.telefone as cliente_telefone, s.nome as servico_nome, s.duracao_minutos
         FROM agendamentos a
         LEFT JOIN clientes c ON a.cliente_id = c.id
         LEFT JOIN servicos s ON a.servico_id = s.id
         WHERE a.barbeiro_id = ? AND DATE(a.data_hora) = ?
         ORDER BY a.data_hora");
    $stmt->execute([$barbeiro['id'], $hoje]);
    $agendamentos_hoje = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Erro ao obter agendamentos de hoje: " . $e->getMessage());
    $agendamentos_hoje = [];
}

$page_title = 'Dashboard Barbeiro - ' . SITE_NAME;
include_once __DIR__ . '/../includes/header.php';
?>

<!-- CSS específico do dashboard -->
<style>
.stats-card {
    background: linear-gradient(135deg, #ffffff, #f8f9fa);
    border-left: 4px solid var(--color-accent);
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.appointment-card {
    border-left: 4px solid #e5e7eb;
    transition: all 0.3s ease;
}

.appointment-card.pendente {
    border-left-color: #f59e0b;
}

.appointment-card.confirmado {
    border-left-color: #3b82f6;
}

.appointment-card.em_curso {
    border-left-color: #10b981;
}

.appointment-card.concluido {
    border-left-color: #6b7280;
    opacity: 0.8;
}

.appointment-card.cancelado {
    border-left-color: #ef4444;
    opacity: 0.6;
}

.quick-action {
    transition: all 0.2s ease;
}

.quick-action:hover {
    transform: scale(1.05);
}

#calendar {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    padding: 20px;
}

.fc-event {
    border: none !important;
    padding: 2px 4px;
    border-radius: 4px;
    font-size: 12px;
}

.fc-event.status-pendente {
    background-color: #f59e0b !important;
    color: white !important;
}

.fc-event.status-confirmado {
    background-color: #3b82f6 !important;
    color: white !important;
}

.fc-event.status-em_curso {
    background-color: #10b981 !important;
    color: white !important;
}

.fc-event.status-concluido {
    background-color: #6b7280 !important;
    color: white !important;
}
</style>

<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100">
    <!-- Header Moderno -->
    <header class="bg-gradient-to-r from-gray-900 via-black to-gray-800 shadow-xl border-b-4 border-yellow-400">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                <!-- Informações do Barbeiro -->
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center shadow-lg">
                        <i class="fas fa-cut text-xl text-black"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-bold text-white">
                            Olá, <?php echo esc($barbeiro_data['nome']); ?>! 
                            <span class="text-2xl">✂️</span>
                        </h1>
                        <p class="text-gray-300 flex items-center">
                            <i class="fas fa-calendar-day mr-2 text-yellow-400"></i>
                            <?php 
                            $dias = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
                            $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
                            $dt = new DateTime($hoje);
                            echo $dias[$dt->format('w')] . ', ' . $dt->format('d') . ' de ' . $meses[intval($dt->format('n'))] . ' de ' . $dt->format('Y');
                            ?>
                        </p>
                    </div>
                </div>
                
                <!-- Navegação e Ações -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                    <!-- Status Online -->
                    <div class="flex items-center px-3 py-2 bg-green-500 bg-opacity-20 rounded-full">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse mr-2"></div>
                        <span class="text-sm text-green-300 font-medium">Online</span>
                    </div>
                    
                    <!-- Botão Logout -->
                    <a href="<?php echo get_base_url('barbeiro/logout.php'); ?>" 
                       class="group flex items-center px-4 py-2 bg-red-600 bg-opacity-20 text-red-300 rounded-lg hover:bg-red-600 hover:text-white transition duration-200 border border-red-500 border-opacity-30">
                        <i class="fas fa-sign-out-alt mr-2 group-hover:rotate-12 transition duration-200"></i>
                        <span class="font-medium">Sair</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container mx-auto px-4 py-8">
        <!-- Cards de Estatísticas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Agendamentos Hoje -->
            <div class="group bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-blue-200 transform hover:-translate-y-1">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Hoje</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo count($agendamentos_hoje); ?></p>
                            <p class="text-xs text-gray-500 mt-1">Agendamentos</p>
                        </div>
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition duration-300">
                            <i class="fas fa-calendar-day text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="flex items-center text-sm text-blue-600">
                            <i class="fas fa-clock mr-1"></i>
                            <span>Próximo às <?php 
                                if (!empty($agendamentos_hoje)) {
                                    echo date('H:i', strtotime($agendamentos_hoje[0]['data_hora']));
                                } else {
                                    echo 'Nenhum';
                                }
                            ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total do Mês -->
            <div class="group bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-green-200 transform hover:-translate-y-1">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Este Mês</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $stats['total_agendamentos'] ?? 0; ?></p>
                            <p class="text-xs text-gray-500 mt-1">Total de serviços</p>
                        </div>
                        <div class="w-14 h-14 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition duration-300">
                            <i class="fas fa-chart-line text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="flex items-center text-sm text-green-600">
                            <i class="fas fa-trending-up mr-1"></i>
                            <span><?php echo round((($stats['total_agendamentos'] ?? 0) / 30), 1); ?>/dia</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Concluídos -->
            <div class="group bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-yellow-200 transform hover:-translate-y-1">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Concluídos</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $stats['concluidos'] ?? 0; ?></p>
                            <p class="text-xs text-gray-500 mt-1">Serviços finalizados</p>
                        </div>
                        <div class="w-14 h-14 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition duration-300">
                            <i class="fas fa-check-circle text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="flex items-center text-sm text-yellow-600">
                            <i class="fas fa-percentage mr-1"></i>
                            <span><?php echo $stats['total_agendamentos'] > 0 ? round((($stats['concluidos'] ?? 0) / $stats['total_agendamentos']) * 100) : 0; ?>% taxa</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Receita -->
            <div class="group bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-purple-200 transform hover:-translate-y-1">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Receita</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">€ <?php echo number_format($stats['receita'] ?? 0, 2, ',', '.'); ?></p>
                            <p class="text-xs text-gray-500 mt-1">Ganhos do mês</p>
                        </div>
                        <div class="w-14 h-14 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition duration-300">
                            <i class="fas fa-euro-sign text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="flex items-center text-sm text-purple-600">
                            <i class="fas fa-coins mr-1"></i>
                            <span>€ <?php echo number_format(($stats['receita'] ?? 0) / max(($stats['concluidos'] ?? 1), 1), 2, ',', '.'); ?>/serviço</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Coluna Principal - Calendário -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900">
                            <i class="fas fa-calendar-alt mr-2 text-barbershop-gold"></i>
                            Meu Calendário
                        </h2>
                        
                        <!-- Ações Rápidas do Calendário -->
                        <div class="flex space-x-2">
                            <button id="btn-add-block" 
                                    class="btn btn-sm bg-red-500 hover:bg-red-600 text-white">
                                <i class="fas fa-times-circle mr-1"></i>
                                Marcar Indisponível
                            </button>
                            
                            <button onclick="calendar.today()" 
                                    class="btn btn-sm btn-outline">
                                <i class="fas fa-calendar-day mr-1"></i>
                                Hoje
                            </button>
                        </div>
                    </div>

                    <!-- Legenda -->
                    <div class="flex flex-wrap gap-4 mb-4 text-sm">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-yellow-500 rounded mr-2"></div>
                            <span>Pendente</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-blue-500 rounded mr-2"></div>
                            <span>Confirmado</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded mr-2"></div>
                            <span>Em Curso</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-gray-500 rounded mr-2"></div>
                            <span>Concluído</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-red-500 rounded mr-2"></div>
                            <span>Indisponível</span>
                        </div>
                    </div>

                    <!-- Calendário FullCalendar -->
                    <div id="calendar"></div>
                </div>
            </div>

            <!-- Sidebar - Agendamentos de Hoje -->
            <div class="space-y-6">
                <!-- Agendamentos de Hoje -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        <i class="fas fa-clock mr-2 text-barbershop-gold"></i>
                        Agendamentos de Hoje
                    </h3>

                    <?php if (empty($agendamentos_hoje)): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-calendar-check text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">Não tem agendamentos para hoje</p>
                            <p class="text-sm text-gray-400">Aproveite para descansar!</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($agendamentos_hoje as $agendamento): ?>
                            <div class="appointment-card <?php echo $agendamento['status']; ?> p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900">
                                            <?php echo esc($agendamento['cliente_nome'] ?? 'Cliente'); ?>
                                        </h4>
                                        <p class="text-sm text-gray-600">
                                            <?php echo esc($agendamento['servico_nome']); ?>
                                        </p>
                                    </div>
                                    
                                    <div class="text-right">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo date('H:i', strtotime($agendamento['data_hora'])); ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?php echo $agendamento['duracao_minutos']; ?> min
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="badge badge-<?php echo $agendamento['status'] === 'concluido' ? 'success' : ($agendamento['status'] === 'cancelado' ? 'error' : 'info'); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $agendamento['status'])); ?>
                                    </span>

                                    <?php if (in_array($agendamento['status'], ['pendente', 'confirmado'])): ?>
                                    <div class="flex space-x-1">
                                        <button onclick="updateAppointmentStatus(<?php echo $agendamento['id']; ?>, 'em_curso')"
                                                class="btn btn-sm bg-green-500 hover:bg-green-600 text-white"
                                                title="Iniciar atendimento">
                                            <i class="fas fa-play"></i>
                                        </button>
                                        
                                        <button onclick="updateAppointmentStatus(<?php echo $agendamento['id']; ?>, 'cancelado')"
                                                class="btn btn-sm bg-red-500 hover:bg-red-600 text-white"
                                                title="Cancelar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <?php elseif ($agendamento['status'] === 'em_curso'): ?>
                                    <button onclick="updateAppointmentStatus(<?php echo $agendamento['id']; ?>, 'concluido')"
                                            class="btn btn-sm bg-gray-600 hover:bg-gray-700 text-white"
                                            title="Marcar como concluído">
                                        <i class="fas fa-check mr-1"></i>
                                        Concluir
                                    </button>
                                    <?php endif; ?>
                                </div>

                                <?php if ($agendamento['cliente_telefone']): ?>
                                <div class="mt-2 pt-2 border-t border-gray-200">
                                    <a href="tel:<?php echo esc($agendamento['cliente_telefone']); ?>" 
                                       class="text-sm text-barbershop-gold hover:underline">
                                        <i class="fas fa-phone mr-1"></i>
                                        <?php echo esc($agendamento['cliente_telefone']); ?>
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Ações Rápidas -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        <i class="fas fa-bolt mr-2 text-barbershop-gold"></i>
                        Ações Rápidas
                    </h3>

                    <div class="space-y-3">
                        <button onclick="markDayUnavailable()" 
                                class="quick-action w-full p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 hover:bg-red-100 text-left">
                            <i class="fas fa-ban mr-3"></i>
                            Marcar Dia como Indisponível
                        </button>

                        <button onclick="viewWeeklySchedule()" 
                                class="quick-action w-full p-3 bg-blue-50 border border-blue-200 rounded-lg text-blue-700 hover:bg-blue-100 text-left">
                            <i class="fas fa-calendar-week mr-3"></i>
                            Ver Horários da Semana
                        </button>

                        <button onclick="generateReport()" 
                                class="quick-action w-full p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 hover:bg-green-100 text-left">
                            <i class="fas fa-chart-bar mr-3"></i>
                            Relatório do Mês
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para marcar indisponibilidade -->
<div id="block-modal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black bg-opacity-50"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Marcar Período Indisponível</h3>
            
            <form id="block-form">
                <div class="space-y-4">
                    <div class="form-group">
                        <label class="form-label">Data/Hora Início</label>
                        <input type="datetime-local" id="block-start" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Data/Hora Fim</label>
                        <input type="datetime-local" id="block-end" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Motivo</label>
                        <select id="block-type" class="form-select" required>
                            <option value="folga">Folga</option>
                            <option value="ferias">Férias</option>
                            <option value="doenca">Doença</option>
                            <option value="formacao">Formação</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Observações</label>
                        <textarea id="block-notes" class="form-textarea" rows="2" placeholder="Opcional"></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeBlockModal()" class="btn btn-secondary">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let calendar;

// Inicializar quando página carregar
document.addEventListener('DOMContentLoaded', function() {
    initializeCalendar();
    
    // Configurar form de bloqueio
    document.getElementById('block-form').addEventListener('submit', handleBlockSubmit);
});

/**
 * Inicializar FullCalendar
 */
function initializeCalendar() {
    const calendarEl = document.getElementById('calendar');
    
    calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'pt',
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        height: 'auto',
        slotMinTime: '08:00:00',
        slotMaxTime: '20:00:00',
        businessHours: {
            daysOfWeek: [1, 2, 3, 4, 5, 6],
            startTime: '<?php echo $barbeiro_data['horario_inicio'] ?? '09:00'; ?>',
            endTime: '<?php echo $barbeiro_data['horario_fim'] ?? '18:00'; ?>'
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            // Carregar eventos via AJAX
            loadCalendarEvents(fetchInfo.startStr, fetchInfo.endStr, successCallback, failureCallback);
        },
        eventClick: function(info) {
            handleEventClick(info);
        },
        dateClick: function(info) {
            // Sugerir criar bloqueio para data clicada
            if (info.date >= new Date()) {
                const date = info.dateStr.split('T')[0];
                document.getElementById('block-start').value = date + 'T09:00';
                document.getElementById('block-end').value = date + 'T18:00';
                openBlockModal();
            }
        }
    });
    
    calendar.render();
}

/**
 * Carregar eventos do calendário via AJAX
 */
function loadCalendarEvents(start, end, successCallback, failureCallback) {
    fetch('<?php echo get_base_url('api/barbeiro_events.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            barbeiro_id: <?php echo $barbeiro['id']; ?>,
            start: start,
            end: end
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            successCallback(data.events);
        } else {
            failureCallback(data.message);
        }
    })
    .catch(error => {
        console.error('Erro ao carregar eventos:', error);
        failureCallback(error);
    });
}

/**
 * Lidar com clique em evento
 */
function handleEventClick(info) {
    const event = info.event;
    
    if (event.extendedProps.type === 'appointment') {
        // Mostrar detalhes do agendamento
        Barbershop.notifications.show(`
            Cliente: ${event.extendedProps.cliente_nome}<br>
            Serviço: ${event.title}<br>
            Status: ${event.extendedProps.status}
        `, 'info', 0);
    } else if (event.extendedProps.type === 'block') {
        // Permitir remover bloqueio
        if (confirm('Deseja remover este período de indisponibilidade?')) {
            removeBlock(event.extendedProps.block_id);
        }
    }
}

/**
 * Atualizar status de agendamento
 */
function updateAppointmentStatus(appointmentId, newStatus) {
    if (!confirm(`Confirma alterar o status para "${newStatus.replace('_', ' ')}"?`)) {
        return;
    }
    
    fetch('<?php echo get_base_url('api/update_appointment_status.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            appointment_id: appointmentId,
            status: newStatus,
            barbeiro_id: <?php echo $barbeiro['id']; ?>
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Barbershop.notifications.show('Status atualizado com sucesso!', 'success');
            // Recarregar página para atualizar lista
            setTimeout(() => location.reload(), 1000);
        } else {
            Barbershop.notifications.show(data.message || 'Erro ao atualizar status', 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Barbershop.notifications.show('Erro de conexão', 'error');
    });
}

/**
 * Abrir modal de bloqueio
 */
function openBlockModal() {
    document.getElementById('block-modal').classList.remove('hidden');
}

/**
 * Fechar modal de bloqueio
 */
function closeBlockModal() {
    document.getElementById('block-modal').classList.add('hidden');
    document.getElementById('block-form').reset();
}

/**
 * Processar formulário de bloqueio
 */
function handleBlockSubmit(e) {
    e.preventDefault();
    
    const formData = {
        barbeiro_id: <?php echo $barbeiro['id']; ?>,
        data_inicio: document.getElementById('block-start').value + ':00',
        data_fim: document.getElementById('block-end').value + ':00',
        tipo: document.getElementById('block-type').value,
        motivo: document.getElementById('block-notes').value || document.getElementById('block-type').value
    };
    
    fetch('<?php echo get_base_url('api/barbeiro_toggle_block.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Barbershop.notifications.show('Período marcado como indisponível!', 'success');
            closeBlockModal();
            calendar.refetchEvents();
        } else {
            Barbershop.notifications.show(data.message || 'Erro ao criar bloqueio', 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Barbershop.notifications.show('Erro de conexão', 'error');
    });
}

/**
 * Ações rápidas
 */
function markDayUnavailable() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('block-start').value = today + 'T09:00';
    document.getElementById('block-end').value = today + 'T18:00';
    document.getElementById('block-type').value = 'folga';
    openBlockModal();
}

function viewWeeklySchedule() {
    calendar.changeView('timeGridWeek');
}

function generateReport() {
    window.open('<?php echo get_base_url('barbeiro/reports.php'); ?>', '_blank');
}

// Event listeners para botões
document.getElementById('btn-add-block').addEventListener('click', openBlockModal);
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>