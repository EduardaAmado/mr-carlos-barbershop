<?php
/**
 * Página de agendamento online
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Interface intuitiva para clientes agendarem cortes
 * 
 * FUNCIONALIDADES:
 * - Seleção passo a passo (barbeiro → serviço → data → horário)
 * - Validação em tempo real via AJAX
 * - Interface visual clara com instruções
 * - Compatível com dispositivos móveis
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Verificar se cliente está logado
if (!is_logged_in('cliente')) {
    // Redirecionar para login com redirect de volta
    $current_url = urlencode($_SERVER['REQUEST_URI']);
    safe_redirect(get_base_url("pages/login.php?redirect={$current_url}"));
}

$user = get_logged_user('cliente');

// Obter barbeiros ativos
try {
    $stmt = $pdo->prepare("SELECT id, nome, especialidades, foto FROM barbeiros WHERE ativo = 1 ORDER BY nome");
    $stmt->execute();
    $barbeiros = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Erro ao obter barbeiros: " . $e->getMessage());
    $barbeiros = [];
}

// Obter serviços ativos
try {
    $stmt = $pdo->prepare("SELECT id, nome, descricao_curta, duracao_minutos, preco, categoria 
         FROM servicos WHERE ativo = 1 ORDER BY ordem_exibicao, nome");
    $stmt->execute();
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Erro ao obter serviços: " . $e->getMessage());
    $servicos = [];
}

// Pré-selecionar serviço se veio da página de serviços
$servico_selecionado = $_GET['servico_id'] ?? null;

$page_title = 'Agendar Corte - ' . SITE_NAME;
include_once __DIR__ . '/../includes/header.php';
?>

<!-- CSS específico para agendamento -->
<style>
.step-indicator {
    position: relative;
}

.step-indicator::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 100%;
    width: 50px;
    height: 2px;
    background: #E5E7EB;
    z-index: -1;
}

.step-indicator.active::after {
    background: var(--color-accent);
}

.step-indicator:last-child::after {
    display: none;
}

.card-selectable {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.card-selectable:hover {
    border-color: var(--color-accent);
    transform: translateY(-2px);
}

.card-selectable.selected {
    border-color: var(--color-accent);
    background: rgba(201, 162, 39, 0.1);
}

.time-slot {
    padding: 8px 16px;
    border: 2px solid #E5E7EB;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
}

.time-slot:hover {
    border-color: var(--color-accent);
    background: rgba(201, 162, 39, 0.1);
}

.time-slot.selected {
    background: var(--color-accent);
    border-color: var(--color-accent);
    color: black;
    font-weight: 600;
}

.time-slot.unavailable {
    background: #F3F4F6;
    color: #9CA3AF;
    cursor: not-allowed;
    opacity: 0.5;
}

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.loading-overlay.hidden {
    display: none !important;
}

.spinner {
    width: 32px;
    height: 32px;
    border: 3px solid #E5E7EB;
    border-top-color: var(--color-accent);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4">
        <!-- Cabeçalho -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Agendar o Seu Corte</h1>
            <p class="text-lg text-gray-600">Siga os passos para marcar o seu agendamento</p>
        </div>

        <!-- Indicador de Passos -->
        <div class="flex justify-center mb-8">
            <div class="flex items-center space-x-4 max-w-2xl w-full">
                <div class="step-indicator flex items-center">
                    <div id="step1-circle" class="w-10 h-10 rounded-full bg-barbershop-gold text-black flex items-center justify-center font-bold text-sm">
                        1
                    </div>
                    <span class="ml-2 font-medium text-gray-700">Barbeiro</span>
                </div>
                
                <div class="step-indicator flex items-center">
                    <div id="step2-circle" class="w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold text-sm">
                        2
                    </div>
                    <span class="ml-2 font-medium text-gray-500">Serviço</span>
                </div>
                
                <div class="step-indicator flex items-center">
                    <div id="step3-circle" class="w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold text-sm">
                        3
                    </div>
                    <span class="ml-2 font-medium text-gray-500">Data</span>
                </div>
                
                <div class="step-indicator flex items-center">
                    <div id="step4-circle" class="w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold text-sm">
                        4
                    </div>
                    <span class="ml-2 font-medium text-gray-500">Horário</span>
                </div>
            </div>
        </div>

        <!-- Formulário de Agendamento -->
        <div class="max-w-4xl mx-auto">
            <form id="booking-form" class="space-y-8">
                <!-- Passo 1: Escolher Barbeiro -->
                <div id="step1" class="card">
                    <div class="card-header">
                        <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                            <i class="fas fa-user-tie mr-3 text-barbershop-gold"></i>
                            1. Escolha o seu barbeiro
                        </h2>
                        <p class="text-gray-600 mt-1">Selecione o profissional que preferir</p>
                    </div>
                    <div class="card-body">
                        <?php if (empty($barbeiros)): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-exclamation-triangle text-4xl text-yellow-500 mb-4"></i>
                                <p class="text-lg text-gray-600">Não há barbeiros disponíveis no momento.</p>
                                <p class="text-sm text-gray-500">Contacte-nos para mais informações.</p>
                            </div>
                        <?php else: ?>
                            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <?php foreach ($barbeiros as $barbeiro): ?>
                                <div class="card card-selectable" 
                                     data-barbeiro-id="<?php echo $barbeiro['id']; ?>"
                                     onclick="selectBarbeiro(<?php echo $barbeiro['id']; ?>, '<?php echo esc($barbeiro['nome']); ?>')">
                                    <div class="p-4 text-center">
                                        <?php if ($barbeiro['foto']): ?>
                                            <img src="<?php echo get_base_url('uploads/barbeiros/' . $barbeiro['foto']); ?>" 
                                                 alt="<?php echo esc($barbeiro['nome']); ?>"
                                                 class="w-20 h-20 rounded-full mx-auto mb-3 object-cover">
                                        <?php else: ?>
                                            <div class="w-20 h-20 rounded-full mx-auto mb-3 bg-gray-300 flex items-center justify-center">
                                                <i class="fas fa-user text-2xl text-gray-500"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <h3 class="font-bold text-lg mb-2"><?php echo esc($barbeiro['nome']); ?></h3>
                                        
                                        <?php if ($barbeiro['especialidades']): ?>
                                            <p class="text-sm text-gray-600 mb-3"><?php echo esc($barbeiro['especialidades']); ?></p>
                                        <?php endif; ?>
                                        
                                        <div class="text-barbershop-gold">
                                            <i class="fas fa-check-circle"></i>
                                            <span class="text-sm font-medium">Selecionar</span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Passo 2: Escolher Serviço -->
                <div id="step2" class="card opacity-50 pointer-events-none">
                    <div class="card-header">
                        <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                            <i class="fas fa-cut mr-3 text-barbershop-gold"></i>
                            2. Escolha o serviço
                        </h2>
                        <p class="text-gray-600 mt-1">Que tipo de corte deseja?</p>
                    </div>
                    <div class="card-body">
                        <?php if (empty($servicos)): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-exclamation-triangle text-4xl text-yellow-500 mb-4"></i>
                                <p class="text-lg text-gray-600">Não há serviços disponíveis no momento.</p>
                            </div>
                        <?php else: ?>
                            <div class="grid md:grid-cols-2 gap-4">
                                <?php foreach ($servicos as $servico): ?>
                                <div class="card card-selectable" 
                                     data-servico-id="<?php echo $servico['id']; ?>"
                                     data-duracao="<?php echo $servico['duracao_minutos']; ?>"
                                     onclick="selectServico(<?php echo $servico['id']; ?>, '<?php echo esc($servico['nome']); ?>', <?php echo $servico['duracao_minutos']; ?>, <?php echo $servico['preco']; ?>)"
                                     <?php echo $servico_selecionado == $servico['id'] ? 'data-preselected="true"' : ''; ?>>
                                    <div class="p-4">
                                        <div class="flex justify-between items-start mb-3">
                                            <h3 class="font-bold text-lg"><?php echo esc($servico['nome']); ?></h3>
                                            <span class="text-2xl font-bold text-barbershop-gold">
                                                <?php echo format_price($servico['preco']); ?>
                                            </span>
                                        </div>
                                        
                                        <?php if ($servico['descricao_curta']): ?>
                                            <p class="text-gray-600 mb-3"><?php echo esc($servico['descricao_curta']); ?></p>
                                        <?php endif; ?>
                                        
                                        <div class="flex justify-between items-center text-sm text-gray-500">
                                            <span>
                                                <i class="fas fa-clock mr-1"></i>
                                                <?php echo $servico['duracao_minutos']; ?> minutos
                                            </span>
                                            <span class="bg-barbershop-gold text-black px-2 py-1 rounded text-xs font-medium">
                                                <?php echo ucfirst($servico['categoria']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Passo 3: Escolher Data -->
                <div id="step3" class="card opacity-50 pointer-events-none">
                    <div class="card-header">
                        <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                            <i class="fas fa-calendar-alt mr-3 text-barbershop-gold"></i>
                            3. Escolha a data
                        </h2>
                        <p class="text-gray-600 mt-1">Selecione o dia do seu agendamento</p>
                    </div>
                    <div class="card-body">
                        <div class="max-w-md mx-auto">
                            <input type="text" 
                                   id="booking-date" 
                                   class="form-input text-center text-lg font-medium"
                                   placeholder="Clique para selecionar a data"
                                   readonly>
                            <p class="text-sm text-gray-500 mt-2 text-center">
                                <i class="fas fa-info-circle mr-1"></i>
                                Disponível de Segunda a Sábado
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Passo 4: Escolher Horário -->
                <div id="step4" class="card opacity-50 pointer-events-none">
                    <div class="card-header">
                        <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                            <i class="fas fa-clock mr-3 text-barbershop-gold"></i>
                            4. Escolha o horário
                        </h2>
                        <p class="text-gray-600 mt-1">Horários disponíveis para o dia selecionado</p>
                    </div>
                    <div class="card-body relative">
                        <div id="loading-times" class="loading-overlay hidden">
                            <div class="spinner"></div>
                        </div>
                        
                        <div id="available-times" class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                            <!-- Horários serão carregados via AJAX -->
                        </div>
                        
                        <div id="no-times-message" class="text-center py-8 hidden">
                            <i class="fas fa-calendar-times text-4xl text-gray-400 mb-4"></i>
                            <p class="text-lg text-gray-600">Não há horários disponíveis para esta data.</p>
                            <p class="text-sm text-gray-500">Tente selecionar outro dia.</p>
                        </div>
                    </div>
                </div>

                <!-- Resumo e Confirmação -->
                <div id="step5" class="card opacity-50 pointer-events-none">
                    <div class="card-header">
                        <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                            <i class="fas fa-check-circle mr-3 text-barbershop-gold"></i>
                            Confirmar agendamento
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Resumo -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Resumo do agendamento:</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Barbeiro:</span>
                                        <span id="summary-barbeiro" class="font-medium">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Serviço:</span>
                                        <span id="summary-servico" class="font-medium">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Data:</span>
                                        <span id="summary-data" class="font-medium">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Horário:</span>
                                        <span id="summary-horario" class="font-medium">-</span>
                                    </div>
                                    <div class="border-t pt-3">
                                        <div class="flex justify-between text-lg font-bold">
                                            <span>Total:</span>
                                            <span id="summary-preco" class="text-barbershop-gold">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Notas opcionais -->
                            <div>
                                <label for="booking-notes" class="form-label">
                                    Notas opcionais
                                </label>
                                <textarea 
                                    id="booking-notes" 
                                    class="form-textarea"
                                    rows="4"
                                    placeholder="Alguma observação especial para o barbeiro? (opcional)">
                                </textarea>
                            </div>
                        </div>
                        
                        <!-- Botão de confirmação -->
                        <div class="mt-8 text-center">
                            <button type="submit" id="confirm-booking" class="btn btn-primary btn-lg px-12">
                                <i class="fas fa-calendar-check mr-2"></i>
                                Confirmar Agendamento
                            </button>
                            <p class="text-sm text-gray-500 mt-2">
                                Receberá uma confirmação por email
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmação -->
<div id="success-modal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black bg-opacity-50"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-2xl text-green-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Agendamento Confirmado!</h3>
                <p class="text-gray-600 mb-6">
                    O seu agendamento foi registado com sucesso. Receberá uma confirmação por email.
                </p>
                <button onclick="window.location.href='<?php echo get_base_url('pages/perfil.php'); ?>'" 
                        class="btn btn-primary">
                    Ver Meus Agendamentos
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Estado do formulário de agendamento
let bookingState = {
    barbeiro_id: null,
    barbeiro_nome: null,
    servico_id: null,
    servico_nome: null,
    servico_duracao: null,
    servico_preco: null,
    data: null,
    horario: null
};

// Inicializar página
document.addEventListener('DOMContentLoaded', function() {
    // Pré-selecionar serviço se veio da URL
    <?php if ($servico_selecionado): ?>
        const preselectedCard = document.querySelector('[data-servico-id="<?php echo $servico_selecionado; ?>"]');
        if (preselectedCard) {
            // Será processado quando o passo 2 for ativado
            preselectedCard.setAttribute('data-preselected', 'true');
        }
    <?php endif; ?>
    
    // Inicializar Flatpickr para data
    initDatePicker();
});

/**
 * Selecionar barbeiro (Passo 1)
 */
