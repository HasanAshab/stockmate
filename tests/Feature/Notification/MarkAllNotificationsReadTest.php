<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patchJson;

describe('Mark All Notifications as Read', function () {
    it('requires authentication', function () {
        $response = patchJson('/api/v1/notifications/mark-all-as-read');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('marks all user notifications as read and returns 204', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->patchJson('/api/v1/notifications/mark-all-as-read');

        $response->assertValidRequest()
            ->assertValidResponse(204);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = patchJson('/api/v1/notifications/mark-all-as-read');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });
});
