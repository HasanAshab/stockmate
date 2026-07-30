<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('List Users', function () {
    it('requires authentication', function () {
        $response = getJson('/api/v1/users');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns paginated list of users with 200', function () {
        $user = User::factory()->create();

        User::factory()->count(3)->create();

        $response = actingAs($user)->getJson('/api/v1/users');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('filters users by is_active status', function () {
        $user = User::factory()->create();

        $activeUser = User::factory()->create(['is_active' => true]);
        $inactiveUser = User::factory()->create(['is_active' => false]);

        $response = actingAs($user)->getJson('/api/v1/users?filter[is_active]=true');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('sorts users by created_at', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/users?sort=created_at');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('includes roles relationship', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/users');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = getJson('/api/v1/users');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns user resource collection', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/users');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
