<?php

declare(strict_types=1);

namespace app\database\migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260522000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Suppliers';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('suppliers');
        $table->addColumn('id',            'bigint',   ['autoincrement' => true]);
        $table->addColumn('nome_fantasia', 'text',     ['default' => '']); // Para "Nome Fantasia"
        $table->addColumn('cpf_cnpj',      'text',     ['default' => '']); // Para "CPF/CNPJ" (flexível para ambos)
        $table->addColumn('ativo',         'boolean',  ['default' => true]);  // Para o "Status"
        $table->addColumn('criado_em',     'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('atualizado_em', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['cpf_cnpj']);      // Impede duplicidade de um mesmo fornecedor
        $table->addIndex(['nome_fantasia']);       // Otimiza a busca rápida pelo nome
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('suppliers');
    }
}
