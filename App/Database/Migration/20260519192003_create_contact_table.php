<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateContactTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('contact');

        $table
            ->addColumn('id_usuario', 'biginteger', ['null' => true])
            ->addColumn('id_cliente', 'biginteger', ['null' => true])
            ->addColumn('tipo', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('contato', 'text', ['null' => true])
            ->create();
    }

    public function down(): void
    {
        $this->table('contact')->drop()->save();
    }
}
