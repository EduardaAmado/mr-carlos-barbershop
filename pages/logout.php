<?php
/**
 * Página de logout
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Terminar sessão do utilizador
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Verificar se está logado
if (!is_logged_in('cliente')) {
    safe_redirect(get_base_url('pages/login.php'));
}

// Fazer logout
session_unset();
session_destroy();

// Remover cookies de "lembrar-me" se existirem
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Iniciar nova sessão para mensagem
session_start();
$_SESSION['message'] = [
    'text' => 'Sessão terminada com sucesso.',
    'type' => 'success'
];

// Redirecionar para página inicial
safe_redirect(get_base_url());
?>