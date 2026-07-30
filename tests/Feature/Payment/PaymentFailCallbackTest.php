<?php

use function Pest\Laravel\postJson;

describe('Payment Fail Callback', function () {
    it('does not require authentication', function () {
        $response = postJson('/api/v1/payment/fail', []);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('processes failed payment callback and returns 200', function () {
        $response = postJson('/api/v1/payment/fail', [
            'tran_id' => 'TXN-FAIL-123',
            'status' => 'FAILED',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
