# Configuration API Tests

## Test Organization

Tests should be organized by endpoint into separate files:
- One endpoint per file (e.g., `GetConfigEnumsTest.php`)
- Use `describe()` blocks with descriptive names

## Testing Guidelines

- **Spectator Setup**: Configured globally in `tests/Pest.php` - do not add to individual test files
- **Contract Testing**: Always use `$response->assertValidRequest()->assertValidResponse(status)`
- **No Duplicate Assertions**: Do not use `assertJsonStructure()` - `assertValidResponse()` already validates against OpenAPI schema
- **File Organization**: One endpoint per file with `describe()` block using descriptive name
- **Public Endpoints**: Config endpoints may not require authentication (check Unauthenticated attribute)
- **Enum Values**: Ensure all enum values and names are returned correctly

## Common Testing Patterns

### Testing Enum Structure
```php
$response = getJson('/api/v1/config/enums');
$enums = $response->json();

expect($enums['purchase_order_statuses'])->toBeArray();
expect($enums['purchase_order_statuses'][0])->toHaveKeys(['value', 'name']);
expect($enums['sales_order_statuses'])->toHaveCount(count(SalesOrderStatus::cases()));
```

---

## GET /api/v1/config/enums
**File**: `tests/Feature/Config/GetConfigEnumsTest.php` (suggested)

```php
describe('Get Configuration Enums', function () {
    it('does not require authentication'); // Unauthenticated attribute
    it('returns all enum values with 200'); // ConfigController::enums
    it('returns purchase_order_statuses array'); // PurchaseOrderStatus::allCasesArray()
    it('returns sales_order_statuses array'); // SalesOrderStatus::allCasesArray()
    it('returns stock_log_types array'); // StockLogType::allCasesArray()
    it('returns social_providers array'); // SocialProvider::allCasesArray()
    it('returns enum values with value property'); // ['value' => string]
    it('returns enum values with name property'); // ['name' => string]
    it('returns all available enum cases'); // allCasesArray() returns all cases
    it('formats enum names correctly'); // converts CamelCase to Title Case
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
});
```

---

## Key Testing Notes

- **Public Access**: This endpoint is typically public (no authentication required)
- **Frontend Usage**: Frontend applications use this to populate dropdowns and filters
- **Enum Synchronization**: Ensure backend enums match what frontend expects
- **Naming Convention**: Enum names should be human-readable (e.g., "Pending" not "pending")
- **Complete Coverage**: All enums used in the application should be returned
- **Caching**: Consider caching enum responses as they rarely change
- **Versioning**: If adding new enum values, ensure backward compatibility
