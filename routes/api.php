<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SellerProfileController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\MaterialController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/seller/{id}/profile', [SellerProfileController::class, 'profile']);
    Route::put('/seller/{id}/profile', [SellerProfileController::class, 'update']);

    Route::prefix('feedback')->group(function () {
        Route::post('/', [FeedbackController::class, 'store']);
        Route::get('/seller/{seller_id}', [FeedbackController::class, 'getSellerFeedback']);
        Route::put('/{id}', [FeedbackController::class, 'update']);
        Route::delete('/{id}', [FeedbackController::class, 'destroy']);
    });

    Route::prefix('materials')->group(function () {
    Route::get('/', [MaterialController::class, 'index']);
    Route::get('/my-materials', [MaterialController::class, 'myMaterials']);
    Route::post('/', [MaterialController::class, 'store']);
    Route::put('/{id}', [MaterialController::class, 'update']);
    Route::delete('/{id}', [MaterialController::class, 'destroy']);
    Route::get('/latest', [MaterialController::class, 'latest']);
    Route::get('/details/{id}', [MaterialController::class, 'show']);
    });

});



