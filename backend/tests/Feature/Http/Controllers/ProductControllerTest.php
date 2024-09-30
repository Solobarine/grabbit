<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

it('can list products', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $response = $this->getJson('/api/products');

    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'products' => [
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand' => $product->brand,
                    'description' => $product->description
                ]
            ]
        ]);
});

it('creates a product with valid data', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $token = Auth::attempt(['email' => $user->email, 'password' => 'password']);
    $category = Category::factory()->create();

    $requestBody = [
        'images' => [
            UploadedFile::fake()->image('image1.jpg'),
            UploadedFile::fake()->image('image2.png'),
            UploadedFile::fake()->image('image3.avif'),
        ],
        'name' => 'Sample Product',
        'brand' => 'Sample Brand',
        'description' => 'This is a sample product description.',
        'category_id' => $category->id,
        'options' => [
            [
                'title' => 'Color',
                'values' => [
                    [
                        'name' => 'Red',
                        'quantity' => 20,
                        'price' => 15.99,
                    ]
                ],
            ],
        ],
    ];

    $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/products', $requestBody);

    $response->assertStatus(201);
    $response->assertJsonStructure([
        'status',
        'message',
        'product' => [
            'id',
            'name',
            'brand',
            'description',
        ],
    ]);

    $this->assertDatabaseHas('products', [
        'name' => 'Sample Product',
        'brand' => 'Sample Brand',
    ]);

    $this->assertDatabaseHas('product_options', [
        'option' => 'Color',
        'name' => 'Red',
        'quantity' => 20,
        'price' => 15.99,
    ]);

    // Assert images are saved
    //Storage::disk('public')->assertExists('products/image1.jpg');
    //Storage::disk('public')->assertExists('products/image2.png');
    //Storage::disk('public')->assertExists('products/image3.avif');
});

it('returns validation errors when required fields are missing', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $token = Auth::attempt(['email' => $user->email, 'password' => 'password']);

    $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/products', []);

    $response->assertStatus(422);
    $response->assertJsonStructure([
        'status',
        'error' => [
            'images', 'name', 'brand', 'description', 'category_id', 'options'
        ]
    ]);
});

it('returns a 404 error if the category does not exist', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $token = Auth::attempt(['email' => $user->email, 'password' => 'password']);

    $data = [
        'images' => [
            UploadedFile::fake()->image('image1.jpg'),
        ],
        'name' => 'Sample Product',
        'brand' => 'Sample Brand',
        'description' => 'Sample product description',
        'category_id' => 9999, // Non-existing category ID
        'options' => [
            [
                'title' => 'Color',
                'values' => [
                    [
                        'name' => 'Red',
                        'quantity' => 20,
                        'price' => 15.99,
                    ]
                ],
            ],
        ],
    ];

    $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/products', $data);

    $response->assertStatus(404);
    $response->assertJson([
        'status' => false,
        'error' => 'Category with id not found',
    ]);
});

it('returns validation errors for invalid file types in images', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $token = Auth::attempt(['email' => $user->email, 'password' => 'password']);

    $category = Category::factory()->create();

    $requestBody = [
        'images' => [
            UploadedFile::fake()->create('document.pdf', 100),
        ],
        'name' => 'Sample Product',
        'brand' => 'Sample Brand',
        'description' => 'Sample product description',
        'category_id' => $category->id,
        'options' => [
            [
                'title' => 'Color',
                'values' => [
                    [
                        'name' => 'Red',
                        'quantity' => 20,
                        'price' => 15.99,
                    ]
                ],
            ],
        ],
    ];

    $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/products', $requestBody);

    $response->assertStatus(422);
    $response->assertJsonStructure(['status', 'error']);
});

it('can update a product', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $token = Auth::attempt(['email' => $user->email, 'password' => 'password']);
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $data = [
        'name' => 'Updated Name'
    ];

    $response = $this->withHeader('Authorization', "Bearer $token")->patchJson("/api/products/$product->id", $data);

    $response->assertStatus(200)
        ->assertJson(['status' => true])
        ->assertJsonStructure([
            'status',
            'message',
            'product'
        ]);
});


it('cannot update a product without Authorization', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $data = [
        'name' => 'Updated Name'
    ];

    $response = $this->patchJson("/api/products/$product->id", $data);

    $response->assertStatus(403)
        ->assertJson(['status' => false])
        ->assertJsonStructure([
            'status',
            'error'
        ]);
});

it('can delete a product', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $token = Auth::attempt(['email' => $user->email, 'password' => 'password']);
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $response = $this->withHeader('Authorization', "Bearer $token")->deleteJson("/api/products/$product->id");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'product'
        ]);
    $this->assertDatabaseMissing('products', [
        'name' => $product->name
    ]);
});
