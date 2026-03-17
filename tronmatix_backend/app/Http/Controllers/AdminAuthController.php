<?php

// app/Http/Controllers/AdminAuthController.php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('dashboard.index');
        }

        return view('dashboard.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ]);

        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $admin = Admin::where($field, $request->login)->first();

        if (! $admin) {
            return back()->withInput($request->only('login'))
                ->with('error', 'No account found with those credentials.');
        }

        // FIX [2]: use isActive() model method
        if (! $admin->isActive()) {
            return back()->withInput($request->only('login'))
                ->with('error', 'Your account has been deactivated. Contact superadmin.');
        }

        if (! Hash::check($request->password, $admin->password)) {
            return back()->withInput($request->only('login'))
                ->with('error', 'Incorrect password.');
        }

        Auth::guard('admin')->login($admin, $request->boolean('remember'));

        // FIX [1]: use recordLogin() model method
        $admin->recordLogin();

        $request->session()->regenerate();

        return redirect()->route('dashboard.index')
            ->with('success', 'Welcome back, '.$admin->name.'!');
    }

    public function showRegister()
    {
        if (Auth::guard('admin')->check()) {
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
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:admins,email'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'unique:admins,username', 'alpha_dash'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'password_confirmation' => ['required'],
        ]);

        $admin = Admin::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();

        return redirect()->route('dashboard.index')
            ->with('success', 'Admin account created! Welcome, '.$admin->name.'.');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dashboard.login')->with('success', 'You have been logged out.');
    }
}
