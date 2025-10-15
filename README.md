# Mr. Carlos Barbershop - Sistema de Agendamento 💈

![Status](https://img.shields.io/badge/status-active-brightgreen.svg)
![PHP](https://img.shields.io/badge/php-%3E%3D8.0-blue.svg)
![MySQL](https://img.shields.io/badge/mysql-%3E%3D5.7-orange.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

Sistema completo de gestão para barbearias com design moderno e funcionalidades avançadas, desenvolvido em PHP 8.x com MySQL.

## ✨ Funcionalidades Principais

- 🏠 **Homepage Premium** - Design moderno com gradientes e animações suaves
- 🔐 **Login Inteligente** - Sistema unificado com detecção automática de tipo de usuário
- 📅 **Agendamento Avançado** - Interface visual intuitiva com seleção de horários em tempo real
- 👤 **Perfil de Cliente** - Dashboard completo com histórico de atendimentos
- 💼 **Dashboard Barbeiro** - Painel profissional com calendário FullCalendar integrado
- 🛡️ **Painel Admin** - Controle total com estatísticas e relatórios detalhados
- 📋 **Catálogo de Serviços** - Gestão completa com categorias e preços dinâmicos
- 📧 **Sistema de Email** - Confirmações automáticas via PHPMailer
- 📱 **Design Responsivo** - Mobile-first com TailwindCSS e componentes interativos
- 🔒 **Segurança Avançada** - CSRF protection, rate limiting, password hashing

## � Screenshots

<details>
<summary>🖼️ Ver capturas de tela</summary>

### Homepage Premium
![Homepage](screenshots/homepage.png)

### Dashboard do Cliente
![Cliente Dashboard](screenshots/cliente-dashboard.png)

### Painel do Barbeiro
![Barbeiro Dashboard](screenshots/barbeiro-dashboard.png)

### Painel Administrativo
![Admin Panel](screenshots/admin-panel.png)

</details>

## 🛠️ Stack Tecnológico

### Backend
- **PHP 8.x** - Linguagem principal com recursos modernos
- **MySQL 5.7+** - Base de dados com charset UTF8MB4
- **PDO** - Camada de abstração para base de dados
- **SecurityManager** - Sistema de segurança personalizado

### Frontend
- **TailwindCSS 3.x** - Framework CSS moderno
- **Font Awesome 6.4.0** - Ícones profissionais
- **Google Fonts (Poppins)** - Tipografia moderna
- **JavaScript ES6+** - Interatividade avançada

### Bibliotecas e Ferramentas
- **FullCalendar 6.x** - Calendário interativo do barbeiro
- **Flatpickr** - Seletor de data avançado
- **PHPMailer** - Envio de emails profissionais
- **jQuery** - Operações AJAX e DOM

## 📋 Requisitos do Sistema

- PHP >= 8.0
- MySQL >= 5.7 ou MariaDB >= 10.2
- Servidor web (Apache/Nginx)
- Composer
- Extensões PHP: mysqli, mbstring, json, openssl

## 🚀 Instalação e Configuração

### 1. Clonar o Repositório
```bash
git clone https://github.com/username/mr-carlos-barbershop.git
cd mr-carlos-barbershop
```

### 2. Instalar Dependências
```bash
composer install
```

### 3. Configurar Base de Dados

#### Opção A: Configuração Automática (Recomendado)
```bash
# Executar script de reparação da base de dados
php reparar_database.php
```

Este script irá:
- Criar todas as tabelas necessárias
- Configurar contas de teste para todos os tipos de usuário
- Validar a estrutura da base de dados

#### Opção B: Configuração Manual
```sql
-- Criar base de dados
CREATE DATABASE mr_carlos_barbershop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Importar schema (se disponível)
mysql -u root -p mr_carlos_barbershop < database/schema.sql
```

### 4. Configurar Aplicação
```bash
# Copiar ficheiro de configuração
cp config/config.example.php config/config.php

# Editar credenciais da base de dados em config/config.php
```

### 5. Configurar Permissões
```bash
# Linux/Mac
chmod 755 logs/
chmod 755 uploads/
```

### 6. Configurar Email (Opcional)
Criar ficheiro `.env` na raiz:
```env
SMTP_USERNAME=seu-email@gmail.com
SMTP_PASSWORD=sua-app-password
```

## � Início Rápido

```bash
# 1. Clonar repositório
git clone https://github.com/seu-usuario/mr-carlos-barbershop.git
cd mr-carlos-barbershop

# 2. Configurar base de dados
# Edite config/database.php com suas credenciais
php reparar_database.php

# 3. Iniciar servidor local
php -S localhost:8000

# 4. Acesse http://localhost:8000
```

## �📁 Estrutura do Projeto

```
mr-carlos-barbershop/
├── 📂 admin/              # Painel administrativo
│   ├── dashboard.php      # Dashboard principal
│   ├── barbeiros.php      # Gestão de barbeiros
│   └── servicos.php       # Gestão de serviços
├── 📂 api/                # Endpoints AJAX
│   ├── get_availability.php
│   └── create_booking.php
├── 📂 assets/             # Recursos estáticos
│   ├── css/              # Estilos personalizados
│   ├── js/               # Scripts JavaScript
│   └── images/           # Imagens do sistema
├── 📂 barbeiro/           # Dashboard do barbeiro
├── 📂 config/             # Configurações do sistema
├── 📂 includes/           # Componentes partilhados
│   ├── header.php        # Cabeçalho global
│   ├── footer.php        # Rodapé global
│   └── SecurityManager.php
├── 📂 pages/              # Páginas principais
│   ├── cliente/          # Área do cliente
│   ├── login.php         # Login unificado
│   └── servicos.php      # Catálogo de serviços
├── 📄 index.php           # Homepage premium
├── 📄 reparar_database.php # Script de configuração
└── 📄 verificar_barbeiros.php # Validação da BD
```

## 🔐 Contas de Teste

Após executar o script `reparar_database.php`, as seguintes contas estarão disponíveis:

### 👨‍💼 Administrador
- **Email:** admin@mrcarlos.pt
- **Password:** Admin123!
- **Acesso:** Painel administrativo completo

### ✂️ Barbeiros
- **Carlos Silva:** carlos@mrcarlos.pt | Carlos123!
- **João Santos:** joao@mrcarlos.pt | Joao123!
- **Miguel Costa:** miguel@mrcarlos.pt | Miguel123!

### 👥 Cliente de Teste
- **Email:** cliente@teste.pt
- **Password:** Cliente123!

> ⚠️ **Importante:** Altere todas as passwords após o primeiro login em produção!

## 📊 Endpoints da API

| Endpoint | Método | Descrição |
|----------|--------|-----------|
| `/api/get_availability.php` | POST | Obter horários disponíveis |
| `/api/create_booking.php` | POST | Criar agendamento |
| `/api/barbeiro_events.php` | GET | Eventos do calendário |
| `/api/barbeiro_toggle_block.php` | POST | Bloquear/desbloquear horários |

## 🧪 Testes

```bash
# Testar conexão à base de dados
php tools/test_db.php

# Testar envio de email
php tools/test_email.php

# Executar limpeza automática
php tools/cron_cleanup.php
```

## 🔒 Segurança

- ✅ Prepared Statements para todas as queries
- ✅ Password hashing com `password_hash()`
- ✅ Proteção CSRF em formulários
- ✅ Rate limiting para login
- ✅ Validação server-side e client-side
- ✅ Escape de output com `htmlspecialchars()`
- ✅ Sessões seguras com regeneração de ID

## 🎨 Paleta de Cores

- **Branco**: #FFFFFF
- **Preto**: #000000
- **Dourado**: #C9A227
- **Gradientes**: Combinações modernas das cores principais

## 📱 Compatibilidade

- ✅ Design responsivo (mobile-first)
- ✅ Compatibilidade com todos os browsers modernos
- ✅ Acessibilidade WCAG AA
- ✅ PWA-ready (Progressive Web App)

## 🤝 Contribuição

1. Fork o projeto
2. Criar branch para feature (`git checkout -b feature/nova-funcionalidade`)
3. Commit das alterações (`git commit -am 'Adicionar nova funcionalidade'`)
4. Push para branch (`git push origin feature/nova-funcionalidade`)
5. Criar Pull Request

## 📞 Suporte

Para suporte técnico, contactar:
- Email: admin@mrcarlosbarbershop.pt
- Telefone: +351 123 456 789

## 🎯 Demonstração de Funcionalidades

### 🎨 Design Premium
- Gradientes modernos e animações suaves
- Paleta de cores profissional (Preto, Branco, Dourado)
- Componentes interativos com hover effects
- Layout responsivo com breakpoints otimizados

### 🔐 Sistema de Autenticação
- Login unificado com detecção automática de perfil
- Múltiplos níveis de acesso (Cliente/Barbeiro/Admin)
- Sessões seguras com regeneração de ID
- Rate limiting para prevenção de ataques

### 📊 Dashboard Inteligente
- Estatísticas em tempo real
- Calendário interativo com FullCalendar
- Gestão visual de disponibilidade
- Relatórios detalhados e exportáveis

## 🏆 Destaques Técnicos

- ✨ **Arquitetura MVC** - Separação clara de responsabilidades
- 🛡️ **SecurityManager** - Sistema de segurança centralizado
- 📱 **Progressive Enhancement** - Funciona sem JavaScript
- ⚡ **Performance Otimizada** - Queries eficientes e caching
- 🌐 **Internacionalização** - Suporte para múltiplos idiomas
- 📈 **Escalabilidade** - Preparado para crescimento

## 🤝 Contribuição

Contribuições são bem-vindas! Para contribuir:

1. 🍴 Fork o projeto
2. 🌟 Crie sua feature branch (`git checkout -b feature/AmazingFeature`)
3. 📝 Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. 📤 Push para a branch (`git push origin feature/AmazingFeature`)
5. 🔄 Abra um Pull Request

## 📞 Suporte e Contato

- 📧 **Email:** admin@mrcarlosbarbershop.pt
- 📱 **Telefone:** +351 123 456 789
- 🌐 **Website:** [mrcarlosbarbershop.pt](http://mrcarlosbarbershop.pt)

## 📄 Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## ⭐ Agradecimentos

- Comunidade PHP pela documentação excelente
- TailwindCSS pela framework CSS incrível
- Font Awesome pelos ícones profissionais
- Todos os contribuidores e testadores

---

<div align="center">

**Mr. Carlos Barbershop System v1.0.0**  
*Desenvolvido com ❤️ para a comunidade de barbeiros*

[![GitHub stars](https://img.shields.io/github/stars/seu-usuario/mr-carlos-barbershop.svg?style=social&label=Star)](https://github.com/seu-usuario/mr-carlos-barbershop)
[![GitHub forks](https://img.shields.io/github/forks/seu-usuario/mr-carlos-barbershop.svg?style=social&label=Fork)](https://github.com/seu-usuario/mr-carlos-barbershop/fork)

</div>