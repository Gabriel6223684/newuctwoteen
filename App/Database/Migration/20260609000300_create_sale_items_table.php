<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSaleItemsTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('sale_items', [
            'id' => false,
            'primary_key' => ['id'],
        ]);

        $table
            ->addColumn('id', 'biginteger', ['identity' => true])
            ->addColumn('sale_id', 'biginteger', ['null' => false])
            ->addColumn('product_id', 'biginteger', ['null' => false])
            ->addColumn('unidades', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('valor_unitario', 'decimal', [
                'precision' => 14,
                'scale' => 2,
                'null' => false,
                'default' => 0,
            ])
            ->addIndex(['sale_id'])
            ->addIndex(['product_id'])
            ->create();
    }

    public function down(): void
    {
        $this->table('sale_items')->drop()->save();
    }
}

