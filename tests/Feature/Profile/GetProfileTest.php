<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('Get Profile', function () {
    it('requires authentication', function () {
        $response = getJson('/api/v1/profile');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns authenticated user profile with 200', function () {
        $user = User::factory()->create(['name' => 'Profile User']);

        $response = actingAs($user)->getJson('/api/v1/profile');

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['name' => 'Profile User']);
    });

    it('does not return password', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/profile');

        $response->assertValidRequest()
            ->assertValidResponse(200);

        expect($response->json('data'))->not->toHaveKey('password');
    });

    it('returns 401 for unauthenticated request', function () {
        $response = getJson('/api/v1/profile');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });
});
