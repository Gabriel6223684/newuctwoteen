<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

final class CreateUsersTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('users', [
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

            ->addColumn('sobrenome', 'string', [
                'limit' => 150,
                'default' => ''
            ])

            ->addColumn('cpf', 'string', [
                'limit' => 14,
                'default' => ''
            ])

            ->addColumn('rg', 'string', [
                'limit' => 20,
                'default' => ''
            ])

            ->addColumn('senha', 'string', [
                'limit' => 255,
                'default' => ''
            ])

            ->addColumn('ativo', 'boolean', [
                'default' => false
            ])

            ->addColumn('administrador', 'boolean', [
                'default' => false
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
                ['cpf'],
                ['unique' => true]
            )

            ->addIndex(['nome'])
            ->addIndex(['sobrenome'])

            ->create();
    }

    public function down(): void
    {
        $this->table('users')->drop()->save();
    }
}
