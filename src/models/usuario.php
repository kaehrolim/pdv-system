<?php

class Usuario
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function buscarPorEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public function criar(string $nome, string $email, string $senha, string $perfil = 'operador'): bool
    {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare(
            "INSERT INTO usuarios (nome, email, senha, perfil) VALUES (?, ?, ?, ?)"
        );

        return $stmt->execute([$nome, $email, $hash, $perfil]);
    }
}