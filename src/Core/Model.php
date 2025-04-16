<?php

namespace drahil\Stutter\Core;

abstract class Model
{
    protected static string $table;
    protected static ?string $connection = null;
    protected string $primaryKey = 'id';
    protected array $attributes = [];
    protected array $original = [];

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
        $this->syncOriginal();
    }

    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    public function setAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    public function __set(string $key, $value): void
    {
        $this->setAttribute($key, $value);
    }

    protected function syncOriginal(): void
    {
        $this->original = $this->attributes;
    }

    public function isDirty(): bool
    {
        return $this->getDirtyAttributes() !== [];
    }

    public function getDirtyAttributes(): array
    {
        return array_diff_assoc($this->attributes, $this->original);
    }

    public static function getConnection(): Connection
    {
        return ConnectionManager::getConnection(static::$connection);
    }

    public static function getTable(): string
    {
        return static::$table;
    }

    public static function query(): QueryBuilder
    {
        return new QueryBuilder(static::getConnection(), static::getTable());
    }

    public static function all(): array
    {
        $models = [];
        $results = self::query()->get();

        foreach ($results as $result) {
            $models[] = new static($result);
        }

        return $models;
    }

    public static function find(int $id)
    {
        return self::query()->find($id);
    }

    //   findOrFail

    public static function where($column, $operator = null, $value = null): QueryBuilder
    {
        return static::query()->where($column, $operator, $value);
    }
    //   save

    //   insert

    //   update

    //   delete
    public function delete(): bool
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            return false;
        }

        return static::query()
                ->where($this->primaryKey, $this->attributes[$this->primaryKey])
                ->delete() > 0;
    }
}