function selectBarbeiro(id, nome) {
    // Remover seleção anterior
    document.querySelectorAll('[data-barbeiro-id]').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Selecionar novo
    const selectedCard = document.querySelector(`[data-barbeiro-id="${id}"]`);
    selectedCard.classList.add('selected');
    
    // Atualizar estado
    bookingState.barbeiro_id = id;
    bookingState.barbeiro_nome = nome;
    
    // Ativar próximo passo
    activateStep(2);
    updateStepIndicator(1, true);
    
    // Auto-selecionar serviço se pré-selecionado
    setTimeout(() => {
        const preselected = document.querySelector('[data-preselected="true"]');
        if (preselected) {
            preselected.click();
        }
    }, 300);
}

/**
 * Selecionar serviço (Passo 2)  
 */
function selectServico(id, nome, duracao, preco) {
    // Remover seleção anterior
    document.querySelectorAll('[data-servico-id]').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Selecionar novo
    const selectedCard = document.querySelector(`[data-servico-id="${id}"]`);
    selectedCard.classList.add('selected');
    
    // Atualizar estado
    bookingState.servico_id = id;
    bookingState.servico_nome = nome;
    bookingState.servico_duracao = duracao;
    bookingState.servico_preco = preco;
    
    // Ativar próximo passo
    activateStep(3);
    updateStepIndicator(2, true);
}

