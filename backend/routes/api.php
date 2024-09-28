<?php

use App\Http\Controllers\AuthController;
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
