<?php
/**
 * Página Sobre Nós - Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Apresentar a história, valores e equipe da barbearia
 */

require_once __DIR__ . '/../config/config.php';

$page_title = 'Sobre Nós - ' . SITE_NAME;

// Buscar barbeiros para mostrar na equipe
try {
    $stmt = $pdo->prepare("SELECT nome, especialidade FROM barbeiros WHERE ativo = 1 ORDER BY nome ASC");
    $stmt->execute();
    $barbeiros = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Erro ao buscar barbeiros: " . $e->getMessage());
    $barbeiros = [];
}

include_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-black via-gray-900 to-black text-white py-20">
        <div class="container mx-auto px-4">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-4xl md:text-5xl font-bold mb-6 text-transparent bg-clip-text bg-gradient-to-r from-white to-dourado">
                        Sobre o Mr. Carlos Barbershop
                    </h1>
                    <p class="text-xl text-gray-300 mb-8 leading-relaxed">
                        Carlos Alves, amante do mundo da barbearia, começou por trabalhar num anexo para os seus amigos 
                        até decidir trocar os autocarros pela barbearia e oferecer um serviço diferenciado aos homens.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="servicos.php" 
                           class="bg-gradient-to-r from-dourado to-dourado_escuro hover:from-dourado_escuro hover:to-dourado text-black font-bold py-3 px-8 rounded-lg transition-all duration-300 transform hover:scale-105 text-center">
                            Nossos Serviços
                        </a>
                        <a href="contato.php" 
                           class="border-2 border-white hover:bg-white hover:text-black text-white font-bold py-3 px-8 rounded-lg transition-all duration-300 text-center">
                            Entre em Contato
                        </a>
                    </div>
                </div>
                <div class="text-center">
                    <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-8">
                        <div class="w-32 h-32 bg-gradient-to-r from-dourado to-dourado_escuro rounded-full mx-auto mb-6 flex items-center justify-center">
                            <i class="fas fa-cut text-black text-4xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold mb-4">Desde 1985</h3>
                        <p class="text-gray-300">
                            Três gerações de experiência em cuidados masculinos
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Nossa História -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Nossa História</h2>
                    <div class="w-24 h-1 bg-gradient-to-r from-dourado to-dourado_escuro mx-auto rounded-full"></div>
                </div>

                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <div class="bg-gray-200 rounded-xl h-80 flex items-center justify-center">
                            <div class="text-center text-gray-600">
                                <i class="fas fa-images text-6xl mb-4"></i>
                                <p class="text-lg">Foto histórica da barbearia</p>
                                <p class="text-sm">(Carlos fundador, anos 80)</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <h3 class="text-2xl font-semibold text-gray-900">A Origem de uma Tradição</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Carlos Alves, amante do mundo da barbearia, começou por trabalhar num anexo para os seus amigos 
                            até decidir trocar os autocarros pela barbearia. Assim, abriu a sua própria barbearia com a 
                            missão de prestar um serviço diferenciado aos homens.
                        </p>
                        <p class="text-gray-600 leading-relaxed">
                            Com paixão e dedicação, Carlos aperfeiçoou suas técnicas ao longo do tempo, sempre focado em 
                            oferecer o melhor atendimento e cuidado personalizado para cada cliente.
                        </p>
                        <div class="bg-dourado bg-opacity-10 border-l-4 border-dourado p-4 rounded-r-lg">
                            <p class="text-gray-700 italic">
                                "Cada cliente que senta na minha cadeira não é apenas um corte, é uma pessoa que confia 
                                em mim para cuidar da sua imagem. É uma responsabilidade que levo a sério."
                            </p>
                            <p class="text-sm text-gray-600 mt-2">- Carlos Alves</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Nossos Valores -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Nossos Valores</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Princípios que norteiam nosso trabalho e relacionamento com os clientes há mais de três décadas
                </p>
                <div class="w-24 h-1 bg-gradient-to-r from-dourado to-dourado_escuro mx-auto mt-4 rounded-full"></div>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Tradição -->
                <div class="bg-white rounded-xl p-8 text-center shadow-lg border border-gray-200 hover:shadow-xl hover:border-dourado transition-all duration-300 group">
                    <div class="w-16 h-16 bg-dourado bg-opacity-20 rounded-full mx-auto mb-6 flex items-center justify-center group-hover:bg-dourado group-hover:shadow-lg transition-all duration-300">
                        <i class="fas fa-history text-dourado text-2xl group-hover:text-black transition-colors duration-300"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4 group-hover:text-dourado transition-colors">Tradição</h3>
                    <p class="text-gray-600">
                        Preservamos as técnicas clássicas de barbearia, passadas de geração em geração, 
                        mantendo viva a arte tradicional do cuidado masculino.
                    </p>
                </div>

                <!-- Qualidade -->
                <div class="bg-white rounded-xl p-8 text-center shadow-lg border border-gray-200 hover:shadow-xl hover:border-dourado transition-all duration-300 group">
                    <div class="w-16 h-16 bg-dourado bg-opacity-20 rounded-full mx-auto mb-6 flex items-center justify-center group-hover:bg-dourado group-hover:shadow-lg transition-all duration-300">
                        <i class="fas fa-star text-dourado text-2xl group-hover:text-black transition-colors duration-300"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4 group-hover:text-dourado transition-colors">Qualidade</h3>
                    <p class="text-gray-600">
                        Utilizamos apenas produtos premium e equipamentos de alta qualidade para garantir 
                        o melhor resultado em cada atendimento.
                    </p>
                </div>

                <!-- Excelência -->
                <div class="bg-white rounded-xl p-8 text-center shadow-lg border border-gray-200 hover:shadow-xl hover:border-dourado transition-all duration-300 group">
                    <div class="w-16 h-16 bg-dourado bg-opacity-20 rounded-full mx-auto mb-6 flex items-center justify-center group-hover:bg-dourado group-hover:shadow-lg transition-all duration-300">
                        <i class="fas fa-trophy text-dourado text-2xl group-hover:text-black transition-colors duration-300"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4 group-hover:text-dourado transition-colors">Excelência</h3>
                    <p class="text-gray-600">
                        Buscamos constantemente a perfeição em cada detalhe, desde o atendimento até 
                        o acabamento final de cada corte.
                    </p>
                </div>

                <!-- Confiança -->
                <div class="bg-white rounded-xl p-8 text-center shadow-lg border border-gray-200 hover:shadow-xl hover:border-dourado transition-all duration-300 group">
                    <div class="w-16 h-16 bg-dourado bg-opacity-20 rounded-full mx-auto mb-6 flex items-center justify-center group-hover:bg-dourado group-hover:shadow-lg transition-all duration-300">
                        <i class="fas fa-handshake text-dourado text-2xl group-hover:text-black transition-colors duration-300"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4 group-hover:text-dourado transition-colors">Confiança</h3>
                    <p class="text-gray-600">
                        Construímos relacionamentos duradouros baseados na confiança mútua e no 
                        respeito pelos nossos clientes.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Nossa Equipe -->
    <?php if (!empty($barbeiros)): ?>
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Nossa Equipe</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Profissionais experientes e apaixonados pelo que fazem, sempre prontos para oferecer o melhor atendimento
                </p>
                <div class="w-24 h-1 bg-gradient-to-r from-dourado to-dourado_escuro mx-auto mt-4 rounded-full"></div>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-4xl mx-auto">
                <?php foreach ($barbeiros as $barbeiro): ?>
                    <div class="bg-gray-50 rounded-xl p-8 text-center hover:shadow-lg transition-all duration-300 border border-gray-200 hover:border-dourado group">
                        <div class="w-24 h-24 bg-gradient-to-r from-dourado to-dourado_escuro rounded-full mx-auto mb-6 flex items-center justify-center group-hover:shadow-xl transition-all duration-300">
                            <span class="text-black text-2xl font-bold">
                                <?= strtoupper(substr($barbeiro['nome'], 0, 1)) ?>
                            </span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-dourado transition-colors">
                            <?= htmlspecialchars($barbeiro['nome']) ?>
                        </h3>
                        <?php if ($barbeiro['especialidade']): ?>
                            <p class="text-gray-600 mb-4">
                                Especialista em <?= htmlspecialchars($barbeiro['especialidade']) ?>
                            </p>
                        <?php endif; ?>
                        <div class="flex justify-center space-x-3">
                            <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center group-hover:bg-dourado transition-colors">
                                <i class="fas fa-cut text-gray-600 group-hover:text-black text-sm"></i>
                            </div>
                            <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center group-hover:bg-dourado transition-colors">
                                <i class="fas fa-award text-gray-600 group-hover:text-black text-sm"></i>
                            </div>
                            <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center group-hover:bg-dourado transition-colors">
                                <i class="fas fa-star text-gray-600 group-hover:text-black text-sm"></i>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Nosso Espaço -->
    <section class="py-16 bg-gray-900 text-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold mb-4">Nosso Espaço</h2>
                <p class="text-lg text-gray-300 max-w-2xl mx-auto">
                    Um ambiente acolhedor e moderno, projetado para proporcionar conforto e relaxamento
                </p>
                <div class="w-24 h-1 bg-gradient-to-r from-dourado to-dourado_escuro mx-auto mt-4 rounded-full"></div>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Ambiente Principal -->
                <div class="bg-gray-800 rounded-xl p-6">
                    <div class="bg-gray-700 rounded-lg h-48 mb-4 flex items-center justify-center">
                        <div class="text-center text-gray-400">
                            <i class="fas fa-chair text-4xl mb-2"></i>
                            <p>Cadeiras Clássicas</p>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-dourado">Ambiente Principal</h3>
                    <p class="text-gray-300">
                        Cadeiras de barbearia clássicas em um ambiente que combina tradição e modernidade.
                    </p>
                </div>

                <!-- Área VIP -->
                <div class="bg-gray-800 rounded-xl p-6">
                    <div class="bg-gray-700 rounded-lg h-48 mb-4 flex items-center justify-center">
                        <div class="text-center text-gray-400">
                            <i class="fas fa-crown text-4xl mb-2"></i>
                            <p>Área Premium</p>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-dourado">Área VIP</h3>
                    <p class="text-gray-300">
                        Espaço exclusivo para atendimentos premium com máximo conforto e privacidade.
                    </p>
                </div>

                <!-- Recepção -->
                <div class="bg-gray-800 rounded-xl p-6">
                    <div class="bg-gray-700 rounded-lg h-48 mb-4 flex items-center justify-center">
                        <div class="text-center text-gray-400">
                            <i class="fas fa-coffee text-4xl mb-2"></i>
                            <p>Área de Espera</p>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-dourado">Recepção Acolhedora</h3>
                    <p class="text-gray-300">
                        Área de espera confortável com café cortesia e entretenimento para nossos clientes.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Compromisso com a Qualidade -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="bg-gradient-to-r from-dourado to-dourado_escuro rounded-xl p-8 md:p-12 text-black">
                    <div class="text-center">
                        <h2 class="text-3xl font-bold mb-6">Nosso Compromisso</h2>
                        <p class="text-xl mb-8 leading-relaxed">
                            Não somos apenas uma barbearia, somos guardiões de uma tradição que valoriza 
                            o cuidado pessoal masculino como uma arte. Cada cliente é único, e nosso compromisso 
                            é proporcionar uma experiência personalizada que supere suas expectativas.
                        </p>
                        
                        <div class="grid md:grid-cols-3 gap-8 mb-8">
                            <div class="text-center">
                                <div class="text-3xl font-bold mb-2">35+</div>
                                <div class="text-sm">Anos de Experiência</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold mb-2">10,000+</div>
                                <div class="text-sm">Clientes Satisfeitos</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold mb-2">100%</div>
                                <div class="text-sm">Dedicação à Qualidade</div>
                            </div>
                        </div>
                        
                        <a href="agendar.php" 
                           class="bg-black text-white font-bold py-4 px-8 rounded-lg hover:bg-gray-800 transition-all duration-300 transform hover:scale-105 inline-block">
                            Faça Parte da Nossa História
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Scripts específicos da página -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animações de entrada
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in-up');
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Preparar elementos para animação
    const animatedElements = document.querySelectorAll('.group, .text-center > h2, .grid > div');
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
        observer.observe(el);
    });

    // Counter animação para estatísticas
    function animateCounter(element, target, duration = 2000) {
        const start = 0;
        const increment = target / (duration / 16);
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            
            if (target >= 1000) {
                element.textContent = (Math.floor(current / 100) / 10).toFixed(1) + 'k+';
            } else {
                element.textContent = Math.floor(current) + (target === 100 ? '%' : '+');
            }
        }, 16);
    }

    // Observar seção de estatísticas
    const statsSection = document.querySelector('.grid.md\\:grid-cols-3');
    if (statsSection) {
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counters = entry.target.querySelectorAll('.text-3xl.font-bold');
                    counters.forEach((counter, index) => {
                        const targets = [35, 10000, 100];
                        setTimeout(() => {
                            animateCounter(counter, targets[index]);
                        }, index * 200);
                    });
                    statsObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        statsObserver.observe(statsSection);
    }
});
</script>

<style>
/* Transições suaves para hover effects */
.group {
    transition: all 0.3s ease;
}

/* Efeito parallax sutil no hero */
.bg-gradient-to-br {
    background-attachment: fixed;
}

/* Animação personalizada para cards */
.group:hover {
    transform: translateY(-5px);
}

/* Gradiente animado */
@keyframes gradient-shift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.bg-gradient-to-r {
    background-size: 200% 200%;
    animation: gradient-shift 6s ease infinite;
}
</style>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>