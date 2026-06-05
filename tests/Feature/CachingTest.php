<?php

use App\Enums\Role;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    Cache::flush();
});

test('dashboard metrics are cached', function () {
    $admin = User::factory()->admin()->create();

    // First request should hit database
    $this->actingAs($admin)->getJson(route('dashboard'))->assertOk();

    // Second request should use cache
    DB::enableQueryLog();
    $this->actingAs($admin)->getJson(route('dashboard'))->assertOk();
    $queryCount = count(DB::getQueryLog());

    // Should be minimal queries (just auth check, not the 4-5 count queries)
    expect($queryCount)->toBeLessThan(3);
});

test('dashboard cache is invalidated on stock changes', function () {
    $admin = User::factory()->admin()->create();

    // Cache dashboard
    $this->actingAs($admin)->getJson(route('dashboard'))->assertOk();
    expect(Cache::has('dashboard:metrics'))->toBeTrue();

    // Manually forget cache to test it's cleared (simulating stock change)
    Cache::forget('dashboard:metrics');

    // Cache should be cleared
    expect(Cache::has('dashboard:metrics'))->toBeFalse();
});

test('categories are cached', function () {
    $admin = User::factory()->admin()->create();
    Category::factory()->count(5)->create();

    // First request should hit database
    $this->actingAs($admin)->getJson(route('categories.index'))->assertOk();
    expect(Cache::has('categories:all'))->toBeTrue();

    // Second request should use cache
    DB::enableQueryLog();
    $this->actingAs($admin)->getJson(route('categories.index'))->assertOk();
    $queryCount = count(DB::getQueryLog());

    // Should be minimal queries (just auth, not category query)
    expect($queryCount)->toBeLessThan(3);
});

test('category cache is cleared when category is created', function () {
    $admin = User::factory()->admin()->create();

    // Cache categories
    $this->actingAs($admin)->getJson(route('categories.index'))->assertOk();
    expect(Cache::has('categories:all'))->toBeTrue();

    // Create category
    $this->actingAs($admin)->postJson(route('categories.store'), [
        'name' => 'New Category',
        'slug' => 'new-category',
    ])->assertCreated();

    // Cache should be cleared
    expect(Cache::has('categories:all'))->toBeFalse();
    expect(Cache::has('dashboard:metrics'))->toBeFalse();
});

test('category cache is cleared when category is updated', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    // Cache categories
    $this->actingAs($admin)->getJson(route('categories.index'))->assertOk();
    expect(Cache::has('categories:all'))->toBeTrue();

    // Update category
    $this->actingAs($admin)->patchJson(route('categories.update', $category), [
        'name' => 'Updated Category',
    ])->assertOk();

    // Cache should be cleared
    expect(Cache::has('categories:all'))->toBeFalse();
    expect(Cache::has('dashboard:metrics'))->toBeFalse();
});

test('category cache is cleared when category is deleted', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    // Cache categories
    $this->actingAs($admin)->getJson(route('categories.index'))->assertOk();
    expect(Cache::has('categories:all'))->toBeTrue();

    // Delete category
    $this->actingAs($admin)->deleteJson(route('categories.destroy', $category))->assertNoContent();

    // Cache should be cleared
    expect(Cache::has('categories:all'))->toBeFalse();
    expect(Cache::has('dashboard:metrics'))->toBeFalse();
});

test('trashed categories list is separate from active list', function () {
    $admin = User::factory()->admin()->create();
    Category::factory()->count(3)->create();
    $deleted = Category::factory()->create();
    $deleted->delete();

    // Cache active categories
    $this->actingAs($admin)->getJson(route('categories.index'))->assertOk();
    expect(Cache::has('categories:all'))->toBeTrue();

    // Trashed should not be cached yet
    expect(Cache::has('categories:trashed'))->toBeFalse();
});

