<?php

use App\Models\User;
use App\Notifications\AuthOtpNotification;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\postJson;

describe('Resend Verification', function () {
    it('resends verification code to email and returns 200', function () {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'john@example.com',
            'email_verified_at' => null,
        ]);

        $response = postJson('/api/v1/auth/verification-notification', [
            'identifier' => 'john@example.com',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);

        Notification::assertSentTo($user, AuthOtpNotification::class);
    });

    it('resends verification code to phone and returns 200', function () {
        Notification::fake();

        $user = User::factory()->create([
            'phone' => '+8801712345678',
            'phone_verified_at' => null,
        ]);

        $response = postJson('/api/v1/auth/verification-notification', [
            'identifier' => '+8801712345678',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);

        Notification::assertSentTo($user, AuthOtpNotification::class);
    });

    it('silently succeeds for non-existent user', function () {
        Notification::fake();

        $response = postJson('/api/v1/auth/verification-notification', [
            'identifier' => 'nonexistent@example.com',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);

        Notification::assertNothingSent();
    });

    it('silently succeeds for already verified user', function () {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'john@example.com',
            'email_verified_at' => now(),
        ]);

        $response = postJson('/api/v1/auth/verification-notification', [
            'identifier' => 'john@example.com',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);

        Notification::assertNothingSent();
    });

    it('creates new OTP and sends notification', function () {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'john@example.com',
            'email_verified_at' => null,
        ]);

        $response = postJson('/api/v1/auth/verification-notification', [
            'identifier' => $user->email,
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);

        Notification::assertSentTo($user, AuthOtpNotification::class);
    });
});
