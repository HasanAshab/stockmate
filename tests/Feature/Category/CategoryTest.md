# Category API Tests

## Test Organization

Tests should be organized by endpoint into separate files:
- One endpoint per file (e.g., `GetCategoriesTest.php`, `CreateCategoryTest.php`)
- Use `describe()` blocks with descriptive names (e.g., `describe('List Categories')`)

## Testing Guidelines

- **Spectator Setup**: Configured globally in `tests/Pest.php` - do not add to individual test files
- **Contract Testing**: Always use `$response->assertValidRequest()->assertValidResponse(status)`
- **No Duplicate Assertions**: Do not use `assertJsonStructure()` - `assertValidResponse()` already validates against OpenAPI schema
- **File Organization**: One endpoint per file with `describe()` block using descriptive name
- **Invalid Requests**: Use `assertInvalidRequest()` when the request violates OpenAPI schema (missing required fields, exceeds max length, invalid format)
- **Authentication**: Use `actingAs($user)` or pass Bearer token in headers
- **Authorization**: Test both permission checks and policy checks where applicable

## Common Testing Patterns

### Authentication Pattern
```php
$user = User::factory()->create();
$token = $user->createToken('test-token')->plainTextToken;

$response = getJson('/api/v1/endpoint', [
    'Authorization' => "Bearer {$token}",
]);
```

### Permission Testing Pattern
```php
$user = User::factory()->create();
$user->givePermissionTo('categories-view');
actingAs($user)->getJson('/api/v1/categories');
```

### Policy Testing Pattern
```php
// Test policy authorization
$user = User::factory()->create();
$user->givePermissionTo('categories-view'); // CategoryPolicy::viewAny
```

---

## GET /api/v1/categories
**File**: `tests/Feature/Category/GetCategoriesTest.php` (suggested)

```php
it('requires authentication'); // auth:sanctum middleware
it('requires categories-view permission'); // CategoryPolicy::viewAny
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without categories-view permission'); // CategoryPolicy::viewAny
it('returns paginated list of categories with 200'); // CategoryController::index
it('returns cached categories within one hour'); // Cache::remember('categories:all')
it('returns category resource collection'); // CategoryResource::collection
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
```

---

## POST /api/v1/categories
**File**: `tests/Feature/Category/CreateCategoryTest.php` (suggested)

```php
describe('Create Category', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires categories-create permission'); // CategoryPolicy::create
    it('creates a new category and returns 201'); // CategoryController::store
    it('returns validation errors for invalid input'); // StoreCategoryRequest::rules
    it('rejects duplicate slug'); // StoreCategoryRequest unique:categories
    it('validates max length for name field'); // StoreCategoryRequest max:70 - use assertInvalidRequest()
    it('validates max length for slug field'); // StoreCategoryRequest max:70 - use assertInvalidRequest()
    it('validates max length for description field'); // StoreCategoryRequest max:255 - use assertInvalidRequest()
    it('allows nullable description'); // StoreCategoryRequest nullable
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without categories-create permission'); // CategoryPolicy::create
    it('returns category resource on success'); // CategoryController::store CategoryResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(201);
    $response->assertInvalidRequest()->assertValidResponse(422); // for OpenAPI violations
});
```

---

## GET /api/v1/categories/{category}
**File**: `tests/Feature/Category/ShowCategoryTest.php` (suggested)

```php
it('requires authentication'); // auth:sanctum middleware
it('requires categories-view permission'); // CategoryPolicy::view
it('returns category details with 200'); // CategoryController::show
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without categories-view permission'); // CategoryPolicy::view
it('returns 404 for non-existent category'); // route model binding
it('returns category resource'); // CategoryController::show CategoryResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

---

## PUT/PATCH /api/v1/categories/{category}
**File**: `tests/Feature/Category/UpdateCategoryTest.php` (suggested)

```php
it('requires authentication'); // auth:sanctum middleware
it('requires categories-update permission'); // CategoryPolicy::update
it('updates category and returns 200'); // CategoryController::update
it('returns validation errors for invalid input'); // UpdateCategoryRequest::rules
it('rejects duplicate slug'); // UpdateCategoryRequest unique:categories
it('validates max length for name field'); // UpdateCategoryRequest max:70
it('validates max length for slug field'); // UpdateCategoryRequest max:70  
it('validates max length for description field'); // UpdateCategoryRequest max:255
it('allows partial updates'); // UpdateCategoryRequest optional fields
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without categories-update permission'); // CategoryPolicy::update
it('returns 404 for non-existent category'); // route model binding
it('returns updated category resource'); // CategoryController::update CategoryResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

---

## DELETE /api/v1/categories/{category}
**File**: `tests/Feature/Category/DeleteCategoryTest.php` (suggested)

```php
it('requires authentication'); // auth:sanctum middleware
it('requires categories-delete permission'); // CategoryPolicy::delete
it('soft deletes category and returns 204'); // CategoryController::destroy
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without categories-delete permission'); // CategoryPolicy::delete
it('returns 404 for non-existent category'); // route model binding
$response->assertValidRequest()->assertValidResponse(204);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

---

## GET /api/v1/categories/trashed
**File**: `tests/Feature/Category/GetTrashedCategoriesTest.php` (suggested)

```php
it('requires authentication'); // auth:sanctum middleware
it('requires categories-view permission'); // CategoryPolicy::viewAny
it('returns list of soft-deleted categories with 200'); // CategoryController::trashed onlyTrashed()
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without categories-view permission'); // CategoryPolicy::viewAny
it('returns category resource collection'); // CategoryResource::collection
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
```

---

## POST /api/v1/categories/{category}/restore
**File**: `tests/Feature/Category/RestoreCategoryTest.php` (suggested)

```php
it('requires authentication'); // auth:sanctum middleware
it('requires categories-restore permission'); // CategoryPolicy::restore
it('restores soft-deleted category and returns 200'); // CategoryController::restore
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without categories-restore permission'); // CategoryPolicy::restore
it('returns 404 for non-existent trashed category'); // route model binding withTrashed()
it('returns restored category resource'); // CategoryController::restore CategoryResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```
