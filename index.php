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
    <div class="relative container mx-auto px-4 py-10 sm:py-16 md:py-20 lg:py-32">
            <div class="max-w-4xl mx-auto text-center">
                <!-- Badge Premium -->
                <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-yellow-400/20 to-yellow-600/20 rounded-full border border-yellow-400/30 mb-8">
                    <i class="fas fa-crown text-yellow-400 mr-2"></i>
                    <span class="text-yellow-300 text-sm font-medium">Tradição & Excelência desde 1985</span>
                </div>

                <!-- Título Principal com Animação -->
                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl xl:text-8xl font-black mb-6 tracking-tight leading-tight">
                    <span class="block text-white glow-gold heading-premium font-serif">Mr.</span>
                    <span class="block text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 animate-pulse glow-gold-lg heading-premium font-serif">Carlos</span>
                    <span class="block text-gray-300 text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-light mt-2 glow-gold heading-premium font-serif">Barbershop</span>
                </h1>

                <!-- Subtítulo Elegante -->
                <p class="text-base sm:text-lg md:text-xl lg:text-2xl mb-8 sm:mb-10 md:mb-12 text-gray-300 font-light leading-relaxed max-w-3xl mx-auto">
                    Onde a <span class="text-yellow-400 font-semibold">tradição</span> encontra a 
                    <span class="text-yellow-400 font-semibold">modernidade</span> em cada corte
                </p>

                <!-- Botões de Ação Modernos -->
                <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 justify-center items-center w-full max-w-xl mx-auto">
                          <a href="<?php echo get_base_url('pages/agendar.php'); ?>" 
                              class="btn-premium group relative overflow-hidden shadow-gold-lg animate-fade-in-up text-base sm:text-lg font-bold font-serif w-full sm:w-auto py-3 px-6 sm:py-4 sm:px-10 transition-all duration-500 hover:scale-110 hover:shadow-gold-lg flex justify-center items-center text-center mx-0 text-white group-hover:text-yellow-400">
                        <span class="relative z-10 flex items-center">
                            <i class="fas fa-calendar-plus mr-3 text-white text-xl group-hover:text-yellow-400 group-hover:animate-pulse"></i>
                            Reserve sua experiência premium
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-yellow-500 to-yellow-700 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                    </a>
                          <a href="<?php echo get_base_url('pages/servicos.php'); ?>" 
                              class="group relative border-2 border-yellow-400 hover:bg-yellow-400 hover:text-black text-yellow-400 font-bold w-full sm:w-auto py-3 px-6 sm:py-4 sm:px-10 rounded-xl transition-all duration-500 transform hover:scale-105 flex justify-center items-center text-center mx-0">
                        <span class="flex items-center">
                            <i class="fas fa-cut mr-3 text-lg"></i>
                            Nossos Serviços
                        </span>
                    </a>
                </div>

                <!-- Indicadores de Qualidade -->
                <div class="mt-12 sm:mt-16 grid grid-cols-1 sm:grid-cols-3 gap-8 gap-y-10 max-w-2xl mx-auto">
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

    <!-- Seção História do Barbeiro -->
    <section id="historia-barbeiro" class="relative py-10 sm:py-16 md:py-20 bg-white overflow-x-hidden scroll-reveal">
        <!-- Parallax Dourado -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-gradient-to-r from-yellow-400/20 to-yellow-600/10 rounded-full blur-3xl z-0"></div>
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-3xl mx-auto text-center">
                <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-yellow-400/20 to-yellow-600/20 rounded-full border border-yellow-400/30 mb-6 animate-fade-in-up">
                    <i class="fas fa-user text-yellow-400 mr-2 glow-gold"></i>
                    <span class="text-yellow-600 text-sm font-medium">História do Barbeiro</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-serif font-black text-gray-900 mb-4 heading-premium animate-fade-in-up" style="font-family: 'Playfair Display', serif;">
                    A Paixão de <span class="text-accent glow-gold font-serif">Carlos Alves</span>
                </h2>
                <div class="decorative-line mb-6 animate-fade-in-up"></div>
                                    <!-- Galeria de Trabalhos do Barbeiro -->
                                                    <div class="mt-10 mb-4">
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 gap-y-12">
                                                            <!-- Card 1 -->
                                                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 group transition-all duration-500 flex flex-col items-stretch h-full min-h-[370px] hover:shadow-gold-lg hover:border-gold">
                                                                <div class="flex-1 flex flex-col justify-between">
                                                                    <img src="assets/images/trabalho1.jpg" alt="Corte clássico - Carlos Alves" class="w-full aspect-[4/3] object-cover rounded-xl object-center transition-transform duration-500 group-hover:scale-105">
                                                                    <div class="flex flex-col items-center justify-center p-5 h-[120px]">
                                                                        <span class="block font-serif font-bold text-xl text-gray-900 mb-1">Corte Clássico</span>
                                                                        <span class="text-gray-500 text-sm">Estilo tradicional, acabamento premium</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- Card 2 -->
                                                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 group transition-all duration-500 flex flex-col items-stretch h-full min-h-[370px] hover:shadow-gold-lg hover:border-gold">
                                                                <div class="flex-1 flex flex-col justify-between">
                                                                    <img src="assets/images/trabalho2.jpg" alt="Barba desenhada - Carlos Alves" class="w-full aspect-[4/3] object-cover rounded-xl object-center transition-transform duration-500 group-hover:scale-105">
                                                                    <div class="flex flex-col items-center justify-center p-5 h-[120px]">
                                                                        <span class="block font-serif font-bold text-xl text-gray-900 mb-1">Barba Desenhada</span>
                                                                        <span class="text-gray-500 text-sm">Detalhamento e simetria impecáveis</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- Card 3 -->
                                                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 group transition-all duration-500 flex flex-col items-stretch h-full min-h-[370px] hover:shadow-gold-lg hover:border-gold">
                                                                <div class="flex-1 flex flex-col justify-between">
                                                                    <img src="assets/images/trabalho3.jpg" alt="Corte moderno - Carlos Alves" class="w-full aspect-[4/3] object-cover rounded-xl object-center transition-transform duration-500 group-hover:scale-105">
                                                                    <div class="flex flex-col items-center justify-center p-5 h-[120px]">
                                                                        <span class="block font-serif font-bold text-xl text-gray-900 mb-1">Corte Moderno</span>
                                                                        <span class="text-gray-500 text-sm">Tendências atuais com toque autoral</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                <p class="text-lg md:text-xl text-gray-700 font-light leading-relaxed mb-8 animate-fade-in-up" style="font-family: 'Poppins', sans-serif;">
                    Desde muito jovem, <span class="text-accent font-semibold font-serif">Carlos Alves</span> encontrou na barbearia não apenas uma profissão, mas uma verdadeira vocação. Com mais de três décadas de dedicação, transformou cada corte em uma experiência única, unindo tradição, técnica e um olhar moderno para valorizar a identidade de cada cliente. Sua paixão e excelência são o coração do Mr. Carlos Barbershop.
                </p>
                <a href="<?php echo get_base_url('pages/agendar.php'); ?>" class="btn-premium shadow-gold-lg animate-fade-in-up group transition-all duration-500 hover:scale-105 hover:shadow-gold-lg inline-flex items-center px-8 py-4 text-lg font-bold mt-2">
                    <i class="fas fa-calendar-plus mr-3 text-white text-xl group-hover:text-yellow-400 group-hover:animate-pulse"></i>
                    <span class="text-white group-hover:text-yellow-400">Reserve o seu corte com Carlos Alves hoje</span>
                </a>
            </div>
        </div>
        <!-- Parallax Sombra -->
        <div class="absolute bottom-0 right-0 w-80 h-40 bg-gradient-to-l from-yellow-400/10 to-yellow-600/5 rounded-full blur-2xl z-0"></div>
    </section>
    <!-- Sobre Nós Modernizada -->
    <section class="py-10 sm:py-16 md:py-20 bg-gradient-to-br from-gray-50 via-white to-gray-100 relative overflow-x-hidden">
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
                    Tradição que <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-600 to-yellow-500 glow-gold font-serif">Inspira</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Mais de três décadas dedicadas à arte de cuidar da imagem masculina com excelência
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 md:gap-16 items-center">
                <!-- Conteúdo Textual -->
                <div class="order-2 lg:order-1">
                    <div class="mb-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-xl flex items-center justify-center mr-4">
                                <i class="fas fa-crown text-white glow-gold"></i>
                            </div>
                            <span class="font-serif heading-premium">Excelência Reconhecida</span>
                        </h3>
                        <p class="text-gray-700 leading-relaxed mb-6">
                            Desde 1985, o Mr. Carlos Barbershop estabeleceu-se como referência em cuidados masculinos premium. 
                            Nossa filosofia combina técnicas tradicionais de barbeiro com as mais modernas tendências de estilo.
                        </p>
                    </div>

                    <!-- Features Premium -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
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
                                   class="btn-premium w-full flex items-center justify-center text-lg font-bold font-serif animate-fade-in-up group transition-all duration-500 hover:scale-110 hover:shadow-gold-lg">
                                    <i class="fas fa-calendar-plus mr-3 text-white text-xl group-hover:text-yellow-400 group-hover:animate-pulse"></i>
                                    <span class="text-white group-hover:text-yellow-400">Agende sua experiência premium</span>
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
    <section class="py-10 sm:py-16 md:py-20 bg-white relative overflow-x-hidden">
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
                    Serviços <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-600 to-yellow-500 font-serif heading-premium">Premium</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Cada serviço é uma experiência única, desenvolvida para realçar o melhor de você
                </p>
            </div>

            <!-- Grid de Serviços -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 gap-y-12 mb-12">
                <!-- Card: Corte Clássico -->
                <div class="group relative">
                    <div class="relative bg-white rounded-2xl shadow-md border border-gray-200 transition-all duration-500 overflow-hidden group-hover:-translate-y-2 group-hover:shadow-gold-lg group-hover:border-gold">
                        <div class="p-8 text-center">
                            <div class="w-20 h-20 mx-auto flex items-center justify-center mb-4 transition-transform duration-500 group-hover:scale-110">
                                <i class="fas fa-cut text-gold-glow text-4xl"></i>
                            </div>
                            <h3 class="text-2xl font-serif font-bold mb-2 text-gold-gradient transition-colors duration-300">Corte Clássico</h3>
                            <p class="text-gray-600 mb-6 leading-relaxed text-base">Corte tradicional executado por barbeiros master, com técnicas refinadas e acabamento impecável para um visual elegante e atemporal.</p>
                            <ul class="space-y-1 mb-6 text-gray-500 text-sm">
                                <li><i class="fas fa-check text-gold mr-2"></i>Análise personalizada do rosto</li>
                                <li><i class="fas fa-check text-gold mr-2"></i>Técnicas tradicionais de barbeiro</li>
                                <li><i class="fas fa-check text-gold mr-2"></i>Produtos premium inclusos</li>
                            </ul>
                            <div class="flex items-end justify-between mt-4">
                                <div>
                                    <div class="text-3xl font-black text-gold-glow mb-1">€15</div>
                                    <div class="text-xs text-gray-400 flex items-center"><i class="fas fa-clock mr-1"></i>30 minutos</div>
                                </div>
                                <a href="<?php echo get_base_url('pages/agendar.php'); ?>" class="btn-premium text-base font-bold font-serif px-6 py-2"><i class="fas fa-calendar-plus mr-2 text-gold text-lg"></i>Agendar</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Card: Barba Completa -->
                <div class="group relative">
                    <div class="relative bg-white rounded-2xl shadow-md border border-gray-200 transition-all duration-500 overflow-hidden group-hover:-translate-y-2 group-hover:shadow-gold-lg group-hover:border-gold">
                        <div class="p-8 text-center">
                            <div class="w-20 h-20 mx-auto flex items-center justify-center mb-4 transition-transform duration-500 group-hover:scale-110">
                                <i class="fas fa-user-tie text-gold-glow text-4xl"></i>
                            </div>
                            <h3 class="text-2xl font-serif font-bold mb-2 text-gold-gradient transition-colors duration-300">Barba Completa</h3>
                            <p class="text-gray-600 mb-6 leading-relaxed text-base">Aparar e modelagem profissional com toalha quente, óleos especiais e acabamento preciso para uma barba sempre impecável.</p>
                            <ul class="space-y-1 mb-6 text-gray-500 text-sm">
                                <li><i class="fas fa-check text-gold mr-2"></i>Toalha quente relaxante</li>
                                <li><i class="fas fa-check text-gold mr-2"></i>Óleos hidratantes premium</li>
                                <li><i class="fas fa-check text-gold mr-2"></i>Modelagem personalizada</li>
                            </ul>
                            <div class="flex items-end justify-between mt-4">
                                <div>
                                    <div class="text-3xl font-black text-gold-glow mb-1">€12</div>
                                    <div class="text-xs text-gray-400 flex items-center"><i class="fas fa-clock mr-1"></i>25 minutos</div>
                                </div>
                                <a href="<?php echo get_base_url('pages/agendar.php'); ?>" class="btn-premium text-base font-bold font-serif px-6 py-2 group text-white group-hover:text-yellow-400"><i class="fas fa-calendar-plus mr-2 text-white text-lg group-hover:text-yellow-400"></i>Agendar</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Card: Corte + Barba (Destaque) -->
                <div class="group relative">
                    <div class="absolute -top-3 -right-3 z-20">
                        <div class="bg-white border border-gold text-gold text-xs font-bold px-3 py-1 rounded-full shadow-sm flex items-center"><i class="fas fa-star mr-1"></i>MAIS POPULAR</div>
                    </div>
                    <div class="relative bg-white rounded-2xl shadow-lg border-2 border-gold transition-all duration-500 overflow-hidden group-hover:-translate-y-2 group-hover:shadow-gold-lg group-hover:border-gold">
                        <div class="p-8 text-center">
                            <div class="w-20 h-20 mx-auto flex items-center justify-center mb-4 transition-transform duration-500 group-hover:scale-110">
                                <i class="fas fa-crown text-gold-glow text-4xl"></i>
                            </div>
                            <h3 class="text-2xl font-serif font-bold mb-2 text-gold-gradient transition-colors duration-300">Corte + Barba</h3>
                            <p class="text-gray-600 mb-6 leading-relaxed text-base">O serviço completo que une o melhor dos dois mundos. Experiência premium com desconto especial para quem quer o visual perfeito.</p>
                            <ul class="space-y-1 mb-6 text-gray-500 text-sm">
                                <li><i class="fas fa-check text-gold mr-2"></i>Serviço completo premium</li>
                                <li><i class="fas fa-check text-gold mr-2"></i>Economize €2 no combo</li>
                                <li><i class="fas fa-check text-gold mr-2"></i>Experiência VIP completa</li>
                            </ul>
                            <div class="flex items-end justify-between mt-4">
                                <div>
                                    <div class="flex items-baseline">
                                        <span class="text-3xl font-black text-gold-glow">€25</span>
                                        <span class="text-lg text-gray-400 line-through ml-2">€27</span>
                                    </div>
                                    <div class="text-xs text-gray-400 flex items-center"><i class="fas fa-clock mr-1"></i>50 minutos</div>
                                </div>
                                <a href="<?php echo get_base_url('pages/agendar.php'); ?>" class="btn-premium text-base font-bold font-serif px-6 py-2"><i class="fas fa-calendar-plus mr-2 text-gold text-lg"></i>Agendar</a>
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
    <section class="relative bg-gradient-to-br from-slate-900 via-gray-900 to-black text-white py-10 sm:py-16 md:py-20 overflow-x-hidden">
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
                    <i class="fas fa-rocket text-yellow-400 mr-3 text-lg glow-gold"></i>
                    <span class="text-yellow-300 font-medium">Transforme seu Visual Hoje</span>
                </div>

                <!-- Título Principal -->
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-black mb-8">
                    <span class="text-white font-serif heading-premium">Pronto para um</span><br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 font-serif heading-premium">
                        Novo Visual?
                    </span>
                </h2>

                <!-- Subtítulo -->
                <p class="text-xl md:text-2xl mb-12 text-gray-300 leading-relaxed max-w-3xl mx-auto">
                    Junte-se a milhares de clientes satisfeitos e descubra a diferença que 
                    <span class="text-yellow-400 font-semibold">35 anos de experiência</span> fazem no seu estilo
                </p>

                <!-- Estatísticas Rápidas -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 gap-y-10 mb-12 max-w-2xl mx-auto">
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-black text-yellow-400 mb-2 flex items-center justify-center">
                            <i class="fas fa-users mr-2 glow-gold"></i>
                            <span class="glow-gold">15K+</span>
                        </div>
                        <div class="text-gray-400 text-sm">Clientes Satisfeitos</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-black text-yellow-400 mb-2 flex items-center justify-center">
                            <i class="fas fa-star mr-2 glow-gold"></i>
                            <span class="glow-gold">5.0</span>
                        </div>
                        <div class="text-gray-400 text-sm">Avaliação Média</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-black text-yellow-400 mb-2 flex items-center justify-center">
                            <i class="fas fa-calendar-check mr-2 glow-gold"></i>
                            <span class="glow-gold">24/7</span>
                        </div>
                        <div class="text-gray-400 text-sm">Agendamento Online</div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 justify-center items-center mb-8 w-full max-w-xl mx-auto">
                          <a href="<?php echo get_base_url('pages/agendar.php'); ?>" 
                              class="btn-premium group relative overflow-hidden shadow-gold-lg animate-fade-in-up text-base sm:text-lg font-bold font-serif w-full sm:w-auto py-3 px-6 sm:py-5 sm:px-12 transition-all duration-500 hover:scale-110 hover:shadow-gold-lg flex justify-center items-center text-center mx-0">
                        <span class="relative z-10 flex items-center">
                            <i class="fas fa-calendar-plus mr-4 text-accent text-xl group-hover:animate-pulse"></i>
                            Reserve sua experiência premium
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-yellow-500 to-yellow-700 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                    </a>
                          <a href="<?php echo get_base_url('pages/contato.php'); ?>" 
                              class="group border-2 border-yellow-400 hover:bg-yellow-400 hover:text-black text-yellow-400 font-bold w-full sm:w-auto py-3 px-6 sm:py-5 sm:px-12 rounded-2xl text-base sm:text-lg transition-all duration-500 transform hover:scale-110 flex justify-center items-center text-center mx-0">
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