<?php
/**
 * Página de perfil do cliente
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Gerir dados pessoais e ver histórico de agendamentos
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../includes/helpers.php';

// Verificar se está logado
if (!is_logged_in('cliente')) {
    safe_redirect(get_base_url('pages/login.php'));
}

$user = get_logged_user('cliente');
$errors = [];
$success_message = '';

// Obter dados completos do cliente
try {
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->execute([$user['id']]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cliente) {
        throw new Exception("Cliente não encontrado");
    }
} catch (Exception $e) {
    error_log("Erro ao obter dados do cliente: " . $e->getMessage());
    safe_redirect(get_base_url('pages/logout.php'));
}

// Processar atualização de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $nome = trim($_POST['nome'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $data_nascimento = $_POST['data_nascimento'] ?? null;
    
    // Validações
    if (empty($nome)) {
        $errors['nome'] = 'Nome é obrigatório.';
    } elseif (strlen($nome) < 2) {
        $errors['nome'] = 'Nome deve ter pelo menos 2 caracteres.';
    }
    
    if (!empty($telefone) && !is_valid_phone($telefone)) {
        $errors['telefone'] = 'Telefone inválido.';
    }
    
    if (!empty($data_nascimento)) {
        $date = DateTime::createFromFormat('Y-m-d', $data_nascimento);
        if (!$date || $date->format('Y-m-d') !== $data_nascimento) {
            $errors['data_nascimento'] = 'Data de nascimento inválida.';
        } elseif ($date > new DateTime()) {
            $errors['data_nascimento'] = 'Data de nascimento não pode ser no futuro.';
        }
    }
    
    // Se não há erros, atualizar dados
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE clientes SET nome = ?, telefone = ?, data_nascimento = ? WHERE id = ?");
            $stmt->execute([$nome, $telefone, $data_nascimento ?: null, $user['id']]);
            
            // Atualizar dados na sessão
            $_SESSION['user']['nome'] = $nome;
            
            // Atualizar dados locais
            $cliente['nome'] = $nome;
            $cliente['telefone'] = $telefone;
            $cliente['data_nascimento'] = $data_nascimento;
            
            $success_message = 'Perfil atualizado com sucesso!';
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar perfil: " . $e->getMessage());
            $errors['general'] = 'Erro ao atualizar perfil. Tente novamente.';
        }
    }
}

// Processar alteração de password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validações
    if (empty($current_password)) {
        $errors['current_password'] = 'Password atual é obrigatória.';
    } elseif (!password_verify($current_password, $cliente['password_hash'])) {
        $errors['current_password'] = 'Password atual incorreta.';
    }
    
    if (empty($new_password)) {
        $errors['new_password'] = 'Nova password é obrigatória.';
    } elseif (strlen($new_password) < 8) {
        $errors['new_password'] = 'Nova password deve ter pelo menos 8 caracteres.';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $new_password)) {
        $errors['new_password'] = 'Nova password deve ter pelo menos uma letra minúscula, uma maiúscula e um número.';
    }
    
    if ($new_password !== $confirm_password) {
        $errors['confirm_password'] = 'As passwords não coincidem.';
    }
    
    // Se não há erros, alterar password
    if (empty($errors)) {
        try {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE clientes SET password_hash = ? WHERE id = ?");
            $stmt->execute([$password_hash, $user['id']]);
            
            $success_message = 'Password alterada com sucesso!';
            
        } catch (Exception $e) {
            error_log("Erro ao alterar password: " . $e->getMessage());
            $errors['password_general'] = 'Erro ao alterar password. Tente novamente.';
        }
    }
}

// Obter histórico de agendamentos
try {
    $stmt = $pdo->prepare(
        "SELECT a.*, b.nome as barbeiro_nome, s.nome as servico_nome, s.preco 
         FROM agendamentos a 
         LEFT JOIN barbeiros b ON a.barbeiro_id = b.id 
         LEFT JOIN servicos s ON a.servico_id = s.id 
         WHERE a.cliente_id = ? 
         ORDER BY a.data_hora DESC 
         LIMIT 10"
    );
    $stmt->execute([$user['id']]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Erro ao obter agendamentos: " . $e->getMessage());
    $agendamentos = [];
}

$page_title = 'Meu Perfil - ' . SITE_NAME;
include_once __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header da página -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Meu Perfil</h1>
        <p class="text-gray-600">Gerir os seus dados pessoais e histórico</p>
    </div>

    <!-- Mensagem de sucesso -->
    <?php if ($success_message): ?>
        <?php echo show_message($success_message, 'success'); ?>
    <?php endif; ?>

    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Coluna principal -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Dados Pessoais -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-xl font-semibold flex items-center">
                        <i class="fas fa-user mr-2 text-barbershop-gold"></i>
                        Dados Pessoais
                    </h2>
                </div>
                <div class="card-body">
                    <?php if (isset($errors['general'])): ?>
                        <?php echo show_message($errors['general'], 'error'); ?>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <!-- Nome -->
                            <div class="form-group">
                                <label for="nome" class="form-label">Nome Completo</label>
                                <input 
                                    type="text" 
                                    id="nome" 
                                    name="nome" 
                                    value="<?php echo esc($cliente['nome']); ?>"
                                    required
                                    class="form-input <?php echo isset($errors['nome']) ? 'error' : ''; ?>"
                                >
                                <?php if (isset($errors['nome'])): ?>
                                    <span class="form-error"><?php echo esc($errors['nome']); ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- Email (readonly) -->
                            <div class="form-group">
                                <label for="email" class="form-label">Email</label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    value="<?php echo esc($cliente['email']); ?>"
                                    readonly
                                    class="form-input bg-gray-100"
                                >
                                <small class="text-gray-500 text-sm">Para alterar o email, contacte o suporte.</small>
                            </div>

                            <!-- Telefone -->
                            <div class="form-group">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input 
                                    type="tel" 
                                    id="telefone" 
                                    name="telefone" 
                                    value="<?php echo esc($cliente['telefone'] ?? ''); ?>"
                                    class="form-input <?php echo isset($errors['telefone']) ? 'error' : ''; ?>"
                                    placeholder="9xxxxxxxx"
                                >
                                <?php if (isset($errors['telefone'])): ?>
                                    <span class="form-error"><?php echo esc($errors['telefone']); ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- Data de Nascimento -->
                            <div class="form-group">
                                <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                                <input 
                                    type="date" 
                                    id="data_nascimento" 
                                    name="data_nascimento" 
                                    value="<?php echo esc($cliente['data_nascimento'] ?? ''); ?>"
                                    class="form-input <?php echo isset($errors['data_nascimento']) ? 'error' : ''; ?>"
                                    max="<?php echo date('Y-m-d'); ?>"
                                >
                                <?php if (isset($errors['data_nascimento'])): ?>
                                    <span class="form-error"><?php echo esc($errors['data_nascimento']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>
                                Guardar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Alterar Password -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-xl font-semibold flex items-center">
                        <i class="fas fa-lock mr-2 text-barbershop-gold"></i>
                        Alterar Password
                    </h2>
                </div>
                <div class="card-body">
                    <?php if (isset($errors['password_general'])): ?>
                        <?php echo show_message($errors['password_general'], 'error'); ?>
                    <?php endif; ?>

                    <form method="POST" id="password-form">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="space-y-4">
                            <!-- Password Atual -->
                            <div class="form-group">
                                <label for="current_password" class="form-label">Password Atual</label>
                                <input 
                                    type="password" 
                                    id="current_password" 
                                    name="current_password" 
                                    required
                                    class="form-input <?php echo isset($errors['current_password']) ? 'error' : ''; ?>"
                                >
                                <?php if (isset($errors['current_password'])): ?>
                                    <span class="form-error"><?php echo esc($errors['current_password']); ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- Nova Password -->
                            <div class="form-group">
                                <label for="new_password" class="form-label">Nova Password</label>
                                <input 
                                    type="password" 
                                    id="new_password" 
                                    name="new_password" 
                                    required
                                    class="form-input <?php echo isset($errors['new_password']) ? 'error' : ''; ?>"
                                    minlength="8"
                                >
                                <?php if (isset($errors['new_password'])): ?>
                                    <span class="form-error"><?php echo esc($errors['new_password']); ?></span>
                                <?php endif; ?>
                                <small class="text-gray-500 text-sm">Mínimo 8 caracteres com letras e números</small>
                            </div>

                            <!-- Confirmar Nova Password -->
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">Confirmar Nova Password</label>
                                <input 
                                    type="password" 
                                    id="confirm_password" 
                                    name="confirm_password" 
                                    required
                                    class="form-input <?php echo isset($errors['confirm_password']) ? 'error' : ''; ?>"
                                >
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <span class="form-error"><?php echo esc($errors['confirm_password']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key mr-2"></i>
                                Alterar Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Informações da Conta -->
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold">Informações da Conta</h3>
                </div>
                <div class="card-body space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Cliente desde:</span>
                        <span class="font-medium"><?php echo format_date($cliente['data_registo'], 'M/Y'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Último login:</span>
                        <span class="font-medium">
                            <?php echo $cliente['ultimo_login'] ? format_date($cliente['ultimo_login'], 'd/m/Y H:i') : 'Nunca'; ?>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total agendamentos:</span>
                        <span class="font-medium"><?php echo count($agendamentos); ?></span>
                    </div>
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold">Ações Rápidas</h3>
                </div>
                <div class="card-body space-y-3">
                    <a href="<?php echo get_base_url('pages/agendar.php'); ?>" 
                       class="btn btn-primary w-full">
                        <i class="fas fa-calendar-plus mr-2"></i>
                        Novo Agendamento
                    </a>
                    <a href="<?php echo get_base_url('pages/meus-agendamentos.php'); ?>" 
                       class="btn btn-outline w-full">
                        <i class="fas fa-history mr-2"></i>
                        Ver Histórico
                    </a>
                    <a href="<?php echo get_base_url('pages/servicos.php'); ?>" 
                       class="btn btn-outline w-full">
                        <i class="fas fa-cut mr-2"></i>
                        Ver Serviços
                    </a>
                </div>
            </div>

            <!-- Últimos Agendamentos -->
            <?php if (!empty($agendamentos)): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold">Últimos Agendamentos</h3>
                </div>
                <div class="card-body">
                    <div class="space-y-3">
                        <?php foreach (array_slice($agendamentos, 0, 3) as $agendamento): ?>
                        <div class="border-l-4 border-barbershop-gold pl-3">
                            <div class="font-medium text-sm"><?php echo esc($agendamento['servico_nome']); ?></div>
                            <div class="text-gray-600 text-xs">
                                <?php echo format_datetime_pt($agendamento['data_hora']); ?>
                            </div>
                            <div class="text-xs">
                                <span class="badge badge-<?php echo $agendamento['status'] === 'concluido' ? 'success' : ($agendamento['status'] === 'cancelado' ? 'error' : 'info'); ?>">
                                    <?php echo ucfirst($agendamento['status']); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (count($agendamentos) > 3): ?>
                    <div class="mt-4 pt-3 border-t">
                        <a href="<?php echo get_base_url('pages/meus-agendamentos.php'); ?>" 
                           class="text-sm text-barbershop-gold hover:underline">
                            Ver todos os agendamentos
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Validação da confirmação de password
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && newPassword !== confirmPassword) {
        this.classList.add('error');
    } else {
        this.classList.remove('error');
    }
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>