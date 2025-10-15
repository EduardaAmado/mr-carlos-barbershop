<?php
/**
 * Página de Contato - Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Formulário de contato e informações da barbearia
 */

require_once __DIR__ . '/../config/config.php';

$page_title = 'Contato - ' . SITE_NAME;
$sucesso = '';
$erro = '';

// Processar formulário de contato
if ($_POST) {
    try {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $assunto = trim($_POST['assunto'] ?? '');
        $mensagem = trim($_POST['mensagem'] ?? '');
        
        // Validações
        if (!$nome || !$email || !$assunto || !$mensagem) {
            throw new Exception('Por favor, preencha todos os campos obrigatórios.');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Por favor, informe um email válido.');
        }
        
        if (strlen($mensagem) < 10) {
            throw new Exception('A mensagem deve ter pelo menos 10 caracteres.');
        }
        
        if (strlen($mensagem) > 1000) {
            throw new Exception('A mensagem não pode exceder 1000 caracteres.');
        }
        
        // Tentar enviar email
        if (file_exists(__DIR__ . '/../includes/email.php')) {
            require_once __DIR__ . '/../includes/email.php';
            
            $dados_contato = [
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefone,
                'assunto' => $assunto,
                'mensagem' => $mensagem
            ];
            
            $email_enviado = enviar_email_contato($dados_contato);
            
            if ($email_enviado) {
                $sucesso = 'Mensagem enviada com sucesso! Entraremos em contato em breve.';
                // Limpar campos após sucesso
                $_POST = [];
            } else {
                $erro = 'Houve um problema ao enviar sua mensagem. Tente entrar em contato pelo telefone.';
            }
        } else {
            // Sistema de email não configurado, salvar no banco (opcional)
            $erro = 'Sistema de email temporariamente indisponível. Entre em contato pelo telefone: (11) 99999-9999';
        }
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

include_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-black via-gray-900 to-black text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6 text-transparent bg-clip-text bg-gradient-to-r from-white to-dourado">
                Entre em Contato
            </h1>
            <p class="text-xl md:text-2xl mb-8 text-gray-300 max-w-3xl mx-auto">
                Estamos aqui para tirar suas dúvidas e agendar seu próximo visual
            </p>
        </div>
    </section>

    <div class="container mx-auto px-4 py-12">
        <div class="grid lg:grid-cols-2 gap-12">
            <!-- Formulário de Contato -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-envelope text-dourado mr-3"></i>
                        Envie sua Mensagem
                    </h2>
                    <p class="text-gray-600">
                        Preencha o formulário abaixo que responderemos o mais breve possível
                    </p>
                </div>

                <!-- Mensagens -->
                <?php if ($erro): ?>
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <span><?= htmlspecialchars($erro) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span><?= htmlspecialchars($sucesso) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6" id="contatoForm">
                    <!-- Nome -->
                    <div>
                        <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome Completo *
                        </label>
                        <input 
                            type="text" 
                            id="nome" 
                            name="nome" 
                            required 
                            value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-dourado focus:border-transparent transition-all duration-200"
                            placeholder="Seu nome completo"
                        >
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email *
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required 
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-dourado focus:border-transparent transition-all duration-200"
                            placeholder="seu.email@exemplo.com"
                        >
                    </div>

                    <!-- Telefone -->
                    <div>
                        <label for="telefone" class="block text-sm font-medium text-gray-700 mb-2">
                            Telefone (opcional)
                        </label>
                        <input 
                            type="tel" 
                            id="telefone" 
                            name="telefone" 
                            value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-dourado focus:border-transparent transition-all duration-200"
                            placeholder="(11) 99999-9999"
                        >
                    </div>

                    <!-- Assunto -->
                    <div>
                        <label for="assunto" class="block text-sm font-medium text-gray-700 mb-2">
                            Assunto *
                        </label>
                        <select 
                            id="assunto" 
                            name="assunto" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-dourado focus:border-transparent transition-all duration-200"
                        >
                            <option value="">Selecione o assunto</option>
                            <option value="Agendamento" <?= ($_POST['assunto'] ?? '') === 'Agendamento' ? 'selected' : '' ?>>Agendamento</option>
                            <option value="Informações sobre serviços" <?= ($_POST['assunto'] ?? '') === 'Informações sobre serviços' ? 'selected' : '' ?>>Informações sobre serviços</option>
                            <option value="Preços" <?= ($_POST['assunto'] ?? '') === 'Preços' ? 'selected' : '' ?>>Preços</option>
                            <option value="Cancelamento" <?= ($_POST['assunto'] ?? '') === 'Cancelamento' ? 'selected' : '' ?>>Cancelamento</option>
                            <option value="Sugestão" <?= ($_POST['assunto'] ?? '') === 'Sugestão' ? 'selected' : '' ?>>Sugestão</option>
                            <option value="Reclamação" <?= ($_POST['assunto'] ?? '') === 'Reclamação' ? 'selected' : '' ?>>Reclamação</option>
                            <option value="Outros" <?= ($_POST['assunto'] ?? '') === 'Outros' ? 'selected' : '' ?>>Outros</option>
                        </select>
                    </div>

                    <!-- Mensagem -->
                    <div>
                        <label for="mensagem" class="block text-sm font-medium text-gray-700 mb-2">
                            Mensagem *
                        </label>
                        <textarea 
                            id="mensagem" 
                            name="mensagem" 
                            rows="5" 
                            required 
                            maxlength="1000"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-dourado focus:border-transparent transition-all duration-200 resize-none"
                            placeholder="Descreva sua mensagem com detalhes..."
                        ><?= htmlspecialchars($_POST['mensagem'] ?? '') ?></textarea>
                        <div class="text-right text-sm text-gray-500 mt-1">
                            <span id="contador">0</span>/1000 caracteres
                        </div>
                    </div>

                    <!-- Botão Enviar -->
                    <div>
                        <button 
                            type="submit" 
                            class="w-full bg-gradient-to-r from-dourado to-dourado_escuro text-black font-bold py-4 px-6 rounded-lg hover:shadow-lg transition-all duration-300 transform hover:scale-105"
                        >
                            <i class="fas fa-paper-plane mr-2"></i>
                            Enviar Mensagem
                        </button>
                    </div>
                </form>
            </div>

            <!-- Informações de Contato -->
            <div class="space-y-8">
                <!-- Informações Principais -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-info-circle text-dourado mr-3"></i>
                        Informações de Contato
                    </h2>
                    
                    <div class="space-y-6">
                        <!-- Endereço -->
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-dourado bg-opacity-20 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-dourado"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Endereço</h3>
                                <p class="text-gray-600">
                                    Rua das Palmeiras, 123<br>
                                    Centro - São Paulo, SP<br>
                                    CEP: 01234-567
                                </p>
                            </div>
                        </div>

                        <!-- Telefone -->
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-dourado bg-opacity-20 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-phone text-dourado"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Telefone</h3>
                                <p class="text-gray-600">
                                    <a href="tel:+5511999999999" class="hover:text-dourado transition-colors">
                                        (11) 99999-9999
                                    </a>
                                </p>
                                <p class="text-sm text-gray-500">WhatsApp disponível</p>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-dourado bg-opacity-20 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-envelope text-dourado"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Email</h3>
                                <p class="text-gray-600">
                                    <a href="mailto:contato@mrcarlosbarbershop.com.br" class="hover:text-dourado transition-colors">
                                        contato@mrcarlosbarbershop.com.br
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Horário de Funcionamento -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-clock text-dourado mr-3"></i>
                        Horário de Funcionamento
                    </h2>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="font-medium text-gray-900">Segunda - Sexta</span>
                            <span class="text-dourado font-semibold">09:00 - 19:00</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="font-medium text-gray-900">Sábado</span>
                            <span class="text-dourado font-semibold">09:00 - 17:00</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="font-medium text-gray-900">Domingo</span>
                            <span class="text-red-600 font-semibold">Fechado</span>
                        </div>
                    </div>
                    
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Dica:</strong> Recomendamos agendamento prévio para garantir seu horário preferido.
                        </p>
                    </div>
                </div>

                <!-- Redes Sociais -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-share-alt text-dourado mr-3"></i>
                        Siga-nos
                    </h2>
                    
                    <div class="flex space-x-4">
                        <a href="#" class="w-12 h-12 bg-blue-600 text-white rounded-lg flex items-center justify-center hover:bg-blue-700 transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg flex items-center justify-center hover:shadow-lg transition-all">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-12 h-12 bg-green-500 text-white rounded-lg flex items-center justify-center hover:bg-green-600 transition-colors">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mapa (Opcional) -->
        <div class="mt-12">
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-map text-dourado mr-3"></i>
                    Como Chegar
                </h2>
                
                <!-- Placeholder para mapa -->
                <div class="bg-gray-200 rounded-lg h-64 flex items-center justify-center">
                    <div class="text-center text-gray-500">
                        <i class="fas fa-map-marked-alt text-4xl mb-4"></i>
                        <p class="text-lg font-semibold">Mapa Interativo</p>
                        <p class="text-sm">Rua das Palmeiras, 123 - Centro, São Paulo</p>
                        <a href="https://maps.google.com/?q=Rua+das+Palmeiras,+123,+Centro,+São+Paulo" 
                           target="_blank" 
                           class="inline-block mt-3 bg-dourado text-black px-4 py-2 rounded-lg hover:bg-dourado_escuro transition-colors">
                            Ver no Google Maps
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts específicos da página -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Contador de caracteres para textarea
    const mensagemTextarea = document.getElementById('mensagem');
    const contador = document.getElementById('contador');
    
    function atualizarContador() {
        const length = mensagemTextarea.value.length;
        contador.textContent = length;
        
        if (length > 900) {
            contador.classList.add('text-orange-500');
            contador.classList.remove('text-gray-500', 'text-red-500');
        } else if (length > 1000) {
            contador.classList.add('text-red-500');
            contador.classList.remove('text-gray-500', 'text-orange-500');
        } else {
            contador.classList.add('text-gray-500');
            contador.classList.remove('text-orange-500', 'text-red-500');
        }
    }
    
    mensagemTextarea.addEventListener('input', atualizarContador);
    atualizarContador(); // Atualizar contador inicial
    
    // Formatação automática do telefone
    const telefoneInput = document.getElementById('telefone');
    telefoneInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        
        if (value.length >= 11) {
            value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else if (value.length >= 7) {
            value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
        } else if (value.length >= 3) {
            value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
        }
        
        this.value = value;
    });
    
    // Validação do formulário
    const form = document.getElementById('contatoForm');
    form.addEventListener('submit', function(e) {
        const mensagem = mensagemTextarea.value.trim();
        
        if (mensagem.length < 10) {
            e.preventDefault();
            alert('A mensagem deve ter pelo menos 10 caracteres.');
            mensagemTextarea.focus();
            return false;
        }
        
        if (mensagem.length > 1000) {
            e.preventDefault();
            alert('A mensagem não pode exceder 1000 caracteres.');
            mensagemTextarea.focus();
            return false;
        }
        
        // Confirmar envio
        const confirmacao = confirm('Deseja enviar esta mensagem?');
        if (!confirmacao) {
            e.preventDefault();
            return false;
        }
        
        // Desabilitar botão para evitar duplo envio
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
    });
    
    // Auto-dismiss mensagens após 5 segundos
    const messages = document.querySelectorAll('.bg-red-50, .bg-green-50');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 300);
        }, 5000);
    });
    
    // Limpar formulário se mensagem foi enviada com sucesso
    <?php if ($sucesso): ?>
    setTimeout(() => {
        document.getElementById('contatoForm').reset();
        atualizarContador();
    }, 2000);
    <?php endif; ?>
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>