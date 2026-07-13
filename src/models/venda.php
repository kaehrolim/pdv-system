<?php

class Venda
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param array $itens [['produto_id' => int, 'quantidade' => int], ...]
     */
    public function registrar(int $usuarioId, array $itens): int
    {
        if (empty($itens)) {
            throw new InvalidArgumentException('Venda sem itens.');
        }

        $this->pdo->beginTransaction();

        try {
            $stmtVenda = $this->pdo->prepare(
                "INSERT INTO vendas (usuario_id, total) VALUES (?, 0)"
            );
            $stmtVenda->execute([$usuarioId]);
            $vendaId = (int) $this->pdo->lastInsertId();

            $total = 0.0;

            foreach ($itens as $item) {
                $produtoId  = (int) $item['produto_id'];
                $quantidade = (int) $item['quantidade'];

                if ($quantidade <= 0) {
                    throw new InvalidArgumentException('Quantidade inválida.');
                }

                // trava a linha do produto até o fim da transação
                $stmt = $this->pdo->prepare(
                    "SELECT nome, preco, estoque FROM produtos WHERE id = ? AND ativo = 1 FOR UPDATE"
                );
                $stmt->execute([$produtoId]);
                $produto = $stmt->fetch();

                if (!$produto) {
                    throw new RuntimeException('Produto não encontrado.');
                }

                if ($produto['estoque'] < $quantidade) {
                    throw new RuntimeException(
                        "Estoque insuficiente para {$produto['nome']}."
                    );
                }

                $precoUnitario = (float) $produto['preco'];
                $subtotal      = $precoUnitario * $quantidade;
                $total        += $subtotal;

                $stmt = $this->pdo->prepare(
                    "INSERT INTO venda_itens (venda_id, produto_id, quantidade, preco_unitario, subtotal)
                     VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->execute([$vendaId, $produtoId, $quantidade, $precoUnitario, $subtotal]);

                $stmt = $this->pdo->prepare(
                    "UPDATE produtos SET estoque = estoque - ? WHERE id = ?"
                );
                $stmt->execute([$quantidade, $produtoId]);
            }

            $stmt = $this->pdo->prepare("UPDATE vendas SET total = ? WHERE id = ?");
            $stmt->execute([$total, $vendaId]);

            $this->pdo->commit();

            return $vendaId;

        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function buscarComItens(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT v.*, u.nome AS usuario
             FROM vendas v
             JOIN usuarios u ON u.id = v.usuario_id
             WHERE v.id = ?"
        );
        $stmt->execute([$id]);
        $venda = $stmt->fetch();

        if (!$venda) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            "SELECT vi.*, p.nome
             FROM venda_itens vi
             JOIN produtos p ON p.id = vi.produto_id
             WHERE vi.venda_id = ?"
        );
        $stmt->execute([$id]);
        $venda['itens'] = $stmt->fetchAll();

        return $venda;
    }
}