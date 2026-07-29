<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\postJson;


describe('POST /api/v1/auth/register', function () {
    it('registers a new user with email and returns 201 with token', function () {
        Event::fake([Registered::class]);

        $response = postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
                'token_type',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => false,
        ]);

        Event::assertDispatched(Registered::class);
    });

    it('registers a new user with phone and returns 201 with token', function () {
        Event::fake([Registered::class]);

        $response = postJson('/api/v1/auth/register', [
            'name' => 'Jane Doe',
            'phone' => '+8801712345678',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'phone'],
                'token',
                'token_type',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Jane Doe',
            'phone' => '+8801712345678',
            'is_active' => false,
        ]);

        Event::assertDispatched(Registered::class);
    });

    it('returns validation errors for invalid input', function () {
        $response = postJson('/api/v1/auth/register', [
            'name' => '',
            'password' => 'short',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422)
            ->assertJsonValidationErrors(['name', 'password']);
    });

    it('rejects registration with duplicate email', function () {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('rejects registration with duplicate phone', function () {
        User::factory()->create(['phone' => '+8801712345678']);

        $response = postJson('/api/v1/auth/register', [
            'name' => 'Jane Doe',
            'phone' => '+8801712345678',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422)
            ->assertJsonValidationErrors(['phone']);
    });

    it('creates inactive user by default', function () {
        postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ])->assertValidRequest()->assertValidResponse(201);

        $user = User::where('email', 'john@example.com')->first();
        expect($user->is_active)->toBeFalse();
    });

    it('fires Registered event after successful registration', function () {
        Event::fake([Registered::class]);

        postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ])->assertValidRequest()->assertValidResponse(201);

        Event::assertDispatched(Registered::class, function ($event) {
            return $event->user->email === 'john@example.com';
        });
    });

    it('validates phone number format for BD', function () {
        $response = postJson('/api/v1/auth/register', [
            'name' => 'Jane Doe',
            'phone' => 'invalid-phone',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422)
            ->assertJsonValidationErrors(['phone']);
    });

    it('requires either email or phone', function () {
        $response = postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('validates max length for name field', function () {
        $response = postJson('/api/v1/auth/register', [
            'name' => str_repeat('a', 51),
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422)
            ->assertJsonValidationErrors(['name']);
    });

    it('returns user resource and bearer token on success', function () {
        $response = postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
                'token_type',
            ])
            ->assertJsonFragment(['token_type' => 'Bearer']);
    });
});

