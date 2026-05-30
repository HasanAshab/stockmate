<?php

use App\Models\User;
use App\Enums\Role;
use Illuminate\Support\Facades\Hash;

it('can list users paginated', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    User::factory()->count(20)->create();

    $response = $this->actingAs($admin)->getJson('/api/v1/users');

    $response->assertStatus(200)
             ->assertJsonStructure(['data', 'current_page', 'per_page', 'total']);
});

it('can create a new user', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $response = $this->actingAs($admin)->postJson('/api/v1/users', [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password123',
        'role' => 'staff',
    ]);

    $response->assertStatus(201)
             ->assertJsonFragment(['name' => 'New User']);

    $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
});

it('can show a user', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $user = User::factory()->create();

    $response = $this->actingAs($admin)->getJson("/api/v1/users/{$user->id}");

    $response->assertStatus(200)
             ->assertJsonFragment(['email' => $user->email]);
});

it('can update a user', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $user = User::factory()->create();

    $response = $this->actingAs($admin)->patchJson("/api/v1/users/{$user->id}", [
        'name' => 'Updated Name',
        'role' => 'admin',
    ]);

    $response->assertStatus(200)
             ->assertJsonFragment(['name' => 'Updated Name']);
    
    $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name']);
});

it('prevents admin from changing their own role', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $response = $this->actingAs($admin)->patchJson("/api/v1/users/{$admin->id}", [
        'role' => 'staff',
    ]);

    $response->assertStatus(403)
             ->assertJson(['message' => 'You cannot change your own role.']);
});

it('can toggle user status', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $user = User::factory()->create(['is_active' => true]);

    $response = $this->actingAs($admin)->patchJson("/api/v1/users/{$user->id}/toggle-status");

    $response->assertStatus(200)
             ->assertJson(['message' => 'User status updated.', 'is_active' => false]);
    
    $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => false]);
});

it('prevents admin from deactivating themselves', function () {
    $admin = User::factory()->create(['role' => Role::Admin, 'is_active' => true]);

    $response = $this->actingAs($admin)->patchJson("/api/v1/users/{$admin->id}/toggle-status");

    $response->assertStatus(403)
             ->assertJson(['message' => 'You cannot deactivate your own account.']);
});
