<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * SetLocale middleware
 *
 * Reads the user's language preference from session → cookie → browser → default.
 * Must be registered in the `web` middleware group so it runs on every request.
 *
 * REGISTRATION (Laravel 10 — app/Http/Kernel.php):
 *   protected $middlewareGroups = [
 *       'web' => [
 *           ...
 *           \App\Http\Middleware\SetLocale::class,
 *       ],
 *   ];
 *
 * REGISTRATION (Laravel 11 — bootstrap/app.php):
 *   ->withMiddleware(function (Middleware $middleware) {
 *       $middleware->web(append: [
 *           \App\Http\Middleware\SetLocale::class,
 *       ]);
 *   })
 */
class SetLocale
{
    /** Languages supported by your dashboard */
    private const SUPPORTED = ['en', 'km'];

    public function handle(Request $request, Closure $next)
    {
        // Priority order: session > cookie > Accept-Language header > config default
        $locale = $request->session()->get('app_lang')
               ?? $request->cookie('app_lang')
               ?? $this->fromBrowser($request)
               ?? config('app.locale', 'en');

        // Sanitise — never trust raw input for locale switching
        $locale = in_array($locale, self::SUPPORTED, true) ? $locale : 'en';

        App::setLocale($locale);

        return $next($request);
    }

    /**
     * Best-effort parse of Accept-Language header.
     * Returns 'km' if the browser reports Khmer, 'en' if English, null otherwise.
     */
    private function fromBrowser(Request $request): ?string
    {
        $accept = $request->header('Accept-Language', '');
        foreach (explode(',', $accept) as $part) {
            $lang = strtolower(trim(explode(';', $part)[0]));
            if ($lang === 'km' || str_starts_with($lang, 'km-')) return 'km';
            if ($lang === 'en' || str_starts_with($lang, 'en-')) return 'en';
        }
        return null;
    }
}
