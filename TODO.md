## Migração Doctrine -> Phinx

- [ ] Confirmar referências a Doctrine no repositório (CI/scripts/docs)
- [x] Remover `doctrine-migrations.php`
- [ ] Remover referências a Doctrine no `composer.json` (se existirem)
- [x] Atualizar documentação/README com comando do Phinx para rodar migrations
- [ ] Validar migrations com `docker compose exec php ./vendor/bin/phinx status` e `migrate`
- [ ] Rodar testes

