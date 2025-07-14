<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SellerProfileController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\API\PayPalController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/profile', [SellerProfileController::class, 'profile']);
    Route::put('/user/profile', [SellerProfileController::class, 'update']);


     Route::prefix('feedback')->group(function () {
        Route::post('/', [FeedbackController::class, 'store']);                           // Add feedback (by buyer)
        Route::get('/seller/{seller_id}', [FeedbackController::class, 'getSellerFeedback']); // View feedback for seller (anyone)
        Route::get('/seller-logged-in', [FeedbackController::class, 'myFeedbacks']);       // View feedback for current seller
        Route::put('/{id}', [FeedbackController::class, 'update']);                        // Update feedback (buyer only)
        Route::delete('/{id}', [FeedbackController::class, 'destroy']);                    // Delete feedback (buyer only)
    });


    // Materials routes
    Route::prefix('materials')->group(function () {
        Route::get('/', [MaterialController::class, 'index']);
        Route::get('/my-materials', [MaterialController::class, 'myMaterials']);
        Route::post('/', [MaterialController::class, 'store']);
        Route::put('/{id}', [MaterialController::class, 'update']);
        Route::delete('/{id}', [MaterialController::class, 'destroy']);
        Route::get('/latest', [MaterialController::class, 'latest']);
        Route::get('/details/{id}', [MaterialController::class, 'show']);
    });
    // Cart routes
    Route::prefix('cart')->group(function () {
        Route::post('/add', [CartController::class, 'addToCart']);
        Route::delete('/remove/{id}', [CartController::class, 'removeFromCart']);
        Route::delete('/clear', [CartController::class, 'clearCart']);
        Route::get('/', [CartController::class, 'viewCart']);
    });

    Route::post('/paypal/create-order', [PayPalController::class, 'createOrder']);
    Route::post('/paypal/capture-order', [PayPalController::class, 'captureOrder']);

});
Route::get('/paypal/success', [PayPalController::class, 'success'])->name('paypal.success');
Route::get('/paypal/cancel', [PayPalController::class, 'cancel'])->name('paypal.cancel');
