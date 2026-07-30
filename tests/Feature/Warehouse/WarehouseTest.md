# Warehouse API Tests

## Test Organization

Tests should be organized by endpoint into separate files:
- One endpoint per file (e.g., `GetWarehousesTest.php`, `CreateWarehouseTest.php`)
- Use `describe()` blocks with descriptive names

## Testing Guidelines

- **Spectator Setup**: Configured globally in `tests/Pest.php` - do not add to individual test files
- **Contract Testing**: Always use `$response->assertValidRequest()->assertValidResponse(status)`
- **No Duplicate Assertions**: Do not use `assertJsonStructure()` - `assertValidResponse()` already validates against OpenAPI schema
- **File Organization**: One endpoint per file with `describe()` block using descriptive name
- **Invalid Requests**: Use `assertInvalidRequest()` when request violates OpenAPI schema
- **Deletion Rules**: Warehouse cannot be deleted if it has stock (test via DeleteWarehouse action)

## Common Testing Patterns

### Creating Warehouses
```php
$response = postJson('/api/v1/warehouses', [
    'name' => 'Main Warehouse',
    'location' => 'Dhaka, Bangladesh',
]);
```

### Testing Deletion with Stock
```php
$warehouse = Warehouse::factory()->create();
$product = Product::factory()->create();

// Add stock to warehouse
WarehouseStock::factory()->create([
    'warehouse_id' => $warehouse->id,
    'product_id' => $product->id,
    'quantity' => 100,
]);

$response = deleteJson("/api/v1/warehouses/{$warehouse->id}");
$response->assertValidRequest()->assertValidResponse(400); // WarehouseNotEmpty exception
```

---

## GET /api/v1/warehouses
**File**: `tests/Feature/Warehouse/GetWarehousesTest.php` (suggested)

```php
describe('List Warehouses', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires warehouses-view permission'); // WarehousePolicy::viewAny
    it('returns paginated list of warehouses with 200'); // WarehouseController::index
    it('filters warehouses by name'); // AllowedFilter::partial('name')
    it('filters warehouses by location'); // AllowedFilter::partial('location')
    it('sorts warehouses by created_at'); // allowedSorts('created_at')
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // WarehousePolicy::viewAny
    it('returns warehouse resource collection'); // WarehouseResource::collection
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
});
```

---

## POST /api/v1/warehouses
**File**: `tests/Feature/Warehouse/CreateWarehouseTest.php` (suggested)

```php
describe('Create Warehouse', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires warehouses-create permission'); // WarehousePolicy::create
    it('creates a new warehouse and returns 201'); // WarehouseController::store
    it('validates required name'); // StoreWarehouseRequest::rules
    it('validates required location'); // StoreWarehouseRequest::rules
    it('validates max length for name'); // max:100 - use assertInvalidRequest()
    it('validates max length for location'); // max:255 - use assertInvalidRequest()
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // WarehousePolicy::create
    it('returns warehouse resource on success'); // WarehouseResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(201);
    $response->assertInvalidRequest()->assertValidResponse(422);
});
```

---

## GET /api/v1/warehouses/{warehouse}
**File**: `tests/Feature/Warehouse/ShowWarehouseTest.php` (suggested)

```php
describe('Show Warehouse', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires warehouses-view permission'); // WarehousePolicy::view
    it('returns warehouse details with 200'); // WarehouseController::show
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // WarehousePolicy::view
    it('returns 404 for non-existent warehouse'); // route model binding
    it('returns warehouse resource'); // WarehouseResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## PUT/PATCH /api/v1/warehouses/{warehouse}
**File**: `tests/Feature/Warehouse/UpdateWarehouseTest.php` (suggested)

```php
describe('Update Warehouse', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires warehouses-update permission'); // WarehousePolicy::update
    it('updates warehouse and returns 200'); // WarehouseController::update
    it('allows partial updates'); // optional fields
    it('validates max length for name'); // max:100 - use assertInvalidRequest()
    it('validates max length for location'); // max:255 - use assertInvalidRequest()
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // WarehousePolicy::update
    it('returns 404 for non-existent warehouse'); // route model binding
    it('returns updated warehouse resource'); // WarehouseResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## DELETE /api/v1/warehouses/{warehouse}
**File**: `tests/Feature/Warehouse/DeleteWarehouseTest.php` (suggested)

```php
describe('Delete Warehouse', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires warehouses-delete permission'); // WarehousePolicy::delete
    it('deletes empty warehouse and returns 204'); // DeleteWarehouse::execute
    it('returns 400 when warehouse has stock'); // DeleteWarehouse WarehouseNotEmpty exception
    it('returns 400 when warehouse has pending orders'); // business rule check
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // WarehousePolicy::delete
    it('returns 404 for non-existent warehouse'); // route model binding
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(204);
    $response->assertValidRequest()->assertValidResponse(400); // not empty
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## Key Testing Notes

- **Deletion Rules**: Warehouse can only be deleted if it's empty (no stock, no pending orders)
- **Stock Association**: Each warehouse has many warehouse_stocks
- **Order Association**: Warehouses are referenced in sales_orders and purchase_orders
- **Activity Logging**: Warehouse CRUD actions should be logged
- **Default Warehouse**: Consider if app needs concept of "default" warehouse
