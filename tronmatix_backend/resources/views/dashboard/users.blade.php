{{-- resources/views/dashboard/users.blade.php --}}
@extends('dashboard.layout')
@section('title', 'USERS')

@push('styles')
<style>
/* ── Role badges ─────────────────────────────────────────────────────────── */
.role-badge-customer { background:rgba(156,163,175,0.15); color:#9CA3AF; border:1px solid rgba(156,163,175,0.3); }
.role-badge-vip      { background:rgba(249,115,22,0.15);  color:#F97316; border:1px solid rgba(249,115,22,0.4); }
.role-badge-reseller { background:rgba(59,130,246,0.15);  color:#3B82F6; border:1px solid rgba(59,130,246,0.4); }
.role-badge-banned   { background:rgba(239,68,68,0.15);   color:#EF4444; border:1px solid rgba(239,68,68,0.4); }

/* ── Inline role select ──────────────────────────────────────────────────── */
.role-select {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 8px;
    color: #fff;
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    font-weight: 600;
    padding: 5px 10px;
    cursor: pointer;
    outline: none;
    transition: border-color 0.2s;
}
.role-select:hover { border-color: var(--orange); }
.role-select:focus { border-color: var(--orange); }

/* ── Filter tabs ─────────────────────────────────────────────────────────── */
.filter-tab {
    padding: 6px 14px;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.1);
    background: transparent;
    color: rgba(255,255,255,0.5);
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 1px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}
.filter-tab:hover               { border-color: var(--orange); color: var(--orange); }
.filter-tab.active              { background: rgba(249,115,22,0.12); border-color: var(--orange); color: var(--orange); }
.count-pill                     { background: rgba(255,255,255,0.08); border-radius: 20px; padding: 1px 8px; font-size: 11px; }
.filter-tab.active .count-pill  { background: rgba(249,115,22,0.2); }

/* ── Search ──────────────────────────────────────────────────────────────── */
.search-input {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 10px;
    color: #fff;
    font-family: 'Rajdhani', sans-serif;
    font-size: 15px;
    padding: 8px 16px 8px 38px;
    outline: none;
    width: 230px;
    transition: border-color 0.2s;
}
.search-input:focus             { border-color: var(--orange); }
.search-input::placeholder      { color: rgba(255,255,255,0.25); }

/* ── VIP progress bar ────────────────────────────────────────────────────── */
.vip-bar-fill {
    height: 100%;
    border-radius: 4px;
    background: linear-gradient(90deg, #F97316, #fb923c);
    transition: width 0.8s cubic-bezier(0.4,0,0.2,1);
    box-shadow: 0 0 6px rgba(249,115,22,0.4);
}

/* ── Table hover ─────────────────────────────────────────────────────────── */
tbody tr:hover td { background: rgba(255,255,255,0.02); }

/* ── Flash toast ─────────────────────────────────────────────────────────── */
.flash-toast {
    position: fixed; bottom: 28px; right: 28px; z-index: 9999;
    display: flex; align-items: center; gap: 10px;
    background: #18181b; border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px; padding: 12px 18px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.4);
    font-family: 'Rajdhani', sans-serif; font-size: 15px; font-weight: 600;
    color: #fff; opacity: 0; transform: translateY(8px);
    transition: opacity 0.25s, transform 0.25s;
    pointer-events: none;
}
.flash-toast.show { opacity: 1; transform: translateY(0); }
.flash-toast.success { border-left: 3px solid #22c55e; }
.flash-toast.error   { border-left: 3px solid #ef4444; }
</style>
@endpush

@section('content')

@php
    use App\Models\AdminSetting;
    $_pRole = Auth::guard('admin')->user()?->role ?? 'viewer';
    $_pFeat = 'users';
    $_pKey  = "perm_{$_pRole}_{$_pFeat}";
    $_pDef  = [
        'admin_dashboard'=>'1','admin_products'=>'1','admin_orders'=>'1',
        'admin_orders_edit'=>'1','admin_users'=>'1','admin_discounts'=>'1',
        'admin_settings'=>'1','admin_staff'=>'1',
        'editor_dashboard'=>'1','editor_products'=>'1','editor_orders'=>'1',
        'editor_orders_edit'=>'0','editor_users'=>'0','editor_discounts'=>'1',
        'editor_settings'=>'0','editor_staff'=>'0',
        'viewer_dashboard'=>'1','viewer_products'=>'0','viewer_orders'=>'1',
        'viewer_orders_edit'=>'0','viewer_users'=>'0','viewer_discounts'=>'0',
        'viewer_settings'=>'0','viewer_staff'=>'0',
    ];
    $_pAccess = $_pRole === 'superadmin'
        || (AdminSetting::get($_pKey, $_pDef["{$_pRole}_{$_pFeat}"] ?? '0') === '1');
    $_pRoleMeta = [
        'superadmin'=>['color'=>'#F97316','icon'=>'👑','label'=>'Super Admin'],
        'admin'     =>['color'=>'#F97316','icon'=>'🛡️','label'=>'Admin'],
        'editor'    =>['color'=>'#3b82f6','icon'=>'✏️', 'label'=>'Editor'],
        'viewer'    =>['color'=>'#a78bfa','icon'=>'👁️', 'label'=>'Viewer'],
    ];
    $_pRM = $_pRoleMeta[$_pRole] ?? $_pRoleMeta['viewer'];
    $_pAllFeats = ['dashboard'=>'📊','products'=>'📦','orders'=>'📋',
                   'orders_edit'=>'✏️','users'=>'👥','discounts'=>'🏷️',
                   'settings'=>'⚙️','staff'=>'🛡️'];
@endphp

@if(!$_pAccess)
{{-- ══════════════════ ACCESS DENIED ══════════════════════════════════════ --}}
<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;
     min-height:60vh;text-align:center;padding:40px 20px;font-family:Rajdhani,sans-serif;
     animation:fadeUp .45s ease both;">
    <div style="width:96px;height:96px;border-radius:28px;margin-bottom:28px;
         background:rgba(239,68,68,0.08);border:1.5px solid rgba(239,68,68,0.25);
         display:flex;align-items:center;justify-content:center;font-size:46px;
         box-shadow:0 0 60px rgba(239,68,68,0.12);animation:lockPulse 2.5s ease-in-out infinite;">🔒</div>
    <div style="font-size:30px;font-weight:900;letter-spacing:3px;color:#ef4444;margin-bottom:8px;">ACCESS DENIED</div>
    <div style="font-size:14px;color:rgba(255,255,255,0.35);margin-bottom:32px;max-width:380px;line-height:1.6;">
        Your role does not have permission to access this module.<br>
        Contact a <span style="color:#F97316;font-weight:700;">Super Admin</span> to request access.
    </div>
    <div style="display:inline-flex;align-items:center;gap:10px;padding:12px 24px;border-radius:16px;
         margin-bottom:32px;background:{{ $_pRM['color'] }}12;border:1.5px solid {{ $_pRM['color'] }}40;">
        <span style="font-size:22px;">{{ $_pRM['icon'] }}</span>
        <div style="text-align:left;">
            <div style="font-size:10px;color:rgba(255,255,255,0.4);letter-spacing:2px;font-weight:700;">YOUR ROLE</div>
            <div style="font-size:16px;font-weight:800;color:{{ $_pRM['color'] }};letter-spacing:1px;">{{ strtoupper($_pRM['label']) }}</div>
        </div>
        <div style="width:1px;height:32px;background:rgba(255,255,255,0.1);margin:0 4px;"></div>
        <div style="text-align:left;">
            <div style="font-size:10px;color:rgba(255,255,255,0.4);letter-spacing:2px;font-weight:700;">MODULE</div>
            <div style="font-size:16px;font-weight:800;color:rgba(255,255,255,0.6);letter-spacing:1px;">{{ strtoupper(str_replace('_',' ','users')) }}</div>
        </div>
    </div>
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);
         border-radius:16px;padding:20px 24px;margin-bottom:32px;max-width:480px;width:100%;">
        <div style="font-size:11px;color:rgba(255,255,255,0.3);letter-spacing:2px;font-weight:700;margin-bottom:16px;text-align:left;">YOUR ACCESS OVERVIEW</div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;">
            @foreach($_pAllFeats as $_fKey => $_fIcon)
            @php
                $_fPKey = "perm_{$_pRole}_{$_fKey}";
                $_fHas  = $_pRole === 'superadmin' || (AdminSetting::get($_fPKey, $_pDef["{$_pRole}_{$_fKey}"] ?? '0') === '1');
                $_fActive = ($_fKey === 'users');
            @endphp
            <div style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:10px 6px;border-radius:10px;
                 background:{{ $_fActive ? 'rgba(239,68,68,0.10)' : ($_fHas ? 'rgba(34,197,94,0.07)' : 'rgba(255,255,255,0.03)') }};
                 border:1px solid {{ $_fActive ? 'rgba(239,68,68,0.3)' : ($_fHas ? 'rgba(34,197,94,0.2)' : 'rgba(255,255,255,0.06)') }};">
                <span style="font-size:18px;{{ !$_fHas ? 'opacity:0.3;' : '' }}">{{ $_fIcon }}</span>
                <span style="font-size:9px;letter-spacing:1px;font-weight:700;
                    color:{{ $_fActive ? '#ef4444' : ($_fHas ? '#22c55e' : 'rgba(255,255,255,0.2)') }};">
                    {{ $_fHas ? '✓' : '✗' }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center;">
        <a href="{{ route('dashboard.index') }}" style="display:inline-flex;align-items:center;gap:8px;
           padding:12px 24px;border-radius:12px;text-decoration:none;background:#F97316;color:#fff;
           font-size:14px;font-weight:700;letter-spacing:1px;box-shadow:0 4px 16px rgba(249,115,22,0.3);"
           onmouseover="this.style.background='#fb923c'" onmouseout="this.style.background='#F97316'">
            🏠 GO TO DASHBOARD
        </a>
        <a href="javascript:history.back()" style="display:inline-flex;align-items:center;gap:8px;
           padding:12px 24px;border-radius:12px;text-decoration:none;
           background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);
           color:rgba(255,255,255,0.6);font-size:14px;font-weight:700;letter-spacing:1px;"
           onmouseover="this.style.background='rgba(255,255,255,0.10)'" onmouseout="this.style.background='rgba(255,255,255,0.06)'">
            ← GO BACK
        </a>
    </div>
</div>
<style>
@keyframes fadeUp   { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:none} }
@keyframes lockPulse { 0%,100%{box-shadow:0 0 30px rgba(239,68,68,0.08)} 50%{box-shadow:0 0 60px rgba(239,68,68,0.22)} }
</style>
@else



@php
    $roleMap     = ['all' => 'All', 'customer' => 'Customer', 'vip' => 'VIP', 'reseller' => 'Reseller', 'banned' => 'Banned'];
    $roleIcons   = ['customer' => '👤', 'vip' => '⭐', 'reseller' => '🏪', 'banned' => '🚫'];
    $currentRole = request('role', 'all');
    $totalUsers  = array_sum($roleCounts ?? []);
@endphp

{{-- Flash message (from redirect()->back()->with('success', ...)) --}}
@if(session('success'))
<div class="flash-toast success show" id="flashToast">
    ✅ {{ session('success') }}
</div>
@elseif(session('error'))
<div class="flash-toast error show" id="flashToast">
    ❌ {{ session('error') }}
</div>
@endif

{{-- ── Stats strip ──────────────────────────────────────────────────────────── --}}
<div class="stats-grid users-stats-grid" style="margin-bottom:20px;">
    @foreach(['customer','vip','reseller','banned'] as $role)
    <div class="stat-card">
        <div class="stat-icon"><span style="font-size:20px;">{{ $roleIcons[$role] }}</span></div>
        <div>
            <div class="stat-value">{{ $roleCounts[$role] ?? 0 }}</div>
            <div class="stat-label">{{ strtoupper($role) }}</div>
        </div>
    </div>
    @endforeach
    <div class="stat-card">
        <div class="stat-icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
            </svg>
        </div>
        <div>
            <div class="stat-value">{{ $totalUsers }}</div>
            <div class="stat-label">TOTAL</div>
        </div>
    </div>
</div>

{{-- ── Main card ─────────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header" style="flex-wrap:wrap; gap:12px;">

        {{-- Role filter tabs --}}
        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            @foreach($roleMap as $key => $label)
                @php
                    $count    = $key === 'all' ? $totalUsers : ($roleCounts[$key] ?? 0);
                    $isActive = $currentRole === $key;
                    $params   = array_merge(request()->only('search'), ['role' => $key]);
                @endphp
                <a href="{{ route('dashboard.users', $params) }}"
                   class="filter-tab {{ $isActive ? 'active' : '' }}">
                    {{ $label }}<span class="count-pill">{{ $count }}</span>
                </a>
            @endforeach
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('dashboard.users') }}" style="position:relative; margin-left:auto;">
            @if(request('role'))
                <input type="hidden" name="role" value="{{ request('role') }}">
            @endif
            <svg style="position:absolute;left:11px;top:50%;transform:translateY(-50%);width:15px;height:15px;stroke:rgba(255,255,255,0.3)"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" class="search-input"
                   placeholder="Search username, email..."
                   value="{{ request('search') }}" />
        </form>
    </div>

    {{-- ── Table ───────────────────────────────────────────────────────────── --}}
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>USERNAME</th>
                    <th>EMAIL</th>
                    <th>PHONE</th>
                    <th>ORDERS</th>
                    <th>SPENT</th>
                    <th>2FA</th>
                    <th>ROLE</th>
                    <th>JOINED</th>
                    <th style="min-width:200px;">CHANGE ROLE</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr id="user-row-{{ $user->id }}">

                    {{-- ID --}}
                    <td style="color:rgba(255,255,255,0.3); font-size:12px;">{{ $user->id }}</td>

                    {{-- Avatar + username --}}
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="
                                width:34px; height:34px; border-radius:50%; flex-shrink:0;
                                background:linear-gradient(135deg,#F97316,#ea580c);
                                display:flex; align-items:center; justify-content:center;
                                font-weight:800; font-size:13px; color:#fff;">
                                {{ strtoupper(substr($user->username, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:700; font-size:15px;">{{ $user->username }}</div>
                                @if($user->name && $user->name !== $user->username)
                                    <div style="font-size:11px; color:rgba(255,255,255,0.3);">{{ $user->name }}</div>
                                @endif
                            </div>
                        </div>
                    </td>

                    <td style="color:rgba(255,255,255,0.5); font-size:14px;">{{ $user->email ?? '—' }}</td>
                    <td style="color:rgba(255,255,255,0.5); font-size:14px;">{{ $user->phone ?? '—' }}</td>

                    {{-- Orders --}}
                    <td>
                        <span class="badge {{ $user->orders_count > 0 ? 'badge-orange' : 'badge-gray' }}">
                            {{ $user->orders_count }}
                        </span>
                    </td>

                    {{-- Total spent + VIP progress ─────────────────────── --}}
                    @php
                        $spent    = (float) ($user->total_spent ?? 0);
                        $vipGoal  = 1000;
                        $pct      = min(100, round(($spent / $vipGoal) * 100));
                        $isVip    = ($user->role ?? 'customer') === 'vip';
                    @endphp
                    <td style="min-width:120px;">
                        <div style="font-weight:700; font-size:14px; color:{{ $spent >= $vipGoal ? '#F97316' : '#fff' }};">
                            ${{ number_format($spent, 0) }}
                        </div>
                        @if(! $isVip)
                        <div style="margin-top:5px; position:relative;">
                            <div style="height:4px; border-radius:4px; background:rgba(255,255,255,0.08); overflow:hidden;">
                                <div style="
                                    height:100%; border-radius:4px;
                                    width:{{ $pct }}%;
                                    background:{{ $pct >= 100 ? '#F97316' : 'linear-gradient(90deg,#F97316,#fb923c)' }};
                                    transition:width 0.6s ease;
                                "></div>
                            </div>
                            <div style="font-size:10px; color:rgba(255,255,255,0.3); margin-top:3px; letter-spacing:0.5px;">
                                ${{ number_format($spent, 0) }} / $1,000 VIP
                            </div>
                        </div>
                        @else
                        <div style="font-size:10px; color:#F97316; margin-top:3px; letter-spacing:1px; font-weight:700;">
                            ⭐ VIP MEMBER
                        </div>
                        @endif
                    </td>

                    {{-- 2FA --}}
                    <td>
                        @if($user->two_factor_enabled)
                            <span class="badge badge-paid">ON</span>
                        @else
                            <span class="badge badge-gray">OFF</span>
                        @endif
                    </td>

                    {{-- Current role badge --}}
                    <td>
                        <span class="badge role-badge-{{ $user->role ?? 'customer' }}"
                              id="role-badge-{{ $user->id }}"
                              style="font-size:12px; letter-spacing:1px;">
                            {{ strtoupper(\App\Models\User::ROLE_LABELS[$user->role ?? 'customer'] ?? 'CUSTOMER') }}
                        </span>
                    </td>

                    <td style="color:rgba(255,255,255,0.4); font-size:12px; white-space:nowrap;">
                        {{ $user->created_at->format('d M Y') }}
                    </td>

                    {{-- ── CHANGE ROLE (AJAX, no page reload) ──────────────── --}}
                    <td>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <select class="role-select" id="role-select-{{ $user->id }}"
                                    data-user="{{ $user->id }}"
                                    data-current="{{ $user->role ?? 'customer' }}">
                                @foreach(\App\Models\User::ROLES as $role)
                                    <option value="{{ $role }}"
                                        {{ ($user->role ?? 'customer') === $role ? 'selected' : '' }}>
                                        {{ $roleIcons[$role] ?? '' }}
                                        {{ \App\Models\User::ROLE_LABELS[$role] }}
                                    </option>
                                @endforeach
                            </select>
                            <button
                                type="button"
                                onclick="applyRole({{ $user->id }}, '{{ $user->username }}')"
                                id="role-btn-{{ $user->id }}"
                                class="btn btn-sm btn-outline"
                                style="padding:5px 14px; font-size:12px; white-space:nowrap; letter-spacing:1px;">
                                APPLY
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align:center; color:rgba(255,255,255,0.3); padding:50px;">
                        <div style="font-size:32px; margin-bottom:10px;">👥</div>
                        No users found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
        <div style="padding:16px 20px; border-top:1px solid rgba(255,255,255,0.07);">
            {{ $users->links('dashboard.pagination') }}
        </div>
    @endif
</div>

{{-- ── Toast element (for AJAX feedback) ──────────────────────────────────── --}}
<div class="flash-toast" id="ajaxToast"></div>

@endif
@endsection

@push('scripts')
<script>
const ROLE_BADGE_CLASS = {
    customer : 'role-badge-customer',
    vip      : 'role-badge-vip',
    reseller : 'role-badge-reseller',
    banned   : 'role-badge-banned',
};
const ROLE_LABEL = {
    customer : 'CUSTOMER',
    vip      : 'VIP',
    reseller : 'RESELLER',
    banned   : 'BANNED',
};

// ── CSRF — read from meta tag added to layout <head> ─────────────────────
// Fallback: also try the hidden _token from any form on the page
function getCsrf() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) return meta.content;
    const input = document.querySelector('input[name="_token"]');
    if (input) return input.value;
    return '';
}

// ── Toast ─────────────────────────────────────────────────────────────────
let toastTimer;
function showToast(msg, type = 'success') {
    const el = document.getElementById('ajaxToast');
    el.innerHTML = (type === 'success'
        ? '<span style="color:#22c55e;font-size:16px;">✓</span> '
        : '<span style="color:#ef4444;font-size:16px;">✕</span> ') + msg;
    el.className = `flash-toast ${type} show`;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove('show'), 3400);
}

// ── Spinner SVG ───────────────────────────────────────────────────────────
const SPINNER = `<svg style="width:14px;height:14px;animation:spin 0.7s linear infinite;vertical-align:middle;"
    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
</svg>`;

// ── Apply role ────────────────────────────────────────────────────────────
async function applyRole(userId, username) {
    const select  = document.getElementById(`role-select-${userId}`);
    const btn     = document.getElementById(`role-btn-${userId}`);
    const row     = document.getElementById(`user-row-${userId}`);
    const newRole = select.value;
    const curRole = select.dataset.current;

    if (newRole === curRole) {
        // Shake button to signal no-op
        btn.style.animation = 'none';
        btn.offsetHeight; // reflow
        btn.style.animation = 'shake 0.35s ease';
        showToast(`@${username} is already ${ROLE_LABEL[newRole]}.`, 'error');
        return;
    }

    // Confirm
    const msg = newRole === 'banned'
        ? `⚠️ Ban @${username}?\nThey will lose access to the store.`
        : `Change @${username} → ${ROLE_LABEL[newRole]}?`;
    if (!confirm(msg)) { select.value = curRole; return; }

    // ── Loading state ─────────────────────────────────────────────────────
    btn.disabled   = true;
    btn.innerHTML  = SPINNER;
    btn.style.minWidth = '64px';
    select.disabled = true;
    row.style.transition = 'opacity 0.2s';
    row.style.opacity    = '0.55';

    try {
        const res = await fetch(`/dashboard/users/${userId}/role`, {
            method  : 'PUT',
            headers : {
                'Content-Type'     : 'application/json',
                'Accept'           : 'application/json',
                'X-CSRF-TOKEN'     : getCsrf(),
                'X-Requested-With' : 'XMLHttpRequest',
            },
            body: JSON.stringify({ role: newRole }),
        });

        const data = await res.json().catch(() => ({}));

        if (res.ok && data.success) {
            // ── Update badge with pop animation ───────────────────────────
            const badge = document.getElementById(`role-badge-${userId}`);
            badge.style.transform  = 'scale(0.6)';
            badge.style.opacity    = '0';
            badge.style.transition = 'all 0.15s ease';

            setTimeout(() => {
                badge.className   = `badge ${ROLE_BADGE_CLASS[newRole]}`;
                badge.textContent = ROLE_LABEL[newRole];
                badge.style.cssText += '; font-size:12px; letter-spacing:1px; transform:scale(1.15); opacity:1; transition:all 0.2s ease;';
                setTimeout(() => badge.style.transform = 'scale(1)', 200);
            }, 150);

            // Row flash green
            row.style.background = 'rgba(34,197,94,0.07)';
            setTimeout(() => row.style.background = '', 1200);

            select.dataset.current = newRole;
            // Reset button to checkmark briefly then back to APPLY
            btn.innerHTML  = '✓';
            btn.style.color = '#22c55e';
            setTimeout(() => {
                btn.innerHTML  = 'APPLY';
                btn.style.color = '';
            }, 1400);

            showToast(data.message || `@${username} → ${ROLE_LABEL[newRole]}`);
        } else {
            select.value = curRole;
            // Row flash red
            row.style.background = 'rgba(239,68,68,0.07)';
            setTimeout(() => row.style.background = '', 900);

            btn.innerHTML = 'APPLY';
            showToast(data.message || 'Failed to update role.', 'error');
        }
    } catch {
        select.value  = curRole;
        btn.innerHTML = 'APPLY';
        showToast('Network error — check your connection.', 'error');
    } finally {
        select.disabled      = false;
        btn.disabled         = false;
        btn.style.minWidth   = '';
        row.style.opacity    = '1';
    }
}

// ── Auto-hide server-side flash ───────────────────────────────────────────
const serverToast = document.getElementById('flashToast');
if (serverToast) setTimeout(() => serverToast.classList.remove('show'), 3500);
</script>

<style>
@keyframes spin  { to { transform: rotate(360deg); } }
@keyframes shake {
    0%,100% { transform: translateX(0); }
    20%     { transform: translateX(-5px); }
    40%     { transform: translateX(5px); }
    60%     { transform: translateX(-4px); }
    80%     { transform: translateX(4px); }
}
</style>

<style>
/* ── Users stats grid responsive ─────────────────────────────────────────── */
.users-stats-grid {
    grid-template-columns: repeat(5, 1fr) !important;
}
@media (max-width: 700px) {
    .users-stats-grid {
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 8px !important;
    }
}
@media (max-width: 420px) {
    .users-stats-grid {
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 6px !important;
    }
    .users-stats-grid .stat-card {
        padding: 10px 8px !important;
    }
    .users-stats-grid .stat-value {
        font-size: 18px !important;
    }
    .users-stats-grid .stat-label {
        font-size: 9px !important;
        letter-spacing: 0.5px !important;
    }
}

/* ── Search input full-width on mobile ────────────────────────────────────── */
@media (max-width: 600px) {
    .search-input {
        width: 100% !important;
    }
    .card-header form {
        width: 100% !important;
        margin-left: 0 !important;
    }
}
</style>
@endpush
