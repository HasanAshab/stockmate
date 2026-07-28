# Profile API Tests

## GET /api/v1/profile

```php
it('requires authentication'); // auth:sanctum middleware
it('returns authenticated user profile with 200'); // ProfileController::show
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns user resource'); // ProfileController::show UserResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
```

## PUT/PATCH /api/v1/profile

```php
it('requires authentication'); // auth:sanctum middleware
it('updates authenticated user profile and returns 200'); // ProfileController::update
it('returns validation errors for invalid input'); // UpdateProfileRequest::rules
it('validates email is unique excluding current user'); // UpdateProfileRequest unique:users,email,{user}
it('validates phone is unique excluding current user'); // UpdateProfileRequest unique:users,phone,{user}
it('validates email format'); // UpdateProfileRequest email
it('validates phone format for BD'); // UpdateProfileRequest phone:BD
it('validates max length for name field'); // UpdateProfileRequest max:50
it('allows partial updates'); // UpdateProfileRequest optional fields
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns updated user resource'); // ProfileController::update UserResource
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
```
