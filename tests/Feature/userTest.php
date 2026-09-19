<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);
use App\Models\User;

// view all suspended users
test('admins can view suspended users', function () {
    // create admin
    $user = User::factory()->create([
        'role' => 'admin',
    ]);
    // logged in user
    Sanctum::actingAs($user);
    // register user
    $response = User::factory()->count(10)->create([
        'is_active' => true,
    ]);
    $response = $this->getJson('/api/users/suspended');

    $response->assertStatus(200);
});

// view all users

test('you can view other users', function () {
    // create admin
    $user = User::factory()->create([
        'role' => 'admin',
    ]);
    // logged in user
    Sanctum::actingAs($user);
    // register user
    $response = User::factory()->count(10)->create();
    $response = $this->getJson('/api/users');
    $response->assertStatus(200);
});

// create admins
test('admins can create admin users', function () {
    // create admin
    $user = User::factory()->create([
        'role' => 'admin',
    ]);
    // logged in user
    Sanctum::actingAs($user);
    // register user
    $response = User::factory()->count(10)->create([
        'is_active' => true,
    ]);
    $response = $this->patchJson('/api/users/create_admin/2',[
        'role'=>'admin'
    ]);

    $response->assertStatus(200);
});

// demote admins
test('admins can demote admin users', function () {
    // create admin
    $user = User::factory()->create([
        'role' => 'admin',
    ]);
    // logged in user
    Sanctum::actingAs($user);
    // register user
    $response = User::factory()->count(10)->create([
        'is_active' => true,
    ]);
    $response = $this->patchJson('/api/users/demote_admin/2',[
        'role'=>'admin'
    ]);

    $response->assertStatus(200);
});

// admins only soft delete users
test('admins can soft delete users', function () {
    // create admin
    $user = User::factory()->create([
        'role' => 'admin',
    ]);
    Sanctum::actingAs($user);
    // register user
    $response = User::factory()->count(10)->create();
    $response = $this->patchJson('/api/users/suspend/1', [
        'is_active' => false,
    ]);

    $response->assertStatus(200);
});

// /api/users/unsuspend/2
// unsuspend users
test('admins can unsuspend users', function () {
    // create admin
    $user = User::factory()->create([
        'role' => 'admin',
    ]);
    Sanctum::actingAs($user);
    // register user
    $response = User::factory()->count(10)->create();
    $response = $this->patchJson('/api/users/unsuspend/1', [
        'is_active' => true,

    ]);

    $response->assertStatus(200);
});

// user logs
test('user can view recent activity', function () {
    // create admin
    $user = User::factory()->create();
    // logged in user
    Sanctum::actingAs($user);
    // register user
    $response = $this->getJson('/api/userActivity');

    $response->assertStatus(200);
});
