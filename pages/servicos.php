<?php
/**
 * Página de Serviços - Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Catálogo público de serviços da barbearia
 */

require_once __DIR__ . '/../config/config.php';

$page_title = 'Nossos Serviços - ' . SITE_NAME;

try {
    // Buscar todos os serviços ativos agrupados por categoria
    $stmt = $pdo->prepare("SELECT * FROM servicos WHERE ativo = 1 ORDER BY categoria ASC, preco ASC");
    $stmt->execute();
    $servicos_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $servicos = [];
    foreach ($servicos_data as $row) {
        $categoria = $row['categoria'] ?: 'Outros Serviços';
        if (!isset($servicos[$categoria])) {
            $servicos[$categoria] = [];
        }
        $servicos[$categoria][] = $row;
    }
    
    // Buscar barbeiros para mostrar a equipe
    $stmt = $pdo->prepare("SELECT nome, especialidade FROM barbeiros WHERE ativo = 1 ORDER BY nome ASC");
    $stmt->execute();
    $barbeiros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Erro na página de serviços: " . $e->getMessage());
    $servicos = [];
    $barbeiros = [];
}

include_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-gray-50">
    <!-- Hero Section Modernizada -->
    <section class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-gray-900 to-black text-white">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, #C9A227 2px, transparent 0); background-size: 40px 40px;"></div>
        </div>
        
        <!-- Background Effects -->
        <div class="absolute top-0 left-0 w-96 h-96 bg-gradient-to-r from-yellow-400/10 to-yellow-600/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-gradient-to-l from-yellow-400/10 to-yellow-600/5 rounded-full blur-3xl"></div>
        
        <!-- Content -->
        <div class="relative container mx-auto px-4 py-24 lg:py-32">
            <div class="max-w-5xl mx-auto text-center">
                <!-- Badge Premium -->
                <div class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-yellow-400/20 to-yellow-600/20 rounded-full border border-yellow-400/30 mb-8">
                    <i class="fas fa-scissors text-yellow-400 mr-3 text-lg"></i>
                    <span class="text-yellow-300 font-medium">Catálogo Premium de Serviços</span>
                </div>

                <!-- Título Principal -->
                <h1 class="text-5xl md:text-6xl lg:text-7xl font-black mb-8 tracking-tight">
                    <span class="block text-white">Nossos</span>
                    <span class="block text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600">Serviços</span>
                </h1>

                <!-- Subtítulo Elegante -->
                <p class="text-xl md:text-2xl lg:text-3xl mb-12 text-gray-300 font-light leading-relaxed max-w-4xl mx-auto">
                    Cada serviço é uma <span class="text-yellow-400 font-semibold">experiência única</span>, 
                    executada por barbeiros master com <span class="text-yellow-400 font-semibold">mais de 35 anos</span> de tradição
                </p>

                <!-- Botões de Ação Modernos -->
                <div class="flex flex-col sm:flex-row gap-6 justify-center items-center mb-16">
                    <a href="agendar.php" 
                       class="group relative overflow-hidden bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-500 hover:to-yellow-700 text-black font-bold py-4 px-10 rounded-xl transition-all duration-500 transform hover:scale-105 hover:shadow-2xl hover:shadow-yellow-500/50">
                        <span class="relative z-10 flex items-center">
                            <i class="fas fa-calendar-plus mr-3 text-lg"></i>
                            Agendar Agora
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-yellow-500 to-yellow-700 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    </a>
                    
                    <a href="#servicos" 
                       class="group relative border-2 border-yellow-400 hover:bg-yellow-400 hover:text-black text-yellow-400 font-bold py-4 px-10 rounded-xl transition-all duration-500 transform hover:scale-105">
                        <span class="flex items-center">
                            <i class="fas fa-tags mr-3 text-lg"></i>
                            Ver Todos os Preços
                        </span>
                    </a>
                </div>

                <!-- Indicadores de Qualidade -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 max-w-3xl mx-auto">
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-4">
                            <i class="fas fa-cut text-white text-2xl"></i>
                        </div>
                        <div class="text-2xl font-bold text-yellow-400 mb-2">15+</div>
                        <div class="text-gray-400 text-sm">Tipos de Corte</div>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto bg-gradient-to-r from-green-500 to-green-600 rounded-2xl flex items-center justify-center mb-4">
                            <i class="fas fa-user-tie text-white text-2xl"></i>
                        </div>
                        <div class="text-2xl font-bold text-yellow-400 mb-2">5</div>
                        <div class="text-gray-400 text-sm">Barbeiros Master</div>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mb-4">
                            <i class="fas fa-leaf text-white text-2xl"></i>
                        </div>
                        <div class="text-2xl font-bold text-yellow-400 mb-2">100%</div>
                        <div class="text-gray-400 text-sm">Produtos Premium</div>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto bg-gradient-to-r from-red-500 to-red-600 rounded-2xl flex items-center justify-center mb-4">
                            <i class="fas fa-star text-white text-2xl"></i>
                        </div>
                        <div class="text-2xl font-bold text-yellow-400 mb-2">5.0</div>
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

    <!-- Catálogo de Serviços Premium -->
    <section id="servicos" class="py-20 bg-gradient-to-br from-gray-50 via-white to-gray-100 relative overflow-hidden">
        <!-- Background Decoration -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-l from-yellow-400/5 to-transparent rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-gradient-to-r from-gray-900/3 to-transparent rounded-full blur-3xl"></div>
        
        <div class="container mx-auto px-4 relative z-10">
            <?php if (empty($servicos)): ?>
                <!-- Estado Vazio com Design Moderno -->
                <div class="text-center py-20 max-w-2xl mx-auto">
                    <div class="relative mb-8">
                        <div class="w-32 h-32 mx-auto bg-gradient-to-r from-gray-200 to-gray-300 rounded-full flex items-center justify-center mb-6">
                            <i class="fas fa-cut text-4xl text-gray-500"></i>
                        </div>
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center">
                            <i class="fas fa-plus text-black text-sm"></i>
                        </div>
                    </div>
                    
                    <h3 class="text-3xl font-bold text-gray-800 mb-4">Serviços em Preparação</h3>
                    <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                        Estamos finalizando nosso catálogo premium de serviços. Em breve você poderá conhecer todos os detalhes dos nossos tratamentos exclusivos.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="contato.php" class="bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-500 hover:to-yellow-700 text-black font-bold py-3 px-8 rounded-xl transition-all duration-300 transform hover:scale-105">
                            <i class="fas fa-phone mr-2"></i>
                            Entre em Contato
                        </a>
                        <a href="../index.php" class="border-2 border-gray-300 hover:border-yellow-400 hover:text-yellow-600 text-gray-600 font-bold py-3 px-8 rounded-xl transition-all duration-300">
                            <i class="fas fa-home mr-2"></i>
                            Voltar ao Início
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Catálogo de Serviços -->
                <?php 
                $categoria_colors = [
                    'Cortes' => ['from-blue-500', 'to-blue-600', 'text-blue-600', 'border-blue-300'],
                    'Barba' => ['from-green-500', 'to-green-600', 'text-green-600', 'border-green-300'],
                    'Tratamentos' => ['from-purple-500', 'to-purple-600', 'text-purple-600', 'border-purple-300'],
                    'Combos' => ['from-yellow-500', 'to-yellow-600', 'text-yellow-600', 'border-yellow-300'],
                    'Outros Serviços' => ['from-red-500', 'to-red-600', 'text-red-600', 'border-red-300']
                ];
                $default_colors = ['from-gray-500', 'to-gray-600', 'text-gray-600', 'border-gray-300'];
                ?>
                
                <?php foreach ($servicos as $categoria => $servicos_categoria): ?>
                    <?php 
                    $colors = $categoria_colors[$categoria] ?? $default_colors;
                    $categoria_id = strtolower(str_replace(' ', '-', $categoria));
                    ?>
                    
                    <div class="mb-20">
                        <!-- Cabeçalho da Categoria Moderno -->
                        <div class="text-center mb-16">
                            <div class="inline-flex items-center px-6 py-3 bg-white rounded-full border-2 <?= $colors[3] ?> mb-6 shadow-lg">
                                <div class="w-8 h-8 bg-gradient-to-r <?= $colors[0] ?> <?= $colors[1] ?> rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-scissors text-white text-sm"></i>
                                </div>
                                <span class="<?= $colors[2] ?> font-semibold"><?= htmlspecialchars($categoria) ?></span>
                            </div>
                            
                            <h2 class="text-4xl lg:text-5xl font-black text-gray-900 mb-4">
                                <?= htmlspecialchars($categoria) ?> <span class="<?= $colors[2] ?>">Premium</span>
                            </h2>
                            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                                Serviços especializados executados com técnicas avançadas e produtos de qualidade superior
                            </p>
                        </div>

                        <!-- Grid de Serviços Modernizado -->
                        <div class="grid lg:grid-cols-3 md:grid-cols-2 gap-8">
                            <?php foreach ($servicos_categoria as $servico): ?>
                                <div class="group relative">
                                    <!-- Card Principal -->
                                    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 hover:border-<?= str_replace('text-', '', $colors[2]) ?>-300 transition-all duration-500 overflow-hidden hover:-translate-y-2 hover:shadow-2xl">
                                        <!-- Header Premium -->
                                        <div class="bg-gradient-to-r <?= $colors[0] ?> <?= $colors[1] ?> p-8 text-white relative">
                                            <!-- Pattern Background -->
                                            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 20px 20px;"></div>
                                            
                                            <div class="relative z-10">
                                                <!-- Ícone do Serviço -->
                                                <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                                    <i class="fas fa-cut text-2xl"></i>
                                                </div>
                                                
                                                <!-- Nome e Preço -->
                                                <h3 class="text-2xl font-bold mb-4 leading-tight">
                                                    <?= htmlspecialchars($servico['nome']) ?>
                                                </h3>
                                                
                                                <div class="flex justify-between items-end">
                                                    <div>
                                                        <div class="text-3xl font-black mb-1">
                                                            R$ <?= number_format($servico['preco'], 2, ',', '.') ?>
                                                        </div>
                                                        <div class="text-white/80 text-sm flex items-center">
                                                            <i class="fas fa-clock mr-2"></i>
                                                            <?= intval($servico['duracao'] / 60) ?>h <?= $servico['duracao'] % 60 ?>min
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Badge de Qualidade -->
                                                    <div class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold">
                                                        PREMIUM
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Conteúdo do Card -->
                                        <div class="p-8">
                                            <!-- Descrição -->
                                            <?php if ($servico['descricao']): ?>
                                                <p class="text-gray-600 mb-6 leading-relaxed">
                                                    <?= htmlspecialchars($servico['descricao']) ?>
                                                </p>
                                            <?php else: ?>
                                                <p class="text-gray-600 mb-6 leading-relaxed">
                                                    Serviço executado com técnicas profissionais e produtos premium para garantir o melhor resultado.
                                                </p>
                                            <?php endif; ?>

                                            <!-- Features Premium -->
                                            <div class="space-y-3 mb-8">
                                                <div class="flex items-center text-sm text-gray-700">
                                                    <div class="w-6 h-6 bg-gradient-to-r <?= $colors[0] ?> <?= $colors[1] ?> rounded-full flex items-center justify-center mr-3">
                                                        <i class="fas fa-check text-white text-xs"></i>
                                                    </div>
                                                    <span>Produtos premium inclusos</span>
                                                </div>
                                                <div class="flex items-center text-sm text-gray-700">
                                                    <div class="w-6 h-6 bg-gradient-to-r <?= $colors[0] ?> <?= $colors[1] ?> rounded-full flex items-center justify-center mr-3">
                                                        <i class="fas fa-check text-white text-xs"></i>
                                                    </div>
                                                    <span>Atendimento personalizado</span>
                                                </div>
                                                <div class="flex items-center text-sm text-gray-700">
                                                    <div class="w-6 h-6 bg-gradient-to-r <?= $colors[0] ?> <?= $colors[1] ?> rounded-full flex items-center justify-center mr-3">
                                                        <i class="fas fa-check text-white text-xs"></i>
                                                    </div>
                                                    <span>Garantia de satisfação</span>
                                                </div>
                                            </div>

                                            <!-- Botão de Agendamento Premium -->
                                            <a href="agendar.php?servico=<?= $servico['id'] ?>" 
                                               class="group/btn w-full bg-gradient-to-r <?= $colors[0] ?> <?= $colors[1] ?> hover:shadow-lg hover:shadow-<?= str_replace('text-', '', $colors[2]) ?>-500/25 text-white font-bold py-4 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 flex items-center justify-center relative overflow-hidden">
                                                <span class="relative z-10 flex items-center">
                                                    <i class="fas fa-calendar-plus mr-3 text-lg"></i>
                                                    Agendar Este Serviço
                                                </span>
                                                <div class="absolute inset-0 bg-black opacity-0 group-hover/btn:opacity-10 transition-opacity duration-300"></div>
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <!-- Elementos Decorativos -->
                                    <div class="absolute -top-3 -right-3 w-6 h-6 bg-gradient-to-r <?= $colors[0] ?> <?= $colors[1] ?> rounded-full opacity-20 group-hover:opacity-40 transition-opacity duration-500"></div>
                                    <div class="absolute -bottom-3 -left-3 w-4 h-4 bg-gradient-to-r <?= $colors[0] ?> <?= $colors[1] ?> rounded-full opacity-15 group-hover:opacity-30 transition-opacity duration-500"></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Nossa Equipe Premium -->
    <?php if (!empty($barbeiros)): ?>
    <section class="py-20 bg-white relative overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute top-20 left-0 w-72 h-72 bg-gradient-to-r from-yellow-400/5 to-yellow-600/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-0 w-72 h-72 bg-gradient-to-l from-gray-900/5 to-gray-700/5 rounded-full blur-3xl"></div>
        
        <div class="container mx-auto px-4 relative z-10">
            <!-- Cabeçalho da Seção -->
            <div class="text-center mb-16">
                <div class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-900 to-black rounded-full mb-6">
                    <i class="fas fa-users text-yellow-400 mr-3 text-lg"></i>
                    <span class="text-white font-medium">Conheça Nossa Equipe</span>
                </div>
                
                <h2 class="text-4xl lg:text-5xl font-black text-gray-900 mb-6">
                    Barbeiros <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-600 to-yellow-500">Master</span>
                </h2>
                
                <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    Profissionais certificados com anos de experiência, especializados em técnicas tradicionais 
                    e tendências modernas para garantir o resultado perfeito
                </p>
            </div>

            <!-- Grid da Equipe -->
            <div class="grid lg:grid-cols-3 md:grid-cols-2 gap-8 mb-12">
                <?php 
                $barbeiro_icons = ['fa-cut', 'fa-user-tie', 'fa-scissors', 'fa-magic', 'fa-crown'];
                $barbeiro_colors = [
                    ['from-blue-500', 'to-blue-600'],
                    ['from-green-500', 'to-green-600'],
                    ['from-purple-500', 'to-purple-600'],
                    ['from-red-500', 'to-red-600'],
                    ['from-yellow-500', 'to-yellow-600']
                ];
                ?>
                
                <?php foreach ($barbeiros as $index => $barbeiro): ?>
                    <?php 
                    $icon = $barbeiro_icons[$index % count($barbeiro_icons)];
                    $colors = $barbeiro_colors[$index % count($barbeiro_colors)];
                    ?>
                    
                    <div class="group relative">
                        <!-- Card Principal -->
                        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 hover:border-yellow-300 transition-all duration-500 overflow-hidden hover:-translate-y-2 hover:shadow-2xl">
                            <!-- Header com Foto/Avatar -->
                            <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-8 text-center relative">
                                <!-- Background Pattern -->
                                <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle at 2px 2px, #C9A227 1px, transparent 0); background-size: 20px 20px;"></div>
                                
                                <div class="relative z-10">
                                    <!-- Avatar Premium -->
                                    <div class="relative mb-6">
                                        <div class="w-24 h-24 mx-auto bg-gradient-to-r <?= $colors[0] ?> <?= $colors[1] ?> rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300 shadow-lg">
                                            <i class="fas <?= $icon ?> text-white text-3xl"></i>
                                        </div>
                                        <!-- Badge de Master -->
                                        <div class="absolute -bottom-2 -right-2 bg-yellow-400 text-black text-xs font-bold px-2 py-1 rounded-full">
                                            MASTER
                                        </div>
                                    </div>
                                    
                                    <!-- Nome -->
                                    <h3 class="text-2xl font-bold text-gray-900 mb-3 group-hover:text-yellow-600 transition-colors">
                                        <?= htmlspecialchars($barbeiro['nome']) ?>
                                    </h3>
                                    
                                    <!-- Especialidade -->
                                    <?php if ($barbeiro['especialidade']): ?>
                                        <div class="inline-flex items-center px-4 py-2 bg-white rounded-full border-2 border-gray-200 group-hover:border-yellow-300 transition-colors">
                                            <i class="fas fa-star text-yellow-500 mr-2 text-sm"></i>
                                            <span class="text-sm font-medium text-gray-700">
                                                <?= htmlspecialchars($barbeiro['especialidade']) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Conteúdo do Card -->
                            <div class="p-8">
                                <!-- Skills/Qualidades -->
                                <div class="space-y-3 mb-6">
                                    <div class="flex items-center text-sm text-gray-700">
                                        <div class="w-6 h-6 bg-gradient-to-r <?= $colors[0] ?> <?= $colors[1] ?> rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-check text-white text-xs"></i>
                                        </div>
                                        <span>Certificação profissional</span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-700">
                                        <div class="w-6 h-6 bg-gradient-to-r <?= $colors[0] ?> <?= $colors[1] ?> rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-check text-white text-xs"></i>
                                        </div>
                                        <span>Anos de experiência</span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-700">
                                        <div class="w-6 h-6 bg-gradient-to-r <?= $colors[0] ?> <?= $colors[1] ?> rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-check text-white text-xs"></i>
                                        </div>
                                        <span>Atendimento personalizado</span>
                                    </div>
                                </div>
                                
                                <!-- Avaliação -->
                                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl p-4 text-center">
                                    <div class="flex justify-center items-center mb-2">
                                        <div class="flex text-yellow-400 text-sm">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <span class="ml-2 text-gray-700 font-bold text-sm">5.0</span>
                                    </div>
                                    <p class="text-gray-600 text-xs italic">
                                        "Excelência reconhecida pelos clientes"
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Elementos Decorativos -->
                        <div class="absolute -top-3 -right-3 w-6 h-6 bg-gradient-to-r <?= $colors[0] ?> <?= $colors[1] ?> rounded-full opacity-20 group-hover:opacity-40 transition-opacity duration-500"></div>
                        <div class="absolute -bottom-3 -left-3 w-4 h-4 bg-gradient-to-r <?= $colors[0] ?> <?= $colors[1] ?> rounded-full opacity-15 group-hover:opacity-30 transition-opacity duration-500"></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Call to Action para Agendamento -->
            <div class="text-center bg-gradient-to-r from-gray-900 to-black rounded-3xl p-12 text-white">
                <!-- Pattern Background -->
                <div class="absolute inset-0 opacity-5 rounded-3xl" style="background-image: radial-gradient(circle at 2px 2px, #C9A227 2px, transparent 0); background-size: 30px 30px;"></div>
                
                <div class="relative z-10">
                    <h3 class="text-3xl font-bold mb-4">
                        Escolha seu <span class="text-yellow-400">Barbeiro Preferido</span>
                    </h3>
                    <p class="text-gray-300 mb-8 max-w-2xl mx-auto">
                        Todos os nossos profissionais estão preparados para oferecer o melhor atendimento. 
                        Agende seu horário e experimente a diferença.
                    </p>
                    
                    <a href="agendar.php" 
                       class="inline-flex items-center bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-500 hover:to-yellow-700 text-black font-bold py-4 px-10 rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-yellow-500/25">
                        <i class="fas fa-calendar-plus mr-3 text-lg"></i>
                        <span>Agendar com Nossa Equipe</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Diferenciais -->
    <section class="py-16 bg-gray-900 text-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold mb-4">Por Que Escolher o Mr. Carlos?</h2>
                <p class="text-lg text-gray-300">
                    Mais de 35 anos oferecendo o melhor em cuidados masculinos
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Experiência -->
                <div class="text-center group">
                    <div class="w-16 h-16 bg-dourado rounded-full mx-auto mb-4 flex items-center justify-center group-hover:shadow-lg group-hover:scale-110 transition-all duration-300">
                        <i class="fas fa-award text-black text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2 group-hover:text-dourado transition-colors">35+ Anos</h3>
                    <p class="text-gray-400">De experiência e tradição em cuidados masculinos</p>
                </div>

                <!-- Qualidade -->
                <div class="text-center group">
                    <div class="w-16 h-16 bg-dourado rounded-full mx-auto mb-4 flex items-center justify-center group-hover:shadow-lg group-hover:scale-110 transition-all duration-300">
                        <i class="fas fa-star text-black text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2 group-hover:text-dourado transition-colors">Qualidade Premium</h3>
                    <p class="text-gray-400">Produtos e equipamentos de primeira linha</p>
                </div>

                <!-- Profissionais -->
                <div class="text-center group">
                    <div class="w-16 h-16 bg-dourado rounded-full mx-auto mb-4 flex items-center justify-center group-hover:shadow-lg group-hover:scale-110 transition-all duration-300">
                        <i class="fas fa-users text-black text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2 group-hover:text-dourado transition-colors">Equipe Especializada</h3>
                    <p class="text-gray-400">Profissionais capacitados e experientes</p>
                </div>

                <!-- Ambiente -->
                <div class="text-center group">
                    <div class="w-16 h-16 bg-dourado rounded-full mx-auto mb-4 flex items-center justify-center group-hover:shadow-lg group-hover:scale-110 transition-all duration-300">
                        <i class="fas fa-home text-black text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2 group-hover:text-dourado transition-colors">Ambiente Acolhedor</h3>
                    <p class="text-gray-400">Espaço confortável e higienizado</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Final -->
    <section class="py-16 bg-gradient-to-r from-dourado to-dourado_escuro">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold text-black mb-6">Pronto para Transformar seu Visual?</h2>
            <p class="text-xl text-gray-800 mb-8 max-w-2xl mx-auto">
                Agende seu horário e descubra por que somos a escolha número 1 em cuidados masculinos
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="agendar.php" 
                   class="bg-black text-white font-bold py-4 px-8 rounded-lg hover:bg-gray-800 transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-calendar-plus mr-2"></i>
                    Agendar Meu Horário
                </a>
                <a href="contato.php" 
                   class="border-2 border-black hover:bg-black hover:text-white text-black font-bold py-4 px-8 rounded-lg transition-all duration-300">
                    <i class="fas fa-phone mr-2"></i>
                    Entrar em Contato
                </a>
            </div>
        </div>
    </section>
</div>

<!-- Scripts específicos da página -->
<script>
// Scroll suave para seção de serviços
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Verificar se há parâmetro de serviço na URL e destacar
    const urlParams = new URLSearchParams(window.location.search);
    const servicoId = urlParams.get('servico');
    if (servicoId) {
        // Destacar serviço específico se vier da home
        setTimeout(() => {
            const servicoElement = document.querySelector(`a[href*="servico=${servicoId}"]`);
            if (servicoElement) {
                servicoElement.closest('.group').classList.add('ring-2', 'ring-dourado');
                servicoElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }, 500);
    }
});

// Animações de entrada
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-fade-in-up');
        }
    });
}, observerOptions);

// Observar elementos para animação
document.addEventListener('DOMContentLoaded', function() {
    const animatedElements = document.querySelectorAll('.group, .text-center');
    animatedElements.forEach(el => observer.observe(el));
});
</script>

<style>
/* Animações customizadas */
.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out forwards;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Efeitos hover personalizados */
.group:hover .fas {
    transform: scale(1.1);
    transition: transform 0.3s ease;
}

/* Gradientes suaves */
.bg-gradient-to-r {
    background-attachment: fixed;
}
</style>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>