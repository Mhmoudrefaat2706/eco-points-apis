<?php
use Illuminate\Support\Facades\Route;
Route::get('/debug-paypal', function () {
    return [
        'client_id' => config('paypal.sandbox.client_id'),
        'client_secret' => config('paypal.sandbox.client_secret'),
        'mode' => config('paypal.mode'),
    ];
});
