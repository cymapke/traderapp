<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderBookController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);       
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'index']);    // GET /api/profile
        Route::post('/refresh', [ProfileController::class, 'refresh']); // POST /api/profile/refresh
    });

    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orderbook', [OrderBookController::class, 'index']);

    Route::get('/user/holdings', [OrderController::class, 'getUserHoldings']);
    Route::get('/user/balance', [OrderController::class, 'getAvailableBalance']);     
});

// Test route
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working',
        'version' => 'Laravel ' . app()->version(),
    ]);
});
