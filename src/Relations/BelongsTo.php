<?php

namespace drahil\Stutter\Relations;

use drahil\Stutter\Core\Model;

class BelongsTo extends Relation
{

    protected function initializeQuery(): void
    {
        $this->query = $this->relatedClass::query();

        $this->query->where($this->localKey, $this->parent->{$this->foreignKey});
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
        return $this->relatedClass::create($attributes);
    }
}