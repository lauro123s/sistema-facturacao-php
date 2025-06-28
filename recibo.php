<?php
require_once(__DIR__ . '/config/config.php');

if (!isset($_GET['id'])) {
    die('ID da venda não especificado.');
}

$venda_id = (int) $_GET['id'];

$stmt = $db->prepare("SELECT v.id, v.total, v.data, v.forma_pagamento, u.nome AS funcionario FROM vendas v JOIN usuarios u ON v.usuario_id = u.id WHERE v.id = ?");
$stmt->execute([$venda_id]);
$venda = $stmt->fetch();

if (!$venda) {
    die('Venda não encontrada.');
}

$stmt = $db->prepare("SELECT p.nome, vi.quantidade, vi.preco_unitario FROM venda_itens vi JOIN produtos p ON vi.produto_id = p.id WHERE vi.venda_id = ?");
$stmt->execute([$venda_id]);
$itens = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Recibo Venda #<?= $venda_id ?></title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ccc; }
        h2, h4 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .totais { text-align: right; margin-top: 20px; font-size: 18px; }
        .assinatura { margin-top: 50px; text-align: right; font-style: italic; }
        .print-btn { margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <h2>Recibo de Venda</h2>
    <h4>Venda #<?= $venda['id'] ?> | <?= date('d/m/Y H:i', strtotime($venda['data'])) ?></h4>
    <p><strong>Funcionário:</strong> <?= htmlspecialchars($venda['funcionario']) ?><br>
       <strong>Forma de Pagamento:</strong> <?= htmlspecialchars($venda['forma_pagamento']) ?></p>

    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Qtd</th>
                <th>Preço Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itens as $item):
                $subtotal = $item['quantidade'] * $item['preco_unitario']; ?>
                <tr>
                    <td><?= htmlspecialchars($item['nome']) ?></td>
                    <td><?= $item['quantidade'] ?></td>
                    <td><?= number_format($item['preco_unitario'], 2) ?> MT</td>
                    <td><?= number_format($subtotal, 2) ?> MT</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totais">
        <strong>Total: <?= number_format($venda['total'], 2) ?> MT</strong>
    </div>

    <div class="assinatura">
        ___________________________<br>
        Assinatura do Cliente
    </div>

    <div class="print-btn">
        <button onclick="window.print()">Imprimir Recibo</button>
    </div>
</body>
</html>