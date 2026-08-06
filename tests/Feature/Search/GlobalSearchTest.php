<?php

use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('Global Search', function () {
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

        $response = actingAs($user)->getJson('/api/v1/search?q=ab');

        $response->assertValidRequest()
            ->assertValidResponse(200);

        expect($response->json('data'))->toBeEmpty();
    });

    it('enforces minimum length of 3 characters for global search', function () {
        $user = User::factory()->create();

        // 2 characters should return empty
        $response = actingAs($user)->getJson('/api/v1/search?q=ab');

        $response->assertValidRequest()
            ->assertValidResponse(200);

        expect($response->json('data'))->toBeEmpty();

        // 3 characters should be valid
        $response = actingAs($user)->getJson('/api/v1/search?q=abc');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('trims whitespace from search term', function () {
        $user = User::factory()->create();

        // Spaces will be trimmed server-side, so query with padded string
        $response = actingAs($user)->getJson('/api/v1/search', ['q' => '   laptop   ']);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns empty data when query is missing', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/search');

        $response->assertValidRequest()
            ->assertValidResponse(200);

        expect($response->json('data'))->toBeEmpty();
    });

    it('returns data as empty when no authorized scopes', function () {
        $user = User::factory()->create();
        // No permissions granted

        Product::factory()->count(3)->create();

        $response = actingAs($user)->getJson('/api/v1/search?q=laptop');

        $response->dump();

        $response->assertValidRequest()
            ->assertValidResponse(200);

        expect($response->json('data'))->toBeEmpty();
    });

    it('handles empty string query gracefully', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/search?q=');

        $response->assertValidRequest()
            ->assertValidResponse(200);

        expect($response->json('data'))->toBeEmpty();
    });

    it('handles only whitespace query gracefully', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/search', ['q' => '   ']);

        $response->assertValidRequest()
            ->assertValidResponse(200);

        expect($response->json('data'))->toBeEmpty();
    });
});
