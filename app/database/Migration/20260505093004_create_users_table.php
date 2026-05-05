<?php

declare(strict_types=1);

namespace app\database\migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260505093004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create_users_table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('users');

        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('address_id', 'bigint', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('email', 'string', ['length' => 255]);
        $table->addColumn('password', 'string', ['length' => 255]);
        $table->addColumn('role', 'string', ['length' => 50, 'default' => 'user']);
        $table->addColumn('active', 'boolean', ['default' => true]);
        $table->addColumn('created_at', 'datetimetz_immutable', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', 'datetimetz_immutable', ['default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('addresses', ['address_id'], ['id']);
        $table->addUniqueIndex(['email']);
        $table->addIndex(['role']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('users');
    }
}
