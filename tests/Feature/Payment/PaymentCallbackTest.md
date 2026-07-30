# Payment Callback API Tests

## Test Organization

Tests should be organized by endpoint into separate files:
- One endpoint per file (e.g., `PaymentSuccessCallbackTest.php`, `PaymentFailCallbackTest.php`)
- Use `describe()` blocks with descriptive names

## Testing Guidelines

- **Spectator Setup**: Configured globally in `tests/Pest.php` - do not add to individual test files
- **Contract Testing**: Always use `$response->assertValidRequest()->assertValidResponse(status)`
- **No Duplicate Assertions**: Do not use `assertJsonStructure()` - `assertValidResponse()` already validates against OpenAPI schema
- **File Organization**: One endpoint per file with `describe()` block using descriptive name
- **Public Endpoints**: Payment callbacks are public (no authentication) - verified by hash
- **SSLCommerz Integration**: Test hash verification and payment validation
- **Idempotency**: Test that duplicate callbacks don't cause issues

## Common Testing Patterns

### Mocking SSLCommerz Facade
```php
use Facades\Nahid\Sslcommerz\Sslcommerz;

Sslcommerz::shouldReceive('verifyHash')
    ->with($payload)
    ->andReturn(true);

Sslcommerz::shouldReceive('validatePayment')
    ->with($payload, $trxId, $amount)
    ->andReturn(true);
```

### Testing Payment Success Flow
```php
$salesOrder = SalesOrder::factory()->create([
    'status' => SalesOrderStatus::Pending,
    'payment_transaction_id' => 'TXN123',
    'total_amount' => 1000,
]);

$payload = [
    'tran_id' => 'TXN123',
    'amount' => 1000,
    'status' => 'VALID',
    'verify_sign' => 'valid_hash',
    // ... other SSLCommerz fields
];

$response = postJson('/api/v1/payment/success', $payload);
expect($salesOrder->fresh()->status)->toBe(SalesOrderStatus::Paid);
```

---

## POST /api/v1/payment/success
**File**: `tests/Feature/Payment/PaymentSuccessCallbackTest.php` (suggested)

```php
describe('Payment Success Callback', function () {
    it('does not require authentication'); // Unauthenticated attribute
    it('processes successful payment callback and returns 200'); // PaymentCallbackController::success
    it('validates payment hash'); // Sslcommerz::verifyHash($payload)
    it('validates payment data'); // Sslcommerz::validatePayment($payload, $trxId, $amount)
    it('finalizes sales order payment'); // FinalizeSalesOrderPayment::execute
    it('updates order status to paid'); // SalesOrderStatus::Paid
    it('removes stock reservation'); // stock deduction
    it('returns 422 for invalid hash'); // ensureHashIsValid ValidationException
    it('returns 422 for invalid payment data'); // ensurePaymentIsValid ValidationException
    it('returns 422 for mismatched amount'); // payment amount != order amount
    it('returns 422 for mismatched transaction ID'); // payment trx_id != order trx_id
    it('handles duplicate callbacks idempotently'); // already paid order
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(422);
});
```

---

## POST /api/v1/payment/fail
**File**: `tests/Feature/Payment/PaymentFailCallbackTest.php` (suggested)

```php
describe('Payment Fail Callback', function () {
    it('does not require authentication'); // Unauthenticated attribute
    it('processes failed payment callback and returns 200'); // PaymentCallbackController::fail
    it('updates sales order status to failed'); // ResolveSalesOrderPaymentState::execute SalesOrderStatus::Failed
    it('restores reserved stock'); // stock restoration
    it('logs failure reason'); // payment failure details
    it('returns 200 to acknowledge callback receipt'); // SSLCommerz expects 200
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
});
```

---

## POST /api/v1/payment/cancel
**File**: `tests/Feature/Payment/PaymentCancelCallbackTest.php` (suggested)

```php
describe('Payment Cancel Callback', function () {
    it('does not require authentication'); // Unauthenticated attribute
    it('processes cancelled payment callback and returns 200'); // PaymentCallbackController::cancel
    it('updates sales order status to cancelled'); // ResolveSalesOrderPaymentState::execute SalesOrderStatus::Cancelled
    it('restores reserved stock'); // stock restoration
    it('logs cancellation reason'); // payment cancellation details
    it('returns 200 to acknowledge callback receipt'); // SSLCommerz expects 200
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
});
```

---

## POST /api/v1/payment/ipn
**File**: `tests/Feature/Payment/PaymentIpnCallbackTest.php` (suggested)

```php
describe('Payment IPN Callback', function () {
    it('does not require authentication'); // Unauthenticated attribute
    it('processes IPN callback and returns 200'); // PaymentCallbackController::ipn
    it('validates payment hash'); // Sslcommerz::verifyHash($payload)
    it('finalizes payment when status is valid'); // FinalizeSalesOrderPayment::execute if SslcommerzPaymentStatus::isValid
    it('ignores invalid payment status'); // early return for invalid statuses
    it('returns 422 for invalid hash'); // ensureHashIsValid ValidationException
    it('handles IPN after success callback'); // idempotency check
    it('returns 200 to acknowledge callback receipt'); // SSLCommerz expects 200
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(422);
});
```

---

## Key Testing Notes

- **No Authentication**: Callbacks are public endpoints, verified by SSLCommerz hash
- **Hash Verification**: Always verify `verify_sign` hash before processing
- **Payment Validation**: Validate transaction ID, amount, and status match expected values
- **Idempotency**: Handle duplicate callbacks gracefully (payment already processed)
- **Stock Management**: Success = deduct stock, Fail/Cancel = restore stock
- **Status Transitions**: Pending → Paid (success) or Pending → Failed/Cancelled
- **IPN (Instant Payment Notification)**: Additional callback for verification, may arrive after success/fail
- **SSLCommerz Mocking**: Mock Sslcommerz facade in tests, don't make real API calls
- **Response Codes**: Always return 200 to acknowledge receipt (even on validation errors, log them instead)
- **Transaction Logging**: Log all callbacks for audit trail
- **Error Handling**: Catch and log errors, but still return 200 to SSLCommerz
