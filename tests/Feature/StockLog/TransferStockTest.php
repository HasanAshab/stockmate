<?php

use App\Enums\Permission;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('Transfer Stock', function () {
    it('requires authentication', function () {
        $response = postJson('/api/v1/stock-logs/transfer', []);

        $response->assertValidResponse(401);
    });

    it('requires warehouses-create permission', function () {
        $user = User::factory()->create();
        $fromWarehouse = Warehouse::factory()->create();
        $toWarehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        WarehouseStock::factory()->create([
            'warehouse_id' => $fromWarehouse->id,
            'product_id' => $product->id,
            'quantity' => 50,
        ]);

        $payload = [
            'from_warehouse_id' => $fromWarehouse->id,
            'to_warehouse_id' => $toWarehouse->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'note' => 'Inter-warehouse transfer',
        ];


        $response = actingAs($user)->postJson('/api/v1/stock-logs/transfer', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('transfers stock between warehouses and returns 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesCreate->value);

        $fromWarehouse = Warehouse::factory()->create();
        $toWarehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        WarehouseStock::factory()->create([
            'warehouse_id' => $fromWarehouse->id,
            'product_id' => $product->id,
            'quantity' => 50,
        ]);

        $payload = [
            'from_warehouse_id' => $fromWarehouse->id,
            'to_warehouse_id' => $toWarehouse->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'note' => 'Inter-warehouse transfer',
        ];

        $response = actingAs($user)->postJson('/api/v1/stock-logs/transfer', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('validates required from_warehouse_id', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesCreate->value);

        $toWarehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'to_warehouse_id' => $toWarehouse->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ];

        $response = actingAs($user)->postJson('/api/v1/stock-logs/transfer', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates required to_warehouse_id', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesCreate->value);

        $fromWarehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'from_warehouse_id' => $fromWarehouse->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ];

        $response = actingAs($user)->postJson('/api/v1/stock-logs/transfer', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates required product_id', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesCreate->value);

        $fromWarehouse = Warehouse::factory()->create();
        $toWarehouse = Warehouse::factory()->create();

        $payload = [
            'from_warehouse_id' => $fromWarehouse->id,
            'to_warehouse_id' => $toWarehouse->id,
            'quantity' => 10,
        ];

        $response = actingAs($user)->postJson('/api/v1/stock-logs/transfer', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates required quantity', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesCreate->value);

        $fromWarehouse = Warehouse::factory()->create();
        $toWarehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'from_warehouse_id' => $fromWarehouse->id,
            'to_warehouse_id' => $toWarehouse->id,
            'product_id' => $product->id,
        ];

        $response = actingAs($user)->postJson('/api/v1/stock-logs/transfer', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates quantity is positive', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesCreate->value);

        $fromWarehouse = Warehouse::factory()->create();
        $toWarehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'from_warehouse_id' => $fromWarehouse->id,
            'to_warehouse_id' => $toWarehouse->id,
            'product_id' => $product->id,
            'quantity' => 0,
        ];

        $response = actingAs($user)->postJson('/api/v1/stock-logs/transfer', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates warehouses are different', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesCreate->value);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'from_warehouse_id' => $warehouse->id,
            'to_warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ];

        $response = actingAs($user)->postJson('/api/v1/stock-logs/transfer', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });
});
