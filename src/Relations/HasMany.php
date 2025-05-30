<?php

namespace drahil\Stutter\Relations;

use drahil\Stutter\Core\Model;

class HasMany extends Relation
{
    protected function initializeQuery(): void
    {
        $this->query = $this->relatedClass::query();

        $this->query->where($this->foreignKey, $this->parent->{$this->localKey});
    }

    public function get(): array
    {
        $results = $this->query->get();

        if (!$results) {
            return [];
        }

        return array_map(function ($result) {
            return new $this->relatedClass($result);
        }, $results);
    }

    public function create(array $attributes): Model
    {
        $attributes[$this->foreignKey] = $this->parent->{$this->localKey};

        return $this->relatedClass::create($attributes);
    }
}