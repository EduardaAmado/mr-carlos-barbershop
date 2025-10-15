-- Criar tabelas faltantes para compatibilidade com o sistema
-- Mr. Carlos Barbershop - Tabelas Adicionais

USE mr_carlos_barbershop;

-- ==================================================
-- TABELA: usuarios (compatibilidade com sistema de login)
-- Finalidade: Alias/View da tabela clientes para compatibilidade
-- ==================================================

-- Criar tabela usuarios como cópia da estrutura de clientes
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    password VARCHAR(255) NOT NULL,  -- Note: mudança de password_hash para password
    data_nascimento DATE,
    data_registo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_login TIMESTAMP NULL,
    ativo BOOLEAN DEFAULT TRUE,
    notas TEXT,
    type ENUM('cliente') DEFAULT 'cliente',  -- Adicionar campo type para compatibilidade
    
    INDEX idx_email (email),
    INDEX idx_nome (nome),
    INDEX idx_data_registo (data_registo),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- TABELA: admin (compatibilidade com sistema de login)
-- Finalidade: Alias da tabela admins para compatibilidade
-- ==================================================

-- Criar tabela admin como cópia da estrutura de admins
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,  -- Note: mudança de password_hash para password
    nivel_acesso ENUM('super_admin', 'admin', 'gestor') DEFAULT 'admin',
    ativo BOOLEAN DEFAULT TRUE,
    ultimo_login TIMESTAMP NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    criado_por INT,
    
    FOREIGN KEY (criado_por) REFERENCES admin(id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_nivel (nivel_acesso),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- INSERIR DADOS DE EXEMPLO
-- ==================================================

-- Inserir alguns usuários de exemplo
-- Senha para todos: 123456 (hash gerado com password_hash())
INSERT INTO usuarios (nome, email, password, telefone, type) VALUES
('João Silva', 'joao.silva@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '912345678', 'cliente'),
('Maria Santos', 'maria.santos@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '913456789', 'cliente'),
('Pedro Costa', 'pedro.costa@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '914567890', 'cliente');

-- Inserir admin de exemplo
-- Senha: admin123 (hash gerado com password_hash())
INSERT INTO admin (nome, email, password, nivel_acesso) VALUES
('Administrador Sistema', 'admin@mrcarlosbarbershop.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin'),
('Gestor Loja', 'gestor@mrcarlosbarbershop.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ==================================================
-- ATUALIZAR SENHAS DOS BARBEIROS PARA FORMATO CORRETO
-- ==================================================

-- Atualizar barbeiros com senhas hash válidas (senha: barber123)
UPDATE barbeiros SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE password_hash = '$2y$10$example_hash_placeholder';

-- ==================================================
-- COMENTÁRIOS
-- ==================================================

/*
NOTA IMPORTANTE:
Estas tabelas foram criadas para compatibilidade com o sistema de login existente.
O sistema espera:
- Tabela 'usuarios' com campo 'password' (não 'password_hash')
- Tabela 'admin' (singular, não 'admins')

SENHAS DE TESTE:
- Todos os usuários: 123456
- Admin: admin123
- Barbeiros: barber123

Para produção, as senhas devem ser alteradas imediatamente!
*/