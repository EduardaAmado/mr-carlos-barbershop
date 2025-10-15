<?php
/**
 * Header principal da aplicação
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Cabeçalho HTML comum a todas as páginas
 */

// Incluir helpers se ainda não foi incluído
if (!function_exists('esc')) {
    require_once __DIR__ . '/helpers.php';
}

// Definir título padrão se não foi definido
if (!isset($page_title)) {
    $page_title = SITE_NAME;
}

// Definir base URL
$base_url = get_base_url();

// Verificar se utilizador está logado
$user_logged = is_logged_in('cliente');
$barbeiro_logged = is_logged_in('barbeiro');
$admin_logged = is_logged_in('admin');

?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <title><?php echo esc($page_title); ?></title>
    <meta name="description" content="Mr. Carlos Barbershop - Tradição e qualidade em cuidados masculinos desde 1985. Agende já o seu corte online.">
    <meta name="keywords" content="barbearia, corte masculino, barba, Porto, agendamento online, barbeiro">
    <meta name="author" content="Mr. Carlos Barbershop">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo esc($page_title); ?>">
    <meta property="og:description" content="A excelência em cuidados masculinos desde 1985">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo get_base_url(); ?>">
    <meta property="og:image" content="<?php echo get_base_url('assets/images/logo-og.jpg'); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo get_base_url('assets/images/favicon.ico'); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_base_url('assets/images/apple-touch-icon.png'); ?>">
    
    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'barbershop-gold': '#C9A227',
                        'barbershop-black': '#000000',
                        'barbershop-white': '#FFFFFF'
                    },
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif']
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css' rel='stylesheet' />
    
    <!-- CSS Personalizado -->
        <!-- CSS Principal -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css" id="main-stylesheet">
    
    <!-- CSS Inline Fallback para casos críticos -->
    <style>
        /* Fallback para garantir que elementos básicos funcionem */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background: linear-gradient(135deg, #C9A227 0%, #B8921F 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(201, 162, 39, 0.3);
        }
        .stats-card {
            background: white;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .dourado { color: #C9A227; }
        .text-barbershop-gold { color: #C9A227; }
        .bg-barbershop-gold { background-color: #C9A227; }
    </style>
    
    <!-- Meta para PWA (futuro) -->
    <meta name="theme-color" content="#C9A227">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
</head>

<body class="font-poppins bg-white text-black min-h-screen flex flex-col">
    <!-- Skip to main content (Acessibilidade) -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-barbershop-gold text-black px-4 py-2 rounded-lg z-50">
        Saltar para conteúdo principal
    </a>

    <!-- Header Navigation -->
    <header class="bg-black shadow-lg sticky top-0 z-40" role="banner">
        <nav class="container mx-auto px-4" role="navigation" aria-label="Navegação principal">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="<?php echo get_base_url(); ?>" class="flex items-center text-white hover:text-barbershop-gold transition-colors duration-300">
                        <i class="fas fa-cut text-2xl mr-3" aria-hidden="true"></i>
                        <span class="font-bold text-xl">Mr. Carlos</span>
                    </a>
                </div>

                <!-- Menu Desktop -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="<?php echo get_base_url(); ?>" 
                       class="<?php echo nav_class('index'); ?> px-3 py-2 rounded-md text-sm font-medium transition-all duration-300"
                       aria-current="<?php echo is_current_page('index') ? 'page' : 'false'; ?>">
                        Início
                    </a>
                    
                    <a href="<?php echo get_base_url('pages/servicos.php'); ?>" 
                       class="<?php echo nav_class('servicos'); ?> px-3 py-2 rounded-md text-sm font-medium transition-all duration-300"
                       aria-current="<?php echo is_current_page('servicos') ? 'page' : 'false'; ?>">
                        Serviços
                    </a>
                    
                    <a href="<?php echo get_base_url('pages/agendar.php'); ?>" 
                       class="<?php echo nav_class('agendar'); ?> px-3 py-2 rounded-md text-sm font-medium transition-all duration-300"
                       aria-current="<?php echo is_current_page('agendar') ? 'page' : 'false'; ?>">
                        Agendar
                    </a>

                    <!-- Menu do Utilizador -->
                    <?php if ($user_logged): ?>
                        <?php $user = get_logged_user('cliente'); ?>
                        <div class="relative group">
                            <button class="flex items-center text-white hover:text-barbershop-gold px-3 py-2 rounded-md text-sm font-medium transition-colors duration-300" 
                                    aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-user-circle mr-2" aria-hidden="true"></i>
                                <?php echo esc($user['nome'] ?? 'Utilizador'); ?>
                                <i class="fas fa-chevron-down ml-2 text-xs" aria-hidden="true"></i>
                            </button>
                            
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                                <div class="py-1">
                                    <a href="<?php echo get_base_url('pages/perfil.php'); ?>" 
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-300">
                                        <i class="fas fa-user mr-2" aria-hidden="true"></i>
                                        Meu Perfil
                                    </a>
                                    <a href="<?php echo get_base_url('pages/meus-agendamentos.php'); ?>" 
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-300">
                                        <i class="fas fa-calendar-alt mr-2" aria-hidden="true"></i>
                                        Meus Agendamentos
                                    </a>
                                    <div class="border-t border-gray-100"></div>
                                    <a href="<?php echo get_base_url('pages/logout.php'); ?>" 
                                       class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-300">
                                        <i class="fas fa-sign-out-alt mr-2" aria-hidden="true"></i>
                                        Terminar Sessão
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($barbeiro_logged): ?>
                        <a href="<?php echo get_base_url('barbeiro/dashboard.php'); ?>" 
                           class="text-barbershop-gold hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors duration-300">
                            <i class="fas fa-tachometer-alt mr-2" aria-hidden="true"></i>
                            Dashboard
                        </a>
                        <a href="<?php echo get_base_url('barbeiro/logout.php'); ?>" 
                           class="text-white hover:text-red-400 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-300">
                            <i class="fas fa-sign-out-alt mr-2" aria-hidden="true"></i>
                            Sair
                        </a>
                    <?php elseif ($admin_logged): ?>
                        <a href="<?php echo get_base_url('admin/'); ?>" 
                           class="text-barbershop-gold hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors duration-300">
                            <i class="fas fa-cog mr-2" aria-hidden="true"></i>
                            Admin
                        </a>
                        <a href="<?php echo get_base_url('admin/logout.php'); ?>" 
                           class="text-white hover:text-red-400 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-300">
                            <i class="fas fa-sign-out-alt mr-2" aria-hidden="true"></i>
                            Sair
                        </a>
                    <?php else: ?>
                        <a href="<?php echo get_base_url('pages/login.php'); ?>" 
                           class="text-white hover:text-barbershop-gold px-3 py-2 rounded-md text-sm font-medium transition-colors duration-300">
                            <i class="fas fa-sign-in-alt mr-2" aria-hidden="true"></i>
                            Entrar
                        </a>
                        <a href="<?php echo get_base_url('pages/register.php'); ?>" 
                           class="bg-barbershop-gold hover:bg-yellow-500 text-black px-4 py-2 rounded-md text-sm font-medium transition-colors duration-300">
                            <i class="fas fa-user-plus mr-2" aria-hidden="true"></i>
                            Registar
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Botão Menu Mobile -->
                <div class="md:hidden">
                    <button id="mobile-menu-button" 
                            class="text-white hover:text-barbershop-gold focus:outline-none focus:text-barbershop-gold transition-colors duration-300"
                            aria-label="Abrir menu de navegação"
                            aria-expanded="false"
                            aria-controls="mobile-menu">
                        <i class="fas fa-bars text-xl" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <!-- Menu Mobile -->
            <div id="mobile-menu" class="hidden md:hidden pb-4" role="menu">
                <div class="space-y-2">
                    <a href="<?php echo get_base_url(); ?>" 
                       class="block text-white hover:text-barbershop-gold px-3 py-2 text-base font-medium transition-colors duration-300"
                       role="menuitem">Início</a>
                    
                    <a href="<?php echo get_base_url('pages/servicos.php'); ?>" 
                       class="block text-white hover:text-barbershop-gold px-3 py-2 text-base font-medium transition-colors duration-300"
                       role="menuitem">Serviços</a>
                    
                    <a href="<?php echo get_base_url('pages/agendar.php'); ?>" 
                       class="block text-white hover:text-barbershop-gold px-3 py-2 text-base font-medium transition-colors duration-300"
                       role="menuitem">Agendar</a>

                    <?php if ($user_logged): ?>
                        <div class="border-t border-gray-600 pt-2 mt-2">
                            <a href="<?php echo get_base_url('pages/perfil.php'); ?>" 
                               class="block text-white hover:text-barbershop-gold px-3 py-2 text-base font-medium transition-colors duration-300"
                               role="menuitem">Meu Perfil</a>
                            <a href="<?php echo get_base_url('pages/logout.php'); ?>" 
                               class="block text-red-400 hover:text-red-300 px-3 py-2 text-base font-medium transition-colors duration-300"
                               role="menuitem">Terminar Sessão</a>
                        </div>
                    <?php else: ?>
                        <div class="border-t border-gray-600 pt-2 mt-2">
                            <a href="<?php echo get_base_url('pages/login.php'); ?>" 
                               class="block text-white hover:text-barbershop-gold px-3 py-2 text-base font-medium transition-colors duration-300"
                               role="menuitem">Entrar</a>
                            <a href="<?php echo get_base_url('pages/register.php'); ?>" 
                               class="block text-barbershop-gold hover:text-yellow-300 px-3 py-2 text-base font-medium transition-colors duration-300"
                               role="menuitem">Registar</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- Área de mensagens (se existirem) -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="container mx-auto px-4 mt-4">
            <?php 
            echo show_message($_SESSION['message']['text'], $_SESSION['message']['type']);
            unset($_SESSION['message']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Script de verificação de CSS -->
    <script>
        // Verificar se o CSS principal foi carregado
        document.addEventListener('DOMContentLoaded', function() {
            var testElement = document.createElement('div');
            testElement.className = 'btn btn-primary';
            testElement.style.display = 'none';
            document.body.appendChild(testElement);
            
            var computedStyle = window.getComputedStyle(testElement);
            var hasValidCSS = computedStyle.background !== '' || computedStyle.backgroundColor !== '';
            
            document.body.removeChild(testElement);
            
            if (!hasValidCSS) {
                console.warn('⚠️ CSS principal não foi carregado corretamente. Tentando recarregar...');
                
                // Tentar recarregar o CSS
                var newLink = document.createElement('link');
                newLink.rel = 'stylesheet';
                newLink.href = '<?php echo $base_url; ?>assets/css/style.css?v=' + Date.now();
                newLink.onload = function() {
                    console.log('✅ CSS recarregado com sucesso');
                };
                newLink.onerror = function() {
                    console.error('❌ Falha ao recarregar CSS, usando fallback');
                    addFallbackCSS();
                };
                document.head.appendChild(newLink);
                
                // Função para adicionar CSS de emergência
                function addFallbackCSS() {
                    var fallbackCSS = document.createElement('style');
                fallbackCSS.textContent = `
                    .min-h-screen { min-height: 100vh; }
                    .container { max-width: 1200px; margin: 0 auto; }
                    .mx-auto { margin-left: auto; margin-right: auto; }
                    .px-4 { padding-left: 1rem; padding-right: 1rem; }
                    .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
                    .text-center { text-align: center; }
                    .font-bold { font-weight: 700; }
                    .text-white { color: white; }
                    .bg-white { background-color: white; }
                    .shadow-lg { box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
                    .rounded-lg { border-radius: 0.5rem; }
                    `;
                    document.head.appendChild(fallbackCSS);
                }
                
                // Aplicar fallback imediatamente como backup
                setTimeout(addFallbackCSS, 1000);
            } else {
                console.log('✅ CSS principal carregado com sucesso');
            }
        });
    </script>

    <!-- Main Content -->
    <main id="main-content" class="flex-1" role="main">