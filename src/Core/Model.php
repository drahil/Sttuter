<?php

namespace Stutter\Core;

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

    //   fill

    //   setAttribute

    //   getAttribute

    //   __get

    //   __set

    //   syncOriginal

    //   isDirty

    //   getDirtyAttributes

    //   getConnection

    //   getTable

    //   query

    //   all

    //   find

    //   findOrFail

    //   where

    //   save

    //   insert

    //   update

    //   delete

}