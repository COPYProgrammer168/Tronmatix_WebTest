<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Dashboard\AdminController;
use App\Http\Controllers\Dashboard\BannerController;
use App\Http\Controllers\Dashboard\VideoController;
use App\Http\Controllers\Dashboard\TelegramAdminController;
use App\Http\Controllers\Dashboard\DiscountController as DashboardDiscountController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Controllers\Dashboard\SettingsController;
use App\Http\Controllers\Dashboard\StaffController;
use App\Http\Controllers\Dashboard\StaffInviteController;
use App\Http\Controllers\Dashboard\PasswordResetController;
use App\Http\Controllers\Dashboard\PhoneOtpController;
use App\Http\Controllers\Dashboard\DeliveryProviderController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\MainCategoryController;
use App\Http\Controllers\Dashboard\SubCategoryController;
use App\Http\Controllers\Dashboard\BrandController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\StockController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardChartController;
use App\Http\Controllers\StaffRequestController;
use App\Models\Feedback;
use Illuminate\Support\Facades\Route;

// ── Redirect root ─────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('dashboard.index'));

// ── Language switcher ─────────────────────────────────────────────────────────
Route::get('/lang/{locale}', function (string $locale) {
    $supported = ['en', 'km'];
    if (in_array($locale, $supported)) {
        session(['app_lang' => $locale]);
        cookie()->queue(cookie('app_lang', $locale, 60 * 24 * 365, '/', null, false, false));
    }
    return redirect()->back()->withHeaders(['Cache-Control' => 'no-store']);
})->name('lang.switch');

// ── Feedback ──────────────────────────────────────────────────────────────────
Route::get('/feedback', fn() => view('dashboard.feedback.index'))->name('feedback');
Route::post('/feedback', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'feedback' => 'required|string',
    ]);
    Feedback::create($request->all());
    return back()->with('success', 'Thank you for your feedback!');
})->name('feedback.submit');

// ── Public staff invite accept (no auth — invited person is not yet a user) ──
Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/invite/{token}', [StaffInviteController::class, 'show'])->name('invite.show');
    Route::post('/invite/{token}', [StaffInviteController::class, 'accept'])->name('invite.accept');
});

// ── Admin Auth Routes (unauthenticated only) ──────────────────────────────────
Route::prefix('dashboard')->name('dashboard.')
    ->middleware(\App\Http\Middleware\AdminGuest::class)
    ->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
        Route::get('/register', [AdminAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AdminAuthController::class, 'register'])->name('register.post');

        // ── Forgot password (Gmail reset link + phone OTP) ────────────────
        // Named dashboard.password.* (no collision with Fortify's customer
        // password.* routes at /forgot-password and /reset-password/{token}).
        Route::get('/password/email', [PasswordResetController::class, 'showForgotForm'])->name('password.email');
        // No route-level throttle here — sendResetLink() does its own cooldown +
        // per-IP attempt limiting with friendly countdown feedback (see
        // config/security.php). throttle:5,1 would 429 before those kick in.
        Route::post('/password/email', [PasswordResetController::class, 'sendResetLink'])
            ->name('password.email.post');
        Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
        Route::post('/password/reset', [PasswordResetController::class, 'resetPassword'])
            ->middleware('throttle:5,1')
            ->name('password.reset.post');

        Route::get('/password/phone', [PhoneOtpController::class, 'showPhoneForm'])->name('password.phone');
        Route::post('/password/phone/verify', [PhoneOtpController::class, 'verifyOtpAndReset'])
            ->middleware('throttle:5,1')
            ->name('password.phone.verify.post');
    });

