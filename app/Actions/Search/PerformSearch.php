<?php

namespace App\Actions\Search;

use Illuminate\Support\Collection;

/**
 * Requires every $modelClass passed in to use Scout's own
 * Laravel\Scout\Searchable trait and define toSearchableArray(). See
 * config/search.php for the list of models wired up this way.
 */
class PerformSearch
{
    public function __construct(
        /** Guards cost on every driver, not just query performance on the DB engine. */
        private readonly int $minLength = 2,

        /** Default result cap, used when no per-call limit is given. */
        private readonly int $limit = 10,
    ) {}

    /**
     * @param  class-string  $modelClass  Model using Laravel\Scout\Searchable.
     * @param  string|null  $term  Raw search term; trimmed internally.
     * @param  int|null  $limit  Overrides the action's default limit for
     *                           this call (global search wants fewer rows per model than
     *                           a single scoped search box).
     */
    public function execute(string $modelClass, ?string $term, ?int $limit = null): Collection
    {
        $term = trim((string) $term);

        if (mb_strlen($term) < $this->minLength) {
            return collect();
        }

        return $modelClass::search($term)
            ->take($limit ?? $this->limit)
            ->get();
    }
}
