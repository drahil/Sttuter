<?php

namespace Stutter\Core;

class QueryBuilders
{
    protected array $selects = ['*'];
    protected array $wheres = [];
    protected array $bindings = [];
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected array $orders = [];
    protected array $joins = [];
    protected array $groups = [];
    protected ?string $having = null;

    public function __construct(
        protected Connection $connection,
        protected string $table
    )
    {
    }

    // select

    // where

    // limit

    // offset

    // order by

    // join

    // group by

    // get

    // first

    // count

    // find

    // exists

    // insert

    // update

    // delete

    // compileWheres

    // compileJoins

    // compileOrders
}