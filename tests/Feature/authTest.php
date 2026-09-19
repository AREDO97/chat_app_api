<?php

use App\Models\Audit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);
use App\Models\User;

test('user can create an account', function () {

    $response = $this->postJson('/api/register', [
        'name' => 'Ivan Joe',
        'email' => 'van@gmail.com',
        'password' => 'ivan256@@',
    ]);
  
    $response->assertStatus(201);
});

// login test
test('user can login to account', function () {
    // create user
    $user = User::factory()->create([
        'name' => 'Ivan Joe',
        'email' => 'van@gmail.com',
        'password' => 'ivan256@@',
    ]);
    $response = $this->postJson('/api/login', [
        'email' => 'van@gmail.com',
        'password' => 'ivan256@@',
    ]);

    $response->assertStatus(200);
});

// test logout
test('user can logout of account', function () {
    // create user
    $user = User::factory()->create([
        'name' => 'Ivan Joe',
        'email' => 'van@gmail.com',
        'password' => 'ivan256@@',
    ]);
    $response = $this->postJson('/api/login', [
        'email' => 'van@gmail.com',
        'password' => 'ivan256@@',
    ]);
    // sanctum
    Sanctum::actingAs($user);
    $user->currentAccessToken()->delete();
    $response->assertStatus(200);
});
