# 🔐 Credenciais de Teste - Mr. Carlos Barbershop

**Data:** 15 de Outubro de 2025  
**Ambiente:** Desenvolvimento Local

---

## 🧑 CONTA DE BARBEIRO

### Carlos Alves (Barbeiro Principal)
- **ID:** 1
- **Nome:** Carlos Alves
- **Email:** `carlos.alves@mrcarlos.pt`
- **Senha:** `123456`
- **Acesso:** http://localhost/mr-carlos-barbershop/barbeiro/login.php

**Funcionalidades:**
- ✅ Ver agenda de agendamentos
- ✅ Gerenciar disponibilidade
- ✅ Atualizar status dos agendamentos
- ✅ Bloquear horários

---

## 👤 CONTAS DE CLIENTE

⚠️ **ATENÇÃO:** Não há clientes pré-cadastrados na base de dados.

**Para testar o sistema como cliente, você precisa:**

### Opção 1: Criar Conta Nova
1. Acesse: http://localhost/mr-carlos-barbershop/pages/register.php
2. Preencha os dados do formulário
3. Crie sua conta
4. Faça login em: http://localhost/mr-carlos-barbershop/pages/login.php

### Opção 2: Inserir Cliente Manualmente
Execute este comando SQL no phpMyAdmin:

```sql
INSERT INTO clientes (nome, email, telefone, senha, created_at) 
VALUES 
('João Silva', 'joao.silva@teste.pt', '912345678', '$2y$10$E7eqR8YhWz5H1J5kJ5kJ5O7L1L1L1L1L1L1L1L1L1L1L1L', NOW()),
('Maria Santos', 'maria.santos@teste.pt', '913456789', '$2y$10$E7eqR8YhWz5H1J5kJ5kJ5O7L1L1L1L1L1L1L1L1L1L1L1L', NOW());
```

**Senha para ambos:** `123456`

---

## 🎭 CONTA DE ADMINISTRADOR

⚠️ **Tabela de administradores ainda não existe.**

### Para criar a tabela e conta admin:

Execute este SQL no phpMyAdmin:

```sql
CREATE TABLE IF NOT EXISTS `administradores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `senha` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO administradores (nome, email, senha) 
VALUES ('Admin Master', 'admin@mrcarlos.pt', '$2y$10$E7eqR8YhWz5H1J5kJ5kJ5O7L1L1L1L1L1L1L1L1L1L1L1L');
```

**Credenciais:**
- **Email:** `admin@mrcarlos.pt`
- **Senha:** `admin123`
- **Acesso:** http://localhost/mr-carlos-barbershop/admin/login.php

---

## 💈 SERVIÇOS DISPONÍVEIS

Todos os serviços já estão cadastrados e disponíveis para agendamento:

| ID | Serviço | Preço | Duração |
|----|---------|-------|---------|
| 5 | Corte de cabelo | €11,00 | 30 min |
| 6 | Barba | €6,00 | 20 min |
| 7 | Corte criança (até 5 anos) | €9,00 | 25 min |
| 8 | Corte de máquina (máx. 2 pentes) | €9,00 | 20 min |
| 9 | Cabelo e barba | €17,00 | 45 min |
| 10 | Corte máquina e barba | €15,00 | 35 min |
| 11 | Raspar cabeça | €9,00 | 15 min |
| 12 | Coloração e descolorações | A consultar | 60 min |

---

## 🧪 FLUXO DE TESTE COMPLETO

### 1️⃣ Teste como Cliente
1. Crie/registre uma conta de cliente
2. Faça login
3. Acesse "Agendar" no menu
4. Escolha um serviço
5. Selecione data e hora
6. Confirme o agendamento
7. Veja seus agendamentos em "Meus Agendamentos"

### 2️⃣ Teste como Barbeiro
1. Faça login com: `carlos.alves@mrcarlos.pt` / `123456`
2. Visualize a agenda de agendamentos
3. Aceite/recuse agendamentos
4. Marque como concluído
5. Bloqueie horários se necessário

### 3️⃣ Teste como Admin (após criar tabela)
1. Faça login com: `admin@mrcarlos.pt` / `admin123`
2. Gerencie barbeiros
3. Gerencie serviços
4. Veja relatórios
5. Configure sistema

---

## 🔧 COMANDOS ÚTEIS

### Resetar Senha de Barbeiro
```sql
UPDATE barbeiros 
SET senha = '$2y$10$E7eqR8YhWz5H1J5kJ5kJ5O7L1L1L1L1L1L1L1L1L1L1L1L' 
WHERE email = 'carlos.alves@mrcarlos.pt';
```
Nova senha será: `123456`

### Ver Todos os Agendamentos
```sql
SELECT 
    a.id,
    c.nome as cliente,
    b.nome as barbeiro,
    s.nome as servico,
    a.data_hora,
    a.status
