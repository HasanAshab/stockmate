<?php

use App\Enums\Permission;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;

describe('Delete Warehouse', function () {
    it('requires authentication', function () {
        $warehouse = Warehouse::factory()->create();

        $response = deleteJson("/api/v1/warehouses/{$warehouse->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires warehouses-delete permission', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $response = actingAs($user)->deleteJson("/api/v1/warehouses/{$warehouse->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('deletes empty warehouse and returns 204', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesDelete->value);

        $warehouse = Warehouse::factory()->create();

        $response = actingAs($user)->deleteJson("/api/v1/warehouses/{$warehouse->id}");

        $response->assertValidRequest()
            ->assertValidResponse(204);

        $this->assertDatabaseMissing('warehouses', [
            'id' => $warehouse->id,
        ]);
    });

    it('returns 400 when warehouse has stock', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesDelete->value);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        WarehouseStock::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $response = actingAs($user)->deleteJson("/api/v1/warehouses/{$warehouse->id}");

        $response->assertValidRequest()
            ->assertValidResponse(400);
    });

    it('returns 401 for unauthenticated request', function () {
        $warehouse = Warehouse::factory()->create();

        $response = deleteJson("/api/v1/warehouses/{$warehouse->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $response = actingAs($user)->deleteJson("/api/v1/warehouses/{$warehouse->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent warehouse', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesDelete->value);

        $response = actingAs($user)->deleteJson('/api/v1/warehouses/999999');

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });
});
