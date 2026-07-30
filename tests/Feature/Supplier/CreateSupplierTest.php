<?php

use App\Enums\Permission;
use App\Models\Supplier;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('Create Supplier', function () {
    it('requires authentication', function () {
        $response = postJson('/api/v1/suppliers', []);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires suppliers-create permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->postJson('/api/v1/suppliers', []);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('creates a new supplier and returns 201', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersCreate->value);

        $payload = [
            'name' => 'ABC Suppliers Ltd',
            'email' => 'contact@abc.com',
            'phone' => '+8801712345678',
            'address' => '123 Main St, Dhaka',
        ];

        $response = actingAs($user)->postJson('/api/v1/suppliers', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201)
            ->assertJsonFragment([
                'name' => 'ABC Suppliers Ltd',
                'email' => 'contact@abc.com',
            ]);

        $this->assertDatabaseHas('suppliers', [
            'name' => 'ABC Suppliers Ltd',
            'email' => 'contact@abc.com',
        ]);
    });

    it('validates required name', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersCreate->value);

        $response = actingAs($user)->postJson('/api/v1/suppliers', [
            'email' => 'test@supplier.com',
        ]);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('rejects duplicate name', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersCreate->value);

        Supplier::factory()->create(['name' => 'Existing Corp']);

        $payload = [
            'name' => 'Existing Corp',
            'email' => 'new@supplier.com',
        ];

        $response = actingAs($user)->postJson('/api/v1/suppliers', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates email format', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersCreate->value);

        $payload = [
            'name' => 'Valid Supplier Name',
            'email' => 'invalid-email-format',
        ];

        $response = actingAs($user)->postJson('/api/v1/suppliers', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates max length for name', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersCreate->value);

        $payload = [
            'name' => str_repeat('a', 71),
        ];

        $response = actingAs($user)->postJson('/api/v1/suppliers', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates max length for address', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersCreate->value);

        $payload = [
            'name' => 'Valid Name',
            'address' => str_repeat('a', 256),
        ];

        $response = actingAs($user)->postJson('/api/v1/suppliers', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('allows nullable email', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersCreate->value);

        $payload = [
            'name' => 'No Email Supplier',
            'email' => null,
            'phone' => '+8801712345678',
        ];

        $response = actingAs($user)->postJson('/api/v1/suppliers', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);
    });

    it('allows nullable phone', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersCreate->value);

        $payload = [
            'name' => 'No Phone Supplier',
            'email' => 'nophone@supplier.com',
            'phone' => null,
        ];

        $response = actingAs($user)->postJson('/api/v1/suppliers', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = postJson('/api/v1/suppliers', [
            'name' => 'Test Supplier',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->postJson('/api/v1/suppliers', [
            'name' => 'Test Supplier',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns supplier resource on success', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersCreate->value);

        $payload = [
            'name' => 'Global Logistics Inc',
        ];

        $response = actingAs($user)->postJson('/api/v1/suppliers', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);
    });
});
