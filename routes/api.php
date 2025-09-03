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
use App\Http\Controllers\API\BrandController;
use App\Http\Controllers\Admin\EventManagementController as AdminEventManagementController;
use App\Http\Controllers\Admin\OrganizerManagementController as AdminOrganizerManagementController;
use App\Http\Controllers\Admin\ReportsController as AdminReportsController;
use App\Services\PaymentService;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// PUBLIC ROUTES (No authentication required) - Both /api/events and /api/v1/events work
Route::middleware('throttle:api')->group(function () {
    // Direct access (without version prefix)
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event}', [EventController::class, 'show']);
    Route::get('/event-categories', [EventController::class, 'categories']);
    
    // Version 1 routes (for API versioning)
    Route::prefix('v1')->group(function () {
        Route::get('/events', [EventController::class, 'index']);
        Route::get('/events/{event}', [EventController::class, 'show']);
        Route::get('/event-categories', [EventController::class, 'categories']);
    });
});

// Authentication routes with strict limiting (prevent brute force)
Route::prefix('admin')->middleware('throttle:auth')->group(function () {
    Route::post('/register', [AdminAuthController::class, 'register']);
    Route::post('/login', [AdminAuthController::class, 'login']);
});

Route::prefix('organizer')->middleware('throttle:auth')->group(function () {
    Route::post('/register', [OrganizerAuthController::class, 'register']);
    Route::post('/login', [OrganizerAuthController::class, 'login']);
});

Route::prefix('customer')->middleware('throttle:auth')->group(function () {
    Route::post('/register', [CustomerAuthController::class, 'register']);
    Route::post('/login', [CustomerAuthController::class, 'login']);
});

Route::prefix('scan-point')->middleware('throttle:auth')->group(function () {
    Route::post('/register', [ScanPointAuthController::class, 'create']);
    Route::post('/create', [ScanPointAuthController::class, 'create']);
    Route::post('/login', [ScanPointAuthController::class, 'login']); // Add this new method
    Route::post('/login-with-token', [ScanPointAuthController::class, 'loginWithToken']); // Keep old method
});

// Admin routes with higher limits for authenticated users
// Admin profile routes
Route::prefix('admin')->middleware(['auth:admin', 'ability:admin:profile', 'throttle:api'])->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout']);
    Route::get('/profile', [AdminAuthController::class, 'profile']);
    Route::put('/profile', [AdminAuthController::class, 'updateProfile']);
    Route::post('/change-password', [AdminAuthController::class, 'changePassword']);
});

// Admin event management
Route::prefix('admin')->middleware(['auth:admin', 'ability:admin:events', 'throttle:api'])->group(function () {
    Route::get('/events', [AdminEventManagementController::class, 'index']);
    Route::get('/events/pending', [AdminEventManagementController::class, 'pending']);
    Route::get('/events/{event}', [AdminEventManagementController::class, 'show']);
    Route::post('/events/{event}/approve', [AdminEventManagementController::class, 'approve']);
    Route::post('/events/{event}/reject', [AdminEventManagementController::class, 'reject']);
});

// Admin organizer management
Route::prefix('admin')->middleware(['auth:admin', 'ability:admin:organizers', 'throttle:api'])->group(function () {
    Route::get('/organizers', [AdminOrganizerManagementController::class, 'index']);
    Route::get('/organizers/{organizer}', [AdminOrganizerManagementController::class, 'show']);
    Route::post('/organizers/{organizer}/verify', [AdminOrganizerManagementController::class, 'verify']);
    Route::post('/organizers/{organizer}/deactivate', [AdminOrganizerManagementController::class, 'deactivate']);
});

// Admin reports
Route::prefix('admin')->middleware(['auth:admin', 'ability:admin:reports', 'throttle:api'])->group(function () {
    Route::get('/reports/sales-summary', [AdminReportsController::class, 'salesSummary']);
    Route::get('/reports/platform-metrics', [AdminReportsController::class, 'platformMetrics']);
});

// Organizer profile routes
Route::prefix('organizer')->middleware(['auth:organizer', 'ability:organizer:profile', 'throttle:api'])->group(function () {
    Route::post('/logout', [OrganizerAuthController::class, 'logout']);
    Route::get('/profile', [OrganizerAuthController::class, 'profile']);
    Route::put('/profile', [OrganizerAuthController::class, 'updateProfile']);
    Route::post('/change-password', [OrganizerAuthController::class, 'changePassword']);
});

