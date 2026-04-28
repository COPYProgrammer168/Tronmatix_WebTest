<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StaffController extends Controller
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

        return view('dashboard.staff', compact('admins', 'staff'));
    }

    // ── invite ────────────────────────────────────────────────────────────────

    public function invite(Request $request)
    {
        $this->assertAdmin();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash',
                           'unique:staff,username', 'unique:admins,username'],
            'email'    => ['required', 'email',
                           'unique:staff,email', 'unique:admins,email'],
            'role'     => ['required', 'in:editor,seller,delivery,developer'],
            'password' => ['required', Password::min(8)],
        ]);

        Staff::create([
            'name'      => $data['name'],
            'username'  => $data['username'],
            'email'     => $data['email'],
            'role'      => $data['role'],
            'password'  => Hash::make($data['password']),
            'is_active' => true,
        ]);

        return redirect()->route('dashboard.staff')
            ->with('success', "{$data['name']} has been added to the team.");
    }

    // ── updateRole ────────────────────────────────────────────────────────────

    public function updateRole(Request $request, int $id)
    {
        $this->assertAdmin();

        $data = $request->validate([
            'role' => ['required', 'in:editor,seller,delivery,developer'],
        ]);

        $member = Staff::findOrFail($id);
        $member->update(['role' => $data['role']]);

        return back()->with('success', "{$member->name}'s role updated to " . ucfirst($data['role']) . '.');
    }

    // ── toggle active/inactive ────────────────────────────────────────────────

    public function toggle(int $id)
    {
        $this->assertAdmin();

        $member = Staff::findOrFail($id);
        $member->update(['is_active' => ! $member->is_active]);

        $status = $member->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "{$member->name} has been {$status}.");
    }

    // ── destroy ───────────────────────────────────────────────────────────────

    public function destroy(int $id)
    {
        $this->assertAdmin();

        $member = Staff::findOrFail($id);
        $name   = $member->name;
        $member->delete();

        return redirect()->route('dashboard.staff')
            ->with('success', "{$name} has been removed from the team.");
    }
}
