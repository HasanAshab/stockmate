# Configuration API Tests

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
