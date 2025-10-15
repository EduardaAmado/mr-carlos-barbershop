<?php
/**
 * Página inicial do Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Página principal do website da barbearia
 */

// Incluir ficheiro de configuração
require_once __DIR__ . '/config/config.php';

// Definir título da página
$page_title = 'Início - ' . SITE_NAME;

// Incluir header
include_once __DIR__ . '/includes/header.php';
?>

<main class="min-h-screen">
    <!-- Hero Section Moderna -->
    <section class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-gray-900 to-black text-white">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, #C9A227 2px, transparent 0); background-size: 40px 40px;"></div>
        </div>
        
        <!-- Content -->
        <div class="relative container mx-auto px-4 py-20 lg:py-32">
            <div class="max-w-4xl mx-auto text-center">
                <!-- Badge Premium -->
                <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-yellow-400/20 to-yellow-600/20 rounded-full border border-yellow-400/30 mb-8">
                    <i class="fas fa-crown text-yellow-400 mr-2"></i>
                    <span class="text-yellow-300 text-sm font-medium">Tradição & Excelência desde 1985</span>
                </div>

                <!-- Título Principal com Animação -->
                <h1 class="text-6xl md:text-7xl lg:text-8xl font-black mb-6 tracking-tight">
                    <span class="block text-white">Mr.</span>
                    <span class="block text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 animate-pulse">Carlos</span>
                    <span class="block text-gray-300 text-4xl md:text-5xl lg:text-6xl font-light mt-2">Barbershop</span>
                </h1>

                <!-- Subtítulo Elegante -->
                <p class="text-xl md:text-2xl lg:text-3xl mb-12 text-gray-300 font-light leading-relaxed max-w-3xl mx-auto">
                    Onde a <span class="text-yellow-400 font-semibold">tradição</span> encontra a 
                    <span class="text-yellow-400 font-semibold">modernidade</span> em cada corte
                </p>

                <!-- Botões de Ação Modernos -->
                <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
                    <a href="<?php echo get_base_url('pages/agendar.php'); ?>" 
                       class="group relative overflow-hidden bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-500 hover:to-yellow-700 text-black font-bold py-4 px-10 rounded-xl transition-all duration-500 transform hover:scale-105 hover:shadow-2xl hover:shadow-yellow-500/50">
                        <span class="relative z-10 flex items-center">
                            <i class="fas fa-calendar-plus mr-3 text-lg"></i>
                            Agendar Agora
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-yellow-500 to-yellow-700 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    </a>
                    
                    <a href="<?php echo get_base_url('pages/servicos.php'); ?>" 
                       class="group relative border-2 border-yellow-400 hover:bg-yellow-400 hover:text-black text-yellow-400 font-bold py-4 px-10 rounded-xl transition-all duration-500 transform hover:scale-105">
                        <span class="flex items-center">
                            <i class="fas fa-cut mr-3 text-lg"></i>
                            Nossos Serviços
                        </span>
                    </a>
                </div>

                <!-- Indicadores de Qualidade -->
                <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8 max-w-2xl mx-auto">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-yellow-400 mb-2">35+</div>
                        <div class="text-gray-400 text-sm">Anos de Experiência</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-yellow-400 mb-2">10K+</div>
                        <div class="text-gray-400 text-sm">Clientes Satisfeitos</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-yellow-400 mb-2">5★</div>
                        <div class="text-gray-400 text-sm">Avaliação Média</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seta de Scroll -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <i class="fas fa-chevron-down text-yellow-400 text-2xl"></i>
        </div>
    </section>

    <!-- Sobre Nós Modernizada -->
    <section class="py-20 bg-gradient-to-br from-gray-50 via-white to-gray-100 relative overflow-hidden">
        <!-- Background Decoration -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-l from-yellow-400/10 to-transparent rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-gradient-to-r from-gray-900/5 to-transparent rounded-full blur-3xl"></div>
        
        <div class="container mx-auto px-4 relative z-10">
            <!-- Cabeçalho da Seção -->
            <div class="text-center mb-16">
                <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-900 to-black rounded-full mb-6">
                    <i class="fas fa-star text-yellow-400 mr-2"></i>
                    <span class="text-white text-sm font-medium">Sobre Nossa História</span>
                </div>
                <h2 class="text-4xl lg:text-5xl font-black text-gray-900 mb-4">
                    Tradição que <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-600 to-yellow-500">Inspira</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Mais de três décadas dedicadas à arte de cuidar da imagem masculina com excelência
                </p>
            </div>

            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <!-- Conteúdo Textual -->
                <div class="order-2 lg:order-1">
                    <div class="mb-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-xl flex items-center justify-center mr-4">
                                <i class="fas fa-crown text-white"></i>
                            </div>
                            Excelência Reconhecida
                        </h3>
                        <p class="text-gray-700 leading-relaxed mb-6">
                            Desde 1985, o Mr. Carlos Barbershop estabeleceu-se como referência em cuidados masculinos premium. 
                            Nossa filosofia combina técnicas tradicionais de barbeiro com as mais modernas tendências de estilo.
                        </p>
                    </div>

                    <!-- Features Premium -->
                    <div class="grid sm:grid-cols-2 gap-6 mb-8">
                        <div class="group">
                            <div class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 border border-gray-100 hover:border-yellow-300">
                                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-user-tie text-white text-lg"></i>
                                </div>
                                <h4 class="font-bold text-gray-900 mb-2">Barbeiros Master</h4>
                                <p class="text-gray-600 text-sm">Profissionais certificados com anos de experiência</p>
                            </div>
                        </div>

                        <div class="group">
                            <div class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 border border-gray-100 hover:border-yellow-300">
                                <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-leaf text-white text-lg"></i>
                                </div>
                                <h4 class="font-bold text-gray-900 mb-2">Produtos Premium</h4>
                                <p class="text-gray-600 text-sm">Apenas as melhores marcas e produtos naturais</p>
                            </div>
                        </div>

                        <div class="group">
                            <div class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 border border-gray-100 hover:border-yellow-300">
                                <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-clock text-white text-lg"></i>
                                </div>
                                <h4 class="font-bold text-gray-900 mb-2">Agendamento 24/7</h4>
                                <p class="text-gray-600 text-sm">Sistema online disponível a qualquer hora</p>
                            </div>
                        </div>

                        <div class="group">
                            <div class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 border border-gray-100 hover:border-yellow-300">
                                <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-red-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-heart text-white text-lg"></i>
                                </div>
                                <h4 class="font-bold text-gray-900 mb-2">Ambiente Premium</h4>
                                <p class="text-gray-600 text-sm">Espaço sofisticado e acolhedor</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Horários e Informações Premium -->
                <div class="order-1 lg:order-2 relative">
                    <!-- Card Principal -->
                    <div class="relative bg-gradient-to-br from-gray-900 to-black rounded-3xl p-8 shadow-2xl">
                        <!-- Background Pattern -->
                        <div class="absolute inset-0 opacity-10 rounded-3xl" style="background-image: radial-gradient(circle at 2px 2px, #C9A227 1px, transparent 0); background-size: 20px 20px;"></div>
                        
                        <div class="relative z-10 text-white">
                            <!-- Header -->
                            <div class="text-center mb-8">
                                <div class="inline-flex items-center px-4 py-2 bg-yellow-400 text-black rounded-full font-bold text-sm mb-4">
                                    <i class="fas fa-clock mr-2"></i>
                                    Horário de Funcionamento
                                </div>
                            </div>
                            
                            <!-- Horários -->
                            <div class="space-y-4 mb-8">
                                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                                    <span class="text-gray-300">Segunda - Sexta</span>
                                    <span class="font-bold text-yellow-400">09:00 - 19:00</span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                                    <span class="text-gray-300">Sábado</span>
                                    <span class="font-bold text-yellow-400">09:00 - 17:00</span>
                                </div>
                                <div class="flex justify-between items-center py-3">
                                    <span class="text-gray-300">Domingo</span>
                                    <span class="font-bold text-red-400">Fechado</span>
                                </div>
                            </div>

                            <!-- Estatísticas -->
                            <div class="grid grid-cols-2 gap-6 border-t border-gray-700 pt-6">
                                <div class="text-center">
                                    <div class="text-3xl font-black text-yellow-400 mb-2">35+</div>
                                    <div class="text-gray-300 text-sm">Anos de Tradição</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-3xl font-black text-yellow-400 mb-2">15K+</div>
                                    <div class="text-gray-300 text-sm">Clientes Satisfeitos</div>
                                </div>
                            </div>

                            <!-- Call to Action -->
                            <div class="mt-8">
                                <a href="<?php echo get_base_url('pages/agendar.php'); ?>" 
                                   class="w-full bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-500 hover:to-yellow-700 text-black font-bold py-4 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 flex items-center justify-center">
                                    <i class="fas fa-calendar-plus mr-3 text-lg"></i>
                                    <span>Agendar Agora</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Elementos Decorativos -->
                    <div class="absolute -top-6 -right-6 w-24 h-24 bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-2xl rotate-12 opacity-20"></div>
                    <div class="absolute -bottom-6 -left-6 w-32 h-32 bg-gradient-to-r from-gray-600 to-gray-800 rounded-2xl rotate-45 opacity-10"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Serviços Premium -->
    <section class="py-20 bg-white relative overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute top-20 left-0 w-72 h-72 bg-gradient-to-r from-yellow-400/5 to-yellow-600/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-0 w-72 h-72 bg-gradient-to-l from-gray-900/5 to-gray-700/5 rounded-full blur-3xl"></div>
        
        <div class="container mx-auto px-4 relative z-10">
            <!-- Cabeçalho da Seção -->
            <div class="text-center mb-16">
                <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-full mb-6">
                    <i class="fas fa-cut text-white mr-2"></i>
                    <span class="text-white text-sm font-medium">Nossos Melhores Serviços</span>
                </div>
                <h2 class="text-4xl lg:text-5xl font-black text-gray-900 mb-4">
                    Serviços <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-600 to-yellow-500">Premium</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Cada serviço é uma experiência única, desenvolvida para realçar o melhor de você
                </p>
            </div>

            <!-- Grid de Serviços -->
            <div class="grid lg:grid-cols-3 gap-8 mb-12">
                <!-- Corte Clássico -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl opacity-0 group-hover:opacity-5 transition-opacity duration-500"></div>
                    <div class="relative bg-white rounded-2xl shadow-xl border border-gray-100 group-hover:border-blue-300 transition-all duration-500 overflow-hidden group-hover:-translate-y-2 group-hover:shadow-2xl">
                        <!-- Header com Ícone -->
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 text-white text-center relative">
                            <div class="absolute top-0 left-0 w-full h-full bg-black opacity-10"></div>
                            <div class="relative z-10">
                                <div class="w-16 h-16 mx-auto bg-white/20 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-cut text-2xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold">Corte Clássico</h3>
                                <div class="w-12 h-1 bg-white/50 mx-auto mt-2 rounded-full"></div>
                            </div>
                        </div>
                        
                        <!-- Conteúdo -->
                        <div class="p-6">
                            <p class="text-gray-600 mb-6 leading-relaxed">
                                Corte tradicional executado por barbeiros master, com técnicas refinadas e acabamento impecável para um visual elegante e atemporal.
                            </p>
                            
                            <!-- Features -->
                            <div class="space-y-2 mb-6">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-check text-blue-500 mr-2 w-4"></i>
                                    Análise personalizada do rosto
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-check text-blue-500 mr-2 w-4"></i>
                                    Técnicas tradicionais de barbeiro
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-check text-blue-500 mr-2 w-4"></i>
                                    Produtos premium inclusos
                                </div>
                            </div>
                            
                            <!-- Preço e Duração -->
                            <div class="flex justify-between items-end">
                                <div>
                                    <div class="text-3xl font-black text-gray-900 mb-1">€15</div>
                                    <div class="text-sm text-gray-500 flex items-center">
                                        <i class="fas fa-clock mr-1"></i>
                                        30 minutos
                                    </div>
                                </div>
                                <a href="<?php echo get_base_url('pages/agendar.php'); ?>" 
                                   class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors duration-300 text-sm font-medium">
                                    Agendar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Barba Completa -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-green-500 to-green-600 rounded-2xl opacity-0 group-hover:opacity-5 transition-opacity duration-500"></div>
                    <div class="relative bg-white rounded-2xl shadow-xl border border-gray-100 group-hover:border-green-300 transition-all duration-500 overflow-hidden group-hover:-translate-y-2 group-hover:shadow-2xl">
                        <!-- Header com Ícone -->
                        <div class="bg-gradient-to-r from-green-500 to-green-600 p-6 text-white text-center relative">
                            <div class="absolute top-0 left-0 w-full h-full bg-black opacity-10"></div>
                            <div class="relative z-10">
                                <div class="w-16 h-16 mx-auto bg-white/20 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-user-tie text-2xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold">Barba Completa</h3>
                                <div class="w-12 h-1 bg-white/50 mx-auto mt-2 rounded-full"></div>
                            </div>
                        </div>
                        
                        <!-- Conteúdo -->
                        <div class="p-6">
                            <p class="text-gray-600 mb-6 leading-relaxed">
                                Aparar e modelagem profissional com toalha quente, óleos especiais e acabamento preciso para uma barba sempre impecável.
                            </p>
                            
                            <!-- Features -->
                            <div class="space-y-2 mb-6">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-check text-green-500 mr-2 w-4"></i>
                                    Toalha quente relaxante
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-check text-green-500 mr-2 w-4"></i>
                                    Óleos hidratantes premium
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-check text-green-500 mr-2 w-4"></i>
                                    Modelagem personalizada
                                </div>
                            </div>
                            
                            <!-- Preço e Duração -->
                            <div class="flex justify-between items-end">
                                <div>
                                    <div class="text-3xl font-black text-gray-900 mb-1">€12</div>
                                    <div class="text-sm text-gray-500 flex items-center">
                                        <i class="fas fa-clock mr-1"></i>
                                        25 minutos
                                    </div>
                                </div>
                                <a href="<?php echo get_base_url('pages/agendar.php'); ?>" 
                                   class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors duration-300 text-sm font-medium">
                                    Agendar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Corte + Barba (Destaque) -->
                <div class="group relative">
                    <!-- Badge de Destaque -->
                    <div class="absolute -top-3 -right-3 z-20">
                        <div class="bg-gradient-to-r from-yellow-400 to-yellow-600 text-black text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                            <i class="fas fa-star mr-1"></i>
                            MAIS POPULAR
                        </div>
                    </div>
                    
                    <div class="absolute inset-0 bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl border-2 border-yellow-400 group-hover:border-yellow-500 transition-all duration-500 overflow-hidden group-hover:-translate-y-2 group-hover:shadow-yellow-500/25">
                        <!-- Header com Ícone -->
                        <div class="bg-gradient-to-r from-yellow-400 to-yellow-600 p-6 text-black text-center relative">
                            <div class="absolute top-0 left-0 w-full h-full bg-black opacity-5"></div>
                            <div class="relative z-10">
                                <div class="w-16 h-16 mx-auto bg-black/10 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-crown text-2xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold">Corte + Barba</h3>
                                <div class="w-12 h-1 bg-black/30 mx-auto mt-2 rounded-full"></div>
                            </div>
                        </div>
                        
                        <!-- Conteúdo -->
                        <div class="p-6">
                            <p class="text-gray-600 mb-6 leading-relaxed">
                                O serviço completo que une o melhor dos dois mundos. Experiência premium com desconto especial para quem quer o visual perfeito.
                            </p>
                            
                            <!-- Features -->
                            <div class="space-y-2 mb-6">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-check text-yellow-600 mr-2 w-4"></i>
                                    Serviço completo premium
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-check text-yellow-600 mr-2 w-4"></i>
                                    Economize €2 no combo
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-check text-yellow-600 mr-2 w-4"></i>
                                    Experiência VIP completa
                                </div>
                            </div>
                            
                            <!-- Preço e Duração -->
                            <div class="flex justify-between items-end">
                                <div>
                                    <div class="flex items-baseline">
                                        <span class="text-3xl font-black text-gray-900">€25</span>
                                        <span class="text-lg text-gray-400 line-through ml-2">€27</span>
                                    </div>
                                    <div class="text-sm text-gray-500 flex items-center">
                                        <i class="fas fa-clock mr-1"></i>
                                        50 minutos
                                    </div>
                                </div>
                                <a href="<?php echo get_base_url('pages/agendar.php'); ?>" 
                                   class="bg-yellow-500 hover:bg-yellow-600 text-black px-6 py-2 rounded-lg transition-colors duration-300 text-sm font-bold">
                                    Agendar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Call to Action para Mais Serviços -->
            <div class="text-center">
                <p class="text-gray-600 mb-6">Descubra todos os nossos serviços especializados</p>
                <a href="<?php echo get_base_url('pages/servicos.php'); ?>" 
                   class="inline-flex items-center bg-gradient-to-r from-gray-900 to-black hover:from-black hover:to-gray-900 text-white font-bold py-4 px-8 rounded-xl transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-list mr-3 text-lg"></i>
                    <span>Ver Todos os Serviços</span>
                    <i class="fas fa-arrow-right ml-3 text-lg"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Call to Action Final -->
    <section class="relative bg-gradient-to-br from-slate-900 via-gray-900 to-black text-white py-20 overflow-hidden">
        <!-- Background Effects -->
        <div class="absolute inset-0">
            <!-- Pattern -->
            <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle at 2px 2px, #C9A227 2px, transparent 0); background-size: 50px 50px;"></div>
            <!-- Gradients -->
            <div class="absolute top-0 left-0 w-96 h-96 bg-gradient-to-r from-yellow-400/10 to-yellow-600/5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-gradient-to-l from-yellow-400/10 to-yellow-600/5 rounded-full blur-3xl"></div>
        </div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <!-- Badge -->
                <div class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-yellow-400/20 to-yellow-600/20 rounded-full border border-yellow-400/30 mb-8">
                    <i class="fas fa-rocket text-yellow-400 mr-3 text-lg"></i>
                    <span class="text-yellow-300 font-medium">Transforme seu Visual Hoje</span>
                </div>

                <!-- Título Principal -->
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-black mb-8">
                    <span class="text-white">Pronto para um</span><br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600">
                        Novo Visual?
                    </span>
                </h2>

                <!-- Subtítulo -->
                <p class="text-xl md:text-2xl mb-12 text-gray-300 leading-relaxed max-w-3xl mx-auto">
                    Junte-se a milhares de clientes satisfeitos e descubra a diferença que 
                    <span class="text-yellow-400 font-semibold">35 anos de experiência</span> fazem no seu estilo
                </p>

                <!-- Estatísticas Rápidas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12 max-w-2xl mx-auto">
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-black text-yellow-400 mb-2 flex items-center justify-center">
                            <i class="fas fa-users mr-2"></i>
                            15K+
                        </div>
                        <div class="text-gray-400 text-sm">Clientes Satisfeitos</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-black text-yellow-400 mb-2 flex items-center justify-center">
                            <i class="fas fa-star mr-2"></i>
                            5.0
                        </div>
                        <div class="text-gray-400 text-sm">Avaliação Média</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-black text-yellow-400 mb-2 flex items-center justify-center">
                            <i class="fas fa-calendar-check mr-2"></i>
                            24/7
                        </div>
                        <div class="text-gray-400 text-sm">Agendamento Online</div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex flex-col sm:flex-row gap-6 justify-center items-center mb-8">
                    <a href="<?php echo get_base_url('pages/agendar.php'); ?>" 
                       class="group relative overflow-hidden bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-500 hover:to-yellow-700 text-black font-bold py-5 px-12 rounded-2xl text-lg transition-all duration-500 transform hover:scale-110 hover:shadow-2xl hover:shadow-yellow-500/50 flex items-center">
                        <span class="relative z-10 flex items-center">
                            <i class="fas fa-calendar-plus mr-4 text-xl"></i>
                            Agendar Agora
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-yellow-500 to-yellow-700 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    </a>
                    
                    <a href="<?php echo get_base_url('pages/contato.php'); ?>" 
                       class="group border-2 border-yellow-400 hover:bg-yellow-400 hover:text-black text-yellow-400 font-bold py-5 px-12 rounded-2xl text-lg transition-all duration-500 transform hover:scale-110 flex items-center">
                        <i class="fas fa-phone mr-4 text-xl"></i>
                        <span>Fale Connosco</span>
                    </a>
                </div>

                <!-- Garantias e Benefícios -->
                <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-8 max-w-3xl mx-auto">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-shield-alt text-green-400 text-xl"></i>
                            </div>
                            <h4 class="font-bold text-white mb-2">Garantia Total</h4>
                            <p class="text-gray-400 text-sm">Satisfação garantida ou refaremos o serviço</p>
                        </div>
                        
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 bg-blue-500/20 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-clock text-blue-400 text-xl"></i>
                            </div>
                            <h4 class="font-bold text-white mb-2">Pontualidade</h4>
                            <p class="text-gray-400 text-sm">Horários rigorosamente respeitados</p>
                        </div>
                        
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 bg-purple-500/20 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-heart text-purple-400 text-xl"></i>
                            </div>
                            <h4 class="font-bold text-white mb-2">Experiência VIP</h4>
                            <p class="text-gray-400 text-sm">Atendimento personalizado e premium</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
// Incluir footer
include_once __DIR__ . '/includes/footer.php';
?>