<?php
/**
 * Página de registo de clientes
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Permitir registo de novos clientes
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Se já está logado, redirecionar
if (is_logged_in('cliente')) {
    safe_redirect(get_base_url('pages/perfil.php'));
}

$errors = [];
$success_message = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF token (será implementado no security.php)
    
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validações
    if (empty($nome)) {
        $errors['nome'] = 'Nome é obrigatório.';
    } elseif (strlen($nome) < 2) {
        $errors['nome'] = 'Nome deve ter pelo menos 2 caracteres.';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email é obrigatório.';
    } elseif (!is_valid_email($email)) {
        $errors['email'] = 'Email inválido.';
    } else {
        // Verificar se email já existe
        try {
            $stmt = $pdo->prepare("SELECT id FROM clientes WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $errors['email'] = 'Este email já está registado.';
            }
        } catch (Exception $e) {
            error_log("Erro ao verificar email: " . $e->getMessage());
            $errors['email'] = 'Erro ao verificar email. Tente novamente.';
        }
    }
    
    if (!empty($telefone) && !is_valid_phone($telefone)) {
        $errors['telefone'] = 'Telefone inválido.';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password é obrigatória.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password deve ter pelo menos 8 caracteres.';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        $errors['password'] = 'Password deve ter pelo menos uma letra minúscula, uma maiúscula e um número.';
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'As passwords não coincidem.';
    }
    
    // Se não há erros, criar conta
    if (empty($errors)) {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO clientes (nome, email, telefone, password_hash) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $telefone, $password_hash]);
            
            $success_message = 'Conta criada com sucesso! Pode fazer login agora.';
            
            // Limpar dados do formulário
            $nome = $email = $telefone = '';
            
        } catch (Exception $e) {
            error_log("Erro ao criar cliente: " . $e->getMessage());
            $errors['general'] = 'Erro ao criar conta. Tente novamente.';
        }
    }
}

$page_title = 'Criar Conta - ' . SITE_NAME;
include_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <i class="fas fa-user-plus text-4xl text-barbershop-gold mb-4"></i>
            <h1 class="text-3xl font-bold text-gray-900">Criar Conta</h1>
            <p class="mt-2 text-gray-600">Junte-se ao Mr. Carlos Barbershop</p>
        </div>

        <!-- Mensagem de sucesso -->
        <?php if ($success_message): ?>
            <?php echo show_message($success_message, 'success'); ?>
        <?php endif; ?>

        <!-- Erro geral -->
        <?php if (isset($errors['general'])): ?>
            <?php echo show_message($errors['general'], 'error'); ?>
        <?php endif; ?>

        <!-- Formulário -->
        <form method="POST" class="mt-8 space-y-6" novalidate>
            <div class="space-y-4">
                <!-- Nome -->
                <div class="form-group">
                    <label for="nome" class="form-label">
                        Nome Completo <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="nome" 
                        name="nome" 
                        value="<?php echo esc($nome ?? ''); ?>"
                        required
                        class="form-input <?php echo isset($errors['nome']) ? 'error' : ''; ?>"
                        placeholder="Insira o seu nome completo"
                        autocomplete="name"
                    >
                    <?php if (isset($errors['nome'])): ?>
                        <span class="form-error"><?php echo esc($errors['nome']); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?php echo esc($email ?? ''); ?>"
                        required
                        class="form-input <?php echo isset($errors['email']) ? 'error' : ''; ?>"
                        placeholder="seuemail@exemplo.com"
                        autocomplete="email"
                    >
                    <?php if (isset($errors['email'])): ?>
                        <span class="form-error"><?php echo esc($errors['email']); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Telefone -->
                <div class="form-group">
                    <label for="telefone" class="form-label">
                        Telefone
                    </label>
                    <input 
                        type="tel" 
                        id="telefone" 
                        name="telefone" 
                        value="<?php echo esc($telefone ?? ''); ?>"
                        class="form-input <?php echo isset($errors['telefone']) ? 'error' : ''; ?>"
                        placeholder="9xxxxxxxx"
                        autocomplete="tel"
                    >
                    <?php if (isset($errors['telefone'])): ?>
                        <span class="form-error"><?php echo esc($errors['telefone']); ?></span>
                    <?php endif; ?>
                    <small class="text-gray-500 text-sm">Opcional. Formato: 9xxxxxxxx</small>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="form-input pr-10 <?php echo isset($errors['password']) ? 'error' : ''; ?>"
                            placeholder="Mínimo 8 caracteres"
                            autocomplete="new-password"
                        >
                        <button 
                            type="button" 
                            class="absolute inset-y-0 right-0 px-3 flex items-center"
                            onclick="togglePassword('password')"
                        >
                            <i class="fas fa-eye text-gray-400" id="password-icon"></i>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <span class="form-error"><?php echo esc($errors['password']); ?></span>
                    <?php endif; ?>
                    <small class="text-gray-500 text-sm">
                        Deve conter pelo menos 8 caracteres, uma letra maiúscula, uma minúscula e um número.
                    </small>
                </div>

                <!-- Confirmar Password -->
                <div class="form-group">
                    <label for="confirm_password" class="form-label">
                        Confirmar Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            required
                            class="form-input pr-10 <?php echo isset($errors['confirm_password']) ? 'error' : ''; ?>"
                            placeholder="Repita a password"
                            autocomplete="new-password"
                        >
                        <button 
                            type="button" 
                            class="absolute inset-y-0 right-0 px-3 flex items-center"
                            onclick="togglePassword('confirm_password')"
                        >
                            <i class="fas fa-eye text-gray-400" id="confirm_password-icon"></i>
                        </button>
                    </div>
                    <?php if (isset($errors['confirm_password'])): ?>
                        <span class="form-error"><?php echo esc($errors['confirm_password']); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Termos -->
                <div class="form-group">
                    <label class="flex items-start">
                        <input 
                            type="checkbox" 
                            required 
                            class="mt-1 mr-2"
                        >
                        <span class="text-sm text-gray-600">
                            Aceito os 
                            <a href="<?php echo get_base_url('pages/terms.php'); ?>" 
                               class="text-barbershop-gold hover:text-barbershop-gold-hover underline" 
                               target="_blank">
                                Termos de Uso
                            </a> 
                            e a 
                            <a href="<?php echo get_base_url('pages/privacy.php'); ?>" 
                               class="text-barbershop-gold hover:text-barbershop-gold-hover underline"
                               target="_blank">
                                Política de Privacidade
                            </a>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Botão Submit -->
            <div>
                <button 
                    type="submit" 
                    class="w-full btn btn-primary btn-lg"
                >
                    <i class="fas fa-user-plus mr-2"></i>
                    Criar Conta
                </button>
            </div>

            <!-- Link para login -->
            <div class="text-center">
                <p class="text-gray-600">
                    Já tem conta? 
                    <a href="<?php echo get_base_url('pages/login.php'); ?>" 
                       class="text-barbershop-gold hover:text-barbershop-gold-hover font-medium">
                        Fazer login
                    </a>
                </p>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Validação de password em tempo real
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /\d/.test(password)
    };
    
    // Aqui pode adicionar indicador visual da força da password
});

// Validação de confirmação em tempo real
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && password !== confirmPassword) {
        this.classList.add('error');
    } else {
        this.classList.remove('error');
    }
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>