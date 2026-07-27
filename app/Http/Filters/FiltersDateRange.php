<?php

namespace App\Http\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class FiltersDateRange implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (! is_array($value)) {
            return;
        }

        $from = $this->parseDate($value['from'] ?? null)?->startOfDay();
        $to = $this->parseDate($value['to'] ?? null)?->endOfDay();

        if (! $from && ! $to) {
            return;
        }

        if ($from && $to) {
            if ($from->gt($to)) {
                // auto-correct swapped range
                [$from, $to] = [$to, $from];
            }

            $query->whereBetween($property, [$from, $to]);
        } elseif ($from) {
            $query->where($property, '>=', $from);
        } else {
            $query->where($property, '<=', $to);
        }
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception) {
            return null;
        }
    }
}
