<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

final class CreateProductsTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('products', [
            'id' => false,
            'primary_key' => ['id'],
        ]);

        $table
            ->addColumn('id', 'biginteger', ['identity' => true])
            ->addColumn('nome', 'string', ['limit' => 200, 'default' => ''])
            ->addColumn('ativo', 'boolean', ['default' => true])
            ->addColumn('criado_em', 'timestamp', [
                'null' => false,
                'default' => Literal::from('CURRENT_TIMESTAMP'),
            ])
            ->addColumn('atualizado_em', 'timestamp', [
                'null' => false,
                'default' => Literal::from('CURRENT_TIMESTAMP'),
            ])
            ->addIndex(['nome'])
            ->create();
    }

    public function down(): void
    {
        $this->table('products')->drop()->save();
    }
}