/**
 * Inicializar seletor de data
 */
function initDatePicker() {
    flatpickr('#booking-date', {
        locale: 'pt',
        dateFormat: 'Y-m-d',
        minDate: 'today',
        maxDate: new Date().fp_incr(60), // 60 dias
        disable: [
            // Desabilitar domingos
            function(date) {
                return date.getDay() === 0;
            }
        ],
        onChange: function(selectedDates, dateStr) {
            if (dateStr) {
                bookingState.data = dateStr;
                loadAvailableTimes();
                activateStep(4);
                updateStepIndicator(3, true);
            }
        }
    });
}

/**
 * Carregar horários disponíveis via AJAX
 */
function loadAvailableTimes() {
    const container = document.getElementById('available-times');
    const loading = document.getElementById('loading-times');
    const noTimesMsg = document.getElementById('no-times-message');
    
    // Mostrar loading
    loading.classList.remove('hidden');
    container.innerHTML = '';
    noTimesMsg.classList.add('hidden');
    
    // Preparar dados para requisição
    const requestData = {
        barbeiro_id: bookingState.barbeiro_id,
        servico_id: bookingState.servico_id,
        data: bookingState.data,
        duracao: bookingState.servico_duracao
    };
    
    // Fazer requisição AJAX
    fetch('<?php echo get_base_url('api/get_availability.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        loading.classList.add('hidden');
        
        if (data.success && data.slots && data.slots.length > 0) {
            // Renderizar horários disponíveis
            data.slots.forEach(slot => {
                const timeButton = document.createElement('button');
                timeButton.type = 'button';
                timeButton.className = 'time-slot';
                timeButton.textContent = slot.time;
                timeButton.onclick = () => selectTime(slot.time);
                
                if (!slot.available) {
                    timeButton.classList.add('unavailable');
                    timeButton.disabled = true;
                }
                
                container.appendChild(timeButton);
            });
        } else {
            // Mostrar mensagem de não há horários
            noTimesMsg.classList.remove('hidden');
        }
    })
    .catch(error => {
        console.error('Erro ao carregar horários:', error);
        loading.classList.add('hidden');
        Barbershop.notifications.show('Erro ao carregar horários disponíveis', 'error');
    })
    .finally(() => {
        // Garantir que o loading seja sempre removido
        loading.classList.add('hidden');
    });
}

