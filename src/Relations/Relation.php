<?php

namespace drahil\Stutter\Relations;

use drahil\Stutter\Core\Model;
use drahil\Stutter\Core\QueryBuilder;

abstract class Relation
{
    protected Model $parent;
    protected string $relatedClass;
    protected string $foreignKey;
    protected string $localKey;
    protected QueryBuilder $query;

    public function __construct(
        Model $parent,
        string $relatedClass,
        string $foreignKey,
        string $localKey
    ) {
        $this->parent = $parent;
        $this->relatedClass = $relatedClass;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;

        $this->initializeQuery();
    }

    abstract protected function initializeQuery(): void;

    public function getQuery(): QueryBuilder
    {
        return $this->query;
    }

    abstract public function get(): array;

}