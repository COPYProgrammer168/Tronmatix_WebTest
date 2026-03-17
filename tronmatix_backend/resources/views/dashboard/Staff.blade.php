@extends('dashboard.layout')
@section('title', 'STAFF & ROLES')
@section('suppress_flash') @endsection

{{-- ══════════════════════════════════════════════════════════════════════════
     GUARD: Only admin / superadmin may view this page at all
══════════════════════════════════════════════════════════════════════════ --}}
@php
    $me     = Auth::guard('admin')->user();
    $myRole = $me->role ?? 'viewer';
    $isSuper = $myRole === 'superadmin';
    $isAdmin = in_array($myRole, ['admin','superadmin']);

    if (!$isAdmin) {
        abort(403, 'Access denied.');
    }

    // Roles available to assign (superadmin cannot be assigned via UI by a regular admin)
    $availableRoles = $isSuper
        ? ['superadmin','admin','editor','viewer']
        : ['admin','editor','viewer'];

    $roleMeta = [
        'superadmin' => ['label'=>'Super Admin','color'=>'#F97316','icon'=>'👑','desc'=>'Full system owner access'],
        'admin'      => ['label'=>'Admin',      'color'=>'#F97316','icon'=>'🛡️','desc'=>'Full access, manage staff & settings'],
        'editor'     => ['label'=>'Editor',     'color'=>'#3b82f6','icon'=>'✏️','desc'=>'Products, banners, discounts'],
        'viewer'     => ['label'=>'Viewer',     'color'=>'#a78bfa','icon'=>'👁️','desc'=>'Read-only orders & dashboard'],
    ];
@endphp

@section('content')

