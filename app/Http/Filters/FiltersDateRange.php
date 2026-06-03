<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class FiltersDateRange implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (is_array($value) && isset($value['from']) && isset($value['to'])) {
            $query->whereBetween($property, [$value['from'], $value['to']]);
        }
    }
}
