# 🚀 Guia de Publicação no GitHub

Este guia irá ajudá-lo a publicar o projeto Mr. Carlos Barbershop no GitHub passo a passo.

## 📋 Pré-requisitos

1. **Conta no GitHub**: [Criar conta](https://github.com/join)
2. **Git instalado**: [Download Git](https://git-scm.com/downloads)
3. **Projeto funcionando localmente**

## 🔧 Passo 1: Configurar Git (Primeira vez)

```bash
# Configurar nome e email
git config --global user.name "Seu Nome"
git config --global user.email "seu-email@exemplo.com"
```

## 📁 Passo 2: Inicializar Repositório Local

```bash
# Navegar para o diretório do projeto
cd c:\wamp64\www\mr-carlos-barbershop

# Inicializar repositório Git
git init

# Adicionar todos os arquivos
git add .

# Fazer primeiro commit
git commit -m "🎉 Initial commit: Mr. Carlos Barbershop Sistema de Agendamento

- Sistema completo de agendamento para barbearia
- Design premium com TailwindCSS
- Dashboard para clientes, barbeiros e admin
- Sistema de autenticação seguro
- Base de dados MySQL configurada"
```

## 🌐 Passo 3: Criar Repositório no GitHub

1. Acesse [GitHub.com](https://github.com)
2. Clique no botão **"New"** ou **"+"** → **"New repository"**
3. **Nome do repositório**: `mr-carlos-barbershop`
4. **Descrição**: `Sistema completo de gestão para barbearias com design moderno e funcionalidades avançadas`
5. **Visibilidade**: Escolha **Public** ou **Private**
6. **NÃO** marque "Add a README file" (já temos um)
7. Clique em **"Create repository"**

## 🔗 Passo 4: Conectar Local com GitHub

```bash
# Adicionar origem remota (substitua SEU-USUARIO pelo seu username)
git remote add origin https://github.com/SEU-USUARIO/mr-carlos-barbershop.git

# Verificar se foi adicionado
git remote -v

# Fazer push do código para GitHub
git branch -M main
git push -u origin main
```

## 📸 Passo 5: Adicionar Screenshots (Opcional)

1. Criar pasta `screenshots/` no projeto
2. Adicionar capturas de tela das principais páginas
3. Atualizar README.md com links corretos

```bash
# Criar pasta para screenshots
mkdir screenshots

# Após adicionar as imagens
git add screenshots/
git commit -m "📸 Add screenshots to README"
git push
```

## 🏷️ Passo 6: Criar Release

1. No GitHub, vá para **"Releases"** → **"Create a new release"**
2. **Tag version**: `v1.0.0`
3. **Release title**: `🎉 Mr. Carlos Barbershop v1.0.0`
4. **Description**:
```markdown
## 🚀 Primeira versão do Mr. Carlos Barbershop

### ✨ Funcionalidades
- Sistema completo de agendamento online
- Dashboard premium para clientes, barbeiros e admin  
- Design responsivo com TailwindCSS
- Sistema de autenticação seguro
- Gestão completa de serviços e horários

### 🛠️ Tecnologias
- PHP 8.x
- MySQL 5.7+
- TailwindCSS 3.x
- FullCalendar 6.x
- Font Awesome 6.4.0

### 🔐 Contas de Teste
- **Admin:** admin@mrcarlos.pt | Admin123!
- **Barbeiro:** carlos@mrcarlos.pt | Carlos123!
- **Cliente:** cliente@teste.pt | Cliente123!
```

## 🔄 Comandos para Atualizações Futuras

```bash
# Adicionar novas mudanças
git add .

# Commit com mensagem descritiva
git commit -m "✨ Descrição da nova funcionalidade"

# Enviar para GitHub
git push
```

## 📊 Configurações Recomendadas no GitHub

### 🛡️ Proteção da Branch Main
1. Settings → Branches → Add rule
2. Branch name: `main`
3. ✅ Require pull request reviews
4. ✅ Dismiss stale PR approvals

### 📋 Templates
Criar `.github/` com templates para:
- `PULL_REQUEST_TEMPLATE.md`
- `ISSUE_TEMPLATE.md`

### 🏷️ Topics
Adicionar topics ao repositório:
- `php`
- `mysql`
- `barbershop`
- `booking-system`
- `tailwindcss`
- `fullcalendar`

## 🎯 URLs Importantes

Após publicação, seu projeto estará disponível em:
- **Repositório**: `https://github.com/SEU-USUARIO/mr-carlos-barbershop`
- **Clone HTTPS**: `https://github.com/SEU-USUARIO/mr-carlos-barbershop.git`
- **Clone SSH**: `git@github.com:SEU-USUARIO/mr-carlos-barbershop.git`

## 🆘 Resolução de Problemas

### Erro de Autenticação
```bash
# Usar Personal Access Token em vez de password
git remote set-url origin https://TOKEN@github.com/SEU-USUARIO/mr-carlos-barbershop.git
```

### Arquivos Muito Grandes
```bash
# Verificar tamanho dos arquivos
git ls-files --sort-by=-size | head -10

# Remover arquivos grandes do histórico
git filter-branch --force --index-filter 'git rm --cached --ignore-unmatch arquivo-grande.zip' --prune-empty --tag-name-filter cat -- --all
```

### Resolver Conflitos
```bash
# Ver status
git status

# Resolver conflitos manualmente nos arquivos indicados
# Depois fazer commit
git add .
git commit -m "🔧 Resolve merge conflicts"
```

---

## ✅ Checklist Final

- [ ] Repositório criado no GitHub
- [ ] Código enviado com sucesso  
- [ ] README.md formatado corretamente
- [ ] .gitignore configurado
- [ ] LICENSE adicionado
- [ ] Screenshots adicionadas (opcional)
- [ ] Release v1.0.0 criada
- [ ] Topics configurados
- [ ] Descrição do repositório preenchida

🎉 **Parabéns! Seu projeto está agora no GitHub e pronto para ser compartilhado com o mundo!**