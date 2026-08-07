<?php

use App\Enums\Permission;
use App\Models\User;
use App\Models\Warehouse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('Create Warehouse', function () {
    it('requires authentication', function () {
        $response = postJson('/api/v1/warehouses', []);

        $response->assertValidResponse(401);
    });

    it('requires warehouses-create permission', function () {
        $user = User::factory()->create();

        $payload = [
            'name' => 'Main Warehouse',
            'location' => 'Dhaka, Bangladesh',
        ];
        $response = actingAs($user)->postJson('/api/v1/warehouses', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('creates a new warehouse and returns 201', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesCreate->value);

        $payload = [
            'name' => 'Main Warehouse',
            'location' => 'Dhaka, Bangladesh',
        ];

        $response = actingAs($user)->postJson('/api/v1/warehouses', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201)
            ->assertJsonFragment([
                'name' => 'Main Warehouse',
                'location' => 'Dhaka, Bangladesh',
            ]);

        $this->assertDatabaseHas('warehouses', [
            'name' => 'Main Warehouse',
            'location' => 'Dhaka, Bangladesh',
        ]);
    });

    it('validates required name', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesCreate->value);

        $response = actingAs($user)->postJson('/api/v1/warehouses', [
            'location' => 'Dhaka',
        ]);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates max length for name', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesCreate->value);

        $payload = [
            'name' => str_repeat('a', 256),
        ];

        $response = actingAs($user)->postJson('/api/v1/warehouses', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates max length for location', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesCreate->value);

        $payload = [
            'name' => 'Valid Warehouse Name',
            'location' => str_repeat('a', 256),
        ];

        $response = actingAs($user)->postJson('/api/v1/warehouses', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = postJson('/api/v1/warehouses', [
            'name' => 'Test Warehouse',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->postJson('/api/v1/warehouses', [
            'name' => 'Test Warehouse',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns warehouse resource on success', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesCreate->value);

        $payload = [
            'name' => 'North Depot',
            'location' => 'Chittagong',
        ];

        $response = actingAs($user)->postJson('/api/v1/warehouses', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);
    });
});
