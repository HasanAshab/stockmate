# Role API Tests

## GET /api/v1/roles

```php
it('requires authentication'); // auth:sanctum middleware
it('requires roles-view permission'); // RolePolicy::viewAny via Gate::authorize
it('returns list of all roles with 200'); // RoleController::index
it('includes permissions relationship'); // with('permissions')
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without roles-view permission'); // RolePolicy::viewAny
it('returns role resource collection'); // RoleResource::collection
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
```
