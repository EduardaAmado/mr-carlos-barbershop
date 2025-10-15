<?php
/**
 * Script para criar tabelas faltantes
 * Mr. Carlos Barbershop
 */

require_once __DIR__ . '/config/config.php';

echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n";
echo "<meta charset='UTF-8'>\n";
echo "<title>Criar Tabelas Faltantes</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }\n";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }\n";
echo ".success { color: #27ae60; padding: 10px; background: #d5f4e6; border-radius: 5px; margin: 10px 0; }\n";
echo ".error { color: #e74c3c; padding: 10px; background: #fdf2f2; border-radius: 5px; margin: 10px 0; }\n";
echo ".info { color: #3498db; padding: 10px; background: #ebf3fd; border-radius: 5px; margin: 10px 0; }\n";
echo ".warning { color: #f39c12; padding: 10px; background: #fef9e7; border-radius: 5px; margin: 10px 0; }\n";
echo "h1 { color: #2c3e50; text-align: center; }\n";
echo "h2 { color: #34495e; border-bottom: 2px solid #3498db; padding-bottom: 10px; }\n";
echo ".status { font-weight: bold; }\n";
echo "</style>\n";
echo "</head>\n<body>\n";

echo "<div class='container'>\n";
echo "<h1>🔧 Reparação da Base de Dados</h1>\n";
echo "<h2>Mr. Carlos Barbershop</h2>\n";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='success'>✅ <strong>Conexão estabelecida com sucesso!</strong></div>\n";
    
    // Verificar tabelas existentes
    echo "<h2>📋 Verificação de Tabelas</h2>\n";
    
    $stmt = $pdo->query("SHOW TABLES");
    $existing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<div class='info'><strong>Tabelas existentes:</strong><br>";
    foreach ($existing_tables as $table) {
        echo "• " . htmlspecialchars($table) . "<br>";
    }
    echo "</div>\n";
    
    // Verificar se tabelas necessárias existem
    $required_tables = ['usuarios', 'admin'];
    $missing_tables = [];
    
    foreach ($required_tables as $table) {
        if (!in_array($table, $existing_tables)) {
            $missing_tables[] = $table;
        }
    }
    
    if (empty($missing_tables)) {
        echo "<div class='success'>✅ <strong>Todas as tabelas necessárias já existem!</strong></div>\n";
    } else {
        echo "<div class='warning'>⚠️ <strong>Tabelas em falta:</strong> " . implode(', ', $missing_tables) . "</div>\n";
        
        echo "<h2>🔨 Criando Tabelas Faltantes</h2>\n";
        
        // Criar tabela usuarios se não existir
        if (in_array('usuarios', $missing_tables)) {
            echo "<div class='info'>📝 Criando tabela 'usuarios'...</div>\n";
            
            $sql_usuarios = "
                CREATE TABLE usuarios (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nome VARCHAR(100) NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    telefone VARCHAR(20),
                    password VARCHAR(255) NOT NULL,
                    data_nascimento DATE,
                    data_registo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    ultimo_login TIMESTAMP NULL,
                    ativo BOOLEAN DEFAULT TRUE,
                    notas TEXT,
                    type ENUM('cliente') DEFAULT 'cliente',
                    
                    INDEX idx_email (email),
                    INDEX idx_nome (nome),
                    INDEX idx_data_registo (data_registo),
                    INDEX idx_type (type)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            
            $pdo->exec($sql_usuarios);
            echo "<div class='success'>✅ Tabela 'usuarios' criada com sucesso!</div>\n";
            
            // Inserir dados de exemplo
            echo "<div class='info'>📝 Inserindo usuários de exemplo...</div>\n";
            
            $senha_hash = password_hash('123456', PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO usuarios (nome, email, password, telefone, type) VALUES
                (?, ?, ?, ?, 'cliente')
            ");
            
            $usuarios_exemplo = [
                ['João Silva', 'joao.silva@email.com', '912345678'],
                ['Maria Santos', 'maria.santos@email.com', '913456789'],
                ['Pedro Costa', 'pedro.costa@email.com', '914567890']
            ];
            
            foreach ($usuarios_exemplo as $usuario) {
                $stmt->execute([$usuario[0], $usuario[1], $senha_hash, $usuario[2]]);
            }
            
            echo "<div class='success'>✅ 3 usuários de exemplo inseridos (senha: 123456)</div>\n";
        }
        
        // Criar tabela admin se não existir
        if (in_array('admin', $missing_tables)) {
            echo "<div class='info'>📝 Criando tabela 'admin'...</div>\n";
            
            $sql_admin = "
                CREATE TABLE admin (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nome VARCHAR(100) NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    nivel_acesso ENUM('super_admin', 'admin', 'gestor') DEFAULT 'admin',
                    ativo BOOLEAN DEFAULT TRUE,
                    ultimo_login TIMESTAMP NULL,
                    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    criado_por INT,
                    
                    INDEX idx_email (email),
                    INDEX idx_nivel (nivel_acesso),
                    INDEX idx_ativo (ativo)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            
            $pdo->exec($sql_admin);
            echo "<div class='success'>✅ Tabela 'admin' criada com sucesso!</div>\n";
            
            // Inserir admin de exemplo
            echo "<div class='info'>📝 Inserindo admin de exemplo...</div>\n";
            
            $admin_senha = password_hash('admin123', PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO admin (nome, email, password, nivel_acesso) VALUES
                (?, ?, ?, ?)
            ");
            
            $stmt->execute(['Administrador Sistema', 'admin@mrcarlosbarbershop.pt', $admin_senha, 'super_admin']);
            $stmt->execute(['Gestor Loja', 'gestor@mrcarlosbarbershop.pt', $admin_senha, 'admin']);
            
            echo "<div class='success'>✅ 2 administradores inseridos (senha: admin123)</div>\n";
        }
    }
    
    // Verificação final
    echo "<h2>🔍 Verificação Final</h2>\n";
    
    $tables_to_check = ['usuarios', 'barbeiros', 'servicos', 'agendamentos', 'admin'];
    
    foreach ($tables_to_check as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM {$table}");
            $count = $stmt->fetchColumn();
            echo "<div class='success'>✅ Tabela <strong>{$table}</strong>: {$count} registros</div>\n";
        } catch (Exception $e) {
            echo "<div class='error'>❌ Tabela <strong>{$table}</strong>: " . htmlspecialchars($e->getMessage()) . "</div>\n";
        }
    }
    
    echo "<h2>🎯 Resumo das Contas de Teste</h2>\n";
    echo "<div class='info'>\n";
    echo "<strong>Clientes (Tabela: usuarios)</strong><br>\n";
    echo "• Email: joao.silva@email.com | Senha: 123456<br>\n";
    echo "• Email: maria.santos@email.com | Senha: 123456<br>\n";
    echo "• Email: pedro.costa@email.com | Senha: 123456<br>\n";
    echo "<br>\n";
    echo "<strong>Administradores (Tabela: admin)</strong><br>\n";
    echo "• Email: admin@mrcarlosbarbershop.pt | Senha: admin123<br>\n";
    echo "• Email: gestor@mrcarlosbarbershop.pt | Senha: admin123<br>\n";
    echo "<br>\n";
    echo "<strong>Barbeiros (Tabela: barbeiros)</strong><br>\n";
    echo "• Verificar com: SELECT nome, email FROM barbeiros;<br>\n";
    echo "</div>\n";
    
    echo "<div class='success'>\n";
    echo "<h3 style='margin: 0; color: #27ae60;'>🎉 Base de Dados Reparada com Sucesso!</h3>\n";
    echo "<p>Todas as tabelas necessárias foram criadas e populadas com dados de teste.</p>\n";
    echo "<p><strong>Próximos passos:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>Testar login com as contas criadas</li>\n";
    echo "<li>Alterar senhas em produção</li>\n";
    echo "<li>Configurar dados reais dos barbeiros</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<div class='error'>\n";
    echo "<strong>❌ Erro:</strong> " . htmlspecialchars($e->getMessage()) . "\n";
    echo "</div>\n";
}

echo "<div style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;'>\n";
echo "<p><a href='teste_sistema_completo.php' style='color: #3498db; text-decoration: none;'>🔄 Executar Teste Completo Novamente</a></p>\n";
echo "<p><a href='pages/login.php' style='color: #27ae60; text-decoration: none;'>🔐 Testar Login</a></p>\n";
echo "<p><a href='index.php' style='color: #e67e22; text-decoration: none;'>🏠 Voltar à Homepage</a></p>\n";
echo "</div>\n";

echo "</div>\n";
echo "</body>\n</html>\n";
?>