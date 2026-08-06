<?php

use App\Enums\Permission;
use App\Models\PurchaseOrder;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\putJson;

describe('Update Purchase Order', function () {
    it('requires authentication', function () {
        $purchaseOrder = PurchaseOrder::factory()->draft()->create();

        $response = putJson("/api/v1/purchase-orders/{$purchaseOrder->id}", []);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires purchase-orders-update permission', function () {
        $user = User::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->draft()->create();

        $response = actingAs($user)->putJson("/api/v1/purchase-orders/{$purchaseOrder->id}", []);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('updates purchase order and returns 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersUpdate->value);

        $purchaseOrder = PurchaseOrder::factory()->draft()->create([
            'note' => 'Original note',
        ]);

        $payload = [
            'note' => 'Updated order note',
        ];

        $response = actingAs($user)->putJson("/api/v1/purchase-orders/{$purchaseOrder->id}", $payload);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['note' => 'Updated order note']);

        $purchaseOrder->refresh();

        expect($purchaseOrder->note)->toBe('Updated order note');
    });

    it('returns 403 when order is received', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersUpdate->value);

        $purchaseOrder = PurchaseOrder::factory()->received()->create();

        $payload = [
            'note' => 'Cannot update received PO',
        ];

        $response = actingAs($user)->putJson("/api/v1/purchase-orders/{$purchaseOrder->id}", $payload);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('allows partial updates', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersUpdate->value);

        $purchaseOrder = PurchaseOrder::factory()->draft()->create([
            'note' => 'Old note',
        ]);

        $payload = [
            'note' => 'New partial note update',
        ];

        $response = actingAs($user)->putJson("/api/v1/purchase-orders/{$purchaseOrder->id}", $payload);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('validates supplier exists', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersUpdate->value);

        $purchaseOrder = PurchaseOrder::factory()->draft()->create();

        $payload = [
            'supplier_id' => 999999,
        ];

        $response = actingAs($user)->putJson("/api/v1/purchase-orders/{$purchaseOrder->id}", $payload);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('returns 404 for non-existent purchase order', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersUpdate->value);

        $response = actingAs($user)->putJson('/api/v1/purchase-orders/999999', [
            'note' => 'Test',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns updated purchase order resource', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersUpdate->value);

        $purchaseOrder = PurchaseOrder::factory()->draft()->create();

        $response = actingAs($user)->putJson("/api/v1/purchase-orders/{$purchaseOrder->id}", [
            'note' => 'Valid updated note',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
