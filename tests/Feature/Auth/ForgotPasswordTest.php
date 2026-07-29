<?php

use App\Models\User;

use function Pest\Laravel\postJson;

describe('Forgot Password', function () {
    it('sends password reset OTP to email and returns 202', function () {
        User::factory()->create(['email' => 'john@example.com']);

        $response = postJson('/api/v1/auth/forgot-password', [
            'identifier' => 'john@example.com',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(202);
    });

    it('sends password reset OTP to phone and returns 202', function () {
        User::factory()->create(['phone' => '+8801712345678']);

        $response = postJson('/api/v1/auth/forgot-password', [
            'identifier' => '+8801712345678',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(202);
    });

    it('returns validation errors for invalid input', function () {
        $response = postJson('/api/v1/auth/forgot-password', [
            'identifier' => '',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('silently succeeds for non-existent user', function () {
        $response = postJson('/api/v1/auth/forgot-password', [
            'identifier' => 'nonexistent@example.com',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(202);
    });
});
