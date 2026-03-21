<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class StaffController extends Controller
{
    // ── Auth helper ───────────────────────────────────────────────────────────

    private function me(): Admin
    {
        $user = Auth::guard('admin')->user();
        if (! $user instanceof Admin) {
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

        $staff = Admin::orderByRaw("
                    CASE role
                        WHEN 'superadmin' THEN 1
                        WHEN 'admin'      THEN 2
                        WHEN 'editor'     THEN 3
                        WHEN 'seller'     THEN 4
                        WHEN 'viewer'     THEN 5
                        ELSE 6
                    END
                ")
            ->orderBy('name')
            ->get();

        return view('dashboard.staff', compact('staff'));
    }

    // ── invite (create) ───────────────────────────────────────────────────────

    public function invite(Request $request)
    {
        $this->assertAdmin();

        $allowedRoles = $this->isSuper()
            ? ['superadmin', 'admin', 'editor', 'seller', 'viewer']
            : ['admin', 'editor', 'seller', 'viewer'];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:admins,email'],
            'role' => ['required', 'in:'.implode(',', $allowedRoles)],
            'password' => ['required', Password::min(8)],
        ]);

        // Regular admins cannot create superadmins
        if (! $this->isSuper() && $data['role'] === 'superadmin') {
            abort(403, 'Only Super Admins can assign the Super Admin role.');
        }

        Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => Str::before($data['email'], '@'),
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        return redirect()->route('dashboard.staff')
            ->with('success', "{$data['name']} has been added to the team.");
    }

    // ── updateRole ────────────────────────────────────────────────────────────

    public function updateRole(Request $request, int $id)
    {
        $this->assertAdmin();

        $allowedRoles = $this->isSuper()
            ? ['superadmin', 'admin', 'editor', 'seller', 'viewer']
            : ['admin', 'editor', 'seller', 'viewer'];

        $data = $request->validate([
            'role' => ['required', 'in:'.implode(',', $allowedRoles)],
        ]);

        $member = Admin::findOrFail($id);

        // Cannot modify superadmins unless you are one
        if ($member->role === 'superadmin' && ! $this->isSuper()) {
            abort(403, 'Only Super Admins can modify Super Admin accounts.');
        }

        // Cannot demote yourself
        if ($member->id === $this->me()->id) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $member->update(['role' => $data['role']]);

        return back()->with('success', "{$member->name}'s role updated to ".ucfirst($data['role']).'.');
    }

    // ── toggle active/inactive ────────────────────────────────────────────────

    public function toggle(int $id)
    {
        $this->assertAdmin();

        $member = Admin::findOrFail($id);

        if ($member->role === 'superadmin' && ! $this->isSuper()) {
            abort(403);
        }

        if ($member->id === $this->me()->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $member->update(['is_active' => ! $member->is_active]);

        $status = $member->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "{$member->name} has been {$status}.");
    }

    // ── destroy ───────────────────────────────────────────────────────────────

    public function destroy(int $id)
    {
        $this->assertAdmin();

        $member = Admin::findOrFail($id);

        if ($member->role === 'superadmin' && ! $this->isSuper()) {
            abort(403, 'Only Super Admins can remove Super Admin accounts.');
        }

        if ($member->id === $this->me()->id) {
            return back()->with('error', 'You cannot remove your own account.');
        }

        $name = $member->name;
        $member->delete();

        return redirect()->route('dashboard.staff')
            ->with('success', "{$name} has been removed from the team.");
    }
}
