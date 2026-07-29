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

---

## GET /api/v1/users

```php
it('requires authentication'); // auth:sanctum middleware
it('returns paginated list of users with 200'); // UserController::index cursorPaginate(10)
it('includes roles and permissions relationships'); // with(['roles', 'permissions'])
it('filters users by role name'); // AllowedFilter::scope('role')
it('filters users by is_active status'); // AllowedFilter::exact('is_active')
it('filters users by created_at date range'); // AllowedFilter::custom('created_at', FiltersDateRange)
it('sorts users by created_at descending'); // allowedSorts('created_at')
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns user resource collection'); // UserResource::collection
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
```

## POST /api/v1/users

```php
it('requires authentication'); // auth:sanctum middleware
it('creates a new user and returns 201'); // UserController::store
it('returns validation errors for invalid input'); // StoreUserRequest::rules
it('validates required name field'); // StoreUserRequest name required
it('validates required password field'); // StoreUserRequest password required
it('validates password confirmation'); // StoreUserRequest Password::default()
it('validates email or phone is provided'); // StoreUserRequest required_without
it('validates email is unique'); // StoreUserRequest unique:users
it('validates phone is unique'); // StoreUserRequest unique:users
it('validates phone format for BD'); // StoreUserRequest phone:BD
it('validates email format'); // StoreUserRequest email
it('validates max length for name field'); // StoreUserRequest max:50
it('validates is_active is boolean'); // StoreUserRequest boolean
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns user resource on success'); // UserController::store UserResource
$response->assertValidRequest()->assertValidResponse(201);
$response->assertValidRequest()->assertValidResponse(401);
```

## GET /api/v1/users/{user}

```php
it('requires authentication'); // auth:sanctum middleware
it('returns user details with 200'); // UserController::show
it('loads roles and permissions relationships'); // $user->load(['roles', 'permissions'])
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 404 for non-existent user'); // route model binding
it('returns user resource'); // UserController::show UserResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(404);
```

## PUT/PATCH /api/v1/users/{user}

```php
it('requires authentication'); // auth:sanctum middleware
it('updates user and returns 200'); // UserController::update
it('returns validation errors for invalid input'); // UpdateUserRequest::rules
it('validates email is unique excluding current user'); // UpdateUserRequest unique:users,email,{user}
it('validates phone is unique excluding current user'); // UpdateUserRequest unique:users,phone,{user}
it('validates password confirmation when password is provided'); // UpdateUserRequest Password::default()
it('validates phone format for BD'); // UpdateUserRequest phone:BD
it('validates email format'); // UpdateUserRequest email
it('validates max length for name field'); // UpdateUserRequest max:50
it('validates is_active is boolean'); // UpdateUserRequest boolean
it('allows partial updates'); // UpdateUserRequest optional fields
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 404 for non-existent user'); // route model binding
it('returns updated user resource'); // UserController::update UserResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(404);
```

## PATCH /api/v1/users/{user}/toggle-status

```php
it('requires authentication'); // auth:sanctum middleware
it('requires users-deactivate permission'); // UserPolicy::deactivate via Gate::authorize
it('toggles user active status and returns 200'); // UserController::toggleStatus
it('activates inactive user'); // ToggleUserStatus::execute
it('deactivates active user'); // ToggleUserStatus::execute
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without users-deactivate permission'); // UserPolicy::deactivate
it('returns 404 for non-existent user'); // route model binding
it('returns user resource'); // UserController::toggleStatus UserResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

## PUT /api/v1/users/{user}/roles

```php
it('requires authentication'); // auth:sanctum middleware
it('requires users-update-role permission'); // UserPolicy::updateRole via Gate::authorize
it('assigns roles to user and returns 200'); // UserController::assignRoles
it('replaces existing roles with provided roles'); // $user->syncRoles
it('returns validation errors for invalid input'); // AssignRolesRequest::rules
it('validates required roles array'); // AssignRolesRequest roles required
it('validates each role exists'); // AssignRolesRequest roles.* Rule::exists('roles', 'name')
it('loads roles and permissions relationships'); // $user->load(['roles', 'permissions'])
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without users-update-role permission'); // UserPolicy::updateRole
it('returns 404 for non-existent user'); // route model binding
it('returns user resource'); // UserController::assignRoles UserResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```

## PUT /api/v1/users/{user}/permissions

```php
it('requires authentication'); // auth:sanctum middleware
it('requires users-update-role permission'); // UserPolicy::updateRole via Gate::authorize
it('assigns permissions to user and returns 200'); // UserController::assignPermissions
it('replaces existing direct permissions with provided permissions'); // $user->syncPermissions
it('returns validation errors for invalid input'); // AssignPermissionsRequest::rules
it('validates required permissions array'); // AssignPermissionsRequest permissions required
it('validates each permission exists'); // AssignPermissionsRequest permissions.* Rule::enum(Permission)
it('loads roles and permissions relationships'); // $user->load(['roles', 'permissions'])
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without users-update-role permission'); // UserPolicy::updateRole
it('returns 404 for non-existent user'); // route model binding
it('returns user resource'); // UserController::assignPermissions UserResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
$response->assertValidRequest()->assertValidResponse(404);
```
