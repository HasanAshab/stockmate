# Stock Log API Tests

## Test Organization

Tests should be organized by endpoint into separate files:
- One endpoint per file (e.g., `GetStockLogsTest.php`, `CreateStockLogTest.php`)
- Use `describe()` blocks with descriptive names

## Testing Guidelines

- **Spectator Setup**: Configured globally in `tests/Pest.php` - do not add to individual test files
- **Contract Testing**: Always use `$response->assertValidRequest()->assertValidResponse(status)`
- **No Duplicate Assertions**: Do not use `assertJsonStructure()` - `assertValidResponse()` already validates against OpenAPI schema
- **File Organization**: One endpoint per file with `describe()` block using descriptive name
- **Invalid Requests**: Use `assertInvalidRequest()` when request violates OpenAPI schema
- **Read-Only Creation**: Stock logs are created automatically by the system, manual creation via API should be tested
- **Export Functionality**: Test CSV/Excel export with proper formatting

## Common Testing Patterns

### Creating Stock Logs
```php
$warehouse = Warehouse::factory()->create();
$product = Product::factory()->create();

$response = postJson('/api/v1/stock-logs', [
    'warehouse_id' => $warehouse->id,
    'product_id' => $product->id,
    'type' => 'adjustment',
    'quantity' => 10,
    'notes' => 'Manual adjustment',
]);
```

### Testing Export
```php
StockLog::factory()->count(5)->create();

$response = getJson('/api/v1/stock-logs/export?format=csv');
$response->assertHeader('Content-Type', 'text/csv');
$response->assertHeader('Content-Disposition', 'attachment; filename=stock-logs.csv');
```

---

## GET /api/v1/stock-logs
**File**: `tests/Feature/StockLog/GetStockLogsTest.php` (suggested)

```php
describe('List Stock Logs', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires stock-logs-view permission'); // StockLogPolicy::viewAny
    it('returns paginated list of stock logs with 200'); // StockLogController::index
    it('includes warehouse, product, creator relationships'); // eager loading
    it('filters by warehouse ID'); // AllowedFilter::belongsTo('warehouse')
    it('filters by product ID'); // AllowedFilter::belongsTo('product')
    it('filters by type'); // AllowedFilter::exact('type')
    it('filters by created_at date range'); // AllowedFilter::custom
    it('sorts by created_at descending'); // defaultSort('-created_at')
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // StockLogPolicy::viewAny
    it('returns stock log resource collection'); // StockLogResource::collection
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
});
```

---

## POST /api/v1/stock-logs
**File**: `tests/Feature/StockLog/CreateStockLogTest.php` (suggested)

```php
describe('Create Stock Log', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires stock-logs-create permission'); // StockLogPolicy::create
    it('creates a new stock log and returns 201'); // CreateStockLog::execute
    it('updates warehouse stock quantity'); // WarehouseStock update
    it('validates required warehouse_id'); // StoreStockLogRequest::rules
    it('validates required product_id'); // StoreStockLogRequest::rules
    it('validates required type'); // StoreStockLogRequest::rules Rule::enum(StockLogType)
    it('validates required quantity'); // StoreStockLogRequest::rules
    it('validates quantity is positive for additions'); // quantity > 0 for purchase/adjustment
    it('validates quantity is negative for removals'); // quantity < 0 for sale/transfer/adjustment
    it('validates warehouse exists'); // exists:warehouses
    it('validates product exists'); // exists:products
    it('validates type is valid enum'); // Rule::enum(StockLogType)
    it('validates sufficient stock for removals'); // business rule check
    it('allows nullable notes'); // notes nullable
    it('assigns creator as authenticated user'); // creator_id = auth()->id()
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // StockLogPolicy::create
    it('returns stock log resource on success'); // StockLogResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(201);
    $response->assertInvalidRequest()->assertValidResponse(422);
});
```

---

## GET /api/v1/stock-logs/{stockLog}
**File**: `tests/Feature/StockLog/ShowStockLogTest.php` (suggested)

```php
describe('Show Stock Log', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires stock-logs-view permission'); // StockLogPolicy::view
    it('returns stock log details with 200'); // StockLogController::show
    it('includes warehouse, product, creator relationships'); // eager loading
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // StockLogPolicy::view
    it('returns 404 for non-existent stock log'); // route model binding
    it('returns stock log resource'); // StockLogResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## POST /api/v1/stock-logs/transfer
**File**: `tests/Feature/StockLog/TransferStockTest.php` (suggested)

```php
describe('Transfer Stock', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires stock-logs-transfer permission'); // Gate::authorize('stock-logs-transfer')
    it('transfers stock between warehouses and returns 201'); // TransferStock::execute
    it('creates removal log for source warehouse'); // type=transfer, quantity negative
    it('creates addition log for destination warehouse'); // type=transfer, quantity positive
    it('validates required from_warehouse_id'); // TransferStockRequest::rules
    it('validates required to_warehouse_id'); // TransferStockRequest::rules
    it('validates required product_id'); // TransferStockRequest::rules
    it('validates required quantity'); // TransferStockRequest::rules
    it('validates quantity is positive'); // quantity > 0
    it('validates warehouses are different'); // different:from_warehouse_id
    it('validates sufficient stock in source'); // business rule
    it('returns 400 for insufficient stock'); // InsufficientStockException
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // Gate::authorize
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(201);
    $response->assertValidRequest()->assertValidResponse(400);
});
```

---

## GET /api/v1/stock-logs/export
**File**: `tests/Feature/StockLog/ExportStockLogTest.php` (suggested)

```php
describe('Export Stock Logs', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires stock-logs-view permission'); // StockLogPolicy::viewAny via Gate::authorize
    it('exports stock logs as CSV'); // ExportStockLog::execute format=csv
    it('exports stock logs as Excel'); // ExportStockLog::execute format=xlsx
    it('includes all required columns'); // warehouse, product, type, quantity, creator, created_at
    it('applies filters before export'); // filters applied to query
    it('returns proper content type for CSV'); // text/csv
    it('returns proper content type for Excel'); // application/vnd.openxmlformats
    it('returns proper filename'); // Content-Disposition header
    it('validates format parameter'); // Rule::enum(StockLogExportFormat)
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // Gate::authorize
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
});
```

---

## Key Testing Notes

- **Log Types**: Purchase, Sale, Adjustment, Transfer (use StockLogType enum)
- **Quantity Sign**: Positive for additions (purchase, adjustment+), negative for removals (sale, adjustment-, transfer out)
- **Atomic Operations**: Stock transfers should be atomic (both logs created or none)
- **Immutable**: Stock logs should not be editable or deletable (audit trail)
- **Creator Tracking**: Always track who created the log
- **Export Performance**: Test export with large datasets, ensure proper pagination/chunking
- **Transfer Validation**: Source and destination warehouses must be different
- **Stock Synchronization**: Log quantity changes must match warehouse_stock changes
