<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

final class CreateSalesTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('sales', [
            'id' => false,
            'primary_key' => ['id'],
        ]);

        $table
            ->addColumn('id', 'biginteger', ['identity' => true])
            ->addColumn('sale_date', 'date', ['null' => false])
            ->addColumn('criado_em', 'timestamp', [
                'null' => false,
                'default' => Literal::from('CURRENT_TIMESTAMP'),
            ])
            ->addColumn('atualizado_em', 'timestamp', [
                'null' => false,
                'default' => Literal::from('CURRENT_TIMESTAMP'),
            ])
            ->addIndex(['sale_date'])
            ->create();
    }

    public function down(): void
    {
        $this->table('sales')->drop()->save();
    }
}

