<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Keep existing pagination view
        Paginator::defaultView('dashboard.pagination');

        // FIX: Force HTTPS in production (Render deployment).
        // Render terminates SSL at the load balancer and forwards requests
        // as HTTP internally — without this, Laravel generates http:// URLs
        // for redirects and forms → browser shows "Form is not secure" warning.
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // The dashboard (admin/staff) has its own reset page. Without this
        // override, Laravel's ResetPassword notification builds the emailed
        // link from route('password.reset') — the CUSTOMER (Fortify) page —
        // which doesn't work for admin/staff brokers. Point admin/staff reset
        // emails at the dashboard reset page instead.
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return url(route('dashboard.password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
                'mode'  => $notifiable instanceof \App\Models\Staff ? 'staff' : 'admins',
            ]));
        });
    }
}
