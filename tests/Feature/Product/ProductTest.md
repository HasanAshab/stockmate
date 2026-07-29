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

---

## GET /api/v1/products

```php
it('requires authentication'); // auth:sanctum middleware
it('requires products-view permission'); // ProductPolicy::viewAny
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without products-view permission'); // ProductPolicy::viewAny
it('returns paginated list of products with 200'); // ProductController::index cursorPaginate(10)
it('filters products by category ID'); // AllowedFilter::belongsTo('category')
it('filters products by supplier ID'); // AllowedFilter::belongsTo('supplier')
it('filters products by price greater than value'); // AllowedFilter::operator('price', FilterOperator::DYNAMIC)
it('filters products by price less than or equal to value'); // AllowedFilter::operator('price', FilterOperator::DYNAMIC)
it('filters products by price exact match'); // AllowedFilter::operator('price', FilterOperator::DYNAMIC)
it('filters trashed products with trashed=with'); // AllowedFilter::trashed()
it('filters trashed products with trashed=only'); // AllowedFilter::trashed()
it('sorts products by price ascending'); // allowedSorts('price')
it('sorts products by created_at descending'); // allowedSorts('created_at')
it('includes category relationship'); // allowedIncludes('category')
it('includes supplier relationship'); // allowedIncludes('supplier')
it('includes media relationship'); // allowedIncludes('media')
it('returns product resource collection'); // ProductResource::collection
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
```

## POST /api/v1/products

```php
it('requires authentication'); // auth:sanctum middleware
it('requires products-create permission'); // ProductPolicy::create
it('creates a new product and returns 201'); // ProductController::store
it('creates a product with image upload'); // $product->addMediaFromRequest('image')
it('returns validation errors for invalid input'); // StoreProductRequest::rules
it('rejects duplicate SKU'); // StoreProductRequest unique:products,sku
it('validates required fields'); // StoreProductRequest name, sku, price, category_id required
it('validates price is numeric'); // StoreProductRequest numeric
it('validates category_id exists'); // StoreProductRequest exists:categories,id
it('validates supplier_id exists when provided'); // StoreProductRequest nullable|exists:suppliers,id
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without products-create permission'); // ProductPolicy::create
it('returns product resource on success'); // ProductController::store ProductResource
$response->assertValidRequest()->assertValidResponse(201);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
```

## GET /api/v1/products/{product}

```php
it('requires authentication'); // auth:sanctum middleware
it('requires products-view permission'); // ProductPolicy::view
it('returns product details with 200'); // ProductController::show
it('loads category, supplier, and media relationships'); // $product->load(['category', 'supplier', 'media'])
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without products-view permission'); // ProductPolicy::view
it('returns 404 for non-existent product'); // route model binding
it('returns product resource'); // ProductController::show ProductResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

## PUT/PATCH /api/v1/products/{product}

```php
it('requires authentication'); // auth:sanctum middleware
it('requires products-update permission'); // ProductPolicy::update
it('updates product and returns 200'); // ProductController::update
it('replaces image when new image is uploaded'); // $product->clearMediaCollection + addMediaFromRequest
it('returns validation errors for invalid input'); // UpdateProductRequest::rules
it('rejects duplicate SKU'); // UpdateProductRequest unique:products,sku,{product}
it('validates price is numeric'); // UpdateProductRequest numeric
it('validates category_id exists when provided'); // UpdateProductRequest exists:categories,id
it('validates supplier_id exists when provided'); // UpdateProductRequest nullable|exists:suppliers,id
it('allows partial updates'); // UpdateProductRequest optional fields
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without products-update permission'); // ProductPolicy::update
it('returns 404 for non-existent product'); // route model binding
it('returns updated product resource'); // ProductController::update ProductResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

## DELETE /api/v1/products/{product}

```php
it('requires authentication'); // auth:sanctum middleware
it('requires products-delete permission'); // ProductPolicy::delete
it('soft deletes product and returns 204'); // ProductController::destroy
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without products-delete permission'); // ProductPolicy::delete
it('returns 404 for non-existent product'); // route model binding
$response->assertValidRequest()->assertValidResponse(204);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```
