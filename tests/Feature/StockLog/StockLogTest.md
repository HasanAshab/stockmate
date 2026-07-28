# Stock Log API Tests

## GET /api/v1/stock-logs

```php
it('requires authentication'); // auth:sanctum middleware
it('requires stock-logs-view permission'); // StockLogPolicy::viewAny
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without stock-logs-view permission'); // StockLogPolicy::viewAny
it('returns paginated list of stock logs with 200'); // StockLogController::index cursorPaginate(10)
it('filters stock logs by product ID'); // AllowedFilter::belongsTo('product')
it('filters stock logs by user ID'); // AllowedFilter::belongsTo('user')
it('filters stock logs by warehouse ID'); // AllowedFilter::belongsTo('warehouse')
it('filters stock logs by type'); // AllowedFilter::exact('type')
it('filters stock logs by quantity greater than value'); // AllowedFilter::operator('quantity', FilterOperator::DYNAMIC)
it('filters stock logs by quantity less than or equal to value'); // AllowedFilter::operator('quantity', FilterOperator::DYNAMIC)
it('filters stock logs by quantity exact match'); // AllowedFilter::operator('quantity', FilterOperator::DYNAMIC)
it('filters stock logs by unit_cost greater than value'); // AllowedFilter::operator('unit_cost', FilterOperator::DYNAMIC)
it('filters stock logs by unit_cost less than or equal to value'); // AllowedFilter::operator('unit_cost', FilterOperator::DYNAMIC)
it('filters stock logs by unit_cost exact match'); // AllowedFilter::operator('unit_cost', FilterOperator::DYNAMIC)
it('filters stock logs by created_at date range'); // AllowedFilter::custom('created_at', FiltersDateRange)
it('sorts stock logs by created_at descending'); // allowedSorts('created_at')
it('sorts stock logs by quantity ascending'); // allowedSorts('quantity')
it('sorts stock logs by unit_cost ascending'); // allowedSorts('unit_cost')
it('includes user relationship'); // allowedIncludes('user')
it('includes product relationship'); // allowedIncludes('product')
it('includes warehouse relationship'); // allowedIncludes('warehouse')
it('returns stock log resource collection'); // StockLogResource::collection
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
```

## POST /api/v1/stock-logs

```php
it('requires authentication'); // auth:sanctum middleware
it('requires stock-logs-create permission'); // StockLogPolicy::create
it('creates a new stock log and returns 201'); // StockLogController::store
it('creates stock log with type in'); // CreateStockLog::execute
it('creates stock log with type out'); // CreateStockLog::execute
it('creates stock log with type adjustment'); // CreateStockLog::execute
it('returns validation errors for invalid input'); // StoreStockLogRequest::rules
it('validates required type field'); // StoreStockLogRequest type required
it('validates required product_id field'); // StoreStockLogRequest product_id required
it('validates required warehouse_id field'); // StoreStockLogRequest warehouse_id required
it('validates required quantity field'); // StoreStockLogRequest quantity required
it('validates type is valid enum value'); // StoreStockLogRequest Rule::enum(StockLogType)
it('validates product_id exists'); // StoreStockLogRequest exists:products,id
it('validates warehouse_id exists'); // StoreStockLogRequest exists:warehouses,id
it('validates quantity is positive integer'); // StoreStockLogRequest integer|min:1
it('validates unit_cost is numeric when provided'); // StoreStockLogRequest nullable|numeric
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without stock-logs-create permission'); // StockLogPolicy::create
it('returns stock log resource on success'); // StockLogController::store StockLogResource
$response->assertValidRequest()->assertValidResponse(201);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
```

## GET /api/v1/stock-logs/export/{format}

```php
it('requires authentication'); // auth:sanctum middleware
it('requires stock-logs-view permission'); // StockLogPolicy::viewAny via Gate::authorize
it('exports stock logs to xlsx format'); // ExportStockLog::execute StockLogExportFormat::Xlsx
it('exports stock logs to pdf format'); // ExportStockLog::execute StockLogExportFormat::Pdf
it('returns validation errors for invalid input'); // ExportStockLogRequest::rules
it('validates required from date'); // ExportStockLogRequest from required
it('validates required to date'); // ExportStockLogRequest to required
it('validates from date format'); // ExportStockLogRequest from date
it('validates to date format'); // ExportStockLogRequest to date
it('validates to date is after or equal to from date'); // ExportStockLogRequest to after_or_equal:from
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without stock-logs-view permission'); // StockLogPolicy::viewAny
it('returns 404 for invalid format'); // route model binding with StockLogExportFormat enum
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

## POST /api/v1/stock-logs/transfer

```php
it('requires authentication'); // auth:sanctum middleware
it('requires warehouses-create permission'); // Gate::authorize('create', Warehouse::class)
it('transfers stock between warehouses and returns 200'); // StockLogController::transfer
it('creates stock out log for source warehouse'); // TransferStock::execute
it('creates stock in log for destination warehouse'); // TransferStock::execute
it('returns validation errors for invalid input'); // TransferStockRequest::rules
it('returns 400 for insufficient stock in source warehouse'); // TransferStock::execute InsufficientStockException
it('validates required product_id'); // TransferStockRequest product_id required
it('validates required from_warehouse_id'); // TransferStockRequest from_warehouse_id required
it('validates required to_warehouse_id'); // TransferStockRequest to_warehouse_id required
it('validates required quantity'); // TransferStockRequest quantity required
it('validates product_id exists'); // TransferStockRequest exists:products,id
it('validates from_warehouse_id exists'); // TransferStockRequest exists:warehouses,id
it('validates to_warehouse_id exists'); // TransferStockRequest exists:warehouses,id
it('validates from_warehouse_id differs from to_warehouse_id'); // TransferStockRequest different:from_warehouse_id
it('validates quantity is positive integer'); // TransferStockRequest integer|min:1
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without warehouses-create permission'); // Gate::authorize
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(400);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
```
