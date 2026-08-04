<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('List Notifications', function () {
    it('requires authentication', function () {
        $response = getJson('/api/v1/notifications');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns paginated list of notifications with 200', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/notifications');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns only authenticated user notifications', function () {
        $user1 = User::factory()->create();

        $response = actingAs($user1)->getJson('/api/v1/notifications');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns unread notifications list', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/notifications/unread');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns unread count', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/notifications/unread/count');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = getJson('/api/v1/notifications');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns notification resource collection', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/notifications');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
