<?php
/**
 * Conexão com a Base de Dados - Mr. Carlos Barbershop
 * 
 * Este arquivo estabelece a conexão PDO com MySQL/MariaDB
 * e configura as definições necessárias para o sistema
 * 
 * @author Sistema Mr. Carlos Barbershop
 * @version 1.0
 * @since 2025-10-14
 */

// Verificar se as constantes estão definidas
if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
    die('Configurações de banco de dados não definidas. Verifique o arquivo config.php');
}

try {
    // Criar conexão PDO
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    );
    
    // Configurar timezone do MySQL
    $pdo->exec("SET time_zone = '" . TIMEZONE_OFFSET . "'");
    
    // Configurar variáveis de sessão do MySQL para performance
    $pdo->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
    
} catch (PDOException $e) {
    // Log do erro (não expor detalhes em produção)
    error_log("Erro de conexão com banco de dados: " . $e->getMessage());
    
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        die("Erro de conexão com o banco: " . $e->getMessage());
    } else {
        die("Erro interno do sistema. Tente novamente em alguns minutos.");
    }
}

/**
 * Função para executar queries com tratamento de erro
 */
function executeQuery($sql, $params = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Erro na query: " . $e->getMessage() . " | SQL: " . $sql);
        throw $e;
    }
}

/**
 * Função para obter um único resultado
 */
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch();
}

/**
 * Função para obter múltiplos resultados
 */
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Função para obter apenas uma coluna
 */
function fetchColumn($sql, $params = [], $column = 0) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchColumn($column);
}

/**
 * Função para inserção com retorno do ID
 */
function insertAndGetId($sql, $params = []) {
    global $pdo;
    
    $stmt = executeQuery($sql, $params);
    return $pdo->lastInsertId();
}

/**
 * Verificar se a conexão está ativa
 */
function checkDatabaseConnection() {
    global $pdo;
    
    try {
        $pdo->query('SELECT 1');
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Verificar se a conexão foi bem-sucedida
if (!checkDatabaseConnection()) {
    error_log("Falha na verificação da conexão com banco de dados");
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        die("Não foi possível conectar ao banco de dados");
    } else {
        die("Erro interno do sistema. Tente novamente em alguns minutos.");
    }
}