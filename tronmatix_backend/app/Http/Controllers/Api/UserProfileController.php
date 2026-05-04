<?php

// app/Http/Controllers/Api/UserProfileController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\Order;
use App\Models\User;
use App\Models\UserLocation;
use App\Services\ImageStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserProfileController extends Controller
{
    public function __construct(
        private readonly ImageStorageService $storage
    ) {}

    public function show(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->userPayload(Auth::user())]);
    }

    public function update(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'phone'    => 'nullable|string|max:30',
        ]);

        $toUpdate = array_filter([
            'username' => $validated['username'] ?? null,
            'phone'    => $validated['phone'] ?? null,
        ], fn($v) => $v !== null);

        if (!empty($toUpdate)) {
            $user->update($toUpdate);
        }

        return response()->json(['success' => true, 'data' => $this->userPayload($user->fresh())]);
    }

    // ── POST /api/user/profile/complete ──────────────────────────────────────
    // Called after Google/Telegram OAuth for new users to set username + phone.
    public function completeProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'username' => 'required|string|min:3|max:50|unique:users,username,' . $user->id . '|regex:/^[a-zA-Z0-9_]+$/',
            'phone'    => 'nullable|string|max:30',
        ], [
            'username.regex' => 'Username may only contain letters, numbers, and underscores.',
        ]);

        $user->update(array_filter([
            'username' => $validated['username'],
            'phone'    => $validated['phone'] ?? null,
            'name'     => $validated['username'], // sync name too
        ], fn($v) => $v !== null));

        return response()->json(['success' => true, 'data' => $this->userPayload($user->fresh())]);
    }

    // ── POST /api/user/avatar ─────────────────────────────────────────────────
    public function uploadAvatar(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'avatar' => 'required|file|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Delete old avatar, store new one
        $this->storage->delete($user->avatar);
        $avatarUrl = $this->storage->store($request->file('avatar'), 'avatars/users');

        $user->update(['avatar' => $avatarUrl]);

        return response()->json([
            'success' => true,
            'data'    => $this->userPayload($user->fresh()),
        ]);
    }

    // ── DELETE /api/user/avatar ───────────────────────────────────────────────
    public function removeAvatar(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->avatar) {
            $this->storage->delete($user->avatar);
            $user->update(['avatar' => null]);
        }

        return response()->json(['success' => true, 'data' => $this->userPayload($user->fresh())]);
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
            'name'        => 'required|string|max:100',
            'phone'       => 'required|string|max:30',
            'address'     => 'required|string|max:500',
            'city'        => 'required|string|max:100',
            'country'     => 'nullable|string|max:100',
            'note'        => 'nullable|string|max:255',
            'is_default'  => 'nullable|boolean',
            'lat'         => 'nullable|numeric|between:-90,90',
            'lng'         => 'nullable|numeric|between:-180,180',
            'map_address' => 'nullable|string|max:1000',
        ]);

        $userId   = Auth::id();
        $location = DB::transaction(function () use ($validated, $userId) {
            $isDefault = !empty($validated['is_default'])
                || !UserLocation::where('user_id', $userId)->exists();

            if ($isDefault) {
                UserLocation::where('user_id', $userId)->update(['is_default' => false]);
            }

            return UserLocation::create([
                ...$validated,
                'user_id'    => $userId,
                'country'    => $validated['country'] ?? 'Cambodia',
                'is_default' => $isDefault,
                'lat'        => $validated['lat'] ?? null,
                'lng'        => $validated['lng'] ?? null,
                'map_address' => $validated['map_address'] ?? null,
            ]);
        });

        return response()->json(['success' => true, 'data' => $location], 201);
    }

    // ── PUT /api/user/locations/{id} ──────────────────────────────────────────
    public function updateLocation(Request $request, int $id): JsonResponse
    {
        $location  = UserLocation::where('user_id', Auth::id())->findOrFail($id);
        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:100',
            'phone'       => 'sometimes|required|string|max:30',
            'address'     => 'sometimes|required|string|max:500',
            'city'        => 'sometimes|required|string|max:100',
            'country'     => 'nullable|string|max:100',
            'note'        => 'nullable|string|max:255',
            'is_default'  => 'nullable|boolean',
            'lat'         => 'nullable|numeric|between:-90,90',
            'lng'         => 'nullable|numeric|between:-180,180',
            'map_address' => 'nullable|string|max:1000',
        ]);

        if (!empty($validated['is_default'])) {
            $location->setAsDefault();
            unset($validated['is_default']);
        }

        $location->update($validated);

        return response()->json(['success' => true, 'data' => $location->fresh()]);
    }

    // ── DELETE /api/user/locations/{id} ──────────────────────────────────────
    public function destroyLocation(int $id): JsonResponse
    {
        $location   = UserLocation::where('user_id', Auth::id())->findOrFail($id);
        $wasDefault = $location->is_default;
        $location->delete();

        if ($wasDefault) {
            UserLocation::where('user_id', Auth::id())
                ->latest()->first()?->update(['is_default' => true]);
        }

        return response()->json(['success' => true]);
    }

    // ── GET /api/user/stats ───────────────────────────────────────────────────
    public function stats(): JsonResponse
    {
        /** @var User $user */
        $user         = Auth::user();
        $totalSpent   = Order::where('user_id', $user->id)->whereNotIn('status', ['cancelled'])->sum('total');
        $vipThreshold = (float) AdminSetting::get('vip_threshold', 1000);

        if ($totalSpent >= $vipThreshold && ($user->role ?? 'customer') === 'customer') {
            $user->update(['role' => 'vip']);
            $user->refresh();
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'total_spent'     => (float) $totalSpent,
                'order_count'     => Order::where('user_id', $user->id)->count(),
                'cancelled_count' => Order::where('user_id', $user->id)->where('status', 'cancelled')->count(),
                'vip_goal'        => $vipThreshold,
                'vip_progress'    => min(100, round(($totalSpent / $vipThreshold) * 100, 1)),
                'role'            => $user->role ?? 'customer',
            ],
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function userPayload(User $user): array
    {
        return [
            'id'         => $user->id,
            'username'   => $user->username,
            'name'       => $user->name,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'avatar'     => $user->avatar,
            'role'       => $user->role ?? 'customer',
            'is_banned'  => $user->is_banned ?? false,
            'created_at' => $user->created_at,
        ];
    }
}
