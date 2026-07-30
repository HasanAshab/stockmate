<?php

use App\Enums\Permission;
use App\Models\Category;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('Restore Category', function () {
    it('requires authentication', function () {
        $category = Category::factory()->create();
        $category->delete();

        $response = postJson("/api/v1/categories/{$category->id}/restore");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires categories-restore permission', function () {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $category->delete();

        $response = actingAs($user)->postJson("/api/v1/categories/{$category->id}/restore");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('restores soft-deleted category and returns 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesRestore->value);

        $category = Category::factory()->create();
        $category->delete();

        $response = actingAs($user)->postJson("/api/v1/categories/{$category->id}/restore");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $category->id]);

        $this->assertNotSoftDeleted('categories', [
            'id' => $category->id,
        ]);
    });

    it('returns 401 for unauthenticated request', function () {
        $category = Category::factory()->create();
        $category->delete();

        $response = postJson("/api/v1/categories/{$category->id}/restore");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without categories-restore permission', function () {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $category->delete();

        $response = actingAs($user)->postJson("/api/v1/categories/{$category->id}/restore");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent trashed category', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesRestore->value);

        $response = actingAs($user)->postJson('/api/v1/categories/999999/restore');

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns restored category resource', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesRestore->value);

        $category = Category::factory()->create();
        $category->delete();

        $response = actingAs($user)->postJson("/api/v1/categories/{$category->id}/restore");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
