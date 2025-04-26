<?php

namespace drahil\Stutter\Relations;

use drahil\Stutter\Core\ConnectionManager;
use drahil\Stutter\Core\Model;
use drahil\Stutter\Core\QueryBuilder;

class BelongsToMany extends Relation
{
    private string $parentKey;
    private string $relatedKey;
    private string $pivotTable;

    public function __construct(Model $parent, string $relatedClass, string $parentKey, string $relatedKey, string $pivotTable)
    {
        $this->parentKey = $parentKey;
        $this->relatedKey = $relatedKey;
        $this->pivotTable = $pivotTable;

        $this->query = new QueryBuilder(ConnectionManager::getConnection(), $this->pivotTable);

        parent::__construct($parent, $relatedClass);
    }

    protected function initializeQuery(): void
    {
        $this->query->where($this->parentKey, $this->parent->id)->select([$this->relatedKey]);
    }

    public function get(): array|null
    {
        $pivotResults = $this->query->get();

        if (empty($pivotResults)) {
            return null;
        }

        $relatedIds = array_column($pivotResults, $this->relatedKey);

        return $this->relatedClass::query()->whereIn('id', $relatedIds)->get();
    }

    public function attach(array $relatedIds): ?array
    {
        foreach ($relatedIds as $relatedId) {
            $this->attachSingle($relatedId);
        }

        return $this->get();
    }

    private function attachSingle(mixed $relatedId): void
    {
        $this->query->insert([
            $this->parentKey => $this->parent->id,
            $this->relatedKey => $relatedId,
        ]);
    }

    public function detach(array $relatedIds): ?array
    {
        foreach ($relatedIds as $relatedId) {
            $this->detachSingle($relatedId);
        }

        return $this->get();
    }

    private function detachSingle(mixed $relatedId): void
    {
        $query = clone $this->query;
        $query->where($this->parentKey, $this->parent->id)
            ->where($this->relatedKey, $relatedId)
            ->delete();
    }
}
