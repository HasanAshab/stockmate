# Role API Tests

## Test Organization

Tests should be organized by endpoint into separate files:
- One endpoint per file (e.g., `GetRolesTest.php`, `CreateRoleTest.php`)
- Use `describe()` blocks with descriptive names

## Testing Guidelines

- **Spectator Setup**: Configured globally in `tests/Pest.php` - do not add to individual test files
- **Contract Testing**: Always use `$response->assertValidRequest()->assertValidResponse(status)`
- **No Duplicate Assertions**: Do not use `assertJsonStructure()` - `assertValidResponse()` already validates against OpenAPI schema
- **File Organization**: One endpoint per file with `describe()` block using descriptive name

---

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
