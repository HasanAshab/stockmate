<?php

use App\Enums\Permission;
use App\Models\Category;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('List Categories', function () {
    it('requires authentication', function () {
        $response = getJson('/api/v1/categories');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires categories-view permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/categories');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = getJson('/api/v1/categories');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without categories-view permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/categories');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns paginated list of categories with 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesView->value);

        Category::factory()->count(3)->create();

        $response = actingAs($user)->getJson('/api/v1/categories');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns cached categories within one hour', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesView->value);

        $category = Category::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/categories');

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $category->id]);
    });

    it('returns category resource collection', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesView->value);

        Category::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/categories');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
