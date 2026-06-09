<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

final class Country extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('countries', [
            'id' => false,
            'primary_key' => ['id']
        ]);

        $table
            ->addColumn('id', 'biginteger', [
                'identity' => true
            ])

            ->addColumn('nome', 'string', [
                'limit' => 150,
                'default' => ''
            ])

            ->addColumn('codigo_iso', 'string', [
                'limit' => 3,
                'default' => ''
            ])

            ->addColumn('ativo', 'boolean', [
                'default' => true
            ])

            ->addColumn('criado_em', 'timestamp', [
                'null' => false,
                'default' => Literal::from('CURRENT_TIMESTAMP')
            ])

            ->addColumn('atualizado_em', 'timestamp', [
                'null' => false,
                'default' => Literal::from('CURRENT_TIMESTAMP')
            ])

            ->addIndex(
                ['codigo_iso'],
                ['unique' => true]
            )

            ->addIndex(['nome'])

            ->create();
    }

    public function down(): void
    {
        $this->table('countries')->drop()->save();
    }
}
