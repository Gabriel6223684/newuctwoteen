<?php

declare(strict_types=1);

namespace app\database\migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260505093003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create_addresses_table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('addresses');

        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('city_id', 'bigint', ['notnull' => false]);
        $table->addColumn('street', 'string', ['length' => 255]);
        $table->addColumn('number', 'string', ['length' => 10]);
        $table->addColumn('neighborhood', 'string', ['length' => 100]);
        $table->addColumn('zip_code', 'string', ['length' => 10]);
        $table->addColumn('complement', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('created_at', 'datetimetz_immutable', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', 'datetimetz_immutable', ['default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('cities', ['city_id'], ['id']);
        $table->addIndex(['zip_code']);
        $table->addIndex(['street']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('addresses');
    }
}
