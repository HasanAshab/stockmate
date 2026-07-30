<?php

use App\Enums\Permission;
use App\Models\Category;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('Show Category', function () {
    it('requires authentication', function () {
        $category = Category::factory()->create();

        $response = getJson("/api/v1/categories/{$category->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires categories-view permission', function () {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/categories/{$category->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns category details with 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesView->value);

        $category = Category::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/categories/{$category->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $category->id]);
    });

    it('returns 401 for unauthenticated request', function () {
        $category = Category::factory()->create();

        $response = getJson("/api/v1/categories/{$category->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without categories-view permission', function () {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/categories/{$category->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent category', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesView->value);

        $response = actingAs($user)->getJson('/api/v1/categories/999999');

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns category resource', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesView->value);

        $category = Category::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/categories/{$category->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
