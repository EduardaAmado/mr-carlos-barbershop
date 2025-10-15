<?php
// API TESTE SIMPLIFICADA
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../includes/helpers.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!is_logged_in('cliente')) {
        echo json_encode(['success' => false, 'message' => 'Não autorizado']);
        exit;
    }

    // SUCESSO TEMPORÁRIO
    echo json_encode(['success' => true, 'message' => 'API funcionando - versão teste']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(['success' => false, 'message' => 'Fatal: ' . $e->getMessage()]);
}
?>