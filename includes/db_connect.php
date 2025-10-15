<?php
/**
 * Ficheiro de conexão à base de dados
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Estabelecer conexão segura com MySQL usando mysqli
 */

// Configurar relatórios de erro do mysqli para modo de exceções
mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR);

/**
 * Função para obter conexão à base de dados
 * @return mysqli Objeto de conexão mysqli
 * @throws Exception Se falhar a conexão
 */
function get_db_connection() {
    try {
        // Criar nova conexão mysqli
        $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Definir charset para UTF-8
        $connection->set_charset("utf8mb4");
        
        return $connection;
        
    } catch (mysqli_sql_exception $e) {
        // Log do erro (não expor detalhes ao utilizador)
        error_log("Erro de conexão à base de dados: " . $e->getMessage());
        
        // Mensagem genérica para o utilizador
        throw new Exception("Erro interno do servidor. Tente novamente mais tarde.");
    }
}

/**
 * Função para fechar conexão de forma segura
 * @param mysqli $connection Conexão a fechar
 */
function close_db_connection($connection) {
    if ($connection && !$connection->connect_error) {
        $connection->close();
    }
}

/**
 * Função para executar query preparada de forma segura
 * @param string $query Query SQL com placeholders (?)
 * @param array $params Parâmetros para bind
 * @param string $types Tipos dos parâmetros (s=string, i=integer, d=double, b=blob)
 * @return mysqli_result|bool Resultado da query
 */
function execute_prepared_query($query, $params = [], $types = '') {
    $connection = get_db_connection();

    try {
        $stmt = $connection->prepare($query);

        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        // Se é SELECT, retorna resultado; caso contrário, retorna boolean
        if (stripos($query, 'SELECT') === 0) {
            $result = $stmt->get_result();
            $stmt->close();
            close_db_connection($connection);
            return $result;
        } else {
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            close_db_connection($connection);
            return $affected_rows > 0;
        }
    } catch (mysqli_sql_exception $e) {
        error_log("Erro na query preparada: " . $e->getMessage());
        throw new Exception("Erro ao processar pedido.");
    }
}

/**
 * Função para obter último ID inserido
 * @return int ID do último registo inserido
 */
function get_last_insert_id() {
    $connection = get_db_connection();
    $id = $connection->insert_id;
    close_db_connection($connection);
    return $id;
}