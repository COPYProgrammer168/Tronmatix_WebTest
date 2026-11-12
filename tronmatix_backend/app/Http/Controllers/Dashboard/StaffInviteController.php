<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\StaffInviteLink;
use Illuminate\Http\Request;

class StaffInviteController extends Controller
{
    // ── show ──────────────────────────────────────────────────────────────────
    // Public: renders the "set your password" form for a valid invite link token.

    public function show(string $token)
    {
        $link = StaffInviteLink::where('token', $token)->first();

        if (! $link || ! $link->isValid()) {
            abort(404, 'This invite link is invalid or has already been used.');
        }

        return view('dashboard.auth.accept-invite', compact('link'));
    }

    // ── accept ────────────────────────────────────────────────────────────────
    // Public: creates the Staff account once the invited person sets a password.

    public function accept(Request $request, string $token)
    {
        $link = StaffInviteLink::where('token', $token)->first();

        if (! $link || ! $link->isValid()) {
            abort(404, 'This invite link is invalid or has already been used.');
        }

        $invite = $link->invite;

        $data = $request->validate([
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->mixedCase()->numbers()],
            'email'    => ['nullable', 'email', 'max:150'],
        ]);

        // Use submitted email if invite didn't have one
        $email = $invite->email ?: $data['email'];

        if (!$email) {
            $link->update(['used_at' => now()]); // burn this link
            return back()->withErrors(['email' => 'Email is required to activate the account.'])->withInput();
        }

        // Race guard — the email/username may have been taken since the invite
        // was created (e.g. another invite, or a manual direct create).
        $emailTaken    = \App\Models\Admin::where('email', $email)->exists()
                       || \App\Models\Staff::where('email', $email)->exists();
        $usernameTaken = \App\Models\Admin::where('username', $invite->username)->exists()
                       || \App\Models\Staff::where('username', $invite->username)->exists();

        if ($emailTaken || $usernameTaken) {
            $link->update(['used_at' => now()]); // burn this link

            return back()->withErrors([
                'password' => 'This invite is no longer valid — the email or username is already taken. Contact your administrator for a new invite.',
            ])->withInput();
        }

        \App\Models\Staff::create([
            'name'      => $invite->name,
            'username'  => $invite->username,
            'email'     => $email,
            'phone'     => $invite->phone,
            'role'      => $invite->role,
            'password'  => \Illuminate\Support\Facades\Hash::make($request->password),
            'is_active' => true,
        ]);

        $link->use();

        return redirect()->route('dashboard.login', ['mode' => 'staff'])
            ->with('success', 'Account activated! Please log in below with your email and the password you just set.');
    }
}
