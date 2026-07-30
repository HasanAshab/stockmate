<?php

use App\Enums\Permission;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('List Warehouse Stocks', function () {
    it('requires authentication', function () {
        $warehouse = Warehouse::factory()->create();

        $response = getJson("/api/v1/warehouses/{$warehouse->id}/stocks");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires warehouses-view permission', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/warehouses/{$warehouse->id}/stocks");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns paginated list of warehouse stocks with 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $warehouse = Warehouse::factory()->create();
        WarehouseStock::factory()->count(3)->create(['warehouse_id' => $warehouse->id]);

        $response = actingAs($user)->getJson("/api/v1/warehouses/{$warehouse->id}/stocks");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('filters stocks by product ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $stock = WarehouseStock::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
        ]);

        $response = actingAs($user)->getJson("/api/v1/warehouses/{$warehouse->id}/stocks?filter[product]={$product->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $stock->id]);
    });

    it('filters stocks by warehouse ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $warehouse = Warehouse::factory()->create();
        $stock = WarehouseStock::factory()->create(['warehouse_id' => $warehouse->id]);

        $response = actingAs($user)->getJson("/api/v1/warehouses/{$warehouse->id}/stocks?filter[warehouse]={$warehouse->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $stock->id]);
    });

    it('filters low stock items', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $warehouse = Warehouse::factory()->create();
        $lowStock = WarehouseStock::factory()->low()->create(['warehouse_id' => $warehouse->id]);

        $response = actingAs($user)->getJson("/api/v1/warehouses/{$warehouse->id}/stocks?filter[low_stock]=true");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('sorts stocks by quantity ascending', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $warehouse = Warehouse::factory()->create();
        WarehouseStock::factory()->create(['warehouse_id' => $warehouse->id, 'quantity' => 10]);
        WarehouseStock::factory()->create(['warehouse_id' => $warehouse->id, 'quantity' => 100]);

        $response = actingAs($user)->getJson("/api/v1/warehouses/{$warehouse->id}/stocks?sort=quantity");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('includes product.category relationship', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $warehouse = Warehouse::factory()->create();
        WarehouseStock::factory()->create(['warehouse_id' => $warehouse->id]);

        $response = actingAs($user)->getJson("/api/v1/warehouses/{$warehouse->id}/stocks?include=product.category");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns 401 for unauthenticated request', function () {
        $warehouse = Warehouse::factory()->create();

        $response = getJson("/api/v1/warehouses/{$warehouse->id}/stocks");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/warehouses/{$warehouse->id}/stocks");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent warehouse', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $response = actingAs($user)->getJson('/api/v1/warehouses/999999/stocks');

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns warehouse stock resource collection', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $warehouse = Warehouse::factory()->create();
        WarehouseStock::factory()->create(['warehouse_id' => $warehouse->id]);

        $response = actingAs($user)->getJson("/api/v1/warehouses/{$warehouse->id}/stocks");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
