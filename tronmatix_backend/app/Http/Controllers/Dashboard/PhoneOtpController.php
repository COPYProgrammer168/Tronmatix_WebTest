<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\PasswordOtp;
use App\Models\Staff;
use App\Services\SmsSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PhoneOtpController extends Controller
{
    public function __construct(
        private readonly SmsSender $sms
    ) {}

    /**
     * GET /dashboard/password/phone
     * Show the phone-number form (request an OTP).
     */
    public function showPhoneForm(Request $request)
    {
        $mode = $request->input('mode', 'staff'); // admin | staff

        return view('dashboard.auth.phone-otp', compact('mode'));
    }

    /**
     * POST /dashboard/password/phone  (throttled 1/min)
     * Generate a 6-digit OTP and SMS it to the account's phone (if it exists).
     */
    public function requestOtp(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'mode'  => ['required', 'in:admin,staff'],
        ]);

        $account = $this->findByPhone($data['mode'], $data['phone']);

        // Anti-enumeration: return the same message whether or not the phone
        // exists; only send an SMS when there is a real account.
        if ($account) {
            $otp = Str::padLeft((string) random_int(0, 999999), 6, '0');

            PasswordOtp::create([
                'phone'      => $data['phone'],
                'mode'       => $data['mode'],
                'otp'        => $otp,
                'expires_at' => now()->addMinutes(5),
            ]);

            $this->sms->send(
                $data['phone'],
                'Your Tronmatix password reset code is ' . $otp . '. It expires in 5 minutes.'
            );
        }

        return redirect()
            ->route('dashboard.password.phone.verify', ['mode' => $data['mode']])
            ->withInput($request->only('phone', 'mode'))
            ->with('status', 'If that phone number belongs to an account, a 6-digit verification code has been sent.');
    }

    /**
     * GET /dashboard/password/phone/verify
     * Show the OTP + new-password form.
     */
    public function showVerifyForm(Request $request)
    {
        $mode  = $request->input('mode', 'staff');
        $phone = $request->old('phone');

        return view('dashboard.auth.phone-verify', compact('mode', 'phone'));
    }

    /**
     * POST /dashboard/password/phone/verify  (throttled 5/min)
     * Validate the OTP and set the new password.
     */
    public function verifyOtpAndReset(Request $request)
    {
        $data = $request->validate([
            'phone'                 => ['required', 'string', 'max:30'],
            'mode'                  => ['required', 'in:admin,staff'],
            'otp'                   => ['required', 'digits:6'],
            'password'              => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
            'password_confirmation' => ['required'],
        ]);

        $otp = PasswordOtp::where('phone', $data['phone'])
            ->where('mode', $data['mode'])
            ->latest()
            ->first();

        if (! $otp || ! $otp->isValid() || $otp->otp !== $data['otp']) {
            return back()
                ->withInput($request->only('phone', 'mode'))
                ->withErrors(['otp' => 'Invalid or expired verification code. Please request a new one.']);
        }

        $account = $this->findByPhone($data['mode'], $data['phone']);

        if (! $account) {
            return back()
                ->withInput($request->only('phone', 'mode'))
                ->withErrors(['otp' => 'No account found with that phone number.']);
        }

        $account->forceFill(['password' => Hash::make($data['password'])])->save();

        // Burn the OTP so it can never be replayed.
        $otp->use();

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
