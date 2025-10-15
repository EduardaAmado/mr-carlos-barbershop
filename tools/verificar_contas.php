<?php
/**
 * Script para verificar as contas criadas
 */

require_once __DIR__ . '/../config/config.php';

echo "<h2>📊 Verificação das Contas Criadas</h2>\n";

try {
    // Verificar clientes
    echo "<h3>👤 Clientes</h3>\n";
    $stmt = $pdo->query("SELECT id, nome, email, telefone, ativo FROM clientes ORDER BY id");
    $clientes = $stmt->fetchAll();
    
    if ($clientes) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Telefone</th><th>Ativo</th></tr>\n";
        foreach ($clientes as $cliente) {
            $ativo = $cliente['ativo'] ? 'Sim' : 'Não';
            echo "<tr><td>{$cliente['id']}</td><td>{$cliente['nome']}</td><td>{$cliente['email']}</td><td>{$cliente['telefone']}</td><td>{$ativo}</td></tr>\n";
        }
        echo "</table><br>\n";
    } else {
        echo "Nenhum cliente encontrado.<br><br>\n";
    }

    // Verificar barbeiros  
    echo "<h3>✂️ Barbeiros</h3>\n";
    $stmt = $pdo->query("SELECT id, nome, email, telefone, especialidades, ativo FROM barbeiros ORDER BY id");
    $barbeiros = $stmt->fetchAll();
    
    if ($barbeiros) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Telefone</th><th>Especialidades</th><th>Ativo</th></tr>\n";
        foreach ($barbeiros as $barbeiro) {
            $ativo = $barbeiro['ativo'] ? 'Sim' : 'Não';
            echo "<tr><td>{$barbeiro['id']}</td><td>{$barbeiro['nome']}</td><td>{$barbeiro['email']}</td><td>{$barbeiro['telefone']}</td><td>{$barbeiro['especialidades']}</td><td>{$ativo}</td></tr>\n";
        }
        echo "</table><br>\n";
    } else {
        echo "Nenhum barbeiro encontrado.<br><br>\n";
    }

    // Verificar admins
    echo "<h3>🛡️ Administradores</h3>\n";
    $stmt = $pdo->query("SELECT id, nome, email, nivel_acesso, ativo FROM admins ORDER BY id");
    $admins = $stmt->fetchAll();
    
    if ($admins) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Nível</th><th>Ativo</th></tr>\n";
        foreach ($admins as $admin) {
            $ativo = $admin['ativo'] ? 'Sim' : 'Não';
            echo "<tr><td>{$admin['id']}</td><td>{$admin['nome']}</td><td>{$admin['email']}</td><td>{$admin['nivel_acesso']}</td><td>{$ativo}</td></tr>\n";
        }
        echo "</table><br>\n";
    } else {
        echo "Nenhum admin encontrado.<br><br>\n";
    }

    // Verificar serviços
    echo "<h3>🎯 Serviços</h3>\n";
    $stmt = $pdo->query("SELECT id, nome, preco, duracao_minutos, categoria, ativo FROM servicos ORDER BY ordem_exibicao");
    $servicos = $stmt->fetchAll();
    
    if ($servicos) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>ID</th><th>Nome</th><th>Preço</th><th>Duração</th><th>Categoria</th><th>Ativo</th></tr>\n";
        foreach ($servicos as $servico) {
            $ativo = $servico['ativo'] ? 'Sim' : 'Não';
            echo "<tr><td>{$servico['id']}</td><td>{$servico['nome']}</td><td>€{$servico['preco']}</td><td>{$servico['duracao_minutos']} min</td><td>{$servico['categoria']}</td><td>{$ativo}</td></tr>\n";
        }
        echo "</table><br>\n";
    } else {
        echo "Nenhum serviço encontrado.<br><br>\n";
    }

    // Resumo estatístico
    echo "<h3>📈 Resumo</h3>\n";
    echo "<ul>\n";
    echo "<li><strong>Total de Clientes:</strong> " . count($clientes) . "</li>\n";
    echo "<li><strong>Total de Barbeiros:</strong> " . count($barbeiros) . "</li>\n";
    echo "<li><strong>Total de Admins:</strong> " . count($admins) . "</li>\n";
    echo "<li><strong>Total de Serviços:</strong> " . count($servicos) . "</li>\n";
    echo "</ul>\n";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erro na base de dados: " . $e->getMessage() . "</p>\n";
}
?>