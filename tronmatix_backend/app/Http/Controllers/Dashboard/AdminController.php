<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    // ── Auth helpers ──────────────────────────────────────────────────────────

    private function me(): Admin
    {
        $user = Auth::guard('admin')->user();
        if (! $user instanceof Admin) {
            abort(401, 'Unauthorized');
        }
        return $user;
    }

    private function assertSuperAdmin(): void
    {
        abort_unless($this->me()->role === 'superadmin', 403, 'Only Super Admins can manage admin accounts.');
    }

    // ── invite (create admin) ─────────────────────────────────────────────────

    public function invite(Request $request)
    {
        $this->assertSuperAdmin();

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email'],
            'admin_role' => ['required', 'in:superadmin,admin'],
            'password'   => ['required', Password::min(8)],
        ]);

        // Email must be unique across both tables
        if (
            Admin::where('email', $data['email'])->exists() ||
            \App\Models\Staff::where('email', $data['email'])->exists()
        ) {
            return back()->withErrors(['email' => 'This email is already taken.'])->withInput();
        }

        Admin::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'username'  => Str::before($data['email'], '@'),
            'role'      => $data['admin_role'],
            'password'  => Hash::make($data['password']),
            'is_active' => true,
        ]);

        return redirect()->route('dashboard.staff', ['tab' => 'admins'])
            ->with('success', "{$data['name']} has been added as " . ucfirst($data['admin_role']) . '.');
    }

    // ── updateRole ────────────────────────────────────────────────────────────

    public function updateRole(Request $request, int $id)
    {
        $this->assertSuperAdmin();

        $data = $request->validate([
            'role' => ['required', 'in:superadmin,admin'],
        ]);

        $member = Admin::findOrFail($id);

        if ($member->id === $this->me()->id) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $member->update(['role' => $data['role']]);

        return back()->with('success', "{$member->name}'s role updated to " . ucfirst($data['role']) . '.');
    }

    // ── toggle active/inactive ────────────────────────────────────────────────

    public function toggle(int $id)
    {
        $this->assertSuperAdmin();

        $member = Admin::findOrFail($id);

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
        $this->assertSuperAdmin();

        $member = Admin::findOrFail($id);

        if ($member->id === $this->me()->id) {
            return back()->with('error', 'You cannot remove your own account.');
        }

        $name = $member->name;
        $member->delete();

        return redirect()->route('dashboard.staff', ['tab' => 'admins'])
            ->with('success', "{$name} has been removed.");
    }
}
