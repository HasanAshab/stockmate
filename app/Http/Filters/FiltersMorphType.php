<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\QueryBuilder\Filters\Filter;

class FiltersMorphType implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $class = Relation::getMorphedModel($value);

        abort_unless($class !== null, 422, 'Invalid type.');

        $query->where($property, $class);
    }
}