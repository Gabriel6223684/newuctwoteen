<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCustomerTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('customer', [
            'id' => false,
            'primary_key' => ['id']
        ]);

        $table
            ->addColumn('id', 'biginteger', ['identity' => true])
            ->addColumn('nome_fantasia', 'string', ['limit' => 255])
            ->addColumn('sobrenome_razao', 'string', ['limit' => 255, 'null' => true]) // Adicionado
            ->addColumn('cpf_cnpj', 'string', ['limit' => 18])
            ->addColumn('inscricao_estadual', 'string', ['limit' => 50, 'null' => true]) // Adicionado
            ->addColumn('nascimento_fundacao', 'date', ['null' => true]) // Adicionado
            ->addColumn('ativo', 'boolean', ['default' => true]) // Adicionado
            ->create();
    }

    public function down(): void
    {
        $this->table('customer')->drop()->save();
    }
}
