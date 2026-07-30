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
- **Invalid Requests**: Use `assertInvalidRequest()` when request violates OpenAPI schema
- **Spatie Permission**: Uses spatie/laravel-permission package
- **System Roles**: Some roles may be protected from modification

## Common Testing Patterns

### Creating Roles with Permissions
```php
$permissions = Permission::factory()->count(3)->create();

$response = postJson('/api/v1/roles', [
    'name' => 'Manager',
    'permissions' => $permissions->pluck('name')->toArray(),
]);
```

### Testing Permission Sync
```php
$role = Role::factory()->create();
$role->givePermissionTo(['products-view', 'products-create']);

$response = patchJson("/api/v1/roles/{$role->id}", [
    'permissions' => ['products-view', 'products-update'], // Remove create, add update
]);

expect($role->fresh()->permissions->pluck('name'))->toContain('products-update');
expect($role->fresh()->permissions->pluck('name'))->not->toContain('products-create');
```

---

## GET /api/v1/roles
**File**: `tests/Feature/Role/GetRolesTest.php` (suggested)

```php
describe('List Roles', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires roles-view permission'); // RolePolicy::viewAny
    it('returns paginated list of roles with 200'); // RoleController::index
    it('includes permissions relationship'); // with('permissions')
    it('filters roles by name'); // AllowedFilter::partial('name')
    it('sorts roles by name'); // allowedSorts('name')
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // RolePolicy::viewAny
    it('returns role resource collection'); // RoleResource::collection
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
});
```

---

## POST /api/v1/roles
**File**: `tests/Feature/Role/CreateRoleTest.php` (suggested)

```php
describe('Create Role', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires roles-create permission'); // RolePolicy::create
    it('creates a new role and returns 201'); // RoleController::store
    it('creates role with permissions'); // syncPermissions()
    it('validates required name'); // StoreRoleRequest::rules
    it('validates unique name'); // StoreRoleRequest unique:roles
    it('validates permissions array'); // StoreRoleRequest permissions array
    it('validates permissions exist'); // StoreRoleRequest permissions.* exists:permissions,name
    it('validates max length for name'); // max:50 - use assertInvalidRequest()
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // RolePolicy::create
    it('returns role resource on success'); // RoleResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(201);
    $response->assertInvalidRequest()->assertValidResponse(422);
});
```

---

## GET /api/v1/roles/{role}
**File**: `tests/Feature/Role/ShowRoleTest.php` (suggested)

```php
describe('Show Role', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires roles-view permission'); // RolePolicy::view
    it('returns role details with 200'); // RoleController::show
    it('includes permissions relationship'); // with('permissions')
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // RolePolicy::view
    it('returns 404 for non-existent role'); // route model binding
    it('returns role resource'); // RoleResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## PUT/PATCH /api/v1/roles/{role}
**File**: `tests/Feature/Role/UpdateRoleTest.php` (suggested)

```php
describe('Update Role', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires roles-update permission'); // RolePolicy::update
    it('updates role and returns 200'); // RoleController::update
    it('updates role name'); // UpdateRoleRequest name
    it('updates role permissions'); // syncPermissions()
    it('validates unique name'); // UpdateRoleRequest unique:roles,id
    it('validates permissions exist'); // UpdateRoleRequest permissions.* exists:permissions,name
    it('allows partial updates'); // optional fields
    it('returns 403 when updating protected system role'); // RolePolicy::update
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // RolePolicy::update
    it('returns 404 for non-existent role'); // route model binding
    it('returns updated role resource'); // RoleResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(403);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## DELETE /api/v1/roles/{role}
**File**: `tests/Feature/Role/DeleteRoleTest.php` (suggested)

```php
describe('Delete Role', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires roles-delete permission'); // RolePolicy::delete
    it('deletes role and returns 204'); // RoleController::destroy
    it('returns 403 when deleting protected system role'); // RolePolicy::delete
    it('returns 400 when role has assigned users'); // business rule check
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // RolePolicy::delete
    it('returns 404 for non-existent role'); // route model binding
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(204);
    $response->assertValidRequest()->assertValidResponse(400);
    $response->assertValidRequest()->assertValidResponse(403);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## Key Testing Notes

- **System Roles**: Roles like 'admin' or 'super-admin' may be protected from modification/deletion
- **Permission Sync**: When updating role permissions, use syncPermissions() to replace all
- **User Assignment**: Test that users inherit permissions from assigned roles
- **Guard Names**: All roles should use same guard (typically 'web' or 'sanctum')
- **Deletion Restrictions**: Cannot delete roles that are assigned to users
- **Permission Validation**: All permission names must exist in permissions table
- **Case Sensitivity**: Role names should be case-insensitive for uniqueness
