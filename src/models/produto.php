<?php

class Produto
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listar(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM produtos WHERE ativo = 1 ORDER BY nome");
        return $stmt->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM produtos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function criar(string $nome, ?string $codigo, float $preco, int $estoque): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO produtos (nome, codigo_barras, preco, estoque) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$nome, $codigo ?: null, $preco, $estoque]);
    }

    public function atualizar(int $id, string $nome, ?string $codigo, float $preco, int $estoque): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE produtos SET nome = ?, codigo_barras = ?, preco = ?, estoque = ? WHERE id = ?"
        );
        return $stmt->execute([$nome, $codigo ?: null, $preco, $estoque, $id]);
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE produtos SET ativo = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}