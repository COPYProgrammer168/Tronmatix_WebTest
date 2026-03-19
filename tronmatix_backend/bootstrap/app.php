<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // FIX: Add CORS middleware FIRST — before any other middleware.
        // Without this, OPTIONS preflight requests get blocked before
        // reaching the route → frontend sees CORS error → "Provisional headers"
        // Laravel 11 does NOT auto-register HandleCors — must be done manually.
        $middleware->prepend(
            \Illuminate\Http\Middleware\HandleCors::class
        );

        // Trust Render's load balancer so Laravel detects HTTPS correctly
        $middleware->prepend(
            \Illuminate\Http\Middleware\TrustProxies::class
        );

        // Exclude all /api/* routes from CSRF (protected by Bearer token)
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        // Sanctum for API token authentication
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Middleware aliases
        $middleware->alias([
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'auth'     => \Illuminate\Auth\Middleware\Authenticate::class,
            'guest'    => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {

        // Return JSON for API authentication errors
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], Response::HTTP_UNAUTHORIZED);
            }
        });

        // Return JSON for API validation errors
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $e->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        });

        // Return JSON for API 404 errors
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                ], Response::HTTP_NOT_FOUND);
            }
        });

    })
    ->create();
