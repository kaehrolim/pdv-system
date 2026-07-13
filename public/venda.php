<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/Produto.php';
require_once __DIR__ . '/../src/models/Venda.php';

exigirLogin();

$produtoModel = new Produto($pdo);
$vendaModel   = new Venda($pdo);
$usuario      = usuarioLogado();

$erro     = '';
$sucesso  = '';
$carrinho = $_SESSION['carrinho'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'adicionar') {
        $produtoId  = (int) $_POST['produto_id'];
        $quantidade = (int) $_POST['quantidade'];

        if ($produtoId && $quantidade > 0) {
            $carrinho[$produtoId] = ($carrinho[$produtoId] ?? 0) + $quantidade;
        }
    }

    if ($acao === 'remover') {
        unset($carrinho[(int) $_POST['produto_id']]);
    }

    if ($acao === 'finalizar') {
        $itens = [];
        foreach ($carrinho as $produtoId => $quantidade) {
            $itens[] = ['produto_id' => $produtoId, 'quantidade' => $quantidade];
        }

        try {
            $vendaId  = $vendaModel->registrar($usuario['id'], $itens);
            $carrinho = [];
            $sucesso  = "Venda #{$vendaId} registrada com sucesso.";
        } catch (Throwable $e) {
            $erro = $e->getMessage();
        }
    }

    $_SESSION['carrinho'] = $carrinho;
}

$produtos = $produtoModel->listar();

$mapaProdutos = [];
foreach ($produtos as $p) {
    $mapaProdutos[$p['id']] = $p;
}

$total = 0.0;
foreach ($carrinho as $produtoId => $quantidade) {
    if (isset($mapaProdutos[$produtoId])) {
        $total += $mapaProdutos[$produtoId]['preco'] * $quantidade;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Nova venda — PDV</title>
</head>
<body>
    <h1>Nova venda</h1>
    <p><a href="index.php">Voltar</a></p>

    <?php if ($erro): ?>
        <p style="color:red;"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <?php if ($sucesso): ?>
        <p style="color:green;"><?= htmlspecialchars($sucesso) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="acao" value="adicionar">

        <select name="produto_id" required>
            <option value="">Selecione o produto</option>
            <?php foreach ($produtos as $p): ?>
                <option value="<?= $p['id'] ?>">
                    <?= htmlspecialchars($p['nome']) ?>
                    — R$ <?= number_format($p['preco'], 2, ',', '.') ?>
                    (estoque: <?= $p['estoque'] ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <input type="number" name="quantidade" value="1" min="1" required>
        <button type="submit">Adicionar</button>
    </form>

    <h2>Carrinho</h2>

    <?php if (empty($carrinho)): ?>
        <p>Carrinho vazio.</p>
    <?php else: ?>
        <table border="1" cellpadding="6">
            <tr>
                <th>Produto</th>
                <th>Qtd</th>
                <th>Subtotal</th>
                <th></th>
            </tr>

            <?php foreach ($carrinho as $produtoId => $quantidade): ?>
                <?php $p = $mapaProdutos[$produtoId] ?? null; ?>
                <?php if (!$p) continue; ?>
                <tr>
                    <td><?= htmlspecialchars($p['nome']) ?></td>
                    <td><?= $quantidade ?></td>
                    <td>R$ <?= number_format($p['preco'] * $quantidade, 2, ',', '.') ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="acao" value="remover">
                            <input type="hidden" name="produto_id" value="<?= $produtoId ?>">
                            <button type="submit">Remover</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3>Total: R$ <?= number_format($total, 2, ',', '.') ?></h3>

        <form method="post">
            <input type="hidden" name="acao" value="finalizar">
            <button type="submit">Finalizar venda</button>
        </form>
    <?php endif; ?>
</body>
</html>