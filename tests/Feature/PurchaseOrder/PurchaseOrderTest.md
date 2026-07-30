# Purchase Order API Tests

## Test Organization

Tests should be organized by endpoint into separate files:
- One endpoint per file (e.g., `GetPurchaseOrdersTest.php`, `CreatePurchaseOrderTest.php`)
- Use `describe()` blocks with descriptive names

## Testing Guidelines

- **Spectator Setup**: Configured globally in `tests/Pest.php` - do not add to individual test files
- **Contract Testing**: Always use `$response->assertValidRequest()->assertValidResponse(status)`
- **No Duplicate Assertions**: Do not use `assertJsonStructure()` - `assertValidResponse()` already validates against OpenAPI schema
- **File Organization**: One endpoint per file with `describe()` block using descriptive name
- **Invalid Requests**: Use `assertInvalidRequest()` when request violates OpenAPI schema
- **Stock Management**: Test stock addition when receiving orders
- **Status Flow**: Test order lifecycle (Pending → Received)

## Common Testing Patterns

### Creating Purchase Orders
```php
$warehouse = Warehouse::factory()->create();
$supplier = Supplier::factory()->create();
$product = Product::factory()->create();

$response = postJson('/api/v1/purchase-orders', [
    'warehouse_id' => $warehouse->id,
    'supplier_id' => $supplier->id,
    'expected_delivery_date' => now()->addDays(7)->toDateString(),
    'items' => [
        [
            'product_id' => $product->id,
            'quantity' => 100,
            'unit_cost' => 50.00,
        ],
    ],
]);
```

### Testing Order Reception
```php
$purchaseOrder = PurchaseOrder::factory()->create([
    'status' => PurchaseOrderStatus::Pending,
]);

$response = patchJson("/api/v1/purchase-orders/{$purchaseOrder->id}/receive", [
    'received_at' => now()->toDateString(),
]);

expect($purchaseOrder->fresh()->status)->toBe(PurchaseOrderStatus::Received);
// Verify stock was added to warehouse
```

---

## GET /api/v1/purchase-orders
**File**: `tests/Feature/PurchaseOrder/GetPurchaseOrdersTest.php` (suggested)

```php
describe('List Purchase Orders', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires purchase-orders-view permission'); // PurchaseOrderPolicy::viewAny
    it('returns paginated list of purchase orders with 200'); // PurchaseOrderController::index
    it('includes warehouse, supplier, creator, items relationships'); // eager loading
    it('filters by supplier ID'); // AllowedFilter::belongsTo('supplier')
    it('filters by warehouse ID'); // AllowedFilter::belongsTo('warehouse')
    it('filters by status'); // AllowedFilter::exact('status')
    it('filters by created_at date range'); // AllowedFilter::custom
    it('sorts by created_at descending'); // defaultSort('-created_at')
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // PurchaseOrderPolicy::viewAny
    it('returns purchase order resource collection'); // PurchaseOrderResource::collection
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
});
```

---

## POST /api/v1/purchase-orders
**File**: `tests/Feature/PurchaseOrder/CreatePurchaseOrderTest.php` (suggested)

```php
describe('Create Purchase Order', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires purchase-orders-create permission'); // PurchaseOrderPolicy::create
    it('creates a new purchase order and returns 201'); // CreatePurchaseOrder::execute
    it('creates purchase order with multiple items'); // items array
    it('calculates total cost from items'); // sum(quantity * unit_cost)
    it('sets status to pending by default'); // PurchaseOrderStatus::Pending
    it('assigns creator as authenticated user'); // creator_id = auth()->id()
    it('validates required warehouse_id'); // StorePurchaseOrderRequest::rules
    it('validates required supplier_id'); // StorePurchaseOrderRequest::rules
    it('validates required items array'); // items required min:1
    it('validates expected_delivery_date is future'); // after_or_equal:today
    it('validates each item has product_id, quantity, unit_cost'); // items.*
    it('validates warehouse exists'); // exists:warehouses
    it('validates supplier exists'); // exists:suppliers
    it('validates products exist'); // items.*.product_id exists:products
    it('validates quantity is positive'); // items.*.quantity integer|min:1 - use assertInvalidRequest()
    it('validates unit_cost is positive'); // items.*.unit_cost numeric|min:0
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // PurchaseOrderPolicy::create
    it('returns purchase order resource on success'); // PurchaseOrderResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(201);
    $response->assertInvalidRequest()->assertValidResponse(422);
});
```

