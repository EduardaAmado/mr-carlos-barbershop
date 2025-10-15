<?php
/**
 * Verificar e Atualizar Senhas dos Barbeiros
 * Mr. Carlos Barbershop
 */

require_once __DIR__ . '/config/config.php';

echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n";
echo "<meta charset='UTF-8'>\n";
echo "<title>Verificar Senhas Barbeiros</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }\n";
echo ".container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }\n";
echo ".success { color: #27ae60; padding: 10px; background: #d5f4e6; border-radius: 5px; margin: 10px 0; }\n";
echo ".error { color: #e74c3c; padding: 10px; background: #fdf2f2; border-radius: 5px; margin: 10px 0; }\n";
echo ".info { color: #3498db; padding: 10px; background: #ebf3fd; border-radius: 5px; margin: 10px 0; }\n";
echo ".warning { color: #f39c12; padding: 10px; background: #fef9e7; border-radius: 5px; margin: 10px 0; }\n";
echo "h1 { color: #2c3e50; text-align: center; }\n";
echo "h2 { color: #34495e; border-bottom: 2px solid #C9A227; padding-bottom: 10px; }\n";
echo "table { width: 100%; border-collapse: collapse; margin: 15px 0; }\n";
echo "th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }\n";
echo "th { background-color: #f8f9fa; font-weight: bold; }\n";
echo ".btn { display: inline-block; padding: 8px 16px; background: #C9A227; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }\n";
echo ".btn:hover { background: #B8921F; }\n";
echo "</style>\n";
echo "</head>\n<body>\n";

