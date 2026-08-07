<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('Create User', function () {
    it('requires authentication', function () {
        $response = postJson('/api/v1/users', []);

        $response->assertValidResponse(401);
    });

    it('creates a new user with email and returns 201', function () {
        $authUser = User::factory()->create();

        $payload = [
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
            'password' => 'Password123!',
        ];

        $response = actingAs($authUser)->postJson('/api/v1/users', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201)
            ->assertJsonFragment([
                'name' => 'Alice Smith',
                'email' => 'alice@example.com',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
        ]);
    });

    it('creates a new user with phone and returns 201', function () {
        $authUser = User::factory()->create();

        $payload = [
            'name' => 'Bob Builder',
            'phone' => '+8801712345678',
            'password' => 'Password123!',
        ];

        $response = actingAs($authUser)->postJson('/api/v1/users', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201)
            ->assertJsonFragment([
                'name' => 'Bob Builder',
                'phone' => '+8801712345678',
            ]);
    });

    it('hashes password before storage', function () {
        $authUser = User::factory()->create();

        $payload = [
            'name' => 'Charlie Brown',
            'email' => 'charlie@example.com',
            'password' => 'SecurePass123!',
        ];

        $response = actingAs($authUser)->postJson('/api/v1/users', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);

        $createdUser = User::where('email', 'charlie@example.com')->first();
        expect(Hash::check('SecurePass123!', $createdUser->password))->toBeTrue();
    });

    it('returns validation errors for invalid input', function () {
        $authUser = User::factory()->create();

        $response = actingAs($authUser)->postJson('/api/v1/users', []);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('rejects duplicate email', function () {
        $authUser = User::factory()->create();
        User::factory()->create(['email' => 'existing@example.com']);

        $payload = [
            'name' => 'Duplicate Email',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
        ];

        $response = actingAs($authUser)->postJson('/api/v1/users', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('rejects duplicate phone', function () {
        $authUser = User::factory()->create();
        User::factory()->create(['phone' => '+8801712345678']);

        $payload = [
            'name' => 'Duplicate Phone',
            'phone' => '+8801712345678',
            'password' => 'Password123!',
        ];

        $response = actingAs($authUser)->postJson('/api/v1/users', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('requires either email or phone', function () {
        $authUser = User::factory()->create();

        $payload = [
            'name' => 'No Contact User',
            'password' => 'Password123!',
        ];

        $response = actingAs($authUser)->postJson('/api/v1/users', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('validates phone number format', function () {
        $authUser = User::factory()->create();

        $payload = [
            'name' => 'Bad Phone User',
            'phone' => '12345',
            'password' => 'Password123!',
        ];

        $response = actingAs($authUser)->postJson('/api/v1/users', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('validates max length for name field', function () {
        $authUser = User::factory()->create();

        $payload = [
            'name' => str_repeat('a', 51),
            'email' => 'longname@example.com',
            'password' => 'Password123!',
        ];

        $response = actingAs($authUser)->postJson('/api/v1/users', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('returns user resource on success', function () {
        $authUser = User::factory()->create();

        $payload = [
            'name' => 'Dave Miller',
            'email' => 'dave@example.com',
            'password' => 'Password123!',
        ];

        $response = actingAs($authUser)->postJson('/api/v1/users', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);
    });

    it('does not return password in response', function () {
        $authUser = User::factory()->create();

        $payload = [
            'name' => 'Eve Adams',
            'email' => 'eve@example.com',
            'password' => 'Password123!',
        ];

        $response = actingAs($authUser)->postJson('/api/v1/users', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);

        expect($response->json('data'))->not->toHaveKey('password');
    });
});
