# TODO - Correção lista de Usuários

- [x] Entender o formato esperado pelo DataTables (serverSide) e o payload JSON retornado por `User::listingdata()`.
- [x] Corrigir `User::listingdata()` para ler de `vw_user` (e não de `users`), usando aliases corretos (`nome`, `email`, `ativo`).

- [x] Corrigir `User::details()` para buscar dados completos via `vw_user`.

- [x] Corrigir `User::insert()` para gravar em `users` usando colunas reais (`nome`, `cpf`, `rg`, `senha`, `ativo`, `administrador`) e gravar o `email` em `contact`.
- [x] Corrigir `User::update()` para atualizar `users` (nome/senha/ativo) e atualizar o `contact` do tipo EMAIL.

- [ ] Validar rapidamente via `phpunit` (ou ao menos chamar o endpoint `/usuario/listingdata` e conferir JSON).


