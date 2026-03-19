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

        // ── CORS + TrustProxies MUST be first ─────────────────────────────────
        // HandleCors is no longer auto-registered in Laravel 11.
        // Without prepend(), OPTIONS preflight hits auth middleware first → 401 → CORS blocked.
        $middleware->prepend([
            \Illuminate\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // ── Exclude /api/* from CSRF — API uses Bearer tokens, not cookies ────
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        // ── Sanctum stateful middleware for API ───────────────────────────────
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'auth'     => \Illuminate\Auth\Middleware\Authenticate::class,
            'guest'    => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // ── Return JSON errors for all /api/* routes ──────────────────────────
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(
                    ['success' => false, 'message' => 'Unauthenticated.'],
                    Response::HTTP_UNAUTHORIZED
                );
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(
                    ['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(
                    ['success' => false, 'message' => 'Resource not found.'],
                    Response::HTTP_NOT_FOUND
                );
            }
        });

        // ── FIX: Handle 405 Method Not Allowed for API routes ─────────────────
        // Without this, wrong-method requests return Laravel's HTML error page
        // instead of JSON — confusing for frontend debugging.
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(
                    ['success' => false, 'message' => 'Method not allowed. Check the HTTP method (GET/POST/PUT/DELETE).'],
                    Response::HTTP_METHOD_NOT_ALLOWED
                );
            }
        });
    })
    ->create();
