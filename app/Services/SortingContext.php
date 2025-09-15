<?php

namespace App\Services;

use App\Strategies\SortingStrategy;
use Illuminate\Database\Eloquent\Builder;

class SortingContext
{
    private SortingStrategy $strategy;

    public function __construct(SortingStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function setStrategy(SortingStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function sort(Builder $query): Builder
    {
        return $this->strategy->sort($query);
    }
}

