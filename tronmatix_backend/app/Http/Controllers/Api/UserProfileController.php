<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\Order;
use App\Models\User;
use App\Models\UserLocation;
use App\Traits\StorageHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserProfileController extends Controller
{
    use StorageHelper;

    public function show(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->userPayload(Auth::user())]);
    }

    public function update(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'username' => 'nullable|string|max:255|unique:users,username,'.$user->id,
            'phone'    => 'nullable|string|max:30',
        ]);

        $toUpdate = array_filter([
            'username' => $validated['username'] ?? null,
            'phone'    => $validated['phone'] ?? null,
        ], fn ($v) => $v !== null);

        if (! empty($toUpdate)) {
            $user->update($toUpdate);
        }

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

        $file     = $request->file('avatar');
        $uuidName = Str::uuid().'.'.$file->getClientOriginalExtension();

        // Delete old avatar from S3/R2 or local
        if ($user->avatar) {
            $this->deleteStorageFile($user->avatar);
        }

        // FIX: Use StorageHelper — auto-detects S3/R2 (production) or local (dev)
        // Replaces the manual disk check + Storage::disk('s3')->putFileAs() pattern
        // which caused PHP0418 error (url() not on Filesystem contract)
        $avatarUrl = $this->storeFileAs($file, 'avatars/users', $uuidName);

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
            $this->deleteStorageFile($user->avatar);
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
            $isDefault = ! empty($validated['is_default'])
                || ! UserLocation::where('user_id', $userId)->exists();

            if ($isDefault) {
                UserLocation::where('user_id', $userId)->update(['is_default' => false]);
            }

            return UserLocation::create([
                ...$validated,
                'user_id'     => $userId,
                'country'     => $validated['country'] ?? 'Cambodia',
                'is_default'  => $isDefault,
                'lat'         => $validated['lat'] ?? null,
                'lng'         => $validated['lng'] ?? null,
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
