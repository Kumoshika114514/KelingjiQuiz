<?php

namespace App\Strategies;

use Illuminate\Database\Eloquent\Builder;

interface SortingStrategy
{
    public function sort(Builder $query): Builder;
}

