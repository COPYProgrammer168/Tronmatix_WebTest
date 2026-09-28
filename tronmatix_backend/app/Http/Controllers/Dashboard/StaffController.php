<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Staff;
use App\Models\StaffInvite;
use App\Models\StaffInviteLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class StaffController extends Controller
{
    // ── Auth helpers ──────────────────────────────────────────────────────────

    private function me(): Admin
    {
        $user = Auth::guard('admin')->user();
        if (!$user instanceof Admin) {
            abort(401, 'Unauthorized');
        }
        return $user;
    }

    private function assertAdmin(): void
    {
        abort_unless(
            in_array($this->me()->role, ['admin', 'superadmin']),
            403,
            'Access denied.'
        );
    }

    private function isSuper(): bool
    {
        return $this->me()->role === 'superadmin';
    }

    // ── index ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $this->assertAdmin();

        $admins = Admin::orderByRaw("
                CASE role
                    WHEN 'superadmin' THEN 1
                    WHEN 'admin'      THEN 2
                    ELSE 3
                END
            ")->orderBy('name')->get();

        $staff = Staff::orderByRaw("
                CASE role
                    WHEN 'editor'    THEN 1
                    WHEN 'seller'    THEN 2
                    WHEN 'delivery'  THEN 3
                    WHEN 'developer' THEN 4
                    ELSE 5
                END
            ")->orderBy('name')->get();

        // Pending invitations (not yet accepted) — shown in the staff tab
        $pendingInvites = StaffInvite::whereNull('used_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderByDesc('created_at')
            ->with('links') // eager-load links for display
            ->get();

        return view('dashboard.staff', compact('admins', 'staff', 'pendingInvites'));
    }

    // ── invite ────────────────────────────────────────────────────────────────
    // Creates a pending invitation with a set-password link. No staff account
    // is created until the invited person opens the link and sets a password.

    public function invite(Request $request)
    {
        $this->assertAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'alpha_dash',
                'unique:staff,username',
                'unique:admins,username'
            ],
            'email' => ['nullable', 'email', 'max:150', 'unique:staff,email', 'unique:admins,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'in:' . implode(',', Staff::ROLES)],
        ]);

        // At least one contact method required (email or phone)
        if (empty($data['email']) && empty($data['phone'])) {
            return back()->withErrors(['email' => 'Provide at least an email or phone number.'])->withInput();
        }

        $invite = StaffInvite::create([
            'token'      => Str::random(64),
            'name'       => $data['name'],
            'username'   => $data['username'],
            'email'      => $data['email'] ?? null,
            'phone'      => \App\Support\PhoneHelper::toE164($data['phone'] ?? null),
            'role'       => $data['role'],
            'expires_at' => null,
        ]);

        // Create the first invite link (7-day default expiry)
        $link = $invite->links()->create([
            'token'      => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        \App\Services\ActivityLogger::staffInvited($data['name'], $data['role'], $request);

        return redirect()->route('dashboard.staff')
            ->with('success', "Invite created for {$data['name']}. Share this link — they set their own email and password: " . $link->inviteUrl());
    }

    // ── resendInvite ──────────────────────────────────────────────────────────
    // Creates a NEW invite link for an existing invite (unlimited links possible).
    // Each link has its own expiration and token. The old links remain valid until
    // they expire or are used.

    public function resendInvite(int $id, Request $request)
    {
        $this->assertAdmin();

        $invite = StaffInvite::findOrFail($id);

        $link = $invite->links()->create([
            'token'      => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        return back()->with('success', 'New invite link generated (link #' . $invite->links()->count() . '): ' . $link->inviteUrl());
    }

    // ── updateRole ────────────────────────────────────────────────────────────

    public function updateRole(Request $request, int $id)
    {
        $this->assertAdmin();

        $data = $request->validate([
            'role' => ['required', 'in:editor,seller,delivery,developer'],
        ]);

        $member = Staff::findOrFail($id);
        $oldRole = $member->role;
        $member->update(['role' => $data['role']]);

        \App\Services\ActivityLogger::staffRoleChanged($member, $oldRole, $data['role'], $request);

        return back()->with('success', "{$member->name}'s role updated to " . ucfirst($data['role']) . '.');
    }

    // ── toggle active/inactive ────────────────────────────────────────────────

    public function toggle(int $id, \Illuminate\Http\Request $request)
    {
        $this->assertAdmin();

        $member = Staff::findOrFail($id);
        $wasActive = $member->is_active;
        $member->update(['is_active' => !$member->is_active]);

        $status = $member->is_active ? 'activated' : 'deactivated';

        \App\Services\ActivityLogger::staffToggled($member, $member->is_active, $request);

        return back()->with('success', "{$member->name} has been {$status}.");
    }

    // ── heartbeat ─────────────────────────────────────────────────────────────

    public function heartbeat(Request $request)
    {
        // Try session guards first (web dashboard), then Sanctum token (React dashboard)
        $user = Auth::guard('admin')->user() ?? Auth::guard('staff')->user() ?? $request->user();

        if ($user) {
            $now = now();
            $user->update([
                'last_seen_at'  => $now,
                'online_status' => 'online',
            ]);
        }
        return response()->json(['ok' => true, 'user' => $user?->name]);
    }

    // ── setOffline ────────────────────────────────────────────────────────────

    public function setOffline(Request $request)
    {
        $user = Auth::guard('admin')->user() ?? Auth::guard('staff')->user();
        if ($user instanceof Staff) {
            $user->update(['online_status' => 'offline']);
        }
        return response()->json(['ok' => true]);
    }

    // ── destroy ───────────────────────────────────────────────────────────────

    public function destroy(int $id, Request $request)
    {
        $this->assertAdmin();

        $member = Staff::findOrFail($id);
        $name = $member->name;
        $memberId = $member->id;
        $member->delete();

        \App\Services\ActivityLogger::staffDeleted($member, $request);

        return redirect()->route('dashboard.staff')
            ->with('success', "{$name} has been removed from the team.");
    }
}
