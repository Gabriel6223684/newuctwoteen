<?php

declare(strict_types=1);

namespace app\database\migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260505093002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create_cities_table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('cities');

        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('federative_unit_id', 'bigint', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('created_at', 'datetimetz_immutable', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', 'datetimetz_immutable', ['default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('federative_units', ['federative_unit_id'], ['id']);
        $table->addIndex(['name']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('cities');
    }
}
