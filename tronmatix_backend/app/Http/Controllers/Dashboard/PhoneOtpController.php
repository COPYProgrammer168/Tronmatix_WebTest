<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Staff;
use App\Services\FirebaseAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PhoneOtpController extends Controller
{
    /**
     * GET /dashboard/password/phone
     * Show the phone-number form. SMS delivery is handled by the Firebase
     * client SDK (signInWithPhoneNumber) in the browser — no server SMS.
     */
    public function showPhoneForm(Request $request)
    {
        $mode = $request->input('mode', 'staff'); // admin | staff

        return view('dashboard.auth.phone-otp', compact('mode') + $this->firebaseConfig());
    }

    /**
     * Firebase web-app config used by the client SDK in the Blade views.
     */
    private function firebaseConfig(): array
    {
        return [
            'firebaseApiKey'            => config('firebase.api_key', env('FIREBASE_API_KEY')),
            'firebaseAuthDomain'        => config('firebase.auth_domain', env('FIREBASE_AUTH_DOMAIN')),
            'firebaseProjectId'         => config('firebase.project_id', env('FIREBASE_PROJECT_ID')),
            'firebaseAppId'             => config('firebase.app_id', env('FIREBASE_APP_ID')),
        ];
    }

    /**
     * POST /dashboard/password/phone/verify  (throttled 5/min)
     * Verify the Firebase ID token (issued after the client confirmed the SMS
     * code), then reset the password for the matching admin/staff account.
     */
    public function verifyOtpAndReset(Request $request, FirebaseAuthService $firebase)
    {
        $data = $request->validate([
            'phone'                 => ['required', 'string', 'max:30'],
            'mode'                  => ['required', 'in:admin,staff'],
            'id_token'              => ['required', 'string'],
            'password'              => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
            'password_confirmation' => ['required'],
        ]);

        $claims = $firebase->verifyIdToken($data['id_token']);

        if (! $claims || empty($claims['phone_number'])) {
            return back()
                ->withInput($request->only('phone', 'mode'))
                ->withErrors(['id_token' => 'Verification failed. Please request a new code.']);
        }

        // Confirm the verified phone matches the account we look up.
        $account = $this->findByPhone($data['mode'], $claims['phone_number']);

        if (! $account) {
            return back()
                ->withInput($request->only('phone', 'mode'))
                ->withErrors(['phone' => 'No account found with that phone number.']);
        }

        $account->forceFill(['password' => Hash::make($data['password'])])->save();

        return redirect()->route('dashboard.login')
            ->with('success', 'Your password has been reset. You can now log in.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function findByPhone(string $mode, string $phone): Admin|Staff|null
    {
        return $mode === 'admin'
            ? Admin::where('phone', $phone)->first()
            : Staff::where('phone', $phone)->first();
    }
}
