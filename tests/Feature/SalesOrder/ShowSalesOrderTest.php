<?php

use App\Enums\Permission;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use App\Models\Warehouse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('Show Sales Order', function () {
    it('requires authentication', function () {
        $salesOrder = SalesOrder::factory()->create();

        $response = getJson("/api/v1/sales-orders/{$salesOrder->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires sales-orders-view permission', function () {
        $user = User::factory()->create();
        $salesOrder = SalesOrder::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/sales-orders/{$salesOrder->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns sales order details with 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersView->value);

        $salesOrder = SalesOrder::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/sales-orders/{$salesOrder->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $salesOrder->id]);
    });

    it('loads warehouse, creator, and items.product relationships', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersView->value);

        $warehouse = Warehouse::factory()->create();
        $creator = User::factory()->create();
        $product = Product::factory()->create();

        $salesOrder = SalesOrder::factory()->create([
            'warehouse_id' => $warehouse->id,
            'creator_id' => $creator->id,
        ]);

        SalesOrderItem::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'product_id' => $product->id,
        ]);

        $response = actingAs($user)->getJson("/api/v1/sales-orders/{$salesOrder->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['warehouse_id' => $warehouse->id])
            ->assertJsonFragment(['creator_id' => $creator->id]);
    });

    it('returns 404 for non-existent sales order', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersView->value);

        $response = actingAs($user)->getJson('/api/v1/sales-orders/999999');

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns sales order resource', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersView->value);

        $salesOrder = SalesOrder::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/sales-orders/{$salesOrder->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
