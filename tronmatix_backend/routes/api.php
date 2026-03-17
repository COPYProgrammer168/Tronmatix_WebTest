<?php

// routes/api.php

use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\CheckPaymentController;
use App\Http\Controllers\Api\DeliveryScheduleController;
use App\Http\Controllers\Api\DiscountController;
use App\Http\Controllers\Api\GenerateKhqrController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/banners', [BannerController::class, 'index']);

Route::get('/delivery-schedules', [DeliveryScheduleController::class, 'index']);
Route::get('/discounts/public', [DiscountController::class, 'storefront']); // storefront badge display — no auth
Route::post('/apply-discount', [DiscountController::class, 'apply']);
Route::post('/chat/message', [ChatController::class, 'message']);

// ABA PayWay webhook — MUST be public (no auth).
// ABA servers POST here after every transaction — they send no Sanctum cookie.
Route::post('/payment/webhook', [CheckPaymentController::class, 'webhook']);

// ── Protected (requires login) ────────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);  // FIX: was missing — delete cancelled orders
    Route::post('/orders/{order}/confirm-delivery', [OrderController::class, 'confirmDelivery']);

    // Payment
    Route::post('/payment/generate-qr', [GenerateKhqrController::class, 'generate']);
    Route::post('/payment/verify', [CheckPaymentController::class, 'verify']);
    Route::post('/payment/confirm-manual', [CheckPaymentController::class, 'confirmManual']);

    // Discounts CRUD — admin
    Route::get('/discounts', [DiscountController::class, 'index']);
    Route::post('/discounts', [DiscountController::class, 'store']);
    Route::put('/discounts/{discount}', [DiscountController::class, 'update']);
    Route::delete('/discounts/{discount}', [DiscountController::class, 'destroy']);
    Route::patch('/discounts/{discount}/badge', [DiscountController::class, 'saveBadge']);

    Route::get('/user/profile', [UserProfileController::class, 'show']);
    Route::put('/user/profile', [UserProfileController::class, 'update']);
    Route::get('/user/stats', [UserProfileController::class, 'stats']);
    Route::get('/user/locations', [UserProfileController::class, 'locations']);
    Route::post('/user/locations', [UserProfileController::class, 'storeLocation']);
    Route::put('/user/locations/{id}', [UserProfileController::class, 'updateLocation']);
    Route::delete('/user/locations/{id}', [UserProfileController::class, 'destroyLocation']);
});
