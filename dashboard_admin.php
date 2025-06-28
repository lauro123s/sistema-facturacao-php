<?php
session_start();
require_once(__DIR__ . '/config/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Dashboard do Administrador</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f4f8;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #1f2937;
            color: white;
            padding: 20px;
            text-align: center;
        }

        h1 {
            margin: 0;
        }

        nav {
            margin: 40px auto;
            max-width: 700px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        li:hover {
            transform: scale(1.02);
            background-color: #e5f0ff;
        }

        a {
            display: block;
            padding: 15px 20px;
            text-decoration: none;
            color: #1f2937;
            font-weight: bold;
        }

        a:hover {
            color: #1d4ed8;
        }

        .footer {
            text-align: center;
            margin-top: 50px;
            color: #888;
        }
    </style>
</head>
<body>
    <header>
        <h1>Bem-vindo, <?= htmlspecialchars($_SESSION['user_name']) ?> 👨‍💼</h1>
        <p>Administrador</p>
    </header>

    <nav>
        <ul>
            <li><a href="funcionarios.php">👥 Gerir Funcionários</a></li>
            <li><a href="categorias.php">📂 Gerir Categorias</a></li>
            <li><a href="produtos.php">📦 Listar Produtos</a></li>
            <li><a href="vendas_diarias.php">📅 Vendas do Dia</a></li>
            <li><a href="vendas_semanais.php">📆 Vendas da Semana</a></li>
            <li><a href="vendas_mensais.php">🗓️ Vendas do Mês</a></li>
            <li><a href="logout.php">🚪 Terminar Sessão</a></li>
            <li><a href="relatorio_geral.php">📊 Gerar Relatórios</a></li>
        </ul>
    </nav>

    <div class="footer">
        <p>Sistema de Facturação &copy; <?= date('Y') ?></p>
    </div>
</body>
</html>




