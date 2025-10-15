<?php
/**
 * Gestão de Barbeiros - Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: CRUD completo de barbeiros com interface intuitiva
 */

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Verificar se está logado como admin
if (!is_logged_in('admin')) {
    safe_redirect('/mr-carlos-barbershop/admin/login.php');
}

$admin = get_logged_user('admin');
$page_title = 'Gestão de Barbeiros';
$erro = '';
$sucesso = '';

// === PROCESSAR AÇÕES ===
if ($_POST) {
    $acao = $_POST['acao'] ?? '';
    
    try {
        if ($acao === 'criar') {
            // CRIAR NOVO BARBEIRO
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telefone = trim($_POST['telefone'] ?? '');
            $especialidade = trim($_POST['especialidade'] ?? '');
            $senha = $_POST['senha'] ?? '';
            $confirmar_senha = $_POST['confirmar_senha'] ?? '';
            
            // Validações
            if (!$nome || !$email || !$telefone || !$senha) {
                throw new Exception('Todos os campos obrigatórios devem ser preenchidos');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido');
            }
            
            if ($senha !== $confirmar_senha) {
                throw new Exception('Senhas não coincidem');
            }
            
            if (strlen($senha) < 6) {
                throw new Exception('Senha deve ter pelo menos 6 caracteres');
            }
            
            // Verificar se email já existe
            $email_check = execute_prepared_query(
                "SELECT id FROM barbeiros WHERE email = ?",
                [$email],
                's'
            );
            
            if ($email_check && $email_check->num_rows > 0) {
                throw new Exception('Este email já está cadastrado');
            }
            
            // Inserir barbeiro
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            execute_prepared_query(
                "INSERT INTO barbeiros (nome, email, telefone, especialidade, senha, ativo) VALUES (?, ?, ?, ?, ?, 1)",
                [$nome, $email, $telefone, $especialidade, $senha_hash],
                'sssss'
            );
            
            $sucesso = "Barbeiro '{$nome}' cadastrado com sucesso!";
            error_log("Barbeiro criado: {$email} por admin ID: {$admin['id']}");
            
        } elseif ($acao === 'editar') {
            // EDITAR BARBEIRO EXISTENTE
            $barbeiro_id = intval($_POST['barbeiro_id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telefone = trim($_POST['telefone'] ?? '');
            $especialidade = trim($_POST['especialidade'] ?? '');
            $nova_senha = $_POST['nova_senha'] ?? '';
            
            if (!$barbeiro_id || !$nome || !$email || !$telefone) {
                throw new Exception('Todos os campos obrigatórios devem ser preenchidos');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido');
            }
            
            // Verificar se email já existe para outro barbeiro
            $email_check = execute_prepared_query(
                "SELECT id FROM barbeiros WHERE email = ? AND id != ?",
                [$email, $barbeiro_id],
                'si'
            );
            
            if ($email_check && $email_check->num_rows > 0) {
                throw new Exception('Este email já está sendo usado por outro barbeiro');
            }
            
            // Atualizar dados básicos
            if ($nova_senha) {
                if (strlen($nova_senha) < 6) {
                    throw new Exception('Nova senha deve ter pelo menos 6 caracteres');
                }
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                execute_prepared_query(
                    "UPDATE barbeiros SET nome = ?, email = ?, telefone = ?, especialidade = ?, senha = ? WHERE id = ?",
                    [$nome, $email, $telefone, $especialidade, $senha_hash, $barbeiro_id],
                    'sssssi'
                );
            } else {
                execute_prepared_query(
                    "UPDATE barbeiros SET nome = ?, email = ?, telefone = ?, especialidade = ? WHERE id = ?",
                    [$nome, $email, $telefone, $especialidade, $barbeiro_id],
                    'ssssi'
                );
            }
            
            $sucesso = "Barbeiro '{$nome}' atualizado com sucesso!";
            error_log("Barbeiro editado ID: {$barbeiro_id} por admin ID: {$admin['id']}");
            
        } elseif ($acao === 'ativar_desativar') {
            // ATIVAR/DESATIVAR BARBEIRO
            $barbeiro_id = intval($_POST['barbeiro_id'] ?? 0);
            $ativo = intval($_POST['ativo'] ?? 0);
            
            if (!$barbeiro_id) {
                throw new Exception('ID do barbeiro é obrigatório');
            }
            
            // Buscar nome do barbeiro para feedback
            $barbeiro_result = execute_prepared_query(
                "SELECT nome FROM barbeiros WHERE id = ?",
                [$barbeiro_id],
                'i'
            );
            
            if (!$barbeiro_result || !($barbeiro_data = $barbeiro_result->fetch_assoc())) {
                throw new Exception('Barbeiro não encontrado');
            }
            
            execute_prepared_query(
                "UPDATE barbeiros SET ativo = ? WHERE id = ?",
                [$ativo, $barbeiro_id],
                'ii'
            );
            
            $status_text = $ativo ? 'ativado' : 'desativado';
            $sucesso = "Barbeiro '{$barbeiro_data['nome']}' foi {$status_text} com sucesso!";
            error_log("Barbeiro {$status_text} ID: {$barbeiro_id} por admin ID: {$admin['id']}");
        }
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
        error_log("Erro na gestão de barbeiros: " . $e->getMessage());
    }
}

// === BUSCAR BARBEIROS ===
$busca = $_GET['busca'] ?? '';
$where_clause = '';
$params = [];
$types = '';

if ($busca) {
    $where_clause = "WHERE (nome LIKE ? OR email LIKE ? OR especialidade LIKE ?)";
    $busca_term = "%{$busca}%";
    $params = [$busca_term, $busca_term, $busca_term];
    $types = 'sss';
}

$barbeiros_result = execute_prepared_query(
    "SELECT b.*, 
            COUNT(a.id) as total_agendamentos,
            COALESCE(SUM(CASE WHEN a.status = 'concluido' AND YEAR(a.data_hora) = YEAR(CURDATE()) AND MONTH(a.data_hora) = MONTH(CURDATE()) THEN s.preco ELSE 0 END), 0) as receita_mes
     FROM barbeiros b
     LEFT JOIN agendamentos a ON b.id = a.barbeiro_id AND a.data_hora >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
     LEFT JOIN servicos s ON a.servico_id = s.id
     {$where_clause}
     GROUP BY b.id
     ORDER BY b.nome ASC",
    $params,
    $types
);

$barbeiros = [];
if ($barbeiros_result) {
    while ($row = $barbeiros_result->fetch_assoc()) {
        $barbeiros[] = $row;
    }
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
                        <h1 class="text-xl font-bold text-gray-900">Gestão de Barbeiros</h1>
                        <p class="text-sm text-gray-600">Cadastrar, editar e gerenciar barbeiros</p>
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

        <!-- Barra de Ações -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-6">
            <div class="p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                    <!-- Busca -->
                    <form method="GET" class="flex-1 max-w-md">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input 
                                type="text" 
                                name="busca" 
                                value="<?= htmlspecialchars($busca) ?>"
                                placeholder="Buscar por nome, email ou especialidade..." 
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado"
                            >
                        </div>
                    </form>

                    <!-- Botão Novo Barbeiro -->
                    <button onclick="abrirModalCriar()" class="bg-gradient-to-r from-dourado to-dourado_escuro text-white px-6 py-2 rounded-lg hover:shadow-lg transition-all duration-200 flex items-center">
                        <i class="fas fa-plus mr-2"></i>
                        Novo Barbeiro
                    </button>
                </div>
            </div>
        </div>

        <!-- Lista de Barbeiros -->
        <div class="bg-white shadow-lg rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-users mr-2 text-dourado"></i>
                    Barbeiros Cadastrados (<?= count($barbeiros) ?>)
                </h3>
            </div>

            <?php if (empty($barbeiros)): ?>
                <div class="p-8 text-center">
                    <i class="fas fa-users-slash text-4xl text-gray-400 mb-4"></i>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Nenhum barbeiro encontrado</h4>
                    <p class="text-gray-600 mb-4">
                        <?= $busca ? 'Nenhum barbeiro corresponde à sua busca.' : 'Comece cadastrando o primeiro barbeiro.' ?>
                    </p>
                    <?php if (!$busca): ?>
                        <button onclick="abrirModalCriar()" class="bg-dourado text-white px-6 py-2 rounded-lg hover:bg-dourado_escuro transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Cadastrar Primeiro Barbeiro
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barbeiro</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contato</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($barbeiros as $barbeiro): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <!-- Dados do Barbeiro -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 bg-gradient-to-r from-dourado to-dourado_escuro rounded-full flex items-center justify-center mr-3">
                                                <span class="text-white font-medium">
                                                    <?= strtoupper(substr($barbeiro['nome'], 0, 1)) ?>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($barbeiro['nome']) ?></div>
                                                <div class="text-sm text-gray-500"><?= htmlspecialchars($barbeiro['especialidade']) ?></div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Contato -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($barbeiro['email']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($barbeiro['telefone']) ?></div>
                                    </td>

                                    <!-- Performance -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">R$ <?= number_format($barbeiro['receita_mes'], 2, ',', '.') ?></div>
                                        <div class="text-sm text-gray-500"><?= $barbeiro['total_agendamentos'] ?> agendamentos (30d)</div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $barbeiro['ativo'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= $barbeiro['ativo'] ? 'Ativo' : 'Inativo' ?>
                                        </span>
                                    </td>

                                    <!-- Ações -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex justify-center space-x-2">
                                            <button onclick="abrirModalEditar(<?= htmlspecialchars(json_encode($barbeiro)) ?>)" 
                                                    class="text-blue-600 hover:text-blue-800 transition-colors" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            
                                            <form method="POST" class="inline" onsubmit="return confirm('<?= $barbeiro['ativo'] ? 'Desativar' : 'Ativar' ?> este barbeiro?')">
                                                <input type="hidden" name="acao" value="ativar_desativar">
                                                <input type="hidden" name="barbeiro_id" value="<?= $barbeiro['id'] ?>">
                                                <input type="hidden" name="ativo" value="<?= $barbeiro['ativo'] ? 0 : 1 ?>">
                                                <button type="submit" class="<?= $barbeiro['ativo'] ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' ?> transition-colors" 
                                                        title="<?= $barbeiro['ativo'] ? 'Desativar' : 'Ativar' ?>">
                                                    <i class="fas fa-<?= $barbeiro['ativo'] ? 'times' : 'check' ?>"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Criar Barbeiro -->
<div id="modalCriar" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" onclick="fecharModal('modalCriar')">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-user-plus mr-2 text-dourado"></i>
                    Cadastrar Novo Barbeiro
                </h3>
            </div>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="acao" value="criar">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
                    <input type="text" name="nome" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone *</label>
                    <input type="tel" name="telefone" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Especialidade</label>
                    <input type="text" name="especialidade" placeholder="Ex: Cortes modernos, Barbas, etc." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Senha *</label>
                    <input type="password" name="senha" required minlength="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Senha *</label>
                    <input type="password" name="confirmar_senha" required minlength="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
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

<!-- Modal Editar Barbeiro -->
<div id="modalEditar" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" onclick="fecharModal('modalEditar')">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-user-edit mr-2 text-dourado"></i>
                    Editar Barbeiro
                </h3>
            </div>

            <form method="POST" class="space-y-4" id="formEditar">
                <input type="hidden" name="acao" value="editar">
                <input type="hidden" name="barbeiro_id" id="edit_barbeiro_id">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
                    <input type="text" name="nome" id="edit_nome" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" id="edit_email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone *</label>
                    <input type="tel" name="telefone" id="edit_telefone" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Especialidade</label>
                    <input type="text" name="especialidade" id="edit_especialidade" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nova Senha (deixe vazio para manter atual)</label>
                    <input type="password" name="nova_senha" minlength="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-dourado focus:border-dourado">
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

function abrirModalEditar(barbeiro) {
    document.getElementById('edit_barbeiro_id').value = barbeiro.id;
    document.getElementById('edit_nome').value = barbeiro.nome;
    document.getElementById('edit_email').value = barbeiro.email;
    document.getElementById('edit_telefone').value = barbeiro.telefone;
    document.getElementById('edit_especialidade').value = barbeiro.especialidade || '';
    
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