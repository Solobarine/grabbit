<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('can list categories', function () {
    // Arrange
    $category = Category::factory()->create();

    // Act
    $response = $this->getJson('/api/categories');

    // Assert
    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'categories' => [
                [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image' => $category->image,
                ]
            ],
        ]);
});

it('can store a new category', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $token = auth()->attempt(['email' => $user->email, 'password' => 'password']);
    Storage::fake('local');

    // Arrange
    $data = [
        'name' => 'New Category',
        'image' => UploadedFile::fake()->image('category.jpg'),
    ];

    // Act
    $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/categories', $data);

    // Assert
    $response->assertStatus(201)
        ->assertJson([
            'status' => true,
            'category' => [
                'name' => 'New Category',
            ],
        ]);

    $this->assertDatabaseHas('categories', [
        'name' => 'New Category',
    ]);
});

it('cannot store category without required fields', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $token = auth()->attempt(['email' => $user->email, 'password' => 'password']);
    // Arrange
    $data = [
        'name' => '',
    ];

    // Act
    $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/categories', $data);

    // Assert
    $response->assertStatus(422)
        ->assertJsonStructure([
            'status',
            'error' => ['name'],
        ]);
});

it('can show a category', function () {
    // Arrange
    $category = Category::factory()->create();

    // Act
    $response = $this->getJson("/api/categories/{$category->id}");

    // Assert
    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'image' => $category->image,
            ],
        ]);
});

it('can update a category', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $token = auth()->attempt(['email' => $user->email, 'password' => 'password']);
    // Arrange
    $category = Category::factory()->create();
    $data = [
        'name' => 'Updated Category',
    ];

    // Act
    $response = $this->withHeader("Authorization", "Bearer $token")->patchJson("/api/categories/{$category->id}", $data);

    // Assert
    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'category' => [
                'id' => $category->id,
                'name' => 'Updated Category',
            ],
        ]);

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Updated Category',
    ]);
});

it('can update a category image', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $token = auth()->attempt(['email' => $user->email, 'password' => 'password']);

    Storage::fake('local');

    // Arrange
    $category = Category::factory()->create();
    $image = UploadedFile::fake()->image('new-image.jpg');

    // Act
    $response = $this->withHeader('Authorization', "Bearer $token")->patchJson("/api/categories/{$category->id}/update-image", ['image' => $image]);

    // Assert
    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'message' => 'Image updated successfully',
        ]);

    Storage::assertExists($response->json('category.image'));
});

it('can delete a category', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $token = auth()->attempt(['email' => $user->email, 'password' => 'password']);

    // Arrange
    $category = Category::factory()->create();

    // Act
    $response = $this->withHeader('Authorization', "Bearer $token")->deleteJson("/api/categories/{$category->id}");

    // Assert
    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'category' => [
                'id' => $category->id,
            ],
        ]);

    $this->assertDatabaseMissing($category);
});
