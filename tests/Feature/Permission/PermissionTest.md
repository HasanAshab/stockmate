# Permission API Tests

## Test Organization

Tests should be organized by endpoint into separate files:
- One endpoint per file (e.g., `GetPermissionsTest.php`)
- Use `describe()` blocks with descriptive names

## Testing Guidelines

- **Spectator Setup**: Configured globally in `tests/Pest.php` - do not add to individual test files
- **Contract Testing**: Always use `$response->assertValidRequest()->assertValidResponse(status)`
- **No Duplicate Assertions**: Do not use `assertJsonStructure()` - `assertValidResponse()` already validates against OpenAPI schema
- **File Organization**: One endpoint per file with `describe()` block using descriptive name
- **Read-Only**: Permissions are typically seeded/defined in code, not created via API
- **Spatie Permission**: Uses spatie/laravel-permission package

## Common Testing Patterns

### Testing Permission Retrieval
```php
// Seed permissions
Permission::create(['name' => 'products-view']);
Permission::create(['name' => 'products-create']);
Permission::create(['name' => 'products-update']);

$response = actingAs($user)->getJson('/api/v1/permissions');
expect($response->json('data'))->toHaveCount(3);
```

### Testing Permission Grouping
```php
// Permissions may be grouped by resource
$response = getJson('/api/v1/permissions?filter[group]=products');
expect($response->json('data'))->each->toHaveKey('name', fn ($name) => str_starts_with($name, 'products-'));
```

---

## GET /api/v1/permissions
**File**: `tests/Feature/Permission/GetPermissionsTest.php` (suggested)

```php
describe('List Permissions', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires permissions-view permission'); // PermissionPolicy::viewAny
    it('returns paginated list of permissions with 200'); // PermissionController::index
    it('filters permissions by name'); // AllowedFilter::partial('name')
    it('groups permissions by resource prefix'); // e.g., products-*, users-*
    it('sorts permissions by name'); // allowedSorts('name')
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // PermissionPolicy::viewAny
    it('returns permission resource collection'); // PermissionResource::collection
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
});
```

---

## GET /api/v1/permissions/{permission}
**File**: `tests/Feature/Permission/ShowPermissionTest.php` (suggested)

```php
describe('Show Permission', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires permissions-view permission'); // PermissionPolicy::view
    it('returns permission details with 200'); // PermissionController::show
    it('includes roles relationship'); // with('roles')
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // PermissionPolicy::view
    it('returns 404 for non-existent permission'); // route model binding
    it('returns permission resource'); // PermissionResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## Key Testing Notes

- **Permission Naming**: Follow convention: `{resource}-{action}` (e.g., `products-view`, `users-create`)
- **Seeded Data**: Permissions are typically defined in PermissionSeeder, not created via API
- **Guard Names**: All permissions should use same guard (typically 'web' or 'sanctum')
- **Role Assignment**: Permissions can be assigned to roles or directly to users
- **Wildcard Permissions**: Some implementations support wildcards (e.g., `products-*`)
- **Permission Groups**: Group related permissions (products, users, orders, etc.)
- **CRUD Operations**: Standard pattern is view, create, update, delete per resource
- **Special Permissions**: Some actions need special permissions (e.g., `purchase-orders-receive`, `users-toggle-status`)
- **Read-Only API**: Most implementations don't allow creating/updating permissions via API
