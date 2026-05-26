# TODO (testes unitários)

- [ ] Atualizar testes existentes de Enterprise/Suppliers para cobrir mais cenários de insert (sem quebrar padrões).
- [x] Criar `tests/unit/UserTest.php` cobrindo: insert (sucesso + validação), update (sucesso + falha sem id), delete (sucesso + falha sem id) e limpeza (delete do registro criado).
- [x] Criar `tests/unit/CountryTest.php` cobrindo insert (sucesso).
- [x] Criar `tests/unit/CustomerTest.php` (ou `CustomerInsertTest.php`) cobrindo insert (sucesso).

- [ ] Rodar `composer test` para confirmar que todos passam.
- [ ] Rodar opcional `composer test:cov` se desejado.

