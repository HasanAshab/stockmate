<?php

use App\Enums\Permission;
use App\Models\Category;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;

describe('Delete Category', function () {
    it('requires authentication', function () {
        $category = Category::factory()->create();

        $response = deleteJson("/api/v1/categories/{$category->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires categories-delete permission', function () {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = actingAs($user)->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('soft deletes category and returns 204', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesDelete->value);

        $category = Category::factory()->create();

        $response = actingAs($user)->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertValidRequest()
            ->assertValidResponse(204);

        $this->assertSoftDeleted('categories', [
            'id' => $category->id,
        ]);
    });

    it('returns 401 for unauthenticated request', function () {
        $category = Category::factory()->create();

        $response = deleteJson("/api/v1/categories/{$category->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without categories-delete permission', function () {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = actingAs($user)->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent category', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesDelete->value);

        $response = actingAs($user)->deleteJson('/api/v1/categories/999999');

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });
});
