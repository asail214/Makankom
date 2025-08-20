<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Organizer\AuthController as OrganizerAuthController;
use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
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

// Organizer Authentication Routes
Route::prefix('organizer')->group(function () {
    Route::post('/register', [OrganizerAuthController::class, 'register']);
    Route::post('/login', [OrganizerAuthController::class, 'login']);
    
    Route::middleware('auth:organizer')->group(function () {
        Route::post('/logout', [OrganizerAuthController::class, 'logout']);
        Route::get('/profile', [OrganizerAuthController::class, 'profile']);
        Route::put('/profile', [OrganizerAuthController::class, 'updateProfile']);
        Route::post('/change-password', [OrganizerAuthController::class, 'changePassword']);
    });
});

// Customer Authentication Routes
Route::prefix('customer')->group(function () {
    Route::post('/register', [CustomerAuthController::class, 'register']);
    Route::post('/login', [CustomerAuthController::class, 'login']);
    
    Route::middleware('auth:customer')->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout']);
        Route::get('/profile', [CustomerAuthController::class, 'profile']);
        Route::put('/profile', [CustomerAuthController::class, 'updateProfile']);
        Route::post('/change-password', [CustomerAuthController::class, 'changePassword']);
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