<?php

namespace App\Providers;

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
    }
}
