<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProductOptionController;
use App\Http\Middleware\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('register', 'register');
    Route::post('/logout', 'logout');
    Route::get('/me', 'me')->middleware('api');
    Route::get('/refresh', 'refresh')->middleware('api');
});

Route::controller(CategoryController::class)->middleware('api')->group(function () {
    Route::get('/categories', 'index');
    Route::post('/categories', 'store')->middleware(Admin::class);
    Route::get('/categories/{category}', 'show');
    Route::patch('/categories/{category}', 'update')->middleware(Admin::class);
    Route::patch('/categories/{category}/update-image', 'updateImage')->middleware(Admin::class);
    Route::delete('/categories/{category}', 'destroy')->middleware(Admin::class);
});

Route::controller(ProductController::class)->middleware(['api', Admin::class])->group(function () {
    Route::get('/products', 'index')->withoutMiddleware(['api', Admin::class]);
    Route::get('/products/{product}', 'show')->withoutMiddleware(['api', Admin::class]);
    Route::post('/products', 'store');
    Route::patch('/products/{product}', 'update');
    Route::delete('/products/{product}', 'destroy');
});

Route::controller(ProductImageController::class)->middleware(['api', Admin::class])->group(function () {
    Route::patch('/product-images/{productImage}', 'update');
});

Route::controller(ProductOptionController::class)->middleware(['api', Admin::class])->group(function () {
    Route::post('/product-options', 'store');
    Route::patch('/product-options/{productOption}', 'update');
    Route::delete('/product-options/{productOption}', 'destroy');
});
