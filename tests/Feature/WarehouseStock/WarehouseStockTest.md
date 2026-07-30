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
- **Invalid Requests**: Use `assertInvalidRequest()` when request violates OpenAPI schema
- **Low Stock Alerts**: Test ProductLowStock and ProductOutOfStock event firing

## Common Testing Patterns

### Testing Low Stock Detection
```php
$product = Product::factory()->create(['reorder_level' => 50]);
$warehouse = Warehouse::factory()->create();

WarehouseStock::factory()->create([
    'warehouse_id' => $warehouse->id,
    'product_id' => $product->id,
    'quantity' => 25, // Below reorder level
]);

Event::fake([ProductLowStock::class]);
// Trigger action that checks stock levels
Event::assertDispatched(ProductLowStock::class);
```

### Testing Query Filters
```php
$response = getJson('/api/v1/warehouses/1/stocks?filter[low_stock]=true&sort=quantity');
```

---

## GET /api/v1/warehouses/{warehouse}/stocks
**File**: `tests/Feature/WarehouseStock/GetWarehouseStocksTest.php` (suggested)

```php
describe('List Warehouse Stocks', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires warehouses-view permission'); // WarehousePolicy::view via Gate::authorize
    it('returns paginated list of warehouse stocks with 200'); // WarehouseStockController::index
    it('filters stocks by product ID'); // AllowedFilter::belongsTo('product')
    it('filters stocks by warehouse ID'); // AllowedFilter::belongsTo('warehouse')
    it('filters stocks by quantity greater than value'); // AllowedFilter::operator('quantity', FilterOperator::DYNAMIC)
    it('filters stocks by quantity less than or equal to value'); // AllowedFilter::operator
    it('filters stocks by quantity exact match'); // AllowedFilter::operator
    it('filters low stock items'); // AllowedFilter::scope('low_stock')
    it('sorts stocks by quantity ascending'); // allowedSorts('quantity')
    it('sorts stocks by created_at descending'); // allowedSorts('created_at')
    it('includes product.category relationship'); // allowedIncludes('product.category')
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // WarehousePolicy::view
    it('returns 404 for non-existent warehouse'); // route model binding
    it('returns warehouse stock resource collection'); // WarehouseStockResource::collection
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## GET /api/v1/warehouses/{warehouse}/stocks/{stock}
**File**: `tests/Feature/WarehouseStock/ShowWarehouseStockTest.php` (suggested)

```php
describe('Show Warehouse Stock', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires warehouses-view permission'); // WarehousePolicy::view via Gate::authorize
    it('returns warehouse stock details with 200'); // WarehouseStockController::show
    it('includes product relationship'); // with('product')
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // WarehousePolicy::view
    it('returns 404 for non-existent warehouse'); // route model binding
    it('returns 404 for non-existent stock'); // route model binding
    it('returns warehouse stock resource'); // WarehouseStockResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## PUT/PATCH /api/v1/warehouses/{warehouse}/stocks/{stock}
**File**: `tests/Feature/WarehouseStock/UpdateWarehouseStockTest.php` (suggested)

```php
describe('Update Warehouse Stock', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires warehouses-view permission'); // WarehousePolicy::view via Gate::authorize
    it('updates warehouse stock quantity and returns 200'); // WarehouseStockController::update
    it('validates required quantity field'); // UpdateWarehouseStockRequest quantity required
    it('validates quantity is non-negative integer'); // integer|min:0 - use assertInvalidRequest()
    it('fires ProductLowStock event when quantity drops below reorder level'); // ProductLowStock event
    it('fires ProductOutOfStock event when quantity reaches zero'); // ProductOutOfStock event
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // WarehousePolicy::view
    it('returns 404 for non-existent warehouse'); // route model binding
    it('returns 404 for non-existent stock'); // route model binding
    it('returns updated warehouse stock resource'); // WarehouseStockResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertInvalidRequest()->assertValidResponse(422);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## Key Testing Notes

- **Low Stock Scope**: Test the `low_stock` filter scope correctly identifies products below reorder level
- **Events**: Test ProductLowStock and ProductOutOfStock events fire at appropriate thresholds
- **Quantity Validation**: Quantity must be non-negative (>= 0)
- **Stock Levels**: Test calculation of available stock vs reserved stock
- **Product Relationship**: Always eager load product data for performance
- **Reorder Level**: Compare quantity against product's reorder_level
- **Nested Routes**: Stock endpoints are nested under warehouses
