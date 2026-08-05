<?php

use HasinHayder\Sslcommerz\Facades\Sslcommerz;

use function Pest\Laravel\postJson;

describe('Payment IPN Callback', function () {
    it('returns 422 for invalid hash', function () {
        Sslcommerz::shouldReceive('verifyHash')
            ->once()
            ->andReturn(false);

        $response = postJson('/api/v1/payment/ipn', [
            'status' => 'VALID',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('processes IPN callback and returns 200', function () {
        Sslcommerz::shouldReceive('verifyHash')
            ->once()
            ->andReturn(true);

        $response = postJson('/api/v1/payment/ipn', [
            'status' => 'FAILED',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
