<?php
session_start();

require_once __DIR__ . '/../src/auth.php';

exigirLogin();

$usuario = usuarioLogado();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>PDV</title>
</head>
<body>
    <h1>PDV</h1>

    <p>
        Logado como <?= htmlspecialchars($usuario['nome']) ?>
        (<?= htmlspecialchars($usuario['perfil']) ?>)
        — <a href="logout.php">Sair</a>
    </p>

    <ul>
        <li><a href="produtos.php">Produtos</a></li>
        <li><a href="venda.php">Nova venda</a></li>
    </ul>
</body>
</html>