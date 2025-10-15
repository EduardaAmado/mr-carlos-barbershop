<?php
/**
 * API - Atualizar status de agendamentos do barbeiro
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Permitir que barbeiros atualizem status dos agendamentos
 * 
 * REQUEST (POST JSON):
 * {
 *   "agendamento_id": 15,
 *   "status": "confirmado",
 *   "notas": "Cliente confirmou presença"
 * }
 * 
 * Status válidos: agendado, confirmado, em_andamento, concluido, cancelado, falta
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Verificar se é requisição AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Requisição inválida']);
    exit;
}

// Verificar se barbeiro está logado
if (!is_logged_in('barbeiro')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

try {
    $barbeiro = get_logged_user('barbeiro');
    
    // Obter dados JSON da requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Dados JSON inválidos');
    }
    
    $agendamento_id = intval($input['agendamento_id'] ?? 0);
    $novo_status = trim($input['status'] ?? '');
    $notas = trim($input['notas'] ?? '');
    
    if (!$agendamento_id) {
        throw new Exception('ID do agendamento é obrigatório');
    }
    
    // Status válidos e suas transições
    $status_validos = ['agendado', 'confirmado', 'em_andamento', 'concluido', 'cancelado', 'falta'];
    
    if (!in_array($novo_status, $status_validos)) {
        throw new Exception('Status inválido');
    }
    
    // Buscar agendamento e verificar se pertence ao barbeiro
    $agendamento_result = execute_prepared_query(
        "SELECT a.*, c.nome as cliente_nome, c.telefone as cliente_telefone, 
                s.nome as servico_nome, s.duracao, s.preco
         FROM agendamentos a
         LEFT JOIN clientes c ON a.cliente_id = c.id
         LEFT JOIN servicos s ON a.servico_id = s.id
         WHERE a.id = ? AND a.barbeiro_id = ?",
        [$agendamento_id, $barbeiro['id']],
        'ii'
    );
    
    if (!$agendamento_result || !($agendamento = $agendamento_result->fetch_assoc())) {
        throw new Exception('Agendamento não encontrado');
    }
    
    $status_atual = $agendamento['status'];
    
    // Validar transições de status
    $transicoes_validas = [
        'agendado' => ['confirmado', 'em_andamento', 'cancelado', 'falta'],
        'confirmado' => ['em_andamento', 'cancelado', 'falta'],
        'em_andamento' => ['concluido', 'cancelado'],
        'concluido' => [], // Status final
        'cancelado' => ['agendado'], // Pode reagendar
        'falta' => ['agendado'] // Pode reagendar
    ];
    
    if (!in_array($novo_status, $transicoes_validas[$status_atual])) {
        throw new Exception("Não é possível alterar de '{$status_atual}' para '{$novo_status}'");
    }
    
    // Validações específicas por status
    switch ($novo_status) {
        case 'em_andamento':
            // Verificar se é o horário apropriado (não muito antes nem muito depois)
            $agora = new DateTime();
            $data_agendamento = new DateTime($agendamento['data_hora']);
            $diferenca = $agora->getTimestamp() - $data_agendamento->getTimestamp();
            
            // Permitir iniciar até 30 min antes ou 2h depois do horário
            if ($diferenca < -1800 || $diferenca > 7200) {
                throw new Exception('Só é possível iniciar o atendimento próximo ao horário agendado');
            }
            break;
            
        case 'concluido':
            // Pode adicionar lógica de finalização (pagamento, etc.)
            if ($status_atual !== 'em_andamento') {
                throw new Exception('Só é possível finalizar um atendimento que está em andamento');
            }
            break;
            
        case 'cancelado':
            // Verificar se não é muito tarde para cancelar
            $data_agendamento = new DateTime($agendamento['data_hora']);
            $agora = new DateTime();
            
            if ($data_agendamento <= $agora) {
                // Se já passou do horário, deve marcar como falta em vez de cancelar
                if ($status_atual === 'agendado' || $status_atual === 'confirmado') {
                    $novo_status = 'falta';
                    $notas = $notas ? $notas : 'Cliente não compareceu';
                }
            }
            break;
    }
    
    // Atualizar agendamento
    execute_prepared_query(
        "UPDATE agendamentos SET status = ?, notas = ? WHERE id = ? AND barbeiro_id = ?",
        [$novo_status, $notas, $agendamento_id, $barbeiro['id']],
        'ssii'
    );
    
    // Log da alteração
    error_log("Status atualizado - Agendamento: {$agendamento_id}, De: {$status_atual}, Para: {$novo_status}, Barbeiro: {$barbeiro['id']}");
    
    // Preparar dados de resposta
    $status_label = [
        'agendado' => 'Agendado',
        'confirmado' => 'Confirmado',
        'em_andamento' => 'Em Andamento',
        'concluido' => 'Concluído',
        'cancelado' => 'Cancelado',
        'falta' => 'Cliente Faltou'
    ];
    
    $status_color = [
        'agendado' => 'blue',
        'confirmado' => 'green',
        'em_andamento' => 'yellow',
        'concluido' => 'purple',
        'cancelado' => 'red',
        'falta' => 'gray'
    ];
    
    // Retornar resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => "Status alterado para '{$status_label[$novo_status]}' com sucesso!",
        'agendamento' => [
            'id' => $agendamento_id,
            'status' => $novo_status,
            'status_label' => $status_label[$novo_status],
            'status_color' => $status_color[$novo_status],
            'notas' => $notas,
            'cliente_nome' => $agendamento['cliente_nome'],
            'servico_nome' => $agendamento['servico_nome'],
            'data_hora' => $agendamento['data_hora']
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Erro na API de atualização de status: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>