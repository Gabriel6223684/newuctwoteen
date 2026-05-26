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
        $table->addColumn('enterprise_id', 'bigint',   []); // Relacionamento com a empresa
        $table->addColumn('nome_fantasia', 'text',     ['default' => '']);
        $table->addColumn('cpf_cnpj',      'text',     ['default' => '']);
        $table->addColumn('ativo',         'boolean',  ['default' => true]);
        $table->addColumn('criado_em',     'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('atualizado_em', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);

        // Índices e Chaves Únicas
        $table->addIndex(['enterprise_id']);               // Otimiza o filtro principal por empresa
        $table->addIndex(['nome_fantasia']);               // Otimiza a busca rápida pelo nome
        $table->addUniqueIndex(['enterprise_id', 'cpf_cnpj']); // Impede duplicidade do mesmo fornecedor NA MESMA empresa
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('suppliers');
    }
}
