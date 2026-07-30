<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('Show User', function () {
    it('requires authentication', function () {
        $user = User::factory()->create();

        $response = getJson("/api/v1/users/{$user->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns user details with 200', function () {
        $authUser = User::factory()->create();
        $targetUser = User::factory()->create();

        $response = actingAs($authUser)->getJson("/api/v1/users/{$targetUser->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $targetUser->id]);
    });

    it('includes roles relationship when requested', function () {
        $authUser = User::factory()->create();
        $targetUser = User::factory()->create();

        $response = actingAs($authUser)->getJson("/api/v1/users/{$targetUser->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('includes permissions relationship when requested', function () {
        $authUser = User::factory()->create();
        $targetUser = User::factory()->create();

        $response = actingAs($authUser)->getJson("/api/v1/users/{$targetUser->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('does not return password', function () {
        $authUser = User::factory()->create();
        $targetUser = User::factory()->create();

        $response = actingAs($authUser)->getJson("/api/v1/users/{$targetUser->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200);

        expect($response->json('data'))->not->toHaveKey('password');
    });

    it('returns 401 for unauthenticated request', function () {
        $targetUser = User::factory()->create();

        $response = getJson("/api/v1/users/{$targetUser->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 404 for non-existent user', function () {
        $authUser = User::factory()->create();

        $response = actingAs($authUser)->getJson('/api/v1/users/999999');

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns user resource', function () {
        $authUser = User::factory()->create();
        $targetUser = User::factory()->create();

        $response = actingAs($authUser)->getJson("/api/v1/users/{$targetUser->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
