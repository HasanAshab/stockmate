<?php

use App\Enums\Permission;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\User;
use App\Models\Warehouse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('List Stock Logs', function () {
    it('requires authentication', function () {
        $response = getJson('/api/v1/stock-logs');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires stock-logs-view permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/stock-logs');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns paginated list of stock logs with 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::StockLogsView->value);

        StockLog::factory()->count(3)->create();

        $response = actingAs($user)->getJson('/api/v1/stock-logs');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('includes warehouse, product, creator relationships', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::StockLogsView->value);

        $stockLog = StockLog::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/stock-logs');

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $stockLog->id]);
    });

    it('filters by warehouse ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::StockLogsView->value);

        $warehouse = Warehouse::factory()->create();
        $matchingLog = StockLog::factory()->create(['warehouse_id' => $warehouse->id]);
        $otherLog = StockLog::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/stock-logs?filter[warehouse]={$warehouse->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $matchingLog->id])
            ->assertJsonMissing(['id' => $otherLog->id]);
    });

    it('filters by product ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::StockLogsView->value);

        $product = Product::factory()->create();
        $matchingLog = StockLog::factory()->create(['product_id' => $product->id]);
        $otherLog = StockLog::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/stock-logs?filter[product]={$product->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $matchingLog->id])
            ->assertJsonMissing(['id' => $otherLog->id]);
    });

    it('sorts by created_at descending', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::StockLogsView->value);

        $firstCreated = StockLog::factory()->create(['created_at' => now()->subDays(2)]);
        $secondCreated = StockLog::factory()->create(['created_at' => now()->subDay()]);

        $response = actingAs($user)->getJson('/api/v1/stock-logs');

        $response->assertValidRequest()
            ->assertValidResponse(200);

        $ids = collect($response->json('data'))->pluck('id');
        expect($ids->first())->toBe($secondCreated->id);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = getJson('/api/v1/stock-logs');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/stock-logs');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns stock log resource collection', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::StockLogsView->value);

        StockLog::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/stock-logs');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
