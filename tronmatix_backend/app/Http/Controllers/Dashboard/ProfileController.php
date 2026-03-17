<?php
// app/Http/Controllers/Dashboard/ProfileController.php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    // ── Show profile page ─────────────────────────────────────────────────────
    public function show()
    {
        return view('dashboard.profile');
    }

    // ── Update name, email, username ──────────────────────────────────────────
    public function update(Request $request)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:admins,email,' . $admin->id,
            // username optional — only validate if the form includes it
            'username' => 'nullable|string|max:100|unique:admins,username,' . $admin->id,
        ]);

        // array_filter removes null values so we never wipe username if not sent
        $admin->update(array_filter($data, fn ($v) => $v !== null));

        return redirect()->route('dashboard.profile')
            ->with('success', 'Profile updated successfully.');
    }

    // ── Change password ───────────────────────────────────────────────────────
    public function updatePassword(Request $request)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'current_password' => [
                'required',
                function ($attr, $value, $fail) use ($admin) {
                    if (! Hash::check($value, $admin->password)) {
                        $fail('The current password is incorrect.');
                    }
                },
            ],
            // Min 8 chars — matches Password::min(8) but written explicitly
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Admin model has no 'hashed' cast → hash manually
        $admin->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('dashboard.profile')
            ->with('success', 'Password changed successfully.');
    }

    // ── Update role ───────────────────────────────────────────────────────────
    // Enum in migration: superadmin | admin | editor  (no 'viewer')
    public function updateRole(Request $request)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        if (! $admin->isSuperAdmin()) {
            abort(403, 'Only superadmins can change roles.');
        }

        $request->validate([
            'role' => 'required|in:superadmin,admin,editor',
        ]);

        $admin->update(['role' => $request->role]);

        return redirect()->route('dashboard.profile')
            ->with('success', 'Role updated to ' . strtoupper($request->role) . '.');
    }
}
