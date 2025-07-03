<?php

use Illuminate\Support\Facades\Route;



use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\SellerController;
use App\Http\Controllers\API\FeedbackController;
Route::get('/', function () {
    return view('welcome');
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/seller/{id}/profile', [SellerController::class, 'profile']);
    Route::put('/seller/{id}/profile', [SellerController::class, 'update']);

    Route::get('/seller/{id}/feedbacks', [FeedbackController::class, 'getSellerFeedback']);
});
