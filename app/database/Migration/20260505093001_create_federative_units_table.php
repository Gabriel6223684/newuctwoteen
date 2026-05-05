<?php

declare(strict_types=1);

namespace app\database\migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260505093001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create_federative_units_table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('federative_units');

        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('country_id', 'bigint', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 100]);
        $table->addColumn('uf_code', 'string', ['length' => 2]);
        $table->addColumn('created_at', 'datetimetz_immutable', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', 'datetimetz_immutable', ['default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('countries', ['country_id'], ['id']);
        $table->addUniqueIndex(['country_id', 'uf_code']);
        $table->addIndex(['name']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('federative_units');
    }
}
