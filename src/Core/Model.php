<?php

namespace drahil\Stutter\Core;

use drahil\Stutter\Exceptions\ModelNotFoundException;
use drahil\Stutter\Relations\BelongsTo;
use drahil\Stutter\Relations\HasMany;
use drahil\Stutter\Relations\HasOne;

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

    public static function find(int $id): ?static
    {
        $result = self::query()->find($id);

        return $result ? new static($result) : null;
    }

    public static function findOrFail(int $id): static
    {
        $model = static::find($id);

        if ($model === null) {
            throw new ModelNotFoundException("No query results for model [" . static::class . "] $id");
        }

        return $model;
    }

    public static function where($column, $operator = null, $value = null): QueryBuilder
    {
        return static::query()->where($column, $operator, $value);
    }

    public static function create(array $attributes): static
    {
        $id = static::query()->insert($attributes);

        return static::find($id);
    }

    public function update(array $attributes): static
    {
        static::query()
                ->where($this->primaryKey, $this->attributes[$this->primaryKey])
                ->update($attributes);

        $this->refresh();
        $this->syncOriginal();

        return $this;
    }

    public function delete(): bool
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            return false;
        }

        return static::query()
                ->where($this->primaryKey, $this->attributes[$this->primaryKey])
                ->delete() > 0;
    }

    public function refresh()
    {
        $fresh = static::find($this->attributes[$this->primaryKey]);
        $this->attributes = $fresh->attributes;
    }

    public function hasOne(string $relatedClass, string $foreignKey = null, string $localKey = 'id'): HasOne
    {
        $foreignKey = $foreignKey ?? strtolower((new \ReflectionClass($this))->getShortName()) . '_id';

        return new HasOne($this, $relatedClass, $foreignKey, $localKey);
    }

    public function belongsTo(string $relatedClass, string $foreignKey = null, string $localKey = 'id'): BelongsTo
    {
        $foreignKey = $foreignKey ?? strtolower((new \ReflectionClass($this))->getShortName()) . '_id';

        return new BelongsTo($this, $relatedClass, $foreignKey, $localKey);
    }

    public function hasMany(string $relatedClass, string $foreignKey = null, string $localKey = 'id'): HasMany
    {
        $foreignKey = $foreignKey ?? strtolower((new \ReflectionClass($this))->getShortName()) . '_id';

        return new HasMany($this, $relatedClass, $foreignKey, $localKey);
    }
}