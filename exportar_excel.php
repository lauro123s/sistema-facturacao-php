<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=relatorio_geral.xls");

echo "Data\tNº de Vendas\tTotal Vendido (MZN)\n";

// Conexão
require_once(__DIR__ . '/config/config.php');
$stmt = $db->query("
    SELECT DATE(data_venda) AS dia, COUNT(*) AS num_vendas, SUM(total) AS total_vendido
    FROM vendas
    GROUP BY dia
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['dia'] . "\t" . $row['num_vendas'] . "\t" . $row['total_vendido'] . "\n";
}
?>
