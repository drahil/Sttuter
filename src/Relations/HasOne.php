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

    public function get(): array
    {
        $result = $this->query->first();

        if (!$result) {
            return [];
        }

        return [new $this->relatedClass($result)];
    }
}