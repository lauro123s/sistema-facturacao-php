<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $db = new PDO(
        'mysql:host=localhost;dbname=faturacao;charset=utf8mb4',
        'root',
        ''
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erro na conexão: ' . $e->getMessage());
}

