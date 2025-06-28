<?php
session_start();
require_once(__DIR__ . '/config/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$erro = '';
$sucesso = '';

// Adicionar categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar'])) {
    $nome = trim($_POST['nome']);
    if ($nome) {
        $stmt = $db->prepare("INSERT INTO categorias (nome) VALUES (?)");
        $stmt->execute([$nome]);
        $sucesso = "Categoria adicionada!";
    } else {
        $erro = "O nome da categoria não pode estar vazio.";
    }
}

// Editar categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    $id = (int)$_POST['id'];
    $nome = trim($_POST['nome']);
    if ($nome) {
        $stmt = $db->prepare("UPDATE categorias SET nome = ? WHERE id = ?");
        $stmt->execute([$nome, $id]);
        $sucesso = "Categoria actualizada!";
    } else {
        $erro = "O nome da categoria não pode estar vazio.";
    }
}

// Eliminar categoria (mesmo que tenha produtos ligados – SET NULL nos produtos)
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    try {
        $stmt = $db->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->execute([$id]);
        $sucesso = "Categoria eliminada com sucesso!";
        header('Location: categorias.php');
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao eliminar categoria: " . $e->getMessage();
    }
}

$categorias = $db->query("SELECT * FROM categorias ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Categorias</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; padding: 20px; }
        h2 { text-align: center; color: #1f2937; }

        .container {
            max-width: 800px; margin: auto;
            background: #fff; padding: 20px;
            border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .form-group { margin-bottom: 15px; }
        input[type="text"] {
            width: 100%; padding: 10px;
            border: 1px solid #ccc; border-radius: 5px;
        }

        .btn {
            background: #2563eb; color: white;
            border: none; padding: 10px 20px;
            border-radius: 5px; cursor: pointer;
        }
        .btn:hover { background: #1d4ed8; }

        .del-btn {
            background: #dc2626; color: white;
            padding: 6px 12px; text-decoration: none;
            border-radius: 5px;
        }

        table {
            width: 100%; margin-top: 20px;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px; text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .msg { margin: 10px 0; color: green; }
        .erro { margin: 10px 0; color: red; }

        .voltar {
            display: inline-block;
            margin-bottom: 20px;
            background: #10b981;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
        }

        form.inline {
            display: inline;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="dashboard_admin.php" class="voltar">← Voltar ao Dashboard</a>
    <h2>Gestão de Categorias</h2>

    <?php if ($sucesso): ?><p class="msg"><?= $sucesso ?></p><?php endif; ?>
    <?php if ($erro): ?><p class="erro"><?= $erro ?></p><?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <input type="text" name="nome" placeholder="Nova categoria" required>
        </div>
        <button type="submit" name="adicionar" class="btn">Adicionar Categoria</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($categorias as $cat): ?>
            <tr>
                <td><?= $cat['id'] ?></td>
                <td>
                    <form method="POST" class="inline">
                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                        <input type="text" name="nome" value="<?= htmlspecialchars($cat['nome']) ?>" required>
                        <button type="submit" name="editar" class="btn">Actualizar</button>
                    </form>
                </td>
                <td>
                    <a href="?delete=<?= $cat['id'] ?>" class="del-btn" onclick="return confirm('Eliminar esta categoria?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>

