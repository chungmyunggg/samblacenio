<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;

// Public route for authentication (issuing a token)
Route::post('/login', [AuthController::class, 'login']);

// Protected routes that require a valid token
Route::middleware('auth:sanctum')->group(function () {
    // This route returns the authenticated user's details
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // This route revokes the user's current token
    Route::post('/logout', [AuthController::class, 'logout']);

    // Your existing post routes are now protected by Sanctum.
    // If you want them to be public, move this line outside the middleware group.
    Route::apiResource('posts', PostController::class);
});
