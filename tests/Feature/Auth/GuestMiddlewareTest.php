<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated users cannot access login page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/login');

    $response->assertRedirect(route('conversations.index'));
});

test('authenticated users CAN access register page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/register');

    // Should be allowed to create another account
    $response->assertOk();
});

test('authenticated users cannot access forgot password page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/forgot-password');

    $response->assertRedirect(route('conversations.index'));
});

test('guests can access login page', function () {
    $response = $this->get('/login');

    $response->assertOk();
});

test('guests can access register page', function () {
    $response = $this->get('/register');

    $response->assertOk();
});

test('authenticated user creating new account logs out of old account and logs into new one', function () {
    $oldUser = User::factory()->create(['email' => 'old@example.com']);

    $response = $this->actingAs($oldUser)->post('/register', [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertRedirect(route('conversations.index'));
    $response->assertSessionHasNoErrors();

    // Verify new user was created
    $newUser = User::where('email', 'new@example.com')->first();
    $this->assertNotNull($newUser);
    $this->assertEquals('New User', $newUser->name);

    // Verify we're not still logged in as the old user
    $this->assertNotEquals($oldUser->id, $newUser->id);
});
