<?php
/**
 * Script para criar contas de teste
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Criar utilizadores de teste para todos os níveis de permissão
 */

require_once __DIR__ . '/../config/config.php';

echo "<h2>🔧 Criação de Contas de Teste - Mr. Carlos Barbershop</h2>\n";
echo "<p>Este script vai criar contas de teste para todos os níveis de utilizadores.</p>\n\n";

// Função para gerar hash de password
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

try {
    // ===================================
    // 1. CLIENTES DE TESTE
    // ===================================
    echo "<h3>👤 Criando Clientes de Teste</h3>\n";
    
    $clientes = [
        [
            'nome' => 'João Silva',
            'email' => 'joao.cliente@teste.com',
            'password' => 'cliente123',
            'telefone' => '912345678',
            'data_nascimento' => '1990-05-15'
        ],
        [
            'nome' => 'Maria Santos',
            'email' => 'maria.cliente@teste.com', 
            'password' => 'cliente123',
            'telefone' => '923456789',
            'data_nascimento' => '1985-08-22'
        ],
        [
            'nome' => 'Pedro Costa',
            'email' => 'pedro.cliente@teste.com',
            'password' => 'cliente123', 
            'telefone' => '934567890',
            'data_nascimento' => '1995-12-03'
        ]
    ];

    foreach ($clientes as $cliente) {
        $sql = "INSERT INTO clientes (nome, email, password_hash, telefone, data_nascimento) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                nome = VALUES(nome), 
                password_hash = VALUES(password_hash),
                telefone = VALUES(telefone),
                data_nascimento = VALUES(data_nascimento)";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $cliente['nome'],
            $cliente['email'], 
            hash_password($cliente['password']),
            $cliente['telefone'],
            $cliente['data_nascimento']
        ]);
        
        if ($result) {
            echo "✅ Cliente criado: <strong>{$cliente['nome']}</strong> ({$cliente['email']})<br>\n";
        } else {
            echo "❌ Erro ao criar cliente: {$cliente['nome']}<br>\n";
        }
    }

    // ===================================
    // 2. BARBEIROS DE TESTE  
    // ===================================
    echo "<h3>✂️ Criando Barbeiros de Teste</h3>\n";
    
    $barbeiros = [
        [
            'nome' => 'Carlos Barbeiro',
            'email' => 'carlos.barbeiro@teste.com',
            'password' => 'barbeiro123',
            'telefone' => '911111111',
            'especialidades' => 'Corte Clássico, Barba, Bigode',
            'biografia' => 'Barbeiro experiente com 15 anos de profissão.',
            'data_contratacao' => '2010-01-15'
        ],
        [
            'nome' => 'António Silva',
            'email' => 'antonio.barbeiro@teste.com', 
            'password' => 'barbeiro123',
            'telefone' => '922222222',
            'especialidades' => 'Corte Moderno, Degradê, Tratamentos',
            'biografia' => 'Especialista em cortes modernos e tendências.',
            'data_contratacao' => '2015-03-20'
        ],
        [
            'nome' => 'Miguel Santos',
            'email' => 'miguel.barbeiro@teste.com',
            'password' => 'barbeiro123',
            'telefone' => '933333333', 
            'especialidades' => 'Todos os serviços',
            'biografia' => 'Barbeiro versátil, atende todos os tipos de cliente.',
            'data_contratacao' => '2018-07-10'
        ]
    ];

    foreach ($barbeiros as $barbeiro) {
        $sql = "INSERT INTO barbeiros (nome, email, password_hash, telefone, especialidades, biografia, data_contratacao, horario_inicio, horario_fim, dias_trabalho) 
                VALUES (?, ?, ?, ?, ?, ?, ?, '09:00:00', '18:00:00', '[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\"]') 
                ON DUPLICATE KEY UPDATE 
                nome = VALUES(nome),
                password_hash = VALUES(password_hash),
                telefone = VALUES(telefone),
                especialidades = VALUES(especialidades),
                biografia = VALUES(biografia),
                data_contratacao = VALUES(data_contratacao)";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $barbeiro['nome'],
            $barbeiro['email'],
            hash_password($barbeiro['password']),
            $barbeiro['telefone'],
            $barbeiro['especialidades'],
            $barbeiro['biografia'],
            $barbeiro['data_contratacao']
        ]);
        
        if ($result) {
            echo "✅ Barbeiro criado: <strong>{$barbeiro['nome']}</strong> ({$barbeiro['email']})<br>\n";
        } else {
            echo "❌ Erro ao criar barbeiro: {$barbeiro['nome']}<br>\n";
        }
    }

    // ===================================
    // 3. ADMINISTRADORES DE TESTE
    // ===================================
    echo "<h3>🛡️ Criando Administradores de Teste</h3>\n";
    
    $admins = [
        [
            'nome' => 'Super Admin',
            'email' => 'super@teste.com',
            'password' => 'super123',
            'nivel_acesso' => 'super_admin'
        ],
        [
            'nome' => 'Admin Principal',
            'email' => 'admin@teste.com',
            'password' => 'admin123', 
            'nivel_acesso' => 'admin'
        ],
        [
            'nome' => 'Gestor Loja',
            'email' => 'gestor@teste.com',
            'password' => 'gestor123',
            'nivel_acesso' => 'gestor'
        ]
    ];

    foreach ($admins as $admin) {
        $sql = "INSERT INTO admins (nome, email, password_hash, nivel_acesso) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                nome = VALUES(nome),
                password_hash = VALUES(password_hash),
                nivel_acesso = VALUES(nivel_acesso)";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $admin['nome'],
            $admin['email'],
            hash_password($admin['password']),
            $admin['nivel_acesso']
        ]);
        
        if ($result) {
            echo "✅ Admin criado: <strong>{$admin['nome']}</strong> ({$admin['email']}) - Nível: {$admin['nivel_acesso']}<br>\n";
        } else {
            echo "❌ Erro ao criar admin: {$admin['nome']}<br>\n";
        }
    }

    // ===================================
    // 4. SERVIÇOS DE TESTE
    // ===================================
    echo "<h3>🎯 Criando Serviços de Teste</h3>\n";
    
    $servicos = [
        [
            'nome' => 'Corte Clássico',
            'descricao' => 'Corte tradicional com tesoura e máquina, acabamento perfeito',
            'descricao_curta' => 'Corte tradicional com acabamento perfeito',
            'duracao_minutos' => 30,
            'preco' => 15.00,
            'categoria' => 'corte'
        ],
        [
            'nome' => 'Corte Moderno',
            'descricao' => 'Cortes atuais seguindo as últimas tendências da moda',
            'descricao_curta' => 'Cortes atuais e modernos',
            'duracao_minutos' => 45,
            'preco' => 20.00,
            'categoria' => 'corte'
        ],
        [
            'nome' => 'Barba Completa',
            'descricao' => 'Aparar e modelar barba com toalha quente e produtos premium',
            'descricao_curta' => 'Aparar e modelar com toalha quente',
            'duracao_minutos' => 25,
            'preco' => 12.00,
            'categoria' => 'barba'
        ],
        [
            'nome' => 'Bigode',
            'descricao' => 'Aparar e modelar bigode com precisão',
            'descricao_curta' => 'Aparar e modelar bigode',
            'duracao_minutos' => 15,
            'preco' => 8.00,
            'categoria' => 'barba'
        ],
        [
            'nome' => 'Corte + Barba',
            'descricao' => 'Serviço completo: corte de cabelo + barba com desconto especial',
            'descricao_curta' => 'Serviço completo com desconto',
            'duracao_minutos' => 50,
            'preco' => 25.00,
            'categoria' => 'combo'
        ],
        [
            'nome' => 'Tratamento Capilar',
            'descricao' => 'Lavagem, tratamento nutritivo e styling profissional',
            'descricao_curta' => 'Tratamento nutritivo completo',
            'duracao_minutos' => 40,
            'preco' => 18.00,
            'categoria' => 'tratamento'
        ]
    ];

    foreach ($servicos as $i => $servico) {
        $sql = "INSERT INTO servicos (nome, descricao, descricao_curta, duracao_minutos, preco, categoria, ordem_exibicao) 
                VALUES (?, ?, ?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                descricao = VALUES(descricao),
                descricao_curta = VALUES(descricao_curta),
                duracao_minutos = VALUES(duracao_minutos),
                preco = VALUES(preco),
                categoria = VALUES(categoria),
                ordem_exibicao = VALUES(ordem_exibicao)";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $servico['nome'],
            $servico['descricao'],
            $servico['descricao_curta'],
            $servico['duracao_minutos'],
            $servico['preco'],
            $servico['categoria'],
            $i + 1
        ]);
        
        if ($result) {
            echo "✅ Serviço criado: <strong>{$servico['nome']}</strong> - €{$servico['preco']}<br>\n";
        } else {
            echo "❌ Erro ao criar serviço: {$servico['nome']}<br>\n";
        }
    }

    echo "<hr>\n";
    echo "<h3>📋 Resumo das Contas Criadas</h3>\n";
    echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 5px; font-family: monospace;'>\n";
    
    echo "<h4>👤 CLIENTES (Password: cliente123)</h4>\n";
    echo "• joao.cliente@teste.com<br>\n";
    echo "• maria.cliente@teste.com<br>\n";
    echo "• pedro.cliente@teste.com<br><br>\n";
    
    echo "<h4>✂️ BARBEIROS (Password: barbeiro123)</h4>\n";
    echo "• carlos.barbeiro@teste.com<br>\n";
    echo "• antonio.barbeiro@teste.com<br>\n";  
    echo "• miguel.barbeiro@teste.com<br><br>\n";
    
    echo "<h4>🛡️ ADMINISTRADORES</h4>\n";
    echo "• <strong>super@teste.com</strong> (Password: super123) - Super Admin<br>\n";
    echo "• <strong>admin@teste.com</strong> (Password: admin123) - Admin<br>\n";
    echo "• <strong>gestor@teste.com</strong> (Password: gestor123) - Gestor<br>\n";
    
    echo "</div>\n";
    
    echo "<h3>🎯 Links de Teste</h3>\n";
    echo "<ul>\n";
    echo "<li><a href='../pages/login.php' target='_blank'>Login Clientes</a></li>\n";
    echo "<li><a href='../barbeiro/login.php' target='_blank'>Login Barbeiros</a></li>\n";
    echo "<li><a href='../admin/login.php' target='_blank'>Login Administradores</a></li>\n";
    echo "</ul>\n";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ <strong>Erro na base de dados:</strong> " . $e->getMessage() . "</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Erro geral:</strong> " . $e->getMessage() . "</p>\n";
}
?>