<?php

use function Pest\Laravel\getJson;

describe('Get Configuration Enums', function () {
    it('does not require authentication', function () {
        $response = getJson('/api/v1/config/enums');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns all enum values with 200', function () {
        $response = getJson('/api/v1/config/enums');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns purchase_order_statuses, sales_order_statuses, stock_log_types', function () {
        $response = getJson('/api/v1/config/enums');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
