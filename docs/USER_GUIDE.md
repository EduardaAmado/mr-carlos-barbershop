# Guia do Usuário - Mr. Carlos Barbershop

## Índice
1. [Visão Geral do Sistema](#visão-geral-do-sistema)
2. [Primeiros Passos](#primeiros-passos)
3. [Para Clientes](#para-clientes)
4. [Para Barbeiros](#para-barbeiros)
5. [Para Administradores](#para-administradores)
6. [Funcionalidades Avançadas](#funcionalidades-avançadas)
7. [Perguntas Frequentes](#perguntas-frequentes)
8. [Suporte](#suporte)

---

## Visão Geral do Sistema

O **Mr. Carlos Barbershop** é um sistema completo de gerenciamento para barbearias que oferece:

### 🎯 **Para Clientes**
- Agendamento online intuitivo
- Visualização de serviços e preços
- Histórico de atendimentos
- Lembretes por email
- Perfil personalizado

### ✂️ **Para Barbeiros**
- Dashboard com agenda visual
- Gerenciamento de bloqueios de horário
- Estatísticas de atendimento
- Controle de status (disponível/indisponível)

### 👨‍💼 **Para Administradores**
- Gestão completa de barbeiros e serviços
- Relatórios detalhados de faturamento
- Monitoramento de segurança
- Ferramentas de backup e manutenção

---

## Primeiros Passos

### Acessando o Sistema

**URL Principal**: `https://seu-dominio.com`

**Páginas de Login**:
- Clientes: `https://seu-dominio.com/login`
- Barbeiros: `https://seu-dominio.com/barbeiro`
- Administradores: `https://seu-dominio.com/admin`

### Navegação Principal

```
🏠 Início          → Página principal com informações da barbearia
📋 Serviços        → Catálogo completo de serviços e preços
ℹ️ Sobre Nós       → História e valores da barbearia
📞 Contato         → Formulário de contato e localização
📅 Agendar         → Sistema de agendamento (requer login)
👤 Perfil          → Área do cliente (após login)
```

---

## Para Clientes

### 1. Criando uma Conta

1. **Acesse**: `https://seu-dominio.com/register`
2. **Preencha os dados**:
   - Nome completo
   - Email válido
   - Telefone com DDD
   - Senha segura (8+ caracteres)
3. **Confirme** o registro via email

### 2. Fazendo um Agendamento

#### Passo 1: Selecionar Serviço
![Agendamento - Passo 1](images/agendamento-1.png)
- Escolha o serviço desejado
- Visualize preço e duração
- Combine múltiplos serviços se necessário

#### Passo 2: Escolher Barbeiro
![Agendamento - Passo 2](images/agendamento-2.png)
- Selecione seu barbeiro preferido
- Veja especialidades de cada profissional
- Ou deixe o sistema escolher automaticamente

#### Passo 3: Data e Horário
![Agendamento - Passo 3](images/agendamento-3.png)
- Calendário interativo
- Apenas horários disponíveis são mostrados
- Horários em vermelho = ocupados
- Horários em verde = disponíveis

#### Passo 4: Confirmação
![Agendamento - Passo 4](images/agendamento-4.png)
- Revise todos os dados
- Confirme o agendamento
- Receba confirmação por email

### 3. Gerenciando Agendamentos

**No seu perfil** (`/perfil`):

#### Agendamentos Ativos
```
📅 15/10/2025 - 14:30
✂️ Corte Clássico + Barba Completa
👨 Carlos (Barbeiro)
💰 R$ 65,00
🔄 [Remarcar] ❌ [Cancelar]
```

#### Histórico
- Visualize todos os atendimentos anteriores
- Avalie o serviço prestado
- Agende novamente com um clique

#### Opções Disponíveis
- **Remarcar**: Alterar data/horário (até 2h antes)
- **Cancelar**: Cancelar agendamento (até 2h antes)
- **Reagendar**: Repetir agendamento anterior

### 4. Notificações por Email

Você receberá emails automáticos para:

#### 📧 **Confirmação de Agendamento**
```
Assunto: Agendamento Confirmado - Mr. Carlos Barbershop

Olá João,

Seu agendamento foi confirmado!

📅 Data: 15/10/2025
🕐 Horário: 14:30
✂️ Serviço: Corte Clássico
👨 Barbeiro: Carlos
💰 Valor: R$ 45,00

Endereço: Rua das Flores, 123 - Centro
```

#### ⏰ **Lembrete (24h antes)**
```
Assunto: Lembrete - Agendamento Amanhã

Olá João,

Lembramos que você tem um agendamento amanhã:
📅 15/10/2025 às 14:30

Para remarcar ou cancelar, acesse seu perfil.
```

#### ✅ **Agradecimento (após atendimento)**
```
Assunto: Obrigado pela visita!

Olá João,

Esperamos que tenha gostado do seu atendimento.
Avalie nosso serviço e agende novamente quando quiser!
```

---

## Para Barbeiros

### 1. Acessando o Dashboard

1. **Acesse**: `https://seu-dominio.com/barbeiro`
2. **Faça login** com suas credenciais
3. **Dashboard** será exibido automaticamente

### 2. Visualizando a Agenda

#### Calendário Principal
![Dashboard Barbeiro](images/barbeiro-dashboard.png)

**Legenda de Cores**:
- 🟢 **Verde**: Agendamento confirmado
- 🟡 **Amarelo**: Agendamento pendente
- 🔴 **Vermelho**: Horário bloqueado
- ⚪ **Branco**: Horário disponível

#### Informações do Agendamento
Clique em qualquer agendamento para ver:
```
👤 Cliente: João Silva
📞 Telefone: (11) 99999-9999
✂️ Serviço: Corte Clássico + Barba
⏱️ Duração: 45 minutos
💰 Valor: R$ 65,00
📝 Observações: Corte social, barba baixa
```

### 3. Gerenciando Disponibilidade

#### Bloquear Horários
1. **Clique** em horário disponível
2. **Selecione** "Bloquear Horário"
3. **Escolha** período (1h, manhã, dia todo)
4. **Confirme** o bloqueio

#### Alterar Status
```
🟢 Disponível    → Aceita novos agendamentos
🟡 Ocupado      → Não aceita agendamentos
🔴 Indisponível → Fora do expediente
```

### 4. Atendimentos do Dia

#### Lista de Hoje
```
📅 Hoje - 15/10/2025

09:00 - João Silva
        Corte Clássico (30min)
        📞 (11) 99999-9999

10:30 - Pedro Santos  
        Corte + Barba (45min)
        📞 (11) 88888-8888

14:00 - HORÁRIO LIVRE

15:30 - Carlos Oliveira
        Barba Completa (30min)
        📞 (11) 77777-7777
```

### 5. Estatísticas Pessoais

#### Resumo Mensal
- **Atendimentos**: 156 clientes
- **Faturamento**: R$ 6.240,00
- **Média por dia**: R$ 208,00
- **Serviço mais solicitado**: Corte Clássico

#### Gráficos
- Atendimentos por dia da semana
- Faturamento mensal
- Horários mais ocupados

---

## Para Administradores

### 1. Dashboard Principal

**Acesso**: `https://seu-dominio.com/admin`

#### Visão Geral
```
📊 HOJE (15/10/2025)
├── 📅 Agendamentos: 24
├── 💰 Faturamento: R$ 1.560,00
├── 👥 Novos clientes: 3
└── ✂️ Barbeiros ativos: 4/4

📈 ESTE MÊS
├── 📅 Agendamentos: 486
├── 💰 Faturamento: R$ 31.890,00
├── 📊 Crescimento: +12% vs mês anterior
└── ⭐ Satisfação: 4.8/5
```

### 2. Gestão de Barbeiros

#### Lista de Barbeiros
![Admin - Barbeiros](images/admin-barbeiros.png)

**Ações Disponíveis**:
- ➕ **Adicionar**: Novo barbeiro
- ✏️ **Editar**: Dados e especialidades
- 🔄 **Status**: Ativar/Desativar
- 📊 **Relatório**: Performance individual

#### Cadastrar Novo Barbeiro
```
📝 Dados Pessoais
├── Nome completo
├── Email (será o login)
├── Telefone
├── Especialidades
└── Senha inicial

⏰ Horários de Trabalho
├── Segunda: 08:00 - 18:00
├── Terça: 08:00 - 18:00
├── ...
└── Domingo: Folga

💰 Comissões
├── Corte: 60%
├── Barba: 55%
└── Outros: 50%
```

### 3. Gestão de Serviços

#### Lista de Serviços
![Admin - Serviços](images/admin-servicos.png)

**Informações por Serviço**:
- Nome e descrição
- Preço atual
- Duração estimada
- Categoria
- Status (ativo/inativo)

#### Categorias de Serviços
```
✂️ CORTES
├── Corte Social - R$ 35,00 (30min)
├── Corte Clássico - R$ 45,00 (30min)
├── Corte Moderno - R$ 55,00 (45min)
└── Corte + Acabamento - R$ 65,00 (45min)

🧔 BARBAS
├── Fazer Barba - R$ 25,00 (20min)
├── Aparar Barba - R$ 20,00 (15min)
└── Barba Completa - R$ 35,00 (30min)

💅 EXTRAS  
├── Sobrancelha - R$ 15,00 (10min)
├── Bigode - R$ 10,00 (5min)
└── Tratamento Capilar - R$ 80,00 (60min)
```

### 4. Relatórios Detalhados

#### Relatório Financeiro
```
📊 FATURAMENTO MENSAL

💰 Total Bruto: R$ 31.890,00
├── Cortes: R$ 18.450,00 (58%)
├── Barbas: R$ 9.280,00 (29%)
└── Extras: R$ 4.160,00 (13%)

📈 Comparativo
├── Mês anterior: R$ 28.460,00
├── Crescimento: +12,05%
├── Meta mensal: R$ 30.000,00
└── Atingimento: 106,3% ✅
```

#### Relatório de Clientes
```
👥 BASE DE CLIENTES

📊 Estatísticas
├── Total cadastrados: 1.247
├── Ativos (últ. 3 meses): 856
├── Novos este mês: 47
└── Taxa de retenção: 68,7%

🏆 Top 10 Clientes
├── 1. João Silva - R$ 480,00 (12 visitas)
├── 2. Pedro Santos - R$ 420,00 (10 visitas)
└── ...
```

#### Relatório de Performance
```
⭐ INDICADORES DE QUALIDADE

📊 Satisfação Geral: 4.8/5
├── Atendimento: 4.9/5
├── Ambiente: 4.7/5
├── Preço: 4.6/5
└── Pontualidade: 4.9/5

📈 Métricas Operacionais
├── Taxa de cancelamento: 3,2%
├── Taxa de no-show: 1,8%
├── Tempo médio de atendimento: 38min
└── Ocupação média: 87%
```

### 5. Ferramentas Administrativas

#### Segurança e Monitoramento
**Acesso**: `/admin/security`

```
🛡️ SEGURANÇA (Últimas 24h)
├── Tentativas de login: 12
├── IPs bloqueados: 0
├── Eventos críticos: 0
└── Status: ✅ Normal

📊 Logs Recentes
├── 14:30 - Login admin (João)
├── 14:25 - Agendamento criado
├── 14:20 - Cliente registrado
└── 14:15 - Backup automático
```

#### Backup e Manutenção
**Acesso**: `/tools/admin_tools`

```
🔧 FERRAMENTAS DO SISTEMA

💾 Último Backup
├── Data: 15/10/2025 02:00
├── Tamanho: 156 MB
├── Status: ✅ Sucesso
└── [Criar Novo Backup]

🔨 Manutenção
├── Última execução: 14/10/2025
├── Tempo: 2,3 segundos  
├── Tarefas: 8/8 concluídas
└── [Executar Manutenção]

📊 Testes do Sistema
├── Última execução: 15/10/2025 08:00
├── Sucessos: 42/45
├── Avisos: 3
└── [Executar Testes]
```

---

## Funcionalidades Avançadas

### 1. Sistema de Notificações

#### Para Clientes
- **Email automático** de confirmação
- **SMS** (se configurado) de lembrete
- **Push notifications** (app móvel)

#### Para Barbeiros
- **Alertas** de novos agendamentos
- **Notificações** de cancelamentos
- **Lembretes** de horários próximos

#### Para Administradores
- **Relatórios** diários por email
- **Alertas** de problemas no sistema
- **Backup** status notifications

### 2. Integração com Calendários

#### Google Calendar
1. **Configure** nas configurações do barbeiro
2. **Autorize** acesso ao Google Calendar
3. **Sincronização** automática bidirecional

#### Outlook Calendar
1. **Export** da agenda em formato .ics
2. **Import** no Outlook
3. **Atualização** manual periódica

### 3. Programa de Fidelidade

#### Sistema de Pontos
```
💳 CARTÃO FIDELIDADE

⭐ João Silva - Cliente Ouro
├── Pontos acumulados: 485
├── Próxima recompensa: 15 pontos
├── Desconto disponível: R$ 12,00
└── Histórico: 24 visitas

🏆 Benefícios por Nível
├── Bronze (0-99pts): 5% desconto
├── Prata (100-299pts): 10% desconto
├── Ouro (300-499pts): 15% desconto
└── Diamante (500+pts): 20% desconto
```

### 4. Analytics Avançadas

#### Dashboard Executivo
```
📊 KPIs PRINCIPAIS

💰 Revenue Metrics
├── MRR: R$ 31.890,00
├── ARPU: R$ 37,25
├── LTV: R$ 892,50
└── CAC: R$ 45,20

📈 Growth Metrics  
├── New Customers: +47 (12%)
├── Churn Rate: 2,3%
├── Retention: 68,7%
└── NPS Score: 78

⚡ Operational Metrics
├── Booking Rate: 87%
├── No-show Rate: 1,8%
├── Avg Service Time: 38min
└── Utilization: 89%
```

---

## Perguntas Frequentes

### Para Clientes

**❓ Como cancelar um agendamento?**
- Acesse seu perfil em `/perfil`
- Encontre o agendamento
- Clique em "Cancelar" (até 2h antes)

**❓ Posso escolher um barbeiro específico?**
- Sim! No passo 2 do agendamento
- Ou deixe "Qualquer barbeiro" para mais opções

**❓ E se eu me atrasar?**
- Tolerance de até 15 minutos
- Após isso, agendamento pode ser cancelado
- Ligue para avisar: (11) 9999-9999

**❓ Como alterar meus dados?**
- Menu "Perfil" → "Editar Dados"
- Altere nome, telefone, email
- Senha requer confirmação atual

### Para Barbeiros

**❓ Como bloquear um horário?**
- Clique no horário livre no calendário
- Selecione "Bloquear"
- Escolha duração
- Confirme

**❓ Cliente faltou, o que fazer?**
- Marque como "No-show" no sistema
- Cliente será notificado automaticamente
- Horário fica liberado para outros

**❓ Como ver meus ganhos?**
- Dashboard → "Estatísticas"
- Relatório mensal detalhado
- Comissões por serviço

### Para Administradores

**❓ Como adicionar um novo serviço?**
- Admin → "Serviços" → "Novo"
- Preencha nome, preço, duração
- Defina categoria
- Ative o serviço

**❓ Relatório não está correto?**
- Verifique período selecionado
- Execute "Atualizar Cache"
- Se persistir, execute manutenção

**❓ Como fazer backup manual?**
- Tools → "Ferramentas"
- Clique "Criar Backup"
- Download automático após criação

---

## Atalhos de Teclado

### Navegação Global
- `Ctrl + H` → Página inicial
- `Ctrl + L` → Login
- `Ctrl + P` → Perfil (se logado)
- `Ctrl + A` → Agendar (se logado)

### Dashboard Admin
- `Ctrl + 1` → Barbeiros
- `Ctrl + 2` → Serviços  
- `Ctrl + 3` → Relatórios
- `Ctrl + 4` → Segurança
- `Ctrl + B` → Backup

### Dashboard Barbeiro
- `Ctrl + T` → Hoje
- `Ctrl + S` → Semana
- `Ctrl + M` → Mês
- `Ctrl + E` → Estatísticas

---

## Suporte

### Contatos de Suporte
- **Email**: suporte@mrcarlosbarbershop.com
- **Telefone**: (11) 9999-9999
- **WhatsApp**: (11) 8888-8888
- **Horário**: Seg-Sex 8h-18h, Sáb 8h-12h

### Documentação Técnica
- **Manual de Instalação**: `/docs/INSTALL.md`
- **API Documentation**: `/docs/API.md`
- **Manual de Manutenção**: `/docs/MAINTENANCE.md`

### Tutoriais em Vídeo
- **Playlist YouTube**: Mr. Carlos Barbershop Tutorials
- **Para Clientes**: Como fazer agendamento
- **Para Barbeiros**: Usando o dashboard
- **Para Admins**: Configuração completa

### Status do Sistema
- **Página de Status**: https://status.mrcarlosbarbershop.com
- **Uptime Atual**: 99.9%
- **Última Manutenção**: 14/10/2025 02:00

---

*Guia atualizado em: 14 de Outubro de 2025 - Versão 1.0*