<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;


// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Profile routes (newly added)
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
});


Route::apiResource('materials', \App\Http\Controllers\Api\MaterialController::class)
    ->middleware('auth:sanctum');