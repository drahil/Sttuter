<?php

namespace drahil\Stutter\Relations;

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
}