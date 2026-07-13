<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/models/Usuario.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $model = new Usuario($pdo);
    $usuario = $model->buscarPorEmail($email);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario'] = [
            'id'     => $usuario['id'],
            'nome'   => $usuario['nome'],
            'perfil' => $usuario['perfil'],
        ];
        header('Location: index.php');
        exit;
    }

    $erro = 'Email ou senha inválidos.';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Login — PDV</title>
</head>
<body>
    <h1>Login</h1>

    <?php if ($erro): ?>
        <p style="color:red;"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Email <input type="email" name="email" required></label><br>
        <label>Senha <input type="password" name="senha" required></label><br>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>