<?php

namespace App\Providers;

use App\Listeners\BackupBeforeDestructiveCommandListener;
use App\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Events\Event;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event as EventFacade;
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
        //
        // IMPORTANT: only apply this for admin/staff. Customers use the
        // default Fortify customer reset page (route('password.reset')) with
        // the `users` broker — overriding their URL to the dashboard page
        // breaks their reset (their token would be checked against the
        // admins/staff broker and never match).
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            // Customer → React frontend reset page (query-param driven).
            if ($notifiable instanceof \App\Models\User) {
                $frontendUrls = explode(',', env('FRONTEND_URL', 'http://localhost:5173'));
                $frontendUrl  = rtrim($frontendUrls[0], '/');

                return $frontendUrl . '/?token=' . $token . '&email=' . urlencode($notifiable->getEmailForPasswordReset());
            }

            // Admin / staff → dashboard reset page.
            return url(route('dashboard.password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
                'mode'  => $notifiable instanceof \App\Models\Staff ? 'staff' : 'admin',
            ]));
        });

        if (app()->runningInConsole()) {
            EventFacade::listen(
                \Illuminate\Console\Events\CommandStarting::class,
                [BackupBeforeDestructiveCommandListener::class, 'handle']
            );
        }
    }
}
