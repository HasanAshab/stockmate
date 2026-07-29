<?php

use function Pest\Laravel\postJson;

describe('Reset Password', function () {
    it('resets password with valid OTP and returns 200', function () {
        $this->markTestSkipped('Requires OTP generation and verification setup');
    });

    it('returns validation errors for invalid input', function () {
        $response = postJson('/api/v1/auth/reset-password', [
            'identifier' => '',
            'code' => '',
            'password' => '',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('returns 400 for invalid OTP code', function () {
        $this->markTestSkipped('Requires OTP verification setup');
    });

    it('returns 400 for expired OTP code', function () {
        $this->markTestSkipped('Requires OTP verification setup');
    });
});
