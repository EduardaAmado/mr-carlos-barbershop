<?php
/**
 * Página de login unificado - Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 15 de Outubro de 2025
 * Finalidade: Autenticação unificada para clientes, barbeiros e administradores
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/security_middleware.php';
require_once __DIR__ . '/../includes/security.php';

// Se já está logado, redirecionar para dashboard apropriado
if (is_logged_in('cliente')) {
    safe_redirect(get_base_url('pages/perfil.php'));
} elseif (is_logged_in('barbeiro')) {
    safe_redirect(get_base_url('barbeiro/dashboard.php'));
} elseif (is_logged_in('admin')) {
    safe_redirect(get_base_url('admin/index.php'));
}

$errors = [];

// Processar formulário com middleware de segurança
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Login POST recebido para email: " . ($_POST['email'] ?? 'não informado'));
    
    // Aplicar middleware de segurança
    $security_result = secure_form_handler('login', [
        'email' => 'email',
        'password' => 'string'
    ]);
    
    if (!$security_result['success']) {
        error_log("Falha no middleware de segurança: " . json_encode($security_result));
        $errors['general'] = $security_result['error'] ?? implode('<br>', $security_result['errors'] ?? []);
    } else {
        error_log("Middleware de segurança passou. Tentando autenticação unificada...");
        $clean_data = $security_result['data'];
        $email = $clean_data['email'];
        $password = $clean_data['password'];
        $remember_me = isset($_POST['remember_me']);
        
        // Tentar autenticar em todas as tabelas de usuários
        try {
            $user_found = null;
            $user_type = null;
            $redirect_page = null;
            
            // 1. Verificar se é cliente
            $stmt = $pdo->prepare("SELECT id, nome, email, password_hash, ativo FROM clientes WHERE email = ?");
            $stmt->execute([$email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                $user_found = $row;
                $user_type = 'cliente';
                $redirect_page = 'pages/perfil.php';
                error_log("Usuário encontrado como CLIENTE (ID: {$row['id']})");
            }
            
            // 2. Se não é cliente, verificar se é barbeiro
            if (!$user_found) {
                $stmt = $pdo->prepare("SELECT id, nome, email, password_hash, ativo FROM barbeiros WHERE email = ?");
                $stmt->execute([$email]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($row) {
                    $user_found = $row;
                    $user_type = 'barbeiro';
                    $redirect_page = 'barbeiro/dashboard.php';
                    error_log("Usuário encontrado como BARBEIRO (ID: {$row['id']})");
                }
            }
            
            // 3. Se não é cliente nem barbeiro, verificar se é admin
            if (!$user_found) {
                $stmt = $pdo->prepare("SELECT id, nome, email, password_hash, ativo, nivel FROM administradores WHERE email = ?");
                $stmt->execute([$email]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($row) {
                    $user_found = $row;
                    $user_type = 'admin';
                    $redirect_page = 'admin/index.php';
                    error_log("Usuário encontrado como ADMIN (ID: {$row['id']})");
                }
            }
            
            if ($user_found) {
                // Verificar se conta está ativa
                if (!$user_found['ativo']) {
                    $errors['general'] = 'Conta desativada. Contacte o suporte.';
                } elseif (password_verify($password, $user_found['password_hash'])) {
                    // Login bem-sucedido
                    error_log("Senha verificada com sucesso para: {$user_found['email']} como {$user_type}");
                    session_regenerate_id(true);
                    
                    // Criar sessão baseada no tipo de usuário
                    if ($user_type === 'cliente') {
                        $_SESSION['user'] = [
                            'id' => $user_found['id'],
                            'nome' => $user_found['nome'],
                            'email' => $user_found['email'],
                            'type' => 'cliente'
                        ];
                    } elseif ($user_type === 'barbeiro') {
                        $_SESSION['barbeiro'] = [
                            'id' => $user_found['id'],
                            'nome' => $user_found['nome'],
                            'email' => $user_found['email'],
                            'type' => 'barbeiro'
                        ];
                    } elseif ($user_type === 'admin') {
                        $_SESSION['admin'] = [
                            'id' => $user_found['id'],
                            'nome' => $user_found['nome'],
                            'email' => $user_found['email'],
                            'nivel' => $user_found['nivel'] ?? 1,
                            'logged_in' => true
                        ];
                        $_SESSION['login_time'] = time();
                    }
                    
                    // Limpar tentativas de login
                    unset($_SESSION['login_attempts'], $_SESSION['locked_until']);
                    
                    // Atualizar último login na tabela correspondente
                    $table_map = [
                        'cliente' => 'clientes',
                        'barbeiro' => 'barbeiros', 
                        'admin' => 'administradores'
                    ];
                    
                    $table = $table_map[$user_type];
                    $stmt = $pdo->prepare("UPDATE {$table} SET ultimo_login = NOW() WHERE id = ?");
                    $stmt->execute([$user_found['id']]);
                    
                    // Configurar cookie "lembrar-me" se solicitado
                    if ($remember_me) {
                        $token = bin2hex(random_bytes(32));
                        // Aqui implementaria sistema de tokens de "lembrar-me"
                    }
                    
                    // Redirecionar para dashboard apropriado
                    record_attempt('login', $email, true);
                    
                    // Permitir redirecionamento customizado ou usar padrão baseado no tipo
                    $final_redirect = $_GET['redirect'] ?? $redirect_page;
                    error_log("Redirecionando usuário {$user_type} para: " . get_base_url($final_redirect));
                    safe_redirect(get_base_url($final_redirect));
                    
                } else {
                    // Password incorreta
                    record_attempt('login', $email, false);
                    $errors['general'] = 'Email ou password incorretos.';
                }
            } else {
                // Email não encontrado (não revelar)
                record_attempt('login', $email, false);
                $errors['general'] = 'Email ou password incorretos.';
            }
            
        } catch (Exception $e) {
            error_log("Erro no login: " . $e->getMessage());
            $errors['general'] = 'Erro interno. Tente novamente.';
            record_attempt('login', $email ?? '', false);
        }
    }
}

$page_title = 'Login - ' . SITE_NAME;
include_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <!-- Card do Login -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 space-y-8 border border-gray-200">
            <!-- Header com Logo e Branding -->
            <div class="text-center">
                <div class="mx-auto w-16 h-16 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center mb-6 shadow-lg">
                    <i class="fas fa-cut text-2xl text-black"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Mr. Carlos Barbershop</h1>
                <p class="text-gray-600 text-lg">Acesso ao Sistema</p>
                <div class="w-16 h-1 bg-gradient-to-r from-yellow-400 to-yellow-600 mx-auto mt-4 rounded-full"></div>
            </div>

            <!-- Mensagens de Feedback -->
            <?php if (isset($errors['general'])): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?php echo esc($errors['general']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Mensagem de sucesso se veio do registo -->
            <?php if (isset($_GET['registered'])): ?>
                <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">Conta criada com sucesso! Faça login para continuar.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Formulário -->
            <form method="POST" class="space-y-6">
                <?php echo csrf_field('login'); ?>
                <div class="space-y-5">
                    <!-- Campo Email -->
                    <div class="space-y-1">
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-envelope text-gray-400 mr-2"></i>Email
                        </label>
                        <div class="relative">
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="<?php echo esc($email ?? ''); ?>"
                                required
                                class="block w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition duration-200 <?php echo isset($errors['email']) ? 'border-red-400 focus:ring-red-400' : ''; ?>"
                                placeholder="seu@email.com"
                                autocomplete="email"
                            >
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                        </div>
                        <?php if (isset($errors['email'])): ?>
                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <?php echo esc($errors['email']); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Campo Password -->
                    <div class="space-y-1">
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-lock text-gray-400 mr-2"></i>Senha
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                                class="block w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-xl text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition duration-200 <?php echo isset($errors['password']) ? 'border-red-400 focus:ring-red-400' : ''; ?>"
                                placeholder="Digite sua senha"
                                autocomplete="current-password"
                            >
                            <button 
                                type="button" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
                                onclick="togglePassword('password')"
                                tabindex="-1"
                                title="Mostrar/ocultar senha"
                            >
                                <i class="fas fa-eye" id="password-icon"></i>
                            </button>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <?php echo esc($errors['password']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
            </div>

                <!-- Opções Extras -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer">
                        <div class="relative">
                            <input 
                                type="checkbox" 
                                name="remember_me" 
                                class="sr-only"
                                id="remember_me"
                                onchange="toggleCheckbox()"
                            >
                            <div class="w-5 h-5 bg-white border-2 border-gray-300 rounded-md flex items-center justify-center transition duration-200" id="checkbox-bg">
                                <i class="fas fa-check text-white text-xs hidden" id="check-icon"></i>
                            </div>
                        </div>
                        <span class="ml-3 text-sm text-gray-600">Manter-me conectado</span>
                    </label>
                    
                    <a href="<?php echo get_base_url('pages/forgot-password.php'); ?>" 
                       class="text-sm text-yellow-600 hover:text-yellow-700 font-medium transition duration-200">
                        Esqueceu a senha?
                    </a>
                </div>

                <!-- Botão de Login -->
                <button 
                    type="submit" 
                    class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-lg font-semibold rounded-xl text-black bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-500 hover:to-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400 transition duration-200 transform hover:scale-105 shadow-lg"
                >
                    <span class="absolute left-0 inset-y-0 flex items-center pl-4">
                        <i class="fas fa-sign-in-alt text-black group-hover:text-black"></i>
                    </span>
                    Acessar Sistema
                </button>

            </form>
            
            <!-- Seção de Links -->
            <div class="text-center space-y-4">
                <!-- Divisor -->
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white text-gray-500">Novo por aqui?</span>
                    </div>
                </div>
                
                <!-- Link para registro -->
                <div>
                    <a href="<?php echo get_base_url('pages/register.php'); ?>" 
                       class="group inline-flex items-center px-6 py-2 border-2 border-yellow-400 text-yellow-600 font-medium rounded-xl hover:bg-yellow-400 hover:text-black transition duration-200">
                        <i class="fas fa-user-plus mr-2 group-hover:text-black"></i>
                        Criar Nova Conta
                    </a>
                </div>
                
                <!-- Info de Acesso -->
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs text-gray-600 mb-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Use o mesmo formulário para acessar como:
                    </p>
                    <div class="flex justify-center space-x-6 text-xs text-gray-500">
                        <span><i class="fas fa-user mr-1 text-blue-500"></i>Cliente</span>
                        <span><i class="fas fa-cut mr-1 text-green-500"></i>Barbeiro</span>
                        <span><i class="fas fa-shield-alt mr-1 text-red-500"></i>Admin</span>
                    </div>
                </div>
            </div>
    </div>
</div>

        </div>
    </div>
</div>

<script>
// Funcionalidade para mostrar/ocultar senha
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        icon.parentElement.title = 'Ocultar senha';
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        icon.parentElement.title = 'Mostrar senha';
    }
}

// Funcionalidade para o checkbox customizado
function toggleCheckbox() {
    const checkbox = document.getElementById('remember_me');
    const checkboxBg = document.getElementById('checkbox-bg');
    const checkIcon = document.getElementById('check-icon');
    
    if (checkbox.checked) {
        checkboxBg.classList.remove('border-gray-300', 'bg-white');
        checkboxBg.classList.add('border-yellow-400', 'bg-yellow-400');
        checkIcon.classList.remove('hidden');
    } else {
        checkboxBg.classList.remove('border-yellow-400', 'bg-yellow-400');
        checkboxBg.classList.add('border-gray-300', 'bg-white');
        checkIcon.classList.add('hidden');
    }
}

// Animações e interações avançadas
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus no campo email
    const emailField = document.getElementById('email');
    if (emailField && !emailField.disabled) {
        setTimeout(() => emailField.focus(), 100);
    }
    
    // Adicionar efeitos de loading no botão de submit
    const form = document.querySelector('form');
    const submitButton = form.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function() {
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Entrando...';
        submitButton.disabled = true;
    });
    
    // Animação suave para entrada do formulário
    const loginCard = document.querySelector('.bg-white.rounded-2xl');
    loginCard.style.opacity = '0';
    loginCard.style.transform = 'translateY(20px)';
    
    setTimeout(() => {
        loginCard.style.transition = 'all 0.6s ease-out';
        loginCard.style.opacity = '1';
        loginCard.style.transform = 'translateY(0)';
    }, 100);
    
    // Efeito de digitação nos campos
    const inputs = document.querySelectorAll('input[type="email"], input[type="password"]');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
            this.parentElement.style.transition = 'transform 0.2s ease';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>