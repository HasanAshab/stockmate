# Warehouse Stock API Tests

## Test Organization

Tests should be organized by endpoint into separate files:
- One endpoint per file (e.g., `GetWarehouseStocksTest.php`, `UpdateWarehouseStockTest.php`)
- Use `describe()` blocks with descriptive names

## Testing Guidelines

- **Spectator Setup**: Configured globally in `tests/Pest.php` - do not add to individual test files
- **Contract Testing**: Always use `$response->assertValidRequest()->assertValidResponse(status)`
- **No Duplicate Assertions**: Do not use `assertJsonStructure()` - `assertValidResponse()` already validates against OpenAPI schema
- **File Organization**: One endpoint per file with `describe()` block using descriptive name

---

## GET /api/v1/warehouses/{warehouse}/stocks

```php
it('requires authentication'); // auth:sanctum middleware
it('requires warehouses-view permission'); // WarehousePolicy::view via Gate::authorize
it('returns paginated list of warehouse stocks with 200'); // WarehouseStockController::index cursorPaginate(15)
it('filters stocks by product ID'); // AllowedFilter::belongsTo('product')
it('filters stocks by warehouse ID'); // AllowedFilter::belongsTo('warehouse')
it('filters stocks by quantity greater than value'); // AllowedFilter::operator('quantity', FilterOperator::DYNAMIC)
it('filters stocks by quantity less than or equal to value'); // AllowedFilter::operator('quantity', FilterOperator::DYNAMIC)
it('filters stocks by quantity exact match'); // AllowedFilter::operator('quantity', FilterOperator::DYNAMIC)
it('filters low stock items'); // AllowedFilter::scope('low_stock')
it('sorts stocks by quantity ascending'); // allowedSorts('quantity')
it('sorts stocks by created_at descending'); // allowedSorts('created_at')
it('includes product.category relationship'); // allowedIncludes('product.category')
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without warehouses-view permission'); // WarehousePolicy::view
it('returns 404 for non-existent warehouse'); // route model binding
it('returns warehouse stock resource collection'); // WarehouseStockResource::collection
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

## GET /api/v1/warehouses/{warehouse}/stocks/{stock}

```php
it('requires authentication'); // auth:sanctum middleware
it('requires warehouses-view permission'); // WarehousePolicy::view via Gate::authorize
it('returns warehouse stock details with 200'); // WarehouseStockController::show
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without warehouses-view permission'); // WarehousePolicy::view
it('returns 404 for non-existent warehouse'); // route model binding
it('returns 404 for non-existent stock'); // route model binding
it('returns warehouse stock resource'); // WarehouseStockController::show WarehouseStockResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

## PUT/PATCH /api/v1/warehouses/{warehouse}/stocks/{stock}

```php
it('requires authentication'); // auth:sanctum middleware
it('requires warehouses-view permission'); // WarehousePolicy::view via Gate::authorize
it('updates warehouse stock quantity and returns 200'); // WarehouseStockController::update
it('returns validation errors for invalid input'); // UpdateWarehouseStockRequest::rules
it('validates required quantity field'); // UpdateWarehouseStockRequest quantity required
it('validates quantity is non-negative integer'); // UpdateWarehouseStockRequest integer|min:0
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without warehouses-view permission'); // WarehousePolicy::view
it('returns 404 for non-existent warehouse'); // route model binding
it('returns 404 for non-existent stock'); // route model binding
it('returns updated warehouse stock resource'); // WarehouseStockController::update WarehouseStockResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```
