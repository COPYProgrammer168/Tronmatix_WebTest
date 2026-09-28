@extends('dashboard.layout')
@section('title', strtoupper(__('dashboard.nav.activityLog')))

@section('content')

@include('dashboard._permission_check', ['feature' => 'activity_log'])
@php
    $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false;
    $_km = app()->getLocale() === 'km';
    $_fw7 = $_km ? 400 : 700;
    $_fw9 = $_km ? 400 : 900;
    $_fw6 = $_km ? 400 : 600;
@endphp

@if(!$_permDenied)

{{-- Page Header --}}
<div style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:20px;">
    <div style="display:flex; align-items:center; gap:14px;">
        <div style="width:48px; height:48px; border-radius:14px; background:rgba(249,115,22,0.12);
                    border:1px solid rgba(249,115,22,0.3); display:flex; align-items:center;
                    justify-content:center; font-size: var(--title-size);">📋</div>
        <div>
            <div style="font-size: var(--title-size); font-weight:{{ $_fw9 }}; letter-spacing:3px;">
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
              font-family: inherit; font-size: var(--title-size); font-weight:{{ $_fw7 }};
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
                <label style="display:block; font-size:var(--text-xs); color:var(--text-muted); margin-bottom:4px; font-weight:{{ $_fw6 }}; letter-spacing:0.5px;">
                    {{ __('dashboard.activityLog.action') ?? 'ACTION' }}
                </label>
                <select name="action" class="input" style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-primary); font-size:var(--text-base);">
                    <option value="">{{ __('dashboard.activityLog.allActions') ?? 'All Actions' }}</option>
                    @foreach($actions as $a)
                        <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>

            <div style="flex:1; min-width:180px;">
                <label style="display:block; font-size:var(--text-xs); color:var(--text-muted); margin-bottom:4px; font-weight:{{ $_fw6 }}; letter-spacing:0.5px;">
                    {{ __('dashboard.activityLog.entity') ?? 'ENTITY TYPE' }}
                </label>
                <select name="entity_type" class="input" style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-primary); font-size:var(--text-base);">
                    <option value="">{{ __('dashboard.activityLog.allEntities') ?? 'All Entities' }}</option>
                    @foreach($entityTypes as $et)
                        <option value="{{ $et }}" {{ request('entity_type') === $et ? 'selected' : '' }}>{{ class_basename($et) }}</option>
                    @endforeach
                </select>
            </div>

            <div style="flex:1; min-width:160px;">
                <label style="display:block; font-size:var(--text-xs); color:var(--text-muted); margin-bottom:4px; font-weight:{{ $_fw6 }}; letter-spacing:0.5px;">
                    {{ __('dashboard.activityLog.actor') ?? 'ACTOR' }}
                </label>
                <input type="text" name="actor_name" class="input" value="{{ request('actor_name') }}" placeholder="{{ __('dashboard.activityLog.actorPlaceholder') ?? 'Search actor...' }}" style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-primary); font-size:var(--text-base);">
            </div>

            <div style="min-width:130px;">
                <label style="display:block; font-size:var(--text-xs); color:var(--text-muted); margin-bottom:4px; font-weight:{{ $_fw6 }}; letter-spacing:0.5px;">
                    {{ __('dashboard.activityLog.from') ?? 'FROM' }}
                </label>
                <input type="date" name="date_from" class="input" value="{{ request('date_from') }}" style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-primary); font-size:var(--text-base);">
            </div>

            <div style="min-width:130px;">
                <label style="display:block; font-size:var(--text-xs); color:var(--text-muted); margin-bottom:4px; font-weight:{{ $_fw6 }}; letter-spacing:0.5px;">
                    {{ __('dashboard.activityLog.to') ?? 'TO' }}
                </label>
                <input type="date" name="date_to" class="input" value="{{ request('date_to') }}" style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-primary); font-size:var(--text-base);">
            </div>

            <div style="display:flex; gap:8px; align-items:flex-end; padding-bottom:1px;">
                <button type="submit" class="btn" style="padding:9px 18px; border-radius:8px; background:#F97316; color:#fff; font-weight:{{ $_fw7 }}; border:none; cursor:pointer; font-size:var(--text-base); letter-spacing:0.5px; white-space:nowrap;">
                    SEARCH
                </button>
                <a href="{{ route('dashboard.activity-logs') }}" style="padding:9px 14px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:transparent; color:var(--text-muted); text-decoration:none; font-family: inherit; font-size:var(--text-base); font-weight:{{ $_fw6 }}; white-space:nowrap;">
                    {{ __('dashboard.btn.clearFilters') ?? 'CLEAR' }}
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Stats Row --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap:12px; margin-bottom:20px;">
    <div class="card" style="border-color:rgba(249,115,22,0.15); text-align:center; padding:16px;">
        <div style="font-size:var(--text-xs); color:var(--text-muted); letter-spacing:1px; font-weight:{{ $_fw7 }};">{{ __('dashboard.activityLog.totalLogs') ?? 'TOTAL LOGS' }}</div>
        <div style="font-size:28px; font-weight:{{ $_fw9 }}; color:#F97316; margin-top:4px;">{{ number_format($logs->total()) }}</div>
    </div>
    <div class="card" style="border-color:rgba(249,115,22,0.15); text-align:center; padding:16px;">
        <div style="font-size:var(--text-xs); color:var(--text-muted); letter-spacing:1px; font-weight:{{ $_fw7 }};">{{ __('dashboard.activityLog.thisPage') ?? 'THIS PAGE' }}</div>
        <div style="font-size:28px; font-weight:{{ $_fw9 }}; color:var(--text-primary); margin-top:4px;">{{ number_format($logs->count()) }}</div>
    </div>
    <div class="card" style="border-color:rgba(249,115,22,0.15); text-align:center; padding:16px;">
        <div style="font-size:var(--text-xs); color:var(--text-muted); letter-spacing:1px; font-weight:{{ $_fw7 }};">{{ __('dashboard.activityLog.lastPage') ?? 'PAGES' }}</div>
        <div style="font-size:28px; font-weight:{{ $_fw9 }}; color:var(--text-primary); margin-top:4px;">{{ $logs->lastPage() }}</div>
    </div>
    <div class="card" style="border-color:rgba(249,115,22,0.15); text-align:center; padding:16px;">
        <div style="font-size:var(--text-xs); color:var(--text-muted); letter-spacing:1px; font-weight:{{ $_fw7 }};">{{ __('dashboard.activityLog.perPage') ?? 'PER PAGE' }}</div>
        <div style="font-size:28px; font-weight:{{ $_fw9 }}; color:var(--text-primary); margin-top:4px;">{{ $logs->perPage() }}</div>
    </div>
