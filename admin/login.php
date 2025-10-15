<?php
/**
 * Login do Administrador - Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Interface de autenticação para administradores do sistema
 */

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

global $pdo;

// Se já está logado como admin, redirecionar
if (is_logged_in('admin')) {
    safe_redirect('/mr-carlos-barbershop/admin/');
}

$erro = '';

// Processar login
if ($_POST) {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (!$email || !$senha) {
        $erro = 'Por favor, preencha todos os campos';
    } else {
        try {
            // Buscar administrador por email
            $stmt = $pdo->prepare("SELECT * FROM administradores WHERE email = ? AND ativo = 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin) {
                // Verificar senha
                if (password_verify($senha, $admin['password_hash'])) {
                    // Login válido - criar sessão
                    session_regenerate_id(true);
                    $_SESSION['admin'] = [
                        'id' => $admin['id'],
                        'nome' => $admin['nome'],
                        'email' => $admin['email'],
                        'nivel' => $admin['nivel'],
                        'logged_in' => true
                    ];
                    $_SESSION['login_time'] = time();
                    
                    // Log de login
                    error_log("Admin login bem-sucedido: {$admin['email']} (ID: {$admin['id']})");
                    
                    // Atualizar último login
                    $stmt = $pdo->prepare("UPDATE administradores SET ultimo_login = NOW() WHERE id = ?");
                    $stmt->execute([$admin['id']]);
                    
                    safe_redirect('/mr-carlos-barbershop/admin/');
                } else {
                    $erro = 'Email ou senha incorretos';
                    error_log("Tentativa de login admin inválida: {$email}");
                }
            } else {
                $erro = 'Email ou senha incorretos';
                error_log("Tentativa de login admin - email não encontrado: {$email}");
            }
        } catch (Exception $e) {
            $erro = 'Erro interno do servidor';
            error_log("Erro no login admin: " . $e->getMessage());
        }
    }
}

$page_title = 'Login do Administrador';
$hide_nav = true;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Mr. Carlos Barbershop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dourado: '#C9A227',
                        dourado_escuro: '#B8941F'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Cabeçalho -->
            <div class="text-center">
                <div class="mx-auto h-20 w-20 bg-gradient-to-r from-dourado to-dourado_escuro rounded-full flex items-center justify-center mb-6 shadow-lg">
                    <i class="fas fa-user-shield text-white text-2xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">
                    Área Administrativa
                </h2>
                <p class="text-sm text-gray-600">
                    Mr. Carlos Barbershop - Acesso Restrito
                </p>
            </div>

            <!-- Formulário de Login -->
            <div class="bg-white py-8 px-6 shadow-xl rounded-2xl border border-gray-200">
                <?php if ($erro): ?>
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <span><?= htmlspecialchars($erro) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-2 text-dourado"></i>
                            Email Administrativo
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required 
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-dourado focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="Digite seu email de administrador"
                        >
                    </div>

                    <!-- Senha -->
                    <div>
                        <label for="senha" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-2 text-dourado"></i>
                            Senha
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="senha" 
                                name="senha" 
                                required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-dourado focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white pr-12"
                                placeholder="Digite sua senha"
                            >
                            <button 
                                type="button" 
                                onclick="togglePassword()" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-dourado transition-colors"
                            >
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Botão de Login -->
                    <div>
                        <button 
                            type="submit" 
                            class="w-full bg-gradient-to-r from-dourado to-dourado_escuro text-white font-bold py-3 px-4 rounded-lg hover:shadow-lg transform hover:scale-105 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-dourado focus:ring-offset-2"
                        >
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Entrar no Sistema
                        </button>
                    </div>
                </form>
            </div>

            <!-- Links Úteis -->
            <div class="text-center space-y-2">
                <p class="text-sm text-gray-500">
                    Problemas de acesso? Entre em contato com o suporte técnico
                </p>
                <a href="../" class="inline-flex items-center text-dourado hover:text-dourado_escuro transition-colors text-sm font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar ao site
                </a>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Toggle mostrar/ocultar senha
        function togglePassword() {
            const senhaInput = document.getElementById('senha');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (senhaInput.type === 'password') {
                senhaInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                senhaInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Auto-focus no primeiro campo
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            if (emailInput && !emailInput.value) {
                emailInput.focus();
            }
        });

        // Limpar mensagens de erro após 5 segundos
        document.addEventListener('DOMContentLoaded', function() {
            const errorDiv = document.querySelector('.bg-red-50');
            if (errorDiv) {
                setTimeout(() => {
                    errorDiv.style.opacity = '0';
                    setTimeout(() => errorDiv.remove(), 300);
                }, 5000);
            }
        });
    </script>
</body>
</html>