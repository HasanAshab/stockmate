<?php

use App\Enums\Permission;
use App\Enums\SalesOrderStatus;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use App\Models\Warehouse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('List Sales Orders', function () {
    it('requires authentication', function () {
        $response = getJson('/api/v1/sales-orders');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires sales-orders-view permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/sales-orders');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns paginated list of sales orders with 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersView->value);

        SalesOrder::factory()->count(3)->create();

        $response = actingAs($user)->getJson('/api/v1/sales-orders');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('includes warehouse, creator, and items relationships', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersView->value);

        $salesOrder = SalesOrder::factory()->create();
        SalesOrderItem::factory()->create(['sales_order_id' => $salesOrder->id]);

        $response = actingAs($user)->getJson('/api/v1/sales-orders');

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonPath('data.0.id', $salesOrder->id)
            ->assertJsonCount(1, 'data');
    });

    it('filters sales orders by creator ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersView->value);

        $creator = User::factory()->create();
        $matchingOrder = SalesOrder::factory()->create(['creator_id' => $creator->id]);
        $otherOrder = SalesOrder::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/sales-orders?filter[creator]={$creator->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonPath('data.0.id', $matchingOrder->id)
            ->assertJsonCount(1, 'data');
    });

    it('filters sales orders by warehouse ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersView->value);

        $warehouse = Warehouse::factory()->create();
        $matchingOrder = SalesOrder::factory()->create(['warehouse_id' => $warehouse->id]);
        $otherOrder = SalesOrder::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/sales-orders?filter[warehouse]={$warehouse->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonPath('data.0.id', $matchingOrder->id)
            ->assertJsonCount(1, 'data');
    });

    it('filters sales orders by product ID in items', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersView->value);

        $product = Product::factory()->create();
        $matchingOrder = SalesOrder::factory()->create();
        SalesOrderItem::factory()->create([
            'sales_order_id' => $matchingOrder->id,
            'product_id' => $product->id,
        ]);

        $otherOrder = SalesOrder::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/sales-orders?filter[product]={$product->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonPath('data.0.id', $matchingOrder->id)
            ->assertJsonCount(1, 'data');
    });

    it('filters sales orders by status', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersView->value);

        $pendingOrder = SalesOrder::factory()->pending()->create();
        $paidOrder = SalesOrder::factory()->paid()->create();

        $response = actingAs($user)->getJson('/api/v1/sales-orders?filter[status]='.SalesOrderStatus::Pending->value);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonPath('data.0.id', $pendingOrder->id)
            ->assertJsonCount(1, 'data');
    });

    it('filters sales orders by created_at date range', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersView->value);

        $oldOrder = SalesOrder::factory()->create(['created_at' => now()->subDays(10)]);
        $recentOrder = SalesOrder::factory()->create(['created_at' => now()->subDay()]);

        $startDate = now()->subDays(2)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = actingAs($user)->getJson("/api/v1/sales-orders?filter[created_at][from]={$startDate}&filter[created_at][from]={$endDate}");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonPath('data.0.id', $recentOrder->id)
            ->assertJsonCount(1, 'data');
    });

    it('filters sales orders by total_amount range', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersView->value);

        $smallOrder = SalesOrder::factory()->create(['total_amount' => 50.00]);
        $largeOrder = SalesOrder::factory()->create(['total_amount' => 500.00]);

        $response = actingAs($user)->getJson('/api/v1/sales-orders?filter[total_amount]=>100');

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $largeOrder->id])
            ->assertJsonMissing(['id' => $smallOrder->id]);
    });

    it('sorts sales orders by created_at descending', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersView->value);

        $firstCreated = SalesOrder::factory()->create(['created_at' => now()->subDays(2)]);
        $secondCreated = SalesOrder::factory()->create(['created_at' => now()->subDay()]);

        $response = actingAs($user)->getJson('/api/v1/sales-orders');

        $response->assertValidRequest()
            ->assertValidResponse(200);

        $ids = collect($response->json('data'))->pluck('id');
        expect($ids->first())->toBe($secondCreated->id);
    });

    it('returns sales order resource collection', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersView->value);

        SalesOrder::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/sales-orders');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
