<?php

declare(strict_types=1);

namespace app\database\migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260505093006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create_suppliers_table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('suppliers');

        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('enterprise_id', 'bigint', ['notnull' => false]);
        $table->addColumn('address_id', 'bigint', ['notnull' => false]);
        $table->addColumn('nome_fantasia', 'string', ['length' => 255]);
        $table->addColumn('razao_social', 'string', ['length' => 255]);
        $table->addColumn('cpf_cnpj', 'string', ['length' => 18]);
        $table->addColumn('inscricao_estadual', 'string', ['length' => 30, 'notnull' => false]);
        $table->addColumn('active', 'boolean', ['default' => true]);
        $table->addColumn('created_at', 'datetimetz_immutable', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', 'datetimetz_immutable', ['default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('enterprises', ['enterprise_id'], ['id']);
        $table->addForeignKeyConstraint('addresses', ['address_id'], ['id']);
        $table->addUniqueIndex(['cpf_cnpj']);
        $table->addIndex(['nome_fantasia']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('suppliers');
    }
}
