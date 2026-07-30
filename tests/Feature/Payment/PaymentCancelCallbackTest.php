<?php

use function Pest\Laravel\postJson;

describe('Payment Cancel Callback', function () {
    it('does not require authentication', function () {
        $response = postJson('/api/v1/payment/cancel', []);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('processes cancelled payment callback and returns 200', function () {
        $response = postJson('/api/v1/payment/cancel', [
            'tran_id' => 'TXN-CANCEL-123',
            'status' => 'CANCELLED',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
