<?php

use App\Enums\Permission;
use App\Models\Category;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('Create Category', function () {
    it('requires authentication', function () {
        $payload = [
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Electronic gadgets and devices',
        ];
        $response = postJson('/api/v1/categories', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires categories-create permission', function () {
        $user = User::factory()->create();

        $payload = [
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Electronic gadgets and devices',
        ];
        $response = actingAs($user)->postJson('/api/v1/categories', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('creates a new category and returns 201', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesCreate->value);

        $payload = [
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Electronic gadgets and devices',
        ];

        $response = actingAs($user)->postJson('/api/v1/categories', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201)
            ->assertJsonFragment([
                'name' => 'Electronics',
                'slug' => 'electronics',
            ]);

        $category = Category::where('slug', 'electronics')->first();

        expect($category)->not->toBeNull()
            ->and($category->name)->toBe('Electronics');
    });

    it('returns validation errors for invalid input', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesCreate->value);

        $response = actingAs($user)->postJson('/api/v1/categories', []);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('rejects duplicate slug', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesCreate->value);

        Category::factory()->create(['slug' => 'electronics']);

        $payload = [
            'name' => 'New Electronics',
            'slug' => 'electronics',
            'description' => 'Duplicate slug test',
        ];

        $response = actingAs($user)->postJson('/api/v1/categories', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('validates max length for name field', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesCreate->value);

        $payload = [
            'name' => str_repeat('a', 71),
            'slug' => 'valid-slug',
        ];

        $response = actingAs($user)->postJson('/api/v1/categories', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates max length for slug field', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesCreate->value);

        $payload = [
            'name' => 'Valid Name',
            'slug' => str_repeat('a', 71),
        ];

        $response = actingAs($user)->postJson('/api/v1/categories', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates max length for description field', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesCreate->value);

        $payload = [
            'name' => 'Valid Name',
            'slug' => 'valid-slug',
            'description' => str_repeat('a', 256),
        ];

        $response = actingAs($user)->postJson('/api/v1/categories', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('allows nullable description', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesCreate->value);

        $payload = [
            'name' => 'Laptops',
            'slug' => 'laptops',
            'description' => null,
        ];

        $response = actingAs($user)->postJson('/api/v1/categories', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);

        $category = Category::where('slug', 'laptops')->first();

        expect($category)->not->toBeNull()
            ->and($category->name)->toBe('Laptops')
            ->and($category->description)->toBeNull();
    });

    it('returns 401 for unauthenticated request', function () {
        $response = postJson('/api/v1/categories', [
            'name' => 'Test',
            'slug' => 'test',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without categories-create permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->postJson('/api/v1/categories', [
            'name' => 'Test',
            'slug' => 'test',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns category resource on success', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::CategoriesCreate->value);

        $payload = [
            'name' => 'Mobile Phones',
            'slug' => 'mobile-phones',
        ];

        $response = actingAs($user)->postJson('/api/v1/categories', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);
    });
});
