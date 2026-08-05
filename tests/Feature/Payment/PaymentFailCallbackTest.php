<?php

use App\Enums\SalesOrderStatus;
use App\Models\SalesOrder;
use function Pest\Laravel\postJson;

describe('Payment Fail Callback', function () {
    it('processes failed payment callback and returns 200', function () {
        $tranId = 'TXN-FAILED-123';
        $salesOrder = SalesOrder::factory()->pending()->create([
            'transaction_reference' => $tranId,
        ]);

        $response = postJson('/api/v1/payment/fail', [
            'tran_id' => $tranId,
            'status' => "FAILED",
            'amount' => $salesOrder->total_amount,
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);

        $salesOrder->refresh();
        expect($salesOrder->status)->toBe(SalesOrderStatus::Failed);
    });

    it('ignores unexisting payment callback and returns 404', function () {
        $salesOrder = SalesOrder::factory()->pending()->create([
            'transaction_reference' => 'TXN-FAILED-123',
        ]);

        $response = postJson('/api/v1/payment/cancel', [
            'tran_id' => 'TXN-WRONG-999',
            'status' => "FAILED",
            'amount' => $salesOrder->total_amount,
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(404);

        $salesOrder->refresh();
        expect($salesOrder->status)->toBe(SalesOrderStatus::Pending);
    });
});
