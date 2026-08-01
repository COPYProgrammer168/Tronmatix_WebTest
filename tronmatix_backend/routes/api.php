<?php

// routes/api.php

use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\CheckPaymentController;
use App\Http\Controllers\Api\DeliveryScheduleController;
use App\Http\Controllers\Api\DiscountController;
use App\Http\Controllers\Api\GenerateKhqrController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TelegramAuthController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\TelegramBotController;
use App\Http\Controllers\Api\TelegramController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\MarqueeController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\StaffAuthController;
use App\Http\Controllers\Auth\DevAuthController;
use App\Http\Controllers\Api\AdminStatsController;
use App\Http\Controllers\Api\DevToolsController;
use App\Http\Controllers\Dashboard\ActivityLogController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/auth/reset-by-phone', [AuthController::class, 'resetByPhone']);

// Social auth — public (returns token + is_new_user flag)
Route::post('/auth/google', [GoogleAuthController::class, 'handleCallback']);
Route::post('/auth/telegram', [TelegramAuthController::class, 'handleCallback']);
Route::post('/auth/telegram-generate-token', [TelegramAuthController::class, 'generateLoginToken']);
Route::get('/auth/telegram-status',          [TelegramAuthController::class, 'checkLoginToken']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/banners', [BannerController::class, 'index']);
Route::get('/videos', [VideoController::class, 'index']);
Route::get('/marquees', [MarqueeController::class, 'index']);
Route::get('/provinces', [DeliveryController::class, 'provinces']);
Route::get('/delivery-providers', [DeliveryController::class, 'deliveryProviders']);

// Staff product management — gated by staff role middleware inside the protected block later

Route::get('/delivery-schedules', [DeliveryScheduleController::class, 'index']);
Route::get('/discounts/public', [DiscountController::class, 'storefront']);
Route::post('/apply-discount', [DiscountController::class, 'apply']);

Route::post('/chat/message', [ChatController::class, 'message']);

// ABA PayWay webhook — public, no auth (ABA server calls this)
Route::post('/payment/webhook', [CheckPaymentController::class, 'webhook']);

// Telegram Bot 2 webhook — public, no auth (Telegram calls this)
Route::post('/telegram/bot-webhook', [TelegramBotController::class, 'webhook']);

// Staff & Dev portals — rate limited separately (10 attempts/min per IP)
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/staff/login', [StaffAuthController::class, 'login']);
    Route::post('/dev/login',   [DevAuthController::class,   'login']);
});

// ── Protected (requires Sanctum login) ───────────────────────────────────────
// Base throttle 60 req/min per user — generous guard against runaway abuse.
// Tighter per-group throttles below for sensitive mutation endpoints.

Route::middleware(['auth:sanctum', 'not_banned', 'throttle:60,1'])->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Firebase one-time phone verification (customer)
    Route::post('/verify-phone', [UserProfileController::class, 'verifyPhone']);

    // Portal-aware session restore — a staff/dev token's tokenable is a Staff row,
    // so the customer /auth/me endpoint (users table) would 401. Return the correct
    // payload based on the authenticated model instead.
    Route::get('/portal/me', [AuthController::class, 'portalMe']);

    // Admin stats — all staff roles + superadmin
    Route::middleware('role:admin,superadmin,editor,seller,delivery,developer')->group(function () {
        Route::get('/admin/stats', [AdminStatsController::class, 'stats']);
        Route::get('/admin/users', [AdminStatsController::class, 'users']);
    });

    // Activity logs — admin + superadmin only
    Route::middleware('role:admin,superadmin')->group(function () {
        Route::get('/activity-logs', [ActivityLogController::class, 'index']);
        Route::get('/activity-logs/stats', [ActivityLogController::class, 'stats']);
    });

    // Staff heartbeat — keep online status alive (all staff roles)
    Route::middleware('role:admin,superadmin,editor,seller,delivery,developer')->group(function () {
        Route::post('/staff/heartbeat', [App\Http\Controllers\Dashboard\StaffController::class, 'heartbeat']);
    });

    // Dev tools — developer only
    Route::middleware('role:developer')->group(function () {
        Route::get('/dev/health', [DevToolsController::class, 'health']);
        Route::get('/dev/logs',   [DevToolsController::class, 'logs']);
        Route::get('/dev/env',    [DevToolsController::class, 'env']);
    });

    // Staff product management — create, update, delete
    Route::middleware('role:admin,superadmin,editor,seller,delivery,developer')->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    });

    // Orders — read (60/min via parent), mutations (20/min)
    Route::middleware('throttle:20,1')->group(function () {
        Route::post('/orders', [OrderController::class, 'store']);
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
        Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
        Route::post('/orders/{order}/confirm-delivery', [OrderController::class, 'confirmDelivery']);
    });
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);

    // Staff order management — update status, verify payment, force delivery
    Route::middleware('role:admin,superadmin,editor,seller,delivery,developer')->group(function () {
        Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);
        Route::post('/orders/{order}/verify-payment', [OrderController::class, 'verifyPayment']);
        Route::post('/orders/{order}/staff-confirm-delivery', [OrderController::class, 'staffConfirmDelivery']);
    });

    // Payment — tightly rate limited (10/min) to prevent abuse/payment brute-force
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/payment/generate-qr', [GenerateKhqrController::class, 'generate']);
        Route::post('/payment/verify', [CheckPaymentController::class, 'verify']);
        Route::post('/payment/confirm-manual', [CheckPaymentController::class, 'confirmManual']);
    });

    // Discounts
    Route::get('/discounts', [DiscountController::class, 'index']);
    Route::post('/discounts', [DiscountController::class, 'store']);
    Route::put('/discounts/{discount}', [DiscountController::class, 'update']);
    Route::delete('/discounts/{discount}', [DiscountController::class, 'destroy']);
    Route::patch('/discounts/{discount}/badge', [DiscountController::class, 'saveBadge']);

    // User profile + saved locations
    Route::get('/user/profile', [UserProfileController::class, 'show']);
    Route::put('/user/profile', [UserProfileController::class, 'update']);
    Route::post('/user/profile/complete', [UserProfileController::class, 'completeProfile']);
    Route::get('/user/stats', [UserProfileController::class, 'stats']);
    Route::post('/user/avatar', [UserProfileController::class, 'uploadAvatar']);
    Route::delete('/user/avatar', [UserProfileController::class, 'removeAvatar']);
    Route::get('/user/locations', [UserProfileController::class, 'locations']);
    Route::post('/user/locations', [UserProfileController::class, 'storeLocation']);
    Route::put('/user/locations/{id}', [UserProfileController::class, 'updateLocation']);
    Route::delete('/user/locations/{id}', [UserProfileController::class, 'destroyLocation']);

    // Telegram user connect/disconnect (requires login — different from social login above)
    Route::prefix('telegram')->group(function () {
        Route::post('/connect', [TelegramController::class, 'connect']);
        Route::post('/generate-token', [TelegramController::class, 'generateToken']);
        Route::post('/disconnect', [TelegramController::class, 'disconnect']);
        Route::get('/status', [TelegramController::class, 'status']);
        Route::post('/test-message', [TelegramController::class, 'testMessage']);

        Route::post('/setup-webhook', [TelegramBotController::class, 'setupWebhook']);
        Route::post('/delete-webhook', [TelegramBotController::class, 'deleteWebhook']);
        Route::get('/webhook-info', [TelegramBotController::class, 'webhookInfo']);
        Route::post('/set-commands', [TelegramBotController::class, 'setCommands']);
    });
});