{{-- Flash toast --}}
@if(session('success'))
<div id="staff-toast" style="
    position:fixed; top:24px; right:24px; z-index:9999;
    display:flex; align-items:center; gap:12px;
    padding:14px 22px; border-radius:14px;
    background:linear-gradient(135deg,#0b1f0e,#0a280c);
    border:1px solid rgba(34,197,94,0.35);
    box-shadow:0 16px 48px rgba(0,0,0,0.5), 0 0 0 1px rgba(34,197,94,0.06);
    font-family:Rajdhani,sans-serif; animation:stToastIn .35s cubic-bezier(0.34,1.4,0.64,1);
">
    <div style="width:34px;height:34px;border-radius:50%;background:rgba(34,197,94,0.15);
                border:1px solid rgba(34,197,94,0.3);display:flex;align-items:center;
                justify-content:center;font-size:18px;">✓</div>
    <div>
        <div style="font-size:14px;font-weight:800;color:#22c55e;letter-spacing:1.5px;">SUCCESS</div>
        <div style="font-size:12px;color:rgba(255,255,255,0.45);margin-top:1px;">{{ session('success') }}</div>
    </div>
    <button onclick="this.closest('#staff-toast').remove()"
            style="margin-left:8px;background:none;border:none;color:rgba(255,255,255,0.3);
                   font-size:18px;cursor:pointer;line-height:1;">×</button>
</div>
<script>setTimeout(()=>document.getElementById('staff-toast')?.remove(),4000);</script>
@endif

@if(session('error'))
<div id="staff-err" style="
    position:fixed; top:24px; right:24px; z-index:9999;
    display:flex; align-items:center; gap:12px;
    padding:14px 22px; border-radius:14px;
    background:linear-gradient(135deg,#1f0b0b,#280a0a);
    border:1px solid rgba(239,68,68,0.35);
    box-shadow:0 16px 48px rgba(0,0,0,0.5);
    font-family:Rajdhani,sans-serif; animation:stToastIn .35s cubic-bezier(0.34,1.4,0.64,1);
">
    <div style="width:34px;height:34px;border-radius:50%;background:rgba(239,68,68,0.12);
                border:1px solid rgba(239,68,68,0.3);display:flex;align-items:center;
                justify-content:center;font-size:18px;">✕</div>
    <div>
        <div style="font-size:14px;font-weight:800;color:#ef4444;letter-spacing:1.5px;">ERROR</div>
        <div style="font-size:12px;color:rgba(255,255,255,0.45);margin-top:1px;">{{ session('error') }}</div>
    </div>
    <button onclick="this.closest('#staff-err').remove()"
            style="margin-left:8px;background:none;border:none;color:rgba(255,255,255,0.3);
                   font-size:18px;cursor:pointer;line-height:1;">×</button>
</div>
<script>setTimeout(()=>document.getElementById('staff-err')?.remove(),5000);</script>
@endif

{{-- ── Page header ──────────────────────────────────────────────────────────── --}}
<div style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:28px;">
    <div style="display:flex; align-items:center; gap:14px;">
        <div style="width:48px; height:48px; border-radius:14px;
                    background:rgba(249,115,22,0.12); border:1px solid rgba(249,115,22,0.3);
                    display:flex; align-items:center; justify-content:center; font-size:24px;">🛡️</div>
        <div>
            <div style="font-size:22px; font-weight:900; letter-spacing:3px;">STAFF & ROLES</div>
            <div style="font-size:13px; color:rgba(255,255,255,0.35); margin-top:2px;">
                Manage team members and their access levels
                @if(!$isSuper)
                · <span style="color:#F97316;">You cannot modify Super Admins</span>
                @endif
            </div>
        </div>
    </div>
    <button onclick="openInviteModal()"
            style="display:flex; align-items:center; gap:8px; padding:11px 22px;
                   border-radius:10px; border:none; cursor:pointer;
                   background:linear-gradient(135deg,#F97316,#ea580c); color:#fff;
                   font-family:Rajdhani,sans-serif; font-size:14px; font-weight:800;
                   letter-spacing:1.5px; box-shadow:0 4px 20px rgba(249,115,22,0.35);
                   transition:all .2s;"
            onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 28px rgba(249,115,22,0.45)'"
            onmouseout="this.style.transform='';this.style.boxShadow='0 4px 20px rgba(249,115,22,0.35)'">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
            <circle cx="8.5" cy="7" r="4"/>
            <line x1="20" y1="8" x2="20" y2="14"/>
            <line x1="23" y1="11" x2="17" y2="11"/>
        </svg>
        INVITE STAFF
    </button>
</div>

{{-- ── Role summary cards ───────────────────────────────────────────────────── --}}
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:24px;">
    @foreach($roleMeta as $rKey => $rMeta)
    @php $count = isset($staffByRole) ? ($staffByRole[$rKey] ?? 0) : ($staff->where('role',$rKey)->count() ?? 0); @endphp
    <div style="padding:18px 20px; border-radius:14px; background:var(--dark-700);
                border:1px solid {{ $rMeta['color'] }}22; position:relative; overflow:hidden;">
        <div style="position:absolute; top:-18px; right:-12px; font-size:64px; opacity:0.06; pointer-events:none;">
            {{ $rMeta['icon'] }}
        </div>
        <div style="font-size:28px; margin-bottom:4px;">{{ $rMeta['icon'] }}</div>
        <div style="font-size:22px; font-weight:900; color:{{ $rMeta['color'] }};">{{ $count }}</div>
        <div style="font-size:12px; font-weight:700; letter-spacing:1.5px; color:{{ $rMeta['color'] }}; margin-top:2px;">
            {{ strtoupper($rMeta['label']) }}
        </div>
        <div style="font-size:11px; color:rgba(255,255,255,0.3); margin-top:4px;">{{ $rMeta['desc'] }}</div>
    </div>
    @endforeach
</div>

{{-- ── Filters ──────────────────────────────────────────────────────────────── --}}
<div style="display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap; align-items:center;">
    <div style="position:relative; flex:1; min-width:220px;">
        <svg style="position:absolute;left:14px;top:50%;transform:translateY(-50%);opacity:0.35;"
             width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" id="staff-search" placeholder="Search by name or email…"
               oninput="filterStaff()"
               style="width:100%; padding:10px 14px 10px 42px; border-radius:10px;
                      background:var(--dark-700); border:1px solid rgba(255,255,255,0.08);
                      color:#fff; font-family:Rajdhani,sans-serif; font-size:14px; outline:none;
                      transition:border-color .2s;"
               onfocus="this.style.borderColor='rgba(249,115,22,0.4)'"
               onblur="this.style.borderColor='rgba(255,255,255,0.08)'" />
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <button class="role-filter active" data-role="all" onclick="setRoleFilter(this,'all')"
                style="padding:9px 16px; border-radius:8px; border:1px solid rgba(255,255,255,0.12);
                       background:rgba(255,255,255,0.06); color:#fff; font-family:Rajdhani,sans-serif;
                       font-size:13px; font-weight:700; letter-spacing:1px; cursor:pointer; transition:all .2s;">
            ALL
        </button>
        @foreach($roleMeta as $rKey => $rMeta)
        <button class="role-filter" data-role="{{ $rKey }}" onclick="setRoleFilter(this,'{{ $rKey }}')"
                style="padding:9px 16px; border-radius:8px; border:1px solid {{ $rMeta['color'] }}30;
                       background:{{ $rMeta['color'] }}0d; color:{{ $rMeta['color'] }};
                       font-family:Rajdhani,sans-serif; font-size:13px; font-weight:700;
                       letter-spacing:1px; cursor:pointer; transition:all .2s;">
            {{ $rMeta['icon'] }} {{ strtoupper($rMeta['label']) }}
        </button>
        @endforeach
    </div>
</div>

{{-- ── Staff table ──────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">TEAM MEMBERS</div>
        <div style="font-size:13px; color:rgba(255,255,255,0.35);">
            {{ $staff->count() }} member{{ $staff->count() !== 1 ? 's' : '' }}
        </div>
    </div>

    @if($staff->isEmpty())
    <div style="padding:64px 20px; text-align:center; color:rgba(255,255,255,0.25);">
        <div style="font-size:48px; margin-bottom:12px;">👥</div>
        <div style="font-size:16px; font-weight:700; letter-spacing:2px;">NO STAFF YET</div>
        <div style="font-size:13px; margin-top:6px;">Invite your first team member to get started.</div>
    </div>
    @else
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse;" id="staff-table">
            <thead>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.07);">
                    <th style="padding:14px 20px; text-align:left; font-size:11px; letter-spacing:2px;
                               color:rgba(255,255,255,0.3); font-weight:700; white-space:nowrap;">MEMBER</th>
                    <th style="padding:14px 14px; text-align:left; font-size:11px; letter-spacing:2px;
                               color:rgba(255,255,255,0.3); font-weight:700; white-space:nowrap;">EMAIL</th>
                    <th style="padding:14px 14px; text-align:center; font-size:11px; letter-spacing:2px;
                               color:rgba(255,255,255,0.3); font-weight:700; white-space:nowrap;">ROLE</th>
                    <th style="padding:14px 14px; text-align:center; font-size:11px; letter-spacing:2px;
                               color:rgba(255,255,255,0.3); font-weight:700; white-space:nowrap;">JOINED</th>
                    <th style="padding:14px 14px; text-align:center; font-size:11px; letter-spacing:2px;
                               color:rgba(255,255,255,0.3); font-weight:700; white-space:nowrap;">STATUS</th>
                    <th style="padding:14px 20px; text-align:right; font-size:11px; letter-spacing:2px;
                               color:rgba(255,255,255,0.3); font-weight:700; white-space:nowrap;">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($staff as $member)
                @php
                    $mRole     = $member->role ?? 'viewer';
                    $mMeta     = $roleMeta[$mRole] ?? $roleMeta['viewer'];
                    $isSelf    = $member->id === $me->id;
                    // Admins cannot edit superadmins unless they themselves are superadmin
                    $canEdit   = $isSuper || ($mRole !== 'superadmin');
                    $canDelete = $canEdit && !$isSelf;
                    $initials  = strtoupper(substr($member->name ?? '?', 0, 1) . (strpos($member->name, ' ') !== false ? substr($member->name, strpos($member->name,' ')+1, 1) : ''));
                @endphp
                <tr class="staff-row"
                    data-role="{{ $mRole }}"
                    data-name="{{ strtolower($member->name ?? '') }}"
                    data-email="{{ strtolower($member->email ?? '') }}"
                    style="border-bottom:1px solid rgba(255,255,255,0.04); transition:background .15s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.02)'"
                    onmouseout="this.style.background=''">

                    {{-- Avatar + Name --}}
                    <td style="padding:16px 20px; white-space:nowrap;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="width:40px; height:40px; border-radius:12px; flex-shrink:0;
                                        background:{{ $mMeta['color'] }}18; border:1.5px solid {{ $mMeta['color'] }}44;
                                        display:flex; align-items:center; justify-content:center;
                                        font-size:14px; font-weight:800; color:{{ $mMeta['color'] }};">
                                {{ $initials }}
                            </div>
                            <div>
                                <div style="font-size:14px; font-weight:700; color:#fff; white-space:nowrap;">
                                    {{ $member->name }}
                                    @if($isSelf)
                                    <span style="font-size:10px; color:rgba(255,255,255,0.3); font-weight:600;
                                                 background:rgba(255,255,255,0.06); border-radius:4px;
                                                 padding:1px 6px; margin-left:4px; letter-spacing:1px;">YOU</span>
                                    @endif
                                </div>
                                <div style="font-size:11px; color:rgba(255,255,255,0.3); margin-top:2px;">
                                    ID #{{ $member->id }}
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Email --}}
                    <td style="padding:16px 14px; font-size:13px; color:rgba(255,255,255,0.55);">
                        {{ $member->email }}
                    </td>

                    {{-- Role badge --}}
                    <td style="padding:16px 14px; text-align:center;">
                        @if($canEdit && !$isSelf)
                        <form method="POST" action="{{ route('dashboard.staff.role', $member->id) }}"
                              style="display:inline-block;" onchange="this.submit()">
                            @csrf @method('PATCH')
                            <select name="role"
                                    style="padding:5px 10px; border-radius:8px; cursor:pointer;
                                           background:{{ $mMeta['color'] }}18; border:1.5px solid {{ $mMeta['color'] }}44;
                                           color:{{ $mMeta['color'] }}; font-family:Rajdhani,sans-serif;
                                           font-size:12px; font-weight:700; letter-spacing:1px; outline:none;">
                                @foreach($availableRoles as $rOpt)
                                <option value="{{ $rOpt }}" {{ $mRole === $rOpt ? 'selected' : '' }}
                                        style="background:#1A1A1A; color:#fff;">
                                    {{ $roleMeta[$rOpt]['icon'] }} {{ strtoupper($roleMeta[$rOpt]['label']) }}
                                </option>
                                @endforeach
                            </select>
                        </form>
                        @else
                        <span style="display:inline-flex; align-items:center; gap:5px; padding:5px 12px;
                                     border-radius:8px; background:{{ $mMeta['color'] }}18;
                                     border:1.5px solid {{ $mMeta['color'] }}44;
                                     color:{{ $mMeta['color'] }}; font-size:12px; font-weight:700; letter-spacing:1px;">
                            {{ $mMeta['icon'] }} {{ strtoupper($mMeta['label']) }}
                        </span>
                        @endif
                    </td>

                    {{-- Joined --}}
                    <td style="padding:16px 14px; text-align:center; font-size:13px; color:rgba(255,255,255,0.4);">
                        {{ $member->created_at ? $member->created_at->format('d M Y') : '—' }}
                    </td>

                    {{-- Status --}}
                    <td style="padding:16px 14px; text-align:center;">
                        @php $active = ($member->is_active ?? true); @endphp
                        <span style="display:inline-flex; align-items:center; gap:5px; padding:4px 10px;
                                     border-radius:6px; font-size:11px; font-weight:700; letter-spacing:1px;
                                     background:{{ $active ? 'rgba(34,197,94,0.1)' : 'rgba(239,68,68,0.1)' }};
                                     border:1px solid {{ $active ? 'rgba(34,197,94,0.3)' : 'rgba(239,68,68,0.3)' }};
                                     color:{{ $active ? '#22c55e' : '#ef4444' }};">
                            <span style="width:6px;height:6px;border-radius:50%;
                                         background:{{ $active ? '#22c55e' : '#ef4444' }};
                                         {{ $active ? 'box-shadow:0 0 6px #22c55e;' : '' }}"></span>
                            {{ $active ? 'ACTIVE' : 'INACTIVE' }}
                        </span>
                    </td>

                    {{-- Actions --}}
                    <td style="padding:16px 20px; text-align:right; white-space:nowrap;">
                        <div style="display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                            @if($canEdit && !$isSelf)
                            {{-- Toggle active/inactive --}}
                            <form method="POST" action="{{ route('dashboard.staff.toggle', $member->id) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        title="{{ $active ? 'Deactivate' : 'Activate' }}"
                                        style="width:32px; height:32px; border-radius:8px; border:1px solid rgba(255,255,255,0.1);
                                               background:rgba(255,255,255,0.04); color:rgba(255,255,255,0.5);
                                               cursor:pointer; display:flex; align-items:center; justify-content:center;
                                               transition:all .2s;"
                                        onmouseover="this.style.background='rgba(255,255,255,0.1)';this.style.color='#fff'"
                                        onmouseout="this.style.background='rgba(255,255,255,0.04)';this.style.color='rgba(255,255,255,0.5)'">
                                    {{ $active ? '⏸' : '▶' }}
                                </button>
                            </form>
                            @endif

                            @if($canDelete)
                            <button onclick="confirmDelete({{ $member->id }}, '{{ addslashes($member->name) }}')"
                                    title="Remove staff member"
                                    style="width:32px; height:32px; border-radius:8px;
                                           border:1px solid rgba(239,68,68,0.2);
                                           background:rgba(239,68,68,0.06); color:rgba(239,68,68,0.6);
                                           cursor:pointer; display:flex; align-items:center; justify-content:center;
                                           transition:all .2s;"
                                    onmouseover="this.style.background='rgba(239,68,68,0.15)';this.style.color='#ef4444'"
                                    onmouseout="this.style.background='rgba(239,68,68,0.06)';this.style.color='rgba(239,68,68,0.6)'">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6l-1 14H6L5 6"/>
                                    <path d="M10 11v6M14 11v6"/>
                                    <path d="M9 6V4h6v2"/>
                                </svg>
                            </button>
                            @endif

                            @if($isSelf)
                            <span style="font-size:12px; color:rgba(255,255,255,0.2); font-style:italic; padding:0 4px;">
                                (your account)
                            </span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     INVITE MODAL
══════════════════════════════════════════════════════════════════════════ --}}
<div id="invite-modal" style="
    display:none; position:fixed; inset:0; z-index:9000;
    align-items:center; justify-content:center; padding:20px;
    background:rgba(0,0,0,0.75); backdrop-filter:blur(8px);
