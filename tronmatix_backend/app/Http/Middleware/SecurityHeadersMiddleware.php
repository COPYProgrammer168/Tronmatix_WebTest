<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecurityHeadersMiddleware
 *
 * Adds HTTP security headers to every response and removes
 * fingerprinting headers that reveal framework/server info.
 *
 * Register globally in app/Http/Kernel.php under $middleware.
 */
class SecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = config('security-headers', []);

        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }

        // Remove headers that fingerprint the stack
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');
        $response->headers->remove('X-Generator');

        return $response;
    }
}
