/**
 * Script principal - Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: JavaScript personalizado e funcionalidades interativas
 */

'use strict';

// Configuração global da aplicação
window.Barbershop = window.Barbershop || {
    config: window.barbershopConfig || {},
    utils: {},
    forms: {},
    calendar: {},
    notifications: {}
};

// ==================================================
// UTILITÁRIOS GERAIS
// ==================================================

Barbershop.utils = {
    /**
     * Fazer pedido AJAX com configuração padrão
     */
    ajax: function(url, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        // Adicionar token CSRF se disponível
        if (Barbershop.config.csrfToken) {
            defaultOptions.headers['X-CSRF-Token'] = Barbershop.config.csrfToken;
        }

        const finalOptions = { ...defaultOptions, ...options };

        return fetch(url, finalOptions)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .catch(error => {
                console.error('Erro na requisição AJAX:', error);
                Barbershop.notifications.show('Erro de conexão. Tente novamente.', 'error');
                throw error;
            });
    },

    /**
     * Formatear preço para euros
     */
    formatPrice: function(price) {
        return new Intl.NumberFormat('pt-PT', {
            style: 'currency',
            currency: 'EUR'
        }).format(price);
    },

    /**
     * Formatear data para português
     */
    formatDate: function(date, options = {}) {
        const defaultOptions = {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            weekday: 'long'
        };

        const finalOptions = { ...defaultOptions, ...options };

        return new Intl.DateTimeFormat('pt-PT', finalOptions).format(new Date(date));
    },

    /**
     * Debounce para limitar execução de funções
     */
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    /**
     * Throttle para limitar execução de funções
     */
    throttle: function(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },

    /**
     * Validar email
     */
    validateEmail: function(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    },

    /**
     * Validar telefone português
     */
    validatePhone: function(phone) {
        const cleaned = phone.replace(/[^0-9+]/g, '');
        const regex = /^(\+351|00351)?[0-9]{9}$/;
        return regex.test(cleaned);
    }
};

// ==================================================
// SISTEMA DE NOTIFICAÇÕES
// ==================================================

Barbershop.notifications = {
    container: null,

    init: function() {
        // Criar container se não existir
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'notifications-container';
            this.container.className = 'fixed top-4 right-4 z-50 space-y-2';
            document.body.appendChild(this.container);
        }
    },

    show: function(message, type = 'info', duration = 5000) {
        this.init();

        const notification = document.createElement('div');
        const types = {
            success: 'bg-green-500 text-white',
            error: 'bg-red-500 text-white',
            warning: 'bg-yellow-500 text-black',
            info: 'bg-blue-500 text-white'
        };

        const icons = {
            success: '✓',
            error: '✗',
            warning: '⚠',
            info: 'ℹ'
        };

        notification.className = `${types[type]} px-6 py-4 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 max-w-sm`;
        notification.innerHTML = `
            <div class="flex items-center">
                <span class="text-lg mr-3">${icons[type]}</span>
                <span class="flex-1">${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-3 text-lg opacity-70 hover:opacity-100">×</button>
            </div>
        `;

        this.container.appendChild(notification);

        // Animar entrada
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 10);

        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 300);
            }, duration);
        }
    }
};

// ==================================================
// MANIPULAÇÃO DE FORMULÁRIOS
// ==================================================

