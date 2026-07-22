<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class FiltersDateRange implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (!is_array($value)) return;

        if (isset($value['from']))
        {
            $query->where($property, '>=', $value['from']);
        }

        if (isset($value['to']))
        {
            $query->where($property, '<=', $value['to']);
        }
    }
}
