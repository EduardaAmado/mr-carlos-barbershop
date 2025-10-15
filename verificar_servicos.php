<?php
require_once 'config/config.php';
global $pdo;

echo "=== ESTRUTURA DA TABELA SERVICOS ===\n";
$stmt = $pdo->query('DESCRIBE servicos');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . ' (' . $row['Type'] . ')' . PHP_EOL;
}

echo "\n=== CONTEÚDO DA TABELA SERVICOS ===\n";
$stmt = $pdo->query('SELECT * FROM servicos LIMIT 3');
$servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($servicos as $servico) {
    print_r($servico);
}
?>