<?php
/**
 * API - Eventos do calendário do barbeiro
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Retornar eventos (agendamentos + bloqueios) para FullCalendar
 * 
 * REQUEST (POST JSON):
 * {
 *   "barbeiro_id": 1,
 *   "start": "2025-10-01",
 *   "end": "2025-10-31"
 * }
 * 
 * RESPONSE JSON:
 * {
 *   "success": true,
 *   "events": [
 *     {
 *       "id": "appointment_123",
 *       "title": "Corte Clássico - João Silva",
 *       "start": "2025-10-15T10:00:00",
 *       "end": "2025-10-15T10:30:00",
 *       "className": "status-confirmado",
 *       "extendedProps": {...}
 *     }
 *   ]
 * }
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
    
    $barbeiro_id = intval($input['barbeiro_id'] ?? 0);
    $start = $input['start'] ?? '';
    $end = $input['end'] ?? '';
    
    // Verificar se o barbeiro pode ver estes dados
    if ($barbeiro_id !== $barbeiro['id']) {
        throw new Exception('Acesso negado');
    }
    
    if (!$start || !$end) {
        throw new Exception('Parâmetros de data obrigatórios');
    }
    
    global $pdo;
    $events = [];
    
    // 1. CARREGAR AGENDAMENTOS
    $stmt = $pdo->prepare("SELECT a.id, a.data_hora, a.data_fim, a.status, a.notas, a.preco_pago,
                c.nome as cliente_nome, c.telefone as cliente_telefone,
                s.nome as servico_nome, s.duracao_minutos, s.preco
         FROM agendamentos a
         LEFT JOIN clientes c ON a.cliente_id = c.id
         LEFT JOIN servicos s ON a.servico_id = s.id
         WHERE a.barbeiro_id = ? 
         AND a.data_hora >= ? 
         AND a.data_hora <= ?
         ORDER BY a.data_hora");
    $stmt->execute([$barbeiro_id, $start, $end]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Determinar cor baseada no status
            $className = 'status-' . $row['status'];
            
            // Título do evento
            $title = $row['servico_nome'];
            if ($row['cliente_nome']) {
                $title .= ' - ' . $row['cliente_nome'];
            }
            
            // Criar evento para FullCalendar
            $events[] = [
                'id' => 'appointment_' . $row['id'],
                'title' => $title,
                'start' => $row['data_hora'],
                'end' => $row['data_fim'],
                'className' => $className,
                'extendedProps' => [
                    'type' => 'appointment',
                    'appointment_id' => $row['id'],
                    'cliente_nome' => $row['cliente_nome'],
                    'cliente_telefone' => $row['cliente_telefone'],
                    'servico_nome' => $row['servico_nome'],
                    'duracao' => $row['duracao_minutos'],
                    'status' => $row['status'],
                    'preco' => $row['preco_pago'] ?? $row['preco'],
                    'notas' => $row['notas']
                ]
            ];
        }
    
    // 2. CARREGAR BLOQUEIOS
    $stmt = $pdo->prepare("SELECT id, data_inicio, data_fim, motivo, tipo
         FROM bloqueios
         WHERE barbeiro_id = ? 
         AND ativo = 1
         AND data_inicio >= ? 
         AND data_inicio <= ?
         ORDER BY data_inicio");
    $stmt->execute([$barbeiro_id, $start, $end]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Título baseado no tipo
            $title_map = [
                'folga' => '🔴 Folga',
                'ferias' => '🏖️ Férias', 
                'doenca' => '🤒 Doença',
                'formacao' => '📚 Formação',
                'outro' => '⚫ Indisponível'
            ];
            
            $title = $title_map[$row['tipo']] ?? '⚫ Indisponível';
            if ($row['motivo'] && $row['motivo'] !== $row['tipo']) {
                $title .= ' - ' . $row['motivo'];
            }
            
            // Criar evento de bloqueio
            $events[] = [
                'id' => 'block_' . $row['id'],
                'title' => $title,
                'start' => $row['data_inicio'],
                'end' => $row['data_fim'],
                'className' => 'status-blocked',
                'backgroundColor' => '#ef4444',
                'borderColor' => '#dc2626',
                'textColor' => 'white',
                'extendedProps' => [
                    'type' => 'block',
                    'block_id' => $row['id'],
                    'motivo' => $row['motivo'],
                    'tipo' => $row['tipo']
                ]
            ];
        }
    
    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'events' => $events,
        'total_agendamentos' => count(array_filter($events, function($e) { 
            return $e['extendedProps']['type'] === 'appointment'; 
        })),
        'total_bloqueios' => count(array_filter($events, function($e) { 
            return $e['extendedProps']['type'] === 'block'; 
        }))
    ]);
    
} catch (Exception $e) {
    error_log("Erro na API de eventos do barbeiro: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'events' => []
    ]);
}
?>