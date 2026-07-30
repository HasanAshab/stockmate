<?php

use App\Enums\Permission;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\putJson;

describe('Update Warehouse Stock', function () {
    it('requires authentication', function () {
        $warehouse = Warehouse::factory()->create();
        $stock = WarehouseStock::factory()->create(['warehouse_id' => $warehouse->id]);

        $response = putJson("/api/v1/warehouses/{$warehouse->id}/stocks/{$stock->id}", []);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires warehouses-view permission', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $stock = WarehouseStock::factory()->create(['warehouse_id' => $warehouse->id]);

        $response = actingAs($user)->putJson("/api/v1/warehouses/{$warehouse->id}/stocks/{$stock->id}", []);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('updates warehouse stock reorder_threshold and returns 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $warehouse = Warehouse::factory()->create();
        $stock = WarehouseStock::factory()->create([
            'warehouse_id' => $warehouse->id,
            'reorder_threshold' => 10,
        ]);

        $payload = [
            'reorder_threshold' => 25,
        ];

        $response = actingAs($user)->putJson("/api/v1/warehouses/{$warehouse->id}/stocks/{$stock->id}", $payload);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment([
                'reorder_threshold' => 25,
            ]);

        $this->assertDatabaseHas('warehouse_stocks', [
            'id' => $stock->id,
            'reorder_threshold' => 25,
        ]);
    });

    it('validates required reorder_threshold field', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $warehouse = Warehouse::factory()->create();
        $stock = WarehouseStock::factory()->create(['warehouse_id' => $warehouse->id]);

        $response = actingAs($user)->putJson("/api/v1/warehouses/{$warehouse->id}/stocks/{$stock->id}", []);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates reorder_threshold is non-negative integer', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $warehouse = Warehouse::factory()->create();
        $stock = WarehouseStock::factory()->create(['warehouse_id' => $warehouse->id]);

        $payload = [
            'reorder_threshold' => -5,
        ];

        $response = actingAs($user)->putJson("/api/v1/warehouses/{$warehouse->id}/stocks/{$stock->id}", $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('returns 401 for unauthenticated request', function () {
        $warehouse = Warehouse::factory()->create();
        $stock = WarehouseStock::factory()->create(['warehouse_id' => $warehouse->id]);

        $response = putJson("/api/v1/warehouses/{$warehouse->id}/stocks/{$stock->id}", [
            'reorder_threshold' => 10,
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $stock = WarehouseStock::factory()->create(['warehouse_id' => $warehouse->id]);

        $response = actingAs($user)->putJson("/api/v1/warehouses/{$warehouse->id}/stocks/{$stock->id}", [
            'reorder_threshold' => 10,
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent warehouse', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $stock = WarehouseStock::factory()->create();

        $response = actingAs($user)->putJson("/api/v1/warehouses/999999/stocks/{$stock->id}", [
            'reorder_threshold' => 10,
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns 404 for non-existent stock', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $warehouse = Warehouse::factory()->create();

        $response = actingAs($user)->putJson("/api/v1/warehouses/{$warehouse->id}/stocks/999999", [
            'reorder_threshold' => 10,
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns updated warehouse stock resource', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $warehouse = Warehouse::factory()->create();
        $stock = WarehouseStock::factory()->create(['warehouse_id' => $warehouse->id]);

        $response = actingAs($user)->putJson("/api/v1/warehouses/{$warehouse->id}/stocks/{$stock->id}", [
            'reorder_threshold' => 15,
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
