# Authentication API Tests

## Test Organization

Tests are split by endpoint into separate files:
- `tests/Feature/Auth/RegisterTest.php` - Registration endpoint
- `tests/Feature/Auth/LoginTest.php` - Login endpoint
- `tests/Feature/Auth/SocialLoginTest.php` - Social authentication endpoint
- `tests/Feature/Auth/LogoutTest.php` - Logout endpoint
- `tests/Feature/Auth/VerifyTest.php` - Account verification endpoint
- `tests/Feature/Auth/ResendVerificationTest.php` - Resend verification code endpoint
- `tests/Feature/Auth/ChangePasswordTest.php` - Change password endpoint
- `tests/Feature/Auth/ForgotPasswordTest.php` - Forgot password endpoint
- `tests/Feature/Auth/ResetPasswordTest.php` - Reset password endpoint

## Testing Guidelines

- **Spectator Setup**: Configured globally in `tests/Pest.php` - do not add to individual test files
- **Contract Testing**: Always use `$response->assertValidRequest()->assertValidResponse(status)`
- **No Duplicate Assertions**: Do not use `assertJsonStructure()` - `assertValidResponse()` already validates against OpenAPI schema
- **File Organization**: One endpoint per file with `describe()` block using descriptive name

---

## POST /api/v1/auth/register
**File**: `tests/Feature/Auth/RegisterTest.php`

```php
describe('Registration', function () {
    it('registers a new user with email and returns 201 with token'); // AuthController::register, RegisterUser::execute
    it('registers a new user with phone and returns 201 with token'); // RegisterRequest phone required_without:email
    it('returns validation errors for invalid input'); // RegisterRequest::rules
    it('rejects registration with duplicate email'); // RegisterRequest unique:users
    it('rejects registration with duplicate phone'); // RegisterRequest unique:users
    it('creates inactive user by default'); // RegisterUser::execute is_active=false
    it('fires Registered event after successful registration'); // RegisterUser::execute event(new Registered)
    it('validates phone number format for BD'); // RegisterRequest phone:BD
    it('requires either email or phone'); // RegisterRequest required_without
    it('validates max length for name field'); // RegisterRequest max:50
    it('returns user resource and bearer token on success'); // AuthController::register response structure
    
    // Contract testing - validates against OpenAPI spec
    $response->assertValidRequest()->assertValidResponse(201);
});
```

## POST /api/v1/auth/login
**File**: `tests/Feature/Auth/LoginTest.php`

```php
describe('Login', function () {
    it('authenticates user with email and returns 200 with token'); // AuthController::login, LoginUser::execute
    it('authenticates user with phone and returns 200 with token'); // LoginUser::execute User::findByIdentifier
    it('returns validation errors for invalid input'); // LoginRequest::rules
    it('returns 401 for non-existent user'); // LoginUser::execute ValidationException
    it('returns 401 for incorrect password'); // LoginUser::execute Hash::check
    it('returns user resource and bearer token on success'); // AuthController::login response structure
    
    // Contract testing - validates against OpenAPI spec
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(401);
});
```

## POST /api/v1/auth/social
**File**: `tests/Feature/Auth/SocialLoginTest.php`

```php
describe('Social Login', function () {
    it('authenticates existing user with google token and returns 200'); // AuthController::socialLogin with Google
    it('authenticates existing user with microsoft token and returns 200'); // AuthController::socialLogin with Microsoft
    it('registers new user with valid google token and returns 200'); // LoginOrRegisterSocialUser::execute creates user
    it('registers new user with valid microsoft token and returns 200'); // LoginOrRegisterSocialUser::execute creates user
    it('returns validation errors for invalid input'); // SocialLoginRequest::rules
    it('rejects invalid provider value'); // SocialLoginRequest Rule::enum(SocialProvider)
    it('returns 401 for invalid social token'); // SocialAuthManager driver resolve failure
    it('returns user resource and bearer token on success'); // AuthController::socialLogin response structure
    
    // Contract testing - validates against OpenAPI spec
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(401);
});
```

## POST /api/v1/auth/logout
**File**: `tests/Feature/Auth/LogoutTest.php`

```php
describe('Logout', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('revokes current access token and returns 204'); // AuthController::logout currentAccessToken()->delete()
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    
    // Contract testing - validates against OpenAPI spec
    $response->assertValidRequest()->assertValidResponse(204);
    $response->assertValidRequest()->assertValidResponse(401);
});
```

## POST /api/v1/auth/verify
**File**: `tests/Feature/Auth/VerifyTest.php`

```php
describe('Account Verification', function () {
    it('verifies user email with valid OTP code and returns 200'); // VerifyAccount::execute markEmailAsVerified
    it('verifies user phone with valid OTP code and returns 200'); // VerifyAccount::execute markPhoneAsVerified
    it('returns validation errors for invalid input'); // VerifyAccountRequest::rules
    it('returns 400 for non-existent user'); // VerifyAccount::execute ConsumeOneTimePasswordResult
    it('returns 400 for invalid OTP code'); // VerifyAccount::execute consumeOneTimePassword
    it('returns 400 for expired OTP code'); // VerifyAccount::execute consumeOneTimePassword
    
    // Contract testing - validates against OpenAPI spec
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(400);
});
```

## POST /api/v1/auth/verification-notification
**File**: `tests/Feature/Auth/ResendVerificationTest.php`

```php
describe('Resend Verification', function () {
    it('resends verification code to email and returns 200'); // SendVerificationOtp::execute with email
    it('resends verification code to phone and returns 200'); // SendVerificationOtp::execute with phone
    it('returns validation errors for invalid input'); // ResendOtpRequest::rules
    it('silently succeeds for non-existent user'); // SendVerificationOtp::execute early return
    it('silently succeeds for already verified user'); // SendVerificationOtp::execute isVerified check
    it('creates new OTP and sends notification'); // SendVerificationOtp::execute createOneTimePassword + notify
    
    // Contract testing - validates against OpenAPI spec
    $response->assertValidRequest()->assertValidResponse(200);
});
```

## POST /api/v1/auth/change-password
**File**: `tests/Feature/Auth/ChangePasswordTest.php`

```php
describe('Change Password', function () {
    it('requires authentication'); // auth:sanctum + Authenticated attribute
    it('changes password for authenticated user and returns 200'); // ChangeUserPassword::execute
    it('returns validation errors for invalid input'); // ChangePasswordRequest::rules
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    
    // Contract testing - validates against OpenAPI spec
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(401);
});
```

## POST /api/v1/auth/forgot-password
**File**: `tests/Feature/Auth/ForgotPasswordTest.php`

```php
describe('Forgot Password', function () {
    it('sends password reset OTP to email and returns 202'); // SendPasswordResetOtp::execute with email
    it('sends password reset OTP to phone and returns 202'); // SendPasswordResetOtp::execute with phone
    it('returns validation errors for invalid input'); // ForgotPasswordRequest::rules
    it('silently succeeds for non-existent user'); // security best practice - no user enumeration
    
    // Contract testing - validates against OpenAPI spec
    $response->assertValidRequest()->assertValidResponse(202);
});
```

## POST /api/v1/auth/reset-password
**File**: `tests/Feature/Auth/ResetPasswordTest.php`

```php
describe('Reset Password', function () {
    it('resets password with valid OTP and returns 200'); // ResetUserPassword::execute
    it('returns validation errors for invalid input'); // ResetPasswordRequest::rules
    it('returns 400 for invalid OTP code'); // ResetUserPassword::execute validation
    it('returns 400 for expired OTP code'); // ResetUserPassword::execute validation
    
    // Contract testing - validates against OpenAPI spec
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(400);
});
```
