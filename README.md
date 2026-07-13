# PDV — Sistema de Ponto de Venda

Sistema web de ponto de venda para pequeno comércio, com controle de estoque,
autenticação e níveis de acesso.

## Stack

- PHP 8.2 (sem framework)
- MySQL 8
- PDO com prepared statements

## Funcionalidades

- Autenticação com `password_hash` / `password_verify` e sessão
- Controle de acesso por perfil (admin / operador)
  - Admin: gerencia produtos
  - Operador: apenas registra vendas
- CRUD de produtos com exclusão lógica (preserva histórico de vendas)
- Registro de venda com carrinho em sessão
- Baixa automática de estoque
- Transação SQL com `FOR UPDATE` e rollback: se qualquer item da venda falhar
  (estoque insuficiente, produto inválido), nenhuma alteração é persistida

## Estrutura
config/     conexão com o banco
database/   schema SQL e seed
src/        models e helpers de autenticação
public/     páginas acessíveis via navegador
## Como rodar

1. Criar o banco: mysql -u root < database/schema.sql
2. Ajustar credenciais em `config/database.php` se necessário
3. Criar usuário admin: php database/seed.php
4. Subir o servidor: php -S localhost:8000 -t public
5. Acessar `http://localhost:8000/login.php`

Credenciais padrão: `admin@pdv.local` / `admin123`

## Decisões técnicas

- **Sem framework**: o objetivo é demonstrar domínio de PHP, SQL e do ciclo
  request/response sem abstrações.
- **Exclusão lógica**: produto vendido não pode desaparecer do histórico.
- **Transação na venda**: operação de venda é atômica. Estoque e itens são
  gravados juntos ou não são gravados.