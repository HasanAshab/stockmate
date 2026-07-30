<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patchJson;

describe('Mark Notification as Read', function () {
    it('requires authentication', function () {
        $response = patchJson('/api/v1/notifications/fake-uuid/read');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 404 for non-existent notification', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->patchJson('/api/v1/notifications/00000000-0000-0000-0000-000000000000/read');

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = patchJson('/api/v1/notifications/00000000-0000-0000-0000-000000000000/read');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });
});
