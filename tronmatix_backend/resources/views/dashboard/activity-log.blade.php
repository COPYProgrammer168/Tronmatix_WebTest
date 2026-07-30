@extends('dashboard.layout')
@section('title', strtoupper(__('dashboard.nav.activityLog')))

@section('content')

@include('dashboard._permission_check', ['feature' => 'activity_log'])
@php $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false; @endphp

@if(!$_permDenied)

{{-- Page Header --}}
<div style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:20px;">
    <div style="display:flex; align-items:center; gap:14px;">
        <div style="width:48px; height:48px; border-radius:14px; background:rgba(249,115,22,0.12);
                    border:1px solid rgba(249,115,22,0.3); display:flex; align-items:center;
                    justify-content:center; font-size: var(--title-size);">📋</div>
        <div>
            <div style="font-size: var(--title-size); font-weight:900; letter-spacing:3px;">
                {{ strtoupper(__('dashboard.nav.activityLog')) }}
            </div>
            <div style="font-size: var(--title-size); color:var(--text-muted); margin-top:2px;">
                {{ __('dashboard.activityLog.pageDesc') }}
            </div>
        </div>
    </div>
    <a href="{{ route('dashboard.index') }}"
       style="display:inline-flex; align-items:center; gap:6px; padding:9px 18px;
              border-radius:9px; border:1px solid rgba(255,255,255,0.1);
              background:rgba(255,255,255,0.04); color:rgba(255,255,255,0.5);
              font-family: inherit; font-size: var(--title-size); font-weight:700;
              letter-spacing:1px; text-decoration:none; transition:all .2s;"
       onmouseover="this.style.color='var(--text-primary)'"
       onmouseout="this.style.color='rgba(255,255,255,0.5)'">
        ← BACK TO DASHBOARD
    </a>
</div>

{{-- Filters Card --}}
<div class="card" style="margin-bottom:20px; border-color:rgba(249,115,22,0.15);">
    <div class="card-header" style="padding:14px 20px;">
        <span class="card-title" style="font-size: var(--title-size);">🔍 {{ __('dashboard.activityLog.filters') ?? 'FILTERS' }}</span>
    </div>
    <div style="padding: 0 20px 18px;">
        <form method="GET" action="{{ route('dashboard.activity-logs') }}" style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">

            <div style="flex:1; min-width:180px;">
                <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:4px; font-weight:600; letter-spacing:0.5px;">
                    {{ __('dashboard.activityLog.action') ?? 'ACTION' }}
                </label>
                <select name="action" class="input" style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-primary); font-size:14px;">
                    <option value="">{{ __('dashboard.activityLog.allActions') ?? 'All Actions' }}</option>
                    @foreach($actions as $a)
                        <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>

            <div style="flex:1; min-width:180px;">
                <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:4px; font-weight:600; letter-spacing:0.5px;">
                    {{ __('dashboard.activityLog.entity') ?? 'ENTITY TYPE' }}
                </label>
                <select name="entity_type" class="input" style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-primary); font-size:14px;">
                    <option value="">{{ __('dashboard.activityLog.allEntities') ?? 'All Entities' }}</option>
                    @foreach($entityTypes as $et)
                        <option value="{{ $et }}" {{ request('entity_type') === $et ? 'selected' : '' }}>{{ $et }}</option>
                    @endforeach
                </select>
            </div>

            <div style="flex:1; min-width:160px;">
                <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:4px; font-weight:600; letter-spacing:0.5px;">
                    {{ __('dashboard.activityLog.actor') ?? 'ACTOR' }}
                </label>
                <input type="text" name="actor_name" class="input" value="{{ request('actor_name') }}" placeholder="{{ __('dashboard.activityLog.actorPlaceholder') ?? 'Search actor...' }}" style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-primary); font-size:14px;">
            </div>

            <div style="min-width:130px;">
                <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:4px; font-weight:600; letter-spacing:0.5px;">
                    {{ __('dashboard.activityLog.from') ?? 'FROM' }}
                </label>
                <input type="date" name="date_from" class="input" value="{{ request('date_from') }}" style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-primary); font-size:14px;">
            </div>

            <div style="min-width:130px;">
                <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:4px; font-weight:600; letter-spacing:0.5px;">
                    {{ __('dashboard.activityLog.to') ?? 'TO' }}
                </label>
                <input type="date" name="date_to" class="input" value="{{ request('date_to') }}" style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-primary); font-size:14px;">
            </div>

            <div style="display:flex; gap:8px; align-items:flex-end; padding-bottom:1px;">
                <button type="submit" class="btn" style="padding:9px 18px; border-radius:8px; background:#F97316; color:#fff; font-weight:700; border:none; cursor:pointer; font-size:14px; letter-spacing:0.5px; white-space:nowrap;">
                    {{ __('dashboard.btn.search') ?? 'SEARCH' }}
                </button>
                <a href="{{ route('dashboard.activity-logs') }}" style="padding:9px 14px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:transparent; color:var(--text-muted); text-decoration:none; font-family: inherit; font-size:14px; font-weight:600; white-space:nowrap;">
                    {{ __('dashboard.btn.clearFilters') ?? 'CLEAR' }}
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Stats Row --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap:12px; margin-bottom:20px;">
    <div class="card" style="border-color:rgba(249,115,22,0.15); text-align:center; padding:16px;">
        <div style="font-size:11px; color:var(--text-muted); letter-spacing:1px; font-weight:700;">{{ __('dashboard.activityLog.totalLogs') ?? 'TOTAL LOGS' }}</div>
        <div style="font-size:28px; font-weight:900; color:#F97316; margin-top:4px;">{{ number_format($logs->total()) }}</div>
    </div>
    <div class="card" style="border-color:rgba(249,115,22,0.15); text-align:center; padding:16px;">
        <div style="font-size:11px; color:var(--text-muted); letter-spacing:1px; font-weight:700;">{{ __('dashboard.activityLog.thisPage') ?? 'THIS PAGE' }}</div>
        <div style="font-size:28px; font-weight:900; color:var(--text-primary); margin-top:4px;">{{ number_format($logs->count()) }}</div>
    </div>
    <div class="card" style="border-color:rgba(249,115,22,0.15); text-align:center; padding:16px;">
        <div style="font-size:11px; color:var(--text-muted); letter-spacing:1px; font-weight:700;">{{ __('dashboard.activityLog.lastPage') ?? 'PAGES' }}</div>
        <div style="font-size:28px; font-weight:900; color:var(--text-primary); margin-top:4px;">{{ $logs->lastPage() }}</div>
    </div>
    <div class="card" style="border-color:rgba(249,115,22,0.15); text-align:center; padding:16px;">
        <div style="font-size:11px; color:var(--text-muted); letter-spacing:1px; font-weight:700;">{{ __('dashboard.activityLog.perPage') ?? 'PER PAGE' }}</div>
        <div style="font-size:28px; font-weight:900; color:var(--text-primary); margin-top:4px;">{{ $logs->perPage() }}</div>
    </div>
