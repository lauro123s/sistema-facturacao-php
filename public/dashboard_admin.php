<?php
require_once __DIR__ . '/../config/config.php';
if ($_SESSION['role'] !== 'admin') header('Location: login.php');

$produtos = $db->query("SELECT * FROM produtos")->fetchAll(PDO::FETCH_ASSOC);
?>
<h1>Produtos</h1>
<a href="produto_add.php">+ Novo Produto</a>
<table border=1>
  <tr><th>ID</th><th>Nome</th><th>Preço</th><th>Estoque</th><th>Ações</th></tr>
  <?php foreach($produtos as $p): ?>
    <tr>
      <td><?= $p['id'] ?></td>
      <td><?= $p['nome'] ?></td>
      <td><?= number_format($p['preco'],2) ?></td>
      <td><?= $p['estoque'] ?></td>
      <td>
        <a href="produto_edit.php?id=<?= $p['id'] ?>">Editar</a> |
        <a href="produto_delete.php?id=<?= $p['id'] ?>" onclick="return confirm('Remover?')">Deletar</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
