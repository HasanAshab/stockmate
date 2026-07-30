<?php

namespace App\Http\Controllers;

use App\Actions\Search\PerformSearch;
use App\Enums\SearchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class SearchController extends Controller
{
    /**
     * Global Search
     *
     * Search across all resources (products, categories, suppliers, warehouses) that the user is authorized to view.
     */
    public function searchAll(Request $request, PerformSearch $search)
    {
        $term = $this->validatedTerm($request, SearchContext::Global);

        if ($term === null) {
            return ['data' => (object) []];
        }

        // Global search fails *quietly* per scope: a resource the user
        // can't view is excluded from results rather than surfacing an
        // error, since they never asked for that resource specifically.
        $targets = collect(config('search.scopes'))
            ->filter(fn (array $entry) => Gate::allows($entry['ability'], $entry['model']))
            ->all();

        $results = $this->collectResults(
            $targets,
            $term,
            SearchContext::Global,
            $search
        );

        return ['data' => $results];
    }

    /**
     * Scoped Search
     *
     * Search within a specific resource type (products, categories, suppliers, or warehouses).
     */
    public function searchScope(Request $request, string $scope, PerformSearch $search)
    {
        $entry = config('search.scopes')[$scope];

        // `scope` is client input already narrowed by the route's
        // whereIn constraint, but re-checked here since that constraint
        // is a fast-fail, not the source of truth. Used only as a
        // config lookup key — never to resolve a class name directly.
        if ($entry === null) {
            abort(404, "Unknown search scope [{$scope}].");
        }

        // A named scope the user isn't allowed to view fails loudly —
        // this is someone hitting one resource's search box directly.
        Gate::authorize($entry['ability'], $entry['model']);

        $term = $this->validatedTerm($request, SearchContext::Scoped);

        if ($term === null) {
            return ['data' => (object) []];
        }

        $results = $this->collectResults(
            [$scope => $entry],
            $term,
            SearchContext::Scoped,
            $search
        );

        return ['data' => $results];
    }

    /**
     * Trim the incoming term and enforce the configured minimum length
     * for the given context. Returns null when
     * the term is too short, signalling callers to short-circuit with
     * an empty result set rather than running any query.
     */
    private function validatedTerm(Request $request, SearchContext $context): ?string
    {
        $term = trim((string) $request->query('q'));
        $minLength = config("search.min_length.{$context->value}");

        return mb_strlen($term) >= $minLength ? $term : null;
    }

    /**
     * Run the search against every target scope and shape matches
     * through each scope's configured resource. Scopes with zero
     * matches are dropped from the response rather than included empty,
     * so the frontend can render only sections that actually have results.
     *
     * @param  array<string, array{model: class-string, ability: string, resource: class-string}>  $targets
     */
    private function collectResults(array $targets, string $term, SearchContext $context, PerformSearch $search): Collection
    {
        $limit = config("search.limit.{$context->value}");

        return collect($targets)
            ->mapWithKeys(function (array $entry, string $key) use ($term, $limit, $search) {
                $matches = $search->execute($entry['model'], $term, limit: $limit);

                return [$key => $entry['resource']::collection($matches)];
            })
            ->reject(fn (Collection $matches) => $matches->isEmpty());
    }
}
