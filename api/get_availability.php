<?php
/**
 * API - Verificar disponibilidade de horários
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Retornar horários disponíveis para agendamento via AJAX
 * 
 * REQUEST (POST JSON):
 * {
 *   "barbeiro_id": 1,
 *   "servico_id": 2,  
 *   "data": "2025-10-15",
 *   "duracao": 30
 * }
 * 
 * RESPONSE JSON:
 * {
 *   "success": true,
 *   "slots": [
 *     {"time": "09:00", "available": true},
 *     {"time": "09:30", "available": false},
 *     ...
 *   ]
 * }
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Iniciar sessão se necessário
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se é requisição AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Requisição inválida']);
    exit;
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    // Obter dados JSON da requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Dados JSON inválidos');
    }
    
    // Validar parâmetros obrigatórios
    $barbeiro_id = intval($input['barbeiro_id'] ?? 0);
    $servico_id = intval($input['servico_id'] ?? 0);
    $data = $input['data'] ?? '';
    $duracao = intval($input['duracao'] ?? 30);
    
    if (!$barbeiro_id || !$servico_id || !$data) {
        throw new Exception('Parâmetros obrigatórios em falta');
    }
    
    // Validar formato da data
    $data_obj = DateTime::createFromFormat('Y-m-d', $data);
    if (!$data_obj || $data_obj->format('Y-m-d') !== $data) {
        throw new Exception('Formato de data inválido');
    }
    
    // Verificar se data não é no passado
    if ($data_obj < new DateTime('today')) {
        throw new Exception('Não é possível agendar para datas passadas');
    }
    
    // Verificar se barbeiro existe e está ativo
    $stmt = $pdo->prepare("SELECT id, horario_inicio, horario_fim, dias_trabalho FROM barbeiros WHERE id = ? AND ativo = 1");
    $stmt->execute([$barbeiro_id]);
    $barbeiro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$barbeiro) {
        throw new Exception('Barbeiro não encontrado ou inativo');
    }
    
    // Verificar se serviço existe e está ativo
    $stmt = $pdo->prepare("SELECT id, duracao_minutos FROM servicos WHERE id = ? AND ativo = 1");
    $stmt->execute([$servico_id]);
    $servico = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$servico) {
        throw new Exception('Serviço não encontrado ou inativo');
    }
    
    // Usar duração do serviço da base de dados
    $duracao = $servico['duracao_minutos'];
    
    // Verificar se barbeiro trabalha neste dia da semana
    $dia_semana = $data_obj->format('N'); // 1=Segunda, 7=Domingo
    $dias_trabalho = json_decode($barbeiro['dias_trabalho'], true) ?: [];
    
    if (!in_array($dia_semana, $dias_trabalho)) {
        echo json_encode([
            'success' => true,
            'slots' => [],
            'message' => 'Barbeiro não trabalha neste dia'
        ]);
        exit;
    }
    
    // Obter horários de trabalho do barbeiro
    $horario_inicio = $barbeiro['horario_inicio'];
    $horario_fim = $barbeiro['horario_fim'];
    
    // Gerar slots de tempo possíveis (intervalos de 30 minutos)
    $slots = [];
    $current_time = DateTime::createFromFormat('H:i:s', $horario_inicio);
    $end_time = DateTime::createFromFormat('H:i:s', $horario_fim);
    
    // Ajustar horário de fim para não permitir agendamentos que ultrapassem
    $end_time->sub(new DateInterval("PT{$duracao}M"));
    
    while ($current_time <= $end_time) {
        $time_str = $current_time->format('H:i');
        
        // Verificar se horário está disponível
        $is_available = isTimeSlotAvailable(
            $barbeiro_id, 
            $data, 
            $time_str, 
            $duracao
        );
        
        $slots[] = [
            'time' => $time_str,
            'available' => $is_available
        ];
        
        // Próximo slot (30 minutos depois)
        $current_time->add(new DateInterval('PT30M'));
    }
    
    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'slots' => $slots,
        'barbeiro_nome' => $barbeiro['nome'] ?? '',
        'data_formatada' => $data_obj->format('d/m/Y')
    ]);
    
} catch (Exception $e) {
    error_log("Erro na API de disponibilidade: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Verificar se um slot de tempo está disponível
 * 
 * @param int $barbeiro_id ID do barbeiro
 * @param string $data Data no formato Y-m-d
 * @param string $horario Horário no formato H:i
 * @param int $duracao Duração do serviço em minutos
 * @return bool True se disponível
 */
function isTimeSlotAvailable($barbeiro_id, $data, $horario, $duracao) {
    global $pdo;
    
    try {
        $datetime_inicio = "{$data} {$horario}:00";
        
        // Calcular horário de fim do agendamento
        $inicio = new DateTime($datetime_inicio);
        $fim = clone $inicio;
        $fim->add(new DateInterval("PT{$duracao}M"));
        
        $datetime_fim = $fim->format('Y-m-d H:i:s');
        
        // 1. Verificar conflitos com agendamentos existentes
        // Um novo agendamento conflita se:
        // - Inicia antes do fim de um agendamento existente E
        // - Termina depois do início de um agendamento existente
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) as conflitos 
             FROM agendamentos 
             WHERE barbeiro_id = ? 
             AND DATE(data_hora) = ? 
             AND status NOT IN ('cancelado', 'falta')
             AND (
                 (data_hora < ? AND data_fim > ?) OR
                 (data_hora < ? AND data_fim > ?)
             )"
        );
        $stmt->execute([
            $barbeiro_id, 
            $data, 
            $datetime_fim, $datetime_inicio,  // Novo termina depois do início existente
            $datetime_inicio, $datetime_fim   // Novo inicia antes do fim existente
        ]);
        $conflitos = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($conflitos && $conflitos['conflitos'] > 0) {
            return false; // Há conflitos
        }
        
        // 2. Verificar bloqueios do barbeiro
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) as bloqueios 
             FROM bloqueios 
             WHERE barbeiro_id = ? 
             AND ativo = 1
             AND (
                 (data_inicio <= ? AND data_fim >= ?) OR
                 (data_inicio <= ? AND data_fim >= ?)
             )"
        );
        $stmt->execute([
            $barbeiro_id,
            $datetime_inicio, $datetime_inicio, // Bloqueio cobre o início
            $datetime_fim, $datetime_fim         // Bloqueio cobre o fim
        ]);
        $bloqueios = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($bloqueios && $bloqueios['bloqueios'] > 0) {
            return false; // Está bloqueado
        }
        
        // 3. Verificar se não é muito próximo da hora atual
        // Não permitir agendamentos com menos de 1 hora de antecedência
        $agora = new DateTime();
        $limite_agendamento = clone $agora;
        $limite_agendamento->add(new DateInterval('PT1H'));
        
        if ($inicio < $limite_agendamento) {
            return false; // Muito próximo da hora atual
        }
        
        return true; // Slot disponível
        
    } catch (Exception $e) {
        error_log("Erro ao verificar disponibilidade do slot: " . $e->getMessage());
        return false;
    }
}
?>