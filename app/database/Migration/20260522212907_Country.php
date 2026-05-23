<?php

declare(strict_types=1);

namespace app\database\migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260522000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Countries';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('countries');
        $table->addColumn('id',          'bigint',   ['autoincrement' => true]);
        $table->addColumn('nome',        'text',     ['default' => '']);
        $table->addColumn('codigo_iso',  'text',     ['default' => '']); // Para a coluna "Código ISO"
        $table->addColumn('ativo',       'boolean',  ['default' => true]);  // Para o "Status" (inicia ativo)
        $table->addColumn('criado_em',   'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('atualizado_em', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['codigo_iso']); // Evita códigos ISO duplicados
        $table->addIndex(['nome']);            // Otimiza a busca rápida por nome
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('countries');
    }
}