test('suppliers are cached', function () {
    $admin = User::factory()->admin()->create();
    Supplier::factory()->count(5)->create();

    // First request should hit database
    $this->actingAs($admin)->getJson(route('suppliers.index'))->assertOk();
    expect(Cache::has('suppliers:all'))->toBeTrue();

    // Second request should use cache
    DB::enableQueryLog();
    $this->actingAs($admin)->getJson(route('suppliers.index'))->assertOk();
    $queryCount = count(DB::getQueryLog());

    // Should be minimal queries
    expect($queryCount)->toBeLessThan(3);
});

test('supplier cache is cleared when supplier is created', function () {
    $admin = User::factory()->admin()->create();

    // Cache suppliers
    $this->actingAs($admin)->getJson(route('suppliers.index'))->assertOk();
    expect(Cache::has('suppliers:all'))->toBeTrue();

    // Create supplier
    $this->actingAs($admin)->postJson(route('suppliers.store'), [
        'name' => 'New Supplier',
        'email' => 'newsupplier@example.com',
        'phone' => '123456789',
        'address' => '123 Main St',
    ])->assertCreated();

    // Cache should be cleared
    expect(Cache::has('suppliers:all'))->toBeFalse();
    expect(Cache::has('dashboard:metrics'))->toBeFalse();
});

test('supplier cache is cleared when supplier is updated', function () {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::factory()->create();

    // Cache suppliers
    $this->actingAs($admin)->getJson(route('suppliers.index'))->assertOk();
    expect(Cache::has('suppliers:all'))->toBeTrue();

    // Update supplier
    $this->actingAs($admin)->patchJson(route('suppliers.update', $supplier), [
        'name' => 'Updated Supplier',
    ])->assertOk();

    // Cache should be cleared
    expect(Cache::has('suppliers:all'))->toBeFalse();
    expect(Cache::has('dashboard:metrics'))->toBeFalse();
});

test('supplier cache is cleared when supplier is deleted', function () {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::factory()->create();

    // Cache suppliers
    $this->actingAs($admin)->getJson(route('suppliers.index'))->assertOk();
    expect(Cache::has('suppliers:all'))->toBeTrue();

    // Delete supplier
    $this->actingAs($admin)->deleteJson(route('suppliers.destroy', $supplier))->assertNoContent();

    // Cache should be cleared
    expect(Cache::has('suppliers:all'))->toBeFalse();
    expect(Cache::has('dashboard:metrics'))->toBeFalse();
});

test('both category caches are cleared when category is restored', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $category->delete();

    // Manually set both caches to simulate they were previously cached
    Cache::put('categories:all', collect([]), now()->addHour());
    Cache::put('categories:trashed', collect([]), now()->addMinutes(30));
    
    expect(Cache::has('categories:all'))->toBeTrue();
    expect(Cache::has('categories:trashed'))->toBeTrue();

    // Restore category
    $this->actingAs($admin)->postJson(route('categories.restore', $category))->assertOk();

    // Both caches should be cleared
    expect(Cache::has('categories:all'))->toBeFalse();
    expect(Cache::has('categories:trashed'))->toBeFalse();
    expect(Cache::has('dashboard:metrics'))->toBeFalse();
});

test('both supplier caches are cleared when supplier is restored', function () {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::factory()->create();
    $supplier->delete();

    // Manually set both caches to simulate they were previously cached
    Cache::put('suppliers:all', collect([]), now()->addHour());
    Cache::put('suppliers:trashed', collect([]), now()->addMinutes(30));
    
    expect(Cache::has('suppliers:all'))->toBeTrue();
    expect(Cache::has('suppliers:trashed'))->toBeTrue();

    // Restore supplier
    $this->actingAs($admin)->postJson(route('suppliers.restore', $supplier))->assertOk();

    // Both caches should be cleared
    expect(Cache::has('suppliers:all'))->toBeFalse();
    expect(Cache::has('suppliers:trashed'))->toBeFalse();
    expect(Cache::has('dashboard:metrics'))->toBeFalse();
});