echo "<div class='container'>\n";
echo "<h1>👥 Gerenciamento de Barbeiros</h1>\n";
echo "<h2>Mr. Carlos Barbershop</h2>\n";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='success'>✅ <strong>Conexão com banco estabelecida!</strong></div>\n";
    
    // Verificar barbeiros existentes
    echo "<h2>📋 Barbeiros Cadastrados</h2>\n";
    
    $stmt = $pdo->query("SELECT id, nome, email, password_hash, especialidades, ativo FROM barbeiros ORDER BY nome");
    $barbeiros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($barbeiros)) {
        echo "<div class='warning'>⚠️ <strong>Nenhum barbeiro encontrado!</strong></div>\n";
        echo "<div class='info'>Vamos criar alguns barbeiros de exemplo...</div>\n";
        
        // Criar barbeiros de exemplo
        $senha_hash = password_hash('barber123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO barbeiros (nome, email, password_hash, especialidades, ativo, data_contratacao, biografia) 
            VALUES (?, ?, ?, ?, 1, CURDATE(), ?)
        ");
        
        $barbeiros_exemplo = [
            [
                'Carlos Silva', 
                'carlos@mrcarlosbarbershop.pt', 
                $senha_hash,
                'Cortes clássicos, Barbas tradicionais, Bigodes',
                'Fundador da barbearia com mais de 35 anos de experiência em cortes clássicos e tradicionais.'
            ],
            [
                'João Santos', 
                'joao@mrcarlosbarbershop.pt', 
                $senha_hash,
                'Cortes modernos, Fade, Desenhos criativos',
                'Especialista em técnicas modernas e tendências atuais. Expert em cortes fade e desenhos.'
            ],
            [
                'Miguel Pereira', 
                'miguel@mrcarlosbarbershop.pt', 
                $senha_hash,
                'Barbas completas, Tratamentos premium',
                'Master em tratamentos de barba e cuidados masculinos premium.'
            ]
        ];
        
        foreach ($barbeiros_exemplo as $barbeiro) {
            $stmt->execute($barbeiro);
        }
        
        echo "<div class='success'>✅ 3 barbeiros de exemplo criados com sucesso!</div>\n";
        
        // Recarregar lista
        $stmt = $pdo->query("SELECT id, nome, email, password_hash, especialidades, ativo FROM barbeiros ORDER BY nome");
        $barbeiros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo "<table>\n";
    echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Especialidades</th><th>Status</th><th>Senha</th></tr>\n";
    
    foreach ($barbeiros as $barbeiro) {
        $status = $barbeiro['ativo'] ? '<span style="color: green;">✅ Ativo</span>' : '<span style="color: red;">❌ Inativo</span>';
        $senha_ok = !empty($barbeiro['password_hash']) && strlen($barbeiro['password_hash']) > 10;
        $senha_status = $senha_ok ? '<span style="color: green;">✅ OK</span>' : '<span style="color: red;">❌ Inválida</span>';
        
        echo "<tr>\n";
        echo "<td>" . htmlspecialchars($barbeiro['id']) . "</td>\n";
        echo "<td><strong>" . htmlspecialchars($barbeiro['nome']) . "</strong></td>\n";
        echo "<td>" . htmlspecialchars($barbeiro['email']) . "</td>\n";
        echo "<td>" . htmlspecialchars($barbeiro['especialidades'] ?? 'Não informado') . "</td>\n";
        echo "<td>{$status}</td>\n";
        echo "<td>{$senha_status}</td>\n";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
    
    // Verificar e corrigir senhas inválidas
    echo "<h2>🔒 Verificação de Senhas</h2>\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM barbeiros WHERE password_hash IS NULL OR password_hash = '' OR password_hash = '\$2y\$10\$example_hash_placeholder'");
    $senhas_invalidas = $stmt->fetchColumn();
    
    if ($senhas_invalidas > 0) {
        echo "<div class='warning'>⚠️ Encontradas {$senhas_invalidas} senhas inválidas. Corrigindo...</div>\n";
        
        $nova_senha = password_hash('barber123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            UPDATE barbeiros 
            SET password_hash = ? 
            WHERE password_hash IS NULL 
               OR password_hash = '' 
               OR password_hash = '\$2y\$10\$example_hash_placeholder'
        ");
        
        $stmt->execute([$nova_senha]);
        
        echo "<div class='success'>✅ Senhas corrigidas! Nova senha para todos: <strong>barber123</strong></div>\n";
    } else {
        echo "<div class='success'>✅ Todas as senhas estão válidas!</div>\n";
    }
    
    // Resumo das contas
    echo "<h2>🎯 Resumo das Contas de Acesso</h2>\n";
    
    echo "<div class='info'>\n";
    echo "<h3>👥 Barbeiros (Dashboard: /barbeiro/dashboard.php)</h3>\n";
    
    $stmt = $pdo->query("SELECT nome, email FROM barbeiros WHERE ativo = 1 ORDER BY nome");
    $barbeiros_ativos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($barbeiros_ativos as $barbeiro) {
        echo "• <strong>" . htmlspecialchars($barbeiro['nome']) . "</strong><br>\n";
        echo "  Email: " . htmlspecialchars($barbeiro['email']) . " | Senha: barber123<br><br>\n";
    }
    echo "</div>\n";
    
    echo "<div class='info'>\n";
    echo "<h3>👤 Clientes (Área do Cliente: /pages/perfil.php)</h3>\n";
    
    try {
        $stmt = $pdo->query("SELECT nome, email FROM usuarios WHERE ativo = 1 ORDER BY nome LIMIT 3");
        $usuarios_ativos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($usuarios_ativos as $usuario) {
            echo "• <strong>" . htmlspecialchars($usuario['nome']) . "</strong><br>\n";
            echo "  Email: " . htmlspecialchars($usuario['email']) . " | Senha: 123456<br><br>\n";
        }
    } catch (Exception $e) {
        echo "<span style='color: red;'>Erro ao carregar usuários: " . htmlspecialchars($e->getMessage()) . "</span><br>\n";
    }
    echo "</div>\n";
    
    echo "<div class='info'>\n";
    echo "<h3>🛡️ Administradores (Admin Panel: /admin/index.php)</h3>\n";
    
    try {
        $stmt = $pdo->query("SELECT nome, email, nivel_acesso FROM admin WHERE ativo = 1 ORDER BY nome");
        $admins_ativos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($admins_ativos as $admin) {
            echo "• <strong>" . htmlspecialchars($admin['nome']) . "</strong><br>\n";
            echo "  Email: " . htmlspecialchars($admin['email']) . " | Senha: admin123 | Nível: " . htmlspecialchars($admin['nivel_acesso']) . "<br><br>\n";
        }
    } catch (Exception $e) {
        echo "<span style='color: red;'>Erro ao carregar admins: " . htmlspecialchars($e->getMessage()) . "</span><br>\n";
    }
    echo "</div>\n";
    
    echo "<div class='success'>\n";
    echo "<h3 style='margin: 0; color: #27ae60;'>🎉 Sistema Totalmente Configurado!</h3>\n";
    echo "<p>Todos os barbeiros têm senhas válidas e o sistema está pronto para uso.</p>\n";
    echo "<p><strong>⚠️ IMPORTANTE:</strong> Em produção, altere todas as senhas padrão!</p>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<div class='error'>\n";
    echo "<strong>❌ Erro:</strong> " . htmlspecialchars($e->getMessage()) . "\n";
    echo "</div>\n";
}

echo "<div style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;'>\n";
echo "<a href='pages/login.php' class='btn'>🔐 Testar Login</a>\n";
echo "<a href='barbeiro/login.php' class='btn'>👨‍💼 Login Barbeiro</a>\n";
echo "<a href='admin/login.php' class='btn'>🛡️ Login Admin</a>\n";
echo "<a href='index.php' class='btn'>🏠 Homepage</a>\n";
echo "</div>\n";

echo "</div>\n";
echo "</body>\n</html>\n";
?>