<?php

use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

it('admin can delete cart', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $token = Auth::attempt(['email' => $user->email, 'password' => 'password']);

    $response = $this->withHeader('Authorization', "Bearer $token")->deleteJson("/api/cart/$cart->id");

    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'cart' => [
                'id' => $cart->id
            ]
        ]);

    $this->assertDatabaseMissing('carts', [
        'id' => $cart->id,
        'user_id' => $user->id
    ]);
});

it('owner can delete cart', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $token = Auth::attempt(['email' => $user->email, 'password' => 'password']);

    $response = $this->withHeader('Authorization', "Bearer $token")->deleteJson("/api/cart/$cart->id");

    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'cart' => [
                'id' =>  $cart->id
            ]
        ]);

    $this->assertDatabaseMissing('carts', [
        'id' => $cart->id,
        'user_id' => $user->id
    ]);
});

it('cannot delete cart unless owner or admin', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $anotherUser = User::factory()->create();
    $token = Auth::attempt(['email' => $anotherUser->email, 'password' => 'password']);

    $response = $this->withHeader('Authorization', "Bearer $token")->deleteJson("/api/cart/$cart->id");

    $response->assertStatus(403);

    $this->assertDatabaseHas('carts', [
        'id' => $cart->id,
        'user_id' => $user->id
    ]);
});
