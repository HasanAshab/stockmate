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

---

## GET /api/v1/purchase-orders

```php
it('requires authentication'); // auth:sanctum middleware
it('requires purchase-orders-view permission'); // PurchaseOrderPolicy::viewAny
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without purchase-orders-view permission'); // PurchaseOrderPolicy::viewAny
it('returns paginated list of purchase orders with 200'); // PurchaseOrderController::index cursorPaginate(10)
it('includes supplier, warehouse, creator, and items relationships'); // with(['supplier', 'warehouse', 'creator', 'items'])
it('filters purchase orders by supplier ID'); // AllowedFilter::belongsTo('supplier')
it('filters purchase orders by warehouse ID'); // AllowedFilter::belongsTo('warehouse')
it('filters purchase orders by creator ID'); // AllowedFilter::belongsTo('creator')
it('filters purchase orders by product ID in items'); // AllowedFilter::belongsTo('product', 'items.product')
it('filters purchase orders by status'); // AllowedFilter::exact('status')
it('filters purchase orders by ordered_at date range'); // AllowedFilter::custom('ordered_at', FiltersDateRange)
it('filters purchase orders by received_at date range'); // AllowedFilter::custom('received_at', FiltersDateRange)
it('filters purchase orders by created_at date range'); // AllowedFilter::custom('created_at', FiltersDateRange)
it('sorts purchase orders by created_at descending'); // defaultSort('-created_at')
it('returns purchase order resource collection'); // PurchaseOrderResource::collection
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
```

## POST /api/v1/purchase-orders

```php
it('requires authentication'); // auth:sanctum middleware
it('requires purchase-orders-create permission'); // PurchaseOrderPolicy::create
it('creates a new purchase order and returns 201'); // PurchaseOrderController::store
it('creates purchase order with multiple items'); // CreatePurchaseOrder::execute
it('returns validation errors for invalid input'); // StorePurchaseOrderRequest::rules
it('validates required supplier_id'); // StorePurchaseOrderRequest supplier_id required
it('validates required warehouse_id'); // StorePurchaseOrderRequest warehouse_id required
it('validates required items array'); // StorePurchaseOrderRequest items required
it('validates items array has at least one item'); // StorePurchaseOrderRequest items min:1
it('validates each item has product_id, quantity, and unit_price'); // StorePurchaseOrderRequest items.*.product_id, quantity, unit_price required
it('validates supplier_id exists'); // StorePurchaseOrderRequest exists:suppliers,id
it('validates warehouse_id exists'); // StorePurchaseOrderRequest exists:warehouses,id
it('validates product_id exists for each item'); // StorePurchaseOrderRequest items.*.product_id exists:products,id
it('validates quantity is positive integer'); // StorePurchaseOrderRequest items.*.quantity integer|min:1
it('validates unit_price is numeric and positive'); // StorePurchaseOrderRequest items.*.unit_price numeric|min:0
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without purchase-orders-create permission'); // PurchaseOrderPolicy::create
it('returns purchase order resource on success'); // PurchaseOrderController::store PurchaseOrderResource
$response->assertValidRequest()->assertValidResponse(201);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
```

## GET /api/v1/purchase-orders/{purchaseOrder}

```php
it('requires authentication'); // auth:sanctum middleware
it('requires purchase-orders-view permission'); // PurchaseOrderPolicy::view
it('returns purchase order details with 200'); // PurchaseOrderController::show
it('loads supplier, warehouse, creator, and items.product relationships'); // $purchaseOrder->load(['supplier', 'warehouse', 'creator', 'items.product'])
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without purchase-orders-view permission'); // PurchaseOrderPolicy::view
it('returns 404 for non-existent purchase order'); // route model binding
it('returns purchase order resource'); // PurchaseOrderController::show PurchaseOrderResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

## PUT/PATCH /api/v1/purchase-orders/{purchaseOrder}

```php
it('requires authentication'); // auth:sanctum middleware
it('requires purchase-orders-update permission'); // PurchaseOrderPolicy::update
it('updates draft purchase order and returns 200'); // PurchaseOrderController::update
it('returns validation errors for invalid input'); // UpdatePurchaseOrderRequest::rules
it('returns 403 when updating received purchase order'); // PurchaseOrderPolicy::update status check
it('returns 403 when updating cancelled purchase order'); // PurchaseOrderPolicy::update status check
it('allows partial updates'); // UpdatePurchaseOrderRequest optional fields
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without purchase-orders-update permission'); // PurchaseOrderPolicy::update
it('returns 404 for non-existent purchase order'); // route model binding
it('returns updated purchase order resource'); // PurchaseOrderController::update PurchaseOrderResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

## PATCH /api/v1/purchase-orders/{purchaseOrder}/mark-ordered

```php
it('requires authentication'); // auth:sanctum middleware
it('requires purchase-orders-mark-ordered permission'); // PurchaseOrderPolicy::markOrdered
it('marks draft purchase order as ordered and returns 200'); // PurchaseOrderController::markOrdered
it('sets status to ordered and ordered_at timestamp'); // update(['status' => PurchaseOrderStatus::Ordered, 'ordered_at' => now()])
it('returns 403 when purchase order is not draft'); // PurchaseOrderPolicy::markOrdered status check
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without purchase-orders-mark-ordered permission'); // PurchaseOrderPolicy::markOrdered
it('returns 404 for non-existent purchase order'); // route model binding
it('returns purchase order resource'); // PurchaseOrderController::markOrdered PurchaseOrderResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

## PATCH /api/v1/purchase-orders/{purchaseOrder}/cancel

```php
it('requires authentication'); // auth:sanctum middleware
it('requires purchase-orders-cancel permission'); // PurchaseOrderPolicy::cancel
it('cancels draft purchase order and returns 200'); // PurchaseOrderController::cancel
it('cancels ordered purchase order and returns 200'); // PurchaseOrderController::cancel
it('sets status to cancelled'); // update(['status' => PurchaseOrderStatus::Cancelled])
it('returns 403 when purchase order has received stock'); // PurchaseOrderPolicy::cancel partially received check
it('returns 403 when purchase order is fully received'); // PurchaseOrderPolicy::cancel received check
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without purchase-orders-cancel permission'); // PurchaseOrderPolicy::cancel
it('returns 404 for non-existent purchase order'); // route model binding
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

## POST /api/v1/purchase-orders/{purchaseOrder}/receive

```php
it('requires authentication'); // auth:sanctum middleware
it('requires purchase-orders-receive permission'); // PurchaseOrderPolicy::receive
it('receives stock for ordered purchase order and returns 200'); // PurchaseOrderController::receive
it('receives stock for partially received purchase order and returns 200'); // PurchaseOrderController::receive
it('updates warehouse stock levels'); // ReceivePurchaseOrder::execute
it('returns validation errors for invalid input'); // ReceivePurchaseOrderRequest::rules
it('validates required items array'); // ReceivePurchaseOrderRequest items required
it('validates items array has at least one item'); // ReceivePurchaseOrderRequest items min:1
it('validates each item has purchase_order_item_id and received_quantity'); // ReceivePurchaseOrderRequest items.*.purchase_order_item_id, received_quantity required
it('validates received_quantity is positive integer'); // ReceivePurchaseOrderRequest items.*.received_quantity integer|min:1
it('returns 403 when purchase order is not ordered or partially received'); // PurchaseOrderPolicy::receive status check
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without purchase-orders-receive permission'); // PurchaseOrderPolicy::receive
it('returns 404 for non-existent purchase order'); // route model binding
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```
