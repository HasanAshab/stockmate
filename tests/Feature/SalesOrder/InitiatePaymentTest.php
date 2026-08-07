<?php

use App\Enums\Permission;
use App\Models\SalesOrder;
use App\Models\User;
use HasinHayder\Sslcommerz\Data\PaymentResponse;
use HasinHayder\Sslcommerz\Facades\Sslcommerz;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('Initiate Payment', function () {
    it('requires authentication', function () {
        $salesOrder = SalesOrder::factory()->pending()->create();

        $response = postJson("/api/v1/sales-orders/{$salesOrder->id}/initiate-payment");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires sales-orders-initiate-payment permission', function () {
        $user = User::factory()->create();
        $salesOrder = SalesOrder::factory()->pending()->create();

        $response = actingAs($user)->postJson("/api/v1/sales-orders/{$salesOrder->id}/initiate-payment");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('initiates payment for pending sales order and returns 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersInitiatePayment->value);

        $salesOrder = SalesOrder::factory()->pending()->create([
            'customer_name' => 'Jane Smith',
            'customer_email' => 'jane@example.com',
            'customer_phone' => '+8801712345678',
            'total_amount' => 1500.00,
        ]);

        $sslResponseMock = Mockery::mock(PaymentResponse::class);
        $sslResponseMock->shouldReceive('gatewayPageURL')->andReturn('https://sandbox.sslcommerz.com/easycheckout/test12345');

        Sslcommerz::shouldReceive('setOrder')->once()->andReturnSelf();
        Sslcommerz::shouldReceive('setCustomer')->once()->andReturnSelf();
        Sslcommerz::shouldReceive('makePayment')->once()->andReturn($sslResponseMock);

        $response = actingAs($user)->postJson("/api/v1/sales-orders/{$salesOrder->id}/initiate-payment");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment([
                'payment_url' => 'https://sandbox.sslcommerz.com/easycheckout/test12345',
            ]);
    });

    it('returns payment gateway URL', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersInitiatePayment->value);

        $salesOrder = SalesOrder::factory()->pending()->create();

        $sslResponseMock = Mockery::mock(PaymentResponse::class);
        $sslResponseMock->shouldReceive('gatewayPageURL')->andReturn('https://sandbox.sslcommerz.com/easycheckout/gw123');

        Sslcommerz::shouldReceive('setOrder')->once()->andReturnSelf();
        Sslcommerz::shouldReceive('setCustomer')->once()->andReturnSelf();
        Sslcommerz::shouldReceive('makePayment')->once()->andReturn($sslResponseMock);

        $response = actingAs($user)->postJson("/api/v1/sales-orders/{$salesOrder->id}/initiate-payment");

        $response->assertValidRequest()
            ->assertValidResponse(200);

        expect($response->json('data.payment_url'))->toBe('https://sandbox.sslcommerz.com/easycheckout/gw123');
    });

    it('updates order with payment details', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersInitiatePayment->value);

        $salesOrder = SalesOrder::factory()->pending()->create();

        $sslResponseMock = Mockery::mock(PaymentResponse::class);
        $sslResponseMock->shouldReceive('gatewayPageURL')->andReturn('https://sandbox.sslcommerz.com/easycheckout/gw123');

        Sslcommerz::shouldReceive('setOrder')->once()->andReturnSelf();
        Sslcommerz::shouldReceive('setCustomer')->once()->andReturnSelf();
        Sslcommerz::shouldReceive('makePayment')->once()->andReturn($sslResponseMock);

        actingAs($user)->postJson("/api/v1/sales-orders/{$salesOrder->id}/initiate-payment");

        $salesOrder->refresh();
        expect($salesOrder->transaction_reference)->not->toBeNull();
    });

    it('returns 403 when sales order is not pending', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersInitiatePayment->value);

        $salesOrder = SalesOrder::factory()->cancelled()->create();

        $response = actingAs($user)->postJson("/api/v1/sales-orders/{$salesOrder->id}/initiate-payment");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 403 when sales order is already paid', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersInitiatePayment->value);

        $salesOrder = SalesOrder::factory()->paid()->create();

        $response = actingAs($user)->postJson("/api/v1/sales-orders/{$salesOrder->id}/initiate-payment");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent sales order', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersInitiatePayment->value);

        $response = actingAs($user)->postJson('/api/v1/sales-orders/999999/initiate-payment');

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });
});
