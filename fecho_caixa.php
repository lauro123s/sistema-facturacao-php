<?php
session_start();
require_once(__DIR__ . '/config/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'funcionario') {
    header('Location: login.php');
    exit;
}

$id = $_SESSION['user_id'];

// Soma todas as vendas feitas pelo funcionário hoje
$stmt = $db->prepare("SELECT SUM(total) FROM vendas WHERE usuario_id = ? AND DATE(data) = CURDATE()");
$stmt->execute([$id]);
$total = $stmt->fetchColumn() ?: 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $insert = $db->prepare("INSERT INTO fecho_caixa (usuario_id, total) VALUES (?, ?)");
    $insert->execute([$id, $total]);

    echo "<script>alert('Fecho de caixa efectuado com sucesso!'); location.href='dashboard_func.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Fecho de Caixa</title>
    <style>
        body { font-family: Arial; background: #f7f7f7; padding: 40px; text-align: center; }
        .box { background: #fff; padding: 30px; max-width: 400px; margin: auto; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .total { font-size: 24px; margin-bottom: 20px; }
        button { padding: 15px 30px; font-size: 18px; background: #4CAF50; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Fecho de Caixa</h2>
        <div class="total">Total do dia: <strong><?= number_format($total, 2) ?> MT</strong></div>

        <form method="POST">
            <button type="submit">Confirmar Fecho</button>
        </form>
    </div>
</body>
</html>
