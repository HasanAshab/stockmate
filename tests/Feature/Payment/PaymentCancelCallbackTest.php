<?php

use App\Enums\SalesOrderStatus;
use App\Models\SalesOrder;
use function Pest\Laravel\postJson;

describe('Payment Cancel Callback', function () {
    it('processes cancelled payment callback and returns 200', function () {
        $tranId = 'TXN-CANCEL-123';
        $salesOrder = SalesOrder::factory()->pending()->create([
            'transaction_reference' => $tranId,
        ]);

        $response = postJson('/api/v1/payment/cancel', [
            'tran_id' => $tranId,
            'status' => "CANCELLED",
            'amount' => $salesOrder->total_amount,
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);

        $salesOrder->refresh();
        expect($salesOrder->status)->toBe(SalesOrderStatus::Cancelled);
    });
    
    it('ignores unexisting payment callback and returns 404', function () {
        $salesOrder = SalesOrder::factory()->pending()->create([
            'transaction_reference' => 'TXN-CANCEL-123',
        ]);

        $response = postJson('/api/v1/payment/cancel', [
            'tran_id' => 'TXN-WRONG-999',
            'status' => "CANCELLED",
            'amount' => $salesOrder->total_amount,
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(404);

        $salesOrder->refresh();
        expect($salesOrder->status)->toBe(SalesOrderStatus::Pending);
    });
});
