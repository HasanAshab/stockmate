# Dashboard API Tests

## Test Organization

Tests should be organized by endpoint into separate files:
- One endpoint per file (e.g., `GetDashboardMetricsTest.php`)
- Use `describe()` blocks with descriptive names

## Testing Guidelines

- **Spectator Setup**: Configured globally in `tests/Pest.php` - do not add to individual test files
- **Contract Testing**: Always use `$response->assertValidRequest()->assertValidResponse(status)`
- **No Duplicate Assertions**: Do not use `assertJsonStructure()` - `assertValidResponse()` already validates against OpenAPI schema
- **File Organization**: One endpoint per file with `describe()` block using descriptive name
- **Metrics Testing**: Verify calculation accuracy for counts, sums, and aggregates
- **Performance**: Dashboard queries should be optimized (use database counts, not collection counts)

## Common Testing Patterns

### Testing Metrics Calculation
```php
// Setup test data
Product::factory()->count(10)->create(['reorder_level' => 50, 'quantity' => 25]); // low stock
Product::factory()->count(5)->create(['quantity' => 0]); // out of stock
SalesOrder::factory()->count(3)->create(['status' => SalesOrderStatus::Pending]);

// Test metric accuracy
$response = actingAs($user)->getJson('/api/v1/dashboard');
expect($response->json('low_stock_count'))->toBe(10);
expect($response->json('out_of_stock_count'))->toBe(5);
expect($response->json('pending_orders_count'))->toBe(3);
```

---

## GET /api/v1/dashboard
**File**: `tests/Feature/Dashboard/GetDashboardMetricsTest.php` (suggested)

```php
describe('Dashboard Metrics', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('returns dashboard metrics with 200'); // DashboardController::index GetDashboardMetrics::execute
    it('returns total products count'); // Product::count()
    it('returns low stock products count'); // Product::where('quantity', '<=', 'reorder_level')->count()
    it('returns out of stock products count'); // Product::where('quantity', 0)->count()
    it('returns total categories count'); // Category::count()
    it('returns total suppliers count'); // Supplier::count()
    it('returns total warehouses count'); // Warehouse::count()
    it('returns pending purchase orders count'); // PurchaseOrder::where('status', 'pending')->count()
    it('returns pending sales orders count'); // SalesOrder::where('status', 'pending')->count()
    it('returns today sales total'); // SalesOrder::whereDate('created_at', today())->sum('total_amount')
    it('returns this month sales total'); // SalesOrder::whereMonth('created_at', now()->month)->sum('total_amount')
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('calculates metrics accurately'); // GetDashboardMetrics business logic
    it('uses efficient queries'); // N+1 prevention, proper aggregates
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
});
```

---

## Key Testing Notes

- **Metric Accuracy**: Always verify calculations match expected values
- **Date Filtering**: Test metrics filtered by date ranges (today, this month, this year)
- **Performance**: Metrics should use database aggregates, not loading all records
- **Authorization**: Dashboard may require specific permissions or roles
- **Real-time Data**: Metrics reflect current state of database
- **Edge Cases**: Test with zero values, empty database, large numbers
