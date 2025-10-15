    </main>

    <!-- Footer -->
    <footer class="bg-black text-white py-12 mt-20" role="contentinfo">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <!-- Sobre a Barbearia -->
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-cut text-barbershop-gold text-2xl mr-3" aria-hidden="true"></i>
                        <h3 class="text-xl font-bold">Mr. Carlos</h3>
                    </div>
                    <p class="text-gray-300 mb-4">
                        Tradição e qualidade em cuidados masculinos desde 1985. 
                        A sua confiança é o nosso orgulho.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-300 hover:text-barbershop-gold transition-colors duration-300" 
                           aria-label="Facebook">
                            <i class="fab fa-facebook-f text-xl" aria-hidden="true"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-barbershop-gold transition-colors duration-300" 
                           aria-label="Instagram">
                            <i class="fab fa-instagram text-xl" aria-hidden="true"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-barbershop-gold transition-colors duration-300" 
                           aria-label="WhatsApp">
                            <i class="fab fa-whatsapp text-xl" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>

                <!-- Links Rápidos -->
                <div>
                    <h3 class="text-lg font-semibold mb-4 text-barbershop-gold">Links Rápidos</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="<?php echo get_base_url(); ?>" 
                               class="text-gray-300 hover:text-white transition-colors duration-300">
                                Início
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo get_base_url('pages/servicos.php'); ?>" 
                               class="text-gray-300 hover:text-white transition-colors duration-300">
                                Serviços
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo get_base_url('pages/agendar.php'); ?>" 
                               class="text-gray-300 hover:text-white transition-colors duration-300">
                                Agendar Corte
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo get_base_url('pages/sobre.php'); ?>" 
                               class="text-gray-300 hover:text-white transition-colors duration-300">
                                Sobre Nós
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo get_base_url('pages/contacto.php'); ?>" 
                               class="text-gray-300 hover:text-white transition-colors duration-300">
                                Contacto
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Serviços -->
                <div>
                    <h3 class="text-lg font-semibold mb-4 text-barbershop-gold">Serviços</h3>
                    <ul class="space-y-2">
                        <li class="text-gray-300">Corte Clássico</li>
                        <li class="text-gray-300">Corte Moderno</li>
                        <li class="text-gray-300">Barba Completa</li>
                        <li class="text-gray-300">Bigode</li>
                        <li class="text-gray-300">Tratamentos Capilares</li>
                    </ul>
                </div>

                <!-- Contacto -->
                <div>
                    <h3 class="text-lg font-semibold mb-4 text-barbershop-gold">Contacto</h3>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-barbershop-gold mr-3" aria-hidden="true"></i>
                            <div>
                                <p class="text-gray-300 text-sm">Rua da Barbearia, 123</p>
                                <p class="text-gray-300 text-sm">4000-000 Porto</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <i class="fas fa-phone text-barbershop-gold mr-3" aria-hidden="true"></i>
                            <a href="tel:+351123456789" 
                               class="text-gray-300 hover:text-white transition-colors duration-300">
                                +351 123 456 789
                            </a>
                        </div>
                        
                        <div class="flex items-center">
                            <i class="fas fa-envelope text-barbershop-gold mr-3" aria-hidden="true"></i>
                            <a href="mailto:info@mrcarlosbarbershop.pt" 
                               class="text-gray-300 hover:text-white transition-colors duration-300">
                                info@mrcarlosbarbershop.pt
                            </a>
                        </div>

                        <div class="mt-4">
                            <h4 class="text-sm font-semibold text-barbershop-gold mb-2">Horário</h4>
                            <div class="text-gray-300 text-sm space-y-1">
                                <p>Segunda - Sexta: 09:00 - 19:00</p>
                                <p>Sábado: 09:00 - 17:00</p>
                                <p>Domingo: Fechado</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Separador -->
            <div class="border-t border-gray-800 mt-8 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-gray-400 text-sm mb-4 md:mb-0">
                        <p>&copy; <?php echo date('Y'); ?> Mr. Carlos Barbershop. Todos os direitos reservados.</p>
                    </div>
                    
                    <div class="flex space-x-6 text-sm">
                        <a href="<?php echo get_base_url('pages/privacy.php'); ?>" 
                           class="text-gray-400 hover:text-white transition-colors duration-300">
                            Política de Privacidade
                        </a>
                        <a href="<?php echo get_base_url('pages/terms.php'); ?>" 
                           class="text-gray-400 hover:text-white transition-colors duration-300">
                            Termos de Uso
                        </a>
                        <a href="<?php echo get_base_url('pages/cookies.php'); ?>" 
                           class="text-gray-400 hover:text-white transition-colors duration-300">
                            Política de Cookies
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    
    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.8/locales/pt.global.min.js'></script>
    
    <!-- Script principal -->
    <script src="<?php echo get_base_url('assets/js/script.js'); ?>"></script>

    <script>
        // Menu mobile toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    const isOpen = !mobileMenu.classList.contains('hidden');
                    
                    if (isOpen) {
                        mobileMenu.classList.add('hidden');
                        mobileMenuButton.setAttribute('aria-expanded', 'false');
                        mobileMenuButton.querySelector('i').classList.remove('fa-times');
                        mobileMenuButton.querySelector('i').classList.add('fa-bars');
                    } else {
                        mobileMenu.classList.remove('hidden');
                        mobileMenuButton.setAttribute('aria-expanded', 'true');
                        mobileMenuButton.querySelector('i').classList.remove('fa-bars');
                        mobileMenuButton.querySelector('i').classList.add('fa-times');
                    }
                });
            }

            // Fechar menu mobile quando clicar num link
            const mobileLinks = mobileMenu?.querySelectorAll('a');
            mobileLinks?.forEach(link => {
                link.addEventListener('click', function() {
                    mobileMenu.classList.add('hidden');
                    mobileMenuButton.setAttribute('aria-expanded', 'false');
                    mobileMenuButton.querySelector('i').classList.remove('fa-times');
                    mobileMenuButton.querySelector('i').classList.add('fa-bars');
                });
            });

            // Scroll suave para âncoras
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
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
        });

        // Configurações globais para AJAX
        window.barbershopConfig = {
            baseUrl: '<?php echo get_base_url(); ?>',
            csrfToken: '<?php echo $_SESSION['csrf_token'] ?? ''; ?>'
        };
    </script>
</body>
</html>