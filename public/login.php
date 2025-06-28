<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'];
    $p = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM usuarios WHERE username = ? AND password = ?");
    $stmt->execute([$u, $p]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['nome'];
        $_SESSION['role']      = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: dashboard_admin.php');
        } else {
            header('Location: dashboard_func.php');
        }
        exit;
    } else {
        $error = "Credenciais inválidas.";
    }
}
?>
<!-- Formulário HTML simples -->
<!DOCTYPE html>
<html>
<head><title>Login</title></head>
<body>
  <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
  <form method="POST">
    <label>Usuário:<input name="username" required></label><br>
    <label>Senha:<input name="password" type="password" required></label><br>
    <button type="submit">Entrar</button>
  </form>
</body>
</html>