</div>

{{-- Recent Alerts — Order Status Updates & Login Events --}}
@if($recentAlerts->count())
<div style="margin-bottom:20px;">
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
        <span style="font-size:16px;">🔔</span>
        <span style="font-size:13px; font-weight:700; color:var(--text-muted); letter-spacing:1px;">
            {{ __('dashboard.activityLog.recentAlerts') ?? 'RECENT ALERTS (LAST 24H)' }}
        </span>
        <span style="font-size:11px; padding:2px 10px; border-radius:9999px; background:rgba(249,115,22,0.12); color:#F97316; font-weight:700;">
            {{ $recentAlerts->count() }}
        </span>
    </div>
    <div style="display:flex; flex-direction:column; gap:8px;">
        @foreach($recentAlerts as $alert)
            @php
                $isOrder = $alert->action === 'order_status_update' || $alert->action === 'order_cancelled';
                $isLogin = $alert->action === 'login_success' || $alert->action === 'login_failed';
                $alertDetails = $alert->details ?: [];

                if ($alert->action === 'order_status_update') {
                    $old = $alertDetails['old_status'] ?? '?';
                    $new = $alertDetails['new_status'] ?? '?';
                    $icon = '📦';
                    $bgColor = 'rgba(99,102,241,0.08)';
                    $borderColor = 'rgba(99,102,241,0.25)';
                    $accentColor = '#6366f1';
                    $title = "Order #{$alert->entity_name}";
                    $desc = "Status changed: <strong style='color:var(--text-primary)'>" . ucwords(str_replace('_', ' ', $old)) . "</strong> → <strong style='color:{$accentColor}'>" . ucwords(str_replace('_', ' ', $new)) . "</strong>";
                } elseif ($alert->action === 'order_cancelled') {
                    $icon = '❌';
                    $bgColor = 'rgba(239,68,68,0.08)';
                    $borderColor = 'rgba(239,68,68,0.25)';
                    $accentColor = '#ef4444';
                    $title = "Order #{$alert->entity_name}";
                    $desc = "Order was <strong style='color:#ef4444'>cancelled</strong>";
                } elseif ($alert->action === 'login_success') {
                    $guard = $alertDetails['guard'] ?? '';
                    $icon = '✅';
                    $bgColor = 'rgba(16,185,129,0.08)';
                    $borderColor = 'rgba(16,185,129,0.25)';
                    $accentColor = '#10b981';
                    $title = $alert->actor_name ?: 'Login';
                    $roleLabel = $guard ? ucfirst($guard) : ($alert->actor_type ?? 'User');
                    $desc = "<strong style='color:#10b981'>Successful login</strong> as <span style='color:var(--text-primary);font-weight:600;'>{$roleLabel}</span>";
                } elseif ($alert->action === 'login_failed') {
                    $icon = '🚫';
                    $bgColor = 'rgba(239,68,68,0.08)';
                    $borderColor = 'rgba(239,68,68,0.25)';
                    $accentColor = '#ef4444';
                    $title = $alert->entity_name ?: 'Unknown';
                    $desc = "<strong style='color:#ef4444'>Failed login</strong> attempt" . (!empty($alertDetails['reason']) ? " ({$alertDetails['reason']})" : '');
                } elseif ($alert->action === 'payment_verified') {
                    $icon = '💳';
                    $bgColor = 'rgba(16,185,129,0.08)';
                    $borderColor = 'rgba(16,185,129,0.25)';
                    $accentColor = '#10b981';
                    $title = "Order #{$alert->entity_name}";
                    $desc = "<strong style='color:#10b981'>Payment verified</strong> — marked as paid";
                } elseif ($alert->action === 'delivery_confirmed') {
                    $by = $alertDetails['confirmed_by'] ?? '';
                    $icon = '📬';
                    $bgColor = 'rgba(16,185,129,0.08)';
                    $borderColor = 'rgba(16,185,129,0.25)';
                    $accentColor = '#10b981';
                    $title = "Order #{$alert->entity_name}";
                    $desc = "<strong style='color:#10b981'>Delivery confirmed</strong>" . ($by ? " by <span style='color:var(--text-primary);font-weight:600;'>{$by}</span>" : '');
                }
            @endphp
            <div style="display:flex; align-items:center; gap:12px; padding:10px 16px; border-radius:10px; background:{{ $bgColor }}; border:1px solid {{ $borderColor }}; transition:all .15s;"
                 onmouseover="this.style.borderColor='{{ $accentColor }}66'"
                 onmouseout="this.style.borderColor='{{ $borderColor }}'">
                <span style="font-size:18px;">{{ $icon }}</span>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:13px; font-weight:700; color:var(--text-primary);">{{ $title }}</div>
                    <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">{!! $desc !!}</div>
                </div>
                <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
                    <span style="font-size:11px; color:var(--text-muted);">{{ $alert->created_at->format('H:i') }}</span>
                    <span style="font-size:11px; padding:2px 8px; border-radius:6px; background:{{ $accentColor }}18; color:{{ $accentColor }}; font-weight:600; white-space:nowrap;">
                        {{ $alert->ip_address ?: '—' }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- Logs Table --}}
