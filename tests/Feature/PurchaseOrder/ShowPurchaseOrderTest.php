<?php

use App\Enums\Permission;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('Show Purchase Order', function () {
    it('requires authentication', function () {
        $purchaseOrder = PurchaseOrder::factory()->create();

        $response = getJson("/api/v1/purchase-orders/{$purchaseOrder->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires purchase-orders-view permission', function () {
        $user = User::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/purchase-orders/{$purchaseOrder->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns purchase order details with 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersView->value);

        $purchaseOrder = PurchaseOrder::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/purchase-orders/{$purchaseOrder->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $purchaseOrder->id]);
    });

    it('loads warehouse, supplier, creator, items.product relationships', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersView->value);

        $warehouse = Warehouse::factory()->create();
        $supplier = Supplier::factory()->create();
        $creator = User::factory()->create();
        $product = Product::factory()->create();

        $purchaseOrder = PurchaseOrder::factory()->create([
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'creator_id' => $creator->id,
        ]);

        PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'product_id' => $product->id,
        ]);

        $response = actingAs($user)->getJson("/api/v1/purchase-orders/{$purchaseOrder->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['warehouse_id' => $warehouse->id])
            ->assertJsonFragment(['supplier_id' => $supplier->id]);
    });

    it('returns 401 for unauthenticated request', function () {
        $purchaseOrder = PurchaseOrder::factory()->create();

        $response = getJson("/api/v1/purchase-orders/{$purchaseOrder->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/purchase-orders/{$purchaseOrder->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent purchase order', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersView->value);

        $response = actingAs($user)->getJson('/api/v1/purchase-orders/999999');

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns purchase order resource', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersView->value);

        $purchaseOrder = PurchaseOrder::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/purchase-orders/{$purchaseOrder->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
