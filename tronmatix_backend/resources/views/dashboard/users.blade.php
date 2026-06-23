{{-- resources/views/dashboard/users.blade.php --}}
@extends('dashboard.layout')
@section('title', strtoupper(__('dashboard.nav.users')))

@push('styles')
<style>
/* ── Role badges ─────────────────────────────────────────────────────────── */
.role-badge-customer { background:rgba(156,163,175,0.15); color:#9CA3AF; border:1px solid rgba(156,163,175,0.3); }
.role-badge-vip      { background:rgba(249,115,22,0.15);  color:#F97316; border:1px solid rgba(249,115,22,0.4); }
.role-badge-reseller { background:rgba(59,130,246,0.15);  color:#3B82F6; border:1px solid rgba(59,130,246,0.4); }
.role-badge-banned   { background:rgba(239,68,68,0.15);   color:#EF4444; border:1px solid rgba(239,68,68,0.4); }

/* ── Inline role select ──────────────────────────────────────────────────── */
.role-select {
    background: var(--dark-700);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text-primary);
    font-family: 'Rajdhani', sans-serif;
    font-size: var(--title-size);
    font-weight: 600;
    padding: 5px 10px;
    cursor: pointer;
    outline: none;
    transition: border-color 0.2s, color 0.2s;
}
.role-select:hover { border-color: var(--orange); }
.role-select:focus { border-color: var(--orange); }
/* Role color per selected value */
.role-select[data-role="customer"] { color: #9CA3AF; border-color: rgba(156,163,175,0.3); }
.role-select[data-role="vip"]      { color: #F97316; border-color: rgba(249,115,22,0.5); }
.role-select[data-role="reseller"] { color: #3B82F6; border-color: rgba(59,130,246,0.5); }
.role-select[data-role="banned"]   { color: #EF4444; border-color: rgba(239,68,68,0.5); }
/* Option colors in dropdown list */
.role-select option[value="customer"] { color: #9CA3AF; background: var(--dark-900); }
.role-select option[value="vip"]      { color: #F97316; background: var(--dark-900); }
.role-select option[value="reseller"] { color: #3B82F6; background: var(--dark-900); }
.role-select option[value="banned"]   { color: #EF4444; background: var(--dark-900); }

/* ── Filter tabs ─────────────────────────────────────────────────────────── */
.filter-tab {
    padding: 6px 14px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: transparent;
    color: var(--text-muted);
    font-family: 'Rajdhani', sans-serif;
    font-size: var(--title-size);
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
.count-pill                     { background: var(--dark-700); border-radius: 20px; padding: 1px 8px; font-size: var(--title-size); }
.filter-tab.active .count-pill  { background: rgba(249,115,22,0.2); }

/* ── Search ──────────────────────────────────────────────────────────────── */
.search-input {
    background: var(--dark-700);
    border: 1px solid var(--border);
    border-radius: 10px;
    color: var(--text-primary);
    font-family: 'Rajdhani', sans-serif;
    font-size: var(--title-size);
    padding: 8px 16px 8px 38px;
    outline: none;
    width: 230px;
    transition: border-color 0.2s;
}
.search-input:focus             { border-color: var(--orange); }
.search-input::placeholder      { color: var(--text-muted); }

/* ── VIP progress bar ────────────────────────────────────────────────────── */
.vip-bar-fill {
    height: 100%;
    border-radius: 4px;
    background: linear-gradient(90deg, #F97316, #fb923c);
    transition: width 0.8s cubic-bezier(0.4,0,0.2,1);
    box-shadow: 0 0 6px rgba(249,115,22,0.4);
}

/* ── Table hover ─────────────────────────────────────────────────────────── */
tbody tr:hover td { background: var(--dark-700); }

/* ── Flash toast ─────────────────────────────────────────────────────────── */
.flash-toast {
    position: fixed; bottom: 28px; right: 28px; z-index: 9999;
    display: flex; align-items: center; gap: 10px;
    background: var(--dark-800); border: 1px solid var(--border);
    border-radius: 12px; padding: 12px 18px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.4);
    font-family: 'Rajdhani', sans-serif; font-size: var(--title-size); font-weight: 600;
    color: var(--text-primary); opacity: 0; transform: translateY(8px);
    transition: opacity 0.25s, transform 0.25s;
    pointer-events: none;
}
.flash-toast.show { opacity: 1; transform: translateY(0); }
.flash-toast.success { border-left: 3px solid #22c55e; }
.flash-toast.error   { border-left: 3px solid #ef4444; }
</style>
@endpush

@section('content')

@include('dashboard._permission_check', ['feature' => 'users'])
@php $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false; @endphp
@if(!$_permDenied)
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
            <div class="stat-icon"><span style="font-size: var(--title-size);">{{ $roleIcons[$role] }}</span></div>
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
        @php $telegramCount = \App\Models\User::whereNotNull('telegram_chat_id')->count(); @endphp
        <div class="stat-card" style="border-color:rgba(34,158,217,0.25);background:rgba(34,158,217,0.06);">
            <div class="stat-icon" style="background:rgba(34,158,217,0.15);border-color:rgba(34,158,217,0.3);">
                <span style="font-size: var(--title-size);">&#9992;&#65039;</span>
            </div>
            <div>
                <div class="stat-value" style="color:#229ED9;">{{ $telegramCount }}</div>
                <div class="stat-label" style="color:#229ED9;">TELEGRAM</div>
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
            <form id="searchForm" method="GET" action="{{ route('dashboard.users') }}" style="position:relative; margin-left:auto;">
                @if(request('role'))
                    <input type="hidden" name="role" value="{{ request('role') }}">
                @endif
                <svg style="position:absolute;left:11px;top:50%;transform:translateY(-50%);width:15px;height:15px;stroke:rgba(255,255,255,0.3)"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="searchInput" name="search" class="search-input"
                       placeholder="Search username, email..."
                       value="{{ request('search') }}" />
            </form>
        </div>

        {{-- ── Table ───────────────────────────────────────────────────────────── --}}
        <div class="table-wrap">
            <table id="userTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>USERNAME</th>
                        <th>EMAIL</th>
                        <th>PHONE</th>
                        <th>ORDERS</th>
                        <th>SPENT</th>
                        <th>2FA</th>
                        <th>TELEGRAM</th>
                        <th>ROLE</th>
                        <th>JOINED</th>
                        {{-- <th style="min-width:200px;">CHANGE ROLE</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr id="user-row-{{ $user->id }}" class="user-row">
                        <td class="user-id" style="color:rgba(255,255,255,0.3); font-size: var(--title-size);">{{ $user->id }}</td>
                        <td class="user-info">
                            <div style="display:flex; align-items:center; gap:10px;">
                                @php
                                    $userAvatar = $user->avatar
                                        ? (Str::startsWith($user->avatar, ['http://','https://'])
                                            ? $user->avatar
                                            : asset('storage/' . $user->avatar))
                                        : null;
                                @endphp
                                <div style="width:36px; height:36px; border-radius:50%; flex-shrink:0; overflow:hidden;
                                            border:1.5px solid rgba(249,115,22,0.3); position:relative;">
                                    @if($userAvatar)
                                        <img src="{{ $userAvatar }}" alt="{{ $user->username }}"
                                             style="width:100%; height:100%; object-fit:cover; display:block;"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                                        <div style="display:none; width:100%; height:100%;
                                                    background:linear-gradient(135deg,#F97316,#ea580c);
                                                    align-items:center; justify-content:center;
                                                    font-weight:800; font-size: var(--title-size); color:#fff; position:absolute; inset:0;">
                                            {{ strtoupper(substr($user->username, 0, 1)) }}
                                        </div>
                                    @else
                                        <div style="width:100%; height:100%;
                                                    background:linear-gradient(135deg,#F97316,#ea580c);
                                                    display:flex; align-items:center; justify-content:center;
                                                    font-weight:800; font-size: var(--title-size); color:#fff;">
                                            {{ strtoupper(substr($user->username, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <div class="user-username" style="font-weight:700; font-size: var(--title-size); cursor:pointer;"
                                         onclick="openUserInfo({{ $user->id }}, @js($user->username), @js($user->name ?? ''), @js($user->email ?? ''), @js($user->phone ?? ''), @js($user->avatar ?? ''), @js($user->role ?? 'customer'), @js($user->created_at->format('d M Y')), {{ $user->orders_count ?? 0 }}, {{ (float)($user->orders_sum_total ?? $user->total_spent ?? 0) }}, {{ $user->two_factor_enabled ? 'true' : 'false' }}, @js($user->telegram_chat_id ? '@'.($user->telegram_username ?? 'connected') : ''))"
                                         onmouseover="this.style.color='#F97316'" onmouseout="this.style.color=''">
                                        {{ $user->username }}
                                    </div>
                                    @if($user->name && $user->name !== $user->username)
                                        <div class="user-name" style="font-size: var(--title-size); color:rgba(255,255,255,0.3);">{{ $user->name }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="user-email" style="color:rgba(255,255,255,0.5); font-size: var(--title-size);">{{ $user->email ?? '—' }}</td>
                        <td class="user-phone" style="color:rgba(255,255,255,0.5); font-size: var(--title-size);">{{ $user->phone ?? '—' }}</td>
                        {{-- ... rest of table ... --}}

                        {{-- Orders --}}
                        <td>
                            <span class="badge {{ $user->orders_count > 0 ? 'badge-orange' : 'badge-gray' }}">
                                {{ $user->orders_count }}
                            </span>
                        </td>

                        {{-- Total spent + VIP progress ─────────────────────── --}}
                        @php
                            $spent = (float) ($user->orders_sum_total ?? $user->total_spent ?? 0);
                            /*
                            $vipGoal  = 1000;
                            $pct      = min(100, round(($spent / $vipGoal) * 100));
                            $isVip    = ($user->role ?? 'customer') === 'vip';
                            */
                        @endphp
                        <td style="min-width:120px;">
                            <div style="font-weight:700; font-size: var(--title-size); color:{{ /* $spent >= $vipGoal ? '#F97316' : */ '#fff' }};">
                                ${{ number_format($spent, 0) }}
                            </div>
                            {{--
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
                                <div style="font-size: var(--text-md); color:rgba(255,255,255,0.3); margin-top:3px; letter-spacing:0.5px;">
                                     ${{ number_format($spent, 0) }} / $1,000 VIP
                                </div>
                            </div>
                            @else
                            <div style="font-size: var(--text-sm); color:#F97316; margin-top:3px; letter-spacing:1px; font-weight:700;">
                                ⭐ VIP MEMBER
                            </div>
                            @endif
                            --}}
                        </td>

                        {{-- 2FA --}}
                        <td>
                            @if($user->two_factor_enabled)
                                <span class="badge badge-paid">ON</span>
                            @else
                                <span class="badge badge-gray">OFF</span>
                            @endif
                        </td>

                        {{-- Telegram --}}
                        <td>
                            @if($user->telegram_chat_id)
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:6px;font-size: var(--title-size);font-weight:700;background:rgba(34,158,217,0.15);border:1px solid rgba(34,158,217,0.3);color:#229ED9;" title="{{ $user->telegram_username ? '@'.$user->telegram_username : 'Connected' }}">
                                    ✈️ {{ $user->telegram_username ? '@'.$user->telegram_username : 'Connected' }}
                                </span>
                            @else
                                <span style="font-size: var(--title-size);color:rgba(255,255,255,0.25);">—</span>
                            @endif
                        </td>

                        {{-- Current role badge --}}
                        <td>
                            <span class="badge role-badge-{{ $user->role ?? 'customer' }}"
                                  id="role-badge-{{ $user->id }}"
                                  style="font-size: var(--title-size); letter-spacing:1px;">
                                {{ strtoupper(\App\Models\User::ROLE_LABELS[$user->role ?? 'customer'] ?? 'CUSTOMER') }}
                            </span>
                        </td>

                        <td style="color:rgba(255,255,255,0.4); font-size: var(--title-size); white-space:nowrap;">
                            {{ $user->created_at->format('d M Y') }}
                        </td>

                        {{-- ── CHANGE ROLE (AJAX, no page reload) ──────────────── --}}
                        {{-- <td>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <select class="role-select" id="role-select-{{ $user->id }}"
                                        data-user="{{ $user->id }}"
                                        data-current="{{ $user->role ?? 'customer' }}"
                                        data-role="{{ $user->role ?? 'customer' }}"
                                        onchange="this.dataset.role=this.value">
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
                                    style="padding:5px 14px; font-size: var(--title-size); white-space:nowrap; letter-spacing:1px;">
                                    APPLY
                                </button>
                            </div>
                        </td> --}}
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" style="text-align:center; color:rgba(255,255,255,0.3); padding:50px;">
                            <div style="font-size: var(--title-size); margin-bottom:10px;">👥</div>
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


    {{-- ── User Info Modal ───────────────────────────────────────────────────────── --}}
    <div id="user-info-modal" style="display:none; position:fixed; inset:0; z-index:9999;
        background:rgba(0,0,0,0.75); backdrop-filter:blur(6px);
        align-items:center; justify-content:center; padding:16px;">
        <div id="user-info-card" style="width:100%; max-width:440px; border-radius:20px; overflow:hidden;
                    background:linear-gradient(145deg,#141414,#1a1a1a);
                    border:1px solid rgba(255,255,255,0.1);
                    box-shadow:0 32px 80px rgba(0,0,0,0.7);
                    animation:uiModalIn .3s cubic-bezier(0.34,1.2,0.64,1);
                    font-family:Rajdhani,sans-serif;">

            {{-- Header --}}
            <div style="padding:24px 24px 0; display:flex; align-items:center; justify-content:space-between;">
                <div style="font-size: var(--title-size); font-weight:800; letter-spacing:2px; color:rgba(255,255,255,0.7);">USER INFORMATION</div>
                <button onclick="closeUserInfo()"
                    style="width:32px; height:32px; border-radius:8px; background:rgba(255,255,255,0.06);
                           border:1px solid rgba(255,255,255,0.1); color:rgba(255,255,255,0.4);
                           font-size: var(--title-size); cursor:pointer; display:flex; align-items:center; justify-content:center;"
                    onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">✕</button>
            </div>

            {{-- Avatar + name --}}
            <div style="padding:20px 24px; display:flex; align-items:center; gap:16px;">
                <div id="ui-avatar-wrap" style="width:72px; height:72px; border-radius:50%; overflow:hidden; flex-shrink:0;
                    border:2.5px solid #F97316; box-shadow:0 0 0 3px rgba(249,115,22,0.15);">
                </div>
                <div>
                    <div id="ui-username" style="font-size: var(--title-size); font-weight:900; color:#fff; letter-spacing:1px;"></div>
                    <div id="ui-name" style="font-size: var(--title-size); color:rgba(255,255,255,0.4); margin-top:2px;"></div>
                    <div id="ui-role-badge" style="margin-top:6px;"></div>
                </div>
            </div>

            {{-- Info grid --}}
            <div style="padding:0 24px 8px; display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07); border-radius:10px; padding:12px;">
                    <div style="font-size: var(--title-size); color:rgba(255,255,255,0.35); letter-spacing:2px; font-weight:700; margin-bottom:4px;">EMAIL</div>
                    <div id="ui-email" style="font-size: var(--title-size); color:#fff; font-weight:600; word-break:break-all;"></div>
                </div>
                <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07); border-radius:10px; padding:12px;">
                    <div style="font-size: var(--title-size); color:rgba(255,255,255,0.35); letter-spacing:2px; font-weight:700; margin-bottom:4px;">PHONE</div>
                    <div id="ui-phone" style="font-size: var(--title-size); color:#fff; font-weight:600;"></div>
                </div>
                <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07); border-radius:10px; padding:12px;">
                    <div style="font-size: var(--title-size); color:rgba(255,255,255,0.35); letter-spacing:2px; font-weight:700; margin-bottom:4px;">ORDERS</div>
                    <div id="ui-orders" style="font-size: var(--title-size); color:#F97316; font-weight:900;"></div>
                </div>
                <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07); border-radius:10px; padding:12px;">
                    <div style="font-size: var(--title-size); color:rgba(255,255,255,0.35); letter-spacing:2px; font-weight:700; margin-bottom:4px;">TOTAL SPENT</div>
                    <div id="ui-spent" style="font-size: var(--title-size); color:#22c55e; font-weight:900;"></div>
                </div>
                <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07); border-radius:10px; padding:12px;">
                    <div style="font-size: var(--title-size); color:rgba(255,255,255,0.35); letter-spacing:2px; font-weight:700; margin-bottom:4px;">JOINED</div>
                    <div id="ui-joined" style="font-size: var(--title-size); color:#fff; font-weight:600;"></div>
                </div>
                <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07); border-radius:10px; padding:12px;">
                    <div style="font-size: var(--title-size); color:rgba(255,255,255,0.35); letter-spacing:2px; font-weight:700; margin-bottom:4px;">2FA</div>
                    <div id="ui-2fa" style="font-size: var(--title-size); font-weight:700;"></div>
                </div>
                <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07); border-radius:10px; padding:12px; grid-column:span 2;">
                    <div style="font-size: var(--title-size); color:rgba(255,255,255,0.35); letter-spacing:2px; font-weight:700; margin-bottom:4px;">TELEGRAM</div>
                    <div id="ui-telegram" style="font-size: var(--title-size); font-weight:700;"></div>
                </div>
            </div>

            {{--
            <div id="ui-vip-wrap" style="padding:0 24px 20px;">
                <div style="background:rgba(249,115,22,0.06); border:1px dashed rgba(249,115,22,0.25); border-radius:10px; padding:12px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <div style="font-size: var(--title-size); color:#F97316; font-weight:700; letter-spacing:1px;">⭐ VIP PROGRESS</div>
                        <div id="ui-vip-pct" style="font-size: var(--title-size); color:rgba(255,255,255,0.4); font-weight:600;"></div>
                    </div>
                    <div style="height:6px; border-radius:6px; background:rgba(255,255,255,0.08); overflow:hidden;">
                        <div id="ui-vip-bar" style="height:100%; border-radius:6px; background:linear-gradient(90deg,#F97316,#fb923c); transition:width 0.6s ease;"></div>
                    </div>
                </div>
            </div>
            --}}

            {{-- Footer button --}}
            <div style="padding:0 24px 24px;">
                <a id="ui-view-orders-btn" href="#"
                    style="display:block; text-align:center; padding:11px; border-radius:10px;
                           background:linear-gradient(135deg,#F97316,#ea580c); color:#fff;
                           font-size: var(--title-size); font-weight:800; letter-spacing:1px; text-decoration:none;
                           box-shadow:0 4px 16px rgba(249,115,22,0.3); transition:opacity .2s;"
                    onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                    📦 VIEW ORDERS
                </a>
            </div>
        </div>
    </div>

    <style>
    @keyframes uiModalIn { from { opacity:0; transform:scale(.93) translateY(16px); } to { opacity:1; transform:none; } }
    </style>

    <script>
    const ROLE_COLORS = {
        customer: { bg:'rgba(156,163,175,0.15)', color:'#9CA3AF', border:'rgba(156,163,175,0.3)', label:'CUSTOMER' },
        vip:      { bg:'rgba(249,115,22,0.15)',  color:'#F97316', border:'rgba(249,115,22,0.4)',  label:'⭐ VIP' },
        reseller: { bg:'rgba(59,130,246,0.15)',  color:'#3B82F6', border:'rgba(59,130,246,0.4)',  label:'🏪 RESELLER' },
        banned:   { bg:'rgba(239,68,68,0.15)',   color:'#EF4444', border:'rgba(239,68,68,0.4)',   label:'🚫 BANNED' },
    };

    function openUserInfo(id, username, name, email, phone, avatar, role, joined, orders, spent, twofa, telegram) {
        const modal = document.getElementById('user-info-modal');
        modal.style.display = 'flex';

        // Avatar
        const avatarWrap = document.getElementById('ui-avatar-wrap');
        const initial = (username || '?').charAt(0).toUpperCase();
        if (avatar) {
            avatarWrap.innerHTML = `<img src="${avatar}" alt="${username}"
                style="width:100%;height:100%;object-fit:cover;display:block;"
                onerror="this.style.display='none';this.nextSibling.style.display='flex'" />
                <div style="display:none;width:100%;height:100%;background:linear-gradient(135deg,#F97316,#ea580c);
                    align-items:center;justify-content:center;font-weight:900;font-size: var(--title-size);color:#fff;">${initial}</div>`;
        } else {
            avatarWrap.innerHTML = `<div style="width:100%;height:100%;background:linear-gradient(135deg,#F97316,#ea580c);
                display:flex;align-items:center;justify-content:center;font-weight:900;font-size: var(--title-size);color:#fff;">${initial}</div>`;
        }

        // Basic info
        document.getElementById('ui-username').textContent = username;
        document.getElementById('ui-name').textContent = (name && name !== username) ? name : '';
        document.getElementById('ui-email').textContent = email || '—';
        document.getElementById('ui-phone').textContent = phone || '—';
        document.getElementById('ui-orders').textContent = orders;

        // Format spent to 2 decimal places to avoid truncation
        const formattedSpent = parseFloat(spent).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('ui-spent').textContent = '$' + formattedSpent;

        document.getElementById('ui-joined').textContent = joined;
        document.getElementById('ui-2fa').innerHTML = twofa
            ? '<span style="color:#22c55e;">✓ ENABLED</span>'
            : '<span style="color:rgba(255,255,255,0.3);">— OFF</span>';
        document.getElementById('ui-telegram').innerHTML = telegram
            ? '<span style="color:#229ED9;">✈️ ' + telegram + '</span>'
            : '<span style="color:rgba(255,255,255,0.3);">— Not connected</span>';

        // Role badge
        const rm = ROLE_COLORS[role] || ROLE_COLORS.customer;
        document.getElementById('ui-role-badge').innerHTML = `<span style="display:inline-flex;align-items:center;gap:4px;
            padding:4px 12px;border-radius:20px;font-size: var(--title-size);font-weight:800;letter-spacing:1px;
            background:${rm.bg};color:${rm.color};border:1px solid ${rm.border};">${rm.label}</span>`;

        // VIP progress
        const vipGoal = 1000;
        const spentNum = parseFloat(spent) || 0;
        const pct = Math.min(100, Math.round((spentNum / vipGoal) * 100));
        const vipWrap = document.getElementById('ui-vip-wrap');
        if (role === 'vip') {
            vipWrap.style.display = 'none';
        } else {
            vipWrap.style.display = 'block';
            document.getElementById('ui-vip-bar').style.width = pct + '%';
            document.getElementById('ui-vip-pct').textContent = pct + '% · $' + vipGoal + ' goal';
        }

        // View orders link
        document.getElementById('ui-view-orders-btn').href = `/dashboard/orders?user=${id}`;
    }

    function closeUserInfo() {
        document.getElementById('user-info-modal').style.display = 'none';
    }
    document.getElementById('user-info-modal').addEventListener('click', function(e) {
        if (e.target === this) closeUserInfo();
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeUserInfo(); });
    </script>
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
        ? '<span style="color:#22c55e;font-size: var(--title-size);">✓</span> '
        : '<span style="color:#ef4444;font-size: var(--title-size);">✕</span> ') + msg;
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
                badge.style.cssText += '; font-size: var(--title-size); letter-spacing:1px; transform:scale(1.15); opacity:1; transition:all 0.2s ease;';
                setTimeout(() => badge.style.transform = 'scale(1)', 200);
            }, 150);

            // Row flash green
            row.style.background = 'rgba(34,197,94,0.07)';
            setTimeout(() => row.style.background = '', 1200);

            select.dataset.current = newRole;
            select.dataset.role = newRole;
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

{{-- ── Auto-hide server-side flash ─────────────────────────────────────────── --}}
const serverToast = document.getElementById('flashToast');
if (serverToast) setTimeout(() => serverToast.classList.remove('show'), 3500);

{{-- ── Real-time Search ────────────────────────────────────────────────────── --}}
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const searchTerm = this.value;
    const role = new URLSearchParams(window.location.search).get('role') || 'all';

    searchTimeout = setTimeout(() => {
        fetch(`{{ route('dashboard.users') }}?search=${encodeURIComponent(searchTerm)}&role=${encodeURIComponent(role)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTableBody = doc.getElementById('userTable').querySelector('tbody');
            document.getElementById('userTable').querySelector('tbody').innerHTML = newTableBody.innerHTML;

            // Update URL for consistency without page reload
            const newUrl = `{{ route('dashboard.users') }}?search=${encodeURIComponent(searchTerm)}&role=${encodeURIComponent(role)}`;
            window.history.pushState({ path: newUrl }, '', newUrl);
        })
        .catch(err => console.error('Search failed:', err));
    }, 300); // 300ms debounce
});
</script>
@endpush
