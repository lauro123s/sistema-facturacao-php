<?php
require_once __DIR__ . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $db->prepare("SELECT * FROM usuarios WHERE username = ? AND password = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        session_start();
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['nome'];
        $_SESSION['role']      = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: dashboard_admin.php");
        } else {
            header("Location: dashboard_func.php");
        }
        exit;
    } else {
        $erro = "Credenciais inválidas.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistema de Facturação</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-box {
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-box h2 {
            margin-bottom: 25px;
            color: #1f2937;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 8px 0 20px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 16px;
        }

        button {
            background-color: #2563eb;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #1d4ed8;
        }

        .erro {
            color: red;
            margin-bottom: 15px;
        }

        .footer {
            margin-top: 25px;
            font-size: 13px;
            color: #6b7280;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Login no Sistema</h2>

    <?php if (!empty($erro)) echo "<div class='erro'>$erro</div>"; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Usuário" required>
        <input type="password" name="password" placeholder="Senha" required>
        <button type="submit">Entrar</button>
    </form>

    <div class="footer">
        &copy; <?= date('Y') ?> Sistema de Facturação
    </div>
</div>

</body>
</html>