// ── Protected Dashboard Routes ────────────────────────────────────────────────
Route::prefix('dashboard')->name('dashboard.')
    ->middleware(\App\Http\Middleware\AdminAuthenticate::class)
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/export', [DashboardController::class, 'dashboardExport'])->name('export');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/request-access', [StaffRequestController::class, 'showForm'])->name('request-access');
        Route::post('/request-access', [StaffRequestController::class, 'submit'])->name('request-access.submit');

        // ── Category system (single accordion page at /dashboard/categories) ─
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::patch('/categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::post('/category-management/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
        // Tree page posts DELETE via FormData _method — add a POST fallback
        // (body _method is NOT honored for a route that doesn't match by verb).
        Route::post('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy.post');

        Route::post('/main-categories', [MainCategoryController::class, 'store'])->name('main-categories.store');
        Route::put('/main-categories/{mainCategory}', [MainCategoryController::class, 'update'])->name('main-categories.update');
        Route::patch('/main-categories/{mainCategory}/toggle', [MainCategoryController::class, 'toggle'])->name('main-categories.toggle');
        Route::delete('/main-categories/{mainCategory}', [MainCategoryController::class, 'destroy'])->name('main-categories.destroy');
        Route::post('/main-categories/{mainCategory}', [MainCategoryController::class, 'destroy'])->name('main-categories.destroy.post');

        Route::post('/sub-categories', [SubCategoryController::class, 'store'])->name('sub-categories.store');
        Route::put('/sub-categories/{subCategory}', [SubCategoryController::class, 'update'])->name('sub-categories.update');
        Route::patch('/sub-categories/{subCategory}/toggle', [SubCategoryController::class, 'toggle'])->name('sub-categories.toggle');
        Route::delete('/sub-categories/{subCategory}', [SubCategoryController::class, 'destroy'])->name('sub-categories.destroy');
        Route::post('/sub-categories/{subCategory}', [SubCategoryController::class, 'destroy'])->name('sub-categories.destroy.post');

        Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
        Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
        Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
        Route::get('/brands/{brand}', [BrandController::class, 'edit'])->name('brands.edit');
        Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
        Route::patch('/brands/{brand}/toggle', [BrandController::class, 'toggle'])->name('brands.toggle');
        Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');
        Route::post('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy.post');

        // ── Products ──────────────────────────────────────────────────────────
        Route::get('/products', [ProductController::class, 'index'])->name('products');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product:slug}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product:slug}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product:slug}', [ProductController::class, 'destroy'])->name('products.destroy');

        // ── Stock management ──────────────────────────────────────────────────
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
        Route::post('/stock/receive', [StockController::class, 'receive'])->name('stock.receive');
        Route::post('/stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
        Route::post('/stock/damaged', [StockController::class, 'damaged'])->name('stock.damaged');
        Route::post('/stock/reset', [StockController::class, 'resetRandom'])->name('stock.reset');
        Route::get('/stock/{product}/history', [StockController::class, 'history'])->name('stock.history');
        Route::get('/stock/report', [StockController::class, 'report'])->name('stock.report');
        Route::get('/stock/export', [StockController::class, 'export'])->name('stock.export');

        // ── Orders ────────────────────────────────────────────────────────────
        Route::get('/orders', [DashboardController::class, 'orders'])->name('orders');
        Route::get('/orders/{order_id}', [DashboardController::class, 'showOrder'])->name('orders.show');
        Route::put('/orders/{order_id}/status', [DashboardController::class, 'updateOrderStatus'])->name('orders.status');
        Route::post('/orders/{order_id}/confirm-delivery', [DashboardController::class, 'confirmDelivery'])->name('orders.confirm-delivery');
        Route::post('/orders/{order_id}/verify-payment', [DashboardController::class, 'verifyOrderPayment'])->name('orders.verify-payment');

        // ── Users ─────────────────────────────────────────────────────────────
        Route::get('/users', [UserController::class, 'index'])->name('users');
        Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');

        // ── Discounts ─────────────────────────────────────────────────────────
        Route::get('/discounts', [DashboardController::class, 'discounts'])->name('discounts');
        Route::post('/discounts', [DashboardDiscountController::class, 'store'])->name('discounts.store');
        Route::put('/discounts/{discount}', [DashboardDiscountController::class, 'update'])->name('discounts.update');
        Route::delete('/discounts/{discount}', [DashboardDiscountController::class, 'destroy'])->name('discounts.destroy');
        Route::patch('/discounts/{discount}/badge', [DashboardDiscountController::class, 'saveBadge'])->name('discounts.badge');

        // ── Banners ───────────────────────────────────────────────────────────
        Route::get('/banners', [BannerController::class, 'index'])->name('banners');
        Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
        Route::put('/banners/{banner}', [BannerController::class, 'update'])->name('banners.update');
        Route::patch('/banners/{banner}/toggle', [BannerController::class, 'toggle'])->name('banners.toggle');
        Route::delete('/banners/{banner}', [BannerController::class, 'destroy'])->name('banners.destroy');

        // ── Videos ────────────────────────────────────────────────────────────
        Route::get('/videos', [VideoController::class, 'index'])->name('videos');
        Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');
        Route::put('/videos/{video}', [VideoController::class, 'update'])->name('videos.update');
        Route::patch('/videos/{video}/toggle', [VideoController::class, 'toggle'])->name('videos.toggle');
        Route::delete('/videos/{video}', [VideoController::class, 'destroy'])->name('videos.destroy');

        // ── Admin Profile ─────────────────────────────────────────────────────
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
        Route::match(['post', 'put'], '/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::put('/profile/role', [ProfileController::class, 'updateRole'])->name('profile.role');
        Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');

        // ── Notifications ─────────────────────────────────────────────────────
        Route::get('/notifications', [SettingsController::class, 'notifications'])->name('notifications');
        Route::post('/notifications/clear', [SettingsController::class, 'clearNotifications'])->name('notifications.clear');

        // Settings — SettingsController::show() checks canEditPerms internally
        Route::get('/settings', [SettingsController::class, 'show'])->name('settings');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/reset-vip', [SettingsController::class, 'resetVipRoles'])->name('settings.reset-vip');
        Route::get('/settings/reset', [SettingsController::class, 'reset'])->name('settings.reset');
        Route::put('/settings/permissions', [SettingsController::class, 'updatePermissions'])->name('settings.permissions');

        // Role CRUD — superadmin only
        Route::post('/settings/roles', [SettingsController::class, 'storeRole'])->name('settings.roles.store');
        Route::put('/settings/roles/{id}', [SettingsController::class, 'updateRole'])->name('settings.roles.update');
        Route::delete('/settings/roles/{id}', [SettingsController::class, 'destroyRole'])->name('settings.roles.destroy');

        // Feature CRUD — superadmin only
        Route::post('/settings/features', [SettingsController::class, 'storeFeature'])->name('settings.features.store');
        Route::put('/settings/features/{id}', [SettingsController::class, 'updateFeature'])->name('settings.features.update');
        Route::delete('/settings/features/{id}', [SettingsController::class, 'destroyFeature'])->name('settings.features.destroy');

        // Marquee messages CRUD
        Route::get('/settings/marquees', [SettingsController::class, 'marquees'])->name('settings.marquees');
        Route::post('/settings/marquees', [SettingsController::class, 'storeMarquee'])->name('settings.marquees.store');
        Route::put('/settings/marquees/{id}', [SettingsController::class, 'updateMarquee'])->name('settings.marquees.update');
        Route::delete('/settings/marquees/{id}', [SettingsController::class, 'destroyMarquee'])->name('settings.marquees.destroy');

        // Delivery providers
        Route::get('/delivery-providers', [DeliveryProviderController::class, 'index'])->name('delivery-providers.index');
        Route::get('/delivery-providers/create', [DeliveryProviderController::class, 'create'])->name('delivery-providers.create');
        Route::post('/delivery-providers', [DeliveryProviderController::class, 'store'])->name('delivery-providers.store');
        Route::get('/delivery-providers/{deliveryProvider}/edit', [DeliveryProviderController::class, 'edit'])->name('delivery-providers.edit');
        Route::put('/delivery-providers/{deliveryProvider}', [DeliveryProviderController::class, 'update'])->name('delivery-providers.update');
        Route::patch('/delivery-providers/{deliveryProvider}/toggle', [DeliveryProviderController::class, 'toggleStatus'])->name('delivery-providers.toggle');
        Route::delete('/delivery-providers/{deliveryProvider}', [DeliveryProviderController::class, 'destroy'])->name('delivery-providers.destroy');

        // Activity log — admin + superadmin only
        Route::get('/activity-logs', [\App\Http\Controllers\Dashboard\ActivityLogController::class, 'show'])->name('activity-logs');

        // Staff management — StaffController::assertAdmin() enforces role
        Route::get('/staff', [StaffController::class, 'index'])->name('staff');
        Route::post('/staff/invite', [StaffController::class, 'invite'])->name('staff.invite');
        Route::post('/staff/invites/{id}/resend', [StaffController::class, 'resendInvite'])->name('staff.invite.resend');
        Route::patch('/staff/{id}/role', [StaffController::class, 'updateRole'])->name('staff.role');
        Route::patch('/staff/{id}/toggle', [StaffController::class, 'toggle'])->name('staff.toggle');
        Route::post('/staff/heartbeat', [StaffController::class, 'heartbeat'])->name('staff.heartbeat');
        Route::post('/staff/offline', [StaffController::class, 'setOffline'])->name('staff.offline');
        Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');

        // Admin management — AdminController enforces superadmin role internally
        Route::post('/admin/invite', [AdminController::class, 'invite'])->name('admin.invite');
        Route::patch('/admin/{id}/role', [AdminController::class, 'updateRole'])->name('admin.role');
        Route::patch('/admin/{id}/toggle', [AdminController::class, 'toggle'])->name('admin.toggle');
        Route::delete('/admin/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');

        // Staff requests — StaffRequestController enforces superadmin internally
        Route::post('/staff-requests/{id}/accept', [StaffRequestController::class, 'accept'])->name('staff-requests.accept');
        Route::post('/staff-requests/{id}/reject', [StaffRequestController::class, 'reject'])->name('staff-requests.reject');

        // Telegram — admin only, enforced in controller
        Route::post('/telegram/setup-webhook', [TelegramAdminController::class, 'setupWebhook'])->name('telegram.setup-webhook');
        Route::post('/telegram/delete-webhook', [TelegramAdminController::class, 'deleteWebhook'])->name('telegram.delete-webhook');
        Route::get('/telegram/webhook-info', [TelegramAdminController::class, 'webhookInfo'])->name('telegram.webhook-info');

        // Feedback
        Route::get('/feedback', [\App\Http\Controllers\Dashboard\FeedbackController::class, 'index'])->name('feedback');

        Route::get('/report', [DashboardController::class, 'report'])->name('report');
        Route::get('/revenue', [DashboardController::class, 'revenue'])->name('revenue');
        Route::get('/stats', [DashboardController::class, 'stats'])->name('stats');

        // ── Chart drill-down detail pages (dashboard index chart cards) ──────
        Route::get('/charts/{chart}', [DashboardChartController::class, 'show'])
            ->whereIn('chart', ['revenue','orders','sales','users','status','category'])
            ->name('charts.show');
    });
