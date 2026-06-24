<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PageContentController;

// Auth
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/auth/google/redirect', [AuthController::class, 'googleRedirect']);
Route::get('/auth/google/callback', [AuthController::class, 'googleCallback']);

// Public fare rates
Route::get('/fares', [SettingController::class, 'fares']);
Route::get('/destinations', [DestinationController::class, 'index']);
Route::get('/site-settings', [SettingController::class, 'siteSettings']);
Route::post('/chat', [ChatController::class, 'chat']);
Route::get('/pages/{slug}', [PageContentController::class, 'show']);
Route::get('/pages', [PageContentController::class, 'index']);

// Bookings
Route::post('/bookings', [BookingController::class, 'store']);
Route::get('/bookings/{reference}', [BookingController::class, 'show']);
Route::get('/bookings', [BookingController::class, 'index']);
Route::get('/loyalty', [BookingController::class, 'loyalty']);

// M-Pesa
Route::post('/payments/mpesa/initiate', [PaymentController::class, 'mpesaInitiate']);
Route::post('/payments/mpesa/callback', [PaymentController::class, 'mpesaCallback']);
Route::post('/payments/mpesa/query', [PaymentController::class, 'mpesaQuery']);

// Stripe
Route::post('/payments/stripe/intent', [PaymentController::class, 'stripeIntent']);
Route::post('/payments/stripe/webhook', [PaymentController::class, 'stripeWebhook']);
Route::post('/payments/stripe/confirm', [PaymentController::class, 'stripeConfirm']);

// PayPal
Route::post('/payments/paypal/order', [PaymentController::class, 'paypalCreateOrder']);
Route::post('/payments/paypal/capture', [PaymentController::class, 'paypalCapture']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);

    // Admin only
    Route::middleware('is_admin')->group(function () {
        Route::get('/admin/bookings', [BookingController::class, 'adminBookings']);
        Route::get('/admin/users', [BookingController::class, 'adminUsers']);
        Route::post('/admin/destinations', [DestinationController::class, 'store']);
        Route::put('/admin/destinations/{id}', [DestinationController::class, 'update']);
        Route::delete('/admin/destinations/{id}', [DestinationController::class, 'destroy']);
        Route::put('/admin/pages/{slug}', [PageContentController::class, 'update']);
        Route::put('/admin/fares', [SettingController::class, 'updateFares']);
        Route::put('/admin/settings', [SettingController::class, 'updateSettings']);
    });
});
