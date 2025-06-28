<?php
session_start();
require_once(__DIR__ . '/config/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'funcionario') {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['carrinho']) || !isset($_POST['valor_pago']) || !isset($_POST['forma_pagamento'])) {
    header('Location: dashboard_func.php');
    exit;
}

$total = 0;
$db->beginTransaction();

try {
    foreach ($_SESSION['carrinho'] as $item) {
        $stmt = $db->prepare("SELECT preco FROM produtos WHERE id = ?");
        $stmt->execute([$item['produto_id']]);
        $preco = $stmt->fetchColumn();
        $total += $preco * $item['qtd'];
    }

    $valor_pago = floatval($_POST['valor_pago']);
    $forma_pagamento = $_POST['forma_pagamento'];

    if ($valor_pago < $total) {
        throw new Exception("Valor pago insuficiente.");
    }

    $stmt = $db->prepare("INSERT INTO vendas (usuario_id, total, forma_pagamento) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $total, $forma_pagamento]);
    $venda_id = $db->lastInsertId();

    foreach ($_SESSION['carrinho'] as $item) {
        $stmt = $db->prepare("SELECT preco, estoque FROM produtos WHERE id = ?");
        $stmt->execute([$item['produto_id']]);
        $prod = $stmt->fetch();

        if ($item['qtd'] > $prod['estoque']) {
            throw new Exception("Estoque insuficiente para o produto ID " . $item['produto_id']);
        }

        $stmt = $db->prepare("INSERT INTO venda_itens (venda_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        $stmt->execute([$venda_id, $item['produto_id'], $item['qtd'], $prod['preco']]);

        $stmt = $db->prepare("UPDATE produtos SET estoque = estoque - ? WHERE id = ?");
        $stmt->execute([$item['qtd'], $item['produto_id']]);
    }

    $db->commit();
    $_SESSION['carrinho'] = [];
    echo "<script>alert('Venda concluída com sucesso! Troco: " . number_format($valor_pago - $total, 2) . " MT');window.location.href='dashboard_func.php';</script>";
} catch (Exception $e) {
    $db->rollBack();
    echo "<p>Erro ao finalizar a venda: " . $e->getMessage() . "</p>";
    echo "<a href='dashboard_func.php'>Voltar</a>";
}
?>

