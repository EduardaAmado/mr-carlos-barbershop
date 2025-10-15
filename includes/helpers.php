<?php
/**
 * Funções auxiliares globais
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Funções utilitárias para uso em toda a aplicação
 */

/**
 * Escapar dados para output seguro
 * @param string|null $string String a escapar
 * @return string String escapada
 */
function esc($string) {
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Verificar se utilizador está autenticado
 * @param string $type Tipo de utilizador (cliente, barbeiro, admin)
 * @return bool True se autenticado
 */
function is_logged_in($type = 'cliente') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    switch ($type) {
        case 'cliente':
            return isset($_SESSION['user']) && $_SESSION['user']['type'] === 'cliente';
        case 'barbeiro':
            return isset($_SESSION['barbeiro']);
        case 'admin':
            return isset($_SESSION['admin']);
        default:
            return false;
    }
}

/**
 * Obter dados do utilizador autenticado
 * @param string $type Tipo de utilizador
 * @return array|null Dados do utilizador ou null
 */
function get_logged_user($type = 'cliente') {
    if (!is_logged_in($type)) {
        return null;
    }
    
    switch ($type) {
        case 'cliente':
            return $_SESSION['user'] ?? null;
        case 'barbeiro':
            return $_SESSION['barbeiro'] ?? null;
        case 'admin':
            return $_SESSION['admin'] ?? null;
        default:
            return null;
    }
}

/**
 * Formatar preço em euros
 * @param float $price Preço
 * @return string Preço formatado
 */
function format_price($price) {
    return number_format($price, 2, ',', '.') . '€';
}

/**
 * Formatar data para display
 * @param string $date Data no formato Y-m-d H:i:s
 * @param string $format Formato desejado
 * @return string Data formatada
 */
function format_date($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    
    try {
        $dateTime = new DateTime($date);
        return $dateTime->format($format);
    } catch (Exception $e) {
        return '';
    }
}

/**
 * Formatar data e hora para display português
 * @param string $datetime Data/hora no formato Y-m-d H:i:s
 * @return string Data/hora formatada
 */
function format_datetime_pt($datetime) {
    if (empty($datetime)) return '';
    
    try {
        $dt = new DateTime($datetime);
        $days = [
            'Sunday' => 'Domingo',
            'Monday' => 'Segunda-feira',
            'Tuesday' => 'Terça-feira', 
            'Wednesday' => 'Quarta-feira',
            'Thursday' => 'Quinta-feira',
            'Friday' => 'Sexta-feira',
            'Saturday' => 'Sábado'
        ];
        
        $months = [
            'January' => 'Janeiro', 'February' => 'Fevereiro', 'March' => 'Março',
            'April' => 'Abril', 'May' => 'Maio', 'June' => 'Junho',
            'July' => 'Julho', 'August' => 'Agosto', 'September' => 'Setembro',
            'October' => 'Outubro', 'November' => 'Novembro', 'December' => 'Dezembro'
        ];
        
        $dayName = $days[$dt->format('l')];
        $monthName = $months[$dt->format('F')];
        
        return $dayName . ', ' . $dt->format('j') . ' de ' . $monthName . ' de ' . $dt->format('Y') . ' às ' . $dt->format('H:i');
        
    } catch (Exception $e) {
        return $datetime;
    }
}

/**
 * Gerar mensagens de feedback
 * @param string $message Mensagem
 * @param string $type Tipo (success, error, warning, info)
 * @return string HTML da mensagem
 */
function show_message($message, $type = 'info') {
    $classes = [
        'success' => 'bg-green-100 border border-green-400 text-green-700',
        'error' => 'bg-red-100 border border-red-400 text-red-700',
        'warning' => 'bg-yellow-100 border border-yellow-400 text-yellow-700',
        'info' => 'bg-blue-100 border border-blue-400 text-blue-700'
    ];
    
    $icons = [
        'success' => '✓',
        'error' => '✗',
        'warning' => '⚠',
        'info' => 'ℹ'
    ];
    
    $class = $classes[$type] ?? $classes['info'];
    $icon = $icons[$type] ?? $icons['info'];
    
    return '<div class="' . $class . ' px-4 py-3 rounded mb-4" role="alert">
                <span class="font-bold">' . $icon . '</span>
                <span class="block sm:inline ml-2">' . esc($message) . '</span>
            </div>';
}

/**
 * Verificar se página atual corresponde ao URL
 * @param string $page Nome da página
 * @return bool True se é a página atual
 */
function is_current_page($page) {
    $current = basename($_SERVER['PHP_SELF'], '.php');
    return $current === $page;
}

/**
 * Gerar menu ativo
 * @param string $page Página a verificar
 * @param string $activeClass Classe CSS para item ativo
 * @param string $defaultClass Classe CSS padrão
 * @return string Classe CSS apropriada
 */
function nav_class($page, $activeClass = 'bg-yellow-600 text-black', $defaultClass = 'text-white hover:bg-gray-700') {
    return is_current_page($page) ? $activeClass : $defaultClass;
}

/**
 * Truncar texto
 * @param string $text Texto
 * @param int $length Comprimento máximo
 * @param string $suffix Sufixo (...)
 * @return string Texto truncado
 */
function truncate_text($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Validar email
 * @param string $email Email a validar
 * @return bool True se válido
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validar telefone português
 * @param string $phone Telefone a validar
 * @return bool True se válido
 */
function is_valid_phone($phone) {
    // Remove espaços e caracteres especiais
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    
    // Verifica padrões portugueses: 9XXXXXXXX, +351 9XXXXXXXX, 00351 9XXXXXXXX
    return preg_match('/^(\+351|00351)?[0-9]{9}$/', $phone);
}

/**
 * Gerar slug para URL
 * @param string $text Texto a converter
 * @return string Slug gerado
 */
function generate_slug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

/**
 * Debug seguro (apenas em desenvolvimento)
 * @param mixed $data Dados a mostrar
 * @param bool $die Parar execução
 */
function debug($data, $die = false) {
    if (defined('DEBUG') && DEBUG === true) {
        echo '<pre style="background: #f4f4f4; padding: 10px; border: 1px solid #ccc; margin: 10px 0;">';
        print_r($data);
        echo '</pre>';
        
        if ($die) {
            die();
        }
    }
}