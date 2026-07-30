# Sales Order API Tests

## Test Organization

Tests should be organized by endpoint into separate files:
- One endpoint per file (e.g., `GetSalesOrdersTest.php`, `CreateSalesOrderTest.php`)
- Use `describe()` blocks with descriptive names

## Testing Guidelines

- **Spectator Setup**: Configured globally in `tests/Pest.php` - do not add to individual test files
- **Contract Testing**: Always use `$response->assertValidRequest()->assertValidResponse(status)`
- **No Duplicate Assertions**: Do not use `assertJsonStructure()` - `assertValidResponse()` already validates against OpenAPI schema
- **File Organization**: One endpoint per file with `describe()` block using descriptive name
- **Invalid Requests**: Use `assertInvalidRequest()` when request violates OpenAPI schema
- **Stock Management**: Test stock validation and reservation
- **Payment Integration**: Test payment gateway integration with mocking

## Common Testing Patterns

### Creating Sales Orders
```php
$warehouse = Warehouse::factory()->create();
$product = Product::factory()->create(['price' => 100]);
$user = User::factory()->create();

// Create warehouse stock for the product
WarehouseStock::factory()->create([
    'warehouse_id' => $warehouse->id,
    'product_id' => $product->id,
    'quantity' => 50,
]);

$response = actingAs($user)->postJson('/api/v1/sales-orders', [
    'warehouse_id' => $warehouse->id,
    'items' => [
        [
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_price' => 100,
        ],
    ],
]);
```

### Testing Stock Validation
```php
// Product with insufficient stock
WarehouseStock::factory()->create([
    'product_id' => $product->id,
    'quantity' => 2, // Only 2 available
]);

$response = postJson('/api/v1/sales-orders', [
    'items' => [
        ['product_id' => $product->id, 'quantity' => 5], // Requesting 5
    ],
]);

$response->assertValidRequest()->assertValidResponse(400);
```

### Testing Payment Flow
```php
$salesOrder = SalesOrder::factory()->create([
    'status' => SalesOrderStatus::Pending,
]);

// Mock payment gateway
$response = postJson("/api/v1/sales-orders/{$salesOrder->id}/initiate-payment");
expect($response->json())->toHaveKeys(['gateway_url', 'transaction_id']);
```

---

## GET /api/v1/sales-orders
**File**: `tests/Feature/SalesOrder/GetSalesOrdersTest.php` (suggested)

```php
describe('List Sales Orders', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires sales-orders-view permission'); // SalesOrderPolicy::viewAny
    it('returns paginated list of sales orders with 200'); // SalesOrderController::index cursorPaginate(10)
    it('includes warehouse, creator, and items relationships'); // with(['warehouse', 'creator', 'items'])
    it('filters sales orders by creator ID'); // AllowedFilter::belongsTo('creator')
    it('filters sales orders by warehouse ID'); // AllowedFilter::belongsTo('warehouse')
    it('filters sales orders by product ID in items'); // AllowedFilter::belongsTo('product', 'items.product')
    it('filters sales orders by status'); // AllowedFilter::exact('status')
    it('filters sales orders by created_at date range'); // AllowedFilter::custom('created_at', FiltersDateRange)
    it('filters sales orders by total_amount range'); // AllowedFilter::operator('total_amount', FilterOperator::DYNAMIC)
    it('sorts sales orders by created_at descending'); // defaultSort('-created_at')
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without sales-orders-view permission'); // SalesOrderPolicy::viewAny
    it('returns sales order resource collection'); // SalesOrderResource::collection
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
});
```

---

## POST /api/v1/sales-orders
**File**: `tests/Feature/SalesOrder/CreateSalesOrderTest.php` (suggested)

