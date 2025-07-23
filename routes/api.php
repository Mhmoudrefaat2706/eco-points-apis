<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SellerProfileController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\API\PayPalController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AdminController;

/*------------------------------------------
| Public Routes (No Authentication Required)
-------------------------------------------*/
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/seller/orders', [OrderController::class, 'getSellerOrders']);
    Route::put('/seller/orders/{id}/status', [OrderController::class, 'updateOrderStatus']);
});

// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Materials
Route::prefix('materials')->group(function () {
    Route::get('/', [MaterialController::class, 'index']);
    Route::get('/latest', [MaterialController::class, 'latest']);
    Route::get('/details/{id}', [MaterialController::class, 'show']);
});

// Categories
Route::get('/categories', [MaterialController::class, 'getCategories']);

// Feedback
Route::prefix('feedback')->group(function () {
    Route::get('/seller/{seller_id}', [FeedbackController::class, 'getSellerFeedback']);
});

// PayPal
Route::get('/paypal/success', [PayPalController::class, 'success'])->name('paypal.success');
Route::get('/paypal/cancel', [PayPalController::class, 'cancel'])->name('paypal.cancel');

/*------------------------------------------
| Protected Routes (Authentication Required)
-------------------------------------------*/
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);

    // User
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Profile
    Route::prefix('user/profile')->group(function () {
        Route::get('/', [SellerProfileController::class, 'profile']);
        Route::put('/', [SellerProfileController::class, 'update']);
    });

    // Materials
    Route::prefix('materials')->group(function () {
        Route::get('/my-materials', [MaterialController::class, 'myMaterials']);
        Route::post('/', [MaterialController::class, 'store']);
        Route::put('/{id}', [MaterialController::class, 'update']);
        Route::delete('/{id}', [MaterialController::class, 'destroy']);
        Route::post('/upload-image', [MaterialController::class, 'uploadImage']);
    });

    // Feedback
    Route::prefix('feedback')->group(function () {
        Route::post('/', [FeedbackController::class, 'store']);
        Route::get('/seller-logged-in', [FeedbackController::class, 'myFeedbacks']);
        Route::put('/{id}', [FeedbackController::class, 'update']);
        Route::delete('/{id}', [FeedbackController::class, 'destroy']);
    });

    // Cart
    Route::prefix('cart')->group(function () {
        Route::post('/add', [CartController::class, 'addToCart']);
        Route::delete('/remove/{id}', [CartController::class, 'removeFromCart']);
        Route::delete('/clear', [CartController::class, 'clearCart']);
        Route::get('/', [CartController::class, 'viewCart']);
        Route::post('/checkout', [CartController::class, 'checkout']);
    });

    // Orders
    Route::prefix('orders')->group(function () {
        Route::get('/user', [OrderController::class, 'getUserOrders']);
        Route::get('/seller', [OrderController::class, 'getSellerOrders']);
        Route::put('/{id}/status', [OrderController::class, 'updateOrderStatus']);
    });

    // PayPal
    Route::post('/paypal/create-order', [PayPalController::class, 'createOrder']);
    Route::post('/paypal/capture-order', [PayPalController::class, 'captureOrder']);
});

/*------------------------------------------
| Admin Routes (Authentication + Admin Role Required)
-------------------------------------------*/
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    // Dashboard statistics
    Route::get('/dashboard', [AdminController::class, 'getDashboardStats']);

    // Users management
    Route::get('/users', [AdminController::class, 'getUsers']);
    Route::post('/users', [AdminController::class, 'createUser']);
    Route::put('/users/{id}/block', [AdminController::class, 'blockUser']);
    Route::put('/users/{id}/unblock', [AdminController::class, 'unblockUser']);
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);

    // Materials management
    Route::get('/materials', [AdminController::class, 'getAllMaterials']);
    Route::delete('/materials/{id}', [AdminController::class, 'deleteMaterial']);
    Route::put('/materials/{id}/block', [AdminController::class, 'blockMaterial']);
    Route::put('/materials/{id}/unblock', [AdminController::class, 'unblockMaterial']);
    Route::put('/materials/{id}/status', [AdminController::class, 'updateMaterialStatus']);

    // Feedback management
    Route::get('/feedbacks', [AdminController::class, 'getAllFeedbacks']);
    Route::delete('/feedbacks/{id}', [AdminController::class, 'deleteFeedback']);
});

Route::put('/orders/{id}/cancel', [OrderController::class, 'cancelOrder'])->middleware('auth:sanctum');

Route::put('/cart/update/{id}', [CartController::class, 'updateCartItem'])->middleware('auth:sanctum');