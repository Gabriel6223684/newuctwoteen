<?php

declare(strict_types=1);

namespace app\database\migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260522000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Enterprises';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('enterprises');
        $table->addColumn('id',           'bigint',   ['autoincrement' => true]);
        $table->addColumn('nome',         'text',     ['default' => '']);
        $table->addColumn('cnpj',         'text',     ['default' => '']); // Para a coluna "CNPJ"
        $table->addColumn('ativo',        'boolean',  ['default' => true]);  // Para o "Status" (inicia ativo)
        $table->addColumn('criado_em',    'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('atualizado_em', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['cnpj']); // Garante que não existam duas empresas com o mesmo CNPJ
        $table->addIndex(['nome']);       // Otimiza a busca rápida por nome/termo
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('enterprises');
    }
}
