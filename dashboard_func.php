<?php
session_start();
require_once(__DIR__ . '/config/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'funcionario') {
    header('Location: login.php');
    exit;
}

$categorias = $db->query("SELECT * FROM categorias")->fetchAll();
$categoria_id = $_GET['cat'] ?? null;
$search = $_GET['search'] ?? null;

if ($search) {
    $produtos = $db->prepare("SELECT * FROM produtos WHERE nome LIKE ? AND estoque > 0");
    $produtos->execute(["%$search%"]);
} elseif ($categoria_id) {
    $produtos = $db->prepare("SELECT * FROM produtos WHERE categoria_id = ? AND estoque > 0");
    $produtos->execute([$categoria_id]);
} else {
    $produtos = $db->prepare("SELECT * FROM produtos WHERE estoque > 0");
    $produtos->execute();
}
$produtos = $produtos->fetchAll();

if (isset($_GET['add'])) {
    $id = (int) $_GET['add'];
    $encontrado = false;
    foreach ($_SESSION['carrinho'] ?? [] as &$item) {
        if ($item['produto_id'] === $id) {
            $item['qtd'] += 1;
            $encontrado = true;
            break;
        }
    }
    if (!$encontrado) {
        $_SESSION['carrinho'][] = ['produto_id' => $id, 'qtd' => 1];
    }
    header('Location: dashboard_func.php');
    exit;
}

if (isset($_GET['remove'])) {
    foreach ($_SESSION['carrinho'] as $index => $item) {
        if ($item['produto_id'] == $_GET['remove']) {
            unset($_SESSION['carrinho'][$index]);
            break;
        }
    }
    $_SESSION['carrinho'] = array_values($_SESSION['carrinho']);
    header('Location: dashboard_func.php');
    exit;
}

if (isset($_POST['atualizar_qtd']) && isset($_POST['produto_id']) && isset($_POST['nova_qtd'])) {
    foreach ($_SESSION['carrinho'] as &$item) {
        if ($item['produto_id'] == $_POST['produto_id']) {
            $item['qtd'] = max(1, (int)$_POST['nova_qtd']);
            break;
        }
    }
    header('Location: dashboard_func.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>POS - Funcionário</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; display: flex; height: 100vh; background: #ecf0f1; }
        .left { width: 70%; padding: 20px; background: #f9f9f9; overflow-y: auto; border-right: 2px solid #dfe6e9; }
        .right { width: 30%; padding: 20px; background: #ffffff; display: flex; flex-direction: column; box-shadow: inset 0 0 10px rgba(0,0,0,0.05); }
        .categoria, .produto {
            display: inline-block; margin: 10px; padding: 15px; background: #ffffff;
            border: 1px solid #dcdde1; border-radius: 10px; text-align: center; cursor: pointer;
            transition: 0.3s ease-in-out; box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }
        .produto:hover, .categoria:hover { background: #dff9fb; transform: scale(1.05); }
        .produto strong { display: block; margin-top: 8px; color: #2d3436; }
        .carrinho-item { border-bottom: 1px solid #ccc; padding: 12px 0; display: flex; justify-content: space-between; align-items: center; }
        .carrinho-item form { display: inline; }
        .total { font-weight: bold; margin-top: 12px; font-size: 20px; color: #2c3e50; }
        .pagar {
            background: #27ae60; color: #fff; padding: 15px;
            text-align: center; cursor: pointer; margin-top: auto; border-radius: 6px;
            border: none; font-size: 18px;
        }
        .btn-remove { background: #e74c3c; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
        .btn-quant { width: 50px; text-align: center; font-size: 16px; padding: 6px; border: 1px solid #bdc3c7; border-radius: 4px; }
        h2 { color: #34495e; border-bottom: 2px solid #dcdde1; padding-bottom: 5px; }
        .sair {
            margin-top: 20px;
            text-align: center;
        }
        .sair a {
            background: #e67e22; color: white; padding: 10px 20px; text-decoration: none;
            border-radius: 5px; font-weight: bold; display: inline-block;
        }
    </style>
</head>
<body>
    <div class="left">
        <h2>Categorias</h2>
        <?php foreach ($categorias as $cat): ?>
            <a class="categoria" href="?cat=<?= $cat['id'] ?>"> <?= htmlspecialchars($cat['nome']) ?> </a>
        <?php endforeach; ?>

        <h2>Pesquisar Produto</h2>
        <form method="GET" style="margin-bottom: 20px;">
            <input type="text" name="search" placeholder="Digite o nome do produto" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="padding:10px; width: 80%; border-radius: 8px; border:1px solid #ccc;">
            <button type="submit" style="padding: 10px; border-radius: 8px; background: #3498db; color: white; border: none; cursor: pointer;">🔍</button>
        </form>

        <h2>Produtos</h2>
        <?php foreach ($produtos as $prod): ?>
            <a class="produto" href="?add=<?= $prod['id'] ?>">
                <?= htmlspecialchars($prod['nome']) ?><br>
                <strong><?= number_format($prod['preco'], 2) ?> MT</strong>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="right">
        <h2>Carrinho</h2>
        <?php
        $total = 0;
        if (!empty($_SESSION['carrinho'])):
            foreach ($_SESSION['carrinho'] as $item):
                $stmt = $db->prepare("SELECT nome, preco FROM produtos WHERE id = ?");
                $stmt->execute([$item['produto_id']]);
                $prod = $stmt->fetch();
                $subtotal = $item['qtd'] * $prod['preco'];
                $total += $subtotal;
                ?>
                <div class='carrinho-item'>
                    <div>
                        <strong><?= $prod['nome'] ?></strong><br>
                        <?= $item['qtd'] ?> x <?= number_format($prod['preco'], 2) ?> MT = <strong><?= number_format($subtotal, 2) ?> MT</strong>
                    </div>
                    <div>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="produto_id" value="<?= $item['produto_id'] ?>">
                            <input class="btn-quant" type="number" name="nova_qtd" value="<?= $item['qtd'] ?>" min="1">
                            <button type="submit" name="atualizar_qtd">↺</button>
                        </form>
                        <a href="?remove=<?= $item['produto_id'] ?>" class="btn-remove">X</a>
                    </div>
                </div>
                <?php
            endforeach;
            echo "<div class='total'>Total: <strong>" . number_format($total, 2) . " MT</strong></div>";
            echo "<form action='finalizar_venda.php' method='POST'><button class='pagar' type='submit'>Efetuar Pagamento (F1)</button></form>";
        else:
            echo "<p>O carrinho está vazio.</p>";
        endif;
        ?>
        <div class="sair">
            <a href="logout.php">Sair</a>
        </div>
    </div>
</body>
</html>




