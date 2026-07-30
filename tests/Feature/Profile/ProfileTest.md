# Profile API Tests

## Test Organization

Tests should be organized by endpoint into separate files:
- One endpoint per file (e.g., `GetProfileTest.php`, `UpdateProfileTest.php`)
- Use `describe()` blocks with descriptive names

## Testing Guidelines

- **Spectator Setup**: Configured globally in `tests/Pest.php` - do not add to individual test files
- **Contract Testing**: Always use `$response->assertValidRequest()->assertValidResponse(status)`
- **No Duplicate Assertions**: Do not use `assertJsonStructure()` - `assertValidResponse()` already validates against OpenAPI schema
- **File Organization**: One endpoint per file with `describe()` block using descriptive name
- **Invalid Requests**: Use `assertInvalidRequest()` when request violates OpenAPI schema
- **Authentication Required**: All profile endpoints require authentication
- **Self-Service**: Users can only view/update their own profile

## Common Testing Patterns

### Authenticated User Context
```php
$user = User::factory()->create(['name' => 'John Doe']);
$token = $user->createToken('test-token')->plainTextToken;

$response = getJson('/api/v1/profile', [
    'Authorization' => "Bearer {$token}",
]);

expect($response->json('name'))->toBe('John Doe');
```

---

## GET /api/v1/profile
**File**: `tests/Feature/Profile/GetProfileTest.php` (suggested)

```php
describe('Get Profile', function () {
    it('requires authentication'); // auth:sanctum + Authenticated attribute
    it('returns authenticated user profile with 200'); // ProfileController::show
    it('returns user resource'); // UserResource
    it('includes roles when requested'); // allowedIncludes('roles')
    it('includes permissions when requested'); // allowedIncludes('permissions')
    it('does not return password'); // UserResource excludes password
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(401);
});
```

---

## PUT/PATCH /api/v1/profile
**File**: `tests/Feature/Profile/UpdateProfileTest.php` (suggested)

```php
describe('Update Profile', function () {
    it('requires authentication'); // auth:sanctum + Authenticated attribute
    it('updates authenticated user profile and returns 200'); // UpdateProfile::execute
    it('updates name'); // UpdateProfileRequest name field
    it('updates email if unique'); // UpdateProfileRequest email unique
    it('updates phone if unique'); // UpdateProfileRequest phone unique
    it('rejects duplicate email'); // UpdateProfileRequest unique:users validation
    it('rejects duplicate phone'); // UpdateProfileRequest unique:users validation
    it('allows partial updates'); // UpdateProfileRequest optional fields
    it('validates email format'); // UpdateProfileRequest email validation
    it('validates phone format'); // UpdateProfileRequest phone:BD validation
    it('validates max length for name field'); // use assertInvalidRequest()
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns updated user resource'); // ProfileController::update UserResource
    it('does not return password'); // UserResource excludes password
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertInvalidRequest()->assertValidResponse(422);
});
```

---

## Key Testing Notes

- **Self-Service Only**: Users can only access/modify their own profile, not other users
- **Password Changes**: Password updates handled separately via `/auth/change-password`
- **Email/Phone Verification**: When changing email/phone, verification may be required
- **Profile Image**: If supported, test with `UploadedFile::fake()->image('avatar.jpg')`
- **Activity Logging**: Profile changes should be logged
- **No Admin Override**: Even admins must use user management endpoints to modify other users
