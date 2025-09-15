<?php

namespace App\Strategies\CommentSortingStrategies;

use App\Strategies\SortingStrategy;
use Illuminate\Database\Eloquent\Builder;

class SortByOldest implements SortingStrategy
{
    public function sort(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'asc');
    }
}

