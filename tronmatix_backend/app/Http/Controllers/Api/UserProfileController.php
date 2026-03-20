<?php

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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserProfileController extends Controller
{
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

        $disk     = config('filesystems.default'); // 's3' on Render, 'public' locally
        $file     = $request->file('avatar');
        $uuidName = Str::uuid().'.'.$file->getClientOriginalExtension();

        // Delete old avatar before uploading new one
        if ($user->avatar) {
            $this->deleteAvatarFile($user->avatar, $disk);
        }

        if ($disk === 's3') {
            // Store to R2/S3
            $storedPath = Storage::disk('s3')->putFileAs(
                'avatars/users',
                $file,
                $uuidName,
                ['visibility' => 'public']
            );

            // FIX: Build URL manually using AWS_URL env var
            // Storage::disk('s3')->url() triggers PHP0418 / P1013 because
            // the contract Filesystem doesn't declare url().
            // Instead: combine AWS_URL + stored path directly — no interface violation.
            $awsUrl    = rtrim(config('filesystems.disks.s3.url', ''), '/');
            $avatarUrl = $awsUrl
                ? $awsUrl.'/'.$storedPath
                : 'https://'.config('filesystems.disks.s3.bucket').'.'.config('filesystems.disks.s3.endpoint').'/'.$storedPath;

        } else {
            // Local public disk
            Storage::disk('public')->makeDirectory('avatars/users');
            $storedPath = $file->storeAs('avatars/users', $uuidName, 'public');
            // Return relative path — resolveImage() in frontend prepends BACKEND_URL
            $avatarUrl  = '/storage/'.$storedPath;
        }

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
            $this->deleteAvatarFile($user->avatar, config('filesystems.default'));
            $user->update(['avatar' => null]);
        }

        return response()->json(['success' => true, 'data' => $this->userPayload($user->fresh())]);
    }

    public function locations(): JsonResponse
    {
        $locations = UserLocation::where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $locations]);
    }

    public function storeLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'phone'      => 'required|string|max:30',
            'address'    => 'required|string|max:500',
            'city'       => 'required|string|max:100',
            'country'    => 'nullable|string|max:100',
            'note'       => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
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
                'user_id'    => $userId,
                'country'    => $validated['country'] ?? 'Cambodia',
                'is_default' => $isDefault,
            ]);
        });

        return response()->json(['success' => true, 'data' => $location], 201);
    }

    public function updateLocation(Request $request, int $id): JsonResponse
    {
        $location  = UserLocation::where('user_id', Auth::id())->findOrFail($id);
        $validated = $request->validate([
            'name'       => 'sometimes|required|string|max:100',
            'phone'      => 'sometimes|required|string|max:30',
            'address'    => 'sometimes|required|string|max:500',
            'city'       => 'sometimes|required|string|max:100',
            'country'    => 'nullable|string|max:100',
            'note'       => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        if (! empty($validated['is_default'])) {
            $location->setAsDefault();
            unset($validated['is_default']);
        }

        $location->update($validated);

        return response()->json(['success' => true, 'data' => $location->fresh()]);
    }

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
            'data' => [
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

    private function deleteAvatarFile(string $avatar, string $disk): void
    {
        try {
            if (Str::startsWith($avatar, ['http://', 'https://'])) {
                if ($disk === 's3') {
                    // Extract relative path from full R2/S3 URL
                    $awsUrl = rtrim(config('filesystems.disks.s3.url', ''), '/');
                    if ($awsUrl && str_starts_with($avatar, $awsUrl)) {
                        $rel = ltrim(substr($avatar, strlen($awsUrl)), '/');
                    } else {
                        // Fallback: parse URL path
                        $parsed = parse_url($avatar);
                        $rel    = ltrim($parsed['path'] ?? '', '/');
                        $bucket = config('filesystems.disks.s3.bucket', '');
                        if ($bucket && str_starts_with($rel, $bucket.'/')) {
                            $rel = substr($rel, strlen($bucket) + 1);
                        }
                    }
                    if (! empty($rel)) {
                        Storage::disk('s3')->delete($rel);
                    }
                }
            } else {
                // Local path e.g. /storage/avatars/users/uuid.jpg
                $rel = ltrim(str_replace('/storage/', '', $avatar), '/');
                Storage::disk('public')->delete($rel);
            }
        } catch (\Throwable $e) {
            Log::warning('Avatar delete failed: '.$e->getMessage());
        }
    }
}
