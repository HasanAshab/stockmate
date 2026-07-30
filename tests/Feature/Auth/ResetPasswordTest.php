<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\postJson;

describe('Reset Password', function () {
    it('resets password with valid OTP and returns 200', function () {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        $oldTokenCount = $user->tokens()->count();
        $token = $user->createToken('test-token')->plainTextToken;
        $otp = $user->createOneTimePassword()->password;

        $response = postJson('/api/v1/auth/reset-password', [
            'identifier' => 'john@example.com',
            'code' => $otp,
            'password' => 'NewPassword123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);

        $user = $user->fresh();

        expect(Hash::check('NewPassword123!', $user->password))->toBeTrue();
        expect($user->tokens()->count())->toBe(0);
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

    it('returns 422 with code validation error for non-existent user', function () {
        $response = postJson('/api/v1/auth/reset-password', [
            'identifier' => 'nonexistent@example.com',
            'code' => '123456',
            'password' => 'NewPassword123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('returns 422 for invalid OTP code', function () {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        $user->createOneTimePassword();

        $response = postJson('/api/v1/auth/reset-password', [
            'identifier' => 'john@example.com',
            'code' => 'wrong-code',
            'password' => 'NewPassword123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('returns 422 for expired OTP code', function () {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        $otp = $user->createOneTimePassword()->password;

        // Travel forward to expire the OTP
        $this->travel(10)->minutes();

        $response = postJson('/api/v1/auth/reset-password', [
            'identifier' => 'john@example.com',
            'code' => $otp,
            'password' => 'NewPassword123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('revokes all user tokens after password reset', function () {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        $user->createToken('token1');
        $user->createToken('token2');
        $user->createToken('token3');

        expect($user->tokens()->count())->toBe(3);

        $otp = $user->createOneTimePassword()->password;

        $response = postJson('/api/v1/auth/reset-password', [
            'identifier' => 'john@example.com',
            'code' => $otp,
            'password' => 'NewPassword123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);

        expect($user->fresh()->tokens()->count())->toBe(0);
    });
});