```php
describe('Create Sales Order', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires sales-orders-create permission'); // SalesOrderPolicy::create
    it('creates a new sales order and returns 201'); // CreateSalesOrder::execute
    it('creates sales order with multiple items'); // CreateSalesOrder::execute items array
    it('calculates total amount from items'); // sum(quantity * unit_price)
    it('reserves stock from warehouse'); // CreateSalesOrder::execute stock validation
    it('creates stock logs for each item'); // CreateStockLog::execute
    it('sets order status to pending'); // SalesOrderStatus::Pending
    it('assigns creator as authenticated user'); // creator_id = auth()->id()
    it('returns validation errors for invalid input'); // StoreSalesOrderRequest::rules
    it('returns 400 for insufficient stock'); // InsufficientStockException
    it('validates required warehouse_id'); // StoreSalesOrderRequest::rules
    it('validates required items array'); // StoreSalesOrderRequest items required min:1
    it('validates each item has product_id, quantity, unit_price'); // StoreSalesOrderRequest items.*
    it('validates warehouse exists'); // StoreSalesOrderRequest exists:warehouses
    it('validates products exist'); // StoreSalesOrderRequest items.*.product_id exists:products
    it('validates quantity is positive'); // StoreSalesOrderRequest items.*.quantity integer|min:1 - use assertInvalidRequest()
    it('validates unit_price is positive'); // StoreSalesOrderRequest items.*.unit_price numeric|min:0
    it('validates customer_email format'); // StoreSalesOrderRequest email|nullable
    it('validates customer_phone format'); // StoreSalesOrderRequest phone:BD|nullable - use assertInvalidRequest()
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without sales-orders-create permission'); // SalesOrderPolicy::create
    it('returns sales order resource on success'); // SalesOrderResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(201);
    $response->assertValidRequest()->assertValidResponse(400); // insufficient stock
    $response->assertInvalidRequest()->assertValidResponse(422); // schema violations
});
```

---

## GET /api/v1/sales-orders/{salesOrder}
**File**: `tests/Feature/SalesOrder/ShowSalesOrderTest.php` (suggested)

```php
describe('Show Sales Order', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires sales-orders-view permission'); // SalesOrderPolicy::view
    it('returns sales order details with 200'); // SalesOrderController::show
    it('loads warehouse, creator, and items.product relationships'); // eager loading
    it('includes payment information if available'); // payment_transaction_id, gateway_url
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without sales-orders-view permission'); // SalesOrderPolicy::view
    it('returns 404 for non-existent sales order'); // route model binding
    it('returns sales order resource'); // SalesOrderResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## POST /api/v1/sales-orders/{salesOrder}/initiate-payment
**File**: `tests/Feature/SalesOrder/InitiatePaymentTest.php` (suggested)

```php
describe('Initiate Payment', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires sales-orders-initiate-payment permission'); // SalesOrderPolicy::initiatePayment
    it('initiates payment for pending sales order and returns 200'); // InitiateSalesOrderPayment::execute
    it('returns payment gateway URL'); // PaymentInitiationDTO gateway_url
    it('returns transaction ID'); // PaymentInitiationDTO transaction_id
    it('updates order with payment details'); // payment_transaction_id, gateway_url
    it('integrates with SSLCommerz payment gateway'); // Sslcommerz facade
    it('returns 403 when sales order is not pending'); // SalesOrderPolicy::initiatePayment status check
    it('returns 403 when sales order is already paid'); // SalesOrderPolicy::initiatePayment paid check
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without sales-orders-initiate-payment permission'); // SalesOrderPolicy::initiatePayment
    it('returns 404 for non-existent sales order'); // route model binding
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(403);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## PATCH /api/v1/sales-orders/{salesOrder}/cancel
**File**: `tests/Feature/SalesOrder/CancelSalesOrderTest.php` (suggested)

```php
describe('Cancel Sales Order', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires sales-orders-cancel permission'); // SalesOrderPolicy::cancel
    it('cancels pending sales order and returns 204'); // CancelSalesOrder::execute
    it('sets status to cancelled'); // SalesOrderStatus::Cancelled
    it('returns reserved stock to warehouse'); // stock restoration
    it('returns 403 when sales order is paid'); // SalesOrderPolicy::cancel paid check
    it('returns 403 when sales order is completed'); // SalesOrderPolicy::cancel status check
    it('returns 403 when sales order is already cancelled'); // SalesOrderPolicy::cancel status check
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without sales-orders-cancel permission'); // SalesOrderPolicy::cancel
    it('returns 404 for non-existent sales order'); // route model binding
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(204);
    $response->assertValidRequest()->assertValidResponse(403);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## Key Testing Notes

- **Stock Reservation**: When order is created, stock is reserved from warehouse
- **Stock Restoration**: When order is cancelled, stock is returned to warehouse
- **Payment Flow**: Test entire payment lifecycle (initiate → callback → finalize)
- **Status Transitions**: Test valid status transitions (Pending → Paid → Completed)
- **Total Calculation**: Ensure `total_amount` matches sum of `items.quantity * items.unit_price`
- **Transaction Atomicity**: Order creation should be atomic (all or nothing)
- **Customer Information**: Email and phone are optional but should be validated if provided
- **Creator Tracking**: Orders should track who created them
- **Stock Logs**: Each order item should create corresponding stock log entries
- **Payment Gateway**: Mock SSLCommerz in tests, test actual integration separately
