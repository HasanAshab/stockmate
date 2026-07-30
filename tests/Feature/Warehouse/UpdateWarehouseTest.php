<?php

use App\Enums\Permission;
use App\Models\User;
use App\Models\Warehouse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\putJson;

describe('Update Warehouse', function () {
    it('requires authentication', function () {
        $warehouse = Warehouse::factory()->create();

        $response = putJson("/api/v1/warehouses/{$warehouse->id}", []);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires warehouses-update permission', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $response = actingAs($user)->putJson("/api/v1/warehouses/{$warehouse->id}", []);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('updates warehouse and returns 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesUpdate->value);

        $warehouse = Warehouse::factory()->create([
            'name' => 'Old Warehouse Name',
        ]);

        $payload = [
            'name' => 'New Warehouse Name',
            'location' => 'New Location',
        ];

        $response = actingAs($user)->putJson("/api/v1/warehouses/{$warehouse->id}", $payload);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment([
                'name' => 'New Warehouse Name',
                'location' => 'New Location',
            ]);

        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'name' => 'New Warehouse Name',
        ]);
    });

    it('allows partial updates', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesUpdate->value);

        $warehouse = Warehouse::factory()->create([
            'name' => 'Original Name',
            'location' => 'Original Location',
        ]);

        $payload = [
            'name' => 'Partially Updated Warehouse',
        ];

        $response = actingAs($user)->putJson("/api/v1/warehouses/{$warehouse->id}", $payload);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment([
                'name' => 'Partially Updated Warehouse',
                'location' => 'Original Location',
            ]);
    });

    it('validates max length for name', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesUpdate->value);

        $warehouse = Warehouse::factory()->create();

        $payload = [
            'name' => str_repeat('a', 256),
        ];

        $response = actingAs($user)->putJson("/api/v1/warehouses/{$warehouse->id}", $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates max length for location', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesUpdate->value);

        $warehouse = Warehouse::factory()->create();

        $payload = [
            'location' => str_repeat('a', 256),
        ];

        $response = actingAs($user)->putJson("/api/v1/warehouses/{$warehouse->id}", $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('returns 401 for unauthenticated request', function () {
        $warehouse = Warehouse::factory()->create();

        $response = putJson("/api/v1/warehouses/{$warehouse->id}", [
            'name' => 'Test',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $response = actingAs($user)->putJson("/api/v1/warehouses/{$warehouse->id}", [
            'name' => 'Test',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent warehouse', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesUpdate->value);

        $response = actingAs($user)->putJson('/api/v1/warehouses/999999', [
            'name' => 'Test',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns updated warehouse resource', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesUpdate->value);

        $warehouse = Warehouse::factory()->create();

        $response = actingAs($user)->putJson("/api/v1/warehouses/{$warehouse->id}", [
            'name' => 'Updated Depot',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
