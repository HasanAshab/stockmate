<?php

use App\Enums\Permission;
use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('Receive Purchase Order', function () {
    it('requires authentication', function () {
        $purchaseOrder = PurchaseOrder::factory()->ordered()->create();

        $response = postJson("/api/v1/purchase-orders/{$purchaseOrder->id}/receive", []);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires purchase-orders-receive permission', function () {
        $user = User::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->ordered()->create();

        $response = actingAs($user)->postJson("/api/v1/purchase-orders/{$purchaseOrder->id}/receive", []);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('receives pending purchase order and returns 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersReceive->value);

        $purchaseOrder = PurchaseOrder::factory()->ordered()->create();
        $item = PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'ordered_quantity' => 20,
            'received_quantity' => 0,
        ]);

        $payload = [
            'items' => [
                [
                    'purchase_order_item_id' => $item->id,
                    'quantity_received' => 20,
                ],
            ],
        ];

        $response = actingAs($user)->postJson("/api/v1/purchase-orders/{$purchaseOrder->id}/receive", $payload);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('sets status to received', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersReceive->value);

        $purchaseOrder = PurchaseOrder::factory()->ordered()->create();
        $item = PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'ordered_quantity' => 10,
            'received_quantity' => 0,
        ]);

        $payload = [
            'items' => [
                [
                    'purchase_order_item_id' => $item->id,
                    'quantity_received' => 10,
                ],
            ],
        ];

        actingAs($user)->postJson("/api/v1/purchase-orders/{$purchaseOrder->id}/receive", $payload);

        $purchaseOrder->refresh();
        expect($purchaseOrder->status)->toBe(PurchaseOrderStatus::Received);
    });

    it('returns 403 when order is already received', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersReceive->value);

        $purchaseOrder = PurchaseOrder::factory()->received()->create();

        $response = actingAs($user)->postJson("/api/v1/purchase-orders/{$purchaseOrder->id}/receive", [
            'items' => [],
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('validates items payload', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersReceive->value);

        $purchaseOrder = PurchaseOrder::factory()->ordered()->create();

        $response = actingAs($user)->postJson("/api/v1/purchase-orders/{$purchaseOrder->id}/receive", []);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('returns 401 for unauthenticated request', function () {
        $purchaseOrder = PurchaseOrder::factory()->ordered()->create();

        $response = postJson("/api/v1/purchase-orders/{$purchaseOrder->id}/receive", []);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->ordered()->create();

        $response = actingAs($user)->postJson("/api/v1/purchase-orders/{$purchaseOrder->id}/receive", []);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent purchase order', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersReceive->value);

        $response = actingAs($user)->postJson('/api/v1/purchase-orders/999999/receive', []);

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns received purchase order resource', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersReceive->value);

        $purchaseOrder = PurchaseOrder::factory()->ordered()->create();
        $item = PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'ordered_quantity' => 5,
            'received_quantity' => 0,
        ]);

        $payload = [
            'items' => [
                [
                    'purchase_order_item_id' => $item->id,
                    'quantity_received' => 5,
                ],
            ],
        ];

        $response = actingAs($user)->postJson("/api/v1/purchase-orders/{$purchaseOrder->id}/receive", $payload);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
