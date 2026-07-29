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

---

## POST /api/v1/payment/success

```php
it('does not require authentication'); // Unauthenticated attribute
it('processes successful payment callback and returns 200'); // PaymentCallbackController::success
it('validates payment hash'); // Sslcommerz::verifyHash($payload)
it('validates payment data'); // Sslcommerz::validatePayment($payload, $trxId, $amount)
it('finalizes sales order payment'); // FinalizeSalesOrderPayment::execute
it('returns 422 for invalid hash'); // ensureHashIsValid throws ValidationException
it('returns 422 for invalid payment data'); // ensurePaymentIsValid throws ValidationException
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(422);
```

## POST /api/v1/payment/fail

```php
it('does not require authentication'); // Unauthenticated attribute
it('processes failed payment callback and returns 200'); // PaymentCallbackController::fail
it('updates sales order status to failed'); // ResolveSalesOrderPaymentState::execute SalesOrderStatus::Failed
it('returns 200 to acknowledge callback receipt'); // return ['message' => 'Payment failed.']
$response->assertValidRequest()->assertValidResponse(200);
```

## POST /api/v1/payment/cancel

```php
it('does not require authentication'); // Unauthenticated attribute
it('processes cancelled payment callback and returns 200'); // PaymentCallbackController::cancel
it('updates sales order status to cancelled'); // ResolveSalesOrderPaymentState::execute SalesOrderStatus::Cancelled
it('returns 200 to acknowledge callback receipt'); // return ['message' => 'Payment cancelled.']
$response->assertValidRequest()->assertValidResponse(200);
```

## POST /api/v1/payment/ipn

```php
it('does not require authentication'); // Unauthenticated attribute
it('processes IPN callback and returns 200'); // PaymentCallbackController::ipn
it('validates payment hash'); // Sslcommerz::verifyHash($payload)
it('finalizes payment when status is valid'); // FinalizeSalesOrderPayment::execute if SslcommerzPaymentStatus::isValid
it('ignores invalid payment status'); // if check before finalize
it('returns 422 for invalid hash'); // ensureHashIsValid throws ValidationException
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(422);
```
