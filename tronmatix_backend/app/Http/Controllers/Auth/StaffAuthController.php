<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Staff;                          // line 6: Staff — not User
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StaffAuthController extends Controller
{
    // editor | seller | delivery — matches Staff::ROLES (excluding developer)
    private const STAFF_ROLES = ['editor', 'seller', 'delivery'];

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Query staff table — NOT users table
        $staff = Staff::where('email', $request->email)->first();

        if (! $staff || ! Hash::check($request->password, $staff->password)) {
            \App\Services\ActivityLogger::loginFailed($request->email, $request, 'invalid_credentials');

            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        // Block deactivated accounts
        if (! $staff->isActive()) {
            \App\Services\ActivityLogger::loginFailed($request->email, $request, 'account_deactivated');

            return response()->json([
                'message' => 'Your account is deactivated. Contact your administrator.',
            ], 403);
        }

        // Developer must use /api/dev/login
        if (! in_array($staff->role, self::STAFF_ROLES, true)) {
            \App\Services\ActivityLogger::loginFailed($request->email, $request, 'insufficient_role');

            return response()->json([
                'message' => 'Access denied. Use the developer portal to login.',
            ], 403);
        }

        // Single active session — revoke old tokens
        $staff->tokens()->where('name', 'staff-token')->delete();
        $token = $staff->createToken('staff-token')->plainTextToken;

        // Track last login
        $staff->recordLogin();

        \App\Services\ActivityLogger::loginSuccess($staff, $request);

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'       => $staff->id,
                'name'     => $staff->name,
                'username' => $staff->username,
                'email'    => $staff->email,
                'role'     => $staff->role,
                'avatar'   => $staff->avatar,
            ],
        ]);
    }
}
