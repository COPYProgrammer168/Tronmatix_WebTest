@extends('dashboard.layout')
@section('title', strtoupper(__('dashboard.nav.staff')))
@section('suppress_flash') @endsection

@php
    // Resolve the current user from whichever guard they logged in under.
    // Admin guard = superadmin/admin. Staff guard = editor/seller/delivery/developer.
    $me = Auth::guard('admin')->user() ?? Auth::guard('staff')->user();
    $myRole = $me->role ?? 'editor';
    $isSuper = $myRole === 'superadmin';
    $isAdmin = in_array($myRole, ['admin', 'superadmin']);

    // Roles available for ADMINS table (superadmin can assign superadmin)
    $adminRoles = $isSuper ? ['superadmin', 'admin'] : ['admin'];

    // Roles available for STAFF table
    $staffRoles = ['editor', 'seller', 'delivery', 'developer'];

    $adminRoleMeta = [
        'superadmin' => [
            'label' => __('dashboard.staff.superAdmin'),
            'color' => '#F97316',
            'icon' => '👑',
            'desc' => __('dashboard.staff.fullSystemOwner'),
        ],
        'admin' => [
            'label' => __('dashboard.staff.admin'),
            'color' => '#fb923c',
            'icon' => '🛡️',
            'desc' => __('dashboard.staff.fullAccess'),
        ],
    ];

    $staffRoleMeta = [
        'editor' => [
            'label' => __('dashboard.staff.editor'),
            'color' => '#3b82f6',
            'icon' => '✏️',
            'desc' => __('dashboard.staff.editorDesc'),
        ],
        'seller' => [
            'label' => __('dashboard.staff.seller'),
            'color' => '#10b981',
            'icon' => '🏪',
            'desc' => __('dashboard.staff.sellerDesc'),
        ],
        'delivery' => [
            'label' => __('dashboard.staff.delivery'),
            'color' => '#a855f7',
            'icon' => '🚚',
            'desc' => __('dashboard.staff.deliveryDesc'),
        ],
        'developer' => [
            'label' => __('dashboard.staff.developer'),
            'color' => '#06b6d4',
            'icon' => '💻',
            'desc' => __('dashboard.staff.developerDesc'),
        ],
    ];

    $allRoleMeta = $adminRoleMeta + $staffRoleMeta;

    // Active tab from query string: 'admins' or 'staff'
    $activeTab = request('tab', 'staff');
    $_km = app()->getLocale() === 'km';
    $_fw7 = $_km ? 400 : 700;
    $_fw8 = $_km ? 400 : 800;
    $_fw9 = $_km ? 400 : 900;
    $_fw6 = $_km ? 400 : 600;
@endphp

