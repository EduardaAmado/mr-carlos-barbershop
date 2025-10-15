<?php
/**
 * API - Criar/Remover bloqueios de barbeiro
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Gerir períodos de indisponibilidade do barbeiro
 * 
 * REQUEST (POST JSON):
 * Para CRIAR:
 * {
 *   "action": "create",
 *   "barbeiro_id": 1,
 *   "data_inicio": "2025-10-15 09:00:00",
 *   "data_fim": "2025-10-15 18:00:00",
 *   "tipo": "folga",
 *   "motivo": "Dia de descanso"
 * }
 * 
 * Para REMOVER:
 * {
 *   "action": "remove",
 *   "barbeiro_id": 1,
 *   "block_id": 5
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
    
    $action = $input['action'] ?? 'create';
    $barbeiro_id = intval($input['barbeiro_id'] ?? 0);
    
    // Verificar se o barbeiro pode fazer esta ação
    if ($barbeiro_id !== $barbeiro['id']) {
        throw new Exception('Acesso negado');
    }
    
    if ($action === 'create') {
        // CRIAR NOVO BLOQUEIO
        $data_inicio = $input['data_inicio'] ?? '';
        $data_fim = $input['data_fim'] ?? '';
        $tipo = $input['tipo'] ?? 'outro';
        $motivo = trim($input['motivo'] ?? '');
        
        if (!$data_inicio || !$data_fim) {
            throw new Exception('Datas de início e fim são obrigatórias');
        }
        
        // Validar datas
        $inicio_obj = DateTime::createFromFormat('Y-m-d H:i:s', $data_inicio);
        $fim_obj = DateTime::createFromFormat('Y-m-d H:i:s', $data_fim);
        
        if (!$inicio_obj || !$fim_obj) {
            throw new Exception('Formato de data inválido');
        }
        
        if ($inicio_obj >= $fim_obj) {
            throw new Exception('Data de fim deve ser posterior à data de início');
        }
        
        // Verificar se não está no passado (exceto para hoje)
        $hoje_inicio = new DateTime('today');
        if ($inicio_obj < $hoje_inicio) {
            throw new Exception('Não é possível criar bloqueios para o passado');
        }
        
        // Verificar tipos válidos
        $tipos_validos = ['folga', 'ferias', 'doenca', 'formacao', 'outro'];
        if (!in_array($tipo, $tipos_validos)) {
            $tipo = 'outro';
        }
        
        // Usar tipo como motivo se motivo estiver vazio
        if (!$motivo) {
            $motivo = ucfirst($tipo);
        }
        
        // Verificar conflitos com agendamentos existentes
        $conflitos_result = execute_prepared_query(
            "SELECT COUNT(*) as conflitos, GROUP_CONCAT(CONCAT(DATE_FORMAT(data_hora, '%d/%m %H:%i'), ' - ', c.nome)) as detalhes
             FROM agendamentos a
             LEFT JOIN clientes c ON a.cliente_id = c.id
             WHERE a.barbeiro_id = ? 
             AND a.status NOT IN ('cancelado', 'falta')
             AND (
                 (a.data_hora < ? AND a.data_fim > ?) OR
                 (a.data_hora < ? AND a.data_fim > ?) OR
                 (a.data_hora >= ? AND a.data_fim <= ?)
             )",
            [
                $barbeiro_id,
                $data_fim, $data_inicio,      // Agendamento que termina depois do início do bloqueio
                $data_inicio, $data_fim,      // Agendamento que inicia antes do fim do bloqueio
                $data_inicio, $data_fim       // Agendamento completamente dentro do bloqueio
            ],
            'issssss'
        );
        
        if ($conflitos_result && ($conflitos = $conflitos_result->fetch_assoc())) {
            if ($conflitos['conflitos'] > 0) {
                throw new Exception("Existem {$conflitos['conflitos']} agendamento(s) neste período: {$conflitos['detalhes']}. Cancele os agendamentos primeiro.");
            }
        }
        
        // Verificar sobreposição com bloqueios existentes
        $sobreposicao_result = execute_prepared_query(
            "SELECT COUNT(*) as sobreposicoes
             FROM bloqueios
             WHERE barbeiro_id = ? 
             AND ativo = 1
             AND (
                 (data_inicio < ? AND data_fim > ?) OR
                 (data_inicio < ? AND data_fim > ?) OR
                 (data_inicio >= ? AND data_fim <= ?)
             )",
            [
                $barbeiro_id,
                $data_fim, $data_inicio,
                $data_inicio, $data_fim,
                $data_inicio, $data_fim
            ],
            'issssss'
        );
        
        if ($sobreposicao_result && ($sobreposicao = $sobreposicao_result->fetch_assoc())) {
            if ($sobreposicao['sobreposicoes'] > 0) {
                throw new Exception('Já existe um período de indisponibilidade que se sobrepõe a estas datas.');
            }
        }
        
        // Inserir bloqueio
        execute_prepared_query(
            "INSERT INTO bloqueios (barbeiro_id, data_inicio, data_fim, motivo, tipo, ativo) VALUES (?, ?, ?, ?, ?, 1)",
            [$barbeiro_id, $data_inicio, $data_fim, $motivo, $tipo],
            'issss'
        );
        
        $bloqueio_id = get_last_insert_id();
        
        // Log da ação
        error_log("Bloqueio criado - Barbeiro: {$barbeiro_id}, ID: {$bloqueio_id}, Período: {$data_inicio} a {$data_fim}");
        
        echo json_encode([
            'success' => true,
            'message' => 'Período marcado como indisponível com sucesso!',
            'bloqueio_id' => $bloqueio_id
        ]);
        
    } elseif ($action === 'remove') {
        // REMOVER BLOQUEIO EXISTENTE
        $block_id = intval($input['block_id'] ?? 0);
        
        if (!$block_id) {
            throw new Exception('ID do bloqueio é obrigatório');
        }
        
        // Verificar se o bloqueio pertence ao barbeiro
        $bloqueio_result = execute_prepared_query(
            "SELECT id, data_inicio, data_fim, motivo FROM bloqueios WHERE id = ? AND barbeiro_id = ?",
            [$block_id, $barbeiro_id],
            'ii'
        );
        
        if (!$bloqueio_result || !($bloqueio = $bloqueio_result->fetch_assoc())) {
            throw new Exception('Bloqueio não encontrado');
        }
        
        // Verificar se não está no passado
        $inicio_obj = new DateTime($bloqueio['data_inicio']);
        $agora = new DateTime();
        
        if ($inicio_obj < $agora) {
            throw new Exception('Não é possível remover bloqueios que já iniciaram');
        }
        
        // Marcar como inativo (soft delete)
        execute_prepared_query(
            "UPDATE bloqueios SET ativo = 0 WHERE id = ? AND barbeiro_id = ?",
            [$block_id, $barbeiro_id],
            'ii'
        );
        
        // Log da ação
        error_log("Bloqueio removido - Barbeiro: {$barbeiro_id}, ID: {$block_id}");
        
        echo json_encode([
            'success' => true,
            'message' => 'Período de indisponibilidade removido com sucesso!'
        ]);
        
    } else {
        throw new Exception('Ação inválida');
    }
    
} catch (Exception $e) {
    error_log("Erro na API de bloqueios do barbeiro: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>