<div class="card" style="border-color:rgba(249,115,22,0.15); overflow:hidden;">
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-family: inherit; font-size:14px;">
            <thead>
                <tr style="background:rgba(249,115,22,0.06); border-bottom:1px solid rgba(249,115,22,0.15);">
                    <th style="text-align:left; padding:12px 16px; font-weight:700; color:var(--text-muted); letter-spacing:0.5px; white-space:nowrap;">{{ __('dashboard.table.date') ?? 'DATE' }}</th>
                    <th style="text-align:left; padding:12px 16px; font-weight:700; color:var(--text-muted); letter-spacing:0.5px; white-space:nowrap;">{{ __('dashboard.activityLog.actor') ?? 'ACTOR' }}</th>
                    <th style="text-align:left; padding:12px 16px; font-weight:700; color:var(--text-muted); letter-spacing:0.5px; white-space:nowrap;">{{ __('dashboard.activityLog.action') ?? 'ACTION' }}</th>
                    <th style="text-align:left; padding:12px 16px; font-weight:700; color:var(--text-muted); letter-spacing:0.5px; white-space:nowrap;">{{ __('dashboard.activityLog.entity') ?? 'ENTITY' }}</th>
                    <th style="text-align:left; padding:12px 16px; font-weight:700; color:var(--text-muted); letter-spacing:0.5px; white-space:nowrap;">{{ __('dashboard.table.details') ?? 'DETAILS' }}</th>
                    <th style="text-align:left; padding:12px 16px; font-weight:700; color:var(--text-muted); letter-spacing:0.5px; white-space:nowrap;">{{ __('dashboard.table.ip') ?? 'IP' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    @php
                        $actionColors = [
                            'login_success'        => '#10b981',
                            'login_failed'         => '#ef4444',
                            'login_rate_limited'   => '#f59e0b',
                            'product_create'       => '#3b82f6',
                            'product_update'       => '#6366f1',
                            'product_delete'       => '#ef4444',
                            'staff_invited'        => '#10b981',
                            'staff_role_changed'   => '#f59e0b',
                            'staff_activated'      => '#10b981',
                            'staff_deactivated'    => '#f59e0b',
                            'staff_deleted'        => '#ef4444',
                            'order_status_update'  => '#6366f1',
                            'payment_verified'     => '#10b981',
                            'delivery_confirmed'   => '#10b981',
                            'order_cancelled'      => '#ef4444',
                            'banner_created'       => '#3b82f6',
                            'banner_updated'       => '#6366f1',
                            'banner_deleted'       => '#ef4444',
                            'video_created'        => '#3b82f6',
                            'video_updated'        => '#6366f1',
                            'video_deleted'        => '#ef4444',
                            'discount_created'     => '#3b82f6',
                            'discount_updated'     => '#6366f1',
                            'discount_deleted'     => '#ef4444',
                            'settings_updated'     => '#6366f1',
                            'marquee_created'      => '#3b82f6',
                            'marquee_updated'      => '#6366f1',
                            'marquee_deleted'      => '#ef4444',
                        ];
                        $actionColor = $actionColors[$log->action] ?? '#9ca3af';
                        $actionLabel = ucwords(str_replace('_', ' ', $log->action));
                        $detailsJson = $log->details ? json_encode($log->details, JSON_UNESCAPED_UNICODE) : '';
                    @endphp
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.04); transition:background .15s;"
                        onmouseover="this.style.background='rgba(255,255,255,0.02)'"
                        onmouseout="this.style.background='transparent'">
                        <td style="padding:10px 16px; white-space:nowrap; color:var(--text-muted);">
                            {{ $log->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td style="padding:10px 16px;">
                            <div style="font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:6px;">
                                {{ $log->actor_name ?: '—' }}
                                @if($log->action === 'login_success')
                                    <span style="font-size:10px; padding:1px 7px; border-radius:9999px; background:rgba(16,185,129,0.12); color:#10b981; font-weight:700; letter-spacing:0.5px; border:1px solid rgba(16,185,129,0.2);">LOGIN</span>
                                @elseif($log->action === 'login_failed' || $log->action === 'login_rate_limited')
                                    <span style="font-size:10px; padding:1px 7px; border-radius:9999px; background:rgba(239,68,68,0.12); color:#ef4444; font-weight:700; letter-spacing:0.5px; border:1px solid rgba(239,68,68,0.2);">FAILED</span>
                                @elseif($log->action === 'order_status_update')
                                    <span style="font-size:10px; padding:1px 7px; border-radius:9999px; background:rgba(99,102,241,0.12); color:#6366f1; font-weight:700; letter-spacing:0.5px; border:1px solid rgba(99,102,241,0.2);">ORDER</span>
                                @elseif($log->action === 'order_cancelled')
                                    <span style="font-size:10px; padding:1px 7px; border-radius:9999px; background:rgba(239,68,68,0.12); color:#ef4444; font-weight:700; letter-spacing:0.5px; border:1px solid rgba(239,68,68,0.2);">CANCEL</span>
                                @endif
                            </div>
                            <div style="font-size:11px; color:var(--text-muted);">{{ $log->actor_type ?: 'System' }}</div>
                        </td>
                        <td style="padding:10px 16px;">
                            <span style="display:inline-block; padding:3px 10px; border-radius:9999px; font-size:12px; font-weight:700; background:{{ $actionColor }}22; color:{{ $actionColor }}; border:1px solid {{ $actionColor }}44;">
                                {{ $actionLabel }}
                            </span>
                        </td>
                        <td style="padding:10px 16px; white-space:nowrap;">
                            @if($log->entity_type)
                                <span style="font-weight:600; color:var(--text-primary);">{{ $log->entity_type }}</span>
                                @if($log->entity_name)
                                    <div style="font-size:12px; color:var(--text-muted);">{{ $log->entity_name }}</div>
                                @endif
                            @else
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td style="padding:10px 16px; max-width:260px;">
                            @php
                                $showDetailAlert = false;
                                $detailIcon = '';
                                $detailText = '';
                                $detailColor = 'var(--text-muted)';
                                if ($log->action === 'order_status_update' && isset($log->details['old_status'], $log->details['new_status'])) {
                                    $showDetailAlert = true;
                                    $detailIcon = '📦';
                                    $detailText = ucwords(str_replace('_', ' ', $log->details['old_status'])) . ' → ' . ucwords(str_replace('_', ' ', $log->details['new_status']));
                                    $detailColor = '#6366f1';
                                } elseif ($log->action === 'order_cancelled') {
                                    $showDetailAlert = true;
                                    $detailIcon = '❌';
                                    $detailText = 'Order Cancelled';
                                    $detailColor = '#ef4444';
                                } elseif ($log->action === 'login_success') {
                                    $showDetailAlert = true;
                                    $detailIcon = '✅';
                                    $guard = $log->details['guard'] ?? $log->actor_type ?? 'User';
                                    $detailText = 'Login — ' . ucfirst($guard);
                                    $detailColor = '#10b981';
                                } elseif ($log->action === 'login_failed') {
                                    $showDetailAlert = true;
                                    $detailIcon = '🚫';
                                    $reason = $log->details['reason'] ?? 'invalid_credentials';
                                    $detailText = 'Failed: ' . ucwords(str_replace('_', ' ', $reason));
                                    $detailColor = '#ef4444';
                                } elseif ($log->action === 'payment_verified') {
                                    $showDetailAlert = true;
                                    $detailIcon = '💳';
                                    $detailText = 'Payment verified';
                                    $detailColor = '#10b981';
                                } elseif ($log->action === 'delivery_confirmed') {
                                    $showDetailAlert = true;
                                    $detailIcon = '📬';
                                    $by = $log->details['confirmed_by'] ?? '';
                                    $detailText = 'Delivered' . ($by ? " by {$by}" : '');
                                    $detailColor = '#10b981';
                                }
                            @endphp
                            @if($showDetailAlert)
                                <div style="font-size:12px; color:{{ $detailColor }}; font-weight:600; display:flex; align-items:center; gap:4px;">
                                    <span>{{ $detailIcon }}</span>
                                    <span>{{ $detailText }}</span>
                                </div>
                            @elseif($detailsJson && $detailsJson !== '[]')
                                <div style="font-size:12px; color:var(--text-muted); word-break:break-all; max-height:60px; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $detailsJson }}
                                </div>
                            @else
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td style="padding:10px 16px; white-space:nowrap; color:var(--text-muted); font-size:12px;">
                            {{ $log->ip_address ?: '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:40px 16px; text-align:center; color:var(--text-muted);">
                            {{ __('dashboard.activityLog.noLogs') ?? 'No activity logs found.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
        <div style="padding:14px 20px; border-top:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
            <div style="font-size:13px; color:var(--text-muted);">
                {{ __('dashboard.activityLog.showing') ?? 'Showing' }} {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} {{ __('dashboard.activityLog.of') ?? 'of' }} {{ number_format($logs->total()) }}
            </div>
            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                @if($logs->onFirstPage())
                    <span style="padding:7px 14px; border-radius:7px; border:1px solid rgba(255,255,255,0.06); color:var(--text-muted); font-size:13px;">← Prev</span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}" style="padding:7px 14px; border-radius:7px; border:1px solid rgba(255,255,255,0.1); color:var(--text-primary); text-decoration:none; font-size:13px; font-weight:600; transition:all .15s;" onmouseover="this.style.borderColor='#F97316'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'">← Prev</a>
                @endif

                @foreach($logs->getUrlRange(1, $logs->lastPage()) as $page => $url)
                    @if($page === $logs->currentPage())
                        <span style="padding:7px 14px; border-radius:7px; background:#F97316; color:#fff; font-size:13px; font-weight:700;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" style="padding:7px 14px; border-radius:7px; border:1px solid rgba(255,255,255,0.1); color:var(--text-primary); text-decoration:none; font-size:13px; font-weight:600; transition:all .15s;" onmouseover="this.style.borderColor='#F97316'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'">{{ $page }}</a>
                    @endif
                @endforeach

                @if($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}" style="padding:7px 14px; border-radius:7px; border:1px solid rgba(255,255,255,0.1); color:var(--text-primary); text-decoration:none; font-size:13px; font-weight:600; transition:all .15s;" onmouseover="this.style.borderColor='#F97316'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'">Next →</a>
                @else
                    <span style="padding:7px 14px; border-radius:7px; border:1px solid rgba(255,255,255,0.06); color:var(--text-muted); font-size:13px;">Next →</span>
                @endif
            </div>
        </div>
    @endif
</div>

@endif

@endsection
