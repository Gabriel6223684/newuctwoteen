# TODO - Ajuste de erro da página ("application" / Slim Application Error)

## Passos
1. Confirmar mensagem de erro no navegador/terminal e identificar ponto de falha (stack trace).
2. Verificar onde a aplicação tenta conectar no Postgres (host/port/dbname/user).
3. Ajustar configuração de DB para rodar dentro do Docker (host deve apontar para o service `postgres`).
4. Garantir que `.env` esteja correto e seja carregado no container durante execução.
5. Reiniciar containers e validar endpoints.
6. Rodar testes (pest) e/ou uma página que antes falhava.

