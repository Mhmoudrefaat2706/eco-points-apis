<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\FeedbackController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/seller/{id}/profile', [SellerController::class, 'profile']);
    Route::put('/seller/{id}/profile', [SellerController::class, 'update']);

    Route::get('/seller/{id}/feedbacks', [FeedbackController::class, 'getSellerFeedback']);
});
