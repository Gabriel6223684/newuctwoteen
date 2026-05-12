# TODO

## Objetivo
Fazer o fluxo de cadastro + login funcionar (autenticar e liberar rotas protegidas).

## Passos
1. Criar rota/endpoint de cadastro (POST) para inserir usuário em `users` (name, email, password, active).
2. Criar rota/endpoint de autenticação (POST) para validar credenciais e gerar JWT no cookie `auth_token`, além de setar `$_SESSION['user']['logado']`.
3. Atualizar `app/controller/Login.php` com métodos `register()` e `auth()`.
4. Atualizar `app/routes/routes.php` adicionando rotas para `POST /cadastro` e `POST /login/auth` (ou conforme o front).
5. Criar tela de cadastro (`app/view/pages/register.html`) e ajustar `app/view/layouts/main.html` se necessário.
6. Implementar/ajustar `resources/js/pages/login.js` para chamar os endpoints corretos (login e cadastro) e redirecionar.
7. Garantir que `$_SESSION` exista (verificar bootstrap/middleware; adicionar `session_start()` onde necessário).
8. Rodar testes/linters (phpunit) e validar manualmente: cadastrar → logar → acessar `/home` e rotas protegidas.

