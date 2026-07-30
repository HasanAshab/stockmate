<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('Search', function () {
    it('requires authentication', function () {
        $response = getJson('/api/v1/search?q=laptop');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns search results with 200', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/search?q=laptop');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns empty data when query term is too short', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/search?q=a');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('searches scoped resource when scope specified', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/search/products?q=laptop');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = getJson('/api/v1/search?q=laptop');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });
});