">
    <div style="
        width:100%; max-width:480px; border-radius:20px;
        background:linear-gradient(145deg,#141414,#1a1a1a);
        border:1px solid rgba(249,115,22,0.2);
        box-shadow:0 32px 80px rgba(0,0,0,0.7), 0 0 0 1px rgba(249,115,22,0.06);
        animation:stModalIn .35s cubic-bezier(0.34,1.2,0.64,1);
        font-family:Rajdhani,sans-serif;
    ">
        {{-- Modal header --}}
        <div style="padding:24px 24px 0; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:44px; height:44px; border-radius:12px;
                            background:rgba(249,115,22,0.12); border:1px solid rgba(249,115,22,0.3);
                            display:flex; align-items:center; justify-content:center; font-size:22px;">👤</div>
                <div>
                    <div style="font-size:17px; font-weight:800; letter-spacing:2px;">INVITE STAFF</div>
                    <div style="font-size:12px; color:rgba(255,255,255,0.35);">Add a new team member</div>
                </div>
            </div>
            <button onclick="closeInviteModal()"
                    style="width:36px; height:36px; border-radius:10px; border:1px solid rgba(255,255,255,0.1);
                           background:rgba(255,255,255,0.04); color:rgba(255,255,255,0.5);
                           font-size:18px; cursor:pointer; display:flex; align-items:center; justify-content:center;
                           transition:all .2s;"
                    onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">×</button>
        </div>

        {{-- Modal body --}}
        <form method="POST" action="{{ route('dashboard.staff.invite') }}" id="invite-form">
            @csrf
            <div style="padding:24px; display:flex; flex-direction:column; gap:16px;">

                <div>
                    <label style="display:block; font-size:11px; font-weight:700; letter-spacing:2px;
                                  color:rgba(255,255,255,0.4); margin-bottom:8px;">FULL NAME</label>
                    <input type="text" name="name" placeholder="e.g. John Doe" required
                           style="width:100%; padding:11px 14px; border-radius:10px;
                                  background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1);
                                  color:#fff; font-family:Rajdhani,sans-serif; font-size:15px; outline:none;
                                  transition:border-color .2s;"
                           onfocus="this.style.borderColor='rgba(249,115,22,0.5)'"
                           onblur="this.style.borderColor='rgba(255,255,255,0.1)'" />
                </div>

                <div>
                    <label style="display:block; font-size:11px; font-weight:700; letter-spacing:2px;
                                  color:rgba(255,255,255,0.4); margin-bottom:8px;">EMAIL ADDRESS</label>
                    <input type="email" name="email" placeholder="e.g. john@example.com" required
                           style="width:100%; padding:11px 14px; border-radius:10px;
                                  background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1);
                                  color:#fff; font-family:Rajdhani,sans-serif; font-size:15px; outline:none;
                                  transition:border-color .2s;"
                           onfocus="this.style.borderColor='rgba(249,115,22,0.5)'"
                           onblur="this.style.borderColor='rgba(255,255,255,0.1)'" />
                </div>

                <div>
                    <label style="display:block; font-size:11px; font-weight:700; letter-spacing:2px;
                                  color:rgba(255,255,255,0.4); margin-bottom:8px;">ASSIGN ROLE</label>
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        @foreach($availableRoles as $rOpt)
                        @php $rM = $roleMeta[$rOpt]; @endphp
                        <label style="display:flex; align-items:center; gap:12px; padding:12px 14px;
                                      border-radius:10px; cursor:pointer; border:1.5px solid rgba(255,255,255,0.07);
                                      background:rgba(255,255,255,0.02); transition:all .2s;"
                               onmouseover="this.style.borderColor='{{ $rM['color'] }}44'; this.style.background='{{ $rM['color'] }}0d'"
                               onmouseout="this.querySelector('input').checked ? null : (this.style.borderColor='rgba(255,255,255,0.07)', this.style.background='rgba(255,255,255,0.02)')">
                            <input type="radio" name="role" value="{{ $rOpt }}"
                                   {{ $rOpt === 'editor' ? 'checked' : '' }}
                                   style="accent-color:{{ $rM['color'] }}; width:16px; height:16px; cursor:pointer;" />
                            <span style="font-size:20px;">{{ $rM['icon'] }}</span>
                            <div style="flex:1;">
                                <div style="font-size:14px; font-weight:800; letter-spacing:1px; color:{{ $rM['color'] }};">
                                    {{ strtoupper($rM['label']) }}
                                </div>
                                <div style="font-size:11px; color:rgba(255,255,255,0.3); margin-top:1px;">
                                    {{ $rM['desc'] }}
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label style="display:block; font-size:11px; font-weight:700; letter-spacing:2px;
                                  color:rgba(255,255,255,0.4); margin-bottom:8px;">TEMPORARY PASSWORD</label>
                    <div style="position:relative;">
                        <input type="password" name="password" id="inv-pass"
                               placeholder="Min 8 characters" required minlength="8"
                               style="width:100%; padding:11px 44px 11px 14px; border-radius:10px;
                                      background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1);
                                      color:#fff; font-family:Rajdhani,sans-serif; font-size:15px; outline:none;
                                      transition:border-color .2s;"
                               onfocus="this.style.borderColor='rgba(249,115,22,0.5)'"
                               onblur="this.style.borderColor='rgba(255,255,255,0.1)'" />
                        <button type="button" onclick="togglePassVis('inv-pass', this)"
                                style="position:absolute; right:12px; top:50%; transform:translateY(-50%);
                                       background:none; border:none; color:rgba(255,255,255,0.35); cursor:pointer;
                                       font-size:16px; padding:2px;">👁</button>
                    </div>
                    <div style="font-size:11px; color:rgba(255,255,255,0.25); margin-top:6px;">
                        Staff member should change this after first login.
                    </div>
                </div>
            </div>

            {{-- Modal footer --}}
            <div style="padding:0 24px 24px; display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" onclick="closeInviteModal()"
                        style="padding:10px 20px; border-radius:9px; border:1px solid rgba(255,255,255,0.1);
                               background:transparent; color:rgba(255,255,255,0.5);
                               font-family:Rajdhani,sans-serif; font-size:13px; font-weight:700;
                               letter-spacing:1px; cursor:pointer; transition:all .2s;"
                        onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">
                    CANCEL
                </button>
                <button type="submit" id="invite-btn"
                        style="display:flex; align-items:center; gap:6px; padding:10px 22px;
                               border-radius:9px; border:none; cursor:pointer;
                               background:linear-gradient(135deg,#F97316,#ea580c); color:#fff;
                               font-family:Rajdhani,sans-serif; font-size:13px; font-weight:800;
                               letter-spacing:1px; box-shadow:0 4px 16px rgba(249,115,22,0.3); transition:all .2s;">
                    ✉ SEND INVITE
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Delete confirm modal --}}
<div id="del-modal" style="
    display:none; position:fixed; inset:0; z-index:9100;
    align-items:center; justify-content:center; padding:20px;
    background:rgba(0,0,0,0.8); backdrop-filter:blur(8px);
