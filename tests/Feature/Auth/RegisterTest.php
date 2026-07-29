<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\postJson;

describe('Registration', function () {
    it('registers a new user with email and returns 201 with token', function () {
        Event::fake([Registered::class]);

        $response = postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(201);

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
            ->assertValidResponse(201);

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
            ->assertValidResponse(422);
    });

    it('rejects registration with duplicate email', function () {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('rejects registration with duplicate phone', function () {
        User::factory()->create(['phone' => '+8801712345678']);

        $response = postJson('/api/v1/auth/register', [
            'name' => 'Jane Doe',
            'phone' => '+8801712345678',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
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
            ->assertValidResponse(422);
    });

    it('requires either email or phone', function () {
        $response = postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('validates max length for name field', function () {
        $response = postJson('/api/v1/auth/register', [
            'name' => str_repeat('a', 51),
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('returns user resource and bearer token on success', function () {
        $response = postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(201)
            ->assertJsonFragment(['token_type' => 'Bearer']);
    });
});
