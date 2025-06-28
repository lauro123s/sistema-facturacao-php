<?php
session_start();
require_once(__DIR__ . '/config/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Total de vendas por dia
$stmtVendas = $db->query("
    SELECT DATE(data_venda) AS dia, COUNT(*) AS num_vendas, SUM(total) AS total_vendido
    FROM vendas
    GROUP BY dia
    ORDER BY dia DESC
");
$vendasPorDia = $stmtVendas->fetchAll(PDO::FETCH_ASSOC);

// Produtos mais vendidos (top 10)
$stmtProdutos = $db->query("
    SELECT p.nome, SUM(vi.quantidade) AS total_vendido
    FROM venda_itens vi
    JOIN produtos p ON vi.produto_id = p.id
    GROUP BY vi.produto_id
    ORDER BY total_vendido DESC
    LIMIT 10
");
$produtosMaisVendidos = $stmtProdutos->fetchAll(PDO::FETCH_ASSOC);

// Todos os produtos vendidos com valor arrecadado
$stmtResumoProdutos = $db->query("
    SELECT 
        p.nome, 
        SUM(vi.quantidade) AS quantidade_total, 
        SUM(vi.quantidade * p.preco) AS total_arrecadado
    FROM venda_itens vi
    JOIN produtos p ON vi.produto_id = p.id
    GROUP BY vi.produto_id
    ORDER BY total_arrecadado DESC
");
$resumoProdutosVendidos = $stmtResumoProdutos->fetchAll(PDO::FETCH_ASSOC);

// Entradas (estoque atual)
$stmtEntradas = $db->query("
    SELECT nome, estoque, id AS entrada_id
    FROM produtos
    ORDER BY entrada_id DESC
");

// Total geral vendido
$stmtTotal = $db->query("SELECT SUM(total) AS total_geral FROM vendas");
$totalGeral = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total_geral'];
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Relatório Geral</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f9f9f9; }
        h1, h2 { color: #1f2937; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #1f2937; color: #fff; }
        .buttons { margin-top: 30px; display: flex; gap: 10px; }
        .buttons a, .buttons button {
            padding: 10px 15px;
            background: #1f2937;
            color: #fff;
            border: none;
            text-decoration: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .buttons a:hover, .buttons button:hover {
            background: #374151;
        }
    </style>
</head>
<body>
    <h1>Relatório Geral de Vendas</h1>

    <div class="buttons">
        <button onclick="window.print()">🖨️ Imprimir</button>
        <a href="exportar_excel.php">📥 Exportar Excel</a>
        <a href="dashboard_admin.php">🔙 Voltar</a>
    </div>

    <h2>Total Geral Vendido: <?= number_format($totalGeral, 2, ',', '.') ?> MZN</h2>

    <h2>Vendas por Dia</h2>
    <table>
        <tr><th>Data</th><th>Quantidade de Vendas</th><th>Total Vendido (MZN)</th></tr>
        <?php foreach ($vendasPorDia as $linha): ?>
        <tr>
            <td><?= $linha['dia'] ?></td>
            <td><?= $linha['num_vendas'] ?></td>
            <td><?= number_format($linha['total_vendido'], 2, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Top 10 Produtos Mais Vendidos</h2>
    <table>
        <tr><th>Produto</th><th>Quantidade Vendida</th></tr>
        <?php foreach ($produtosMaisVendidos as $produto): ?>
        <tr>
            <td><?= htmlspecialchars($produto['nome']) ?></td>
            <td><?= $produto['total_vendido'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Resumo de Todos os Produtos Vendidos</h2>
    <table>
        <tr>
            <th>Produto</th>
            <th>Quantidade Vendida</th>
            <th>Valor Arrecadado (MZN)</th>
        </tr>
        <?php foreach ($resumoProdutosVendidos as $produto): ?>
        <tr>
            <td><?= htmlspecialchars($produto['nome']) ?></td>
            <td><?= $produto['quantidade_total'] ?></td>
            <td><?= number_format($produto['total_arrecadado'], 2, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Entradas de Produtos (Estoque Atual)</h2>
    <table>
        <tr><th>Produto</th><th>Quantidade Atual</th><th>Data Aproximada da Entrada</th></tr>
        <?php foreach ($stmtEntradas as $entrada): ?>
        <tr>
            <td><?= htmlspecialchars($entrada['nome']) ?></td>
            <td><?= $entrada['estoque'] ?></td>
            <td><?= date('Y-m-d', strtotime("-" . (100 - $entrada['entrada_id']) . " days")) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>


