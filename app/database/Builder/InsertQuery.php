<?php

declare(strict_types=1);

namespace app\database\Builder;

use App\Database\Connection;

final class InsertQuery
{
    private string $table;
    private array $fieldsAndValues = [];

    public static function insert(string $table): ?self
    {
        $self = new self();
        $self->table = $table;
        return $self;
    }

    private function createQuery(): string
    {
        if (empty($this->table)) {
            throw new \Exception('A consulta precisa invocar o método insert.');
        }
        if (empty($this->fieldsAndValues)) {
            throw new \Exception('A consulta precisa dos dados para realizar a inserção.');
        }

        // Cria uma string com '?' repetidos baseada na quantidade de campos
        $placeholders = implode(', ', array_fill(0, count($this->fieldsAndValues), '?'));

        $query = "insert into {$this->table} (";
        $query .= implode(',', array_keys($this->fieldsAndValues)) . ') values (';
        $query .= $placeholders . ')'; // Removido o ponto e vírgula interno para evitar problemas de sintaxe em algumas versões do driver

        return $query;
    }

    /**
     * Salva o registro e retorna o ID gerado pelo banco de dados.
     */
    public function save(array $fieldsAndValues): int
    {
        $this->fieldsAndValues = $fieldsAndValues;
        $query = $this->createQuery();

        $connection = Connection::connection();

        try {
            // Executa a query e faz o bind de forma atômica diretamente na conexão
            $connection->executeStatement($query, array_values($this->fieldsAndValues));

            // Captura e retorna o último ID gerado na tabela corrente
            return (int) $connection->lastInsertId();
        } catch (\Exception $e) {
            throw new \Exception('Restrição: ' . $e->getMessage(), 1);
        }
    }
}
