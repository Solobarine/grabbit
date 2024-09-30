<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

it('can create a product option', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $token = Auth::attempt(['email' => $user->email, 'password' => 'password']);

    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $data = [
        'product_id' => $product->id,
        'option' => 'Color',
        'name' => 'blue',
        'quantity' => 50,
        'price' => 4
    ];

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson("/api/product-options", $data);

    $response->assertStatus(201)
        ->assertJson([
            'status' => true,
            'productOption' => [
                'option' => 'Color',
                'name' => 'blue',
                'quantity' => 50,
                'price' => 4
            ]
        ]);

    $this->assertDatabaseHas('product_options', [
        'option' => 'Color',
        'name' => 'blue'
    ]);
});

it('cannot create a product option without Authorization', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $data = [
        'product_id' => $product->id,
        'option' => 'Color',
        'name' => 'green',
        'quantity' => 50,
        'price' => 40
    ];

    $response = $this->postJson("/api/product-options", $data);

    $response->assertStatus(403)
        ->assertJson([
            'status' => false,
        ]);

    $this->assertDatabaseMissing('product_options', [
        'option' => 'Color',
        'name' => 'green',
        'price' => 40
    ]);
});


it('cannot create a product option with invalid params', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $token = Auth::attempt(['email' => $user->email, 'password' => 'password']);

    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $data = [
        'product_id' => $product->id,
        'option' => 8,
        'name' => 'blue',
        'quantity' => 50,
        'price' => 'four'
    ];

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson("/api/product-options", $data);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'status',
            'error' => [
                'option',
                'price'
            ]
        ]);

    $this->assertDatabaseMissing('product_options', [
        'option' => 8,
        'name' => 'blue'
    ]);
});

it('can update product option', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $token = Auth::attempt(['email' => $user->email, 'password' => 'password']);

    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $productOption = ProductOption::factory()->create(['product_id' => $product->id]);

    $data = [
        'name' => '#657',
        'quantity' => 50,
        'price' => 30.43
    ];

    $response = $this->withHeader('Authorization', "Bearer $token")->patchJson("/api/product-options/$productOption->id", $data);

    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'message' => 'Product Option updated sucessfully',
            'productOption' => [
                'name' => '#657',
                'quantity' => 50,
                'price' => 30.43
            ]
        ]);
});

it('cannot update product option without Authorization', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $productOption = ProductOption::factory()->create(['product_id' => $product->id]);

    $data = [
        'name' => '#657',
        'quantity' => 50,
        'price' => 30.43
    ];

    $response = $this->patchJson("/api/product-options/$productOption->id", $data);

    $response->assertStatus(403)
        ->assertJsonStructure([
            'status',
            'error'
        ]);
});

it('cannot update product option when invalid params present', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $token = Auth::attempt(['email' => $user->email, 'password' => 'password']);

    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $productOption = ProductOption::factory()->create(['product_id' => $product->id]);

    $data = [
        'name' => '#657',
        'quantity' => 50,
        'price' => "fourty"
    ];

    $response = $this->withHeader('Authorization', "Bearer $token")->patchJson("/api/product-options/$productOption->id", $data);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'status',
            'error' => ['price']
        ]);
});

it('can delete a product option', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $token = Auth::attempt(['email' => $user->email, 'password' => 'password']);

    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $productOption = ProductOption::factory()->create(['product_id' => $product->id]);

    $response = $this->withHeader('Authorization', "Bearer $token")->deleteJson("/api/product-options/$productOption->id");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'productOption'
        ]);
    $this->assertDatabaseMissing('product_options', [
        'id' => $productOption->id,
        'name' => $productOption->name
    ]);
});

it('cannot delete a product option without Authorization', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $productOption = ProductOption::factory()->create(['product_id' => $product->id]);

    $response = $this->deleteJson("/api/product-options/$productOption->id");

    $response->assertStatus(403)
        ->assertJsonStructure([
            'status',
            'error'
        ]);
});
