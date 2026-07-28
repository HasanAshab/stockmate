# Search API Tests

## GET /api/v1/search

```php
it('requires authentication'); // auth:sanctum middleware
it('returns global search results across all resources with 200'); // SearchController::searchAll
it('searches products when user has products-view permission'); // PerformSearch::execute + Gate::allows
it('searches categories when user has categories-view permission'); // PerformSearch::execute + Gate::allows
it('searches suppliers when user has suppliers-view permission'); // PerformSearch::execute + Gate::allows
it('searches warehouses when user has warehouses-view permission'); // PerformSearch::execute + Gate::allows
it('excludes resources user cannot view from results'); // collect($targets)->filter(fn => Gate::allows)
it('returns empty results for search term less than minimum length'); // validatedTerm check mb_strlen < min_length
it('returns empty results for empty search term'); // validatedTerm check
it('omits resource types with zero matches from response'); // reject(fn => $matches->isEmpty())
it('applies configured limit per resource type'); // config('search.limit.global')
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
```

## GET /api/v1/search/{scope}

```php
it('requires authentication'); // auth:sanctum middleware
it('requires permission for the searched resource type'); // Gate::authorize($entry['ability'], $entry['model'])
it('returns scoped search results for products with 200'); // SearchController::searchScope
it('returns scoped search results for categories with 200'); // SearchController::searchScope
it('returns scoped search results for suppliers with 200'); // SearchController::searchScope
it('returns scoped search results for warehouses with 200'); // SearchController::searchScope
it('returns empty results for search term less than minimum length'); // validatedTerm check mb_strlen < min_length
it('returns empty results for empty search term'); // validatedTerm check
it('applies configured limit for scoped search'); // config('search.limit.scoped')
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without permission for resource type'); // Gate::authorize
it('returns 404 for invalid scope'); // config('search.scopes')[$scope] === null
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```
