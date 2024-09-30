<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

// Refresh the database between tests
uses(RefreshDatabase::class);

// Test the registration of a user
it('registers a user successfully', function () {
    $response = $this->postJson('/api/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'tos' => true,
        'password' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'status' => true,
            'message' => 'User Registered Successfully',
        ]);

    expect(User::where('email', 'john.doe@example.com')->exists())->toBeTrue();
});

// Test validation failure on registration
it('fails validation on registration', function () {
    $response = $this->postJson('/api/register', [
        'first_name' => '',
        'last_name' => '',
        'email' => 'invalid-email',
        'password' => '123',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'status',
        ]);
});

// Test login success
it('logs in a user successfully', function () {
    User::factory()->create([
        'email' => 'jane.doe@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'jane.doe@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
        ]);
});

// Test login failure with incorrect credentials
it('fails login with incorrect credentials', function () {
    User::factory()->create([
        'email' => 'jane.doe@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'jane.doe@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'error' => 'Unauthorized',
        ]);
});

// Test fetching authenticated user
it('fetches the authenticated user', function () {
    $user = User::factory()->create();
    $token = auth()->attempt(['email' => $user->email, 'password' => 'password']);

    $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/me');

    $response->assertStatus(200)
        ->assertJson([
            'id' => $user->id,
            'email' => $user->email,
        ]);
});

// Test logging out the user
it('logs out the user successfully', function () {
    $user = User::factory()->create();
    $token = auth()->attempt(['email' => $user->email, 'password' => 'password']);

    $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Successfully logged out',
        ]);
});

// Test token refresh
it('refreshes the token', function () {
    $user = User::factory()->create();
    $token = auth()->attempt(['email' => $user->email, 'password' => 'password']);

    $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/refresh');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
        ]);
});
