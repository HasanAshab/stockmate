<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('can show own profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/v1/profile');

    $response->assertStatus(200)
             ->assertJsonFragment(['email' => $user->email]);
});

it('can update own profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->putJson('/api/v1/profile', [
        'name' => 'My New Name',
        'password' => 'newpassword123',
    ]);

    $response->assertStatus(200)
             ->assertJsonFragment(['name' => 'My New Name']);

    expect(Hash::check('newpassword123', $user->fresh()->password))->toBeTrue();
});
