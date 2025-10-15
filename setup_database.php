<?php
/**
 * Script para criar banco de dados - Mr. Carlos Barbershop
 * Execute este arquivo uma vez para criar o banco de dados inicial
 */

// Configurações de conexão
$host = 'localhost';
$user = 'root';
$pass = '';
$database = 'mr_carlos_barbershop';

echo "=== Setup do Banco de Dados - Mr. Carlos Barbershop ===\n\n";

try {
    // Conectar sem especificar banco para criar o banco
    echo "1. Conectando ao MySQL...\n";
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Conexão estabelecida com sucesso!\n\n";

    // Criar banco de dados
    echo "2. Criando banco de dados '$database'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Banco de dados criado com sucesso!\n\n";

    // Usar o banco criado
    $pdo->exec("USE `$database`");

    // Verificar se já existem tabelas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($tables) > 0) {
        echo "3. Banco já contém " . count($tables) . " tabelas:\n";
        foreach ($tables as $table) {
            echo "   - $table\n";
        }
        echo "\n✓ Banco já está configurado!\n\n";
    } else {
        echo "3. Executando schema SQL...\n";
        
        // Ler arquivo do schema
        $schema_file = __DIR__ . '/database/schema.sql';
        
        if (!file_exists($schema_file)) {
            throw new Exception("Arquivo schema.sql não encontrado em: $schema_file");
        }
        
        $sql = file_get_contents($schema_file);
        
        if (empty($sql)) {
            throw new Exception("Arquivo schema.sql está vazio");
        }
        
        // Executar SQL (dividir por ; para executar comando por comando)
        $statements = explode(';', $sql);
        $executed = 0;
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                try {
                    $pdo->exec($statement);
                    $executed++;
                } catch (PDOException $e) {
                    // Ignorar erros de "table already exists" etc.
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        echo "Erro ao executar: " . substr($statement, 0, 50) . "...\n";
                        echo "Erro: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
        
        echo "✓ $executed comandos SQL executados com sucesso!\n\n";
        
        // Verificar tabelas criadas
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "4. Tabelas criadas:\n";
        foreach ($tables as $table) {
            echo "   - $table\n";
        }
        echo "\n";
    }

    echo "=== SETUP CONCLUÍDO COM SUCESSO! ===\n";
    echo "Agora você pode acessar o sistema em:\n";
    echo "http://localhost/mr-carlos-barbershop/\n\n";

} catch (PDOException $e) {
    echo "❌ Erro de conexão: " . $e->getMessage() . "\n";
    echo "\nVerifique se:\n";
    echo "- O MySQL/MariaDB está rodando\n";
    echo "- As credenciais estão corretas\n";
    echo "- O usuário 'root' tem permissões adequadas\n\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n\n";
    exit(1);
}