describe('POST /api/v1/auth/login', function () {
    it('authenticates user with email and returns 200 with token', function () {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = postJson('/api/v1/auth/login', [
            'identifier' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
                'token_type',
            ])
            ->assertJsonFragment(['email' => 'john@example.com']);
    });

    it('authenticates user with phone and returns 200 with token', function () {
        $user = User::factory()->create([
            'phone' => '+8801712345678',
            'password' => Hash::make('Password123!'),
        ]);

        $response = postJson('/api/v1/auth/login', [
            'identifier' => '+8801712345678',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'phone'],
                'token',
                'token_type',
            ])
            ->assertJsonFragment(['phone' => '+8801712345678']);
    });

    it('returns validation errors for invalid input', function () {
        $response = postJson('/api/v1/auth/login', [
            'identifier' => '',
            'password' => '',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422)
            ->assertJsonValidationErrors(['identifier', 'password']);
    });

    it('returns 401 for non-existent user', function () {
        $response = postJson('/api/v1/auth/login', [
            'identifier' => 'nonexistent@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 401 for incorrect password', function () {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('CorrectPassword123!'),
        ]);

        $response = postJson('/api/v1/auth/login', [
            'identifier' => 'john@example.com',
            'password' => 'WrongPassword123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns user resource and bearer token on success', function () {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = postJson('/api/v1/auth/login', [
            'identifier' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonStructure([
                'user',
                'token',
                'token_type',
            ])
            ->assertJsonFragment(['token_type' => 'Bearer']);
    });
});

describe('POST /api/v1/auth/social', function () {
    it('authenticates existing user with google token and returns 200', function () {
        $this->markTestSkipped('Requires Google OAuth token mocking setup');
    });

    it('authenticates existing user with microsoft token and returns 200', function () {
        $this->markTestSkipped('Requires Microsoft OAuth token mocking setup');
    });

    it('registers new user with valid google token and returns 200', function () {
        $this->markTestSkipped('Requires Google OAuth token mocking setup');
    });

    it('registers new user with valid microsoft token and returns 200', function () {
        $this->markTestSkipped('Requires Microsoft OAuth token mocking setup');
    });

    it('returns validation errors for invalid input', function () {
        $response = postJson('/api/v1/auth/social', [
            'provider' => '',
            'token' => '',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422)
            ->assertJsonValidationErrors(['provider', 'token']);
    });

    it('rejects invalid provider value', function () {
        $response = postJson('/api/v1/auth/social', [
            'provider' => 'invalid-provider',
            'token' => 'some-token',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422)
            ->assertJsonValidationErrors(['provider']);
    });

    it('returns 401 for invalid social token', function () {
        $this->markTestSkipped('Requires social provider token mocking setup');
    });

    it('returns user resource and bearer token on success', function () {
        $this->markTestSkipped('Requires social provider token mocking setup');
    });
});

describe('POST /api/v1/auth/logout', function () {
    it('requires authentication', function () {
        $response = postJson('/api/v1/auth/logout');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('revokes current access token and returns 204', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = postJson('/api/v1/auth/logout', [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(204);

        expect($user->fresh()->tokens)->toHaveCount(0);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = postJson('/api/v1/auth/logout');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });
});

describe('POST /api/v1/auth/verify', function () {
    it('verifies user email with valid OTP code and returns 200', function () {
        $this->markTestSkipped('Requires OTP generation and verification setup');
    });

    it('verifies user phone with valid OTP code and returns 200', function () {
        $this->markTestSkipped('Requires OTP generation and verification setup');
    });

    it('returns validation errors for invalid input', function () {
        $response = postJson('/api/v1/auth/verify', [
            'identifier' => '',
            'code' => '',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422)
            ->assertJsonValidationErrors(['identifier', 'code']);
    });

    it('returns 400 for non-existent user', function () {
        $this->markTestSkipped('Requires OTP verification setup');
    });

    it('returns 400 for invalid OTP code', function () {
        $this->markTestSkipped('Requires OTP verification setup');
    });

    it('returns 400 for expired OTP code', function () {
        $this->markTestSkipped('Requires OTP verification setup');
    });
});

describe('POST /api/v1/auth/verification-notification', function () {
    it('resends verification code to email and returns 200', function () {
        $this->markTestSkipped('Requires notification/OTP setup');
    });

    it('resends verification code to phone and returns 200', function () {
        $this->markTestSkipped('Requires notification/OTP setup');
    });

    it('returns validation errors for invalid input', function () {
        $response = postJson('/api/v1/auth/verification-notification', [
            'identifier' => '',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422)
            ->assertJsonValidationErrors(['identifier']);
    });

    it('silently succeeds for non-existent user', function () {
        $response = postJson('/api/v1/auth/verification-notification', [
            'identifier' => 'nonexistent@example.com',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('silently succeeds for already verified user', function () {
        $this->markTestSkipped('Requires verification status check setup');
    });

    it('creates new OTP and sends notification', function () {
        $this->markTestSkipped('Requires OTP and notification verification setup');
    });
});

describe('POST /api/v1/auth/change-password', function () {
    it('requires authentication', function () {
        $response = postJson('/api/v1/auth/change-password', [
            'password' => 'NewPassword123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('changes password for authenticated user and returns 200', function () {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = postJson('/api/v1/auth/change-password', [
            'password' => 'NewPassword123!',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);

        expect(Hash::check('NewPassword123!', $user->fresh()->password))->toBeTrue();
    });

    it('returns validation errors for invalid input', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = postJson('/api/v1/auth/change-password', [
            'password' => '',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422)
            ->assertJsonValidationErrors(['password']);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = postJson('/api/v1/auth/change-password', [
            'password' => 'NewPassword123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });
});

describe('POST /api/v1/auth/forgot-password', function () {
    it('sends password reset OTP to email and returns 202', function () {
        User::factory()->create(['email' => 'john@example.com']);

        $response = postJson('/api/v1/auth/forgot-password', [
            'identifier' => 'john@example.com',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(202);
    });

    it('sends password reset OTP to phone and returns 202', function () {
        User::factory()->create(['phone' => '+8801712345678']);

        $response = postJson('/api/v1/auth/forgot-password', [
            'identifier' => '+8801712345678',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(202);
    });

    it('returns validation errors for invalid input', function () {
        $response = postJson('/api/v1/auth/forgot-password', [
            'identifier' => '',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422)
            ->assertJsonValidationErrors(['identifier']);
    });

    it('silently succeeds for non-existent user', function () {
        $response = postJson('/api/v1/auth/forgot-password', [
            'identifier' => 'nonexistent@example.com',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(202);
    });
});

describe('POST /api/v1/auth/reset-password', function () {
    it('resets password with valid OTP and returns 200', function () {
        $this->markTestSkipped('Requires OTP generation and verification setup');
    });

    it('returns validation errors for invalid input', function () {
        $response = postJson('/api/v1/auth/reset-password', [
            'identifier' => '',
            'code' => '',
            'password' => '',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422)
            ->assertJsonValidationErrors(['identifier', 'code', 'password']);
    });

    it('returns 400 for invalid OTP code', function () {
        $this->markTestSkipped('Requires OTP verification setup');
    });

    it('returns 400 for expired OTP code', function () {
        $this->markTestSkipped('Requires OTP verification setup');
    });
});

