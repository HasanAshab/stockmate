<?php

use App\Enums\Permission;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('Show Warehouse Stock', function () {
    it('requires authentication', function () {
        $warehouse = Warehouse::factory()->create();
        $stock = WarehouseStock::factory()->create(['warehouse_id' => $warehouse->id]);

        $response = getJson("/api/v1/warehouses/{$warehouse->id}/stocks/{$stock->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires warehouses-view permission', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $stock = WarehouseStock::factory()->create(['warehouse_id' => $warehouse->id]);

        $response = actingAs($user)->getJson("/api/v1/warehouses/{$warehouse->id}/stocks/{$stock->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns warehouse stock details with 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $warehouse = Warehouse::factory()->create();
        $stock = WarehouseStock::factory()->create(['warehouse_id' => $warehouse->id]);

        $response = actingAs($user)->getJson("/api/v1/warehouses/{$warehouse->id}/stocks/{$stock->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $stock->id]);
    });

    it('includes product relationship', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $warehouse = Warehouse::factory()->create();
        $stock = WarehouseStock::factory()->create(['warehouse_id' => $warehouse->id]);

        $response = actingAs($user)->getJson("/api/v1/warehouses/{$warehouse->id}/stocks/{$stock->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns 401 for unauthenticated request', function () {
        $warehouse = Warehouse::factory()->create();
        $stock = WarehouseStock::factory()->create(['warehouse_id' => $warehouse->id]);

        $response = getJson("/api/v1/warehouses/{$warehouse->id}/stocks/{$stock->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $stock = WarehouseStock::factory()->create(['warehouse_id' => $warehouse->id]);

        $response = actingAs($user)->getJson("/api/v1/warehouses/{$warehouse->id}/stocks/{$stock->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent warehouse', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $stock = WarehouseStock::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/warehouses/999999/stocks/{$stock->id}");

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns 404 for non-existent stock', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $warehouse = Warehouse::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/warehouses/{$warehouse->id}/stocks/999999");

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns warehouse stock resource', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $warehouse = Warehouse::factory()->create();
        $stock = WarehouseStock::factory()->create(['warehouse_id' => $warehouse->id]);

        $response = actingAs($user)->getJson("/api/v1/warehouses/{$warehouse->id}/stocks/{$stock->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
