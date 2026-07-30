# Search API Tests

## Test Organization

Tests should be organized by endpoint into separate files:
- One endpoint per file (e.g., `SearchTest.php`)
- Use `describe()` blocks with descriptive names

## Testing Guidelines

- **Spectator Setup**: Configured globally in `tests/Pest.php` - do not add to individual test files
- **Contract Testing**: Always use `$response->assertValidRequest()->assertValidResponse(status)`
- **No Duplicate Assertions**: Do not use `assertJsonStructure()` - `assertValidResponse()` already validates against OpenAPI schema
- **File Organization**: One endpoint per file with `describe()` block using descriptive name
- **Scout/Search**: If using Laravel Scout, ensure searchable models are properly indexed
- **Context Filtering**: Search can be scoped to specific models (products, users, etc.)

## Common Testing Patterns

### Testing Search Functionality
```php
// Create searchable data
Product::factory()->create(['name' => 'Laptop Computer', 'sku' => 'COMP-001']);
Product::factory()->create(['name' => 'Desktop Computer', 'sku' => 'COMP-002']);
User::factory()->create(['name' => 'John Computer Smith']);

// Search across contexts
$response = getJson('/api/v1/search?query=computer&context=all');
expect($response->json('products'))->toHaveCount(2);
expect($response->json('users'))->toHaveCount(1);
```

### Testing Context Filtering
```php
// Search only in products
$response = getJson('/api/v1/search?query=laptop&context=products');
expect($response->json())->toHaveKey('products');
expect($response->json())->not->toHaveKey('users');
```

---

## GET /api/v1/search
**File**: `tests/Feature/Search/SearchTest.php` (suggested)

```php
describe('Search', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('returns search results with 200'); // SearchController::index PerformSearch::execute
    it('searches across all contexts by default'); // SearchContext::All
    it('searches products by name and SKU'); // Product::search($query)
    it('searches users by name and email'); // User::search($query)
    it('searches categories by name'); // Category::search($query)
    it('searches suppliers by name'); // Supplier::search($query)
    it('filters search by context parameter'); // SearchContext enum
    it('returns empty results for non-matching query'); // search returns empty array
    it('requires query parameter'); // SearchRequest::rules query required
    it('validates context enum value'); // SearchRequest Rule::enum(SearchContext)
    it('returns validation errors for invalid context'); // SearchRequest::rules
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('groups results by model type'); // PerformSearch groups by context
    it('limits results per context'); // take(10) per context
    it('performs case-insensitive search'); // search is case-insensitive
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertInvalidRequest()->assertValidResponse(422);
});
```

---

## Key Testing Notes

- **Search Engine**: If using Scout with Algolia/Meilisearch/etc., ensure test data is indexed
- **Context Validation**: Test that invalid context values are rejected
- **Permission Filtering**: Users should only see results they have permission to view
- **Result Limiting**: Prevent returning too many results (paginate or limit)
- **Query Length**: Test minimum query length requirements (e.g., min:2 characters)
- **Special Characters**: Test search with special characters, quotes, wildcards
- **Performance**: Search should be fast, use proper indexes
- **Relevance**: Most relevant results should appear first
