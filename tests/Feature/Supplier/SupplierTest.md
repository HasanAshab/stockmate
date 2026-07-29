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

---

## GET /api/v1/suppliers

```php
it('requires authentication'); // auth:sanctum middleware
it('requires suppliers-view permission'); // SupplierPolicy::viewAny
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without suppliers-view permission'); // SupplierPolicy::viewAny
it('returns list of suppliers with 200'); // SupplierController::index
it('returns cached suppliers within one hour'); // Cache::remember('suppliers:all')
it('returns supplier resource collection'); // SupplierResource::collection
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
```

## POST /api/v1/suppliers

```php
it('requires authentication'); // auth:sanctum middleware
it('requires suppliers-create permission'); // SupplierPolicy::create
it('creates a new supplier and returns 201'); // SupplierController::store
it('returns validation errors for invalid input'); // StoreSupplierRequest::rules
it('validates required name field'); // StoreSupplierRequest name required
it('validates email format when provided'); // StoreSupplierRequest email|nullable
it('validates phone format when provided'); // StoreSupplierRequest phone:BD|nullable
it('validates max length for name field'); // StoreSupplierRequest max:70
it('validates max length for address field'); // StoreSupplierRequest max:255
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without suppliers-create permission'); // SupplierPolicy::create
it('returns supplier resource on success'); // SupplierController::store SupplierResource
$response->assertValidRequest()->assertValidResponse(201);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
```

## GET /api/v1/suppliers/{supplier}

```php
it('requires authentication'); // auth:sanctum middleware
it('requires suppliers-view permission'); // SupplierPolicy::view
it('returns supplier details with 200'); // SupplierController::show
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without suppliers-view permission'); // SupplierPolicy::view
it('returns 404 for non-existent supplier'); // route model binding
it('returns supplier resource'); // SupplierController::show SupplierResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

## PUT/PATCH /api/v1/suppliers/{supplier}

```php
it('requires authentication'); // auth:sanctum middleware
it('requires suppliers-update permission'); // SupplierPolicy::update
it('updates supplier and returns 200'); // SupplierController::update
it('returns validation errors for invalid input'); // UpdateSupplierRequest::rules
it('validates email format when provided'); // UpdateSupplierRequest email|nullable
it('validates phone format when provided'); // UpdateSupplierRequest phone:BD|nullable
it('validates max length for name field'); // UpdateSupplierRequest max:70
it('validates max length for address field'); // UpdateSupplierRequest max:255
it('allows partial updates'); // UpdateSupplierRequest optional fields
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without suppliers-update permission'); // SupplierPolicy::update
it('returns 404 for non-existent supplier'); // route model binding
it('returns updated supplier resource'); // SupplierController::update SupplierResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

## DELETE /api/v1/suppliers/{supplier}

```php
it('requires authentication'); // auth:sanctum middleware
it('requires suppliers-delete permission'); // SupplierPolicy::delete
it('soft deletes supplier and returns 204'); // SupplierController::destroy
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without suppliers-delete permission'); // SupplierPolicy::delete
it('returns 404 for non-existent supplier'); // route model binding
$response->assertValidRequest()->assertValidResponse(204);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

## GET /api/v1/suppliers/trashed

```php
it('requires authentication'); // auth:sanctum middleware
it('requires suppliers-view permission'); // SupplierPolicy::viewAny
it('returns list of soft-deleted suppliers with 200'); // SupplierController::trashed onlyTrashed()
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without suppliers-view permission'); // SupplierPolicy::viewAny
it('returns supplier resource collection'); // SupplierResource::collection
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
```

## POST /api/v1/suppliers/{supplier}/restore

```php
it('requires authentication'); // auth:sanctum middleware
it('requires suppliers-restore permission'); // SupplierPolicy::restore
it('restores soft-deleted supplier and returns 200'); // SupplierController::restore
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without suppliers-restore permission'); // SupplierPolicy::restore
it('returns 404 for non-existent trashed supplier'); // route model binding withTrashed()
it('returns restored supplier resource'); // SupplierController::restore SupplierResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```
