<?php
/**
 * Logout do Barbeiro - Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop  
 * Data: 15 de Outubro de 2025
 * Finalidade: Encerrar sessão do barbeiro com segurança
 */

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Log de logout se estiver logado
if (is_logged_in('barbeiro')) {
    $barbeiro = get_logged_user('barbeiro');
    error_log("Barbeiro logout: {$barbeiro['email']} (ID: {$barbeiro['id']})");
}

// Destruir sessão completamente
$_SESSION = array();

// Remover cookie de sessão se existir
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir sessão
session_destroy();

// Regenerar ID de sessão para nova sessão
session_start();
session_regenerate_id(true);

// Redirecionar para login unificado
safe_redirect('/mr-carlos-barbershop/pages/login.php');
?>