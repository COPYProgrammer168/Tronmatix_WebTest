<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Traits\StorageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    use StorageHelper;

    public function show()
    {
        return view('dashboard.profile');
    }

    public function update(Request $request)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:admins,email,'.$admin->id,
            'username' => 'nullable|string|max:100|unique:admins,username,'.$admin->id,
            'avatar'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

<<<<<<< HEAD
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            // Delete old avatar
            $this->deleteStorageFile($admin->avatar);

            // Store new avatar and get URL
            $file     = $request->file('avatar');
            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
            $data['avatar'] = $this->storeFileAs($file, 'avatars/admins', $filename);
=======
        // FIX: Upload avatar to S3/R2 instead of local 'public' disk
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            // Delete old avatar
            $this->deleteAvatar($admin->avatar);

            $disk = $this->storageDisk();
            $path = $request->file('avatar')->store('avatars/admins', $disk);
            $data['avatar'] = Storage::disk($disk)->url($path);
>>>>>>> 82a51346582e7e958aee906bd907014d342a8a3b
        }

        if (!isset($data['avatar'])) unset($data['avatar']);

        $admin->update(array_filter($data, fn($v) => $v !== null));

        return redirect()->route('dashboard.profile')
            ->with('success', 'Profile updated successfully.');
    }

    public function removeAvatar()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();
<<<<<<< HEAD
        $this->deleteStorageFile($admin->avatar);
=======
        $this->deleteAvatar($admin->avatar);
>>>>>>> 82a51346582e7e958aee906bd907014d342a8a3b
        $admin->update(['avatar' => null]);

        return redirect()->route('dashboard.profile')
            ->with('success', 'Avatar removed.');
    }

    public function updatePassword(Request $request)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'current_password' => ['required', function ($attr, $value, $fail) use ($admin) {
                if (!Hash::check($value, $admin->password)) $fail('The current password is incorrect.');
            }],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $admin->update(['password' => Hash::make($request->password)]);

        return redirect()->route('dashboard.profile')
            ->with('success', 'Password changed successfully.');
    }

    public function updateRole(Request $request)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        if (!$admin->isSuperAdmin()) abort(403, 'Only superadmins can change roles.');

        $request->validate(['role' => 'required|in:superadmin,admin,editor']);
        $admin->update(['role' => $request->role]);

        return redirect()->route('dashboard.profile')
            ->with('success', 'Role updated to '.strtoupper($request->role).'.');
<<<<<<< HEAD
=======
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function deleteAvatar(?string $avatar): void
    {
        if (!$avatar) return;

        $disk = $this->storageDisk();

        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            $bucket = config("filesystems.disks.{$disk}.bucket");
            $key    = preg_replace('#^https?://[^/]+/(?:'.preg_quote($bucket, '#').'/)?#', '', $avatar);
            if ($key) Storage::disk($disk)->delete($key);
        } elseif (Storage::disk('public')->exists($avatar)) {
            Storage::disk('public')->delete($avatar);
        }
    }

    private function storageDisk(): string
    {
        return config('filesystems.default') === 's3' ? 's3' : 'public';
>>>>>>> 82a51346582e7e958aee906bd907014d342a8a3b
    }
}
