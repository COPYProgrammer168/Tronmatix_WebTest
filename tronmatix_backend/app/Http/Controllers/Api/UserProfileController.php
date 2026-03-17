<?php

// app/Http/Controllers/Api/UserProfileController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\Order;
use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserProfileController extends Controller
{
    // ── GET /api/user/profile ─────────────────────────────────────────────────
    public function show(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => Auth::user()]);
    }

    // ── PUT /api/user/profile ─────────────────────────────────────────────────
    public function update(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'username' => 'nullable|string|max:255|unique:users,username,'.$user->id,
            'phone' => 'nullable|string|max:30',
        ]);

        $toUpdate = array_filter([
            'username' => $validated['username'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ], fn ($v) => $v !== null);

        if (! empty($toUpdate)) {
            $user->update($toUpdate);
        }

        return response()->json(['success' => true, 'data' => $user->fresh()]);
    }

    // ── GET /api/user/locations ───────────────────────────────────────────────
    public function locations(): JsonResponse
    {
        $locations = UserLocation::where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $locations]);
    }

    // ── POST /api/user/locations ──────────────────────────────────────────────
    public function storeLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'country' => 'nullable|string|max:100',  // FIX [2]
            'note' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        $userId = Auth::id();

        // FIX [3,4]: wrap in transaction to prevent race condition on is_default
        $location = DB::transaction(function () use ($validated, $userId) {
            $isDefault = ! empty($validated['is_default'])
                || ! UserLocation::where('user_id', $userId)->exists(); // FIX [4]: first location always default

            if ($isDefault) {
                UserLocation::where('user_id', $userId)->update(['is_default' => false]);
            }

            return UserLocation::create([
                ...$validated,
                'user_id' => $userId,
                'country' => $validated['country'] ?? 'Cambodia',  // FIX [2]
                'is_default' => $isDefault,
            ]);
        });

        return response()->json(['success' => true, 'data' => $location], 201);
    }

    // ── PUT /api/user/locations/{id} ──────────────────────────────────────────
    public function updateLocation(Request $request, int $id): JsonResponse
    {
        $location = UserLocation::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'phone' => 'sometimes|required|string|max:30',
            'address' => 'sometimes|required|string|max:500',
            'city' => 'sometimes|required|string|max:100',
            'country' => 'nullable|string|max:100',  // FIX [2]
            'note' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        // FIX [3]: use model's setAsDefault() which wraps in DB::transaction
        if (! empty($validated['is_default'])) {
            $location->setAsDefault();
            unset($validated['is_default']);
        }

        $location->update($validated);

        return response()->json(['success' => true, 'data' => $location->fresh()]);
    }

    // ── DELETE /api/user/locations/{id} ──────────────────────────────────────
    public function destroyLocation(int $id): JsonResponse
    {
        $location = UserLocation::where('user_id', Auth::id())->findOrFail($id);
        $wasDefault = $location->is_default;
        $location->delete();

        if ($wasDefault) {
            $next = UserLocation::where('user_id', Auth::id())->latest()->first();
            $next?->update(['is_default' => true]);
        }

        return response()->json(['success' => true]);
    }

    // ── GET /api/user/stats ───────────────────────────────────────────────────
    public function stats(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $totalSpent = Order::where('user_id', $user->id)->whereNotIn('status', ['cancelled'])->sum('total');
        $orderCount = Order::where('user_id', $user->id)->count();
        $cancelledCount = Order::where('user_id', $user->id)->where('status', 'cancelled')->count();

        // FIX [1]: use AdminSetting instead of hardcoded 1000
        $vipThreshold = (float) AdminSetting::get('vip_threshold', 1000);

        // Safety-net: promote if eligible but not yet promoted
        if ($totalSpent >= $vipThreshold && ($user->role ?? 'customer') === 'customer') {
            $user->update(['role' => 'vip']);
            $user->refresh();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total_spent' => (float) $totalSpent,
                'order_count' => $orderCount,
                'cancelled_count' => $cancelledCount,
                'vip_goal' => $vipThreshold,           // FIX [1]
                'vip_progress' => min(100, round(($totalSpent / $vipThreshold) * 100, 1)),
                'role' => $user->role ?? 'customer',
            ],
        ]);
    }
}
