<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Merchant\AuthController as MerchantAuthController;
use App\Http\Controllers\User\AuthController as UserAuthController;
use App\Http\Controllers\ScanPoint\AuthController as ScanPointAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Admin Authentication Routes
Route::prefix('admin')->group(function () {
    Route::post('/register', [AdminAuthController::class, 'register']);
    Route::post('/login', [AdminAuthController::class, 'login']);
    
    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::get('/profile', [AdminAuthController::class, 'profile']);
        Route::put('/profile', [AdminAuthController::class, 'updateProfile']);
        Route::post('/change-password', [AdminAuthController::class, 'changePassword']);
    });
});

// Merchant Authentication Routes
Route::prefix('merchant')->group(function () {
    Route::post('/register', [MerchantAuthController::class, 'register']);
    Route::post('/login', [MerchantAuthController::class, 'login']);
    
    Route::middleware('auth:merchant')->group(function () {
        Route::post('/logout', [MerchantAuthController::class, 'logout']);
        Route::get('/profile', [MerchantAuthController::class, 'profile']);
        Route::put('/profile', [MerchantAuthController::class, 'updateProfile']);
        Route::post('/change-password', [MerchantAuthController::class, 'changePassword']);
    });
});

// User Authentication Routes
Route::prefix('user')->group(function () {
    Route::post('/register', [UserAuthController::class, 'register']);
    Route::post('/login', [UserAuthController::class, 'login']);
    
    Route::middleware('auth:user')->group(function () {
        Route::post('/logout', [UserAuthController::class, 'logout']);
        Route::get('/profile', [UserAuthController::class, 'profile']);
        Route::put('/profile', [UserAuthController::class, 'updateProfile']);
        Route::post('/change-password', [UserAuthController::class, 'changePassword']);
    });
});

// Scan Point Authentication Routes
Route::prefix('scan-point')->group(function () {
    Route::post('/create', [ScanPointAuthController::class, 'create']);
    Route::post('/login', [ScanPointAuthController::class, 'loginWithToken']);
    
    Route::middleware('auth:scan_point')->group(function () {
        Route::post('/logout', [ScanPointAuthController::class, 'logout']);
        Route::get('/profile', [ScanPointAuthController::class, 'profile']);
        Route::put('/profile', [ScanPointAuthController::class, 'updateProfile']);
        Route::post('/generate-token', [ScanPointAuthController::class, 'generateNewToken']);
    });
}); 