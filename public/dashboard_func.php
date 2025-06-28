// Em vendas.php
session_start(); require_once '../config/config.php';
// Ao clicar produto:
if (isset($_GET['add'])) {
  $_SESSION['carrinho'][] = ['produto_id'=>$_GET['add'], 'qtd'=>1];
  header('Location: vendas.php');
  exit;
}
// Exibe botoes:
$prods = $db->query("SELECT * FROM produtos WHERE estoque > 0")->fetchAll();
foreach($prods as $p){
  echo "<a href='?add={$p['id']}'>".htmlspecialchars($p['nome'])." ({$p['preco']} MT)</a><br>";
}
