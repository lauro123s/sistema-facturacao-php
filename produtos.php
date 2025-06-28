<?php
session_start();
require_once(__DIR__ . '/config/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$categorias = $db->query("SELECT * FROM categorias ORDER BY nome")->fetchAll();

// Adicionar produto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar'])) {
    $nome = trim($_POST['nome']);
    $preco = (float)$_POST['preco'];
    $estoque = (int)$_POST['estoque'];
    $cat_id = (int)$_POST['categoria_id'];

    if ($nome && $preco > 0 && $estoque >= 0 && $cat_id > 0) {
        $stmt = $db->prepare("INSERT INTO produtos (nome, preco, estoque, categoria_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $preco, $estoque, $cat_id]);
        $msg = "Produto adicionado!";
    } else {
        $erro = "Preencha todos os campos correctamente.";
    }
}

// Editar produto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    $id = (int)$_POST['id'];
    $nome = trim($_POST['nome']);
    $preco = (float)$_POST['preco'];
    $estoque = (int)$_POST['estoque'];
    $cat_id = (int)$_POST['categoria_id'];

    if ($nome && $preco > 0 && $estoque >= 0 && $cat_id > 0) {
        $stmt = $db->prepare("UPDATE produtos SET nome=?, preco=?, estoque=?, categoria_id=? WHERE id=?");
        $stmt->execute([$nome, $preco, $estoque, $cat_id, $id]);
        $msg = "Produto actualizado!";
    } else {
        $erro = "Dados inválidos.";
    }
}

// Eliminar produto
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $db->prepare("DELETE FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: produtos.php');
    exit;
}

$produtos = $db->query("SELECT p.*, c.nome as categoria FROM produtos p JOIN categorias c ON p.categoria_id = c.id ORDER BY p.id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Produtos</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        input, select { padding: 10px; width: 100%; margin-top: 5px; }
        .btn { background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px; }
        .btn:hover { background: #1d4ed8; }
        .del-btn { background: #dc2626; color: white; padding: 6px 12px; text-decoration: none; border-radius: 5px; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .msg { color: green; }
        .erro { color: red; }
        .voltar { background: #10b981; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="container">
    <a href="dashboard_admin.php" class="voltar">← Voltar ao Dashboard</a>
    <h2>Gestão de Produtos</h2>

    <?php if (!empty($msg)): ?><p class="msg"><?= $msg ?></p><?php endif; ?>
    <?php if (!empty($erro)): ?><p class="erro"><?= $erro ?></p><?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <input type="text" name="nome" placeholder="Nome do Produto" required>
        </div>
        <div class="form-group">
            <input type="number" step="0.01" name="preco" placeholder="Preço" required>
        </div>
        <div class="form-group">
            <input type="number" name="estoque" placeholder="Estoque" required>
        </div>
        <div class="form-group">
            <select name="categoria_id" required>
                <option value="">-- Categoria --</option>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" name="adicionar" class="btn">Adicionar Produto</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>Preço</th>
                <th>Estoque</th>
                <th>Categoria</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($produtos as $p): ?>
            <tr>
                <form method="POST">
                    <td><?= $p['id'] ?><input type="hidden" name="id" value="<?= $p['id'] ?>"></td>
                    <td><input type="text" name="nome" value="<?= htmlspecialchars($p['nome']) ?>" required></td>
                    <td><input type="number" step="0.01" name="preco" value="<?= $p['preco'] ?>" required></td>
                    <td><input type="number" name="estoque" value="<?= $p['estoque'] ?>" required></td>
                    <td>
                        <select name="categoria_id" required>
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $c['id'] == $p['categoria_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <button type="submit" name="editar" class="btn">Actualizar</button>
                        <a href="?delete=<?= $p['id'] ?>" class="del-btn" onclick="return confirm('Eliminar este produto?')">Eliminar</a>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
