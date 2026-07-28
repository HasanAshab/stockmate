# Permission API Tests

## GET /api/v1/permissions

```php
it('requires authentication'); // auth:sanctum middleware
it('requires permissions-view permission'); // PermissionPolicy::viewAny via Gate::authorize
it('returns list of all permissions with 200'); // PermissionController::index
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without permissions-view permission'); // PermissionPolicy::viewAny
it('returns permission resource collection'); // PermissionResource::collection
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
```
