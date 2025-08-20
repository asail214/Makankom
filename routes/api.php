<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Organizer\AuthController as OrganizerAuthController;
use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\ScanPoint\AuthController as ScanPointAuthController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ScanController;
use App\Http\Controllers\API\TicketController;
use App\Http\Controllers\API\WishlistController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\FileUploadController;
use App\Http\Controllers\Admin\EventManagementController as AdminEventManagementController;
use App\Http\Controllers\Admin\OrganizerManagementController as AdminOrganizerManagementController;
use App\Http\Controllers\Admin\ReportsController as AdminReportsController;
use App\Services\PaymentService;

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

        // Admin features
        Route::get('/events', [AdminEventManagementController::class, 'index']);
        Route::get('/events/pending', [AdminEventManagementController::class, 'pending']);
        Route::get('/events/{event}', [AdminEventManagementController::class, 'show']);
        Route::post('/events/{event}/approve', [AdminEventManagementController::class, 'approve']);
        Route::post('/events/{event}/reject', [AdminEventManagementController::class, 'reject']);

        Route::get('/organizers', [AdminOrganizerManagementController::class, 'index']);
        Route::get('/organizers/{organizer}', [AdminOrganizerManagementController::class, 'show']);
        Route::post('/organizers/{organizer}/verify', [AdminOrganizerManagementController::class, 'verify']);
        Route::post('/organizers/{organizer}/deactivate', [AdminOrganizerManagementController::class, 'deactivate']);

        Route::get('/reports/sales-summary', [AdminReportsController::class, 'salesSummary']);
        Route::get('/reports/platform-metrics', [AdminReportsController::class, 'platformMetrics']);
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

// Public v1 routes
Route::prefix('v1')->group(function () {
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event}', [EventController::class, 'show']);
    Route::get('/event-categories', [EventController::class, 'categories']);
});

// Organizer routes (protected)
Route::prefix('organizer')->middleware('auth:organizer')->group(function () {
    Route::resource('events', EventController::class);
    Route::post('events/{event}/submit-for-approval', [EventController::class, 'submitForApproval']);
    Route::get('my-events', [EventController::class, 'myEvents']);

    // File uploads
    Route::post('events/{event}/cover', [FileUploadController::class, 'uploadEventCover']);
    Route::post('brands/{brand}/logo', [FileUploadController::class, 'uploadBrandLogo']);
    Route::post('cr/upload', [FileUploadController::class, 'uploadOrganizerCr']);
});

// Customer routes (protected)
Route::prefix('customer')->middleware('auth:customer')->group(function () {
    Route::resource('orders', OrderController::class);
    Route::resource('wishlist', WishlistController::class);
    Route::get('tickets', [TicketController::class, 'index']);
    Route::get('tickets/{ticket}', [TicketController::class, 'show']);
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('orders/{order}/refund', [OrderController::class, 'requestRefund']);
    Route::post('orders/summary', [OrderController::class, 'summary']);
    Route::get('payments', [PaymentController::class, 'index']);
    Route::post('payments', [PaymentController::class, 'store']);
});

// Payments webhooks (public endpoint with secret verification inside service)
Route::post('/payments/webhook/{gateway}', function (\Illuminate\Http\Request $request, string $gateway, PaymentService $service) {
    $service->handleWebhook($gateway, $request->all(), $request->header('X-Signature'));
    return response()->json(['success' => true]);
});

// Scan Point routes (protected)
Route::prefix('scan-point')->middleware('auth:scan_point')->group(function () {
    Route::post('/scan', [ScanController::class, 'scanTicket']);
    Route::post('/validate', [ScanController::class, 'validateTicket']);
    Route::get('/history', [ScanController::class, 'scanHistory']);
    Route::get('/{event}/stats', [ScanController::class, 'scanStats']);
});