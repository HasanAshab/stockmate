<?php

use App\Enums\Permission;
use App\Enums\SalesOrderStatus;
use App\Models\SalesOrder;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patchJson;

describe('Cancel Sales Order', function () {
    it('requires authentication', function () {
        $salesOrder = SalesOrder::factory()->pending()->create();

        $response = patchJson("/api/v1/sales-orders/{$salesOrder->id}/cancel");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires sales-orders-cancel permission', function () {
        $user = User::factory()->create();
        $salesOrder = SalesOrder::factory()->pending()->create();

        $response = actingAs($user)->patchJson("/api/v1/sales-orders/{$salesOrder->id}/cancel");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('cancels pending sales order and returns 204', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCancel->value);

        $salesOrder = SalesOrder::factory()->pending()->create();

        $response = actingAs($user)->patchJson("/api/v1/sales-orders/{$salesOrder->id}/cancel");

        $response->assertValidRequest()
            ->assertValidResponse(204);
    });

    it('sets status to cancelled', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCancel->value);

        $salesOrder = SalesOrder::factory()->pending()->create();

        actingAs($user)->patchJson("/api/v1/sales-orders/{$salesOrder->id}/cancel");

        $salesOrder->refresh();
        expect($salesOrder->status)->toBe(SalesOrderStatus::Cancelled);
    });

    it('returns 403 when sales order is paid', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCancel->value);

        $salesOrder = SalesOrder::factory()->paid()->create();

        $response = actingAs($user)->patchJson("/api/v1/sales-orders/{$salesOrder->id}/cancel");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 403 when sales order is completed', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCancel->value);

        $salesOrder = SalesOrder::factory()->failed()->create();

        $response = actingAs($user)->patchJson("/api/v1/sales-orders/{$salesOrder->id}/cancel");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 403 when sales order is already cancelled', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCancel->value);

        $salesOrder = SalesOrder::factory()->cancelled()->create();

        $response = actingAs($user)->patchJson("/api/v1/sales-orders/{$salesOrder->id}/cancel");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent sales order', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCancel->value);

        $response = actingAs($user)->patchJson('/api/v1/sales-orders/999999/cancel');

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });
});
