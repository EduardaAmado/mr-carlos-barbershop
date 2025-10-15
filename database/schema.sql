-- ==================================================
-- Mr. Carlos Barbershop - Schema da Base de Dados
-- Autor: Sistema Mr. Carlos Barbershop
-- Data: 14 de Outubro de 2025
-- Finalidade: Criar estrutura completa da base de dados
-- ==================================================

-- Criar base de dados se não existir
CREATE DATABASE IF NOT EXISTS mr_carlos_barbershop 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE mr_carlos_barbershop;

-- ==================================================
-- TABELA: clientes
-- Finalidade: Armazenar dados dos clientes registados
-- ==================================================
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    data_nascimento DATE,
    data_registo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_login TIMESTAMP NULL,
    ativo BOOLEAN DEFAULT TRUE,
    notas TEXT,
    INDEX idx_email (email),
    INDEX idx_nome (nome),
    INDEX idx_data_registo (data_registo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- TABELA: barbeiros
-- Finalidade: Armazenar dados dos barbeiros
-- ==================================================
CREATE TABLE barbeiros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    especialidades TEXT,
    horario_inicio TIME DEFAULT '09:00:00',
    horario_fim TIME DEFAULT '18:00:00',
    dias_trabalho JSON DEFAULT ('["1","2","3","4","5","6"]'), -- 1=Segunda, 6=Sábado
    ativo BOOLEAN DEFAULT TRUE,
    data_contratacao DATE,
    biografia TEXT,
    foto VARCHAR(255),
    INDEX idx_email (email),
    INDEX idx_nome (nome),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- TABELA: servicos
-- Finalidade: Catálogo de serviços oferecidos
-- ==================================================
CREATE TABLE servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    descricao_curta VARCHAR(200),
    duracao_minutos INT NOT NULL DEFAULT 30,
    preco DECIMAL(5,2) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    categoria VARCHAR(50) DEFAULT 'corte',
    ordem_exibicao INT DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nome (nome),
    INDEX idx_categoria (categoria),
    INDEX idx_ativo (ativo),
    INDEX idx_ordem (ordem_exibicao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- TABELA: agendamentos
-- Finalidade: Armazenar agendamentos dos clientes
-- ==================================================
CREATE TABLE agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    barbeiro_id INT,
    servico_id INT,
    data_hora DATETIME NOT NULL,
    data_fim DATETIME NOT NULL,
    status ENUM('pendente', 'confirmado', 'em_curso', 'concluido', 'cancelado', 'falta') DEFAULT 'pendente',
    notas TEXT,
    preco_pago DECIMAL(5,2),
    metodo_pagamento ENUM('dinheiro', 'cartao', 'mbway', 'transferencia') DEFAULT 'dinheiro',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    cancelado_por ENUM('cliente', 'barbeiro', 'admin', 'sistema') NULL,
    motivo_cancelamento TEXT,
    
    -- Foreign Keys
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (barbeiro_id) REFERENCES barbeiros(id) ON DELETE SET NULL,
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE SET NULL,
    
    -- Índices para performance
    INDEX idx_data_hora (data_hora),
    INDEX idx_barbeiro_data (barbeiro_id, data_hora),
    INDEX idx_cliente_data (cliente_id, data_hora),
    INDEX idx_servico (servico_id),
    INDEX idx_status (status),
    INDEX idx_data_criacao (data_criacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- TABELA: admins
-- Finalidade: Utilizadores com acesso administrativo
-- ==================================================
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    nivel_acesso ENUM('super_admin', 'admin', 'gestor') DEFAULT 'admin',
    ativo BOOLEAN DEFAULT TRUE,
    ultimo_login TIMESTAMP NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    criado_por INT,
    
    FOREIGN KEY (criado_por) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_nivel (nivel_acesso),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- TABELA: administradores
-- Finalidade: Armazenar dados dos administradores do sistema
-- ==================================================
CREATE TABLE administradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    nivel INT DEFAULT 1,
    ativo BOOLEAN DEFAULT TRUE,
    ultimo_login TIMESTAMP NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- TABELA: bloqueios
-- Finalidade: Períodos indisponíveis dos barbeiros
-- ==================================================
CREATE TABLE bloqueios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barbeiro_id INT NOT NULL,
    data_inicio DATETIME NOT NULL,
    data_fim DATETIME NOT NULL,
    motivo VARCHAR(100) DEFAULT 'Indisponível',
    tipo ENUM('folga', 'ferias', 'doenca', 'formacao', 'outro') DEFAULT 'folga',
    ativo BOOLEAN DEFAULT TRUE,
    criado_por_admin INT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (barbeiro_id) REFERENCES barbeiros(id) ON DELETE CASCADE,
    FOREIGN KEY (criado_por_admin) REFERENCES admins(id) ON DELETE SET NULL,
    
    INDEX idx_barbeiro_data (barbeiro_id, data_inicio, data_fim),
    INDEX idx_data_inicio (data_inicio),
    INDEX idx_tipo (tipo),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- TABELA: tentativas_login
-- Finalidade: Controlo de tentativas de login para segurança
-- ==================================================
CREATE TABLE tentativas_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    email VARCHAR(100),
    tentativas INT DEFAULT 1,
    ultimo_tentativa TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    bloqueado_ate TIMESTAMP NULL,
    tipo_usuario ENUM('cliente', 'barbeiro', 'admin') DEFAULT 'cliente',
    
    INDEX idx_ip (ip_address),
    INDEX idx_email (email),
    INDEX idx_bloqueado (bloqueado_ate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- DADOS DE EXEMPLO
-- ==================================================

-- Inserir barbeiros exemplo
INSERT INTO barbeiros (nome, email, password_hash, especialidades, data_contratacao, biografia) VALUES
('Carlos Silva', 'carlos@mrcarlosbarbershop.pt', '$2y$10$example_hash_placeholder', 'Cortes clássicos, Barbas, Bigodes', '1985-01-15', 'Fundador da barbearia com mais de 35 anos de experiência. Especialista em cortes clássicos e tradicionais.'),
('João Santos', 'joao@mrcarlosbarbershop.pt', '$2y$10$example_hash_placeholder', 'Cortes modernos, Fade, Desenhos', '2010-03-20', 'Barbeiro especializado em técnicas modernas e tendências atuais. Expert em cortes fade e desenhos criativos.');

-- Inserir serviços exemplo
INSERT INTO servicos (nome, descricao, descricao_curta, duracao_minutos, preco, categoria, ordem_exibicao) VALUES
('Corte Clássico', 'Corte tradicional com tesoura e máquina, acabamento perfeito com atenção aos detalhes. Inclui lavagem e styling.', 'Corte tradicional com acabamento perfeito', 30, 15.00, 'corte', 1),
('Corte Moderno', 'Corte contemporâneo seguindo as últimas tendências. Técnicas modernas adaptadas ao seu estilo pessoal.', 'Corte contemporâneo e na moda', 35, 18.00, 'corte', 2),
('Barba Completa', 'Aparar e modelar a barba com navalha tradicional. Inclui toalha quente, óleo e bálsamo pós-barba.', 'Aparar e modelar com toalha quente', 25, 12.00, 'barba', 3),
('Corte + Barba', 'Serviço completo combinando corte de cabelo e tratamento de barba. Experiência completa de barbearia clássica.', 'Serviço completo com desconto especial', 50, 25.00, 'combo', 4);

-- Inserir admin exemplo (password: admin123)
INSERT INTO admins (nome, email, password_hash, nivel_acesso) VALUES
('Administrador', 'admin@mrcarlosbarbershop.pt', '$2y$10$example_hash_placeholder', 'super_admin');

-- ==================================================
-- TRIGGERS E PROCEDURES (Opcional)
-- ==================================================

-- Trigger para calcular data_fim do agendamento
DELIMITER $$
CREATE TRIGGER tr_agendamento_data_fim 
BEFORE INSERT ON agendamentos
FOR EACH ROW
BEGIN
    DECLARE duracao INT DEFAULT 30;
    
    -- Obter duração do serviço
    SELECT duracao_minutos INTO duracao 
    FROM servicos 
    WHERE id = NEW.servico_id;
    
    -- Calcular data de fim
    SET NEW.data_fim = DATE_ADD(NEW.data_hora, INTERVAL duracao MINUTE);
END$$
DELIMITER ;

-- Trigger para atualizar data_atualizacao
DELIMITER $$
CREATE TRIGGER tr_agendamento_update 
BEFORE UPDATE ON agendamentos
FOR EACH ROW
BEGIN
    SET NEW.data_atualizacao = CURRENT_TIMESTAMP;
END$$
DELIMITER ;

-- ==================================================
-- VIEWS ÚTEIS
-- ==================================================

-- View para agendamentos com detalhes
CREATE VIEW v_agendamentos_completos AS
SELECT 
    a.id,
    a.data_hora,
    a.data_fim,
    a.status,
    a.notas,
    a.preco_pago,
    c.nome AS cliente_nome,
    c.telefone AS cliente_telefone,
    b.nome AS barbeiro_nome,
    s.nome AS servico_nome,
    s.duracao_minutos,
    s.preco AS servico_preco
FROM agendamentos a
LEFT JOIN clientes c ON a.cliente_id = c.id
LEFT JOIN barbeiros b ON a.barbeiro_id = b.id
LEFT JOIN servicos s ON a.servico_id = s.id;

-- View para estatísticas mensais
CREATE VIEW v_stats_mensais AS
SELECT 
    YEAR(data_hora) as ano,
    MONTH(data_hora) as mes,
    COUNT(*) as total_agendamentos,
    SUM(CASE WHEN status = 'concluido' THEN preco_pago ELSE 0 END) as receita_total,
    COUNT(CASE WHEN status = 'concluido' THEN 1 END) as agendamentos_concluidos,
    COUNT(CASE WHEN status = 'cancelado' THEN 1 END) as agendamentos_cancelados
FROM agendamentos
GROUP BY YEAR(data_hora), MONTH(data_hora)
ORDER BY ano DESC, mes DESC;

-- ==================================================
-- COMENTÁRIOS FINAIS
-- ==================================================

/*
DECISÕES DE SCHEMA:

1. FOREIGN KEYS com ON DELETE SET NULL:
   - Permite manter histórico mesmo se barbeiro/cliente for removido
   - Agendamentos antigos não são perdidos

2. ÍNDICES:
   - data_hora: Consultas frequentes por data
   - barbeiro_id + data_hora: Disponibilidade do barbeiro
   - cliente_id: Histórico do cliente

3. CAMPOS JSON:
   - dias_trabalho: Flexibilidade para horários diferentes

4. ENUM para status:
   - Controlo rigoroso dos estados possíveis
   - Performance superior a strings livres

5. CHARSET utf8mb4:
   - Suporte completo para caracteres especiais
   - Emojis e acentos portugueses

6. TIMESTAMPS:
   - Auditoria completa de criação/alteração
   - Timezone-aware com configuração PHP
*/