---

## GET /api/v1/purchase-orders/{purchaseOrder}
**File**: `tests/Feature/PurchaseOrder/ShowPurchaseOrderTest.php` (suggested)

```php
describe('Show Purchase Order', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires purchase-orders-view permission'); // PurchaseOrderPolicy::view
    it('returns purchase order details with 200'); // PurchaseOrderController::show
    it('loads warehouse, supplier, creator, items.product relationships'); // eager loading
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // PurchaseOrderPolicy::view
    it('returns 404 for non-existent purchase order'); // route model binding
    it('returns purchase order resource'); // PurchaseOrderResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## PUT/PATCH /api/v1/purchase-orders/{purchaseOrder}
**File**: `tests/Feature/PurchaseOrder/UpdatePurchaseOrderTest.php` (suggested)

```php
describe('Update Purchase Order', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires purchase-orders-update permission'); // PurchaseOrderPolicy::update
    it('updates purchase order and returns 200'); // UpdatePurchaseOrder::execute
    it('updates supplier'); // UpdatePurchaseOrderRequest supplier_id
    it('updates expected delivery date'); // UpdatePurchaseOrderRequest expected_delivery_date
    it('updates items'); // UpdatePurchaseOrderRequest items array
    it('recalculates total cost'); // sum(quantity * unit_cost)
    it('returns 403 when order is received'); // PurchaseOrderPolicy::update status check
    it('allows partial updates'); // optional fields
    it('validates supplier exists'); // exists:suppliers
    it('validates products exist'); // items.*.product_id exists:products
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // PurchaseOrderPolicy::update
    it('returns 404 for non-existent purchase order'); // route model binding
    it('returns updated purchase order resource'); // PurchaseOrderResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(403);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## PATCH /api/v1/purchase-orders/{purchaseOrder}/receive
**File**: `tests/Feature/PurchaseOrder/ReceivePurchaseOrderTest.php` (suggested)

```php
describe('Receive Purchase Order', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires purchase-orders-receive permission'); // PurchaseOrderPolicy::receive
    it('receives pending purchase order and returns 200'); // ReceivePurchaseOrder::execute
    it('sets status to received'); // PurchaseOrderStatus::Received
    it('sets received_at timestamp'); // received_at = now()
    it('adds stock to warehouse for each item'); // WarehouseStock update
    it('creates stock logs for each item'); // CreateStockLog::execute
    it('returns 403 when order is already received'); // PurchaseOrderPolicy::receive status check
    it('validates received_at date'); // ReceivePurchaseOrderRequest::rules
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // PurchaseOrderPolicy::receive
    it('returns 404 for non-existent purchase order'); // route model binding
    it('returns received purchase order resource'); // PurchaseOrderResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(403);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## Key Testing Notes

- **Status Flow**: Pending → Received (no going back)
- **Stock Addition**: Receiving order adds stock to warehouse (opposite of sales orders)
- **Stock Logs**: Create stock logs with type "Purchase" when receiving
- **Edit Restrictions**: Can only edit pending orders, not received ones
- **Total Calculation**: `total_cost` = sum of `items.quantity * items.unit_cost`
- **Expected Delivery**: Track expected vs actual delivery dates
- **Creator Tracking**: Track who created and who received the order
- **Supplier Relationship**: Each order belongs to one supplier
- **Transaction Atomicity**: Receiving order should be atomic (all stock updates succeed or none)
