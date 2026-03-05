<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

// Register
Route::post('/register', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    return response()->json($user, 201);
});

// Login
Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'access_token' => $token,
        'token_type' => 'Bearer',
    ]);
});

// Protected route example
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 8. Protect Routes Using Roles
Route::middleware(['auth:sanctum','role:admin'])->get('/admin-dashboard', function () {
    return response()->json([
        'message' => 'Welcome Admin'
    ]);
});

// 9. Protect Routes Using Permission
Route::middleware(['auth:sanctum','permission:create posts'])->post('/posts', function () {
    return response()->json([
        'message' => 'Post created'
    ]);
});
