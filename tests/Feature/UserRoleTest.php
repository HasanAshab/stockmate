<?php

use App\Enums\Role;
use App\Models\User;

it('allows admin to access users endpoints', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $response = $this->actingAs($admin)->getJson('/api/v1/users');

    $response->assertStatus(200);
});

it('rejects staff from accessing admin-only endpoints', function () {
    $staff = User::factory()->create(['role' => Role::Staff]);

    $response = $this->actingAs($staff)->getJson('/api/v1/users');

    $response->assertStatus(403)
        ->assertJson(['message' => 'Unauthorized.']);
});
