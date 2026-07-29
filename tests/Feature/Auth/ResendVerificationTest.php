<?php

use function Pest\Laravel\postJson;

describe('Resend Verification', function () {
    it('resends verification code to email and returns 200', function () {
        $this->markTestSkipped('Requires notification/OTP setup');
    });

    it('resends verification code to phone and returns 200', function () {
        $this->markTestSkipped('Requires notification/OTP setup');
    });

    it('returns validation errors for invalid input', function () {
        $response = postJson('/api/v1/auth/verification-notification', [
            'identifier' => '',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('silently succeeds for non-existent user', function () {
        $response = postJson('/api/v1/auth/verification-notification', [
            'identifier' => 'nonexistent@example.com',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('silently succeeds for already verified user', function () {
        $this->markTestSkipped('Requires verification status check setup');
    });

    it('creates new OTP and sends notification', function () {
        $this->markTestSkipped('Requires OTP and notification verification setup');
    });
});
