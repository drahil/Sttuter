<?php

namespace drahil\Stutter\Relations;

use drahil\Stutter\Core\Model;

class HasOne extends Relation
{
    protected function initializeQuery(): void
    {
        $this->query = $this->relatedClass::query();

        $this->query->where($this->foreignKey, $this->parent->{$this->localKey});
    }

    public function get(): Model|null
    {
        $result = $this->query->first();

        if (!$result) {
            return null;
        }

        return new $this->relatedClass($result);
    }

    public function create(array $attributes): Model
    {
        $attributes[$this->foreignKey] = $this->parent->{$this->localKey};

        return $this->relatedClass::create($attributes);
    }
}