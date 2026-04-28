<?php

// app/Http/Controllers/AdminAuthController.php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        // Already authenticated under either guard — skip login page
        if (Auth::guard('admin')->check() || Auth::guard('staff')->check()) {
            return redirect()->route('dashboard.index');
        }

        return view('dashboard.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
            'mode'     => ['nullable', 'in:admin,staff'],
        ]);

        // ── Rate limiting: 5 attempts / min per login+IP ──────────────────────
        $throttleKey = Str::lower($request->input('login', '')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withInput($request->only('login'))
                ->with('error', "Too many login attempts. Please try again in {$seconds} seconds.");
        }

        $field    = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $remember = $request->boolean('remember');
        $mode     = $request->input('mode', 'admin'); // Default to admin if not provided

        // ── Mode-based authentication ─────────────────────────────────────────
        // Each mode ONLY checks its own table — no cross-authentication

        if ($mode === 'staff') {
            // ── STAFF MODE: Only check staff table ────────────────────────────
            $staff = Staff::where($field, $request->login)->first();

            if ($staff) {
                if (! $staff->isActive()) {
                    RateLimiter::hit($throttleKey, 60);
                    return back()->withInput($request->only('login'))
                        ->with('error', 'Your account has been deactivated. Contact your admin.');
                }

                if (! Hash::check($request->password, $staff->password)) {
                    RateLimiter::hit($throttleKey, 60);
                    return back()->withInput($request->only('login'))
                        ->with('error', 'No account found with those credentials.');
                }

                RateLimiter::clear($throttleKey);
                Auth::guard('staff')->login($staff, $remember);
                $staff->recordLogin();
                $request->session()->regenerate();

                return redirect()->intended(route('dashboard.index'))
                    ->with('success', 'Welcome back, ' . $staff->name . '!');
            }

            // Staff mode failed — do NOT check admins table
            RateLimiter::hit($throttleKey, 60);
            return back()->withInput($request->only('login'))
                ->with('error', 'No staff account found with those credentials.');

        } else {
            // ── ADMIN MODE: Only check admins table ───────────────────────────
            $admin = Admin::where($field, $request->login)->first();

            if ($admin) {
                if (! $admin->isActive()) {
                    RateLimiter::hit($throttleKey, 60);
                    return back()->withInput($request->only('login'))
                        ->with('error', 'Your account has been deactivated. Contact superadmin.');
                }

                if (! Hash::check($request->password, $admin->password)) {
                    RateLimiter::hit($throttleKey, 60);
                    return back()->withInput($request->only('login'))
                        ->with('error', 'No account found with those credentials.');
                }

                RateLimiter::clear($throttleKey);
                Auth::guard('admin')->login($admin, $remember);
                $admin->recordLogin();
                $request->session()->regenerate();

                return redirect()->intended(route('dashboard.index'))
                    ->with('success', 'Welcome back, ' . $admin->name . '!');
            }

            // Admin mode failed — do NOT check staff table
            RateLimiter::hit($throttleKey, 60);
            return back()->withInput($request->only('login'))
                ->with('error', 'No admin account found with those credentials.');
        }
    }

    public function showRegister()
    {
        if (Auth::guard('admin')->check() || Auth::guard('staff')->check()) {
            return redirect()->route('dashboard.index');
        }
        if (Admin::count() > 0) {
            return redirect()->route('dashboard.login')
                ->with('error', 'Registration is closed. Contact superadmin.');
        }

        return view('dashboard.auth.register');
    }

    public function register(Request $request)
    {
        if (Admin::count() > 0) {
            return redirect()->route('dashboard.login')->with('error', 'Registration is closed.');
        }

        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:100'],
            'email'                 => ['required', 'email', 'unique:admins,email'],
            'username'              => ['required', 'string', 'min:3', 'max:50', 'unique:admins,username', 'alpha_dash'],
            'password'              => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'password_confirmation' => ['required'],
        ]);

        $admin = Admin::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'username'  => $validated['username'],
            'password'  => Hash::make($validated['password']),
            'role'      => 'superadmin',
            'is_active' => true,
        ]);

        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();

        return redirect()->route('dashboard.index')
            ->with('success', 'Admin account created! Welcome, ' . $admin->name . '.');
    }

    public function logout(Request $request)
    {
        // Log out whichever guard is active
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        } elseif (Auth::guard('staff')->check()) {
            Auth::guard('staff')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dashboard.login')
            ->with('success', 'You have been logged out.');
    }
}
