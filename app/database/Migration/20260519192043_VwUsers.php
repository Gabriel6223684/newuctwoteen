<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class VwUsers extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(<<<'SQL'
CREATE OR REPLACE VIEW vw_user AS
SELECT
    u.id,
    u.nome,
    u.sobrenome,
    u.cpf,
    u.rg,
    u.senha,
    u.ativo,
    u.administrador,

    MAX(c.contato) FILTER (WHERE c.tipo = 'EMAIL')    AS email,
    MAX(c.contato) FILTER (WHERE c.tipo = 'CELULAR')  AS celular,
    MAX(c.contato) FILTER (WHERE c.tipo = 'TELEFONE') AS telefone,
    MAX(c.contato) FILTER (WHERE c.tipo = 'WHATSAPP') AS whatsapp,

    u.criado_em,
    u.atualizado_em

FROM users u
LEFT JOIN contact c
       ON c.id_usuario = u.id

GROUP BY
    u.id,
    u.nome,
    u.sobrenome,
    u.cpf,
    u.rg,
    u.senha,
    u.ativo,
    u.administrador,
    u.criado_em,
    u.atualizado_em;
SQL);
    }

    public function down(): void
    {
        $this->execute('DROP VIEW IF EXISTS vw_user');
    }
}
