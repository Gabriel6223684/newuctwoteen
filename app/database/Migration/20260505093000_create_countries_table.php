<?php

declare(strict_types=1);

namespace app\database\migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260505093000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create_countries_table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('countries');

        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 100]);
        $table->addColumn('iso_code', 'string', ['length' => 3]);
        $table->addColumn('created_at', 'datetimetz_immutable', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', 'datetimetz_immutable', ['default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['iso_code']);
        $table->addIndex(['name']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('countries');
    }
}
