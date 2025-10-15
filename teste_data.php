<?php
echo "=== TESTE DA FORMATAÇÃO DE DATA ===\n";

$hoje = date('Y-m-d');
echo "Data atual: $hoje\n\n";

// Testar a nova formatação
$dias = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
$meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
$dt = new DateTime($hoje);
$data_formatada = $dias[$dt->format('w')] . ', ' . $dt->format('d') . ' de ' . $meses[intval($dt->format('n'))] . ' de ' . $dt->format('Y');

echo "Formatação nova: $data_formatada\n";

// Testar com uma data específica
$dt_test = new DateTime('2025-10-15');
$data_test = $dias[$dt_test->format('w')] . ', ' . $dt_test->format('d') . ' de ' . $meses[intval($dt_test->format('n'))] . ' de ' . $dt_test->format('Y');
echo "Teste 15/10/2025: $data_test\n";

echo "\n=== TESTE CONCLUÍDO ===\n";
?>