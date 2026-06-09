<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Supplier extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('suppliers', [
            'id' => 'id',
            'primary_key' => ['id'],
            'signed' => false
        ]);

        $table
            ->addColumn('enterprise_id', 'biginteger')
            ->addColumn('nome_fantasia', 'text', [
                'default' => ''
            ])
            ->addColumn('cpf_cnpj', 'string', [
                'limit' => 20,
                'default' => ''
            ])
            ->addColumn('ativo', 'boolean', [
                'default' => true
            ])
            ->addColumn('criado_em', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP'
            ])
            ->addColumn('atualizado_em', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP'
            ])

            ->addIndex(['enterprise_id'])
            ->addIndex(['nome_fantasia'])
            ->addIndex(
                ['enterprise_id', 'cpf_cnpj'],
                ['unique' => true]
            )

            ->create();
    }

    public function down(): void
    {
        $this->table('suppliers')->drop()->save();
    }
}
