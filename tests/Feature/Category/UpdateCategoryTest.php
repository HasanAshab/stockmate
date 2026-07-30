<?php

use App\Enums\Permission;
use App\Models\Category;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\putJson;

describe('Update Category', function () {
    it('requires authentication', function () {
        $category = Category::factory()->create();

        $response = putJson("/api/v1/categories/{$category->id}", []);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires categories-update permission', function () {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = actingAs($user)->putJson("/api/v1/categories/{$category->id}", []);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('updates category and returns 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesUpdate->value);

        $category = Category::factory()->create([
            'name' => 'Old Name',
            'slug' => 'old-name',
        ]);

        $payload = [
            'name' => 'New Name',
            'slug' => 'new-name',
            'description' => 'Updated description',
        ];

        $response = actingAs($user)->putJson("/api/v1/categories/{$category->id}", $payload);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment([
                'name' => 'New Name',
                'slug' => 'new-name',
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New Name',
            'slug' => 'new-name',
        ]);
    });

    it('returns validation errors for invalid input', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesUpdate->value);

        $category = Category::factory()->create();

        $payload = [
            'name' => str_repeat('a', 71),
        ];

        $response = actingAs($user)->putJson("/api/v1/categories/{$category->id}", $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('rejects duplicate slug', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesUpdate->value);

        Category::factory()->create(['slug' => 'existing-slug']);
        $categoryToUpdate = Category::factory()->create(['slug' => 'original-slug']);

        $payload = [
            'slug' => 'existing-slug',
        ];

        $response = actingAs($user)->putJson("/api/v1/categories/{$categoryToUpdate->id}", $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates max length for name field', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesUpdate->value);

        $category = Category::factory()->create();

        $payload = [
            'name' => str_repeat('a', 71),
        ];

        $response = actingAs($user)->putJson("/api/v1/categories/{$category->id}", $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates max length for slug field', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesUpdate->value);

        $category = Category::factory()->create();

        $payload = [
            'slug' => str_repeat('a', 71),
        ];

        $response = actingAs($user)->putJson("/api/v1/categories/{$category->id}", $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates max length for description field', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesUpdate->value);

        $category = Category::factory()->create();

        $payload = [
            'description' => str_repeat('a', 256),
        ];

        $response = actingAs($user)->putJson("/api/v1/categories/{$category->id}", $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('allows partial updates', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesUpdate->value);

        $category = Category::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-name',
            'description' => 'Original Description',
        ]);

        $payload = [
            'name' => 'Only Name Updated',
        ];

        $response = actingAs($user)->putJson("/api/v1/categories/{$category->id}", $payload);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment([
                'name' => 'Only Name Updated',
                'slug' => 'original-name',
            ]);
    });

    it('returns 401 for unauthenticated request', function () {
        $category = Category::factory()->create();

        $response = putJson("/api/v1/categories/{$category->id}", [
            'name' => 'Test',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without categories-update permission', function () {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = actingAs($user)->putJson("/api/v1/categories/{$category->id}", [
            'name' => 'Test',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent category', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesUpdate->value);

        $response = actingAs($user)->putJson('/api/v1/categories/999999', [
            'name' => 'Test',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns updated category resource', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesUpdate->value);

        $category = Category::factory()->create();

        $response = actingAs($user)->putJson("/api/v1/categories/{$category->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