// Organizer event management routes
Route::prefix('organizer')->middleware(['auth:organizer', 'ability:organizer:events', 'throttle:api'])->group(function () {
    Route::resource('events', EventController::class);
    Route::post('events/{event}/submit-for-approval', [EventController::class, 'submitForApproval']);
    Route::get('my-events', [EventController::class, 'myEvents']);
});

// Organizer brand management routes with strict limiting
Route::prefix('organizer')->middleware(['auth:organizer', 'ability:organizer:brands', 'throttle:uploads'])->group(function () {
    Route::post('events/{event}/cover', [FileUploadController::class, 'uploadEventCover']);
    Route::post('brands/{brand}/logo', [FileUploadController::class, 'uploadBrandLogo']);
    Route::post('cr/upload', [FileUploadController::class, 'uploadOrganizerCr']);
});

// Customer profile routes
Route::prefix('customer')->middleware(['auth:customer', 'ability:customer:profile', 'throttle:api'])->group(function () {
    Route::post('/logout', [CustomerAuthController::class, 'logout']);
    Route::get('/profile', [CustomerAuthController::class, 'profile']);
    Route::put('/profile', [CustomerAuthController::class, 'updateProfile']);
    Route::post('/change-password', [CustomerAuthController::class, 'changePassword']);
});

// Customer order routes
Route::prefix('customer')->middleware(['auth:customer', 'ability:customer:orders', 'throttle:api'])->group(function () {
    Route::resource('orders', OrderController::class);
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('orders/{order}/refund', [OrderController::class, 'requestRefund']);
    Route::post('orders/summary', [OrderController::class, 'summary']);
    Route::get('payments', [PaymentController::class, 'index']);
    Route::post('payments', [PaymentController::class, 'store']);
});

// Customer ticket routes
Route::prefix('customer')->middleware(['auth:customer', 'ability:customer:tickets', 'throttle:api'])->group(function () {
    Route::get('tickets', [TicketController::class, 'index']);
    Route::get('tickets/{ticket}', [TicketController::class, 'show']);
});

// Customer wishlist routes
Route::prefix('customer')->middleware(['auth:customer', 'ability:customer:wishlist', 'throttle:api'])->group(function () {
    Route::resource('wishlist', WishlistController::class);
});

// Scan Point profile routes
Route::prefix('scan-point')->middleware(['auth:scan_point', 'ability:scan_point:profile', 'throttle:scan-point'])->group(function () {
    Route::post('/logout', [ScanPointAuthController::class, 'logout']);
    Route::get('/profile', [ScanPointAuthController::class, 'profile']);
    Route::put('/profile', [ScanPointAuthController::class, 'updateProfile']);
    Route::post('/generate-token', [ScanPointAuthController::class, 'generateNewToken']);
});

// Scan Point scanning routes
Route::prefix('scan-point')->middleware(['auth:scan_point', 'ability:scan_point:scan', 'throttle:scan-point'])->group(function () {
    Route::post('/scan', [ScanController::class, 'scanTicket']);
    Route::post('/validate', [ScanController::class, 'validateTicket']);
    Route::get('/history', [ScanController::class, 'scanHistory']);
    Route::get('/{event}/stats', [ScanController::class, 'scanStats']);
});

// Payments webhooks (no rate limiting - handled by signature verification)
Route::post('/payments/webhook/{gateway}', function (\Illuminate\Http\Request $request, string $gateway, PaymentService $service) {
    $service->handleWebhook($gateway, $request->all(), $request->header('X-Signature'));
    return response()->json(['success' => true]);
});

// Organizer brand management routes (CRUD operations)
Route::prefix('organizer')->middleware(['auth:organizer', 'ability:organizer:brands', 'throttle:api'])->group(function () {
    Route::resource('brands', App\Http\Controllers\API\BrandController::class);
    Route::get('brands/{brand}/usage', [App\Http\Controllers\API\BrandController::class, 'usage']);
    Route::get('brands-dropdown', [App\Http\Controllers\API\BrandController::class, 'dropdown']);
});

// Keep your existing file upload routes
Route::prefix('organizer')->middleware(['auth:organizer', 'ability:organizer:brands', 'throttle:uploads'])->group(function () {
    Route::post('events/{event}/cover', [FileUploadController::class, 'uploadEventCover']);
    Route::post('brands/{brand}/logo', [FileUploadController::class, 'uploadBrandLogo']);
    Route::post('cr/upload', [FileUploadController::class, 'uploadOrganizerCr']);
});

// Health check route
Route::get('/health/database', function () {
    try {
        DB::connection()->getPdo();
        return response()->json(['status' => 'connected', 'driver' => DB::connection()->getDriverName()]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'failed', 'error' => $e->getMessage()], 500);
    }
});

