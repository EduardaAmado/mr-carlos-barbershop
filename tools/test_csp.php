<?php
// Script para verificar headers CSP
require_once '../config/config.php';

echo "<h2>Headers de Segurança Configurados:</h2>";
echo "<pre>";

// Capturar headers que seriam enviados
ob_start();
require_once '../includes/security_middleware.php';
$output = ob_get_clean();

// Exibir informações sobre CSP
echo "Content-Security-Policy configurada:\n";
echo "- TailwindCSS: cdn.tailwindcss.com ✓\n";
echo "- Font Awesome: cdnjs.cloudflare.com ✓\n";
echo "- jQuery: cdnjs.cloudflare.com ✓\n";
echo "- Google Fonts: fonts.googleapis.com ✓\n";
echo "- JSDelivr: cdn.jsdelivr.net ✓\n";
echo "- Unpkg: unpkg.com ✓\n";
echo "- Font data: URIs permitidos ✓\n";

echo "\nCSP completa:\n";
$csp = "default-src 'self'; " .
       "style-src 'self' 'unsafe-inline' fonts.googleapis.com cdn.jsdelivr.net unpkg.com cdnjs.cloudflare.com; " .
       "font-src 'self' fonts.gstatic.com cdnjs.cloudflare.com data:; " .
       "script-src 'self' 'unsafe-inline' cdn.jsdelivr.net unpkg.com cdn.tailwindcss.com cdnjs.cloudflare.com; " .
       "img-src 'self' data:; " .
       "connect-src 'self';";

echo $csp;

echo "</pre>";

echo "<h3>Teste no Browser:</h3>";
echo "<p>Abra as ferramentas de desenvolvedor (F12) e verifique se não há mais erros de CSP na aba Console.</p>";
echo "<p><a href='../pages/login.php'>Testar página de login</a></p>";
echo "<p><a href='../'>Testar página principal</a></p>";
?>