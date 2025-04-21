<?php

namespace drahil\Stutter\Core;

class QueryBuilder
{
    protected array $selects = ['*'];
    protected array $wheres = [];
    protected array $bindings = [];
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected array $orders = [];
    protected array $joins = [];
    protected array $groups = [];
    protected ?string $having = null;

    public function __construct(
        protected Connection $connection,
        protected string $table
    )
    {
    }

    public function select(array $columns = ['*']): static
    {
        $this->selects = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function where($column, $operator = null, $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];
        $this->bindings[] = $value;

        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->orders[] = compact('column', 'direction');
        return $this;
    }

    public function get(): array
    {
        $sql = $this->toSql();

        return $this->connection->query($sql, $this->bindings);
    }

    public function first(): mixed
    {
        $this->limit = 1;

        $result = $this->get();

        return $result[0] ?? null;
    }

    public function count(): int
    {
        $this->selects = ['COUNT(*) as count'];
        $results = $this->get();
        return (int) ($results[0]['count'] ?? 0);
    }

    public function find(int $id)
    {
        return $this->where('id', $id)->first();
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    public function insert(array $values): int
    {
        $columns = array_keys($values);
        $parameters = array_fill(0, count($values), '?');

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $parameters) . ")";

        $this->connection->execute($sql, array_values($values));
        return (int) $this->connection->lastInsertId();
    }

    public function update(array $values): int
    {
        $sql = "UPDATE {$this->table} SET ";
        $columns = array_keys($values);
        $parameters = array_fill(0, count($values), '?');

        foreach ($columns as $index => $column) {
            $sql .= "{$column} = {$parameters[$index]}, ";
        }

        $sql = rtrim($sql, ', ') . " WHERE " . $this->wheres[0]['column'] . " = ?;";

        return $this->connection->execute($sql, array_merge(array_values($values), [$this->wheres[0]['value']]));
    }

    public function delete(): int
    {
        $sql = "DELETE FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->compileWheres();
        }

        return $this->connection->execute($sql, $this->bindings);
    }

    public function toSql(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->selects)
            . ' FROM ' . $this->table;

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->compileWheres();
        }

        if (!empty($this->groups)) {
            $sql .= " GROUP BY " . implode(', ', $this->groups);
        }

        if ($this->having !== null) {
            $sql .= " HAVING {$this->having}";
        }

        if (!empty($this->orders)) {
            $sql .= " ORDER BY " . $this->compileOrders();
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    protected function compileWheres(): string
    {
        $whereParts = [];

        foreach ($this->wheres as $where) {
            $whereParts[] = match ($where['type']) {
                'raw', 'in' => $where['sql'],
                default => "{$where['column']} {$where['operator']} ?",
            };
        }

        return implode(' AND ', $whereParts);
    }

    protected function compileOrders(): string
    {
        $orderParts = [];

        foreach ($this->orders as $order) {
            $orderParts[] = "{$order['column']} {$order['direction']}";
        }

        return implode(', ', $orderParts);
    }

    public function whereIn(string $column, array $values): self
    {
        if (empty($values)) {
            $this->wheres[] = [
                'type' => 'raw',
                'sql' => '0 = 1'
            ];
            return $this;
        }

        $placeholders = array_fill(0, count($values), '?');

        $this->wheres[] = [
            'type' => 'in',
            'column' => $column,
            'sql' => "$column IN (" . implode(', ', $placeholders) . ")"
        ];

        foreach ($values as $value) {
            $this->bindings[] = $value;
        }

        return $this;
    }

}
