# Product API Tests

## Test Organization

Tests should be organized by endpoint into separate files:
- One endpoint per file (e.g., `GetProductsTest.php`, `CreateProductTest.php`)
- Use `describe()` blocks with descriptive names

## Testing Guidelines

- **Spectator Setup**: Configured globally in `tests/Pest.php` - do not add to individual test files
- **Contract Testing**: Always use `$response->assertValidRequest()->assertValidResponse(status)`
- **No Duplicate Assertions**: Do not use `assertJsonStructure()` - `assertValidResponse()` already validates against OpenAPI schema
- **File Organization**: One endpoint per file with `describe()` block using descriptive name
- **Invalid Requests**: Use `assertInvalidRequest()` when the request violates OpenAPI schema constraints
- **Authentication**: Use Bearer token in headers or `actingAs($user)` helper
- **Authorization**: Test permissions (e.g., `products-view`) and policies (e.g., `ProductPolicy::viewAny`)

## Common Testing Patterns

### Factory Usage
```php
$product = Product::factory()->create(['sku' => 'PROD-001']);
$category = Category::factory()->create();
```

### Testing Relationships
```php
// With eager loading
$product = Product::factory()->create();
$response = getJson("/api/v1/products/{$product->id}?include=category");
```

### Testing Query Filters
```php
// Laravel Query Builder package
$response = getJson('/api/v1/products?filter[category_id]=1&filter[sku]=PROD');
```

---

## GET /api/v1/products
**File**: `tests/Feature/Product/GetProductsTest.php` (suggested)

```php
describe('List Products', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires products-view permission'); // ProductPolicy::viewAny
    it('returns paginated list of products with 200'); // ProductController::index
    it('filters products by SKU'); // AllowedFilter::partial('sku')
    it('filters products by category'); // AllowedFilter::exact('category_id')
    it('filters products by name'); // AllowedFilter::partial('name')
    it('sorts products by price'); // allowedSorts('price')
    it('includes category relationship'); // allowedIncludes('category')
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without products-view permission'); // ProductPolicy::viewAny
    it('returns product resource collection'); // ProductResource::collection
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(401);
    $response->assertValidRequest()->assertValidResponse(403);
});
```

---

## POST /api/v1/products
**File**: `tests/Feature/Product/CreateProductTest.php` (suggested)

```php
describe('Create Product', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires products-create permission'); // ProductPolicy::create
    it('creates a new product and returns 201'); // ProductController::store
    it('returns validation errors for invalid input'); // StoreProductRequest::rules
    it('rejects duplicate SKU'); // StoreProductRequest unique:products
    it('validates SKU format'); // StoreProductRequest regex pattern
    it('validates price is positive'); // StoreProductRequest min:0
    it('validates reorder level is non-negative'); // StoreProductRequest min:0
    it('validates category exists'); // StoreProductRequest exists:categories
    it('validates max length for name field'); // use assertInvalidRequest()
    it('validates max length for SKU field'); // use assertInvalidRequest()
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without products-create permission'); // ProductPolicy::create
    it('returns product resource on success'); // ProductController::store ProductResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(201);
    $response->assertInvalidRequest()->assertValidResponse(422);
});
```

---

## GET /api/v1/products/{product}
**File**: `tests/Feature/Product/ShowProductTest.php` (suggested)

```php
describe('Show Product', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires products-view permission'); // ProductPolicy::view
    it('returns product details with 200'); // ProductController::show
    it('includes category relationship when requested'); // allowedIncludes('category')
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without products-view permission'); // ProductPolicy::view
    it('returns 404 for non-existent product'); // route model binding
    it('returns product resource'); // ProductController::show ProductResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## PUT/PATCH /api/v1/products/{product}
**File**: `tests/Feature/Product/UpdateProductTest.php` (suggested)

```php
describe('Update Product', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires products-update permission'); // ProductPolicy::update
    it('updates product and returns 200'); // ProductController::update
    it('returns validation errors for invalid input'); // UpdateProductRequest::rules
    it('rejects duplicate SKU'); // UpdateProductRequest unique:products,id
    it('validates price is positive'); // UpdateProductRequest min:0
    it('allows partial updates'); // UpdateProductRequest optional fields
    it('validates category exists'); // UpdateProductRequest exists:categories
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without products-update permission'); // ProductPolicy::update
    it('returns 404 for non-existent product'); // route model binding
    it('returns updated product resource'); // ProductController::update ProductResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## DELETE /api/v1/products/{product}
**File**: `tests/Feature/Product/DeleteProductTest.php` (suggested)

```php
describe('Delete Product', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires products-delete permission'); // ProductPolicy::delete
    it('soft deletes product and returns 204'); // ProductController::destroy
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without products-delete permission'); // ProductPolicy::delete
    it('returns 404 for non-existent product'); // route model binding
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(204);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## GET /api/v1/products/trashed
**File**: `tests/Feature/Product/GetTrashedProductsTest.php` (suggested)

```php
describe('List Trashed Products', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires products-view permission'); // ProductPolicy::viewAny
    it('returns list of soft-deleted products with 200'); // ProductController::trashed onlyTrashed()
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without products-view permission'); // ProductPolicy::viewAny
    it('returns product resource collection'); // ProductResource::collection
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
});
```

---

## POST /api/v1/products/{product}/restore
**File**: `tests/Feature/Product/RestoreProductTest.php` (suggested)

```php
describe('Restore Product', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires products-restore permission'); // ProductPolicy::restore
    it('restores soft-deleted product and returns 200'); // ProductController::restore
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without products-restore permission'); // ProductPolicy::restore
    it('returns 404 for non-existent trashed product'); // route model binding withTrashed()
    it('returns restored product resource'); // ProductController::restore ProductResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## Key Testing Notes

- **Low Stock Events**: Test that `ProductLowStock` and `ProductOutOfStock` events are fired when appropriate
- **Stock Tracking**: Test warehouse stock updates when product quantities change
- **Image Uploads**: If products support images, test with `UploadedFile::fake()->image('product.jpg')`
- **Decimal Precision**: Ensure price fields maintain correct decimal precision (2 places)
