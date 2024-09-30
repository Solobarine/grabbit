<?php

use App\Models\Cart;
use App\Models\User;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\ProductOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Setup test user and product for reuse
    $category = Category::factory()->create();
    $this->user = User::factory()->create();
    $this->cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $this->product = Product::factory()->create(['category_id' => $category->id]);
});

it('can add item to cart', function () {
    $token = Auth::attempt(['email' => $this->user->email, 'password' => 'password']);


    $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/cart-items', [
        'product_id' => $this->product->id,
        'quantity' => 2,
        'options' => [
            ['id' => 1],
        ],
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'message' => 'Item added to cart',
        ]);

    $this->assertDatabaseHas('cart_items', [
        'product_id' => $this->product->id,
        'quantity' => 2,
    ]);
});

it('validates the add item to cart request', function () {
    $token = Auth::attempt(['email' => $this->user->email, 'password' => 'password']);

    // Missing fields
    $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/cart-items', [
        'quantity' => 2,
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure(['status', 'error']);
});

it('can update cart item', function () {
    $token = Auth::attempt(['email' => $this->user->email, 'password' => 'password']);

    $cartItem = CartItem::factory()->create(['product_id' => $this->product->id, 'cart_id' => $this->cart->id]);

    $response = $this->withHeader('Authorization', "Bearer $token")->patchJson("/api/cart-items/{$cartItem->id}", [
        'options' => [
            ['id' => 2],
        ],
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'message' => 'Item updated successfully',
        ]);
});

it('validates the update cart item request', function () {
    $token = Auth::attempt(['email' => $this->user->email, 'password' => 'password']);

    $cartItem = CartItem::factory()->create(['product_id' => $this->product->id, 'cart_id' => $this->cart->id]);

    // Invalid options data
    $response = $this->withHeader('Authorization', "Bearer $token")->patchJson("/api/cart-items/{$cartItem->id}", [
        'options' => 'not-an-array',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure(['status', 'error']);
});

it('can update cart item quantity', function () {
    $token = Auth::attempt(['email' => $this->user->email, 'password' => 'password']);

    $cartItem = CartItem::factory()->create(['product_id' => $this->product->id, 'cart_id' => $this->cart->id]);

    $response = $this->withHeader('Authorization', "Bearer $token")->patchJson("/api/cart-items/{$cartItem->id}/quantity", [
        'quantity' => 5,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'message' => 'Item updated successfully',
        ]);

    $this->assertDatabaseHas('cart_items', [
        'id' => $cartItem->id,
        'quantity' => 5,
    ]);
});

it('validates the update cart item quantity request', function () {
    $token = Auth::attempt(['email' => $this->user->email, 'password' => 'password']);
    $cartItem = CartItem::factory()->create(['product_id' => $this->product->id, 'cart_id' => $this->cart->id]);

    // Invalid quantity
    $response = $this->withHeader('Authorization', "Bearer $token")->patchJson("/api/cart-items/{$cartItem->id}/quantity", [
        'quantity' => -1,
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure(['status', 'error']);
});

it('can remove cart item', function () {
    $token = Auth::attempt(['email' => $this->user->email, 'password' => 'password']);
    $optionOne = ProductOption::factory()->create(['product_id' => $this->product->id]);

    $cartItem = CartItem::factory()->create(['product_id' => $this->product->id, 'cart_id' => $this->cart->id, 'options' => [['id' => $optionOne->id]]]);

    $response = $this->withHeader('Authorization', "Bearer $token")->deleteJson("/api/cart-items/$cartItem->id");

    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'message' => 'Item removed from cart',
        ]);

    $this->assertDatabaseMissing('cart_items', [
        'id' => $cartItem->id,
    ]);
});