@section('content')

    @include('dashboard._permission_check', ['feature' => 'staff'])
    @php $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false; @endphp
    @if(!$_permDenied)
    {{-- Flash toasts --}}
    @php
        $_successMsg = session('success', '');
        // Detect a trailing invite URL (created by StaffController::invite / resendInvite)
        $_inviteLink = null;
        if (preg_match('#(https?://\S+/dashboard/invite/\w+)#', $_successMsg, $_m)) {
            $_inviteLink = $_m[1];
            $_successMsg = trim(str_replace($_m[1], '', $_successMsg));
        }
    @endphp
    @if (session('success'))
        <div id="page-toast"
            style="position:fixed;top:24px;right:24px;z-index:9999;display:flex;align-items:center;gap:12px;
    padding:14px 22px;border-radius:16px;background:var(--dark-800);
    border:1px solid rgba(34,197,94,0.4);box-shadow:0 16px 48px rgba(0,0,0,0.6);
    font-family:Rajdhani, var(--font-kh), sans-serif;animation:stToastIn .4s cubic-bezier(0.34,1.4,0.64,1);max-width:380px;">
            <div
                style="width:38px;height:38px;border-radius:50%;background:rgba(34,197,94,0.15);flex-shrink:0;
                border:1.5px solid rgba(34,197,94,0.4);display:flex;align-items:center;justify-content:center;
                font-size: var(--title-size);animation:popIn .4s .15s cubic-bezier(0.34,1.6,0.64,1) both;">
                ✓</div>
            <div style="flex:1;min-width:0;">
                <div style="font-size: var(--title-size);font-weight:{{ $_fw8 }};color:#22c55e;letter-spacing:1.5px;">SUCCESS</div>
                <div style="font-size: var(--title-size);color:var(--text-muted);margin-top:2px;line-height:1.4;">{{ $_successMsg }}
                </div>
                @if ($_inviteLink)
                    <div
                        style="display:flex;align-items:center;gap:8px;margin-top:10px;padding:8px 12px;border-radius:10px;
                               background:rgba(249,115,22,0.08);border:1px solid rgba(249,115,22,0.3);">
                        <div style="flex:1;min-width:0;font-size: var(--title-size);color:#F97316;font-weight:{{ $_fw7 }};
                            overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" id="invite-link-text">{{ $_inviteLink }}</div>
                        <button type="button" onclick="copyInviteLink(this)"
                            style="flex-shrink:0;padding:5px 12px;border-radius:8px;cursor:pointer;font-family:Rajdhani, var(--font-kh), sans-serif;
                                   font-size: var(--title-size);font-weight:{{ $_fw8 }};letter-spacing:1px;
                                   background:linear-gradient(135deg,#F97316,#ea580c);border:none;color:#fff;">COPY</button>
                    </div>
                @endif
            </div>
            <button onclick="dismissToast('page-toast')"
                style="flex-shrink:0;width:28px;height:28px;border-radius:8px;background:rgba(255,255,255,0.05);
                   border:1px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.3);font-size: var(--title-size);cursor:pointer;
                   display:flex;align-items:center;justify-content:center;transition:all .2s;"
                onmouseover="this.style.color='var(--text-primary)'"
                onmouseout="this.style.color='rgba(255,255,255,0.3)'">×</button>
            <div style="position:absolute;bottom:0;left:0;right:0;height:3px;border-radius:0 0 16px 16px;overflow:hidden;">
                <div
                    style="height:100%;width:100%;background:linear-gradient(90deg,#22c55e,#4ade80);
            animation:toastBar 8s linear forwards;border-radius:0 0 16px 16px;">
                </div>
            </div>
        </div>
        <script>
            setTimeout(() => dismissToast('page-toast'), 8000);
            function copyInviteLink(btn) {
                const text = document.getElementById('invite-link-text');
                const val = text.textContent.trim();
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(val).then(() => {
                        btn.textContent = 'COPIED ✓';
                        setTimeout(() => btn.textContent = 'COPY', 2000);
                    });
                } else {
                    const ta = document.createElement('textarea');
                    ta.value = val;
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                    btn.textContent = 'COPIED ✓';
                    setTimeout(() => btn.textContent = 'COPY', 2000);
                }
            }
        </script>
    @endif

    @if (session('error'))
        <div id="page-err"
            style="position:fixed;top:24px;right:24px;z-index:9999;display:flex;align-items:center;gap:12px;
    padding:14px 22px;border-radius:16px;background:var(--dark-800);
    border:1px solid rgba(239,68,68,0.4);box-shadow:0 16px 48px rgba(0,0,0,0.6);
    font-family:Rajdhani, var(--font-kh), sans-serif;animation:stToastIn .4s cubic-bezier(0.34,1.4,0.64,1);max-width:340px;position:relative;">
            <div
                style="width:38px;height:38px;border-radius:50%;background:rgba(239,68,68,0.12);flex-shrink:0;
                border:1.5px solid rgba(239,68,68,0.4);display:flex;align-items:center;justify-content:center;
                font-size: var(--title-size);">
                ✕</div>
            <div style="flex:1;min-width:0;">
                <div style="font-size: var(--title-size);font-weight:{{ $_fw8 }};color:#ef4444;letter-spacing:1.5px;">ERROR</div>
                <div style="font-size: var(--title-size);color:var(--text-muted);margin-top:2px;line-height:1.4;">{{ session('error') }}
                </div>
            </div>
            <button onclick="dismissToast('page-err')"
                style="flex-shrink:0;width:28px;height:28px;border-radius:8px;background:rgba(255,255,255,0.05);
                   border:1px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.3);font-size: var(--title-size);cursor:pointer;
                   display:flex;align-items:center;justify-content:center;"
                onmouseover="this.style.color='var(--text-primary)'"
                onmouseout="this.style.color='rgba(255,255,255,0.3)'">×</button>
            <div style="position:absolute;bottom:0;left:0;right:0;height:3px;border-radius:0 0 16px 16px;overflow:hidden;">
                <div
                    style="height:100%;width:100%;background:linear-gradient(90deg,#ef4444,#f87171);
            animation:toastBar 5s linear forwards;border-radius:0 0 16px 16px;">
                </div>
            </div>
        </div>
        <script>
            setTimeout(() => dismissToast('page-err'), 5000);
        </script>
    @endif

    {{-- ── Page Header ──────────────────────────────────────────────────────────── --}}
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:28px;">
        <div style="display:flex;align-items:center;gap:14px;">
            <div
                style="width:48px;height:48px;border-radius:14px;background:rgba(249,115,22,0.12);
                    border:1px solid rgba(249,115,22,0.3);display:flex;align-items:center;justify-content:center;font-size: var(--title-size);">
                🛡️</div>
            <div>
                <div style="font-size: var(--title-size);font-weight:{{ $_fw9 }};letter-spacing:3px;">ADMINS & STAFF</div>
                <div style="font-size: var(--title-size);color:var(--text-muted);margin-top:2px;">
                    Manage team members and their access levels
                    @if (!$isSuper)
                        · <span style="color:#F97316;">You cannot modify Super Admins</span>
                    @endif
                </div>
            </div>
        </div>
        {{-- Action buttons --}}
        <div style="display:flex;gap:10px;flex-wrap:wrap;">

            {{-- Request Access pending count badge --}}
            @php $pendingCount = \App\Models\StaffRequest::where('status','pending')->count(); @endphp
            @if ($isSuper && $pendingCount > 0)
                <a href="{{ route('dashboard.settings') }}#staff-requests"
                    style="display:flex;align-items:center;gap:8px;padding:11px 18px;border-radius:10px;
                  border:1px solid rgba(249,115,22,0.4);background:rgba(249,115,22,0.08);
                  color:#F97316;font-family:Rajdhani, var(--font-kh), sans-serif;font-size: var(--title-size);font-weight:{{ $_fw8 }};
                  letter-spacing:1.5px;text-decoration:none;transition:all .2s;position:relative;"
                    onmouseover="this.style.background='rgba(249,115,22,0.15)'"
                    onmouseout="this.style.background='rgba(249,115,22,0.08)'">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    REQUESTS
                    <span
                        style="background:#F97316;color:#fff;font-size: var(--title-size);font-weight:{{ $_fw9 }};
                         padding:1px 7px;border-radius:20px;letter-spacing:0.5px;">{{ $pendingCount }}</span>
                </a>
            @endif

            {{-- Invite button --}}
            <button id="invite-btn-top" onclick="openInviteModal()"
                style="display:flex;align-items:center;gap:8px;padding:11px 22px;border-radius:10px;border:none;cursor:pointer;
                       background:linear-gradient(135deg,#F97316,#ea580c);color:#fff;font-family:Rajdhani, var(--font-kh), sans-serif;
                       font-size: var(--title-size);font-weight:{{ $_fw8 }};letter-spacing:1.5px;box-shadow:0 4px 20px rgba(249,115,22,0.35);transition:all .2s;"
                onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 28px rgba(249,115,22,0.45)'"
                onmouseout="this.style.transform='';this.style.boxShadow='0 4px 20px rgba(249,115,22,0.35)'">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                    viewBox="0 0 24 24">
                    <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                    <circle cx="8.5" cy="7" r="4" />
                    <line x1="20" y1="8" x2="20" y2="14" />
                    <line x1="23" y1="11" x2="17" y2="11" />
                </svg>
                <span id="invite-btn-label">{{ $activeTab === 'admins' ? strtoupper(__('dashboard.staff.inviteAdmin')) : strtoupper(__('dashboard.staff.inviteStaff')) }}</span>
            </button>

        </div>
    </div>

    {{-- ── Tab Switcher ─────────────────────────────────────────────────────────── --}}
    <div
        style="display:flex;gap:4px;margin-bottom:24px;background:var(--dark-700);padding:4px;
            border-radius:12px;border:1px solid var(--border);width:fit-content;">
        <button class="tab-btn {{ $activeTab === 'staff' ? 'tab-active' : '' }}" onclick="switchTab('staff')"
            style="padding:9px 24px;border-radius:9px;border:none;cursor:pointer;font-family:Rajdhani, var(--font-kh), sans-serif;
                   font-size: var(--title-size);font-weight:{{ $_fw8 }};letter-spacing:1.5px;transition:all .2s;">
            👥 {{ strtoupper(__('dashboard.nav.staff')) }} ({{ $staff->count() }})
        </button>
        @if ($isSuper)
            <button class="tab-btn {{ $activeTab === 'admins' ? 'tab-active' : '' }}" onclick="switchTab('admins')"
                style="padding:9px 24px;border-radius:9px;border:none;cursor:pointer;font-family:Rajdhani, var(--font-kh), sans-serif;
                   font-size: var(--title-size);font-weight:{{ $_fw8 }};letter-spacing:1.5px;transition:all .2s;">
                🛡️ {{ strtoupper(__('dashboard.staff.admin')) }} ({{ $admins->count() }})
            </button>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════════
     STAFF TAB
══════════════════════════════════════════════════════════════════════════ --}}
    <div id="tab-staff" class="tab-panel" style="{{ $activeTab !== 'staff' ? 'display:none;' : '' }}">

        {{-- Role summary cards --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:10px;margin-bottom:24px;"
            class="role-summary-grid">
            @foreach ($staffRoleMeta as $rKey => $rMeta)
                @php $count = $staff->where('role',$rKey)->count(); @endphp
                <div class="role-card"
                    style="padding:12px 14px;border-radius:12px;background:var(--dark-700);
                    border:1px solid {{ $rMeta['color'] }}22;position:relative;overflow:hidden;">
                    <div style="position:absolute;top:-14px;right:-8px;font-size: var(--title-size);opacity:0.06;pointer-events:none;">
                        {{ $rMeta['icon'] }}</div>
                    <div style="font-size: var(--title-size);margin-bottom:2px;">{{ $rMeta['icon'] }}</div>
                    <div style="font-size: var(--title-size);font-weight:{{ $_fw9 }};color:{{ $rMeta['color'] }};line-height:1.1;">
                        {{ $count }}</div>
                    <div
                        style="font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:1px;color:{{ $rMeta['color'] }};margin-top:2px;">
                        {{ strtoupper($rMeta['label']) }}</div>
                    <div class="role-card-desc"
                        style="font-size: var(--title-size);color:var(--text-muted);margin-top:3px;line-height:1.3;">{{ $rMeta['desc'] }}
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pending invitations (created but not yet accepted) --}}
        @if (($pendingInvites ?? collect())->isNotEmpty())
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header">
                    <div class="card-title">✉️ PENDING INVITATIONS</div>
                    <div style="font-size: var(--title-size);color:var(--text-muted);">{{ $pendingInvites->count() }}
                        awaiting set-password</div>
                </div>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;" id="invite-table">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border);">
                                <th style="padding:12px 16px;text-align:left;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">NAME</th>
                                <th style="padding:12px 16px;text-align:left;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">EMAIL</th>
                                <th style="padding:12px 16px;text-align:center;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">ROLE</th>
                                <th style="padding:12px 16px;text-align:center;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">LINKS</th>
                                <th style="padding:12px 16px;text-align:right;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingInvites as $inv)
                                @php
                                    $iRole = $inv->role ?? 'editor';
                                    $iMeta = $staffRoleMeta[$iRole] ?? $staffRoleMeta['editor'];
                                    $linkCount = $inv->links->count();
                                @endphp
                                <tr style="border-bottom:1px solid var(--border);">
                                    <td style="padding:12px 16px;white-space:nowrap;font-weight:{{ $_fw7 }};color:var(--text-primary);">{{ $inv->name }}</td>
                                    <td style="padding:12px 16px;font-size: var(--title-size);color:var(--text-secondary);">{{ $inv->email }}</td>
                                    <td style="padding:12px 16px;text-align:center;">
                                        <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:6px;
                                            font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:1px;
                                            background:{{ $iMeta['color'] }}18;border:1px solid {{ $iMeta['color'] }}44;color:{{ $iMeta['color'] }};">
                                            {{ $iMeta['icon'] }} {{ strtoupper($iMeta['label']) }}
                                        </span>
                                    </td>
                                    <td style="padding:12px 16px;text-align:center;font-size: var(--title-size);color:var(--text-muted);">
                                        <span style="background:rgba(255,255,255,0.06);padding:2px 10px;border-radius:20px;font-weight:{{ $_fw8 }};">{{ $linkCount }} link{{ $linkCount !== 1 ? 's' : '' }}</span>
                                    </td>
                                    <td style="padding:12px 16px;text-align:right;white-space:nowrap;display:flex;gap:6px;justify-content:flex-end;align-items:center;">
                                        {{-- Expandable links list --}}
                                        <button type="button" onclick="toggleLinks(this, 'links-{{ $inv->id }}')"
                                            style="padding:5px 10px;border-radius:6px;cursor:pointer;font-family:Rajdhani, var(--font-kh), sans-serif;
                                                   font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:0.5px;
                                                   border:1px solid rgba(255,255,255,0.1);background:rgba(255,255,255,0.04);color:var(--text-muted);">
                                            LINKS ▾
                                        </button>
                                        {{-- Generate new link --}}
                                        <form method="POST" action="{{ route('dashboard.staff.invite.resend', $inv->id) }}" style="display:inline;" onsubmit="return generateNewLink(this, event)">
                                            @csrf
                                            <button type="submit" title="Generate a new invite link"
                                                style="padding:6px 14px;border-radius:8px;cursor:pointer;font-family:Rajdhani, var(--font-kh), sans-serif;
                                                       font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:1px;
                                                       border:1px solid rgba(249,115,22,0.3);background:rgba(249,115,22,0.08);color:#F97316;">+ NEW LINK</button>
                                        </form>
                                    </td>
                                </tr>
                                {{-- Expandable links detail row --}}
                                <tr id="links-{{ $inv->id }}" style="display:none;border-bottom:1px solid var(--border);">
                                    <td colspan="5" style="padding:12px 16px;">
                                        <div style="display:flex;flex-direction:column;gap:8px;">
                                            @foreach ($inv->links as $l)
                                                @php
                                                    $lValid = $l->isValid();
                                                    $lStatus = $l->used_at ? 'Used' : ($l->expires_at && $l->expires_at->isPast() ? 'Expired' : 'Active');
                                                    $lColor = $l->used_at ? '#ef4444' : ($l->expires_at && $l->expires_at->isPast() ? '#f59e0b' : '#22c55e');
                                                @endphp
                                                <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:8px;background:var(--dark-700);border:1px solid var(--border);flex-wrap:wrap;">
                                                    <span style="font-size:10px;font-weight:700;letter-spacing:1px;color:{{ $lColor }};min-width:50px;">{{ strtoupper($lStatus) }}</span>
                                                    <span style="flex:1;min-width:200px;font-size:12px;color:var(--text-secondary);word-break:break-all;font-family:monospace;">{{ $l->inviteUrl() }}</span>
                                                    <span style="font-size:11px;color:var(--text-muted);white-space:nowrap;">Exp: {{ $l->expires_at ? $l->expires_at->format('d M Y H:i') : 'Never' }}</span>
                                                    @if ($lValid)
                                                    <button type="button" onclick="copyText('{{ $l->inviteUrl() }}', this)"
                                                        style="padding:4px 10px;border-radius:6px;cursor:pointer;font-family:Rajdhani, var(--font-kh), sans-serif;
                                                               font-size:11px;font-weight:700;letter-spacing:1px;
                                                               border:1px solid rgba(249,115,22,0.3);background:rgba(249,115,22,0.08);color:#F97316;">COPY</button>
                                                    @endif
                                                </div>
                                            @endforeach
                                            @if ($inv->links->isEmpty())
                                                <div style="font-size:12px;color:var(--text-muted);padding:8px;">No links generated yet.</div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Filters --}}
        <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;align-items:center;">
            <div style="position:relative;flex:1;min-width:220px;">
                <svg style="position:absolute;left:14px;top:50%;transform:translateY(-50%);opacity:0.35;" width="16"
                    height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="text" id="staff-search" placeholder="{{ __('dashboard.staff.searchStaff') }}"
                    oninput="filterRows('staff')"
                    style="width:100%;padding:10px 14px 10px 42px;border-radius:10px;background:var(--dark-700);
                          border:1px solid var(--border);color:var(--text-primary);font-family:Rajdhani, var(--font-kh), sans-serif;
                          font-size: var(--title-size);outline:none;transition:border-color .2s;"
                    onfocus="this.style.borderColor='rgba(249,115,22,0.4)'" onblur="this.style.borderColor=''" />
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button class="role-filter active" data-table="staff" data-role="all"
                    onclick="setRoleFilter(this,'staff','all')"
                    style="padding:9px 16px;border-radius:8px;border:1px solid var(--border);
                           background:var(--dark-700);color:var(--text-primary);font-family:Rajdhani, var(--font-kh), sans-serif;
                           font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:1px;cursor:pointer;transition:all .2s;">{{ strtoupper(__('dashboard.staff.all')) }}</button>
                @foreach ($staffRoleMeta as $rKey => $rMeta)
                    <button class="role-filter" data-table="staff" data-role="{{ $rKey }}"
                        onclick="setRoleFilter(this,'staff','{{ $rKey }}')"
                        style="padding:9px 16px;border-radius:8px;border:1px solid {{ $rMeta['color'] }}30;
                           background:{{ $rMeta['color'] }}0d;color:{{ $rMeta['color'] }};font-family:Rajdhani, var(--font-kh), sans-serif;
                           font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:1px;cursor:pointer;transition:all .2s;">
                        {{ $rMeta['icon'] }} {{ strtoupper($rMeta['label']) }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Staff table --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">{{ strtoupper(__('dashboard.staff.staffMembers')) }}</div>
                <div style="font-size: var(--title-size);color:var(--text-muted);">{{ $staff->count() }}
                    {{ $staff->count() > 1 ? __('dashboard.staff.members') : __('dashboard.staff.member') }}</div>
            </div>

            @if ($staff->isEmpty())
                <div style="padding:64px 20px;text-align:center;color:var(--text-muted);">
                    <div style="font-size: var(--title-size);margin-bottom:12px;">👥</div>
                    <div style="font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:2px;">{{ strtoupper(__('dashboard.staff.noStaffYet')) }}</div>
                    <div style="font-size: var(--title-size);margin-top:6px;">{{ __('dashboard.staff.inviteFirst') }}</div>
                </div>
            @else
                <div style="overflow-x:auto;">
                    <style>
                        /* keep all columns visible on mobile — table scrolls horizontally */
                    </style>
                    <table style="width:100%;border-collapse:collapse;" id="staff-table">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border);">
                                <th
                                    style="padding:14px 20px;text-align:left;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};white-space:nowrap;">
                                    {{ strtoupper(__('dashboard.staff.staffMembers')) }}</th>
                                <th class="col-email"
                                    style="padding:14px 14px;text-align:left;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">
                                    {{ strtoupper(__('dashboard.table.email')) }}</th>
                                <th class="col-phone"
                                    style="padding:14px 14px;text-align:left;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">
                                    {{ strtoupper(__('dashboard.table.phone')) }}</th>
                                <th
                                    style="padding:14px 14px;text-align:center;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">
                                    {{ strtoupper(__('dashboard.table.role')) }}</th>
                                <th class="col-joined"
                                    style="padding:14px 14px;text-align:center;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">
                                    {{ strtoupper(__('dashboard.staff.memberSince')) }}</th>
                                <th class="col-status"
                                    style="padding:14px 14px;text-align:center;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">
                                    {{ strtoupper(__('dashboard.table.status')) }}</th>
                                <th
                                    style="padding:14px 20px;text-align:right;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">
                                    {{ strtoupper(__('dashboard.table.actions')) }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($staff as $member)
                                @php
                                    $mRole = $member->role ?? 'editor';
                                    $mMeta = $staffRoleMeta[$mRole] ?? $staffRoleMeta['editor'];
                                    $canEdit = true; // admins can always edit staff
                                    $active = $member->is_active ?? true;
                                    $isOnline = $active && $member->is_online;
                                    $initials = strtoupper(
                                        substr($member->name ?? '?', 0, 1) .
                                            (strpos($member->name, ' ') !== false
                                                ? substr($member->name, strpos($member->name, ' ') + 1, 1)
                                                : ''),
                                    );
                                    $memberAvatar = $member->avatar
                                        ? (Str::startsWith($member->avatar, ['http://', 'https://'])
                                            ? $member->avatar
                                            : asset('storage/' . $member->avatar))
                                        : null;
                                @endphp
                                <tr class="staff-row" data-table="staff" data-role="{{ $mRole }}"
                                    data-name="{{ strtolower($member->name ?? '') }}"
                                    data-email="{{ strtolower($member->email ?? '') }}"
                                    data-phone="{{ strtolower($member->phone ?? '') }}"
                                    style="border-bottom:1px solid var(--border);transition:background .15s;"
                                    onmouseover="this.style.background='rgba(15,23,42,0.02)'"
                                    onmouseout="this.style.background=''">

                                    <td style="padding:16px 20px;white-space:nowrap;">
                                        <div style="display:flex;align-items:center;gap:12px;">
                                            <div
                                                style="width:40px;height:40px;border-radius:12px;flex-shrink:0;overflow:hidden;
                                            background:{{ $mMeta['color'] }}18;border:1.5px solid {{ $mMeta['color'] }}44;
                                            display:flex;align-items:center;justify-content:center;">
                                                @if ($memberAvatar)
                                                    <img src="{{ $memberAvatar }}" alt="{{ $member->name }}"
                                                        style="width:100%;height:100%;object-fit:cover;"
                                                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
                                                    <span
                                                        style="display:none;width:100%;height:100%;align-items:center;justify-content:center;
                                                     font-size: var(--title-size);font-weight:{{ $_fw8 }};color:{{ $mMeta['color'] }};">{{ $initials }}</span>
                                                @else
                                                    <span
                                                        style="font-size: var(--title-size);font-weight:{{ $_fw8 }};color:{{ $mMeta['color'] }};">{{ $initials }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <div style="font-size: var(--title-size);font-weight:{{ $_fw7 }};color:var(--text-primary);cursor:pointer;"
                                                    onclick="openMemberProfile({{ $member->id }}, @js($member->name ?? ''), @js($member->email ?? ''), @js($memberAvatar ?? ''), '{{ $mRole }}', @js($mMeta['color']), @js($mMeta['icon']), @js($mMeta['label']), '{{ $member->created_at ? $member->created_at->format('d M Y') : '—' }}', {{ $active ? 'true' : 'false' }}, {{ $isOnline ? 'true' : 'false' }}, false)"
                                                    onmouseover="this.style.color='#F97316'"
                                                    onmouseout="this.style.color=''">
                                                    {{ $member->name }}
                                                </div>
                                                <div style="font-size: var(--title-size);color:var(--text-muted);margin-top:2px;">ID
                                                    #{{ $member->id }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="col-email"
                                        style="padding:16px 14px;font-size: var(--title-size);color:var(--text-secondary);">
                                        {{ $member->email }}</td>

                                    <td class="col-phone"
                                        style="padding:16px 14px;font-size: var(--title-size);color:var(--text-secondary);">
                                        {{ $member->phone ?? '—' }}</td>

                                    <td style="padding:16px 14px;text-align:center;">
                                        <form method="POST" action="{{ route('dashboard.staff.role', $member->id) }}"
                                            style="display:inline-block;" onchange="this.submit()">
                                            @csrf @method('PATCH')
                                            <select name="role"
                                                style="padding:5px 10px;border-radius:8px;cursor:pointer;
                                               background:{{ $mMeta['color'] }}18;border:1.5px solid {{ $mMeta['color'] }}44;
                                               color:{{ $mMeta['color'] }};font-family:Rajdhani, var(--font-kh), sans-serif;
                                               font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:1px;outline:none;">
                                                @foreach ($staffRoles as $rOpt)
                                                    <option value="{{ $rOpt }}"
                                                        {{ $mRole === $rOpt ? 'selected' : '' }}
                                                        style="background:var(--dark-900);color:var(--text-primary);">
                                                        {{ $staffRoleMeta[$rOpt]['icon'] }}
                                                        {{ strtoupper($staffRoleMeta[$rOpt]['label']) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>

                                    <td class="col-joined"
                                        style="padding:16px 14px;text-align:center;font-size: var(--title-size);color:var(--text-muted);">
                                        {{ $member->created_at ? $member->created_at->format('d M Y') : '—' }}
                                    </td>

                                    <td class="col-status" style="padding:16px 14px;text-align:center;">
                                        @php
                                            $active = $member->is_active ?? true;
                                            $isOnline = $active && $member->is_online;
                                            $statusColor = $isOnline ? '#22c55e' : ($active ? '#f59e0b' : '#ef4444');
                                            $statusBg = $isOnline
                                                ? 'rgba(34,197,94,0.1)'
                                                : ($active
                                                    ? 'rgba(245,158,11,0.1)'
                                                    : 'rgba(239,68,68,0.1)');
                                            $statusBdr = $isOnline
                                                ? 'rgba(34,197,94,0.3)'
                                                : ($active
                                                    ? 'rgba(245,158,11,0.3)'
                                                    : 'rgba(239,68,68,0.3)');
                                            $statusLabel = $isOnline ? strtoupper(__('dashboard.staff.online')) : ($active ? strtoupper(__('dashboard.staff.offline')) : strtoupper(__('dashboard.staff.inactive')));
                                            $dotExtra = $isOnline ? 'animation:onlinePulse 2s infinite;' : '';
                                        @endphp
                                        <span
                                            style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;
                                            border-radius:6px;font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:1px;
                                            background:{{ $statusBg }};border:1px solid {{ $statusBdr }};color:{{ $statusColor }};">
                                            <span
                                                style="width:6px;height:6px;border-radius:50%;
                                                background:{{ $statusColor }};{{ $dotExtra }}"></span>
                                            {{ $statusLabel }}
                                        </span>
                                    </td>

                                    <td style="padding:16px 20px;text-align:right;white-space:nowrap;">
                                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px;">
                                            <form method="POST"
                                                action="{{ route('dashboard.staff.toggle', $member->id) }}"
                                                style="display:inline;">
                                                @csrf @method('PATCH')
                                                <button type="submit" title="{{ $active ? 'Deactivate' : 'Activate' }}"
                                                    style="width:32px;height:32px;border-radius:8px;cursor:pointer;
                                                           display:flex;align-items:center;justify-content:center;
                                                           transition:all .2s;font-size: var(--title-size);
                                                           border:1px solid {{ $active ? 'rgba(249,115,22,0.3)' : 'rgba(34,197,94,0.3)' }};
                                                           background:{{ $active ? 'rgba(249,115,22,0.08)' : 'rgba(34,197,94,0.08)' }};
                                                           color:{{ $active ? '#F97316' : '#22c55e' }};"
                                                    onmouseover="this.style.background='{{ $active ? 'rgba(249,115,22,0.2)' : 'rgba(34,197,94,0.2)' }}'"
                                                    onmouseout="this.style.background='{{ $active ? 'rgba(249,115,22,0.08)' : 'rgba(34,197,94,0.08)' }}'">
                                                    {{ $active ? '⏸' : '▶' }}
                                                </button>
                                            </form>
                                            <button
                                                onclick="confirmDelete({{ $member->id }}, '{{ addslashes($member->name) }}', 'staff')"
                                                title="Remove staff member"
                                                style="width:32px;height:32px;border-radius:8px;border:1px solid rgba(239,68,68,0.2);
                                               background:rgba(239,68,68,0.06);color:rgba(239,68,68,0.6);cursor:pointer;
                                               display:flex;align-items:center;justify-content:center;transition:all .2s;"
                                                onmouseover="this.style.background='rgba(239,68,68,0.15)';this.style.color='#ef4444'"
                                                onmouseout="this.style.background='rgba(239,68,68,0.06)';this.style.color='rgba(239,68,68,0.6)'">
                                                <svg width="14" height="14" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <polyline points="3 6 5 6 21 6" />
                                                    <path d="M19 6l-1 14H6L5 6" />
                                                    <path d="M10 11v6M14 11v6M9 6V4h6v2" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>{{-- end tab-staff --}}

    {{-- ══════════════════════════════════════════════════════════════════════════
     ADMINS TAB (superadmin only)
══════════════════════════════════════════════════════════════════════════ --}}
    @if ($isSuper)
        <div id="tab-admins" class="tab-panel" style="{{ $activeTab !== 'admins' ? 'display:none;' : '' }}">

            {{-- Role summary cards --}}
            <div
                style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;margin-bottom:24px;">
                @foreach ($adminRoleMeta as $rKey => $rMeta)
                    @php $count = $admins->where('role',$rKey)->count(); @endphp
                    <div class="role-card"
                        style="padding:12px 14px;border-radius:12px;background:var(--dark-700);
                    border:1px solid {{ $rMeta['color'] }}22;position:relative;overflow:hidden;">
                        <div
                            style="position:absolute;top:-14px;right:-8px;font-size: var(--title-size);opacity:0.06;pointer-events:none;">
                            {{ $rMeta['icon'] }}</div>
                        <div style="font-size: var(--title-size);margin-bottom:2px;">{{ $rMeta['icon'] }}</div>
                        <div style="font-size: var(--title-size);font-weight:{{ $_fw9 }};color:{{ $rMeta['color'] }};line-height:1.1;">
                            {{ $count }}</div>
                        <div
                            style="font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:1px;color:{{ $rMeta['color'] }};margin-top:2px;">
                            {{ strtoupper($rMeta['label']) }}</div>
                        <div style="font-size: var(--title-size);color:var(--text-muted);margin-top:3px;line-height:1.3;">
                            {{ $rMeta['desc'] }}</div>
                    </div>
                @endforeach
            </div>

            {{-- Filters --}}
            <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;align-items:center;">
                <div style="position:relative;flex:1;min-width:220px;">
                    <svg style="position:absolute;left:14px;top:50%;transform:translateY(-50%);opacity:0.35;"
                        width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input type="text" id="admin-search" placeholder="Search by name or email…"
                        oninput="filterRows('admins')"
                        style="width:100%;padding:10px 14px 10px 42px;border-radius:10px;background:var(--dark-700);
                          border:1px solid var(--border);color:var(--text-primary);font-family:Rajdhani, var(--font-kh), sans-serif;
                          font-size: var(--title-size);outline:none;transition:border-color .2s;"
                        onfocus="this.style.borderColor='rgba(249,115,22,0.4)'" onblur="this.style.borderColor=''" />
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <button class="admin-filter active" data-table="admins" data-role="all"
                        onclick="setAdminFilter(this,'all')"
                        style="padding:9px 16px;border-radius:8px;border:1px solid var(--border);
                           background:var(--dark-700);color:var(--text-primary);font-family:Rajdhani, var(--font-kh), sans-serif;
                           font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:1px;cursor:pointer;transition:all .2s;">{{ strtoupper(__('dashboard.staff.all')) }}</button>
                    @foreach ($adminRoleMeta as $rKey => $rMeta)
                        <button class="admin-filter" data-table="admins" data-role="{{ $rKey }}"
                            onclick="setAdminFilter(this,'{{ $rKey }}')"
                            style="padding:9px 16px;border-radius:8px;border:1px solid {{ $rMeta['color'] }}30;
                           background:{{ $rMeta['color'] }}0d;color:{{ $rMeta['color'] }};font-family:Rajdhani, var(--font-kh), sans-serif;
                           font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:1px;cursor:pointer;transition:all .2s;">
                            {{ $rMeta['icon'] }} {{ strtoupper($rMeta['label']) }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Admins table --}}
            <div class="card">
                <div class="card-header">
                    <div class="card-title">ADMIN MEMBERS</div>
                    <div style="font-size: var(--title-size);color:var(--text-muted);">{{ $admins->count() }}
                        member{{ $admins->count() !== 1 ? 's' : '' }}</div>
                </div>

                @if ($admins->isEmpty())
                    <div style="padding:64px 20px;text-align:center;color:var(--text-muted);">
                        <div style="font-size: var(--title-size);margin-bottom:12px;">🛡️</div>
                        <div style="font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:2px;">NO ADMINS</div>
                    </div>
                @else
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;" id="admin-table">
                            <thead>
                                <tr style="border-bottom:1px solid var(--border);">
                                    <th
                                        style="padding:14px 20px;text-align:left;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">
                                        {{ strtoupper(__('dashboard.staff.staffMembers')) }}</th>
                                    <th class="col-email"
                                        style="padding:14px 14px;text-align:left;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">
                                        {{ strtoupper(__('dashboard.table.email')) }}</th>
                                    <th
                                        style="padding:14px 14px;text-align:center;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">
                                        {{ strtoupper(__('dashboard.table.role')) }}</th>
                                    <th class="col-joined"
                                        style="padding:14px 14px;text-align:center;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">
                                        {{ strtoupper(__('dashboard.staff.memberSince')) }}</th>
                                    <th class="col-status"
                                        style="padding:14px 14px;text-align:center;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">
                                        {{ strtoupper(__('dashboard.table.status')) }}</th>
                                    <th
                                        style="padding:14px 20px;text-align:right;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">
                                        {{ strtoupper(__('dashboard.table.actions')) }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($admins as $member)
                                    @php
                                        $mRole = $member->role ?? 'admin';
                                        $mMeta = $adminRoleMeta[$mRole] ?? $adminRoleMeta['admin'];
                                        $isSelf = $member->id === $me->id;
                                        $canEdit = $isSuper && !$isSelf;
                                        $active = ($member->is_active ?? true);
                                        $isOnline = $active && $member->is_online;
                                        $initials = strtoupper(
                                            substr($member->name ?? '?', 0, 1) .
                                                (strpos($member->name, ' ') !== false
                                                    ? substr($member->name, strpos($member->name, ' ') + 1, 1)
                                                    : ''),
                                        );
                                        $memberAvatar = $member->avatar
                                            ? (Str::startsWith($member->avatar, ['http://', 'https://'])
                                                ? $member->avatar
                                                : asset('storage/' . $member->avatar))
                                            : null;
                                    @endphp
                                    <tr class="admin-row" data-role="{{ $mRole }}"
                                        data-name="{{ strtolower($member->name ?? '') }}"
                                        data-email="{{ strtolower($member->email ?? '') }}"
                                        style="border-bottom:1px solid var(--border);transition:background .15s;"
                                        onmouseover="this.style.background='rgba(15,23,42,0.02)'"
                                        onmouseout="this.style.background=''">

                                        <td style="padding:16px 20px;white-space:nowrap;">
                                            <div style="display:flex;align-items:center;gap:12px;">
                                                <div
                                                    style="width:40px;height:40px;border-radius:12px;flex-shrink:0;overflow:hidden;
                                                background:{{ $mMeta['color'] }}18;border:1.5px solid {{ $mMeta['color'] }}44;
                                                display:flex;align-items:center;justify-content:center;">
                                                    @if ($memberAvatar)
                                                        <img src="{{ $memberAvatar }}" alt="{{ $member->name }}"
                                                            style="width:100%;height:100%;object-fit:cover;"
                                                            onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
                                                        <span
                                                            style="display:none;width:100%;height:100%;align-items:center;justify-content:center;
                                                         font-size: var(--title-size);font-weight:{{ $_fw8 }};color:{{ $mMeta['color'] }};">{{ $initials }}</span>
                                                    @else
                                                        <span
                                                            style="font-size: var(--title-size);font-weight:{{ $_fw8 }};color:{{ $mMeta['color'] }};">{{ $initials }}</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div style="font-size: var(--title-size);font-weight:{{ $_fw7 }};color:var(--text-primary);cursor:pointer;"
                                                        onclick="openMemberProfile({{ $member->id }}, @js($member->name ?? ''), @js($member->email ?? ''), @js($memberAvatar ?? ''), '{{ $mRole }}', @js($mMeta['color']), @js($mMeta['icon']), @js($mMeta['label']), '{{ $member->created_at ? $member->created_at->format('d M Y') : '—' }}', {{ $active ? 'true' : 'false' }}, {{ $isOnline ? 'true' : 'false' }}, {{ $isSelf ? 'true' : 'false' }})"
                                                        onmouseover="this.style.color='#F97316'"
                                                        onmouseout="this.style.color=''">
                                                        {{ $member->name }}
                                                        @if ($isSelf)
                                                            <span
                                                                style="font-size: var(--title-size);color:rgba(255,255,255,0.3);font-weight:{{ $_fw6 }};
                                                     background:rgba(255,255,255,0.06);border-radius:4px;
                                                     padding:1px 6px;margin-left:4px;letter-spacing:1px;">{{ strtoupper(__('dashboard.staff.youBadge')) }}</span>
                                                        @endif
                                                    </div>
                                                    <div style="font-size: var(--title-size);color:var(--text-muted);margin-top:2px;">ID
                                                        #{{ $member->id }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="col-email"
                                            style="padding:16px 14px;font-size: var(--title-size);color:var(--text-secondary);">
                                            {{ $member->email }}</td>

                                        <td style="padding:16px 14px;text-align:center;">
                                            @if ($canEdit)
                                                <form method="POST"
                                                    action="{{ route('dashboard.admin.role', $member->id) }}"
                                                    style="display:inline-block;" onchange="this.submit()">
                                                    @csrf @method('PATCH')
                                                    <select name="role"
                                                        style="padding:5px 10px;border-radius:8px;cursor:pointer;
                                               background:{{ $mMeta['color'] }}18;border:1.5px solid {{ $mMeta['color'] }}44;
                                               color:{{ $mMeta['color'] }};font-family:Rajdhani, var(--font-kh), sans-serif;
                                               font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:1px;outline:none;">
                                                        @foreach ($adminRoles as $rOpt)
                                                            <option value="{{ $rOpt }}"
                                                                {{ $mRole === $rOpt ? 'selected' : '' }}
                                                                style="background:var(--dark-900);color:var(--text-primary);">
                                                                {{ $adminRoleMeta[$rOpt]['icon'] }}
                                                                {{ strtoupper($adminRoleMeta[$rOpt]['label']) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            @else
                                                <span
                                                    style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;
                                         border-radius:8px;background:{{ $mMeta['color'] }}18;
                                         border:1.5px solid {{ $mMeta['color'] }}44;
                                         color:{{ $mMeta['color'] }};font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:1px;">
                                                    {{ $mMeta['icon'] }} {{ strtoupper($mMeta['label']) }}
                                                </span>
                                            @endif
                                        </td>

                                        <td class="col-joined"
                                            style="padding:16px 14px;text-align:center;font-size: var(--title-size);color:var(--text-muted);">
                                            {{ $member->created_at ? $member->created_at->format('d M Y') : '—' }}
                                        </td>

                                        <td class="col-status" style="padding:16px 14px;text-align:center;">
                                            @php
                                                $active      = ($member->is_active ?? true);
                                                $isOnline    = $active && $member->is_online;
                                                $statusColor = $isOnline ? '#22c55e' : ($active ? '#f59e0b' : '#ef4444');
                                                $statusBg    = $isOnline ? 'rgba(34,197,94,0.1)'  : ($active ? 'rgba(245,158,11,0.1)'  : 'rgba(239,68,68,0.1)');
                                                $statusBdr   = $isOnline ? 'rgba(34,197,94,0.3)'  : ($active ? 'rgba(245,158,11,0.3)'  : 'rgba(239,68,68,0.3)');
                                                $statusLabel = $isOnline ? 'ONLINE'                : ($active ? 'OFFLINE'               : 'INACTIVE');
                                                $dotExtra    = $isOnline ? 'animation:onlinePulse 2s infinite;' : '';
                                            @endphp
                                            <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;
                                                         border-radius:6px;font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:1px;
                                                         background:{{ $statusBg }};border:1px solid {{ $statusBdr }};color:{{ $statusColor }};">
                                                <span style="width:6px;height:6px;border-radius:50%;
                                                             background:{{ $statusColor }};{{ $dotExtra }}"></span>
                                                {{ $statusLabel }}
                                            </span>
                                        </td>

                                        <td style="padding:16px 20px;text-align:right;white-space:nowrap;">
                                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px;">
                                                @if ($canEdit)
                                                    <form method="POST"
                                                        action="{{ route('dashboard.admin.toggle', $member->id) }}"
                                                        style="display:inline;">
                                                        @csrf @method('PATCH')
                                                        <button type="submit"
                                                            title="{{ $active ? 'Deactivate' : 'Activate' }}"
                                                            style="width:32px;height:32px;border-radius:8px;cursor:pointer;
                                                            display:flex;align-items:center;justify-content:center;
                                                            transition:all .2s;font-size: var(--title-size);
                                                            border:1px solid {{ $active ? 'rgba(249,115,22,0.3)' : 'rgba(34,197,94,0.3)' }};
                                                            background:{{ $active ? 'rgba(249,115,22,0.08)' : 'rgba(34,197,94,0.08)' }};
                                                            color:{{ $active ? '#F97316' : '#22c55e' }};"
                                                            onmouseover="this.style.background='{{ $active ? 'rgba(249,115,22,0.2)' : 'rgba(34,197,94,0.2)' }}'"
                                                            onmouseout="this.style.background='{{ $active ? 'rgba(249,115,22,0.08)' : 'rgba(34,197,94,0.08)' }}'">
                                                            {{ $active ? '⏸' : '▶' }}
                                                        </button>
                                                    </form>
                                                    <button
                                                        onclick="confirmDelete({{ $member->id }}, '{{ addslashes($member->name) }}', 'admin')"
                                                        title="Remove admin"
                                                        style="width:32px;height:32px;border-radius:8px;border:1px solid rgba(239,68,68,0.2);
                                               background:rgba(239,68,68,0.06);color:rgba(239,68,68,0.6);cursor:pointer;
                                               display:flex;align-items:center;justify-content:center;transition:all .2s;"
                                                        onmouseover="this.style.background='rgba(239,68,68,0.15)';this.style.color='#ef4444'"
                                                        onmouseout="this.style.background='rgba(239,68,68,0.06)';this.style.color='rgba(239,68,68,0.6)'">
                                                        <svg width="14" height="14" fill="none"
                                                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <polyline points="3 6 5 6 21 6" />
                                                            <path d="M19 6l-1 14H6L5 6" />
                                                            <path d="M10 11v6M14 11v6M9 6V4h6v2" />
                                                        </svg>
                                                    </button>
                                                @else
                                                    <span
                                                        style="font-size: var(--title-size);color:var(--text-muted);font-style:italic;padding:0 4px;">
                                                        @if ($isSelf)
                                                            (your account)
                                                        @else(protected)
                                                        @endif
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
        </div>{{-- end tab-admins --}}
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════════
     INVITE MODAL — redesigned partial
══════════════════════════════════════════════════════════════════════════ --}}
    @include('components.staff-invite-modal', ['activeTab' => $activeTab, 'isSuper' => $isSuper, 'staffRoleMeta' => $staffRoleMeta, 'adminRoleMeta' => $adminRoleMeta])

    {{-- ── Delete confirm modal ─────────────────────────────────────────────────── --}}
    <div id="del-modal"
        style="display:none;position:fixed;inset:0;z-index:9100;
    align-items:center;justify-content:center;padding:20px;
    background:rgba(0,0,0,0.8);backdrop-filter:blur(8px);">
        <div
            style="width:100%;max-width:380px;border-radius:18px;padding:32px;
                background:var(--dark-800);border:1px solid rgba(239,68,68,0.25);
                box-shadow:0 32px 80px rgba(0,0,0,0.7);text-align:center;
                font-family:Rajdhani, var(--font-kh), sans-serif;animation:stModalIn .3s cubic-bezier(0.34,1.2,0.64,1);">
            <div style="font-size: var(--title-size);margin-bottom:12px;">⚠️</div>
            <div style="font-size: var(--title-size);font-weight:{{ $_fw9 }};letter-spacing:2px;margin-bottom:8px;">{{ strtoupper(__('dashboard.staff.removeMember')) }}</div>
            <div style="font-size: var(--title-size);color:var(--text-muted);margin-bottom:24px;">
                {{ __('dashboard.staff.confirmRemove') }}
            </div>
            <form method="POST" id="del-form">
                @csrf @method('DELETE')
                <div style="display:flex;gap:10px;justify-content:center;">
                    <button type="button" onclick="closeDelModal()"
                        style="flex:1;padding:10px;border-radius:9px;border:1px solid var(--border);
                               background:transparent;color:var(--text-muted);font-family:Rajdhani, var(--font-kh), sans-serif;
                               font-size: var(--title-size);font-weight:{{ $_fw7 }};cursor:pointer;"
                        onmouseover="this.style.color='var(--text-primary)'"
                        onmouseout="this.style.color='var(--text-muted)'">CANCEL</button>
                    <button type="submit"
                        style="flex:1;padding:10px;border-radius:9px;border:none;
                               background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;
                               font-family:Rajdhani, var(--font-kh), sans-serif;font-size: var(--title-size);font-weight:{{ $_fw8 }};cursor:pointer;">
                        {{ strtoupper(__('dashboard.staff.yesRemove')) }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Member Profile Modal ────────────────────────────────────────────────── --}}
    <div id="member-profile-modal"
        style="display:none;position:fixed;inset:0;z-index:9999;
    background:rgba(0,0,0,0.75);backdrop-filter:blur(6px);
    align-items:center;justify-content:center;padding:16px;">
        <div
            style="width:100%;max-width:380px;border-radius:20px;overflow:hidden;
                background:var(--dark-800);border:1px solid var(--border);
                box-shadow:0 32px 80px rgba(0,0,0,0.7);font-family:Rajdhani, var(--font-kh), sans-serif;
                animation:spModalIn .3s cubic-bezier(0.34,1.2,0.64,1);">
            <div id="mp-header-bar" style="height:4px;background:linear-gradient(90deg,#F97316,#fb923c);"></div>
            <div style="padding:20px 20px 0;display:flex;justify-content:space-between;align-items:center;">
                <div style="font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:2px;color:var(--text-muted);">MEMBER PROFILE
                </div>
                <button onclick="closeMemberProfile()"
                    style="width:30px;height:30px;border-radius:8px;background:rgba(255,255,255,0.06);
                       border:1px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.4);
                       font-size: var(--title-size);cursor:pointer;display:flex;align-items:center;justify-content:center;"
                    onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">✕</button>
            </div>
            <div style="padding:16px 20px;display:flex;align-items:center;gap:14px;">
                <div id="mp-avatar"
                    style="width:64px;height:64px;border-radius:16px;flex-shrink:0;overflow:hidden;
                border:2px solid #F97316;box-shadow:0 0 0 3px rgba(249,115,22,0.15);">
                </div>
                <div>
                    <div id="mp-name"
                        style="font-size: var(--title-size);font-weight:{{ $_fw9 }};color:var(--text-primary);letter-spacing:1px;"></div>
                    <div id="mp-email" style="font-size: var(--title-size);color:var(--text-muted);margin-top:2px;"></div>
                    <div id="mp-role-badge" style="margin-top:8px;"></div>
                </div>
            </div>
            <div style="padding:0 20px;display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div style="background:var(--dark-700);border:1px solid var(--border);border-radius:10px;padding:12px;">
                    <div
                        style="font-size: var(--title-size);color:var(--text-muted);letter-spacing:2px;font-weight:{{ $_fw7 }};margin-bottom:4px;">
                        {{ strtoupper(__('dashboard.staff.memberSince')) }}</div>
                    <div id="mp-joined" style="font-size: var(--title-size);color:var(--text-primary);font-weight:{{ $_fw7 }};"></div>
                </div>
                <div style="background:var(--dark-700);border:1px solid var(--border);border-radius:10px;padding:12px;">
                    <div
                        style="font-size: var(--title-size);color:var(--text-muted);letter-spacing:2px;font-weight:{{ $_fw7 }};margin-bottom:4px;">
                        {{ strtoupper(__('dashboard.table.status')) }}</div>
                    <div id="mp-status" style="font-size: var(--title-size);font-weight:{{ $_fw7 }};"></div>
                </div>
            </div>
            <div id="mp-self-note"
                style="display:none;margin:10px 20px 0;padding:10px 14px;border-radius:10px;
            background:rgba(249,115,22,0.08);border:1px solid rgba(249,115,22,0.2);
            font-size: var(--title-size);color:#F97316;font-weight:{{ $_fw7 }};text-align:center;letter-spacing:1px;">
                👤 {{ strtoupper(__('dashboard.staff.thisIsYou')) }}
            </div>
            <div style="padding:16px 20px 20px;margin-top:4px;">
                <a id="mp-email-btn" href="#"
                    style="display:block;text-align:center;padding:10px;border-radius:10px;
                       background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);
                       color:rgba(255,255,255,0.5);font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:1px;
                       text-decoration:none;transition:all .2s;"
                    onmouseover="this.style.borderColor='#F97316';this.style.color='#F97316'"
                    onmouseout="this.style.borderColor='rgba(255,255,255,0.1)';this.style.color='rgba(255,255,255,0.5)'">
                    ✉ {{ strtoupper(__('dashboard.table.email')) }}
                </a>
            </div>
        </div>
    </div>

    {{-- ── Scripts ──────────────────────────────────────────────────────────────── --}}
    <script>
        // ── Tab switching ─────────────────────────────────────────────────────────────
        var currentTab = '{{ $activeTab }}';

        // Store routes in JS vars — avoids Blade interpolation inside dynamic strings
        var routeStaffInvite = '{{ route('dashboard.staff.invite') }}';
        var routeAdminInvite = '{{ route('dashboard.admin.invite') }}';

        function switchTab(tab) {
            currentTab = tab;
            document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
            document.getElementById('tab-' + tab).style.display = '';
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('tab-active'));
            document.querySelectorAll('.tab-btn').forEach(b => {
                if (b.textContent.toLowerCase().includes(tab === 'staff' ? 'staff' : 'admin')) b.classList.add(
                    'tab-active');
            });
            // Update invite button label
            document.getElementById('invite-btn-label').textContent = tab === 'admins'
              ? '{{ strtoupper(__("dashboard.staff.admin")) }}'
              : '{{ strtoupper(__("dashboard.staff.inviteStaff")) }}';
        }

        // ── Toast ─────────────────────────────────────────────────────────────────────
        function dismissToast(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.style.animation = 'fadeOutRight .3s ease forwards';
            setTimeout(() => el?.remove(), 300);
        }

        // ── Invite modal — form logic lives inside the partial (Alpine.js) ─────────────
        function openInviteModal() {
            switchTab(currentTab); // sync visual tab state before opening
            const m = document.getElementById('invite-modal');
            if (m) { m.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
            setTimeout(() => m?.querySelector('input[name=name]')?.focus(), 100);
        }

        function closeInviteModal() {
            const m = document.getElementById('invite-modal');
            if (m) { m.style.display = 'none'; document.body.style.overflow = ''; }
        }
        document.getElementById('invite-modal').addEventListener('click', function(e) {
            if (e.target === this) closeInviteModal();
        });

        // ── Delete modal ──────────────────────────────────────────────────────────────
        function confirmDelete(id, name, type) {
            document.getElementById('del-name').textContent = name;
            const base = type === 'admin' ? '/dashboard/admin/' : '/dashboard/staff/';
            document.getElementById('del-form').action = base + id;
            document.getElementById('del-modal').style.display = 'flex';
        }

        function closeDelModal() {
            document.getElementById('del-modal').style.display = 'none';
        }
        document.getElementById('del-modal').addEventListener('click', function(e) {
            if (e.target === this) closeDelModal();
        });

        // ── Password visibility ───────────────────────────────────────────────────────
        function togglePassVis(id, btn) {
            const inp = document.getElementById(id);
            inp.type = inp.type === 'password' ? 'text' : 'password';
            btn.textContent = inp.type === 'password' ? '👁' : '🙈';
        }

        // ── Filters ───────────────────────────────────────────────────────────────────
        var activeStaffRole = 'all';
        var activeAdminRole = 'all';

        function filterRows(table) {
            const q = document.getElementById(table + '-search').value.toLowerCase();
            const activeRole = table === 'staff' ? activeStaffRole : activeAdminRole;
            document.querySelectorAll('.' + table + '-row').forEach(row => {
                const match = (!q || row.dataset.name.includes(q) || row.dataset.email.includes(q) ||
                    (row.dataset.phone || '').includes(q)) &&
                    (activeRole === 'all' || row.dataset.role === activeRole);
                row.style.display = match ? '' : 'none';
            });
        }

        function setRoleFilter(btn, table, role) {
            activeStaffRole = role;
            document.querySelectorAll('.role-filter[data-table="staff"]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            filterRows('staff');
        }

        function setAdminFilter(btn, role) {
            activeAdminRole = role;
            document.querySelectorAll('.admin-filter').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            filterRows('admins');
        }

        // ── Profile modal ─────────────────────────────────────────────────────────────
        function openMemberProfile(id, name, email, avatar, role, roleColor, roleIcon, roleLabel, joined, active, isOnline, isSelf) {
            const modal = document.getElementById('member-profile-modal');
            modal.style.display = 'flex';
            document.getElementById('mp-header-bar').style.background =
                `linear-gradient(90deg, ${roleColor}, ${roleColor}88)`;
            const avatarEl = document.getElementById('mp-avatar');
            avatarEl.style.borderColor = roleColor;
            avatarEl.style.boxShadow = `0 0 0 3px ${roleColor}22`;
            const initial = (name || '?').charAt(0).toUpperCase();
            avatarEl.innerHTML = avatar ?
                `<img src="${avatar}" alt="${name}" style="width:100%;height:100%;object-fit:cover;display:block;"
               onerror="this.style.display='none';this.nextSibling.style.display='flex'" />
           <div style="display:none;width:100%;height:100%;background:linear-gradient(135deg,${roleColor},${roleColor}88);
               align-items:center;justify-content:center;font-weight:{{ $_fw9 }};font-size: var(--title-size);color:#fff;">${initial}</div>` :
                `<div style="width:100%;height:100%;background:linear-gradient(135deg,${roleColor},${roleColor}88);
               display:flex;align-items:center;justify-content:center;font-weight:{{ $_fw9 }};font-size: var(--title-size);color:#fff;">${initial}</div>`;
            document.getElementById('mp-name').textContent = name || '—';
            document.getElementById('mp-email').textContent = email || '—';
            document.getElementById('mp-joined').textContent = joined;

            const statusColor = isOnline ? '#22c55e' : (active ? '#f59e0b' : '#ef4444');
            const statusLabel = isOnline ? 'ONLINE' : (active ? 'OFFLINE' : 'INACTIVE');
            document.getElementById('mp-status').innerHTML = `<span style="color:${statusColor};">● ${statusLabel}</span>`;

            document.getElementById('mp-role-badge').innerHTML =
                `<span style="display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:20px;
            font-size: var(--title-size);font-weight:{{ $_fw8 }};letter-spacing:1px;
            background:${roleColor}18;color:${roleColor};border:1px solid ${roleColor}44;">
            ${roleIcon} ${roleLabel.toUpperCase()}
        </span>`;
            document.getElementById('mp-self-note').style.display = isSelf ? 'block' : 'none';
            document.getElementById('mp-email-btn').href = `mailto:${email}`;
        }

        function closeMemberProfile() {
            document.getElementById('member-profile-modal').style.display = 'none';
        }
        document.getElementById('member-profile-modal').addEventListener('click', function(e) {
            if (e.target === this) closeMemberProfile();
        });

            // ── Invite links expand/collapse & copy ─────────────────────────────────
        function toggleLinks(btn, rowId) {
            const row = document.getElementById(rowId);
            const open = row.style.display !== 'none';
            row.style.display = open ? 'none' : '';
            btn.textContent = open ? 'LINKS ▾' : 'LINKS ▴';
        }

        function copyText(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                const orig = btn.textContent;
                btn.textContent = 'COPIED ✓';
                btn.style.background = 'rgba(34,197,94,0.15)';
                btn.style.color = '#22c55e';
                btn.style.borderColor = 'rgba(34,197,94,0.4)';
                setTimeout(() => {
                    btn.textContent = orig;
                    btn.style.background = '';
                    btn.style.color = '';
                    btn.style.borderColor = '';
                }, 2000);
            }).catch(() => {
                const ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                const orig = btn.textContent;
                btn.textContent = 'COPIED ✓';
                setTimeout(() => { btn.textContent = orig; }, 2000);
            });
        }

        function generateNewLink(form, event) {
            // The form submit already triggers; just return true.
            // We use onsubmit to allow any future async handling.
            return true;
        }

    document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                closeInviteModal();
                closeDelModal();
                closeMemberProfile();
            }
        });
    </script>

    <style>
        /* ── Tab buttons ─────────────────────────────────────────────────────────── */
        .tab-btn {
            background: transparent;
            color: var(--text-muted);
        }

        .tab-btn.tab-active {
            background: var(--dark-900);
            color: #F97316;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .tab-btn:hover:not(.tab-active) {
            color: var(--text-primary);
        }

        /* ── Animations ──────────────────────────────────────────────────────────── */
        @keyframes stToastIn {
            from {
                opacity: 0;
                transform: translateX(30px) scale(.95)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        @keyframes stModalIn {
            from {
                opacity: 0;
                transform: scale(.92)
            }

            to {
                opacity: 1;
                transform: scale(1)
            }
        }

        @keyframes spModalIn {
            from {
                opacity: 0;
                transform: scale(.93) translateY(16px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        @keyframes popIn {
            0% {
                transform: scale(0) rotate(-10deg)
            }

            60% {
                transform: scale(1.2) rotate(3deg)
            }

            100% {
                transform: scale(1) rotate(0)
            }
        }

        @keyframes toastBar {
            from {
                width: 100%
            }

            to {
                width: 0%
            }
        }

        @keyframes fadeOutRight {
            to {
                opacity: 0;
                transform: translateX(30px) scale(.95)
            }
        }

        /* ── Role filters ────────────────────────────────────────────────────────── */
        .role-filter {
            transition: all .2s;
        }

        .role-filter.active {
            background: rgba(249, 115, 22, 0.15) !important;
            border-color: rgba(249, 115, 22, 0.4) !important;
            color: #F97316 !important;
        }

        .admin-filter {
            transition: all .2s;
        }

        .admin-filter.active {
            background: rgba(249, 115, 22, 0.15) !important;
            border-color: rgba(249, 115, 22, 0.4) !important;
            color: #F97316 !important;
        }

        /* ── Responsive ──────────────────────────────────────────────────────────── */
        @media(max-width:900px) {
            .role-summary-grid {
                grid-template-columns: repeat(3, 1fr) !important;
            }
        }

        @media(max-width:600px) {
            .role-summary-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 8px !important;
            }
        }


        @media(max-width:600px) {
            div[style*="align-items:flex-start;justify-content:space-between"] {
                flex-direction: column !important;
                align-items: stretch !important;
            }

            div[style*="align-items:flex-start;justify-content:space-between"]>button {
                width: 100% !important;
                justify-content: center !important;
            }
        }

        @media(max-width:480px) {

            #page-toast,
            #page-err {
                top: auto !important;
                bottom: 16px !important;
                right: 12px !important;
                left: 12px !important;
            }

            .role-card-desc {
                display: none !important;
            }

            .role-card {
                padding: 10px 10px !important;
            }
        }

        @media(max-width:540px) {
            #invite-modal>div {
                max-width: 100% !important;
                border-radius: 16px !important;
            }
        }

        /* ── Invite links detail rows ───────────────────────────────────────────── */
        #invite-table td[colspan] {
            padding: 8px 16px 12px !important;
        }

        /* ── Role cards: 2x2 grid stays 2x2 on all screen sizes ────────────────── */

        /* ── Light theme ─────────────────────────────────────────────────────────── */
        [data-theme="light"] #invite-modal>div,
        [data-theme="light"] #member-profile-modal>div {
            background: #FFFFFF !important;
            border-color: rgba(15, 23, 42, 0.10) !important;
        }

        [data-theme="light"] #invite-modal input,
        [data-theme="light"] #invite-modal select {
            background: #F8FAFC !important;
            border-color: rgba(15, 23, 42, 0.12) !important;
            color: #0F172A !important;
        }

        [data-theme="light"] .role-filter {
            border-color: rgba(15, 23, 42, 0.12) !important;
            color: rgba(15, 23, 42, 0.55) !important;
            background: transparent !important;
        }

        [data-theme="light"] .role-filter.active,
        [data-theme="light"] .admin-filter.active {
            background: rgba(249, 115, 22, 0.08) !important;
            border-color: rgba(249, 115, 22, 0.4) !important;
            color: #F97316 !important;
        }

        [data-theme="light"] #staff-search,
        [data-theme="light"] #admin-search {
            background: #F8FAFC !important;
            border-color: rgba(15, 23, 42, 0.12) !important;
            color: #0F172A !important;
        }

        [data-theme="light"] .role-card {
            background: #f9fafb !important;
        }

        [data-theme="light"] .tab-btn.tab-active {
            background: #F8FAFC !important;
        }

        [data-theme="light"] #page-toast,
        [data-theme="light"] #page-err {
            background: #fff !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1) !important;
        }
    </style>

@endif {{-- $_permDenied --}}

@endsection

@push('styles')
    <style>
        [data-theme="light"] #invite-modal [style*="background:rgba(255,255,255,0.03)"],
        [data-theme="light"] #member-profile-modal [style*="background:rgba(255,255,255,0.03)"] {
            background: rgba(15, 23, 42, 0.025) !important;
        }

        [data-theme="light"] #invite-modal [style*="color:rgba(255,255,255,0.4)"],
        [data-theme="light"] #member-profile-modal [style*="color:rgba(255,255,255,0.4)"] {
            color: rgba(15, 23, 42, 0.45) !important;
        }

        [data-theme="light"] #invite-modal [style*="color:rgba(255,255,255,0.3)"],
        [data-theme="light"] #member-profile-modal [style*="color:rgba(255,255,255,0.3)"] {
            color: rgba(15, 23, 42, 0.35) !important;
        }

        [data-theme="light"] .staff-stat-label {
            color: rgba(15, 23, 42, 0.40) !important;
        }

        [data-theme="light"] #staff-table tr:hover,
        [data-theme="light"] #admin-table tr:hover {
            background: #f3f4f6 !important;
        }
    </style>
@endpush
