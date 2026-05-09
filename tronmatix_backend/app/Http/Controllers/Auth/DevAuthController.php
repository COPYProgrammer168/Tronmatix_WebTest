<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Staff;                          // line 6: Staff — not User
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DevAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
            'dev_key'  => 'required|string',
        ]);

        // Validate secret developer key from .env
        if ($request->dev_key !== config('app.dev_portal_key')) {
            sleep(1); // Slow down brute-force
            return response()->json(['message' => 'Invalid developer key.'], 403);
        }

        // Query staff table where role=developer — NOT users table
        $staff = Staff::where('email', $request->email)
                      ->where('role', 'developer')
                      ->first();

        if (! $staff || ! Hash::check($request->password, $staff->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        // Block deactivated developer accounts
        if (! $staff->isActive()) {
            return response()->json([
                'message' => 'Your account is deactivated. Contact your administrator.',
            ], 403);
        }

        // Single active session — revoke old tokens
        $staff->tokens()->where('name', 'dev-token')->delete();
        $token = $staff->createToken('dev-token')->plainTextToken;

        $staff->recordLogin();

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
