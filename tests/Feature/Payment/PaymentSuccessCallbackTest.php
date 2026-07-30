<?php

use HasinHayder\Sslcommerz\Facades\Sslcommerz;

use function Pest\Laravel\postJson;

describe('Payment Success Callback', function () {
    it('does not require authentication', function () {
        Sslcommerz::shouldReceive('verifyHash')
            ->once()
            ->andReturn(false);

        $response = postJson('/api/v1/payment/success', []);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('returns 422 for invalid hash', function () {
        Sslcommerz::shouldReceive('verifyHash')
            ->once()
            ->andReturn(false);

        $response = postJson('/api/v1/payment/success', [
            'tran_id' => 'TXN123',
            'amount' => '100.00',
        ]);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('returns 422 for invalid payment data', function () {
        Sslcommerz::shouldReceive('verifyHash')
            ->once()
            ->andReturn(true);

        Sslcommerz::shouldReceive('validatePayment')
            ->once()
            ->andReturn(false);

        $response = postJson('/api/v1/payment/success', [
            'tran_id' => 'TXN123',
            'amount' => '100.00',
        ]);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });
});
