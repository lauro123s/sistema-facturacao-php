<?php
session_start();
// Remove todas as variáveis de sessão
session_unset();
// Destroi a sessão
session_destroy();
// Redireciona para o formulário de login
header('Location: login.php');
exit;
