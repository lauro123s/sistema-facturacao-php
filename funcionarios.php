<?php
session_start();
require_once(__DIR__ . '/config/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$erro = '';
$sucesso = '';

// Inserir funcionário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar'])) {
    $nome = trim($_POST['nome']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    if ($nome && $username && $password && $role) {
        $stmt = $db->prepare("INSERT INTO usuarios (nome, username, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $username, $password, $role]);
        $sucesso = "Funcionário adicionado com sucesso!";
    } else {
        $erro = "Preencha todos os campos.";
    }
}

// Excluir funcionário mesmo que tenha vendas (usuario_id será NULL nas vendas)
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $sucesso = "Funcionário eliminado com sucesso!";
    header('Location: funcionarios.php');
    exit;
}

// Buscar todos os funcionários
$funcionarios = $db->query("SELECT * FROM usuarios ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Funcionários</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; padding: 20px; }
        h2 { text-align: center; }
        .container { max-width: 900px; margin: auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }

        .form-box { margin-bottom: 30px; }
        input, select { padding: 10px; width: 100%; margin-top: 8px; margin-bottom: 15px; }
        .btn { background: #2563eb; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #1d4ed8; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #ccc; text-align: left; }
        th { background: #f1f5f9; }
        .del-btn { background: #e11d48; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; }
        .del-btn:hover { background: #be123c; }

        .mensagem { margin: 10px 0; color: green; }
        .erro { margin: 10px 0; color: red; }

        .voltar {
            display: inline-block;
            margin-bottom: 15px;
            background: #10b981;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="dashboard_admin.php" class="voltar">← Voltar</a>
    <h2>Gestão de Funcionários</h2>

    <?php if ($sucesso): ?><p class="mensagem"><?= $sucesso ?></p><?php endif; ?>
    <?php if ($erro): ?><p class="erro"><?= $erro ?></p><?php endif; ?>

    <div class="form-box">
        <form method="POST">
            <label>Nome:</label>
            <input type="text" name="nome" required>

            <label>Usuário:</label>
            <input type="text" name="username" required>

            <label>Senha:</label>
            <input type="text" name="password" required>

            <label>Tipo:</label>
            <select name="role" required>
                <option value="funcionario">Funcionário</option>
                <option value="admin">Administrador</option>
            </select>

            <button class="btn" type="submit" name="adicionar">Adicionar Funcionário</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>Usuário</th>
                <th>Tipo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($funcionarios as $f): ?>
            <tr>
                <td><?= $f['id'] ?></td>
                <td><?= htmlspecialchars($f['nome']) ?></td>
                <td><?= htmlspecialchars($f['username']) ?></td>
                <td><?= $f['role'] ?></td>
                <td>
                    <a class="del-btn" href="?delete=<?= $f['id'] ?>" onclick="return confirm('Deseja excluir este funcionário?')">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>