">
    <div style="
        width:100%; max-width:380px; border-radius:18px; padding:32px;
        background:linear-gradient(145deg,#1a0a0a,#1f0b0b);
        border:1px solid rgba(239,68,68,0.25);
        box-shadow:0 32px 80px rgba(0,0,0,0.7);
        text-align:center; font-family:Rajdhani,sans-serif;
        animation:stModalIn .3s cubic-bezier(0.34,1.2,0.64,1);
    ">
        <div style="font-size:48px; margin-bottom:12px;">⚠️</div>
        <div style="font-size:18px; font-weight:900; letter-spacing:2px; margin-bottom:8px;">REMOVE STAFF?</div>
        <div style="font-size:13px; color:rgba(255,255,255,0.4); margin-bottom:24px;">
            You are about to remove <strong id="del-name" style="color:#ef4444;"></strong> from the team.
            This cannot be undone.
        </div>
        <form method="POST" id="del-form">
            @csrf @method('DELETE')
            <div style="display:flex; gap:10px; justify-content:center;">
                <button type="button" onclick="closeDelModal()"
                        style="flex:1; padding:10px; border-radius:9px; border:1px solid rgba(255,255,255,0.1);
                               background:transparent; color:rgba(255,255,255,0.5);
                               font-family:Rajdhani,sans-serif; font-size:13px; font-weight:700;
                               cursor:pointer; transition:all .2s;"
                        onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">
                    CANCEL
                </button>
                <button type="submit"
                        style="flex:1; padding:10px; border-radius:9px; border:none;
                               background:linear-gradient(135deg,#dc2626,#b91c1c); color:#fff;
                               font-family:Rajdhani,sans-serif; font-size:13px; font-weight:800;
                               cursor:pointer; box-shadow:0 4px 16px rgba(220,38,38,0.3); transition:all .2s;">
                    YES, REMOVE
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Scripts ──────────────────────────────────────────────────────────────── --}}
<script>
/* Invite modal */
function openInviteModal() {
    const m = document.getElementById('invite-modal');
    m.style.display = 'flex';
    setTimeout(() => m.querySelector('input[name=name]')?.focus(), 100);
}
function closeInviteModal() {
    document.getElementById('invite-modal').style.display = 'none';
}
document.getElementById('invite-modal').addEventListener('click', function(e) {
    if (e.target === this) closeInviteModal();
});
document.getElementById('invite-form').addEventListener('submit', function() {
    const btn = document.getElementById('invite-btn');
    btn.textContent = '⏳ SENDING...'; btn.disabled = true;
});

