<?php

// app/Http/Controllers/StaffRequestController.php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\StaffRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StaffRequestController extends Controller
{
    // ── Show the request-access form (public, unauthenticated) ────────────────

    public function showForm()
    {
        // If already logged in, go to dashboard
        if (Auth::guard('admin')->check()) {
            return redirect()->route('dashboard.index');
        }

        // If no admins exist at all, redirect to full registration
        if (Admin::count() === 0) {
            return redirect()->route('dashboard.register');
        }

        return view('dashboard.auth.request-access');
    }

    // ── Submit a staff access request ─────────────────────────────────────────

    public function submit(Request $request)
    {
        if (Admin::count() === 0) {
            return redirect()->route('dashboard.register');
        }

        $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'email'          => ['required', 'email', 'unique:admins,email', 'unique:staff_requests,email'],
            'username'       => ['required', 'string', 'min:3', 'max:50', 'alpha_dash',
                                 'unique:admins,username', 'unique:staff_requests,username'],
            'requested_role' => ['required', 'in:admin,editor,seller,viewer'],
            'message'        => ['nullable', 'string', 'max:500'],
            'password'       => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        StaffRequest::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'username'       => $request->username,
            'password'       => Hash::make($request->password),
            'requested_role' => $request->requested_role,
            'message'        => $request->message,
            'status'         => 'pending',
        ]);

        return redirect()->route('dashboard.login')
            ->with('success', 'Your access request has been submitted. A superadmin will review it shortly.');
    }

    // ── Accept a request (superadmin only) ────────────────────────────────────

    public function accept(int $id)
    {
        $this->assertSuperAdmin();

        $req = StaffRequest::findOrFail($id);

        if (! $req->isPending()) {
            return response()->json(['error' => 'Request already reviewed.'], 422);
        }

        // Check for conflicts again (email/username may have been taken since request)
        if (Admin::where('email', $req->email)->orWhere('username', $req->username)->exists()) {
            $req->update(['status' => 'rejected', 'reviewed_by' => Auth::guard('admin')->id(), 'reviewed_at' => now()]);
            return response()->json(['error' => 'Email or username was taken by another admin. Request rejected.'], 422);
        }

        Admin::create([
            'name'      => $req->name,
            'email'     => $req->email,
            'username'  => $req->username,
            'password'  => $req->password, // already hashed
            'role'      => $req->requested_role,
            'is_active' => true,
        ]);

        $req->update([
            'status'      => 'accepted',
            'reviewed_by' => Auth::guard('admin')->id(),
            'reviewed_at' => now(),
        ]);

        return response()->json(['message' => "{$req->name} has been added as {$req->requested_role}."]);
    }

    // ── Reject a request (superadmin only) ────────────────────────────────────

    public function reject(int $id)
    {
        $this->assertSuperAdmin();

        $req = StaffRequest::findOrFail($id);

        if (! $req->isPending()) {
            return response()->json(['error' => 'Request already reviewed.'], 422);
        }

        $req->update([
            'status'      => 'rejected',
            'reviewed_by' => Auth::guard('admin')->id(),
            'reviewed_at' => now(),
        ]);

        return response()->json(['message' => "{$req->name}'s request has been rejected."]);
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function assertSuperAdmin(): void
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->role === 'superadmin', 403, 'Only superadmins can review staff requests.');
    }
}
