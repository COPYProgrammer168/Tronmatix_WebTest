<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * PUT /api/settings/password
     * Update the authenticated user's password.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => [
                'required',
                'string',
                'confirmed',              // requires password_confirmation field
                Password::min(8)
                    ->mixedCase()         // upper + lowercase
                    ->numbers()           // at least one number
                    ->symbols()           // at least one symbol
                    ->uncompromised(),    // check against HaveIBeenPwned
            ],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        // Revoke all OTHER tokens so other devices are logged out
        $request->user()
            ->tokens()
            ->where('id', '!=', $request->user()->currentAccessToken()->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    }

    /**
     * POST /api/settings/password/reset-all
     * Revoke ALL tokens — forces logout on every device.
     */
    public function revokeAllTokens(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'All sessions have been terminated.',
        ]);
    }
}
