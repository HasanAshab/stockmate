# Sales Order API Tests

## GET /api/v1/sales-orders

```php
it('requires authentication'); // auth:sanctum middleware
it('requires sales-orders-view permission'); // SalesOrderPolicy::viewAny
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without sales-orders-view permission'); // SalesOrderPolicy::viewAny
it('returns paginated list of sales orders with 200'); // SalesOrderController::index cursorPaginate(10)
it('includes warehouse, creator, and items relationships'); // with(['warehouse', 'creator', 'items'])
it('filters sales orders by creator ID'); // AllowedFilter::belongsTo('creator')
it('filters sales orders by warehouse ID'); // AllowedFilter::belongsTo('warehouse')
it('filters sales orders by product ID in items'); // AllowedFilter::belongsTo('product', 'items.product')
it('filters sales orders by status'); // AllowedFilter::exact('status')
it('filters sales orders by created_at date range'); // AllowedFilter::custom('created_at', FiltersDateRange)
it('filters sales orders by total_amount greater than value'); // AllowedFilter::operator('total_amount', FilterOperator::DYNAMIC)
it('filters sales orders by total_amount less than or equal to value'); // AllowedFilter::operator('total_amount', FilterOperator::DYNAMIC)
it('filters sales orders by total_amount exact match'); // AllowedFilter::operator('total_amount', FilterOperator::DYNAMIC)
it('sorts sales orders by created_at descending'); // defaultSort('-created_at')
it('returns sales order resource collection'); // SalesOrderResource::collection
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
```

## POST /api/v1/sales-orders

```php
it('requires authentication'); // auth:sanctum middleware
it('requires sales-orders-create permission'); // SalesOrderPolicy::create
it('creates a new sales order and returns 201'); // SalesOrderController::store
it('creates sales order with multiple items'); // CreateSalesOrder::execute
it('reserves stock from warehouse'); // CreateSalesOrder::execute stock validation
it('returns validation errors for invalid input'); // StoreSalesOrderRequest::rules
it('returns 400 for insufficient stock'); // CreateSalesOrder::validateStock ValidationException
it('validates required warehouse_id'); // StoreSalesOrderRequest warehouse_id required
it('validates required items array'); // StoreSalesOrderRequest items required
it('validates items array has at least one item'); // StoreSalesOrderRequest items min:1
it('validates each item has product_id, quantity, and unit_price'); // StoreSalesOrderRequest items.*.product_id, quantity, unit_price required
it('validates warehouse_id exists'); // StoreSalesOrderRequest exists:warehouses,id
it('validates product_id exists for each item'); // StoreSalesOrderRequest items.*.product_id exists:products,id
it('validates quantity is positive integer'); // StoreSalesOrderRequest items.*.quantity integer|min:1
it('validates unit_price is numeric and positive'); // StoreSalesOrderRequest items.*.unit_price numeric|min:0
it('validates customer_email format when provided'); // StoreSalesOrderRequest customer_email email|nullable
it('validates customer_phone format when provided'); // StoreSalesOrderRequest customer_phone phone:BD|nullable
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without sales-orders-create permission'); // SalesOrderPolicy::create
it('returns sales order resource on success'); // SalesOrderController::store SalesOrderResource
$response->assertValidRequest()->assertValidResponse(201);
$response->assertValidRequest()->assertValidResponse(400);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
```

## GET /api/v1/sales-orders/{salesOrder}

```php
it('requires authentication'); // auth:sanctum middleware
it('requires sales-orders-view permission'); // SalesOrderPolicy::view
it('returns sales order details with 200'); // SalesOrderController::show
it('loads warehouse, creator, and items.product relationships'); // $salesOrder->load(['warehouse', 'creator', 'items.product'])
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without sales-orders-view permission'); // SalesOrderPolicy::view
it('returns 404 for non-existent sales order'); // route model binding
it('returns sales order resource'); // SalesOrderController::show SalesOrderResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

## POST /api/v1/sales-orders/{salesOrder}/initiate-payment

```php
it('requires authentication'); // auth:sanctum middleware
it('requires sales-orders-initiate-payment permission'); // SalesOrderPolicy::initiatePayment
it('initiates payment for pending sales order and returns 200'); // SalesOrderController::initiatePayment
it('returns payment gateway URL and transaction details'); // InitiateSalesOrderPayment::execute
it('returns 403 when sales order is not pending'); // SalesOrderPolicy::initiatePayment status check
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without sales-orders-initiate-payment permission'); // SalesOrderPolicy::initiatePayment
it('returns 404 for non-existent sales order'); // route model binding
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

## PATCH /api/v1/sales-orders/{salesOrder}/cancel

```php
it('requires authentication'); // auth:sanctum middleware
it('requires sales-orders-cancel permission'); // SalesOrderPolicy::cancel
it('cancels pending sales order and returns 204'); // SalesOrderController::cancel
it('sets status to cancelled'); // CancelSalesOrder::execute
it('returns 403 when sales order is paid'); // SalesOrderPolicy::cancel paid check
it('returns 403 when sales order is not pending'); // SalesOrderPolicy::cancel status check
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without sales-orders-cancel permission'); // SalesOrderPolicy::cancel
it('returns 404 for non-existent sales order'); // route model binding
$response->assertValidRequest()->assertValidResponse(204);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```
