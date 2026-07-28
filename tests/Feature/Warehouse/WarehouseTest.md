# Warehouse API Tests

## GET /api/v1/warehouses

```php
it('requires authentication'); // auth:sanctum middleware
it('requires warehouses-view permission'); // WarehousePolicy::viewAny
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without warehouses-view permission'); // WarehousePolicy::viewAny
it('returns paginated list of warehouses with 200'); // WarehouseController::index cursorPaginate(7)
it('returns warehouse resource collection'); // WarehouseResource::collection
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
```

## POST /api/v1/warehouses

```php
it('requires authentication'); // auth:sanctum middleware
it('requires warehouses-create permission'); // WarehousePolicy::create
it('creates a new warehouse and returns 201'); // WarehouseController::store
it('returns validation errors for invalid input'); // StoreWarehouseRequest::rules
it('validates required name field'); // StoreWarehouseRequest name required
it('validates required location field'); // StoreWarehouseRequest location required
it('validates max length for name field'); // StoreWarehouseRequest max
it('validates max length for location field'); // StoreWarehouseRequest max
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without warehouses-create permission'); // WarehousePolicy::create
it('returns warehouse resource on success'); // WarehouseController::store WarehouseResource
$response->assertValidRequest()->assertValidResponse(201);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
```

## GET /api/v1/warehouses/{warehouse}

```php
it('requires authentication'); // auth:sanctum middleware
it('requires warehouses-view permission'); // WarehousePolicy::view
it('returns warehouse details with 200'); // WarehouseController::show
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without warehouses-view permission'); // WarehousePolicy::view
it('returns 404 for non-existent warehouse'); // route model binding
it('returns warehouse resource'); // WarehouseController::show WarehouseResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

## PUT/PATCH /api/v1/warehouses/{warehouse}

```php
it('requires authentication'); // auth:sanctum middleware
it('requires warehouses-update permission'); // WarehousePolicy::update
it('updates warehouse and returns 200'); // WarehouseController::update
it('returns validation errors for invalid input'); // UpdateWarehouseRequest::rules
it('validates max length for name field'); // UpdateWarehouseRequest max
it('validates max length for location field'); // UpdateWarehouseRequest max
it('allows partial updates'); // UpdateWarehouseRequest optional fields
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without warehouses-update permission'); // WarehousePolicy::update
it('returns 404 for non-existent warehouse'); // route model binding
it('returns updated warehouse resource'); // WarehouseController::update WarehouseResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

## DELETE /api/v1/warehouses/{warehouse}

```php
it('requires authentication'); // auth:sanctum middleware
it('requires warehouses-delete permission'); // WarehousePolicy::delete
it('deletes empty warehouse and returns 204'); // WarehouseController::destroy
it('returns 400 when warehouse has stock'); // DeleteWarehouse::execute WarehouseNotEmpty
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without warehouses-delete permission'); // WarehousePolicy::delete
it('returns 404 for non-existent warehouse'); // route model binding
$response->assertValidRequest()->assertValidResponse(204);
$response->assertValidRequest()->assertValidResponse(400);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```
