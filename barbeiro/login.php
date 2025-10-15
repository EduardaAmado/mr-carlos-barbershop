<?php
/**
 * Login para barbeiros
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Autenticação específica para barbeiros
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Se já está logado como barbeiro, redirecionar para dashboard
if (is_logged_in('barbeiro')) {
    safe_redirect(get_base_url('barbeiro/dashboard.php'));
}

$errors = [];
$login_attempts = $_SESSION['barbeiro_login_attempts'] ?? 0;
$locked_until = $_SESSION['barbeiro_locked_until'] ?? null;

// Verificar se está bloqueado
if ($locked_until && time() < $locked_until) {
    $remaining = ceil(($locked_until - time()) / 60);
    $errors['general'] = "Muitas tentativas falhadas. Tente novamente em {$remaining} minutos.";
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!$locked_until || time() >= $locked_until)) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validações básicas
    if (empty($email)) {
        $errors['email'] = 'Email é obrigatório.';
    } elseif (!is_valid_email($email)) {
        $errors['email'] = 'Email inválido.';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password é obrigatória.';
    }
    
    // Se não há erros de validação, tentar autenticar
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id, nome, email, password_hash, ativo FROM barbeiros WHERE email = ?");
            $stmt->execute([$email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                // Verificar se conta está ativa
                if (!$row['ativo']) {
                    $errors['general'] = 'Conta desativada. Contacte a administração.';
                } elseif (password_verify($password, $row['password_hash'])) {
                    // Login bem-sucedido
                    session_regenerate_id(true);
                    
                    $_SESSION['barbeiro'] = [
                        'id' => $row['id'],
                        'nome' => $row['nome'],
                        'email' => $row['email'],
                        'type' => 'barbeiro'
                    ];
                    
                    // Limpar tentativas de login
                    unset($_SESSION['barbeiro_login_attempts'], $_SESSION['barbeiro_locked_until']);
                    
                    // Log da ação
                    error_log("Login barbeiro: {$row['email']} (ID: {$row['id']})");
                    
                    // Redirecionar para dashboard
                    safe_redirect(get_base_url('barbeiro/dashboard.php'));
                    
                } else {
                    // Password incorreta
                    $login_attempts++;
                    $errors['general'] = 'Email ou password incorretos.';
                }
            } else {
                // Email não encontrado
                $login_attempts++;
                $errors['general'] = 'Email ou password incorretos.';
            }
            
            // Gerenciar tentativas falhadas
            if (isset($errors['general'])) {
                $_SESSION['barbeiro_login_attempts'] = $login_attempts;
                
                if ($login_attempts >= MAX_LOGIN_ATTEMPTS) {
                    $_SESSION['barbeiro_locked_until'] = time() + LOGIN_TIMEOUT;
                    $errors['general'] = 'Muitas tentativas falhadas. Conta bloqueada por 15 minutos.';
                }
            }
            
        } catch (Exception $e) {
            error_log("Erro no login barbeiro: " . $e->getMessage());
            $errors['general'] = 'Erro interno. Tente novamente.';
        }
    }
}

$page_title = 'Login Barbeiro - ' . SITE_NAME;
include_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 to-black py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <div class="w-20 h-20 bg-barbershop-gold rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-cut text-3xl text-black"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Área do Barbeiro</h1>
            <p class="text-gray-400">Acesso profissional ao sistema</p>
        </div>

        <!-- Erro geral -->
        <?php if (isset($errors['general'])): ?>
            <div class="bg-red-500 text-white px-4 py-3 rounded-lg mb-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?php echo esc($errors['general']); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Formulário -->
        <div class="bg-white rounded-lg shadow-xl p-8">
            <form method="POST" class="space-y-6">
                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label text-gray-700">
                        <i class="fas fa-envelope mr-2 text-barbershop-gold"></i>
                        Email Profissional
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?php echo esc($email ?? ''); ?>"
                        required
                        class="form-input <?php echo isset($errors['email']) ? 'error' : ''; ?>"
                        placeholder="barbeiro@mrcarlosbarbershop.pt"
                        autocomplete="email"
                        <?php echo ($locked_until && time() < $locked_until) ? 'disabled' : ''; ?>
                    >
                    <?php if (isset($errors['email'])): ?>
                        <span class="form-error"><?php echo esc($errors['email']); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label text-gray-700">
                        <i class="fas fa-lock mr-2 text-barbershop-gold"></i>
                        Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="form-input pr-10 <?php echo isset($errors['password']) ? 'error' : ''; ?>"
                            placeholder="A sua password"
                            autocomplete="current-password"
                            <?php echo ($locked_until && time() < $locked_until) ? 'disabled' : ''; ?>
                        >
                        <button 
                            type="button" 
                            class="absolute inset-y-0 right-0 px-3 flex items-center"
                            onclick="togglePassword('password')"
                            tabindex="-1"
                        >
                            <i class="fas fa-eye text-gray-400" id="password-icon"></i>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <span class="form-error"><?php echo esc($errors['password']); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Indicador de tentativas -->
                <?php if ($login_attempts > 0 && $login_attempts < MAX_LOGIN_ATTEMPTS): ?>
                    <div class="text-center text-sm text-yellow-600 bg-yellow-50 p-2 rounded">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <?php echo $login_attempts; ?> de <?php echo MAX_LOGIN_ATTEMPTS; ?> tentativas utilizadas
                    </div>
                <?php endif; ?>

                <!-- Botão Submit -->
                <button 
                    type="submit" 
                    class="w-full btn btn-primary btn-lg"
                    <?php echo ($locked_until && time() < $locked_until) ? 'disabled' : ''; ?>
                >
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Entrar no Sistema
                </button>
            </form>

            <!-- Links úteis -->
            <div class="mt-6 pt-6 border-t border-gray-200 text-center space-y-2">
                <p class="text-sm text-gray-500">Esqueceu a password?</p>
                <p class="text-xs text-gray-400">Contacte a administração para recuperar o acesso</p>
                
                <div class="flex justify-center space-x-4 mt-4">
                    <a href="<?php echo get_base_url(); ?>" 
                       class="text-sm text-gray-600 hover:text-barbershop-gold">
                        <i class="fas fa-home mr-1"></i>
                        Página Inicial
                    </a>
                    <a href="<?php echo get_base_url('pages/login.php'); ?>" 
                       class="text-sm text-gray-600 hover:text-barbershop-gold">
                        <i class="fas fa-user mr-1"></i>
                        Login Cliente
                    </a>
                </div>
            </div>
        </div>

        <!-- Informação adicional -->
        <div class="text-center">
            <div class="bg-black bg-opacity-50 rounded-lg p-4">
                <h3 class="text-white font-semibold mb-2">
                    <i class="fas fa-info-circle mr-2 text-barbershop-gold"></i>
                    Área Profissional
                </h3>
                <p class="text-gray-300 text-sm">
                    Esta área é exclusiva para barbeiros. Aqui pode gerir os seus agendamentos, 
                    definir disponibilidade e acompanhar o seu trabalho.
                </p>
            </div>
        </div>
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

// Auto-focus no primeiro campo
document.addEventListener('DOMContentLoaded', function() {
    const emailField = document.getElementById('email');
    if (emailField && !emailField.disabled) {
        emailField.focus();
    }
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>