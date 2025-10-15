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
    <!-- CSS Principal com cache busting -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); ?>" id="main-stylesheet">
    
    <!-- CSS Inline Premium Garantido -->
    <style>
        /* Variáveis CSS Premium */
        :root {
            --color-accent: #D4AF37;
            --color-accent-hover: #B8921F;
            --color-accent-glow: rgba(212, 175, 55, 0.3);
            --color-dark: #1a1a1a;
            --color-light: #ffffff;
            --color-surface: #fafafa;
            --color-text: #333333;
            --color-text-secondary: #666666;
            --shadow-gold: 0 4px 15px rgba(212, 175, 55, 0.25);
            --shadow-gold-lg: 0 8px 25px rgba(212, 175, 55, 0.35);
        }

        /* Classes Premium Essenciais */
        .btn-premium {
            background: linear-gradient(135deg, var(--color-accent) 0%, var(--color-accent-hover) 100%);
            color: var(--color-dark);
            border: none;
            padding: 0.875rem 2rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-gold);
        }
        
        .btn-premium:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: var(--shadow-gold-lg);
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--color-accent);
            border: 2px solid var(--color-accent);
            padding: 0.875rem 2rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-secondary:hover {
            background: var(--color-accent);
            color: var(--color-dark);
            transform: translateY(-2px);
        }

        .card-premium {
            background: linear-gradient(145deg, var(--color-surface) 0%, rgba(255, 255, 255, 0.05) 100%);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 1.25rem;
            padding: 2rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }
        
        .card-premium:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-gold-lg);
            border-color: var(--color-accent);
        }

        .text-accent { color: var(--color-accent) !important; }
        .text-serif { font-family: Georgia, 'Times New Roman', serif; }
        .heading-premium {
            font-family: Georgia, 'Times New Roman', serif;
            background: linear-gradient(135deg, var(--color-text) 0%, var(--color-accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
            line-height: 1.2;
        }

        .decorative-line {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--color-accent), var(--color-accent-hover));
            margin: 1rem auto;
            border-radius: 3px;
            box-shadow: 0 0 10px var(--color-accent-glow);
        }

        .decorative-circle {
            width: 12px;
            height: 12px;
            background: var(--color-accent);
            border-radius: 50%;
            box-shadow: 0 0 15px var(--color-accent-glow);
            animation: goldPulse 3s ease-in-out infinite;
        }

        .service-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--color-accent), var(--color-accent-hover));
            border-radius: 50%;
            color: var(--color-dark);
            font-size: 1.5rem;
            box-shadow: 0 0 20px var(--color-accent-glow);
        }

        @keyframes goldPulse {
            0%, 100% { box-shadow: 0 0 5px var(--color-accent-glow); }
            50% { box-shadow: var(--shadow-gold); }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .scroll-reveal {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .scroll-reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        /* Fallback para Tailwind */
        .container { max-width: 1200px; margin: 0 auto; padding: 0 1rem; }
        .text-center { text-align: center; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .text-white { color: white; }
        .bg-white { background: white; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .py-20 { padding-top: 5rem; padding-bottom: 5rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .text-lg { font-size: 1.125rem; }
        .font-bold { font-weight: 700; }
        .rounded-lg { border-radius: 0.5rem; }
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

    <!-- Script de verificação de CSS otimizado -->
    <script>
        // Verificação otimizada de CSS
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar se o link CSS está presente
            var mainStylesheet = document.getElementById('main-stylesheet');
            if (!mainStylesheet) {
                console.warn('⚠️ Link do CSS principal não encontrado');
                loadFallbackCSS();
                return;
            }
            
            // Verificar se CSS foi aplicado através de uma classe premium
            function checkCSSLoaded() {
                var testDiv = document.createElement('div');
                testDiv.className = 'btn-premium';
                testDiv.style.position = 'absolute';
                testDiv.style.visibility = 'hidden';
                document.body.appendChild(testDiv);
                
                var styles = window.getComputedStyle(testDiv);
                var isLoaded = styles.background && styles.background !== 'rgba(0, 0, 0, 0)';
                
                document.body.removeChild(testDiv);
                
                if (isLoaded) {
                    console.log('✅ CSS Premium carregado com sucesso');
                } else {
                    console.warn('⚠️ CSS Premium não detectado, aplicando fallback');
                    loadFallbackCSS();
                }
            }
            
            // Aguardar um momento para o CSS carregar
            setTimeout(checkCSSLoaded, 100);
            
            function loadFallbackCSS() {
                if (document.getElementById('fallback-css')) return; // Evitar duplicação
                
                var fallbackStyle = document.createElement('style');
                fallbackStyle.id = 'fallback-css';
                fallbackStyle.textContent = `
                    /* Fallback CSS Premium */
                    .btn-premium { 
                        background: linear-gradient(135deg, #D4AF37 0%, #B8921F 100%);
                        color: #000;
                        border: none;
                        padding: 0.75rem 1.5rem;
                        border-radius: 0.5rem;
                        font-weight: 600;
                        transition: all 0.3s ease;
                    }
                    .card-premium {
                        background: #fff;
                        border: 1px solid #D4AF37;
                        border-radius: 1rem;
                        box-shadow: 0 4px 6px rgba(212, 175, 55, 0.1);
                    }
                    .text-accent { color: #D4AF37 !important; }
                    .bg-surface { background: #fafafa; }
                    .scroll-reveal { opacity: 1; transform: none; }
                    .container { max-width: 1200px; margin: 0 auto; }
                    .px-4 { padding-left: 1rem; padding-right: 1rem; }
                    .py-20 { padding-top: 5rem; padding-bottom: 5rem; }
                `;
                document.head.appendChild(fallbackStyle);
                console.log('🔄 Fallback CSS aplicado');
            }
        });
    </script>

    <!-- Main Content -->
    <main id="main-content" class="flex-1" role="main">