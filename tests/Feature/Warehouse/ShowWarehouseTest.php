<?php

use App\Enums\Permission;
use App\Models\User;
use App\Models\Warehouse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('Show Warehouse', function () {
    it('requires authentication', function () {
        $warehouse = Warehouse::factory()->create();

        $response = getJson("/api/v1/warehouses/{$warehouse->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires warehouses-view permission', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/warehouses/{$warehouse->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns warehouse details with 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $warehouse = Warehouse::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/warehouses/{$warehouse->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $warehouse->id]);
    });

    it('returns 401 for unauthenticated request', function () {
        $warehouse = Warehouse::factory()->create();

        $response = getJson("/api/v1/warehouses/{$warehouse->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/warehouses/{$warehouse->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent warehouse', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $response = actingAs($user)->getJson('/api/v1/warehouses/999999');

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns warehouse resource', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $warehouse = Warehouse::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/warehouses/{$warehouse->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