/**
 * Selecionar horário
 */
function selectTime(time) {
    // Remover seleção anterior
    document.querySelectorAll('.time-slot').forEach(slot => {
        slot.classList.remove('selected');
    });
    
    // Selecionar novo
    event.target.classList.add('selected');
    
    // Atualizar estado
    bookingState.horario = time;
    
    // Ativar confirmação e atualizar resumo
    activateStep(5);
    updateStepIndicator(4, true);
    updateSummary();
}

/**
 * Ativar passo do formulário
 */
function activateStep(stepNumber) {
    const step = document.getElementById(`step${stepNumber}`);
    step.classList.remove('opacity-50', 'pointer-events-none');
    
    // Scroll suave para o passo
    setTimeout(() => {
        step.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }, 100);
}

/**
 * Atualizar indicador visual dos passos
 */
function updateStepIndicator(stepNumber, completed) {
    const circle = document.getElementById(`step${stepNumber}-circle`);
    const nextCircle = document.getElementById(`step${stepNumber + 1}-circle`);
    
    if (completed) {
        circle.classList.remove('bg-gray-300', 'text-gray-600');
        circle.classList.add('bg-barbershop-gold', 'text-black');
        circle.innerHTML = '<i class="fas fa-check"></i>';
        
        // Ativar próximo passo visualmente
        if (nextCircle) {
            nextCircle.classList.remove('bg-gray-300', 'text-gray-600');
            nextCircle.classList.add('bg-barbershop-gold', 'text-black');
        }
    }
}

