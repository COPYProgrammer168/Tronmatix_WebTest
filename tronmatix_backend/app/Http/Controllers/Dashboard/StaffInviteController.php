<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Staff;
use App\Models\StaffInvite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StaffInviteController extends Controller
{
    // ── show ──────────────────────────────────────────────────────────────────
    // Public: renders the "set your password" form for a valid invite token.

    public function show(string $token)
    {
        $invite = StaffInvite::where('token', $token)->first();

        if (! $invite || ! $invite->isValid()) {
            abort(404, 'This invite link is invalid or has already been used.');
        }

        return view('dashboard.auth.accept-invite', compact('invite'));
    }

    // ── accept ────────────────────────────────────────────────────────────────
    // Public: creates the Staff account once the invited person sets a password.

    public function accept(Request $request, string $token)
    {
        $invite = StaffInvite::where('token', $token)->first();

        if (! $invite || ! $invite->isValid()) {
            abort(404, 'This invite link is invalid or has already been used.');
        }

        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        // Race guard — the email/username may have been taken since the invite
        // was created (e.g. another invite, or a manual direct create).
        $emailTaken    = Admin::where('email', $invite->email)->exists()
                       || Staff::where('email', $invite->email)->exists();
        $usernameTaken = Admin::where('username', $invite->username)->exists()
                       || Staff::where('username', $invite->username)->exists();

        if ($emailTaken || $usernameTaken) {
            $invite->update(['used_at' => now()]); // burn the invite

            return back()->withErrors([
                'password' => 'This invite is no longer valid — the email or username is already taken. Contact your administrator for a new invite.',
            ])->withInput();
        }

        Staff::create([
            'name'      => $invite->name,
            'username'  => $invite->username,
            'email'     => $invite->email,
            'phone'     => $invite->phone,
            'role'      => $invite->role,
            'password'  => Hash::make($data['password']),
            'is_active' => true,
        ]);

        $invite->use();

        return redirect()->route('dashboard.login')
            ->with('success', 'Account activated! You can now log in with your email and the password you just set.');
    }
}
