<?php

namespace App\Strategies\CommentSortingStrategies;

use App\Strategies\SortingStrategy;
use Illuminate\Database\Eloquent\Builder;

class SortByLikes implements SortingStrategy
{
    public function sort(Builder $query): Builder
    {
        return $query->orderByDesc('likes_count');
    }
}

