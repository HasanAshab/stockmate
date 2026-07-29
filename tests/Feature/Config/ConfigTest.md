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

---

## GET /api/v1/config/enums

```php
it('does not require authentication'); // Unauthenticated attribute
it('returns all enum values with 200'); // ConfigController::enums
it('returns purchase_order_statuses array'); // PurchaseOrderStatus::allCasesArray()
it('returns sales_order_statuses array'); // SalesOrderStatus::allCasesArray()
it('returns stock_log_types array'); // StockLogType::allCasesArray()
it('returns enum values with value and name properties'); // ['value' => string, 'name' => string]
$response->assertValidRequest()->assertValidResponse(200);
```