/**
 * Atualizar resumo do agendamento
 */
function updateSummary() {
    document.getElementById('summary-barbeiro').textContent = bookingState.barbeiro_nome;
    document.getElementById('summary-servico').textContent = bookingState.servico_nome;
    document.getElementById('summary-data').textContent = formatDate(bookingState.data);
    document.getElementById('summary-horario').textContent = bookingState.horario;
    document.getElementById('summary-preco').textContent = Barbershop.utils.formatPrice(bookingState.servico_preco);
}

/**
 * Formatar data para português
 */
function formatDate(dateStr) {
    const date = new Date(dateStr + 'T00:00:00');
    return date.toLocaleDateString('pt-PT', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

/**
 * Submeter agendamento
 */
document.getElementById('booking-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitButton = document.getElementById('confirm-booking');
    const originalText = submitButton.innerHTML;
    
    // Estado de loading
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processando...';
    
    // Preparar dados
    const bookingData = {
        ...bookingState,
        notas: document.getElementById('booking-notes').value,
        datetime: `${bookingState.data} ${bookingState.horario}`
    };
    
    // Enviar requisição
    fetch('<?php echo get_base_url('api/create_booking.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(bookingData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar modal de sucesso
            document.getElementById('success-modal').classList.remove('hidden');
        } else {
            Barbershop.notifications.show(data.message || 'Erro ao criar agendamento', 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Barbershop.notifications.show('Erro de conexão. Tente novamente.', 'error');
    })
    .finally(() => {
        // Restaurar botão
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>