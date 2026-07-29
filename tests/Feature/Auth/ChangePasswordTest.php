<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\postJson;

describe('Change Password', function () {
    it('requires authentication', function () {
        $response = postJson('/api/v1/auth/change-password', [
            'password' => 'NewPassword123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('changes password for authenticated user and returns 200', function () {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = postJson('/api/v1/auth/change-password', [
            'password' => 'NewPassword123!',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);

        expect(Hash::check('NewPassword123!', $user->fresh()->password))->toBeTrue();
    });

    it('returns validation errors for invalid input', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = postJson('/api/v1/auth/change-password', [
            'password' => '',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = postJson('/api/v1/auth/change-password', [
            'password' => 'NewPassword123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });
});
