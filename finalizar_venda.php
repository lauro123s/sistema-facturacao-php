<?php
session_start();
require_once(__DIR__ . '/config/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'funcionario') {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['carrinho'])) {
    header('Location: dashboard_func.php');
    exit;
}

$total = 0;
foreach ($_SESSION['carrinho'] as $item) {
    $stmt = $db->prepare("SELECT preco FROM produtos WHERE id = ?");
    $stmt->execute([$item['produto_id']]);
    $preco = $stmt->fetchColumn();
    $total += $preco * $item['qtd'];
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Pagamento</title>
    <style>
        body { font-family: Arial; background: #eef1f5; padding: 20px; }
        .pagamento { background: #fff; max-width: 500px; margin: auto; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .teclado button { width: 60px; height: 60px; margin: 5px; font-size: 20px; }
        .formas button { width: 48%; margin: 5px 1%; padding: 15px; font-size: 16px; }
        .valor { font-size: 24px; margin: 10px 0; }
        .total, .troco { margin-top: 15px; font-size: 18px; }
        input[type="text"], select { font-size: 22px; padding: 10px; width: 100%; text-align: right; margin-top: 10px; }
        .concluir { background: #4CAF50; color: white; font-size: 18px; padding: 15px; width: 100%; border: none; border-radius: 5px; margin-top: 15px; }
        .voltar { text-align: center; margin-top: 20px; }
        .voltar a { text-decoration: none; background: #3498db; color: white; padding: 10px 20px; border-radius: 5px; font-weight: bold; display: inline-block; }
    </style>
    <script>
        let campoValor;
        let valorPago = 0;

        function insert(num) {
            campoValor.value += num;
        }

        function limpar() {
            campoValor.value = '';
        }

        function calcularTroco(total) {
            valorPago = parseFloat(campoValor.value);
            const troco = valorPago - total;
            document.getElementById('totalPago').textContent = valorPago.toFixed(2);
            document.getElementById('troco').textContent = troco.toFixed(2);
        }

        window.onload = function() {
            campoValor = document.getElementById('valor_pago');
            document.getElementById('calcular').onclick = function() {
                calcularTroco(<?= $total ?>);
            };

            document.querySelector("form").addEventListener("submit", function(e) {
                document.getElementById('input_valor_pago').value = campoValor.value;
                document.getElementById('input_forma_pagamento').value = document.getElementById('forma_pagamento').value;
            });
        }
    </script>
</head>
<body>
    <div class="pagamento">
        <h2>Total a Pagar: <?= number_format($total, 2) ?> MT</h2>
        <div class="valor">
            <label>Valor Recebido:</label>
            <input type="text" id="valor_pago" readonly>
        </div>
        <div class="teclado">
            <?php for ($i = 1; $i <= 9; $i++): ?>
                <button onclick="insert('<?= $i ?>')"><?= $i ?></button>
            <?php endfor; ?>
            <button onclick="insert('0')">0</button>
            <button onclick="insert('.')">.</button>
            <button onclick="limpar()">C</button>
        </div>
        <div class="formas">
            <select id="forma_pagamento">
                <option value="CASH">CASH (F2)</option>
                <option value="MPESA">M-PESA (F3)</option>
                <option value="CMOVEL">C. MOVEL (F1)</option>
                <option value="POS">POS (F4)</option>
            </select>
        </div>
        <button id="calcular" class="concluir">CALCULAR</button>
        <div class="total">TOTAL PAGO: <span id="totalPago">0.00</span> MT</div>
        <div class="troco">TROCO: <span id="troco">0.00</span> MT</div>

        <form action="finalizar_venda_submit.php" method="POST">
            <input type="hidden" name="valor_pago" id="input_valor_pago">
            <input type="hidden" name="forma_pagamento" id="input_forma_pagamento">
            <button type="submit" class="concluir">CONCLUIR</button>
        </form>

        <div class="voltar">
            <a href="dashboard_func.php">← Voltar ao Painel</a>
        </div>
    </div>
</body>
</html>


