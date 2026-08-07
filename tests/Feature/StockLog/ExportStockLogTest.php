<?php

use App\Enums\Permission;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('Export Stock Logs', function () {
    it('requires authentication', function () {
        $response = getJson('/api/v1/stock-logs/export/csv');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires stock-logs-view permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/stock-logs/export/csv');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('exports stock logs as CSV', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::StockLogsView->value);

        $response = actingAs($user)->getJson('/api/v1/stock-logs/export/csv');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
    
    it('exports stock logs as Excel', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::StockLogsView->value);

        $response = actingAs($user)->getJson('/api/v1/stock-logs/export/excel');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = getJson('/api/v1/stock-logs/export/csv');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/stock-logs/export/csv');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });
});
