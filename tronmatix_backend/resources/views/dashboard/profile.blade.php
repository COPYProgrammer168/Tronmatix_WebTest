{{-- resources/views/dashboard/profile.blade.php --}}
@extends('dashboard.layout')
@section('title', 'MY PROFILE')

@section('content')
<div style="max-width:800px; margin:0 auto;">

    {{-- ── Page header ─────────────────────────────────────────────────────── --}}
    <div style="margin-bottom:28px; display:flex; align-items:center; gap:16px;">
        <div style="
            width:56px; height:56px; border-radius:50%;
            background:linear-gradient(135deg,#F97316,#ea580c);
            display:flex; align-items:center; justify-content:center;
            font-size:22px; font-weight:800; color:#fff; flex-shrink:0;
            box-shadow:0 4px 16px rgba(249,115,22,0.35);
        ">
            {{ strtoupper(substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1)) }}
        </div>
        <div>
            <div style="font-size:24px; font-weight:800; letter-spacing:2px;">
                {{ Auth::guard('admin')->user()->name ?? 'Admin' }}
            </div>
            <div style="font-size:14px; color:rgba(255,255,255,0.4); margin-top:2px; letter-spacing:1px;">
                {{ Auth::guard('admin')->user()->email ?? '' }}
            </div>
        </div>
        <div style="margin-left:auto;">
            <span class="badge badge-orange" style="font-size:13px; letter-spacing:2px;">
                {{ strtoupper(Auth::guard('admin')->user()->role ?? 'ADMIN') }}
            </span>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

        {{-- ── Left: Edit Profile ──────────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <span style="font-size:16px; font-weight:800; letter-spacing:2px;">✏️ EDIT PROFILE</span>
            </div>
            <div style="padding:24px;">
                <form method="POST" action="{{ route('dashboard.profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label">FULL NAME</label>
                        <input type="text" name="name" class="form-control"
                            value="{{ old('name', Auth::guard('admin')->user()->name) }}"
                            placeholder="Admin name" required
                            style="border-color:{{ $errors->has('name') ? '#EF4444' : 'rgba(255,255,255,0.1)' }};" />
                        @error('name')
                            <div style="color:#EF4444; font-size:13px; margin-top:5px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">EMAIL ADDRESS</label>
                        <input type="email" name="email" class="form-control"
                            value="{{ old('email', Auth::guard('admin')->user()->email) }}"
                            placeholder="admin@example.com" required
                            style="border-color:{{ $errors->has('email') ? '#EF4444' : 'rgba(255,255,255,0.1)' }};" />
                        @error('email')
                            <div style="color:#EF4444; font-size:13px; margin-top:5px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-orange" style="width:100%; justify-content:center; font-size:15px;">
                        <svg style="width:16px;height:16px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                        </svg>
                        SAVE CHANGES
                    </button>
                </form>
            </div>
        </div>

        {{-- ── Right: Change Password ───────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <span style="font-size:16px; font-weight:800; letter-spacing:2px;">🔒 CHANGE PASSWORD</span>
            </div>
            <div style="padding:24px;">
                <form method="POST" action="{{ route('dashboard.profile.password') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label">CURRENT PASSWORD</label>
                        <input type="password" name="current_password" class="form-control"
                            placeholder="Enter current password"
                            style="border-color:{{ $errors->has('current_password') ? '#EF4444' : 'rgba(255,255,255,0.1)' }};" />
                        @error('current_password')
                            <div style="color:#EF4444; font-size:13px; margin-top:5px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">NEW PASSWORD</label>
                        <input type="password" name="password" class="form-control"
                            placeholder="Min. 8 characters"
                            style="border-color:{{ $errors->has('password') ? '#EF4444' : 'rgba(255,255,255,0.1)' }};" />
                        @error('password')
                            <div style="color:#EF4444; font-size:13px; margin-top:5px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">CONFIRM NEW PASSWORD</label>
                        <input type="password" name="password_confirmation" class="form-control"
                            placeholder="Repeat new password"
                            style="border-color:rgba(255,255,255,0.1);" />
                    </div>

                    <button type="submit" class="btn btn-outline" style="width:100%; justify-content:center; font-size:15px;">
                        <svg style="width:16px;height:16px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        UPDATE PASSWORD
                    </button>
                </form>
            </div>
        </div>

    </div>{{-- /grid --}}

    {{-- ── Change Role ──────────────────────────────────────────────────────── --}}
    {{-- @php
        $adminRoles = [
            'admin'      => ['label' => 'Admin',       'icon' => '🛡️',  'desc' => 'Full access to all sections'],
            'superadmin' => ['label' => 'Super Admin', 'icon' => '👑',  'desc' => 'Owner-level access, all permissions'],
            'editor'     => ['label' => 'Editor',      'icon' => '✏️',  'desc' => 'Can manage products & content'],
            'viewer'     => ['label' => 'Viewer',      'icon' => '👁️',  'desc' => 'Read-only access to dashboard'],
        ];
        $currentAdminRole = Auth::guard('admin')->user()->role ?? 'admin';
    @endphp

    <div class="card" style="margin-top:20px;">
        <div class="card-header">
            <span style="font-size:16px; font-weight:800; letter-spacing:2px;">🎭 CHANGE ROLE</span>
            <span style="font-size:13px; color:rgba(255,255,255,0.3);">
                Current: <span style="color:#F97316; font-weight:700;">{{ strtoupper($currentAdminRole) }}</span>
            </span>
        </div>
        <div style="padding:20px;">
            <form method="POST" action="{{ route('dashboard.profile.role') }}"
                  onsubmit="return confirmRoleChange(this)">
                @csrf
                @method('PUT')
                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:12px; margin-bottom:20px;">
                    @foreach($adminRoles as $roleKey => $roleInfo)
                    <label style="cursor:pointer; display:block;">
                        <input type="radio" name="role" value="{{ $roleKey }}"
                               {{ $currentAdminRole === $roleKey ? 'checked' : '' }}
                               style="display:none;"
                               class="role-radio"
                               onchange="updateRoleSelection()" />
                        <div class="role-option-card {{ $currentAdminRole === $roleKey ? 'selected' : '' }}"
                             id="role-card-{{ $roleKey }}"
                             style="
                                padding:14px 16px; border-radius:12px; border:2px solid;
                                transition: all 0.18s;
                                {{ $currentAdminRole === $roleKey
                                    ? 'border-color:#F97316; background:rgba(249,115,22,0.1);'
                                    : 'border-color:rgba(255,255,255,0.08); background:rgba(255,255,255,0.02);' }}
                             ">
                            <div style="font-size:22px; margin-bottom:6px;">{{ $roleInfo['icon'] }}</div>
                            <div style="font-size:14px; font-weight:800; letter-spacing:1px; color:#fff;">
                                {{ strtoupper($roleInfo['label']) }}
                            </div>
                            <div style="font-size:11px; color:rgba(255,255,255,0.4); margin-top:4px; line-height:1.4;">
                                {{ $roleInfo['desc'] }}
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>

                @error('role')
                    <div style="color:#EF4444; font-size:13px; margin-bottom:12px;">{{ $message }}</div>
                @enderror

                <button type="submit" id="roleSubmitBtn"
                        class="btn btn-orange"
                        style="justify-content:center; font-size:15px; opacity:0.4; pointer-events:none;"
                        disabled>
                    <svg style="width:16px;height:16px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    APPLY ROLE CHANGE
                </button>
            </form>
        </div>
    </div> --}}

    {{-- ── Account Info ─────────────────────────────────────────────────────── --}}
    <div class="card" style="margin-top:20px;">
        <div class="card-header">
            <span style="font-size:16px; font-weight:800; letter-spacing:2px;">📋 ACCOUNT INFORMATION</span>
        </div>
        <div style="padding:20px;">
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:20px;">
                @php
                    $admin = Auth::guard('admin')->user();
                    $infos = [
                        ['label' => 'ACCOUNT ID',    'value' => '#' . ($admin->id ?? '—')],
                        ['label' => 'ROLE',          'value' => strtoupper($admin->role ?? 'ADMIN')],
                        ['label' => 'MEMBER SINCE',  'value' => $admin->created_at ? $admin->created_at->format('M Y') : '—'],
                        ['label' => 'LAST UPDATED',  'value' => $admin->updated_at ? $admin->updated_at->diffForHumans() : '—'],
                    ];
                @endphp
                @foreach($infos as $info)
                <div style="
                    padding:16px; border-radius:12px;
                    background:rgba(255,255,255,0.03);
                    border:1px solid rgba(255,255,255,0.06);
                    text-align:center;
                ">
                    <div style="font-size:11px; letter-spacing:2px; color:rgba(255,255,255,0.3); margin-bottom:8px;">
                        {{ $info['label'] }}
                    </div>
                    <div style="font-size:18px; font-weight:700; color:#fff;">
                        {{ $info['value'] }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.role-option-card:hover {
    border-color: rgba(249,115,22,0.5) !important;
    background: rgba(249,115,22,0.05) !important;
}
</style>
@endpush

{{-- @push('scripts')
<script>
var INITIAL_ROLE = '{{ $currentAdminRole }}';

function updateRoleSelection() {
    var radios  = document.querySelectorAll('.role-radio');
    var btn     = document.getElementById('roleSubmitBtn');
    var selected = null;

    radios.forEach(function(r) {
        var card = document.getElementById('role-card-' + r.value);
        if (r.checked) {
            selected = r.value;
            card.style.borderColor = '#F97316';
            card.style.background  = 'rgba(249,115,22,0.1)';
        } else {
            card.style.borderColor = 'rgba(255,255,255,0.08)';
            card.style.background  = 'rgba(255,255,255,0.02)';
        }
    });

    // Only enable button if selection differs from current role
    if (selected && selected !== INITIAL_ROLE) {
        btn.disabled             = false;
        btn.style.opacity        = '1';
        btn.style.pointerEvents  = 'auto';
    } else {
        btn.disabled             = true;
        btn.style.opacity        = '0.4';
        btn.style.pointerEvents  = 'none';
    }
}

function confirmRoleChange(form) {
    var selected = form.querySelector('.role-radio:checked');
    if (!selected || selected.value === INITIAL_ROLE) return false;

    var label = selected.closest('label').querySelector('[id^="role-card-"] div:nth-child(2)');
    var roleName = label ? label.textContent.trim() : selected.value;

    if (selected.value === 'superadmin') {
        return confirm('⚠️ Grant SUPER ADMIN role?\nThis gives full owner-level access to the dashboard.');
    }
    return confirm('Change admin role to ' + roleName + '?');
}
</script>
@endpush --}}
