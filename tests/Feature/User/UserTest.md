# User API Tests

## Test Organization

Tests should be organized by endpoint into separate files:
- One endpoint per file (e.g., `GetUsersTest.php`, `CreateUserTest.php`)
- Use `describe()` blocks with descriptive names

## Testing Guidelines

- **Spectator Setup**: Configured globally in `tests/Pest.php` - do not add to individual test files
- **Contract Testing**: Always use `$response->assertValidRequest()->assertValidResponse(status)`
- **No Duplicate Assertions**: Do not use `assertJsonStructure()` - `assertValidResponse()` already validates against OpenAPI schema
- **File Organization**: One endpoint per file with `describe()` block using descriptive name
- **Invalid Requests**: Use `assertInvalidRequest()` when request violates OpenAPI schema
- **Password Handling**: Never return password in responses, hash passwords before storage
- **Role Management**: Test role assignment and permission inheritance

## Common Testing Patterns

### User Creation with Roles
```php
$user = User::factory()->create();
$user->assignRole('admin');
$user->givePermissionTo('users-create');
```

### Testing Password Security
```php
expect(Hash::check('Password123!', $user->password))->toBeTrue();
expect($response->json())->not->toHaveKey('password');
```

### Testing Email/Phone Uniqueness
```php
User::factory()->create(['email' => 'existing@example.com']);
// Test duplicate email rejection
```

---

## GET /api/v1/users
**File**: `tests/Feature/User/GetUsersTest.php` (suggested)

```php
describe('List Users', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires users-view permission'); // UserPolicy::viewAny
    it('returns paginated list of users with 200'); // UserController::index
    it('filters users by name'); // AllowedFilter::partial('name')
    it('filters users by email'); // AllowedFilter::partial('email')
    it('filters users by role'); // AllowedFilter::exact('role')
    it('filters users by is_active status'); // AllowedFilter::exact('is_active')
    it('sorts users by created_at'); // allowedSorts('created_at')
    it('includes roles relationship'); // allowedIncludes('roles')
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without users-view permission'); // UserPolicy::viewAny
    it('returns user resource collection'); // UserResource::collection
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
});
```

---

## POST /api/v1/users
**File**: `tests/Feature/User/CreateUserTest.php` (suggested)

```php
describe('Create User', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires users-create permission'); // UserPolicy::create
    it('creates a new user with email and returns 201'); // CreateUser::execute
    it('creates a new user with phone and returns 201'); // CreateUser::execute
    it('assigns role to new user'); // CreateUser::execute assignRole()
    it('hashes password before storage'); // Hash::make()
    it('returns validation errors for invalid input'); // StoreUserRequest::rules
    it('rejects duplicate email'); // StoreUserRequest unique:users
    it('rejects duplicate phone'); // StoreUserRequest unique:users
    it('requires either email or phone'); // StoreUserRequest required_without
    it('validates phone number format'); // StoreUserRequest phone:BD
    it('validates max length for name field'); // use assertInvalidRequest()
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without users-create permission'); // UserPolicy::create
    it('returns user resource on success'); // UserController::store UserResource
    it('does not return password in response'); // UserResource excludes password
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(201);
    $response->assertInvalidRequest()->assertValidResponse(422);
});
```

---

## GET /api/v1/users/{user}
**File**: `tests/Feature/User/ShowUserTest.php` (suggested)

```php
describe('Show User', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires users-view permission'); // UserPolicy::view
    it('returns user details with 200'); // UserController::show
    it('includes roles relationship when requested'); // allowedIncludes('roles')
    it('includes permissions relationship when requested'); // allowedIncludes('permissions')
    it('does not return password'); // UserResource excludes password
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without users-view permission'); // UserPolicy::view
    it('returns 404 for non-existent user'); // route model binding
    it('returns user resource'); // UserController::show UserResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## PUT/PATCH /api/v1/users/{user}
**File**: `tests/Feature/User/UpdateUserTest.php` (suggested)

```php
describe('Update User', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires users-update permission'); // UserPolicy::update
    it('updates user and returns 200'); // UpdateUser::execute
    it('updates user role'); // UpdateUser::execute syncRoles()
    it('returns validation errors for invalid input'); // UpdateUserRequest::rules
    it('rejects duplicate email'); // UpdateUserRequest unique:users,id
    it('rejects duplicate phone'); // UpdateUserRequest unique:users,id
    it('allows partial updates'); // UpdateUserRequest optional fields
    it('does not update password through this endpoint'); // password handled separately
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without users-update permission'); // UserPolicy::update
    it('returns 404 for non-existent user'); // route model binding
    it('returns updated user resource'); // UserController::update UserResource
    it('does not return password in response'); // UserResource excludes password
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## DELETE /api/v1/users/{user}
**File**: `tests/Feature/User/DeleteUserTest.php` (suggested)

```php
describe('Delete User', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires users-delete permission'); // UserPolicy::delete
    it('soft deletes user and returns 204'); // UserController::destroy
    it('revokes all user tokens on deletion'); // tokens()->delete()
    it('prevents self-deletion'); // UserPolicy::delete check
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without users-delete permission'); // UserPolicy::delete
    it('returns 403 when attempting self-deletion'); // UserPolicy::delete
    it('returns 404 for non-existent user'); // route model binding
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(204);
    $response->assertValidRequest()->assertValidResponse(403);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## POST /api/v1/users/{user}/toggle-status
**File**: `tests/Feature/User/ToggleUserStatusTest.php` (suggested)

```php
describe('Toggle User Status', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires users-update permission'); // UserPolicy::update via Gate::authorize
    it('toggles user status from active to inactive'); // ToggleUserStatus::execute
    it('toggles user status from inactive to active'); // ToggleUserStatus::execute
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without users-update permission'); // UserPolicy::update
    it('returns 404 for non-existent user'); // route model binding
    it('returns updated user resource'); // UserController::toggleStatus UserResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## Key Testing Notes

- **Password Security**: Always test that passwords are hashed and never returned in responses
- **Email/Phone Verification**: Test that `email_verified_at` and `phone_verified_at` are set appropriately
- **Role Synchronization**: When updating roles, test that old roles are removed and new ones added
- **Self-Deletion Prevention**: Users should not be able to delete their own account
- **Token Revocation**: When users are deleted or deactivated, their tokens should be revoked
- **Activity Logging**: User actions should be logged via Spatie Activity Log
