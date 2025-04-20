<?php

namespace drahil\Stutter\Relations;

class BelongsTo extends Relation
{

    protected function initializeQuery(): void
    {
        $this->query = $this->relatedClass::query();

        $this->query->where($this->localKey, $this->parent->{$this->foreignKey});
    }

    public function get(): array
    {
        $result = $this->query->first();

        if (!$result) {
            return [];
        }

        return [new $this->relatedClass($result)];
    }
}