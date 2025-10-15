<?php
/**
 * Página - Meus Agendamentos
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Exibir histórico completo de agendamentos do cliente
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Verificar se cliente está logado
if (!is_logged_in('cliente')) {
    safe_redirect(get_base_url('pages/login.php'));
}

$user = get_logged_user('cliente');
$errors = [];
$success_message = '';

// Obter todos os agendamentos do cliente
try {
    $stmt = $pdo->prepare(
        "SELECT a.*, b.nome as barbeiro_nome, s.nome as servico_nome, s.preco, s.categoria
         FROM agendamentos a 
         LEFT JOIN barbeiros b ON a.barbeiro_id = b.id 
         LEFT JOIN servicos s ON a.servico_id = s.id 
         WHERE a.cliente_id = ? 
         ORDER BY a.data_hora DESC"
    );
    $stmt->execute([$user['id']]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Erro ao obter agendamentos: " . $e->getMessage());
    $agendamentos = [];
    $errors[] = "Erro ao carregar agendamentos. Tente novamente.";
}

$page_title = 'Meus Agendamentos - ' . SITE_NAME;
include_once __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header da página -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Meus Agendamentos</h1>
                <p class="text-gray-600 mt-2">Histórico completo dos seus agendamentos</p>
            </div>
            <div>
                <a href="<?php echo get_base_url('pages/agendar.php'); ?>" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                    <i class="fas fa-plus mr-2"></i>Novo Agendamento
                </a>
            </div>
        </div>
    </div>

    <!-- Mensagens de erro/sucesso -->
    <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Erro!</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Lista de Agendamentos -->
    <div class="bg-white rounded-lg shadow-sm">
        <?php if (empty($agendamentos)): ?>
            <!-- Estado vazio -->
            <div class="text-center py-12">
                <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum agendamento encontrado</h3>
                <p class="text-gray-600 mb-6">Você ainda não possui agendamentos.</p>
                <a href="<?php echo get_base_url('pages/agendar.php'); ?>" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                    Fazer meu primeiro agendamento
                </a>
            </div>
        <?php else: ?>
            <!-- Lista de agendamentos -->
            <div class="divide-y divide-gray-200">
                <?php foreach ($agendamentos as $agendamento): 
                    $data_agendamento = new DateTime($agendamento['data_hora']);
                    $data_fim = new DateTime($agendamento['data_fim']);
                    $status = $agendamento['status'];
                    
                    // Definir cores por status
                    $status_colors = [
                        'agendado' => 'bg-blue-100 text-blue-800',
                        'confirmado' => 'bg-green-100 text-green-800',
                        'em_andamento' => 'bg-yellow-100 text-yellow-800',
                        'concluido' => 'bg-gray-100 text-gray-800',
                        'cancelado' => 'bg-red-100 text-red-800',
                        'falta' => 'bg-red-100 text-red-800'
                    ];
                    $status_color = $status_colors[$status] ?? 'bg-gray-100 text-gray-800';
                    
                    // Traduzir status
                    $status_names = [
                        'agendado' => 'Agendado',
                        'confirmado' => 'Confirmado',
                        'em_andamento' => 'Em Andamento',
                        'concluido' => 'Concluído',
                        'cancelado' => 'Cancelado',
                        'falta' => 'Falta'
                    ];
                    $status_name = $status_names[$status] ?? $status;
                ?>
                    <div class="p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <!-- Informações principais -->
                            <div class="flex-1">
                                <div class="flex items-center space-x-4 mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <?php echo htmlspecialchars($agendamento['servico_nome']); ?>
                                    </h3>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $status_color; ?>">
                                        <?php echo $status_name; ?>
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar mr-2 text-gray-400"></i>
                                        <span><?php echo $data_agendamento->format('d/m/Y'); ?></span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-clock mr-2 text-gray-400"></i>
                                        <span>
                                            <?php echo $data_agendamento->format('H:i'); ?> - 
                                            <?php echo $data_fim->format('H:i'); ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-user mr-2 text-gray-400"></i>
                                        <span><?php echo htmlspecialchars($agendamento['barbeiro_nome']); ?></span>
                                    </div>
                                </div>
                                
                                <?php if (!empty($agendamento['observacoes'])): ?>
                                    <div class="mt-2 text-sm text-gray-600">
                                        <i class="fas fa-comment mr-2 text-gray-400"></i>
                                        <?php echo htmlspecialchars($agendamento['observacoes']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Preço e ações -->
                            <div class="text-right">
                                <div class="text-lg font-semibold text-gray-900 mb-2">
                                    €<?php echo number_format($agendamento['preco'], 2, ',', '.'); ?>
                                </div>
                                
                                <?php if ($status === 'agendado' || $status === 'confirmado'): ?>
                                    <button class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        Cancelar
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>