/* Delete modal */
function confirmDelete(id, name) {
    document.getElementById('del-name').textContent = name;
    document.getElementById('del-form').action = `/dashboard/staff/${id}`;
    const m = document.getElementById('del-modal');
    m.style.display = 'flex';
}
function closeDelModal() {
    document.getElementById('del-modal').style.display = 'none';
}
document.getElementById('del-modal').addEventListener('click', function(e) {
    if (e.target === this) closeDelModal();
});

/* Password visibility */
function togglePassVis(id, btn) {
    const inp = document.getElementById(id);
    if (inp.type === 'password') { inp.type = 'text'; btn.textContent = '🙈'; }
    else { inp.type = 'password'; btn.textContent = '👁'; }
}

/* Search + role filter */
let activeRole = 'all';
function filterStaff() {
    const q = document.getElementById('staff-search').value.toLowerCase();
    document.querySelectorAll('.staff-row').forEach(row => {
        const name  = row.dataset.name  || '';
        const email = row.dataset.email || '';
        const role  = row.dataset.role  || '';
        const matchSearch = !q || name.includes(q) || email.includes(q);
        const matchRole   = activeRole === 'all' || role === activeRole;
        row.style.display = matchSearch && matchRole ? '' : 'none';
    });
}
function setRoleFilter(btn, role) {
    activeRole = role;
    document.querySelectorAll('.role-filter').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    filterStaff();
}

/* ESC to close modals */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeInviteModal(); closeDelModal(); }
});
</script>

{{-- ── Styles ───────────────────────────────────────────────────────────────── --}}
<style>
@keyframes stToastIn  { from{opacity:0;transform:translateX(20px)} to{opacity:1;transform:none} }
@keyframes stModalIn  { from{opacity:0;transform:scale(.92)} to{opacity:1;transform:scale(1)} }

.role-filter { transition: all .2s; }
.role-filter.active {
    background: rgba(249,115,22,0.15) !important;
    border-color: rgba(249,115,22,0.4) !important;
    color: #F97316 !important;
}

@media (max-width: 900px) {
    /* stack role summary cards */
    div[style*="grid-template-columns:repeat(4"] {
        grid-template-columns: repeat(2,1fr) !important;
    }
}
@media (max-width: 560px) {
    div[style*="grid-template-columns:repeat(4"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endsection