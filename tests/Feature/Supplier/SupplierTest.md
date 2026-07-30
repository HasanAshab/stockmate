# Supplier API Tests

## Test Organization

Tests should be organized by endpoint into separate files:
- One endpoint per file (e.g., `GetSuppliersTest.php`, `CreateSupplierTest.php`)
- Use `describe()` blocks with descriptive names

## Testing Guidelines

- **Spectator Setup**: Configured globally in `tests/Pest.php` - do not add to individual test files
- **Contract Testing**: Always use `$response->assertValidRequest()->assertValidResponse(status)`
- **No Duplicate Assertions**: Do not use `assertJsonStructure()` - `assertValidResponse()` already validates against OpenAPI schema
- **File Organization**: One endpoint per file with `describe()` block using descriptive name
- **Invalid Requests**: Use `assertInvalidRequest()` when request violates OpenAPI schema
- **Soft Deletes**: Suppliers use soft deletes, test trashed/restore functionality

## Common Testing Patterns

### Creating Suppliers
```php
$response = postJson('/api/v1/suppliers', [
    'name' => 'ABC Suppliers Ltd',
    'email' => 'contact@abc.com',
    'phone' => '+8801712345678',
    'address' => '123 Main St, Dhaka',
]);
```

### Testing Uniqueness
```php
Supplier::factory()->create(['email' => 'existing@supplier.com']);

$response = postJson('/api/v1/suppliers', [
    'email' => 'existing@supplier.com', // Should fail
]);

$response->assertValidRequest()->assertValidResponse(422);
```

---

## GET /api/v1/suppliers
**File**: `tests/Feature/Supplier/GetSuppliersTest.php` (suggested)

```php
describe('List Suppliers', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires suppliers-view permission'); // SupplierPolicy::viewAny
    it('returns paginated list of suppliers with 200'); // SupplierController::index
    it('filters suppliers by name'); // AllowedFilter::partial('name')
    it('filters suppliers by email'); // AllowedFilter::partial('email')
    it('filters suppliers by phone'); // AllowedFilter::partial('phone')
    it('sorts suppliers by created_at'); // allowedSorts('created_at')
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // SupplierPolicy::viewAny
    it('returns supplier resource collection'); // SupplierResource::collection
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
});
```

---

## POST /api/v1/suppliers
**File**: `tests/Feature/Supplier/CreateSupplierTest.php` (suggested)

```php
describe('Create Supplier', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires suppliers-create permission'); // SupplierPolicy::create
    it('creates a new supplier and returns 201'); // SupplierController::store
    it('validates required name'); // StoreSupplierRequest::rules
    it('rejects duplicate email'); // StoreSupplierRequest unique:suppliers
    it('rejects duplicate phone'); // StoreSupplierRequest unique:suppliers
    it('validates email format'); // StoreSupplierRequest email
    it('validates phone format for BD'); // StoreSupplierRequest phone:BD
    it('validates max length for name'); // max:100 - use assertInvalidRequest()
    it('validates max length for address'); // max:255 - use assertInvalidRequest()
    it('allows nullable email'); // email nullable
    it('allows nullable phone'); // phone nullable
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // SupplierPolicy::create
    it('returns supplier resource on success'); // SupplierResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(201);
    $response->assertInvalidRequest()->assertValidResponse(422);
});
```

---

## GET /api/v1/suppliers/{supplier}
**File**: `tests/Feature/Supplier/ShowSupplierTest.php` (suggested)

```php
describe('Show Supplier', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires suppliers-view permission'); // SupplierPolicy::view
    it('returns supplier details with 200'); // SupplierController::show
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // SupplierPolicy::view
    it('returns 404 for non-existent supplier'); // route model binding
    it('returns supplier resource'); // SupplierResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## PUT/PATCH /api/v1/suppliers/{supplier}
**File**: `tests/Feature/Supplier/UpdateSupplierTest.php` (suggested)

```php
describe('Update Supplier', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires suppliers-update permission'); // SupplierPolicy::update
    it('updates supplier and returns 200'); // SupplierController::update
    it('rejects duplicate email'); // UpdateSupplierRequest unique:suppliers,id
    it('rejects duplicate phone'); // UpdateSupplierRequest unique:suppliers,id
    it('allows partial updates'); // optional fields
    it('validates email format'); // UpdateSupplierRequest email
    it('validates phone format'); // UpdateSupplierRequest phone:BD
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // SupplierPolicy::update
    it('returns 404 for non-existent supplier'); // route model binding
    it('returns updated supplier resource'); // SupplierResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## DELETE /api/v1/suppliers/{supplier}
**File**: `tests/Feature/Supplier/DeleteSupplierTest.php` (suggested)

```php
describe('Delete Supplier', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires suppliers-delete permission'); // SupplierPolicy::delete
    it('soft deletes supplier and returns 204'); // SupplierController::destroy
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // SupplierPolicy::delete
    it('returns 404 for non-existent supplier'); // route model binding
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(204);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## GET /api/v1/suppliers/trashed
**File**: `tests/Feature/Supplier/GetTrashedSuppliersTest.php` (suggested)

```php
describe('List Trashed Suppliers', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires suppliers-view permission'); // SupplierPolicy::viewAny
    it('returns list of soft-deleted suppliers with 200'); // SupplierController::trashed
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // SupplierPolicy::viewAny
    it('returns supplier resource collection'); // SupplierResource::collection
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
});
```

---

## POST /api/v1/suppliers/{supplier}/restore
**File**: `tests/Feature/Supplier/RestoreSupplierTest.php` (suggested)

```php
describe('Restore Supplier', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires suppliers-restore permission'); // SupplierPolicy::restore
    it('restores soft-deleted supplier and returns 200'); // SupplierController::restore
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // SupplierPolicy::restore
    it('returns 404 for non-existent trashed supplier'); // withTrashed()
    it('returns restored supplier resource'); // SupplierResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## Key Testing Notes

- **Contact Information**: Email and phone are both optional but should be unique if provided
- **Soft Deletes**: Suppliers are soft deleted, not permanently removed
- **Purchase Orders**: Consider testing cascade behavior with related purchase orders
- **Activity Logging**: Supplier CRUD actions should be logged
- **Search**: Suppliers should be searchable by name, email, and phone
