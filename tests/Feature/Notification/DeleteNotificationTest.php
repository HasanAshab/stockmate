<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;

describe('Delete Notification', function () {
    it('requires authentication', function () {
        $response = deleteJson('/api/v1/notifications/00000000-0000-0000-0000-000000000000');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 404 for non-existent notification', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->deleteJson('/api/v1/notifications/00000000-0000-0000-0000-000000000000');

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = deleteJson('/api/v1/notifications/00000000-0000-0000-0000-000000000000');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });
});
