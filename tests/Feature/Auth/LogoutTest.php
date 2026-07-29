<?php

use App\Models\User;

use function Pest\Laravel\postJson;

describe('Logout', function () {
    it('requires authentication', function () {
        $response = postJson('/api/v1/auth/logout');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('revokes current access token and returns 204', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = postJson('/api/v1/auth/logout', [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(204);

        expect($user->fresh()->tokens)->toHaveCount(0);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = postJson('/api/v1/auth/logout');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });
});
