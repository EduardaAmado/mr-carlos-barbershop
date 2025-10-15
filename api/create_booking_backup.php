<?php
/**
 * API - Criar novo agendamento
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Processar criação de agendamentos via AJAX
 * 
 * REQUEST (POST JSON):
 * {
 *   "barbeiro_id": 1,
 *   "servico_id": 2,
 *   "datetime": "2025-10-15 09:30",
 *   "notas": "Corte degradê"
 * }
 * 
 * RESPONSE JSON:
 * {
 *   "success": true,
 *   "message": "Agendamento criado com sucesso",
 *   "agendamento_id": 123
 * }
 */

// Capturar qualquer erro PHP e converter para JSON
ob_start();
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        exit;
    }
});

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Iniciar sessão para verificar autenticação
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

// Verificar se cliente está logado
if (!is_logged_in('cliente')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

try {
    // Obter dados do cliente logado
    $user = get_logged_user('cliente');
    $cliente_id = $user['id'];
    
    // Obter dados JSON da requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Dados JSON inválidos');
    }
    
    // Validar parâmetros obrigatórios
    $barbeiro_id = intval($input['barbeiro_id'] ?? 0);
    $servico_id = intval($input['servico_id'] ?? 0);
    $datetime = $input['datetime'] ?? '';
    $notas = trim($input['notas'] ?? '');
    
    if (!$barbeiro_id || !$servico_id || !$datetime) {
        throw new Exception('Parâmetros obrigatórios em falta');
    }
    
    // Validar formato da data/hora
    $data_hora_obj = DateTime::createFromFormat('Y-m-d H:i', $datetime);
    if (!$data_hora_obj) {
        throw new Exception('Formato de data/hora inválido');
    }
    
    // Verificar se não é no passado
    if ($data_hora_obj < new DateTime()) {
        throw new Exception('Não é possível agendar para o passado');
    }
    
    // Verificar se barbeiro existe e está ativo
    $stmt = $pdo->prepare("SELECT id, nome, email FROM barbeiros WHERE id = ? AND ativo = 1");
    $stmt->execute([$barbeiro_id]);
    $barbeiro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$barbeiro) {
        throw new Exception('Barbeiro não encontrado');
    }
    
    // Verificar se serviço existe e está ativo
    $stmt = $pdo->prepare("SELECT id, nome, duracao_minutos, preco FROM servicos WHERE id = ? AND ativo = 1");
    $stmt->execute([$servico_id]);
    $servico = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$servico) {
        throw new Exception('Serviço não encontrado');
    }
    
    // Verificar se cliente existe e está ativo
    $stmt = $pdo->prepare("SELECT id, nome, email FROM clientes WHERE id = ? AND ativo = 1");
    $stmt->execute([$cliente_id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cliente) {
        throw new Exception('Cliente não encontrado');
    }
    
    // Calcular data/hora de fim
    $duracao = $servico['duracao_minutos'];
    $data_fim_obj = clone $data_hora_obj;
    $data_fim_obj->add(new DateInterval("PT{$duracao}M"));
    
    $data_hora_mysql = $data_hora_obj->format('Y-m-d H:i:s');
    $data_fim_mysql = $data_fim_obj->format('Y-m-d H:i:s');
    
    // VERIFICAR NOVAMENTE A DISPONIBILIDADE
    // (Segurança extra - pode ter mudado entre a consulta inicial e agora)
    
    // 1. Conflitos com outros agendamentos
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) as conflitos 
         FROM agendamentos 
         WHERE barbeiro_id = ? 
         AND status NOT IN ('cancelado', 'falta')
         AND (
             (data_hora < ? AND data_fim > ?) OR
             (data_hora < ? AND data_fim > ?)
         )"
    );
    $stmt->execute([
        $barbeiro_id, 
        $data_fim_mysql, $data_hora_mysql,
        $data_hora_mysql, $data_fim_mysql
    ]);
    $conflitos = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($conflitos && $conflitos['conflitos'] > 0) {
        throw new Exception('Horário não está mais disponível. Recarregue a página.');
    }
    
    // 2. Bloqueios do barbeiro
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
        $data_hora_mysql, $data_hora_mysql,
        $data_fim_mysql, $data_fim_mysql
    ]);
    $bloqueios = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($bloqueios && $bloqueios['bloqueios'] > 0) {
        throw new Exception('Barbeiro não disponível neste horário.');
    }
    
    // 3. Verificar limite de agendamentos futuros por cliente (opcional)
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) as total 
         FROM agendamentos 
         WHERE cliente_id = ? 
         AND data_hora > NOW() 
         AND status NOT IN ('cancelado', 'falta')"
    );
    $stmt->execute([$cliente_id]);
    $agendamentos_futuros = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($agendamentos_futuros && $agendamentos_futuros['total'] >= 5) { // Limite de 5 agendamentos futuros
        throw new Exception('Limite de agendamentos futuros atingido. Cancele um agendamento existente primeiro.');
    }
    
    // INICIAR TRANSAÇÃO PARA CRIAR AGENDAMENTO
    $pdo->beginTransaction();
    
    try {
        // Inserir agendamento
        $stmt = $pdo->prepare(
            "INSERT INTO agendamentos (cliente_id, barbeiro_id, servico_id, data_hora, data_fim, notas, preco_pago, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, 'pendente')"
        );
        
        $stmt->execute([
            $cliente_id,
            $barbeiro_id, 
            $servico_id,
            $data_hora_mysql,
            $data_fim_mysql,
            $notas,
            $servico['preco']
        ]);
        
        $agendamento_id = $pdo->lastInsertId();
        
        // Confirmar transação
        $pdo->commit();
        
        // Log da criação do agendamento
        error_log("Agendamento criado com sucesso - ID: {$agendamento_id}");
        
        // ENVIAR EMAIL DE CONFIRMAÇÃO
        try {
            // Incluir sistema de email se disponível
            if (file_exists(__DIR__ . '/../includes/email.php')) {
                require_once __DIR__ . '/../includes/email.php';
                
                $dados_email = [
                    'cliente_nome' => $cliente['nome'],
                    'cliente_email' => $cliente['email'],
                    'barbeiro_nome' => $barbeiro['nome'],
                    'servico_nome' => $servico['nome'],
                    'data_hora' => $data_hora_mysql,
                    'preco' => $servico['preco']
                ];
                
                // Tentar enviar email (não falhar o agendamento se der erro)
                $email_enviado = enviar_email_agendamento('confirmacao', $dados_email);
                
                if ($email_enviado) {
                    error_log("Email de confirmação enviado para: " . $cliente['email']);
                } else {
                    error_log("Falha no envio do email de confirmação para: " . $cliente['email']);
                }
            }
        } catch (Exception $e) {
            // Log do erro mas não falhar o agendamento
            error_log("Erro ao processar email de confirmação: " . $e->getMessage());
        }
        
        // Log da ação
        error_log("Agendamento criado - ID: {$agendamento_id}, Cliente: {$cliente_id}, Barbeiro: {$barbeiro_id}, Data: {$data_hora_mysql}");
        
        // Resposta de sucesso
        echo json_encode([
            'success' => true,
            'message' => 'Agendamento criado com sucesso! Receberá uma confirmação por email.',
            'agendamento_id' => (int)$agendamento_id,
            'data_formatada' => $data_hora_obj->format('d/m/Y'),
            'horario_formatado' => $data_hora_obj->format('H:i'),
            'barbeiro_nome' => $barbeiro['nome'],
            'servico_nome' => $servico['nome'],
            'preco_formatado' => '€' . number_format($servico['preco'], 2, ',', '.')
        ]);
        
    } catch (Exception $e) {
        // Rollback da transação
        $pdo->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Erro na API de criação de agendamento: " . $e->getMessage());
    
    // Determinar código de status HTTP apropriado
    $status_code = 400;
    if (strpos($e->getMessage(), 'não autorizado') !== false) {
        $status_code = 401;
    } elseif (strpos($e->getMessage(), 'não encontrado') !== false) {
        $status_code = 404;
    } elseif (strpos($e->getMessage(), 'conflito') !== false) {
        $status_code = 409;
    }
    
    http_response_code($status_code);
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}


?>