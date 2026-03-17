<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Dashboard\BannerController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Controllers\Dashboard\SettingsController;
use App\Http\Controllers\Dashboard\StaffController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
// use App\Exports\DashboardExport;
// use Maatwebsite\Excel\Facades\Excel;


// ── Redirect root ─────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('dashboard.index'));

// ── Admin Auth Routes (unauthenticated only) ──────────────────────────────────
Route::prefix('dashboard')->name('dashboard.')
    ->middleware(\App\Http\Middleware\AdminGuest::class)  // redirect to dashboard if already logged in
    ->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
        Route::get('/register', [AdminAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AdminAuthController::class, 'register'])->name('register.post');
    });

// ── Protected Dashboard Routes ────────────────────────────────────────────────
Route::prefix('dashboard')->name('dashboard.')
    ->middleware(\App\Http\Middleware\AdminAuthenticate::class)
    ->group(function () {

        // ── Overview ──────────────────────────────────────────────────────────
        Route::get('/', [DashboardController::class, 'index'])->name('index');

        Route::get('/dashboard/export', [DashboardController::class, 'dashboardExport'])
     ->name('dashboard.export');

        // ── Logout (must be inside auth middleware) ───────────────────────────
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        // ── Products ──────────────────────────────────────────────────────────
        // NOTE: /create must be declared before /{product} to avoid route collision
        Route::get('/products', [ProductController::class, 'index'])->name('products');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        // ── Orders ────────────────────────────────────────────────────────────
        Route::get('/orders', [DashboardController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [DashboardController::class, 'showOrder'])->name('orders.show');
        Route::put('/orders/{order}/status', [DashboardController::class, 'updateOrderStatus'])->name('orders.status');
        Route::post('/orders/{order}/confirm-delivery', [DashboardController::class, 'confirmDelivery'])->name('orders.confirm-delivery');

        // ── Users ─────────────────────────────────────────────────────────────
        Route::get('/users', [UserController::class, 'index'])->name('users');
        Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');

        // ── Discounts ─────────────────────────────────────────────────────────
        Route::get('/discounts', [DashboardController::class, 'discounts'])->name('discounts');
        Route::post('/discounts', [DashboardController::class, 'discountsStore'])->name('discounts.store');
        Route::put('/discounts/{discount}', [DashboardController::class, 'discountsUpdate'])->name('discounts.update');
        Route::delete('/discounts/{discount}', [DashboardController::class, 'discountsDestroy'])->name('discounts.destroy');
        Route::patch('/discounts/{discount}/badge', [DashboardController::class, 'discountsSaveBadge'])->name('discounts.badge');

        // ── Banners ───────────────────────────────────────────────────────────
        Route::get('/banners', [BannerController::class, 'index'])->name('banners');
        Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
        Route::put('/banners/{banner}', [BannerController::class, 'update'])->name('banners.update');
        Route::patch('/banners/{banner}/toggle', [BannerController::class, 'toggle'])->name('banners.toggle');
        Route::delete('/banners/{banner}', [BannerController::class, 'destroy'])->name('banners.destroy');

        // ── Settings ──────────────────────────────────────────────────────────
        Route::get('/settings', [SettingsController::class, 'show'])->name('settings');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('/settings/reset', [SettingsController::class, 'reset'])->name('settings.reset');
        Route::put('/settings/permissions', [SettingsController::class, 'updatePermissions'])->name('settings.permissions'); // ← ADD THIS

        // ── Notifications API (for bell polling) ──────────────────────────────
        Route::get('/notifications', [SettingsController::class, 'notifications'])->name('notifications');

        // ── Admin Profile ─────────────────────────────────────────────────────
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::put('/profile/role', [ProfileController::class, 'updateRole'])->name('profile.role');

        // ── Staff List ─────────────────────────────────────────────────────
        Route::get('/staff', [StaffController::class, 'index'])->name('staff');
        Route::post('/staff/invite', [StaffController::class, 'invite'])->name('staff.invite');
        Route::patch('/staff/{id}/role', [StaffController::class, 'updateRole'])->name('staff.role');
        Route::patch('/staff/{id}/toggle', [StaffController::class, 'toggle'])->name('staff.toggle');
        Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');
    });
