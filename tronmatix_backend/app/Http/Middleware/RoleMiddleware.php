<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Role;

class RoleMiddleware
{
    /** Cache the DB role slugs for 10 minutes — roles rarely change */
    private const CACHE_KEY = 'dynamic_role_keys';
    private const CACHE_TTL = 600;

    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user(); // resolved by sanctum

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validRoleSlugs = $this->validRoleSlugs();

        // Every role slug passed in the route definition must exist in the DB
        // so we never accidentally expose a feature to a non-existent role.
        foreach ($roles as $routeRole) {
            if (! in_array($routeRole, $validRoleSlugs, true)) {
                \Illuminate\Support\Facades\Log::warning('RoleMiddleware: unknown role in route definition', [
                    'role' => $routeRole,
                    'route' => $request->path(),
                ]);
            }
        }

        // Merge DB roles with the route-declared list (route list wins if DB is stale)
        $allowed = array_values(array_unique(array_merge($roles, $validRoleSlugs)));

        if (! in_array($user->role ?? '', $allowed, true)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return $next($request);
    }

    /** Return all known role slugs from DB, with cache fallback */
    private function validRoleSlugs(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            if (! \Illuminate\Support\Facades\Schema::hasTable('roles')) {
                return [];
            }

            return Role::pluck('key')->filter()->values()->toArray();
        });
    }
}