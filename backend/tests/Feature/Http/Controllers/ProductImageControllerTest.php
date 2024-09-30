<?php

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeAll(function () {
});

it('can update product image', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $token = Auth::attempt(['email' => $user->email, 'password' => 'password']);

    $product = Product::factory()->create(['category_id' => 1]);
    $productImage = ProductImage::factory()->create(['product_id' => $product->id]);

    $data = [
        'image' => UploadedFile::fake()->create('new_image.jpg')
    ];

    $response = $this->withHeader('Authorization', "Bearer $token")->patchJson("/api/product-images/$productImage->id", $data);

    $response->assertStatus(200)
        ->assertJson([
            'status' => true
        ])
        ->assertJsonStructure([
            'status',
            'message',
            'url'
        ]);
});

it('cannot update product image without Authorization', function () {
    $product = Product::factory()->create(['category_id' => 1]);
    $productImage = ProductImage::factory()->create(['product_id' => $product->id]);

    $data = [
        'image' => UploadedFile::fake()->create('new_image.jpg')
    ];

    $response = $this->patchJson("/api/product-images/$productImage->id", $data);

    $response->assertStatus(403)
        ->assertJsonStructure([
            'status',
            'error'
        ]);
});
