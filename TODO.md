# TODO - Google Auth + Cadastro em users

- [x] Revisar implementação atual do `Login::google()` e identificar trechos incompatíveis.

- [x] Implementar `Login::google()` para usar o schema do projeto (`users`, `contact`, `vw_user`).

- [x] Criar usuário se não existir (por `contact` tipo EMAIL) e inserir `contact` (EMAIL).

- [x] Após criar/recuperar o usuário, gerar sessão `$_SESSION['user']` e cookie `auth_token` no mesmo formato do `authenticate()`.

- [x] Retornar JSON compatível com `resources/js/pages/login.js` (`status`, `msg`, `url`).

- [x] Garantir que não haja 302 redirect no endpoint `/authentication/google`.

- [ ] Validar rapidamente com testes/execução manual (login via Google) e/ou rodar `phpunit`.




