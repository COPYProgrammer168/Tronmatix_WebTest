<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Dashboard\AdminController;
use App\Http\Controllers\Dashboard\BannerController;
use App\Http\Controllers\Dashboard\TelegramAdminController;
use App\Http\Controllers\Dashboard\DiscountController as DashboardDiscountController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Controllers\Dashboard\SettingsController;
use App\Http\Controllers\Dashboard\StaffController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaffRequestController;
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

// ── Admin Auth Routes (unauthenticated only) ──────────────────────────────────
Route::prefix('dashboard')->name('dashboard.')
    ->middleware(\App\Http\Middleware\AdminGuest::class)
    ->group(function () {
        Route::get('/login',     [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login',    [AdminAuthController::class, 'login'])->name('login.post');
        Route::get('/register',  [AdminAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AdminAuthController::class, 'register'])->name('register.post');

        Route::get('/request-access',  [StaffRequestController::class, 'showForm'])->name('request-access');
        Route::post('/request-access', [StaffRequestController::class, 'submit'])->name('request-access.submit');
    });

// ── Protected Dashboard Routes ────────────────────────────────────────────────
// AdminAuthenticate accepts BOTH admin guard AND staff guard.
// Routes that must be admin-only are nested under a second middleware group
// using StaffAuthenticate inverted (i.e., AdminAuthenticate checks role inside
// the controller with assertAdmin()).
Route::prefix('dashboard')->name('dashboard.')
    ->middleware(\App\Http\Middleware\AdminAuthenticate::class)
    ->group(function () {

        Route::get('/',       [DashboardController::class, 'index'])->name('index');
        Route::get('/export', [DashboardController::class, 'dashboardExport'])->name('export');
        Route::post('/logout',[AdminAuthController::class, 'logout'])->name('logout');

        // ── Products ──────────────────────────────────────────────────────────
        Route::get('/products',               [ProductController::class, 'index'])->name('products');
        Route::get('/products/create',        [ProductController::class, 'create'])->name('products.create');
        Route::post('/products',              [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit',[ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}',     [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}',  [ProductController::class, 'destroy'])->name('products.destroy');

        // ── Orders ────────────────────────────────────────────────────────────
        Route::get('/orders',                           [DashboardController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}',                   [DashboardController::class, 'showOrder'])->name('orders.show');
        Route::put('/orders/{order}/status',            [DashboardController::class, 'updateOrderStatus'])->name('orders.status');
        Route::post('/orders/{order}/confirm-delivery', [DashboardController::class, 'confirmDelivery'])->name('orders.confirm-delivery');

        // ── Users ─────────────────────────────────────────────────────────────
        Route::get('/users',             [UserController::class, 'index'])->name('users');
        Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');

        // ── Discounts ─────────────────────────────────────────────────────────
        Route::get('/discounts',                    [DashboardController::class,         'discounts'])->name('discounts');
        Route::post('/discounts',                   [DashboardDiscountController::class, 'store'])->name('discounts.store');
        Route::put('/discounts/{discount}',         [DashboardDiscountController::class, 'update'])->name('discounts.update');
        Route::delete('/discounts/{discount}',      [DashboardDiscountController::class, 'destroy'])->name('discounts.destroy');
        Route::patch('/discounts/{discount}/badge', [DashboardDiscountController::class, 'saveBadge'])->name('discounts.badge');

        // ── Banners ───────────────────────────────────────────────────────────
        Route::get('/banners',                   [BannerController::class, 'index'])->name('banners');
        Route::post('/banners',                  [BannerController::class, 'store'])->name('banners.store');
        Route::put('/banners/{banner}',          [BannerController::class, 'update'])->name('banners.update');
        Route::patch('/banners/{banner}/toggle', [BannerController::class, 'toggle'])->name('banners.toggle');
        Route::delete('/banners/{banner}',       [BannerController::class, 'destroy'])->name('banners.destroy');

        // ── Admin Profile ─────────────────────────────────────────────────────
        Route::get('/profile',            [ProfileController::class, 'show'])->name('profile');
        Route::post('/profile',           [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password',   [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::put('/profile/role',       [ProfileController::class, 'updateRole'])->name('profile.role');
        Route::delete('/profile/avatar',  [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');

        // ── Notifications ─────────────────────────────────────────────────────
        Route::get('/notifications', [SettingsController::class, 'notifications'])->name('notifications');

        // Settings — SettingsController::show() checks canEditPerms internally
        Route::get('/settings',             [SettingsController::class, 'show'])->name('settings');
        Route::put('/settings',             [SettingsController::class, 'update'])->name('settings.update');
        Route::get('/settings/reset',       [SettingsController::class, 'reset'])->name('settings.reset');
        Route::put('/settings/permissions', [SettingsController::class, 'updatePermissions'])->name('settings.permissions');

        // Staff management — StaffController::assertAdmin() enforces role
        Route::get('/staff',               [StaffController::class, 'index'])->name('staff');
        Route::post('/staff/invite',       [StaffController::class, 'invite'])->name('staff.invite');
        Route::patch('/staff/{id}/role',   [StaffController::class, 'updateRole'])->name('staff.role');
        Route::patch('/staff/{id}/toggle', [StaffController::class, 'toggle'])->name('staff.toggle');
        Route::delete('/staff/{id}',       [StaffController::class, 'destroy'])->name('staff.destroy');

        // Admin management — AdminController enforces superadmin role internally
        Route::post('/admin/invite',        [AdminController::class, 'invite'])->name('admin.invite');
        Route::patch('/admin/{id}/role',    [AdminController::class, 'updateRole'])->name('admin.role');
        Route::patch('/admin/{id}/toggle',  [AdminController::class, 'toggle'])->name('admin.toggle');
        Route::delete('/admin/{id}',        [AdminController::class, 'destroy'])->name('admin.destroy');

        // Staff requests — StaffRequestController enforces superadmin internally
        Route::post('/staff-requests/{id}/accept', [StaffRequestController::class, 'accept'])->name('staff-requests.accept');
        Route::post('/staff-requests/{id}/reject', [StaffRequestController::class, 'reject'])->name('staff-requests.reject');

        // Telegram — admin only, enforced in controller
        Route::post('/telegram/setup-webhook',  [TelegramAdminController::class, 'setupWebhook'])->name('telegram.setup-webhook');
        Route::post('/telegram/delete-webhook', [TelegramAdminController::class, 'deleteWebhook'])->name('telegram.delete-webhook');
        Route::get('/telegram/webhook-info',    [TelegramAdminController::class, 'webhookInfo'])->name('telegram.webhook-info');

        Route::get('/report', [DashboardController::class, 'report'])->name('report');
    });
