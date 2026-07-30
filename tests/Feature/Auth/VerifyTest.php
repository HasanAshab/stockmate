<?php

use App\Models\User;

use function Pest\Laravel\postJson;

describe('Account Verification', function () {
    it('verifies user email with valid OTP code and returns 200', function () {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'email_verified_at' => null,
        ]);

        $otp = $user->createOneTimePassword()->password;

        $response = postJson('/api/v1/auth/verify', [
            'identifier' => 'john@example.com',
            'code' => $otp,
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);

        expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    });

    it('verifies user phone with valid OTP code and returns 200', function () {
        $user = User::factory()->create([
            'phone' => '+8801712345678',
            'phone_verified_at' => null,
        ]);

        $otp = $user->createOneTimePassword()->password;

        $response = postJson('/api/v1/auth/verify', [
            'identifier' => '+8801712345678',
            'code' => $otp,
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);

        expect($user->fresh()->phone_verified_at)->not->toBeNull();
    });

    it('returns validation errors for invalid input', function () {
        $response = postJson('/api/v1/auth/verify', [
            'identifier' => '',
            'code' => '',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('returns 422 for non-existent user', function () {
        $response = postJson('/api/v1/auth/verify', [
            'identifier' => 'nonexistent@example.com',
            'code' => '123456',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('returns 422 for invalid OTP code', function () {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'email_verified_at' => null,
        ]);

        $user->createOneTimePassword();

        $response = postJson('/api/v1/auth/verify', [
            'identifier' => 'john@example.com',
            'code' => 'wrong-code',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('returns 422 for expired OTP code', function () {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'email_verified_at' => null,
        ]);

        $otp = $user->createOneTimePassword()->password;

        // Travel forward to expire the OTP (default expiry is 5 minutes)
        $this->travel(10)->minutes();

        $response = postJson('/api/v1/auth/verify', [
            'identifier' => 'john@example.com',
            'code' => $otp,
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });
});