</div>

{{-- Recent Alerts — Order Status Updates & Login Events --}}
@if($recentAlerts->count())
<div style="margin-bottom:20px;">
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
        <span style="font-size:16px;">🔔</span>
        <span style="font-size:var(--text-sm); font-weight:{{ $_fw7 }}; color:var(--text-muted); letter-spacing:1px;">
            {{ __('dashboard.activityLog.recentAlerts') }}
        </span>
        <span style="font-size:var(--text-xs); padding:2px 10px; border-radius:9999px; background:rgba(249,115,22,0.12); color:#F97316; font-weight:{{ $_fw7 }};">
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
                } elseif ($alert->action === 'discount_expired') {
                    $icon = '⏰';
                    $bgColor = 'rgba(245,158,11,0.08)';
                    $borderColor = 'rgba(245,158,11,0.25)';
                    $accentColor = '#f59e0b';
                    $title = $alert->entity_name ? 'Discount #' . $alert->entity_id : 'Discount';
                    $dv = $alertDetails['value'] ?? '';
                    $dt = ($alertDetails['type'] ?? 'percentage') === 'percentage' ? '%' : '$';
                    $desc = "Discount <strong style='color:#f59e0b'>auto-expired</strong>" . ($dv !== '' ? " ({$dv}{$dt} OFF)" : '');
                } elseif ($alert->action === 'login_success') {
                    $guard = $alertDetails['guard'] ?? '';
                    $icon = '✅';
                    $bgColor = 'rgba(16,185,129,0.08)';
                    $borderColor = 'rgba(16,185,129,0.25)';
                    $accentColor = '#10b981';
                    $title = $alert->actor_name ?: 'Login';
                    $roleLabel = $guard ? ucfirst($guard) : ($alert->actor_type ?? 'User');
                    $desc = "<strong style='color:#10b981'>Successful login</strong> as <span style='color:var(--text-primary);font-weight:{{ $_fw6 }};'>{$roleLabel}</span>";
                } elseif ($alert->action === 'login_failed') {
                    $icon = '🚫';
                    $bgColor = 'rgba(239,68,68,0.08)';
                    $borderColor = 'rgba(239,68,68,0.25)';
                    $accentColor = '#ef4444';
                    $title = $alert->entity_name ?: 'Unknown';
                    $desc = "<strong style='color:#ef4444'>Failed login</strong> attempt" . (!empty($alertDetails['reason']) ? " ({$alertDetails['reason']})" : '');
                } elseif ($alert->action === 'password_reset_requested') {
                    $icon = '🔑';
                    $bgColor = 'rgba(59,130,246,0.08)';
                    $borderColor = 'rgba(59,130,246,0.25)';
                    $accentColor = '#3b82f6';
                    $title = $alert->entity_name ?: 'Password reset';
                    $desc = "<strong style='color:#3b82f6'>Password reset link</strong> requested for <span style='color:var(--text-primary);font-weight:{{ $_fw6 }};'>{$alert->entity_name}</span>";
                } elseif ($alert->action === 'password_reset_failed') {
                    $icon = '🔒';
                    $bgColor = 'rgba(239,68,68,0.08)';
                    $borderColor = 'rgba(239,68,68,0.25)';
                    $accentColor = '#ef4444';
                    $title = $alert->entity_name ?: 'Password reset';
                    $desc = "<strong style='color:#ef4444'>Password reset failed</strong> for <span style='color:var(--text-primary);font-weight:{{ $_fw6 }};'>{$alert->entity_name}</span>";
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
                    $desc = "<strong style='color:#10b981'>Delivery confirmed</strong>" . ($by ? " by <span style='color:var(--text-primary);font-weight:{{ $_fw6 }};'>{$by}</span>" : '');
                }
            @endphp
            <div style="display:flex; align-items:center; gap:12px; padding:10px 16px; border-radius:10px; background:{{ $bgColor }}; border:1px solid {{ $borderColor }}; transition:all .15s;"
                 onmouseover="this.style.borderColor='{{ $accentColor }}66'"
                 onmouseout="this.style.borderColor='{{ $borderColor }}'">
                <span style="font-size:18px;">{{ $icon }}</span>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:var(--text-sm); font-weight:{{ $_fw7 }}; color:var(--text-primary);">{{ $title }}</div>
                    <div style="font-size:var(--text-xs); color:var(--text-muted); margin-top:2px;">{!! $desc !!}</div>
                </div>
                <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
                    <span style="font-size:var(--text-xs); color:var(--text-muted);">{{ $alert->created_at?->format('H:i') ?? '—' }}</span>
                    <span style="font-size:var(--text-xs); padding:2px 8px; border-radius:6px; background:{{ $accentColor }}18; color:{{ $accentColor }}; font-weight:{{ $_fw6 }}; white-space:nowrap; text-align:right;" title="{{ $alert->ip_address ?: '' }}">
                        @php
                            $loc = \App\Services\IpLocationService::format([
                                'city'    => $alert->ip_city,
                                'region'  => $alert->ip_region,
                                'country' => $alert->ip_country,
                            ]);
                        @endphp
                        {{ $loc !== 'Unknown' ? '📍 ' . $loc : ($alert->ip_address ?: '—') }}
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
        <table style="width:100%; border-collapse:collapse; font-family: inherit; font-size:var(--text-sm);">
            <thead>
                <tr style="background:rgba(249,115,22,0.06); border-bottom:1px solid rgba(249,115,22,0.15);">
                    <th style="text-align:left; padding:12px 16px; font-weight:{{ $_fw7 }}; color:var(--text-muted); letter-spacing:0.5px; white-space:nowrap;">{{ __('dashboard.table.date') ?? 'DATE' }}</th>
                    <th style="text-align:left; padding:12px 16px; font-weight:{{ $_fw7 }}; color:var(--text-muted); letter-spacing:0.5px; white-space:nowrap;">{{ __('dashboard.activityLog.actor') ?? 'ACTOR' }}</th>
                    <th style="text-align:left; padding:12px 16px; font-weight:{{ $_fw7 }}; color:var(--text-muted); letter-spacing:0.5px; white-space:nowrap;">{{ __('dashboard.activityLog.action') ?? 'ACTION' }}</th>
                    <th style="text-align:left; padding:12px 16px; font-weight:{{ $_fw7 }}; color:var(--text-muted); letter-spacing:0.5px; white-space:nowrap;">{{ __('dashboard.activityLog.entity') ?? 'ENTITY' }}</th>
                    <th style="text-align:left; padding:12px 16px; font-weight:{{ $_fw7 }}; color:var(--text-muted); letter-spacing:0.5px; white-space:nowrap;">{{ __('dashboard.table.details') ?? 'DETAILS' }}</th>
                    <th style="text-align:left; padding:12px 16px; font-weight:{{ $_fw7 }}; color:var(--text-muted); letter-spacing:0.5px; white-space:nowrap;">{{ __('dashboard.table.ip') ?? 'IP' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    @php
                        $actionColors = [
                            'login_success'        => '#10b981',
                            'login_failed'         => '#ef4444',
                            'login_rate_limited'   => '#f59e0b',
                            'password_reset_requested' => '#3b82f6',
                            'password_reset_failed'    => '#ef4444',
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
                            'discount_expired'     => '#f59e0b',
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
                            {{ $log->created_at?->format('Y-m-d H:i') ?? '—' }}
                        </td>
                        <td style="padding:10px 16px;">
                            <div style="font-weight:{{ $_fw7 }}; color:var(--text-primary); display:flex; align-items:center; gap:6px;">
                                {{ $log->actor_name ?: '—' }}
                                @if($log->action === 'login_success')
                                    <span style="font-size:var(--text-xs); padding:1px 7px; border-radius:9999px; background:rgba(16,185,129,0.12); color:#10b981; font-weight:{{ $_fw7 }}; letter-spacing:0.5px; border:1px solid rgba(16,185,129,0.2);">LOGIN</span>
                                @elseif($log->action === 'login_failed' || $log->action === 'login_rate_limited')
                                    <span style="font-size:var(--text-xs); padding:1px 7px; border-radius:9999px; background:rgba(239,68,68,0.12); color:#ef4444; font-weight:{{ $_fw7 }}; letter-spacing:0.5px; border:1px solid rgba(239,68,68,0.2);">FAILED</span>
                                @elseif($log->action === 'order_status_update')
                                    <span style="font-size:var(--text-xs); padding:1px 7px; border-radius:9999px; background:rgba(99,102,241,0.12); color:#6366f1; font-weight:{{ $_fw7 }}; letter-spacing:0.5px; border:1px solid rgba(99,102,241,0.2);">ORDER</span>
                                @elseif($log->action === 'order_cancelled')
                                    <span style="font-size:var(--text-xs); padding:1px 7px; border-radius:9999px; background:rgba(239,68,68,0.12); color:#ef4444; font-weight:{{ $_fw7 }}; letter-spacing:0.5px; border:1px solid rgba(239,68,68,0.2);">CANCEL</span>
                                @endif
                            </div>
                            <div style="font-size:var(--text-xs); color:var(--text-muted);">{{ $log->actor_type ?: 'System' }}</div>
                        </td>
                        <td style="padding:10px 16px;">
                            <span style="display:inline-block; padding:3px 10px; border-radius:9999px; font-size:var(--text-xs); font-weight:{{ $_fw7 }}; background:{{ $actionColor }}22; color:{{ $actionColor }}; border:1px solid {{ $actionColor }}44;">
                                {{ $actionLabel }}
                            </span>
                        </td>
                        <td style="padding:10px 16px; white-space:nowrap;">
                            @if($log->entity_type)
                                <span style="font-weight:{{ $_fw6 }}; color:var(--text-primary);">{{ class_basename($log->entity_type) }}</span>
                                @if($log->entity_name)
                                    <div style="font-size:var(--text-xs); color:var(--text-muted);">{{ $log->entity_name }}</div>
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
                                } elseif ($log->action === 'password_reset_requested') {
                                    $showDetailAlert = true;
                                    $detailIcon = '🔑';
                                    $detailText = 'Password reset link sent';
                                    $detailColor = '#3b82f6';
                                } elseif ($log->action === 'password_reset_failed') {
                                    $showDetailAlert = true;
                                    $detailIcon = '🔒';
                                    $reason = $log->details['reason'] ?? 'account_not_found';
                                    $detailText = 'Reset failed: ' . ucwords(str_replace('_', ' ', $reason));
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
                                } elseif ($log->action === 'discount_created') {
                                    $showDetailAlert = true;
                                    $detailIcon = '🏷️';
                                    $dv = $log->details['value'] ?? '';
                                    $dt = ($log->details['type'] ?? 'percentage') === 'percentage' ? '%' : '$';
                                    $detailText = 'Discount created' . ($dv !== '' ? ": {$dv}{$dt} OFF" : '');
                                    $detailColor = '#3b82f6';
                                } elseif ($log->action === 'discount_updated') {
                                    $showDetailAlert = true;
                                    $detailIcon = '✏️';
                                    $hadBadge = ! empty($log->details['badge_config']);
                                    $dv = $log->details['value'] ?? '';
                                    $dt = ($log->details['type'] ?? 'percentage') === 'percentage' ? '%' : '$';
                                    $detailText = $hadBadge
                                        ? 'Badge updated'
                                        : 'Discount updated' . ($dv !== '' ? ": {$dv}{$dt} OFF" : '');
                                    $detailColor = '#6366f1';
                                } elseif ($log->action === 'discount_deleted') {
                                    $showDetailAlert = true;
                                    $detailIcon = '🗑️';
                                    $detailText = 'Discount deleted';
                                    $detailColor = '#ef4444';
                                } elseif ($log->action === 'discount_expired') {
                                    $showDetailAlert = true;
                                    $detailIcon = '⏰';
                                    $dv = $log->details['value'] ?? '';
                                    $dt = ($log->details['type'] ?? 'percentage') === 'percentage' ? '%' : '$';
                                    $detailText = 'Auto-expired' . ($dv !== '' ? ": {$dv}{$dt} OFF" : '');
                                    $detailColor = '#f59e0b';
                                }
                            @endphp
                            @if($showDetailAlert)
                                <div style="font-size:var(--text-sm); color:{{ $detailColor }}; font-weight:{{ $_fw6 }}; display:flex; align-items:center; gap:4px;">
                                    <span>{{ $detailIcon }}</span>
                                    <span>{{ $detailText }}</span>
                                </div>
                            @elseif($detailsJson && $detailsJson !== '[]')
                                <button type="button"
                                    onclick="openLogDetailsModal(this)"
                                    data-action-label="{{ $actionLabel }}"
                                    data-entity-name="{{ $log->entity_name }}"
                                    data-details="{{ $detailsJson }}"
                                    style="font-size:var(--text-sm); color:#F97316; font-weight:{{ $_fw6 }}; background:none; border:none; cursor:pointer; text-decoration:underline; padding:0; font-family:inherit;">
                                    View details →
                                </button>
                            @else
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td style="padding:10px 16px; white-space:nowrap; color:var(--text-muted); font-size:var(--text-sm); font-weight:{{ $_fw6 }};">
                            @php
                                $logLoc = \App\Services\IpLocationService::format([
                                    'city'    => $log->ip_city,
                                    'region'  => $log->ip_region,
                                    'country' => $log->ip_country,
                                ]);
                            @endphp
                            @if($logLoc !== 'Unknown')
                                <span title="{{ $log->ip_address ?: '' }}">📍 {{ $logLoc }}</span>
                            @else
                                {{ $log->ip_address ?: '—' }}
                            @endif
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
            <div style="font-size:var(--text-sm); color:var(--text-muted);">
                {{ __('dashboard.activityLog.showing') ?? 'Showing' }} {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} {{ __('dashboard.activityLog.of') ?? 'of' }} {{ number_format($logs->total()) }}
            </div>
            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                @if($logs->onFirstPage())
                    <span style="padding:7px 14px; border-radius:7px; border:1px solid rgba(255,255,255,0.06); color:var(--text-muted); font-size:var(--text-sm);">← Prev</span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}" style="padding:7px 14px; border-radius:7px; border:1px solid rgba(255,255,255,0.1); color:var(--text-primary); text-decoration:none; font-size:var(--text-sm); font-weight:{{ $_fw6 }}; transition:all .15s;" onmouseover="this.style.borderColor='#F97316'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'">← Prev</a>
                @endif

                @foreach($logs->getUrlRange(1, $logs->lastPage()) as $page => $url)
                    @if($page === $logs->currentPage())
                        <span style="padding:7px 14px; border-radius:7px; background:#F97316; color:#fff; font-size:var(--text-sm); font-weight:{{ $_fw7 }};">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" style="padding:7px 14px; border-radius:7px; border:1px solid rgba(255,255,255,0.1); color:var(--text-primary); text-decoration:none; font-size:var(--text-sm); font-weight:{{ $_fw6 }}; transition:all .15s;" onmouseover="this.style.borderColor='#F97316'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'">{{ $page }}</a>
                    @endif
                @endforeach

                @if($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}" style="padding:7px 14px; border-radius:7px; border:1px solid rgba(255,255,255,0.1); color:var(--text-primary); text-decoration:none; font-size:var(--text-sm); font-weight:{{ $_fw6 }}; transition:all .15s;" onmouseover="this.style.borderColor='#F97316'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'">Next →</a>
                @else
                    <span style="padding:7px 14px; border-radius:7px; border:1px solid rgba(255,255,255,0.06); color:var(--text-muted); font-size:var(--text-sm);">Next →</span>
                @endif
            </div>
        </div>
    @endif
</div>

@endif

{{-- ── Shared "View details" modal (rendered once, populated by JS) ──────────── --}}
<div id="logDetailsModal"
     style="position:fixed; inset:0; background:var(--overlay); display:none; z-index:100; align-items:center; justify-content:center; padding:20px;"
     onclick="if(event.target===this) closeLogDetailsModal()">
    <div id="logDetailsCard" style="background:var(--modal-bg); border:1px solid var(--border-input); border-radius:14px; width:100%; max-width:480px; max-height:80vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 24px 64px rgba(0,0,0,0.45);">
        {{-- Header --}}
        <div style="display:flex; align-items:center; gap:10px; padding:14px 18px; border-bottom:1px solid var(--border);">
            <div style="width:34px; height:34px; border-radius:9px; background:var(--active-bg); border:1px solid rgba(249,115,22,0.3); display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0;">📋</div>
            <div style="flex:1; min-width:0;">
                <div id="logDetailsTitle" style="font-size:var(--text-base); font-weight:{{ $_fw7 }}; color:var(--text-main); letter-spacing:0.5px;">Details</div>
                <div id="logDetailsSubtitle" style="font-size:var(--text-xs); color:var(--text-muted); margin-top:1px;">—</div>
            </div>
            <button type="button" id="logDetailsCloseBtn" onclick="closeLogDetailsModal()"
                style="width:30px; height:30px; border-radius:8px; background:var(--hover-bg); border:1px solid var(--border); color:var(--text-muted); cursor:pointer; font-size:15px; line-height:1; flex-shrink:0;"
                onmouseover="this.style.background='rgba(249,115,22,0.15)'" onmouseout="this.style.background='var(--hover-bg)'">✕</button>
        </div>
        {{-- Body: two-column table filled by JS --}}
        <div style="padding:14px 18px; overflow-y:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:var(--text-sm);">
                <thead>
                    <tr>
                        <th style="text-align:left; padding:6px 10px; color:var(--text-muted); font-weight:700; letter-spacing:0.5px; border-bottom:1px solid var(--border); font-size:var(--text-sm);">FIELD</th>
                        <th style="text-align:left; padding:6px 10px; color:var(--text-muted); font-weight:700; letter-spacing:0.5px; border-bottom:1px solid var(--border); font-size:var(--text-sm);">VALUE</th>
                    </tr>
                </thead>
                <tbody id="logDetailsBody"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function humanizeKey(str) {
        return String(str)
            .replace(/_/g, ' ')
            .replace(/\b\w/g, c => c.toUpperCase());
    }

    var logTheme = {
        muted: 'var(--text-muted)',
        xfaint: 'var(--text-xfaint)',
        border: 'var(--border)',
        rowStripe: 'var(--hover-bg)',
        valueColor: 'var(--text-main)'
    };

    function formatDetailValue(v) {
        if (v === null || v === undefined) return '<span style="color:' + logTheme.muted + ';">—</span>';
        if (typeof v === 'boolean') return v ? '<span style="color:#22c55e;">true</span>' : '<span style="color:#ef4444;">false</span>';
        if (typeof v === 'object') return '<code style="color:#a78bfa; font-size:var(--text-sm); font-weight:600;">' + JSON.stringify(v) + '</code>';
        return String(v);
    }

    function openLogDetailsModal(btn) {
        var title = btn.dataset.actionLabel || 'Details';
        var entityName = btn.dataset.entityName || '—';
        var raw = btn.dataset.details || '{}';
        var details;
        try { details = JSON.parse(raw); } catch (e) { details = {}; }

        document.getElementById('logDetailsTitle').textContent = title;
        document.getElementById('logDetailsSubtitle').textContent = entityName;

        var body = document.getElementById('logDetailsBody');
        body.innerHTML = '';

        var keys = Object.keys(details);
        if (keys.length === 0) {
            body.innerHTML = '<tr><td colspan="2" style="padding:14px 10px; color:' + logTheme.muted + '; font-weight:600; font-size:var(--text-sm);">No details.</td></tr>';
        } else {
            keys.forEach(function (k, i) {
                var v = details[k];
                var rowBg = (i % 2 === 0) ? logTheme.rowStripe : 'transparent';

                // Detect old→new diff shape: value is an object with 'old' and 'new'
                var isDiff = v !== null && typeof v === 'object' && !Array.isArray(v) &&
                             ('old' in v || 'new' in v);

                var labelCell = '<td style="padding:6px 10px; color:var(--text-muted); font-weight:600; white-space:nowrap;">' + humanizeKey(k) + '</td>';

                var valueCell;
                if (isDiff) {
                    var oldV = formatDetailValue(v.old);
                    var newV = formatDetailValue(v.new);
                    valueCell = '<td style="padding:6px 10px; color:' + logTheme.valueColor + '; font-weight:600; word-break:break-word;">' +
                        oldV + ' <span style="color:' + logTheme.muted + ';">→</span> ' + newV + '</td>';
                } else {
                    valueCell = '<td style="padding:6px 10px; color:' + logTheme.valueColor + '; font-weight:600; word-break:break-word;">' + formatDetailValue(v) + '</td>';
                }

                body.insertAdjacentHTML('beforeend', '<tr style="background:' + rowBg + ';">' + labelCell + valueCell + '</tr>');
            });
        }

        document.getElementById('logDetailsModal').style.display = 'flex';
    }

    function closeLogDetailsModal() {
        document.getElementById('logDetailsModal').style.display = 'none';
    }

    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeLogDetailsModal();
    });
</script>

@endsection
