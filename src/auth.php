<?php

function usuarioLogado(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function exigirLogin(): void
{
    if (!usuarioLogado()) {
        header('Location: login.php');
        exit;
    }
}

function exigirAdmin(): void
{
    exigirLogin();
    if ($_SESSION['usuario']['perfil'] !== 'admin') {
        http_response_code(403);
        die('Acesso negado.');
    }
}