<?php

use App\Enums\Permission;
use App\Models\Category;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('List Trashed Categories', function () {
    it('requires authentication', function () {
        $response = getJson('/api/v1/categories/trashed');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires categories-view permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/categories/trashed');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns list of soft-deleted categories with 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesView->value);

        $activeCategory = Category::factory()->create();
        $trashedCategory = Category::factory()->create();
        $trashedCategory->delete();

        $response = actingAs($user)->getJson('/api/v1/categories/trashed');

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $trashedCategory->id])
            ->assertJsonMissing(['id' => $activeCategory->id]);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = getJson('/api/v1/categories/trashed');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without categories-view permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/categories/trashed');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns category resource collection', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesView->value);

        $trashedCategory = Category::factory()->create();
        $trashedCategory->delete();

        $response = actingAs($user)->getJson('/api/v1/categories/trashed');
        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