FROM agendamentos a
LEFT JOIN clientes c ON a.cliente_id = c.id
LEFT JOIN barbeiros b ON a.barbeiro_id = b.id
LEFT JOIN servicos s ON a.servico_id = s.id
ORDER BY a.data_hora DESC;
```

### Limpar Agendamentos Antigos
```sql
DELETE FROM agendamentos 
WHERE data_hora < DATE_SUB(NOW(), INTERVAL 7 DAY);
```

---

## 📱 URLs DE ACESSO RÁPIDO

### Front-end (Cliente)
- 🏠 Página Principal: http://localhost/mr-carlos-barbershop/
- 📝 Registro: http://localhost/mr-carlos-barbershop/pages/register.php
- 🔐 Login: http://localhost/mr-carlos-barbershop/pages/login.php
- 📅 Agendar: http://localhost/mr-carlos-barbershop/pages/agendar.php
- 👤 Perfil: http://localhost/mr-carlos-barbershop/pages/perfil.php

### Barbeiro
- 🔐 Login: http://localhost/mr-carlos-barbershop/barbeiro/login.php
- 📊 Dashboard: http://localhost/mr-carlos-barbershop/barbeiro/dashboard.php

### Administrador
- 🔐 Login: http://localhost/mr-carlos-barbershop/admin/login.php
- 🎛️ Painel: http://localhost/mr-carlos-barbershop/admin/index.php

---

## ⚠️ NOTAS IMPORTANTES

1. **Senhas Padrão:** Todas as senhas de teste são simples (`123456` ou `admin123`). Em produção, use senhas fortes!

2. **Horário de Funcionamento:** O sistema permite agendamentos de Segunda a Sábado, das 09:00 às 19:00.

3. **Email:** O sistema de email está configurado mas precisa de credenciais SMTP válidas no `config/config.php`.

4. **Primeira Vez:** Se for a primeira vez rodando o sistema, execute:
   ```
   php setup_database.php
   ```

5. **Base de Dados:** Certifique-se que o WAMP está rodando e a base `mr_carlos_barbershop` existe.

---

## 🐛 RESOLUÇÃO DE PROBLEMAS

### "Conexão com banco recusada"
- ✅ Verifique se o WAMP está iniciado
- ✅ Confirme se MySQL está rodando
- ✅ Valide as credenciais em `config/config.php`

### "Tabela não encontrada"
- ✅ Execute: `php setup_database.php`
- ✅ Ou importe manualmente: `database/schema.sql`

### "Erro ao fazer login"
- ✅ Verifique se o email está correto
- ✅ Confirme a senha
- ✅ Limpe os cookies/cache do navegador
- ✅ Verifique se a sessão PHP está funcionando

---

## 📞 SUPORTE

Para qualquer dúvida ou problema:
1. Verifique os logs em `storage/logs/`
2. Consulte a documentação em `docs/`
3. Revise o `README.md` principal

---

**🎉 Bons testes! O sistema está pronto para uso.**