Barbershop.forms = {
    /**
     * Configurar validação em tempo real
     */
    setupValidation: function(form) {
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', Barbershop.utils.debounce(() => {
                this.clearFieldError(input);
            }, 300));
        });

        form.addEventListener('submit', (e) => {
            if (!this.validateForm(form)) {
                e.preventDefault();
            }
        });
    },

    /**
     * Validar campo individual
     */
    validateField: function(field) {
        const value = field.value.trim();
        let isValid = true;
        let message = '';

        // Remover erro anterior
        this.clearFieldError(field);

        // Campos obrigatórios
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            message = 'Este campo é obrigatório.';
        }

        // Validação por tipo
        if (value && field.type === 'email' && !Barbershop.utils.validateEmail(value)) {
            isValid = false;
            message = 'Email inválido.';
        }

        if (value && field.name === 'telefone' && !Barbershop.utils.validatePhone(value)) {
            isValid = false;
            message = 'Telefone inválido.';
        }

        if (value && field.type === 'password' && value.length < 8) {
            isValid = false;
            message = 'Password deve ter pelo menos 8 caracteres.';
        }

        // Confirmar password
        if (field.name === 'confirm_password') {
            const passwordField = field.form.querySelector('input[name="password"]');
            if (passwordField && value !== passwordField.value) {
                isValid = false;
                message = 'Passwords não coincidem.';
            }
        }

        // Mostrar erro se inválido
        if (!isValid) {
            this.showFieldError(field, message);
        }

        return isValid;
    },

    /**
     * Validar formulário completo
     */
    validateForm: function(form) {
        const fields = form.querySelectorAll('input, select, textarea');
        let isValid = true;

        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    },

    /**
     * Mostrar erro do campo
     */
    showFieldError: function(field, message) {
        field.classList.add('error');
        
        let errorElement = field.parentElement.querySelector('.form-error');
        if (!errorElement) {
            errorElement = document.createElement('span');
            errorElement.className = 'form-error';
            field.parentElement.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
    },

    /**
     * Limpar erro do campo
     */
    clearFieldError: function(field) {
        field.classList.remove('error');
        const errorElement = field.parentElement.querySelector('.form-error');
        if (errorElement) {
            errorElement.remove();
        }
    },

    /**
     * Submeter formulário via AJAX
     */
    submitAjax: function(form, onSuccess, onError) {
        if (!this.validateForm(form)) {
            return;
        }

        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;

        // Estado de loading
        submitButton.disabled = true;
        submitButton.textContent = 'A processar...';
        submitButton.classList.add('loading');

        fetch(form.action, {
            method: form.method || 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (onSuccess) onSuccess(data);
                Barbershop.notifications.show(data.message || 'Sucesso!', 'success');
            } else {
                if (onError) onError(data);
                Barbershop.notifications.show(data.message || 'Erro ao processar.', 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            if (onError) onError(error);
            Barbershop.notifications.show('Erro de conexão.', 'error');
        })
        .finally(() => {
            // Restaurar botão
            submitButton.disabled = false;
            submitButton.textContent = originalText;
            submitButton.classList.remove('loading');
        });
    }
};

// ==================================================
// FUNCIONALIDADES DO CALENDÁRIO
// ==================================================

Barbershop.calendar = {
    /**
     * Inicializar Flatpickr para seleção de data
     */
    initDatePicker: function(selector, options = {}) {
        const defaultOptions = {
            locale: 'pt',
            dateFormat: 'Y-m-d',
            minDate: 'today',
            maxDate: new Date().fp_incr(60), // 60 dias a partir de hoje
            disable: [
                function(date) {
                    // Desabilitar domingos (0 = domingo)
                    return date.getDay() === 0;
                }
            ]
        };

        return flatpickr(selector, { ...defaultOptions, ...options });
    },

    /**
     * Inicializar FullCalendar
     */
    initFullCalendar: function(element, options = {}) {
        const defaultOptions = {
            locale: 'pt',
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            height: 'auto',
            events: [],
            eventClick: function(info) {
                // Callback padrão para clique em evento
                console.log('Evento clicado:', info.event);
            }
        };

        return new FullCalendar.Calendar(element, { ...defaultOptions, ...options });
    }
};

// ==================================================
// INICIALIZAÇÃO DA APLICAÇÃO
// ==================================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Mr. Carlos Barbershop - Sistema iniciado');

    // Inicializar notificações
    Barbershop.notifications.init();

    // Configurar validação em todos os formulários
    document.querySelectorAll('form').forEach(form => {
        if (!form.hasAttribute('novalidate')) {
            Barbershop.forms.setupValidation(form);
        }
    });

    // Inicializar date pickers
    document.querySelectorAll('.date-picker').forEach(input => {
        Barbershop.calendar.initDatePicker(input);
    });

    // Smooth scroll para links âncora
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
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

    // Lazy loading para imagens
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }

    // Auto-hide mensagens após 5 segundos
    document.querySelectorAll('.alert, .message').forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 300);
        }, 5000);
    });
});

// ==================================================
// EXPORTAR PARA ESCOPO GLOBAL
// ==================================================

window.Barbershop = Barbershop;