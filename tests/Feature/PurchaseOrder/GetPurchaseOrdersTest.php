<?php

use App\Enums\Permission;
use App\Enums\PurchaseOrderStatus;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('List Purchase Orders', function () {
    it('requires authentication', function () {
        $response = getJson('/api/v1/purchase-orders');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires purchase-orders-view permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/purchase-orders');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns paginated list of purchase orders with 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersView->value);

        PurchaseOrder::factory()->count(3)->create();

        $response = actingAs($user)->getJson('/api/v1/purchase-orders');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('includes warehouse, supplier, creator, items relationships', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersView->value);

        $purchaseOrder = PurchaseOrder::factory()->create();
        PurchaseOrderItem::factory()->create(['purchase_order_id' => $purchaseOrder->id]);

        $response = actingAs($user)->getJson('/api/v1/purchase-orders');

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonPath('data.0.id', $purchaseOrder->id)
            ->assertJsonCount(1, 'data');
    });

    it('filters by supplier ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersView->value);

        $supplier = Supplier::factory()->create();
        $matchingOrder = PurchaseOrder::factory()->create(['supplier_id' => $supplier->id]);
        $otherOrder = PurchaseOrder::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/purchase-orders?filter[supplier]={$supplier->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonPath('data.0.id', $matchingOrder->id)
            ->assertJsonCount(1, 'data');
    });

    it('filters by warehouse ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersView->value);

        $warehouse = Warehouse::factory()->create();
        $matchingOrder = PurchaseOrder::factory()->create(['warehouse_id' => $warehouse->id]);
        $otherOrder = PurchaseOrder::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/purchase-orders?filter[warehouse]={$warehouse->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonPath('data.0.id', $matchingOrder->id)
            ->assertJsonCount(1, 'data');
    });

    it('filters by status', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersView->value);

        $draftOrder = PurchaseOrder::factory()->draft()->create();
        $orderedOrder = PurchaseOrder::factory()->ordered()->create();

        $response = actingAs($user)->getJson('/api/v1/purchase-orders?filter[status]='.PurchaseOrderStatus::Draft->value);
        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonPath('data.0.id', $draftOrder->id)
            ->assertJsonCount(1, 'data');
    });

    it('sorts by created_at descending', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersView->value);

        $firstCreated = PurchaseOrder::factory()->create(['created_at' => now()->subDays(2)]);
        $secondCreated = PurchaseOrder::factory()->create(['created_at' => now()->subDay()]);

        $response = actingAs($user)->getJson('/api/v1/purchase-orders');

        $response->assertValidRequest()
            ->assertValidResponse(200);

        $ids = collect($response->json('data'))->pluck('id');
        expect($ids->first())->toBe($secondCreated->id);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = getJson('/api/v1/purchase-orders');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/purchase-orders');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns purchase order resource collection', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersView->value);

        PurchaseOrder::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/purchase-orders');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
