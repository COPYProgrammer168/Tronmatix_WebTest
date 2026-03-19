{{-- resources/views/dashboard/profile.blade.php --}}
@extends('dashboard.layout')
@section('title', 'MY PROFILE')

@section('content')
@php
    $admin   = Auth::guard('admin')->user();
    $avatarUrl = $admin->avatar
        ? (Str::startsWith($admin->avatar, ['http://','https://'])
            ? $admin->avatar
            : asset('storage/' . $admin->avatar))
        : null;
@endphp

<div style="max-width:800px; margin:0 auto;">

    {{-- ── Page header ─────────────────────────────────────────────────────── --}}
    <div style="margin-bottom:28px; display:flex; align-items:center; gap:16px;">

        {{-- Avatar display --}}
        <div style="position:relative; flex-shrink:0;">
            @if($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="Avatar"
                     style="width:60px; height:60px; border-radius:50%; object-fit:cover;
                            border:2px solid rgba(249,115,22,0.5);
                            box-shadow:0 4px 16px rgba(249,115,22,0.3);" />
            @else
                <div style="width:60px; height:60px; border-radius:50%;
                            background:linear-gradient(135deg,#F97316,#ea580c);
                            display:flex; align-items:center; justify-content:center;
                            font-size:24px; font-weight:800; color:#fff;
                            box-shadow:0 4px 16px rgba(249,115,22,0.35);">
                    {{ strtoupper(substr($admin->name ?? 'A', 0, 1)) }}
                </div>
            @endif
        </div>

        <div>
            <div style="font-size:24px; font-weight:800; letter-spacing:2px;">
                {{ $admin->name ?? 'Admin' }}
            </div>
            <div style="font-size:14px; color:rgba(255,255,255,0.4); margin-top:2px; letter-spacing:1px;">
                {{ $admin->email ?? '' }}
            </div>
        </div>
        <div style="margin-left:auto;">
            <span class="badge badge-orange" style="font-size:13px; letter-spacing:2px;">
                {{ strtoupper($admin->role ?? 'ADMIN') }}
            </span>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div style="margin-bottom:20px; padding:14px 18px; border-radius:10px;
                background:rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.3);
                color:#22c55e; font-weight:700; font-size:14px;">
        ✓ {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div style="margin-bottom:20px; padding:14px 18px; border-radius:10px;
                background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3);
                color:#ef4444; font-size:13px;">
        @foreach($errors->all() as $e) <div>• {{ $e }}</div> @endforeach
    </div>
    @endif

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

        {{-- ── Left: Edit Profile + Avatar Upload ─────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <span style="font-size:16px; font-weight:800; letter-spacing:2px;">✏️ EDIT PROFILE</span>
            </div>
            <div style="padding:24px;">
                {{-- NOTE: action uses POST (not PUT) — required for file uploads --}}
                <form method="POST" action="{{ route('dashboard.profile.update') }}"
                      enctype="multipart/form-data">
                    @csrf

                    {{-- Avatar Upload --}}
                    <div class="form-group">
                        <label class="form-label">PROFILE PHOTO</label>
                        <div style="display:flex; align-items:center; gap:14px; margin-bottom:8px;">

                            {{-- Preview --}}
                            <div id="avatar-preview-wrap" style="width:64px; height:64px; border-radius:50%; overflow:hidden; flex-shrink:0;
                                      border:2px solid rgba(249,115,22,0.4); background:rgba(249,115,22,0.08);
                                      display:flex; align-items:center; justify-content:center;">
                                @if($avatarUrl)
                                    <img id="avatar-preview" src="{{ $avatarUrl }}" alt="Avatar"
                                         style="width:100%; height:100%; object-fit:cover;" />
                                @else
                                    <span id="avatar-initials" style="font-size:24px; font-weight:800; color:#F97316;">
                                        {{ strtoupper(substr($admin->name ?? 'A', 0, 1)) }}
                                    </span>
                                @endif
                            </div>

                            <div style="flex:1;">
                                <label for="avatar-input" style="
                                    display:inline-flex; align-items:center; gap:6px;
                                    padding:8px 14px; border-radius:8px; cursor:pointer;
                                    background:rgba(249,115,22,0.1); border:1px solid rgba(249,115,22,0.3);
                                    color:#F97316; font-size:13px; font-weight:700; letter-spacing:1px;
                                    transition:all .2s;"
                                    onmouseover="this.style.background='rgba(249,115,22,0.2)'"
                                    onmouseout="this.style.background='rgba(249,115,22,0.1)'">
                                    📷 CHOOSE PHOTO
                                </label>
                                <input id="avatar-input" type="file" name="avatar"
                                       accept="image/jpg,image/jpeg,image/png,image/webp"
                                       style="display:none;"
                                       onchange="previewAvatar(this)" />
                                <div style="font-size:11px; color:rgba(255,255,255,0.3); margin-top:4px;">
                                    JPG, PNG, WEBP · Max 2MB
                                </div>
                            </div>
                        </div>

                    {{-- Remove avatar button — MUST be outside the upload form (no nested forms) --}}
                        @if($avatarUrl)
                        <div style="margin-top:6px;">
                            <button type="button"
                                    onclick="document.getElementById('remove-avatar-form').submit()"
                                    style="font-size:12px; color:rgba(239,68,68,0.7); background:none;
                                           border:none; cursor:pointer; padding:0; font-weight:600;"
                                    onmouseover="this.style.color='#ef4444'"
                                    onmouseout="this.style.color='rgba(239,68,68,0.7)'">
                                🗑 Remove photo
                            </button>
                        </div>
                        @endif
                    </div>

                    {{-- Name --}}
                    <div class="form-group">
                        <label class="form-label">FULL NAME</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', $admin->name) }}"
                               placeholder="Admin name" required
                               style="border-color:{{ $errors->has('name') ? '#EF4444' : 'rgba(255,255,255,0.1)' }};" />
                        @error('name')
                            <div style="color:#EF4444; font-size:13px; margin-top:5px;">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="form-group">
                        <label class="form-label">EMAIL ADDRESS</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email', $admin->email) }}"
                               placeholder="admin@example.com" required
                               style="border-color:{{ $errors->has('email') ? '#EF4444' : 'rgba(255,255,255,0.1)' }};" />
                        @error('email')
                            <div style="color:#EF4444; font-size:13px; margin-top:5px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-orange"
                            style="width:100%; justify-content:center; font-size:15px;">
                        <svg style="width:16px;height:16px" fill="none" stroke="currentColor"
                             stroke-width="2" viewBox="0 0 24 24">
                            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
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
                    @csrf @method('PUT')

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

                    <button type="submit" class="btn btn-outline"
                            style="width:100%; justify-content:center; font-size:15px;">
                        <svg style="width:16px;height:16px" fill="none" stroke="currentColor"
                             stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        UPDATE PASSWORD
                    </button>
                </form>
            </div>
        </div>

    </div>{{-- /grid --}}

    {{-- ── Account Info ─────────────────────────────────────────────────────── --}}
    <div class="card" style="margin-top:20px;">
        <div class="card-header">
            <span style="font-size:16px; font-weight:800; letter-spacing:2px;">📋 ACCOUNT INFORMATION</span>
        </div>
        <div style="padding:20px;">
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:20px;">
                @php
                    $infos = [
                        ['label' => 'ACCOUNT ID',   'value' => '#' . ($admin->id ?? '—')],
                        ['label' => 'ROLE',         'value' => strtoupper($admin->role ?? 'ADMIN')],
                        ['label' => 'MEMBER SINCE', 'value' => $admin->created_at ? $admin->created_at->format('M Y') : '—'],
                        ['label' => 'LAST UPDATED', 'value' => $admin->updated_at ? $admin->updated_at->diffForHumans() : '—'],
                    ];
                @endphp
                @foreach($infos as $info)
                <div style="padding:16px; border-radius:12px;
                            background:rgba(255,255,255,0.03);
                            border:1px solid rgba(255,255,255,0.06);
                            text-align:center;">
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

{{-- Standalone remove-avatar form (outside the upload form to avoid nested forms) --}}
@if($avatarUrl)
<form method="POST" action="{{ route('dashboard.profile.avatar.remove') }}"
      id="remove-avatar-form" style="display:none;">
    @csrf @method('DELETE')
</form>
@endif

@push('scripts')
<script>
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const wrap = document.getElementById('avatar-preview-wrap');
    const reader = new FileReader();
    reader.onload = function(e) {
        // Replace initials with image preview
        wrap.innerHTML = `<img id="avatar-preview" src="${e.target.result}"
            style="width:100%;height:100%;object-fit:cover;border-radius:50%;" />`;
    };
    reader.readAsDataURL(file);
}
</script>
@endpush

@push('styles')
<style>
/* Profile grid: 2-col → 1-col on tablet/mobile */
@media (max-width: 760px) {
    div[style*="grid-template-columns:1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}

/* Page header stack on mobile */
@media (max-width: 540px) {
    div[style*="margin-bottom:28px; display:flex"] {
        flex-wrap: wrap !important;
    }
}

/* Account info grid: compact on mobile */
@media (max-width: 480px) {
    div[style*="grid-template-columns:repeat(auto-fit,minmax(180px"] {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

/* Full-width form controls on small screens */
@media (max-width: 380px) {
    div[style*="max-width:800px"] {
        max-width: 100% !important;
    }
}
</style>
@endpush