<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/Produto.php';

exigirLogin();

$model = new Produto($pdo);
$usuario = usuarioLogado();
$isAdmin = $usuario['perfil'] === 'admin';

$erro = '';
$editando = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$isAdmin) {
        http_response_code(403);
        die('Acesso negado.');
    }

    $acao    = $_POST['acao'] ?? '';
    $id      = (int) ($_POST['id'] ?? 0);
    $nome    = trim($_POST['nome'] ?? '');
    $codigo  = trim($_POST['codigo_barras'] ?? '');
    $preco   = (float) str_replace(',', '.', $_POST['preco'] ?? '0');
    $estoque = (int) ($_POST['estoque'] ?? 0);

    if ($acao === 'excluir' && $id) {
        $model->excluir($id);
        header('Location: produtos.php');
        exit;
    }

    if ($nome === '' || $preco <= 0) {
        $erro = 'Nome e preço são obrigatórios.';
    } else {
        if ($acao === 'editar' && $id) {
            $model->atualizar($id, $nome, $codigo, $preco, $estoque);
        } else {
            $model->criar($nome, $codigo, $preco, $estoque);
        }
        header('Location: produtos.php');
        exit;
    }
}

if (isset($_GET['editar'])) {
    $editando = $model->buscar((int) $_GET['editar']);
}

$produtos = $model->listar();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Produtos — PDV</title>
</head>
<body>
    <h1>Produtos</h1>
    <p><a href="index.php">Voltar</a></p>

    <?php if ($erro): ?>
        <p style="color:red;"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <?php if ($isAdmin): ?>
        <h2><?= $editando ? 'Editar produto' : 'Novo produto' ?></h2>
        <form method="post">
            <input type="hidden" name="acao" value="<?= $editando ? 'editar' : 'criar' ?>">
            <input type="hidden" name="id" value="<?= $editando['id'] ?? '' ?>">

            <label>Nome
                <input type="text" name="nome" required
                       value="<?= htmlspecialchars($editando['nome'] ?? '') ?>">
            </label><br>

            <label>Código de barras
                <input type="text" name="codigo_barras"
                       value="<?= htmlspecialchars($editando['codigo_barras'] ?? '') ?>">
            </label><br>

            <label>Preço
                <input type="text" name="preco" required
                       value="<?= htmlspecialchars($editando['preco'] ?? '') ?>">
            </label><br>

            <label>Estoque
                <input type="number" name="estoque"
                       value="<?= htmlspecialchars($editando['estoque'] ?? '0') ?>">
            </label><br>

            <button type="submit">Salvar</button>
            <?php if ($editando): ?>
                <a href="produtos.php">Cancelar</a>
            <?php endif; ?>
        </form>
    <?php endif; ?>

    <h2>Lista</h2>
    <table border="1" cellpadding="6">
        <tr>
            <th>Nome</th>
            <th>Código</th>
            <th>Preço</th>
            <th>Estoque</th>
            <?php if ($isAdmin): ?><th>Ações</th><?php endif; ?>
        </tr>

        <?php foreach ($produtos as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['nome']) ?></td>
                <td><?= htmlspecialchars($p['codigo_barras'] ?? '-') ?></td>
                <td>R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
                <td><?= $p['estoque'] ?></td>
                <?php if ($isAdmin): ?>
                    <td>
                        <a href="produtos.php?editar=<?= $p['id'] ?>">Editar</a>
                        <form method="post" style="display:inline;"
                              onsubmit="return confirm('Excluir produto?');">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit">Excluir</button>
                        </form>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>