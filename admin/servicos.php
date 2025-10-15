<?php
/**
 * Gestão de Serviços - Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: CRUD completo de serviços com interface intuitiva
 */

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Verificar se está logado como admin
if (!is_logged_in('admin')) {
    safe_redirect('/mr-carlos-barbershop/pages/login.php');
}

$admin = get_logged_user('admin');
$page_title = 'Gestão de Serviços';
$erro = '';

global $pdo;
$sucesso = '';

// === PROCESSAR AÇÕES ===
if ($_POST) {
    $acao = $_POST['acao'] ?? '';
    
    try {
        if ($acao === 'criar') {
            // CRIAR NOVO SERVIÇO
            $nome = trim($_POST['nome'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $preco = floatval($_POST['preco'] ?? 0);
            $duracao = intval($_POST['duracao'] ?? 0);
            $categoria = trim($_POST['categoria'] ?? '');
            
            // Validações
            if (!$nome || $preco <= 0 || $duracao <= 0) {
                throw new Exception('Nome, preço e duração são obrigatórios e devem ser maiores que zero');
            }
            
            if ($preco > 9999.99) {
                throw new Exception('Preço não pode exceder € 9.999,99');
            }
            
            if ($duracao > 480) {
                throw new Exception('Duração não pode exceder 8 horas (480 minutos)');
            }
            
            // Verificar se nome já existe
            $stmt = $pdo->prepare("SELECT id FROM servicos WHERE nome = ?");
            $stmt->execute([$nome]);
            
            if ($stmt->fetch()) {
                throw new Exception('Já existe um serviço com este nome');
            }
            
            // Inserir serviço
            $stmt = $pdo->prepare("INSERT INTO servicos (nome, descricao, preco, duracao_minutos, categoria, ativo) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([$nome, $descricao, $preco, $duracao, $categoria]);
            
            $sucesso = "Serviço '{$nome}' cadastrado com sucesso!";
            error_log("Serviço criado: {$nome} por admin ID: {$admin['id']}");
            
        } elseif ($acao === 'editar') {
            // EDITAR SERVIÇO EXISTENTE
            $servico_id = intval($_POST['servico_id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $preco = floatval($_POST['preco'] ?? 0);
            $duracao = intval($_POST['duracao'] ?? 0);
            $categoria = trim($_POST['categoria'] ?? '');
            
            if (!$servico_id || !$nome || $preco <= 0 || $duracao <= 0) {
                throw new Exception('Nome, preço e duração são obrigatórios e devem ser maiores que zero');
            }
            
            if ($preco > 9999.99) {
                throw new Exception('Preço não pode exceder € 9.999,99');
            }
            
            if ($duracao > 480) {
                throw new Exception('Duração não pode exceder 8 horas (480 minutos)');
            }
            
            // Verificar se nome já existe para outro serviço
            $stmt = $pdo->prepare("SELECT id FROM servicos WHERE nome = ? AND id != ?");
            $stmt->execute([$nome, $servico_id]);
            
            if ($stmt->fetch()) {
                throw new Exception('Já existe outro serviço com este nome');
            }
            
            // Atualizar serviço
            $stmt = $pdo->prepare("UPDATE servicos SET nome = ?, descricao = ?, preco = ?, duracao_minutos = ?, categoria = ? WHERE id = ?");
            $stmt->execute([$nome, $descricao, $preco, $duracao, $categoria, $servico_id]);
            
            $sucesso = "Serviço '{$nome}' atualizado com sucesso!";
            error_log("Serviço editado ID: {$servico_id} por admin ID: {$admin['id']}");
            
        } elseif ($acao === 'ativar_desativar') {
            // ATIVAR/DESATIVAR SERVIÇO
            $servico_id = intval($_POST['servico_id'] ?? 0);
            $ativo = intval($_POST['ativo'] ?? 0);
            
            if (!$servico_id) {
                throw new Exception('ID do serviço é obrigatório');
            }
            
            // Buscar nome do serviço para feedback
            $stmt = $pdo->prepare("SELECT nome FROM servicos WHERE id = ?");
            $stmt->execute([$servico_id]);
            $servico_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$servico_data) {
                throw new Exception('Serviço não encontrado');
            }
            
            // Verificar agendamentos futuros se estiver desativando
            if (!$ativo) {
                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM agendamentos 
                     WHERE servico_id = ? AND data_hora >= NOW() AND status NOT IN ('cancelado', 'falta')");
                $stmt->execute([$servico_id]);
                $agendamentos = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($agendamentos && $agendamentos['total'] > 0) {
                    throw new Exception("Não é possível desativar este serviço pois há {$agendamentos['total']} agendamento(s) futuro(s).");
                }
            }
            
            $stmt = $pdo->prepare("UPDATE servicos SET ativo = ? WHERE id = ?");
            $stmt->execute([$ativo, $servico_id]);
            
            $status_text = $ativo ? 'ativado' : 'desativado';
            $sucesso = "Serviço '{$servico_data['nome']}' foi {$status_text} com sucesso!";
            error_log("Serviço {$status_text} ID: {$servico_id} por admin ID: {$admin['id']}");
        }
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
        error_log("Erro na gestão de serviços: " . $e->getMessage());
    }
}

// === BUSCAR SERVIÇOS ===
$busca = $_GET['busca'] ?? '';
$categoria_filtro = $_GET['categoria'] ?? '';
$where_conditions = [];
$params = [];
$types = '';

if ($busca) {
    $where_conditions[] = "(nome LIKE ? OR descricao LIKE ?)";
    $busca_term = "%{$busca}%";
    $params[] = $busca_term;
    $params[] = $busca_term;
    $types .= 'ss';
}

if ($categoria_filtro) {
    $where_conditions[] = "categoria = ?";
    $params[] = $categoria_filtro;
    $types .= 's';
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(' AND ', $where_conditions);
}

$stmt = $pdo->prepare("SELECT s.*, 
            COUNT(a.id) as total_agendamentos,
            COUNT(CASE WHEN a.status = 'concluido' AND YEAR(a.data_hora) = YEAR(CURDATE()) AND MONTH(a.data_hora) = MONTH(CURDATE()) THEN 1 END) as agendamentos_mes,
            COALESCE(SUM(CASE WHEN a.status = 'concluido' AND YEAR(a.data_hora) = YEAR(CURDATE()) AND MONTH(a.data_hora) = MONTH(CURDATE()) THEN s.preco END), 0) as receita_mes
     FROM servicos s
     LEFT JOIN agendamentos a ON s.id = a.servico_id AND a.data_hora >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
     {$where_clause}
     GROUP BY s.id
     ORDER BY s.nome ASC");
$stmt->execute($params);
$servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar categorias para o filtro
$stmt = $pdo->prepare("SELECT DISTINCT categoria FROM servicos WHERE categoria != '' AND categoria IS NOT NULL ORDER BY categoria");
$stmt->execute();
$categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);

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
                        <h1 class="text-xl font-bold text-gray-900">Gestão de Serviços</h1>
                        <p class="text-sm text-gray-600">Cadastrar, editar e gerenciar serviços</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-600 hover:text-dourado transition-colors">
                        <i class="fas fa-home text-xl"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Mensagens -->
        <?php if ($erro): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span><?= htmlspecialchars($erro) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span><?= htmlspecialchars($sucesso) ?></span>
            </div>
        <?php endif; ?>

        <!-- Barra de Filtros e Ações -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-6">
            <div class="p-6">
                <form method="GET" class="flex flex-col lg:flex-row justify-between items-start lg:items-end space-y-4 lg:space-y-0 lg:space-x-4">
                    <!-- Busca -->
                    <div class="flex-1 max-w-md">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Serviços</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input 
                                type="text" 
                                name="busca" 
                                value="<?= htmlspecialchars($busca) ?>"
                                placeholder="Nome ou descrição..." 
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado"
                            >
                        </div>
                    </div>

                    <!-- Filtro por Categoria -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                        <select name="categoria" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                            <option value="">Todas as categorias</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= htmlspecialchars($categoria) ?>" <?= $categoria_filtro === $categoria ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Botões -->
                    <div class="flex space-x-2">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-filter mr-2"></i>
                            Filtrar
                        </button>
                        
                        <button type="button" onclick="abrirModalCriar()" class="bg-gradient-to-r from-dourado to-dourado_escuro text-white px-4 py-2 rounded-lg hover:shadow-lg transition-all duration-200">
                            <i class="fas fa-plus mr-2"></i>
                            Novo Serviço
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de Serviços -->
        <div class="bg-white shadow-lg rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-cut mr-2 text-dourado"></i>
                    Serviços Cadastrados (<?= count($servicos) ?>)
                </h3>
            </div>

            <?php if (empty($servicos)): ?>
                <div class="p-8 text-center">
                    <i class="fas fa-cut text-4xl text-gray-400 mb-4"></i>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Nenhum serviço encontrado</h4>
                    <p class="text-gray-600 mb-4">
                        <?= $busca || $categoria_filtro ? 'Nenhum serviço corresponde aos filtros aplicados.' : 'Comece cadastrando o primeiro serviço.' ?>
                    </p>
                    <?php if (!$busca && !$categoria_filtro): ?>
                        <button onclick="abrirModalCriar()" class="bg-dourado text-white px-6 py-2 rounded-lg hover:bg-dourado_escuro transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Cadastrar Primeiro Serviço
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                    <?php foreach ($servicos as $servico): ?>
                        <div class="bg-gray-50 rounded-lg border border-gray-200 hover:shadow-lg transition-all duration-200 <?= !$servico['ativo'] ? 'opacity-60' : '' ?>">
                            <div class="p-6">
                                <!-- Header do Card -->
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex-1">
                                        <h4 class="text-lg font-semibold text-gray-900 mb-1"><?= htmlspecialchars($servico['nome']) ?></h4>
                                        <?php if ($servico['categoria']): ?>
                                            <span class="inline-block px-2 py-1 text-xs rounded-full bg-dourado bg-opacity-20 text-dourado font-medium">
                                                <?= htmlspecialchars($servico['categoria']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $servico['ativo'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $servico['ativo'] ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                </div>

                                <!-- Descrição -->
                                <?php if ($servico['descricao']): ?>
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-2"><?= htmlspecialchars($servico['descricao']) ?></p>
                                <?php endif; ?>

                                <!-- Detalhes do Serviço -->
                                <div class="space-y-2 mb-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600 flex items-center">
                                            <i class="fas fa-dollar-sign mr-1 text-dourado"></i>
                                            Preço:
                                        </span>
                                        <span class="font-semibold text-gray-900">€ <?= number_format($servico['preco'], 2, '.', ',') ?></span>
                                    </div>
                                    
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600 flex items-center">
                                            <i class="fas fa-clock mr-1 text-dourado"></i>
                                            Duração:
                                        </span>
                                        <span class="font-semibold text-gray-900">
                                            <?= intval($servico['duracao_minutos'] / 60) ?>h <?= $servico['duracao_minutos'] % 60 ?>min
                                        </span>
                                    </div>
                                </div>

                                <!-- Estatísticas -->
                                <div class="bg-white rounded-lg p-3 mb-4">
                                    <div class="text-xs text-gray-500 mb-1">Performance (30 dias)</div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-700"><?= $servico['agendamentos_mes'] ?> agendamentos</span>
                                        <span class="text-sm font-semibold text-dourado">€ <?= number_format($servico['receita_mes'], 2, '.', ',') ?></span>
                                    </div>
                                </div>

                                <!-- Ações -->
                                <div class="flex justify-center space-x-2">
                                    <button onclick="abrirModalEditar(<?= htmlspecialchars(json_encode($servico)) ?>)" 
                                            class="flex-1 bg-blue-600 text-white py-2 px-3 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                        <i class="fas fa-edit mr-1"></i>
                                        Editar
                                    </button>
                                    
                                    <form method="POST" class="flex-1" onsubmit="return confirm('<?= $servico['ativo'] ? 'Desativar' : 'Ativar' ?> este serviço?')">
                                        <input type="hidden" name="acao" value="ativar_desativar">
                                        <input type="hidden" name="servico_id" value="<?= $servico['id'] ?>">
                                        <input type="hidden" name="ativo" value="<?= $servico['ativo'] ? 0 : 1 ?>">
                                        <button type="submit" class="w-full <?= $servico['ativo'] ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' ?> text-white py-2 px-3 rounded-lg transition-colors text-sm">
                                            <i class="fas fa-<?= $servico['ativo'] ? 'times' : 'check' ?> mr-1"></i>
                                            <?= $servico['ativo'] ? 'Desativar' : 'Ativar' ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Criar Serviço -->
<div id="modalCriar" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" onclick="fecharModal('modalCriar')">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-plus-circle mr-2 text-dourado"></i>
                    Cadastrar Novo Serviço
                </h3>
            </div>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="acao" value="criar">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Serviço *</label>
                    <input type="text" name="nome" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                    <textarea name="descricao" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado" placeholder="Descreva o serviço..."></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Preço (€) *</label>
                        <input type="number" name="preco" step="0.01" min="0.01" max="9999.99" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Duração (min) *</label>
                        <input type="number" name="duracao" min="1" max="480" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                    <input type="text" name="categoria" placeholder="Ex: Cortes, Barbas, Tratamentos..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="fecharModal('modalCriar')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-dourado text-white rounded-lg hover:bg-dourado_escuro transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Cadastrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Serviço -->
<div id="modalEditar" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" onclick="fecharModal('modalEditar')">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-edit mr-2 text-dourado"></i>
                    Editar Serviço
                </h3>
            </div>

            <form method="POST" class="space-y-4" id="formEditar">
                <input type="hidden" name="acao" value="editar">
                <input type="hidden" name="servico_id" id="edit_servico_id">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Serviço *</label>
                    <input type="text" name="nome" id="edit_nome" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                    <textarea name="descricao" id="edit_descricao" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Preço (€) *</label>
                        <input type="number" name="preco" id="edit_preco" step="0.01" min="0.01" max="9999.99" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Duração (min) *</label>
                        <input type="number" name="duracao" id="edit_duracao" min="1" max="480" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                    <input type="text" name="categoria" id="edit_categoria" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="fecharModal('modalEditar')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-dourado text-white rounded-lg hover:bg-dourado_escuro transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function abrirModalCriar() {
    document.getElementById('modalCriar').classList.remove('hidden');
}

function abrirModalEditar(servico) {
    document.getElementById('edit_servico_id').value = servico.id;
    document.getElementById('edit_nome').value = servico.nome;
    document.getElementById('edit_descricao').value = servico.descricao || '';
    document.getElementById('edit_preco').value = servico.preco;
    document.getElementById('edit_duracao').value = servico.duracao_minutos;
    document.getElementById('edit_categoria').value = servico.categoria || '';
    
    document.getElementById('modalEditar').classList.remove('hidden');
}

function fecharModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Fechar modal com ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        fecharModal('modalCriar');
        fecharModal('modalEditar');
    }
});

// Auto-dismiss mensagens
document.addEventListener('DOMContentLoaded', function() {
    const messages = document.querySelectorAll('.bg-red-50, .bg-green-50');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 300);
        }, 5000);
    });
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>