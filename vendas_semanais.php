<?php
session_start();
require_once(__DIR__ . '/config/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Obter vendas da última semana
$stmt = $db->prepare("
    SELECT v.id AS venda_id, v.data_venda, u.nome AS funcionario, v.total, v.forma_pagamento 
    FROM vendas v 
    JOIN usuarios u ON v.usuario_id = u.id 
    WHERE v.data_venda >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ORDER BY v.data_venda DESC
");
$stmt->execute();
$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar produtos por venda
$vendaItens = [];
foreach ($vendas as $venda) {
    $stmtItens = $db->prepare("
        SELECT p.nome AS produto, vi.quantidade 
        FROM venda_itens vi 
        LEFT JOIN produtos p ON vi.produto_id = p.id
        WHERE vi.venda_id = ?
    ");
    $stmtItens->execute([$venda['venda_id']]);
    $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
    $produtos = [];
    foreach ($itens as $item) {
        $nome = $item['produto'] ?? 'Produto removido';
        $produtos[] = "$nome ({$item['quantidade']})";
    }
    $vendaItens[$venda['venda_id']] = implode(', ', $produtos);
}

$total_geral = array_sum(array_column($vendas, 'total'));
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Vendas Semanais</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #1f2937;
        }

        .voltar {
            display: inline-block;
            margin-bottom: 20px;
            background: #10b981;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .voltar:hover {
            background: #059669;
        }

        .print-btn, .excel-btn {
            float: right;
            margin-left: 10px;
            background: #2563eb;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .print-btn:hover, .excel-btn:hover {
            background: #1d4ed8;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background-color: #f9fafb;
            color: #111827;
        }

        tr:hover {
            background-color: #f1f5f9;
        }

        .total-final {
            text-align: right;
            font-weight: bold;
            padding: 15px;
            margin-top: 10px;
            font-size: 18px;
            color: #111827;
        }
    </style>
</head>
<body>

<a href="dashboard_admin.php" class="voltar">← Voltar ao Dashboard</a>
<button class="print-btn" onclick="window.print()">🖨️ Imprimir</button>
<button class="excel-btn" onclick="exportToExcel()">📥 Exportar Excel</button>

<h2>Vendas da Última Semana</h2>

<?php if (count($vendas) > 0): ?>
    <table id="tabela-vendas">
        <thead>
            <tr>
                <th>#</th>
                <th>Funcionário</th>
                <th>Valor Total</th>
                <th>Forma de Pagamento</th>
                <th>Data</th>
                <th>Produtos Vendidos</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vendas as $v): ?>
                <tr>
                    <td><?= $v['venda_id'] ?></td>
                    <td><?= htmlspecialchars($v['funcionario']) ?></td>
                    <td><?= number_format($v['total'], 2) ?> MT</td>
                    <td><?= $v['forma_pagamento'] ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($v['data_venda'])) ?></td>
                    <td><?= htmlspecialchars($vendaItens[$v['venda_id']] ?: '-') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="total-final">
        Total da Semana: <?= number_format($total_geral, 2) ?> MT
    </div>
<?php else: ?>
    <p style="text-align: center; color: #6b7280;">Nenhuma venda foi realizada na última semana.</p>
<?php endif; ?>

<script>
function exportToExcel() {
    var table = document.getElementById("tabela-vendas").outerHTML;
    var dataType = 'application/vnd.ms-excel';
    var blob = new Blob(['\ufeff', table], { type: dataType });

    var a = document.createElement("a");
    a.href = URL.createObjectURL(blob);
    a.download = 'vendas_semanal.xls';
    a.click();
}
</script>

</body>
</html>

