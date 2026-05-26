<?php

declare(strict_types=1);

namespace App\Database\Builder;

use App\Database\Connection;

class DeleteQuery
{
    private string $table;
    private array $where = [];
    private array $binds = [];

    public static function table(string $table)
    {
        $self = new self;
        $self->table = $table;
        return $self;
    }

    public function where(string $field, string $operator, string|int $value, ?string $logic = null)
    {
        $placeHolder = $field;
        if (str_contains($placeHolder, '.')) {
            $placeHolder = substr($field, strpos($field, '.') + 1);
        }
        $this->where[] = "{$field} {$operator} :{$placeHolder} {$logic}";
        $this->binds[$placeHolder] = $value;
        return $this;
    }

    private function createQuery()
    {
        if (!$this->table) {
            throw new \Exception("A consulta precisa invocar o método delete.");
        }
        $query = "delete from {$this->table} ";
        $query .= (isset($this->where) and (count($this->where) > 0)) ? ' where ' . implode(' ', $this->where) : '';
        return $query;
    }

    public function executeQuery($query)
    {
        $connection = Connection::connection();
        // O Doctrine DBAL usa executeStatement para operações que alteram linhas (INSERT/UPDATE/DELETE)
        return $connection->executeStatement($query, $this->binds ?? []);
    }

    public function delete()
    {
        $query = $this->createQuery();
        try {
            return $this->executeQuery($query);
        } catch (\Exception $e) {
            throw new \Exception("Restrição: {$e->getMessage()}");
        }
    }
}