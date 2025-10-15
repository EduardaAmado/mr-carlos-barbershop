# 🔐 CONTAS DE TESTE - MR. CARLOS BARBERSHOP
# ==========================================
# Data: 14 de Outubro de 2025
# Todas as passwords são simples para facilitar os testes
# Em produção, use passwords seguras!

## 👤 CLIENTES
# Acesso: http://localhost/mr-carlos-barbershop/pages/login.php
# Password padrão: cliente123

Email: joao.cliente@teste.com
Nome: João Silva
Telefone: 912345678
Nascimento: 15/05/1990

Email: maria.cliente@teste.com  
Nome: Maria Santos
Telefone: 923456789
Nascimento: 22/08/1985

Email: pedro.cliente@teste.com
Nome: Pedro Costa
Telefone: 934567890
Nascimento: 03/12/1995

## ✂️ BARBEIROS
# Acesso: http://localhost/mr-carlos-barbershop/barbeiro/login.php
# Password padrão: barbeiro123

Email: carlos.barbeiro@teste.com
Nome: Carlos Barbeiro
Telefone: 911111111
Especialidades: Corte Clássico, Barba, Bigode
Contratação: 15/01/2010

Email: antonio.barbeiro@teste.com
Nome: António Silva  
Telefone: 922222222
Especialidades: Corte Moderno, Degradê, Tratamentos
Contratação: 20/03/2015

Email: miguel.barbeiro@teste.com
Nome: Miguel Santos
Telefone: 933333333
Especialidades: Todos os serviços
Contratação: 10/07/2018

## 🛡️ ADMINISTRADORES
# Acesso: http://localhost/mr-carlos-barbershop/admin/login.php

Email: super@teste.com
Password: super123
Nome: Super Admin
Nível: super_admin (acesso total)

Email: admin@teste.com
Password: admin123
Nome: Admin Principal  
Nível: admin (gestão geral)

Email: gestor@teste.com
Password: gestor123
Nome: Gestor Loja
Nível: gestor (gestão operacional)

## 🎯 SERVIÇOS DISPONÍVEIS

1. Corte Clássico - €15,00 (30 min)
2. Corte Moderno - €20,00 (45 min)  
3. Barba Completa - €12,00 (25 min)
4. Bigode - €8,00 (15 min)
5. Corte + Barba - €25,00 (50 min)
6. Tratamento Capilar - €18,00 (40 min)

## 📝 CENÁRIOS DE TESTE SUGERIDOS

### CLIENTE:
- Registar nova conta
- Fazer login com conta existente
- Navegar pelos serviços
- Agendar um corte
- Ver histórico de agendamentos
- Editar perfil

### BARBEIRO:  
- Login no painel de barbeiro
- Ver agenda do dia
- Confirmar/cancelar agendamentos
- Atualizar disponibilidade
- Ver histórico de clientes

### ADMIN:
- Gestão de utilizadores
- Configurações do sistema
- Relatórios e estatísticas
- Gestão de serviços e preços
- Backup e manutenção

## 🔧 FERRAMENTAS DE TESTE

- Script de criação: /tools/criar_contas_teste.php
- Teste do sistema: /tools/test_system.php  
- Verificação CSP: /tools/test_csp.php

==========================================
Mr. Carlos Barbershop - Sistema de Teste
==========================================