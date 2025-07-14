<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SellerProfileController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\CartController;


// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public material routes
Route::prefix('materials')->group(function () {
    Route::get('/', [MaterialController::class, 'index']); // List all materials
    Route::get('/latest', [MaterialController::class, 'latest']); // Get latest materials
    Route::get('/details/{id}', [MaterialController::class, 'show']); // Get material details
});

Route::get('/categories', [MaterialController::class, 'getCategories']); // Get all categories

// Public feedback routes
Route::prefix('feedback')->group(function () {
    Route::get('/seller/{seller_id}', [FeedbackController::class, 'getSellerFeedback']); // View feedback for seller
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Profile routes
    Route::prefix('user/profile')->group(function () {
        Route::get('/', [SellerProfileController::class, 'profile']);
        Route::put('/', [SellerProfileController::class, 'update']);
    });

    // Protected material routes
    Route::prefix('materials')->group(function () {
        Route::get('/my-materials', [MaterialController::class, 'myMaterials']); // Get seller's materials
        Route::post('/', [MaterialController::class, 'store']); // Add new material
        Route::put('/{id}', [MaterialController::class, 'update']); // Update material
        Route::delete('/{id}', [MaterialController::class, 'destroy']); // Delete material
    });

    // Protected feedback routes
    Route::prefix('feedback')->group(function () {
        Route::post('/', [FeedbackController::class, 'store']); // Add feedback
        Route::get('/seller-logged-in', [FeedbackController::class, 'myFeedbacks']); // View logged-in seller's feedback
        Route::put('/{id}', [FeedbackController::class, 'update']); // Update feedback
        Route::delete('/{id}', [FeedbackController::class, 'destroy']); // Delete feedback
    });

    // Image upload
    Route::post('/upload-image', [MaterialController::class, 'uploadImage']);
});