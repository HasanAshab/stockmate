<?php

use function Pest\Laravel\postJson;

describe('Account Verification', function () {
    it('verifies user email with valid OTP code and returns 200', function () {
        $this->markTestSkipped('Requires OTP generation and verification setup');
    });

    it('verifies user phone with valid OTP code and returns 200', function () {
        $this->markTestSkipped('Requires OTP generation and verification setup');
    });

    it('returns validation errors for invalid input', function () {
        $response = postJson('/api/v1/auth/verify', [
            'identifier' => '',
            'code' => '',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('returns 400 for non-existent user', function () {
        $this->markTestSkipped('Requires OTP verification setup');
    });

    it('returns 400 for invalid OTP code', function () {
        $this->markTestSkipped('Requires OTP verification setup');
    });

    it('returns 400 for expired OTP code', function () {
        $this->markTestSkipped('Requires OTP verification setup');
    });
});
