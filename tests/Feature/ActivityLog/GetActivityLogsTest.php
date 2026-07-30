<?php

use App\Models\User;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('List Activity Logs', function () {
    it('requires authentication', function () {
        $response = getJson('/api/v1/activity-logs');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns paginated list of activity logs with 200', function () {
        $user = User::factory()->create();

        activity()
            ->causedBy($user)
            ->log('Test activity log entry');

        $response = actingAs($user)->getJson('/api/v1/activity-logs');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('includes causer and subject relationships', function () {
        $user = User::factory()->create();

        activity()
            ->causedBy($user)
            ->log('Test action with causer');

        $response = actingAs($user)->getJson('/api/v1/activity-logs');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = getJson('/api/v1/activity-logs');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns activity log resource collection', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/activity-logs');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
