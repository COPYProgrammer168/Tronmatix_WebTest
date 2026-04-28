@extends('dashboard.layout')
@section('title', 'DISCOUNTS')

@section('content')

@php
    use App\Models\AdminSetting;
    $_pRole = Auth::guard('admin')->user()?->role ?? 'viewer';
    $_pFeat = 'products';
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
@endphp

@if(!$_pAccess)
<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:60vh;text-align:center;padding:40px 20px;font-family:Rajdhani,sans-serif;">
    <div style="font-size:46px;margin-bottom:20px;">🔒</div>
    <div style="font-size:28px;font-weight:900;letter-spacing:3px;color:#ef4444;margin-bottom:8px;">ACCESS DENIED</div>
    <div style="font-size:14px;color:rgba(255,255,255,0.35);margin-bottom:24px;">Contact a <span style="color:#F97316;font-weight:700;">Super Admin</span> to request access.</div>
    <a href="{{ route('dashboard.index') }}" style="padding:12px 24px;border-radius:12px;text-decoration:none;background:#F97316;color:#fff;font-size:14px;font-weight:700;">🏠 GO TO DASHBOARD</a>
</div>
@else

@php
    $activeCount    = $discounts->where('is_active', true)->filter(fn($d) => !($d->expires_at && $d->expires_at->isPast()) && !($d->max_uses && $d->used_count >= $d->max_uses))->count();
    $expiredCount   = $discounts->filter(fn($d) => $d->expires_at && $d->expires_at->isPast())->count();
    $exhaustedCount = $discounts->filter(fn($d) => $d->max_uses && $d->used_count >= $d->max_uses)->count();
    $disabledCount  = $discounts->where('is_active', false)->count();
    $totalUses      = $discounts->sum('used_count');
    $badgeDiscounts = $discounts->filter(fn($d) =>
        ($d->badge_config && !empty($d->badge_config['text']))
        || ($d->kind ?? 'code') === 'badge'
    );

    $catGroups = [
        'PC BUILD'    => ['PC BUILD UNDER 1K','PC BUILD UNDER 2K','PC BUILD UNDER 3K','PC BUILD UNDER 4K','PC BUILD UNDER 5K','PC BUILD 5K UP'],
        'MONITOR'     => ['MONITOR 25INCH','MONITOR 27INCH','MONITOR 32INCH','MONITOR 34INCH','MONITOR 39INCH','MONITOR 42INCH','MONITOR 48INCH','MONITOR 49INCH'],
        'PC PART'     => ['CPU','RAM','MAINBOARD','COOLING','M2','VGA','CASE','POWER SUPPLY','FAN'],
        'HOT ITEM'    => ['BEST PRICE','BEST SET'],
        'ACCESSORY'   => ['KEYBOARD','MOUSE','HEADSET','EARPHONE','MONITOR STAND','SPEAKER','MICROPHONE','WEBCAM','MOUSEPAD','LIGHTBAR','ROUTER'],
        'TABLE CHAIR' => ['DX RACER','SECRETLAB','RAZER','CONSAIR','FANTECH','COOLER MASTER','TTR RACING'],
    ];
@endphp

<style>
.disc-stats   { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; margin-bottom:24px; }
.disc-header  { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.disc-actions { display:flex; align-items:center; gap:10px; margin-left:auto; flex-shrink:0; }
.modal-grid   { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.modal-full   { grid-column:1/-1; }

@media(max-width:900px) {
    .disc-stats { grid-template-columns:repeat(3,1fr); }
}
@media(max-width:640px) {
    .disc-stats           { grid-template-columns:repeat(2,1fr); }
    .disc-header          { flex-direction:column; align-items:flex-start; gap:8px; }
    .disc-actions         { margin-left:0; width:100%; }
    .disc-actions .btn,
    .disc-actions button  { flex:1; text-align:center; justify-content:center; }
    .modal-grid           { grid-template-columns:1fr; }
    .modal-full           { grid-column:1/-1; }
    .badge-grid           { grid-template-columns:1fr !important; }
    .disc-table th,
    .disc-table td        { padding:8px 10px; font-size:12px; }
}
@media(max-width:400px) {
    .disc-stats { grid-template-columns:1fr 1fr; }
}
</style>

{{-- ── Stats ────────────────────────────────────────────────────────────────── --}}
<div class="disc-stats">
    @foreach([
        ['label'=>'ACTIVE',    'val'=>$activeCount,    'color'=>'#22c55e'],
        ['label'=>'EXPIRED',   'val'=>$expiredCount,   'color'=>'#ef4444'],
        ['label'=>'EXHAUSTED', 'val'=>$exhaustedCount, 'color'=>'#a855f7'],
        ['label'=>'DISABLED',  'val'=>$disabledCount,  'color'=>'#6b7280'],
        ['label'=>'TOTAL USES','val'=>$totalUses,       'color'=>'#F97316'],
    ] as $stat)
    <div style="background:#1a1a1a; border:1px solid rgba(255,255,255,0.07); border-radius:12px; padding:14px 18px;">
        <div style="font-size:11px; letter-spacing:2px; color:rgba(255,255,255,0.4); margin-bottom:4px;">{{ $stat['label'] }}</div>
        <div style="font-size:26px; font-weight:900; color:{{ $stat['color'] }};">{{ $stat['val'] }}</div>
    </div>
    @endforeach
</div>


{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- SECTION 1 — COUPON CODE DISCOUNTS                                         --}}
{{-- Customer types the code at checkout                                        --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="card" style="margin-bottom:28px;">
    <div class="card-header">
        <div class="disc-header">
            <span style="font-size:18px;">🎟️</span>
            <div>
                <span class="card-title" style="font-weight:900; font-size:18px; letter-spacing:1.5px;">COUPON CODE DISCOUNTS</span>
                <div style="font-size:11px; color:rgba(255,255,255,0.35); margin-top:2px;">Customer types code at checkout to receive the discount</div>
            </div>
            <div class="disc-actions">
                <button type="button" onclick="openCouponModal()" class="btn btn-orange"
                        style="font-size:13px; padding:8px 18px; white-space:nowrap; display:flex; align-items:center; gap:6px;">
                    + ADD COUPON
                </button>
            </div>
        </div>
    </div>
    <div class="table-wrap" style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
        <table class="disc-table" style="min-width:680px;">
            <thead>
                <tr>
                    <th>CODE</th>
                    <th>KIND</th>
                    <th>TYPE / VALUE</th>
                    <th>CATEGORIES</th>
                    <th>MIN ORDER</th>
                    <th>USAGE</th>
                    <th>EXPIRES</th>
                    <th>STATUS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($discounts as $d)
                @php
                    $isExpired   = $d->expires_at && $d->expires_at->isPast();
                    $isExhausted = $d->max_uses && $d->used_count >= $d->max_uses;
                    $status = !$d->is_active ? 'disabled'
                        : ($isExpired   ? 'expired'
                        : ($isExhausted ? 'exhausted' : 'active'));
                    $usagePct = $d->max_uses ? min(100, round($d->used_count / $d->max_uses * 100)) : null;
                    $barColor = $usagePct >= 100 ? '#a855f7' : ($usagePct >= 75 ? '#ef4444' : '#F97316');
                @endphp
                <tr>
                    {{-- CODE --}}
                    <td>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-family:monospace; font-weight:700; color:#F97316; font-size:14px; letter-spacing:1px; white-space:nowrap;">{{ $d->code }}</span>
                            <button onclick="copyCode('{{ $d->code }}')" title="Copy"
                                style="background:none; border:none; cursor:pointer; color:rgba(255,255,255,0.3); padding:2px; flex-shrink:0;"
                                onmouseenter="this.style.color='#F97316'" onmouseleave="this.style.color='rgba(255,255,255,0.3)'">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                                </svg>
                            </button>
                        </div>
                    </td>

                    {{-- KIND --}}
                    @php $dKind = $d->kind ?? 'code'; @endphp
                    <td>
                        @if($dKind === 'badge')
                            <span style="font-size:10px; font-weight:800; letter-spacing:1px; padding:3px 9px; border-radius:20px;
                                         color:#a78bfa; background:rgba(167,139,250,0.12); border:1px solid rgba(167,139,250,0.3); white-space:nowrap;">
                                🏷 BADGE
                            </span>
                        @else
                            <span style="font-size:10px; font-weight:800; letter-spacing:1px; padding:3px 9px; border-radius:20px;
                                         color:#F97316; background:rgba(249,115,22,0.1); border:1px solid rgba(249,115,22,0.3); white-space:nowrap;">
                                🎟 CODE
                            </span>
                        @endif
                    </td>

                    {{-- TYPE / VALUE --}}
                    <td>
                        <div style="display:flex; flex-direction:column; gap:3px;">
                            <span class="badge badge-gray" style="width:fit-content; font-size:10px;">
                                {{ $d->type === 'percentage' ? 'PERCENT %' : 'FIXED $' }}
                            </span>
                            <span style="font-weight:900; color:#fff; font-size:16px;">
                                {{ $d->type === 'percentage' ? $d->value.'%' : '$'.number_format($d->value, 2) }}
                            </span>
                        </div>
                    </td>

                    {{-- CATEGORIES --}}
                    <td>
                        @if($d->categories && count($d->categories) > 0)
                            <div style="display:flex; flex-wrap:wrap; gap:4px; max-width:180px;">
                                @foreach($d->categories as $cat)
                                    <span class="badge badge-gray" style="font-size:10px;">{{ $cat }}</span>
                                @endforeach
                            </div>
                        @else
                            <span style="color:rgba(255,255,255,0.35); font-size:12px; font-style:italic;">All categories</span>
                        @endif
                    </td>

                    {{-- MIN ORDER --}}
                    <td style="color:rgba(255,255,255,0.5); white-space:nowrap;">
                        {{ $d->min_order > 0 ? '$'.number_format($d->min_order, 2) : '—' }}
                    </td>

                    {{-- USAGE --}}
                    <td>
                        <div style="min-width:85px;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                <span style="color:rgba(255,255,255,0.8); font-weight:700; font-size:13px;">
                                    {{ $d->used_count }}
                                    <span style="color:rgba(255,255,255,0.3); font-weight:400;">/ {{ $d->max_uses ?? '∞' }}</span>
                                </span>
                                @if($usagePct !== null)
                                    <span style="font-size:10px; color:{{ $barColor }}; font-weight:700;">{{ $usagePct }}%</span>
                                @endif
                            </div>
                            @if($d->max_uses)
                            <div style="height:4px; background:rgba(255,255,255,0.08); border-radius:99px; overflow:hidden;">
                                <div style="height:100%; width:{{ $usagePct }}%; background:{{ $barColor }}; border-radius:99px;"></div>
                            </div>
                            @endif
                        </div>
                    </td>

                    {{-- EXPIRES --}}
                    <td style="white-space:nowrap;">
                        @if($d->expires_at)
                            <span style="color:{{ $isExpired ? '#ef4444' : 'rgba(255,255,255,0.5)' }}; font-size:12px;">{{ $d->expires_at->format('d M Y') }}</span>
                            @if($isExpired)<div style="font-size:10px; color:#ef4444; font-weight:700;">EXPIRED</div>@endif
                        @else
                            <span style="color:rgba(255,255,255,0.25); font-size:12px;">No limit</span>
                        @endif
                    </td>

                    {{-- STATUS --}}
                    <td>
                        @php
                            $sc = ['active'=>['#22c55e','rgba(34,197,94,0.12)','rgba(34,197,94,0.3)'],
                                   'expired'=>['#ef4444','rgba(239,68,68,0.12)','rgba(239,68,68,0.3)'],
                                   'exhausted'=>['#a855f7','rgba(168,85,247,0.12)','rgba(168,85,247,0.3)'],
                                   'disabled'=>['#6b7280','rgba(107,114,128,0.12)','rgba(107,114,128,0.3)']][$status];
                        @endphp
                        <span style="font-size:11px; font-weight:800; letter-spacing:1px; padding:3px 10px; border-radius:20px;
                                     color:{{ $sc[0] }}; background:{{ $sc[1] }}; border:1px solid {{ $sc[2] }}; white-space:nowrap;">
                            {{ strtoupper($status) }}
                        </span>
                    </td>

                    {{-- ACTIONS --}}
                    <td>
                        <div style="display:flex; gap:6px; flex-wrap:nowrap;">
                            <button onclick="openBadgeModal({{ $d->id }}, {{ json_encode($d->badge_config) }}, '{{ $d->type }}', {{ $d->value }})"
                                style="padding:5px 10px; border-radius:7px; font-size:11px; font-weight:700; cursor:pointer; letter-spacing:1px;
                                       background:rgba(167,139,250,0.1); border:1px solid rgba(167,139,250,0.3); color:#a78bfa;
                                       font-family:Rajdhani,sans-serif; white-space:nowrap;"
                                onmouseover="this.style.background='rgba(167,139,250,0.2)'" onmouseout="this.style.background='rgba(167,139,250,0.1)'">
                                BADGE
                            </button>
                            <button onclick="openCouponModal({{ $d->id }}, '{{ $d->code }}', '{{ $d->type }}', {{ $d->value }}, {{ $d->min_order }}, {{ $d->max_uses ?? 'null' }}, '{{ $d->expires_at ? $d->expires_at->format('Y-m-d') : '' }}', {{ $d->is_active ? 'true' : 'false' }}, {{ json_encode($d->categories ?? []) }}, '{{ $d->kind ?? 'code' }}')"
                                style="padding:5px 10px; border-radius:7px; font-size:11px; font-weight:700; cursor:pointer; letter-spacing:1px;
                                       background:rgba(249,115,22,0.1); border:1px solid rgba(249,115,22,0.3); color:#F97316;
                                       font-family:Rajdhani,sans-serif; white-space:nowrap;"
                                onmouseover="this.style.background='rgba(249,115,22,0.2)'" onmouseout="this.style.background='rgba(249,115,22,0.1)'">
                                EDIT
                            </button>
                            <form method="POST" action="{{ route('dashboard.discounts.destroy', $d->id) }}"
                                  onsubmit="return confirm('Delete this discount?')" style="display:inline; margin:0;">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    style="padding:5px 10px; border-radius:7px; font-size:11px; font-weight:700; cursor:pointer; letter-spacing:1px;
                                           background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#ef4444;
                                           font-family:Rajdhani,sans-serif; white-space:nowrap;"
                                    onmouseover="this.style.background='rgba(239,68,68,0.2)'" onmouseout="this.style.background='rgba(239,68,68,0.1)'">
                                    DEL
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding:40px; color:rgba(255,255,255,0.3);">
                        No discounts yet. Click <strong style="color:#F97316;">+ ADD COUPON</strong> to create one.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- SECTION 2 — DISCOUNT BADGES                                               --}}
{{-- Auto-shown on product cards & cart page, no code needed                   --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="card">
    <div class="card-header">
        <div class="disc-header">
            <span style="font-size:18px;">🏷️</span>
            <div>
                <span class="card-title" style="font-weight:900; font-size:18px; letter-spacing:1.5px;">DISCOUNT BADGES</span>
                <div style="font-size:11px; color:rgba(255,255,255,0.35); margin-top:2px;">Auto-displayed on product cards &amp; cart page — no code required</div>
            </div>
            <div class="disc-actions">
                <span style="font-size:11px; color:rgba(255,255,255,0.3); font-style:italic;">
                    → Click <strong style="color:#a78bfa; font-style:normal;">BADGE</strong> on any row above to configure
                </span>
            </div>
        </div>
    </div>

    @if($badgeDiscounts->isEmpty())
    <div style="padding:40px; text-align:center;">
        <div style="font-size:36px; margin-bottom:12px;">🏷️</div>
        <div style="color:rgba(255,255,255,0.4); font-size:14px; margin-bottom:6px;">No badge discounts configured yet.</div>
        <div style="color:rgba(255,255,255,0.25); font-size:12px;">
            Create a coupon above, then click its <strong style="color:#a78bfa;">BADGE</strong> button to configure it.
        </div>
    </div>
    @else
    <div class="badge-grid" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:14px; padding:18px 20px;">
        @foreach($badgeDiscounts as $d)
        @php
            $bc2       = $d->badge_config;
            $badgeLive = $d->is_active
                && !($d->expires_at && $d->expires_at->isPast())
                && !($d->max_uses && $d->used_count >= $d->max_uses);
        @endphp
        <div style="background:#141414; border:1px solid {{ $badgeLive ? 'rgba(167,139,250,0.3)' : 'rgba(255,255,255,0.08)' }};
                    border-radius:14px; padding:16px 18px; display:flex; flex-direction:column; gap:12px; position:relative;">

            <div style="position:absolute; top:12px; right:14px;">
                @if($badgeLive)
                    <span style="font-size:11px; font-weight:800; color:#22c55e; background:rgba(34,197,94,0.12);
                                 border:1px solid rgba(34,197,94,0.3); border-radius:20px; padding:2px 8px; letter-spacing:1px;">● LIVE</span>
                @else
                    <span style="font-size:11px; font-weight:800; color:#6b7280; background:rgba(107,114,128,0.1);
                                 border:1px solid rgba(107,114,128,0.2); border-radius:20px; padding:2px 8px; letter-spacing:1px;">○ INACTIVE</span>
                @endif
            </div>

            <div style="display:flex; align-items:center; gap:10px; padding-right:80px; flex-wrap:wrap;">
                @if($bc2 && !empty($bc2['text']))
                <div style="display:inline-flex; align-items:center; gap:6px; padding:5px 14px; border-radius:20px;
                            font-size:13px; font-weight:900; letter-spacing:1px;
                            background:{{ $bc2['bg'] ?? 'rgba(249,115,22,0.18)' }};
                            border:1.5px solid {{ $bc2['border'] ?? 'rgba(249,115,22,0.55)' }};
                            color:{{ $bc2['color'] ?? '#F97316' }};">
                    {{ $bc2['icon'] ?? '🏷️' }} {{ $bc2['text'] }}
                </div>
                @else
                <div style="display:inline-flex; align-items:center; gap:6px; padding:5px 14px; border-radius:20px;
                            font-size:13px; font-weight:900; letter-spacing:1px;
                            background:rgba(249,115,22,0.10); border:1.5px solid rgba(249,115,22,0.3); color:#F97316;">
                    🏷️ NO BADGE YET
                </div>
                @endif
                <div style="font-size:13px; font-weight:900; color:#fff;">
                    {{ $d->type === 'percentage' ? $d->value.'% OFF' : '$'.number_format($d->value,2).' OFF' }}
                </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:5px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:10px; color:rgba(255,255,255,0.3); letter-spacing:1px; min-width:58px;">CODE</span>
                    <span style="font-family:monospace; font-weight:700; color:#F97316; font-size:12px; letter-spacing:1px;">{{ $d->code }}</span>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:10px; color:rgba(255,255,255,0.3); letter-spacing:1px; min-width:58px;">VALUE</span>
                    <span style="font-size:12px; font-weight:800; color:#fff;">
                        {{ $d->type === 'percentage' ? $d->value.'%' : '$'.number_format($d->value,2) }}
                        <span style="font-size:11px; color:rgba(255,255,255,0.4); font-weight:400;">({{ $d->type }})</span>
                    </span>
                </div>
            </div>

            <div>
                <div style="font-size:10px; color:rgba(255,255,255,0.3); letter-spacing:1px; margin-bottom:5px;">APPLIES TO</div>
                @if($d->categories && count($d->categories) > 0)
                    <div style="display:flex; flex-wrap:wrap; gap:4px;">
                        @foreach($d->categories as $cat)
                            <span style="background:rgba(167,139,250,0.1); border:1px solid rgba(167,139,250,0.25);
                                         color:#a78bfa; font-size:10px; font-weight:700; border-radius:20px; padding:2px 8px;">{{ $cat }}</span>
                        @endforeach
                    </div>
                @else
                    <span style="color:rgba(255,255,255,0.3); font-size:12px; font-style:italic;">All categories (sitewide)</span>
                @endif
            </div>

            <div style="border-top:1px solid rgba(255,255,255,0.06); padding-top:10px; display:flex; gap:8px;">
                <button onclick="openBadgeModal({{ $d->id }}, {{ json_encode($bc2) }}, '{{ $d->type }}', {{ $d->value }})"
                    style="flex:1; padding:7px; border-radius:8px; cursor:pointer; font-size:12px; font-weight:700;
                           background:rgba(167,139,250,0.1); border:1px solid rgba(167,139,250,0.3); color:#a78bfa; font-family:Rajdhani,sans-serif;"
                    onmouseover="this.style.background='rgba(167,139,250,0.2)'" onmouseout="this.style.background='rgba(167,139,250,0.1)'">
                    ✏ EDIT BADGE
                </button>
                <button onclick="openCouponModal({{ $d->id }}, '{{ $d->code }}', '{{ $d->type }}', {{ $d->value }}, {{ $d->min_order }}, {{ $d->max_uses ?? 'null' }}, '{{ $d->expires_at ? $d->expires_at->format('Y-m-d') : '' }}', {{ $d->is_active ? 'true' : 'false' }}, {{ json_encode($d->categories ?? []) }}, '{{ $d->kind ?? 'code' }}')"
                    style="flex:1; padding:7px; border-radius:8px; cursor:pointer; font-size:12px; font-weight:700;
                           background:rgba(249,115,22,0.08); border:1px solid rgba(249,115,22,0.25); color:#F97316; font-family:Rajdhani,sans-serif;"
                    onmouseover="this.style.background='rgba(249,115,22,0.18)'" onmouseout="this.style.background='rgba(249,115,22,0.08)'">
                    ✏ EDIT
                </button>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>


{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- COUPON CODE MODAL                                                          --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div id="couponModal" style="display:none; position:fixed; inset:0; z-index:1000;
     background:rgba(0,0,0,0.75); align-items:center; justify-content:center; padding:16px;">
    <div style="background:#1a1a1a; border:1px solid rgba(249,115,22,0.2); border-radius:16px;
                padding:24px; width:100%; max-width:500px; max-height:92vh; overflow-y:auto;">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:22px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:34px; height:34px; border-radius:9px; background:rgba(249,115,22,0.12); border:1px solid rgba(249,115,22,0.3);
                            display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">🎟️</div>
                <div>
                    <div id="couponModalTitle" style="font-size:17px; font-weight:900; color:#fff; letter-spacing:2px;">ADD COUPON</div>
                    <div style="font-size:11px; color:rgba(255,255,255,0.3); margin-top:1px;">Customer types this code at checkout</div>
                </div>
            </div>
            <button onclick="closeCouponModal()"
                style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1);
                       color:rgba(255,255,255,0.5); width:32px; height:32px; border-radius:8px;
                       cursor:pointer; font-size:16px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">✕</button>
        </div>

        <form id="couponForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="couponMethod" value="POST">
            <input type="hidden" id="couponId" name="id" value="">

            <div class="modal-grid">
                <div class="modal-full">
                    <label class="form-label">DISCOUNT CODE *</label>
                    <input type="text" name="code" id="fCode" class="form-control" required
                           placeholder="e.g. SAVE20" style="text-transform:uppercase; letter-spacing:2px; width:100%; box-sizing:border-box;"
                           oninput="this.value=this.value.toUpperCase()">
                </div>
                <div>
                    <label class="form-label">TYPE *</label>
                    <select name="type" id="fType" class="form-control" required style="width:100%; box-sizing:border-box;">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Amount ($)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">VALUE *</label>
                    <input type="number" name="value" id="fValue" class="form-control" required
                           min="0" step="0.01" placeholder="e.g. 20" style="width:100%; box-sizing:border-box;">
                </div>
                <div>
                    <label class="form-label">MIN ORDER ($)</label>
                    <input type="number" name="min_order" id="fMinOrder" class="form-control"
                           min="0" step="0.01" placeholder="0 = no minimum" style="width:100%; box-sizing:border-box;">
                </div>
                <div>
                    <label class="form-label">MAX USES</label>
                    <input type="number" name="max_uses" id="fMaxUses" class="form-control"
                           min="1" placeholder="Blank = unlimited" style="width:100%; box-sizing:border-box;">
                </div>
                <div class="modal-full">
                    <label class="form-label">EXPIRY DATE</label>
                    <input type="date" name="expires_at" id="fExpires" class="form-control" style="width:100%; box-sizing:border-box;">
                </div>
                <div class="modal-full">
                    <label class="form-label">
                        APPLY TO CATEGORIES
                        <span style="color:rgba(255,255,255,0.3); font-size:11px; font-weight:400;">(empty = all categories)</span>
                    </label>
                    <div id="catHiddenInputs"></div>
                    <div id="catTags" onclick="toggleCatDropdown(event)"
                         style="min-height:42px; background:#111; border:1px solid rgba(255,255,255,0.15);
                                border-radius:8px; padding:6px 36px 6px 10px; cursor:pointer;
                                display:flex; flex-wrap:wrap; gap:6px; align-items:center;
                                position:relative; transition:border-color .2s; box-sizing:border-box;">
                        <span id="catPlaceholder" style="color:rgba(255,255,255,0.3); font-size:13px; user-select:none;">
                            Select categories…
                        </span>
                        <svg style="position:absolute; right:10px; top:50%; transform:translateY(-50%); pointer-events:none;"
                             width="14" height="14" fill="none" stroke="rgba(255,255,255,0.4)" stroke-width="2" viewBox="0 0 24 24">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </div>
                </div>
                <div class="modal-full">
                    <label class="form-label" style="margin-bottom:8px; display:block;">DISCOUNT KIND</label>
                    <div style="display:flex; gap:10px;">

                        {{-- 🎟 CODE pill --}}
                        <label id="kindCodeLabel" for="fKindCode" onclick="setKind('code')"
                               style="flex:1; cursor:pointer; display:flex; align-items:center; gap:10px;
                                      padding:11px 14px; border-radius:10px; transition:all .2s;
                                      border:2px solid rgba(249,115,22,0.5); background:rgba(249,115,22,0.12);">
                            <input type="radio" name="kind" id="fKindCode" value="code" checked style="display:none">
                            <span id="kindCodeDot"
                                  style="width:20px; height:20px; border-radius:5px; flex-shrink:0; transition:all .2s;
                                         border:2px solid #F97316; background:#F97316;
                                         display:flex; align-items:center; justify-content:center;">
                                <svg id="kindCodeCheck" width="11" height="11" viewBox="0 0 11 11" fill="none">
                                    <path d="M1.5 5.5l3 3 5-5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <div>
                                <div id="kindCodeText" style="font-weight:800; font-size:13px; letter-spacing:1px; color:#F97316;">🎟 DISCOUNT CODE</div>
                                <div style="font-size:10px; color:rgba(255,255,255,0.4); margin-top:1px;">Customer types code at checkout</div>
                            </div>
                        </label>

                        {{-- 🏷 BADGE pill --}}
                        <label id="kindBadgeLabel" for="fKindBadge" onclick="setKind('badge')"
                               style="flex:1; cursor:pointer; display:flex; align-items:center; gap:10px;
                                      padding:11px 14px; border-radius:10px; transition:all .2s;
                                      border:2px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.03);">
                            <input type="radio" name="kind" id="fKindBadge" value="badge" style="display:none">
                            <span id="kindBadgeDot"
                                  style="width:20px; height:20px; border-radius:5px; flex-shrink:0; transition:all .2s;
                                         border:2px solid #6b7280; background:transparent;
                                         display:flex; align-items:center; justify-content:center;">
                                <svg id="kindBadgeCheck" width="11" height="11" viewBox="0 0 11 11" fill="none" style="opacity:0">
                                    <path d="M1.5 5.5l3 3 5-5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <div>
                                <div id="kindBadgeText" style="font-weight:800; font-size:13px; letter-spacing:1px; color:rgba(255,255,255,0.5);">🏷 DISCOUNT BADGE</div>
                                <div style="font-size:10px; color:rgba(255,255,255,0.4); margin-top:1px;">Auto-shown on product cards</div>
                            </div>
                        </label>

                    </div>
                </div>

                {{-- ── ACTIVE — styled orange checkbox ── --}}
                <div class="modal-full" style="display:flex; align-items:center; gap:10px; cursor:pointer; user-select:none;"
                     onclick="document.getElementById('fActive').click(); event.preventDefault();">
                    <span id="fActiveVisual"
                          style="width:20px; height:20px; border-radius:5px; flex-shrink:0; transition:all .2s;
                                 border:2px solid #F97316; background:#F97316;
                                 display:flex; align-items:center; justify-content:center;">
                        <svg id="fActiveCheck" width="11" height="11" viewBox="0 0 11 11" fill="none">
                            <path d="M1.5 5.5l3 3 5-5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <input type="checkbox" name="is_active" id="fActive" value="1" checked
                           style="display:none" onchange="syncActiveVisual()">
                    <label for="fActive" class="form-label" style="margin:0; cursor:pointer; user-select:none;">
                        Active (can be used by customers)
                    </label>
                </div>
            </div>

            <div style="margin-top:20px; display:flex; gap:12px;">
                <button type="button" onclick="closeCouponModal()" class="btn btn-outline" style="flex:1;">CANCEL</button>
                <button type="submit" class="btn btn-orange" style="flex:1;">SAVE COUPON</button>
            </div>
        </form>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- CATEGORY DROPDOWN — at body level, escapes modal overflow clipping        --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div id="catDropdown"
     style="display:none; position:fixed; z-index:99999; background:#1e1e1e;
            border:1px solid rgba(249,115,22,0.4); border-radius:10px;
            padding:12px; max-height:300px; overflow-y:auto;
            box-shadow:0 8px 32px rgba(0,0,0,0.85);">
    <input id="catSearch" type="text" placeholder="🔍 Search category…"
           oninput="filterCats(this.value)"
           style="width:100%; background:#111; border:1px solid rgba(255,255,255,0.1);
                  color:#fff; border-radius:6px; padding:6px 10px; font-size:13px;
                  margin-bottom:10px; outline:none; box-sizing:border-box;"
           onclick="event.stopPropagation()">
    @foreach($catGroups as $group => $subs)
    <div class="cat-group" style="margin-bottom:10px;">
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:5px;">
            <input type="checkbox" class="group-checkbox" data-group="{{ $group }}"
                   style="accent-color:#F97316; width:14px; height:14px; cursor:pointer; flex-shrink:0;"
                   onclick="toggleGroup('{{ $group }}', this.checked); event.stopPropagation()">
            <span style="color:#F97316; font-weight:800; font-size:12px; letter-spacing:1.5px; user-select:none;">{{ $group }}</span>
        </div>
        <div style="display:flex; flex-wrap:wrap; gap:6px; padding-left:22px;">
            @foreach($subs as $cat)
            <label class="cat-chip" data-cat="{{ $cat }}" data-group="{{ $group }}"
                   style="display:inline-flex; align-items:center; gap:5px; background:#2a2a2a; border:1.5px solid transparent;
                          border-radius:20px; padding:4px 11px; cursor:pointer; font-size:12px; color:rgba(255,255,255,0.7);
                          transition:all .15s; user-select:none; white-space:nowrap;"
                   onclick="toggleCat('{{ $cat }}', '{{ $group }}'); event.stopPropagation()">
                <span class="chip-dot" style="width:7px; height:7px; border-radius:50%; background:transparent; border:1.5px solid #666; flex-shrink:0;"></span>
                {{ $cat }}
            </label>
            @endforeach
        </div>
    </div>
    @endforeach
</div>


{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- BADGE MODAL                                                               --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div id="badgeModal" style="display:none; position:fixed; inset:0; z-index:2000;
     background:rgba(0,0,0,0.78); align-items:center; justify-content:center; padding:16px; backdrop-filter:blur(4px);">
    <div style="background:#141414; border:1px solid rgba(249,115,22,0.2); border-radius:20px;
                padding:28px; width:100%; max-width:480px; max-height:92vh; overflow-y:auto;
                box-shadow:0 32px 80px rgba(0,0,0,0.7);">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:22px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:36px; height:36px; border-radius:10px; background:rgba(249,115,22,0.12); border:1px solid rgba(249,115,22,0.3);
                            display:flex; align-items:center; justify-content:center; font-size:17px; flex-shrink:0;">🏷️</div>
                <div>
                    <div style="font-size:17px; font-weight:900; color:#fff; letter-spacing:2px;" id="badgeModalTitle">DISCOUNT BADGE</div>
                    <div style="font-size:11px; color:rgba(255,255,255,0.3); margin-top:1px;" id="badgeModalSubtitle">Auto-shown on cart &amp; product cards</div>
                </div>
            </div>
            <button onclick="closeBadgeModal()"
                style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1);
                       color:rgba(255,255,255,0.5); width:32px; height:32px; border-radius:8px;
                       cursor:pointer; font-size:16px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">✕</button>
        </div>

        {{-- Live preview --}}
        <div style="background:#0d0d0d; border:1px solid rgba(255,255,255,0.07); border-radius:12px;
                    padding:16px; margin-bottom:20px; text-align:center;">
            <div style="font-size:10px; letter-spacing:2px; color:rgba(255,255,255,0.3); margin-bottom:12px; font-weight:700;">LIVE PREVIEW</div>
            <div style="display:inline-flex; align-items:center; gap:10px; flex-wrap:wrap; justify-content:center;">
                <div id="bPreview" style="display:inline-flex; align-items:center; gap:5px; padding:5px 14px;
                    border-radius:20px; font-size:13px; font-weight:800; letter-spacing:1px; transition:all .25s;">
                    <span id="bPrevIcon">🏷️</span><span id="bPrevText">SALE</span>
                </div>
                <div id="bPrevValue" style="font-size:14px; font-weight:900; color:#fff;"></div>
            </div>
        </div>

        <div style="margin-bottom:14px;">
            <label style="font-size:11px; font-weight:700; letter-spacing:2px; color:rgba(255,255,255,0.4); display:block; margin-bottom:6px;">BADGE TEXT *</label>
            <input type="text" id="bText" placeholder="e.g. SALE, HOT, 20% OFF" maxlength="18"
                   style="width:100%; background:#111; border:1px solid rgba(255,255,255,0.12); color:#fff;
                          border-radius:8px; padding:10px 14px; font-size:14px; font-weight:800; letter-spacing:1.5px;
                          box-sizing:border-box; outline:none; font-family:Rajdhani,sans-serif;"
                   oninput="this.value=this.value.toUpperCase(); syncPreview()"
                   onfocus="this.style.borderColor='#F97316'" onblur="this.style.borderColor='rgba(255,255,255,0.12)'">
        </div>

        <div style="margin-bottom:14px;">
            <label style="font-size:11px; font-weight:700; letter-spacing:2px; color:rgba(255,255,255,0.4); display:block; margin-bottom:8px;">ICON</label>
            <div style="display:flex; flex-wrap:wrap; gap:6px;">
                @foreach(['🏷️','🔥','⚡','💥','🎯','✨','🎁','💎','🚀','⭐','🆕','💰'] as $ico)
                <button type="button" onclick="selectBIcon('{{ $ico }}')" data-bico="{{ $ico }}"
                    style="width:36px; height:36px; border-radius:8px; font-size:19px; cursor:pointer;
                           background:rgba(255,255,255,0.05); border:1.5px solid rgba(255,255,255,0.1);
                           transition:all .15s; display:flex; align-items:center; justify-content:center;">{{ $ico }}</button>
                @endforeach
            </div>
        </div>

        <div style="margin-bottom:20px;">
            <label style="font-size:11px; font-weight:700; letter-spacing:2px; color:rgba(255,255,255,0.4); display:block; margin-bottom:8px;">COLOR</label>
            <div style="display:flex; flex-wrap:wrap; gap:6px;">
                @php $bcp = [
                    ['l'=>'Orange','bg'=>'rgba(249,115,22,0.18)','bd'=>'rgba(249,115,22,0.55)','c'=>'#F97316'],
                    ['l'=>'Red',   'bg'=>'rgba(239,68,68,0.18)',  'bd'=>'rgba(239,68,68,0.55)',  'c'=>'#ef4444'],
                    ['l'=>'Green', 'bg'=>'rgba(34,197,94,0.18)',  'bd'=>'rgba(34,197,94,0.55)',  'c'=>'#22c55e'],
                    ['l'=>'Blue',  'bg'=>'rgba(59,130,246,0.18)', 'bd'=>'rgba(59,130,246,0.55)', 'c'=>'#3b82f6'],
                    ['l'=>'Purple','bg'=>'rgba(167,139,250,0.18)','bd'=>'rgba(167,139,250,0.55)','c'=>'#a78bfa'],
                    ['l'=>'Yellow','bg'=>'rgba(234,179,8,0.18)',  'bd'=>'rgba(234,179,8,0.55)',  'c'=>'#eab308'],
                    ['l'=>'Solid', 'bg'=>'#F97316',               'bd'=>'#F97316',               'c'=>'#fff'],
                ]; @endphp
                @foreach($bcp as $i => $p)
                <button type="button" data-bbg="{{ $p['bg'] }}" data-bbd="{{ $p['bd'] }}" data-bc="{{ $p['c'] }}"
                    onclick="selectBColor(this)"
                    style="padding:5px 12px; border-radius:20px; cursor:pointer; font-size:11px; font-weight:800; letter-spacing:1px;
                           font-family:Rajdhani,sans-serif; transition:all .15s; background:{{ $p['bg'] }};
                           border:1.5px solid {{ $p['bd'] }}; color:{{ $p['c'] }};
                           {{ $i===0 ? 'outline:2px solid rgba(255,255,255,0.4);outline-offset:2px;' : '' }}"
                    onmouseover="this.style.filter='brightness(1.2)'" onmouseout="this.style.filter=''">
                    {{ $p['l'] }}
                </button>
                @endforeach
            </div>
        </div>

        <div style="display:flex; gap:10px;">
            <button type="button" onclick="closeBadgeModal()"
                style="flex:1; padding:10px; border-radius:10px; cursor:pointer; font-size:13px; font-weight:700;
                       background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1);
                       color:rgba(255,255,255,0.5); font-family:Rajdhani,sans-serif;">CANCEL</button>
            <button type="button" id="badgeClearBtn" onclick="saveBadge(true)"
                style="flex:1; padding:10px; border-radius:10px; cursor:pointer; font-size:13px; font-weight:700;
                       background:rgba(239,68,68,0.1); border:1.5px solid rgba(239,68,68,0.3);
                       color:#ef4444; font-family:Rajdhani,sans-serif; display:none;"
                onmouseover="this.style.background='rgba(239,68,68,0.2)'" onmouseout="this.style.background='rgba(239,68,68,0.1)'">
                🗑 CLEAR</button>
            <button type="button" onclick="saveBadge(false)"
                style="flex:2; padding:10px; border-radius:10px; cursor:pointer; font-size:14px; font-weight:800;
                       background:rgba(167,139,250,0.15); border:1.5px solid rgba(167,139,250,0.4); color:#a78bfa;
                       font-family:Rajdhani,sans-serif; letter-spacing:1px;"
                onmouseover="this.style.background='rgba(167,139,250,0.25)'" onmouseout="this.style.background='rgba(167,139,250,0.15)'">
                💾 SAVE BADGE</button>
        </div>
    </div>
</div>


{{-- Toasts --}}
<div id="copyToast" style="display:none; position:fixed; bottom:24px; right:24px; z-index:9999;
     background:#22c55e; color:#fff; font-weight:700; font-size:14px; letter-spacing:1px;
     padding:10px 20px; border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,0.4);">
    ✓ Code copied!
</div>
<div id="badgeCopiedToast" style="display:none; position:fixed; bottom:24px; right:24px; z-index:9999;
     background:linear-gradient(135deg,#a78bfa,#8b5cf6); color:#fff; font-weight:700; font-size:14px;
     letter-spacing:1px; padding:12px 22px; border-radius:12px; box-shadow:0 4px 24px rgba(139,92,246,0.5);">
    ✓ Badge saved!
</div>


<script>
// ── Copy ──────────────────────────────────────────────────────────────────────
function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        const t = document.getElementById('copyToast')
        t.style.display = 'block'
        setTimeout(() => t.style.display = 'none', 2000)
    })
}

// ── Category dropdown ─────────────────────────────────────────────────────────
let selectedCats = []

function toggleCatDropdown(e) {
    if (e) e.stopPropagation()
    const dd = document.getElementById('catDropdown')
    if (dd.style.display !== 'none') { dd.style.display = 'none'; return }
    const rect = document.getElementById('catTags').getBoundingClientRect()
    const ddW  = Math.min(460, window.innerWidth - 24)
    let left   = rect.left
    if (left + ddW > window.innerWidth - 12) left = window.innerWidth - ddW - 12
    if (left < 12) left = 12
    dd.style.left    = left + 'px'
    dd.style.width   = ddW + 'px'
    dd.style.top     = (rect.bottom + 6) + 'px'
    dd.style.display = 'block'
    setTimeout(() => document.getElementById('catSearch').focus(), 50)
}

function toggleCat(cat, group) {
    const idx = selectedCats.indexOf(cat)
    if (idx === -1) selectedCats.push(cat); else selectedCats.splice(idx, 1)
    renderCatState(); updateGroupCheckbox(group)
}

function toggleGroup(group, checked) {
    document.querySelectorAll(`[data-group="${group}"]`).forEach(chip => {
        const cat = chip.dataset.cat; if (!cat) return
        if (checked && !selectedCats.includes(cat)) selectedCats.push(cat)
        else if (!checked) selectedCats = selectedCats.filter(c => c !== cat)
    })
    renderCatState()
}

function updateGroupCheckbox(group) {
    const chips = document.querySelectorAll(`label[data-group="${group}"]`)
    const total = chips.length
    const chk   = [...chips].filter(c => selectedCats.includes(c.dataset.cat)).length
    const cb    = document.querySelector(`.group-checkbox[data-group="${group}"]`)
    if (cb) { cb.checked = chk === total && total > 0; cb.indeterminate = chk > 0 && chk < total }
}

function renderCatState() {
    document.querySelectorAll('label[data-cat]').forEach(chip => {
        const active = selectedCats.includes(chip.dataset.cat)
        chip.style.background  = active ? 'rgba(249,115,22,0.2)' : '#2a2a2a'
        chip.style.borderColor = active ? '#F97316' : 'transparent'
        chip.style.color       = active ? '#fff' : 'rgba(255,255,255,0.7)'
        const dot = chip.querySelector('.chip-dot')
        if (dot) { dot.style.background = active ? '#F97316' : 'transparent'; dot.style.borderColor = active ? '#F97316' : '#666' }
    })
    const tagsEl = document.getElementById('catTags'), ph = document.getElementById('catPlaceholder')
    if (!tagsEl) return
    tagsEl.querySelectorAll('.selected-tag').forEach(t => t.remove())
    if (selectedCats.length === 0) { ph.style.display = '' } else {
        ph.style.display = 'none'
        selectedCats.forEach(cat => {
            const tag = document.createElement('span')
            tag.className = 'selected-tag'
            tag.style.cssText = 'background:#F97316;color:#fff;font-size:11px;font-weight:700;border-radius:20px;padding:2px 10px;display:inline-flex;align-items:center;gap:5px;letter-spacing:.5px;white-space:nowrap;'
            tag.innerHTML = `${cat} <span onclick="removeCat('${cat}');event.stopPropagation()" style="cursor:pointer;opacity:.8;font-size:13px;line-height:1;">&times;</span>`
            tagsEl.insertBefore(tag, tagsEl.lastElementChild)
        })
    }
    const hd = document.getElementById('catHiddenInputs')
    if (hd) hd.innerHTML = selectedCats.map(c => `<input type="hidden" name="categories[]" value="${c}">`).join('')
}

function removeCat(cat) {
    selectedCats = selectedCats.filter(c => c !== cat)
    const group = document.querySelector(`label[data-cat="${cat}"]`)?.dataset.group
    renderCatState(); if (group) updateGroupCheckbox(group)
}

function filterCats(q) {
    q = q.toLowerCase()
    document.querySelectorAll('.cat-group').forEach(g => {
        let vis = false
        g.querySelectorAll('label[data-cat]').forEach(c => {
            const m = c.dataset.cat.toLowerCase().includes(q); c.style.display = m ? '' : 'none'; if (m) vis = true
        })
        g.style.display = vis ? '' : 'none'
    })
}

function repositionDropdown() {
    const dd = document.getElementById('catDropdown'); if (!dd || dd.style.display === 'none') return
    const tags = document.getElementById('catTags'); if (!tags) return
    const rect = tags.getBoundingClientRect()
    dd.style.top = (rect.bottom + 6) + 'px'; dd.style.left = rect.left + 'px'
}
window.addEventListener('scroll', repositionDropdown, true)
window.addEventListener('resize', repositionDropdown)

document.addEventListener('click', function(e) {
    const dd = document.getElementById('catDropdown'), tags = document.getElementById('catTags')
    if (dd && !dd.contains(e.target) && tags && !tags.contains(e.target)) dd.style.display = 'none'
})

// ── Coupon Modal ──────────────────────────────────────────────────────────────
function openCouponModal(id, code, type, value, minOrder, maxUses, expires, isActive, categories, kind) {
    document.getElementById('couponModal').style.display = 'flex'
    selectedCats = categories && categories.length ? [...categories] : []
    renderCatState()
    document.querySelectorAll('.group-checkbox').forEach(cb => updateGroupCheckbox(cb.dataset.group))
    document.getElementById('catSearch').value = ''; filterCats('')
    document.getElementById('catDropdown').style.display = 'none'

    if (id) {
        document.getElementById('couponModalTitle').textContent = 'EDIT COUPON'
        document.getElementById('couponForm').action   = `/dashboard/discounts/${id}`
        document.getElementById('couponMethod').value  = 'PUT'
        document.getElementById('couponId').value      = id
        document.getElementById('fCode').value         = code
        document.getElementById('fType').value         = type
        document.getElementById('fValue').value        = value
        document.getElementById('fMinOrder').value     = minOrder || ''
        document.getElementById('fMaxUses').value      = maxUses  || ''
        document.getElementById('fExpires').value      = expires  || ''
        document.getElementById('fActive').checked     = isActive
        syncActiveVisual()
        setKind(kind || 'code')
    } else {
        document.getElementById('couponModalTitle').textContent = 'ADD COUPON'
        document.getElementById('couponForm').action   = '{{ route("dashboard.discounts.store") }}'
        document.getElementById('couponMethod').value  = 'POST'
        document.getElementById('couponId').value      = ''
        document.getElementById('couponForm').reset()
        document.getElementById('fActive').checked     = true
        syncActiveVisual()
        selectedCats = []; renderCatState()
        setKind('code')
    }
}

function closeCouponModal() {
    document.getElementById('couponModal').style.display = 'none'
    document.getElementById('catDropdown').style.display = 'none'
}
document.getElementById('couponModal').addEventListener('click', function(e) { if (e.target === this) closeCouponModal() })

// ── Badge Modal ───────────────────────────────────────────────────────────────
let _bIco = '🏷️', _bBg = 'rgba(249,115,22,0.18)', _bBd = 'rgba(249,115,22,0.55)', _bC = '#F97316'
let _bDiscountId = null, _bDiscountType = null, _bDiscountVal = null

function openBadgeModal(discountId, existingBadge, discountType, discountValue) {
    _bDiscountId = discountId; _bDiscountType = discountType || null; _bDiscountVal = discountValue || null
    document.getElementById('badgeModal').style.display = 'flex'
    if (existingBadge && existingBadge.text) {
        document.getElementById('bText').value = existingBadge.text || ''
        _bIco = existingBadge.icon || '🏷️'; _bBg = existingBadge.bg || 'rgba(249,115,22,0.18)'
        _bBd = existingBadge.border || 'rgba(249,115,22,0.55)'; _bC = existingBadge.color || '#F97316'
        document.getElementById('badgeModalTitle').textContent    = 'EDIT BADGE'
        document.getElementById('badgeModalSubtitle').textContent = `Discount #${discountId}`
        document.getElementById('badgeClearBtn').style.display    = 'block'
    } else {
        document.getElementById('bText').value = ''
        _bIco = '🏷️'; _bBg = 'rgba(249,115,22,0.18)'; _bBd = 'rgba(249,115,22,0.55)'; _bC = '#F97316'
        document.getElementById('badgeModalTitle').textContent    = 'ADD BADGE'
        document.getElementById('badgeModalSubtitle').textContent = `Discount #${discountId}`
        document.getElementById('badgeClearBtn').style.display    = 'none'
    }
    document.querySelectorAll('[data-bico]').forEach(b => {
        const on = b.dataset.bico === _bIco
        b.style.background = on ? 'rgba(249,115,22,0.2)' : 'rgba(255,255,255,0.05)'
        b.style.borderColor = on ? '#F97316' : 'rgba(255,255,255,0.1)'
        b.style.transform = on ? 'scale(1.18)' : 'scale(1)'
    })
    document.querySelectorAll('[data-bbg]').forEach(b => {
        b.style.outline = b.dataset.bbg === _bBg ? '2px solid rgba(255,255,255,0.45)' : 'none'
        b.style.outlineOffset = '2px'
    })
    syncPreview()
}

function closeBadgeModal() { document.getElementById('badgeModal').style.display = 'none' }
document.getElementById('badgeModal').addEventListener('click', function(e) { if (e.target === this) closeBadgeModal() })

function selectBIcon(ico) {
    _bIco = ico
    document.querySelectorAll('[data-bico]').forEach(b => {
        const on = b.dataset.bico === ico
        b.style.background = on ? 'rgba(249,115,22,0.2)' : 'rgba(255,255,255,0.05)'
        b.style.borderColor = on ? '#F97316' : 'rgba(255,255,255,0.1)'
        b.style.transform = on ? 'scale(1.18)' : 'scale(1)'
    })
    syncPreview()
}

function selectBColor(btn) {
    _bBg = btn.dataset.bbg; _bBd = btn.dataset.bbd; _bC = btn.dataset.bc
    document.querySelectorAll('[data-bbg]').forEach(b => {
        b.style.outline = b === btn ? '2px solid rgba(255,255,255,0.45)' : 'none'
        b.style.outlineOffset = '2px'
    })
    syncPreview()
}

function syncPreview() {
    const text = document.getElementById('bText').value.trim() || 'SALE'
    document.getElementById('bPrevIcon').textContent = _bIco
    document.getElementById('bPrevText').textContent = text
    const prev = document.getElementById('bPreview')
    prev.style.background = _bBg; prev.style.border = `1.5px solid ${_bBd}`; prev.style.color = _bC
    const valEl = document.getElementById('bPrevValue')
    valEl.textContent = (_bDiscountType && _bDiscountVal)
        ? (_bDiscountType === 'percentage' ? `−${_bDiscountVal}%` : `−$${parseFloat(_bDiscountVal).toFixed(2)}`)
        : ''
}

async function saveBadge(clear) {
    if (!_bDiscountId) return
    const text = document.getElementById('bText').value.trim()
    if (!clear && !text) {
        document.getElementById('bText').style.borderColor = '#ef4444'
        document.getElementById('bText').focus()
        setTimeout(() => document.getElementById('bText').style.borderColor = 'rgba(255,255,255,0.12)', 2000)
        return
    }
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_token"]')?.value || ''
    const fd = new FormData()
    fd.append('_token', csrf); fd.append('_method', 'PATCH')
    if (clear) { fd.append('clear_badge', '1') } else {
        fd.append('badge_config[text]', text); fd.append('badge_config[icon]', _bIco)
        fd.append('badge_config[bg]', _bBg); fd.append('badge_config[border]', _bBd)
        fd.append('badge_config[color]', _bC)
    }
    try {
        const res = await fetch(`/dashboard/discounts/${_bDiscountId}/badge`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: fd
        })
        if (!res.ok) throw new Error(`HTTP ${res.status}`)
        const toast = document.getElementById('badgeCopiedToast')
        toast.textContent = clear ? '🗑 Badge cleared!' : '✓ Badge saved!'
        toast.style.background = clear ? 'linear-gradient(135deg,#ef4444,#dc2626)' : 'linear-gradient(135deg,#a78bfa,#8b5cf6)'
        toast.style.display = 'block'
        setTimeout(() => toast.style.display = 'none', 2200)
        closeBadgeModal()
        setTimeout(() => window.location.reload(), 600)
    } catch (err) { alert('Failed to save badge: ' + err.message) }
}

// ── Kind selector ─────────────────────────────────────────────────────────────
function setKind(kind) {
    document.getElementById('fKindCode').checked  = (kind === 'code')
    document.getElementById('fKindBadge').checked = (kind === 'badge')

    const cL = document.getElementById('kindCodeLabel')
    const cD = document.getElementById('kindCodeDot')
    const cT = document.getElementById('kindCodeText')
    const cK = document.getElementById('kindCodeCheck')
    if (kind === 'code') {
        cL.style.border = '2px solid rgba(249,115,22,0.5)'; cL.style.background = 'rgba(249,115,22,0.12)'
        cD.style.background = '#F97316'; cD.style.borderColor = '#F97316'
        cK.style.opacity = '1'; cT.style.color = '#F97316'
    } else {
        cL.style.border = '2px solid rgba(255,255,255,0.1)'; cL.style.background = 'rgba(255,255,255,0.03)'
        cD.style.background = 'transparent'; cD.style.borderColor = '#6b7280'
        cK.style.opacity = '0'; cT.style.color = 'rgba(255,255,255,0.5)'
    }

    const bL = document.getElementById('kindBadgeLabel')
    const bD = document.getElementById('kindBadgeDot')
    const bT = document.getElementById('kindBadgeText')
    const bK = document.getElementById('kindBadgeCheck')
    if (kind === 'badge') {
        bL.style.border = '2px solid rgba(167,139,250,0.5)'; bL.style.background = 'rgba(167,139,250,0.12)'
        bD.style.background = '#a78bfa'; bD.style.borderColor = '#a78bfa'
        bK.style.opacity = '1'; bT.style.color = '#a78bfa'
    } else {
        bL.style.border = '2px solid rgba(255,255,255,0.1)'; bL.style.background = 'rgba(255,255,255,0.03)'
        bD.style.background = 'transparent'; bD.style.borderColor = '#6b7280'
        bK.style.opacity = '0'; bT.style.color = 'rgba(255,255,255,0.5)'
    }
}

// ── Active visual sync ────────────────────────────────────────────────────────
function syncActiveVisual() {
    const checked = document.getElementById('fActive').checked
    const vis     = document.getElementById('fActiveVisual')
    const chk     = document.getElementById('fActiveCheck')
    vis.style.background  = checked ? '#F97316' : 'transparent'
    vis.style.borderColor = checked ? '#F97316' : '#6b7280'
    chk.style.opacity     = checked ? '1' : '0'
}
</script>

@endif

@push('styles')
<style>
/* ── Discounts – light theme ──────────────────────────────────────────────── */
[data-theme="light"] #couponModal > div,
[data-theme="light"] #badgeModal  > div {
    background: #FFFFFF !important;
    border-color: rgba(15,23,42,0.10) !important;
    box-shadow: 0 32px 80px rgba(15,23,42,0.16) !important;
}
[data-theme="light"] #couponModal input,
[data-theme="light"] #couponModal select,
[data-theme="light"] #badgeModal input,
[data-theme="light"] #badgeModal select {
    background: #F8FAFC !important;
    border-color: rgba(15,23,42,0.14) !important;
    color: #0F172A !important;
}
[data-theme="light"] #couponModal [style*="color:rgba(255,255,255,0.4)"],
[data-theme="light"] #badgeModal  [style*="color:rgba(255,255,255,0.4)"] { color: rgba(15,23,42,0.45) !important; }
[data-theme="light"] #couponModal [style*="color:rgba(255,255,255,0.3)"],
[data-theme="light"] #badgeModal  [style*="color:rgba(255,255,255,0.3)"] { color: rgba(15,23,42,0.35) !important; }
[data-theme="light"] #couponModal [style*="color:rgba(255,255,255,0.5)"],
[data-theme="light"] #badgeModal  [style*="color:rgba(255,255,255,0.5)"] { color: rgba(15,23,42,0.55) !important; }
[data-theme="light"] #couponModal [style*="background:rgba(255,255,255,0.04)"],
[data-theme="light"] #badgeModal  [style*="background:rgba(255,255,255,0.04)"] { background: rgba(15,23,42,0.03) !important; }
[data-theme="light"] #couponModal [style*="border-bottom:1px solid rgba(255,255,255"],
[data-theme="light"] #badgeModal  [style*="border-bottom:1px solid rgba(255,255,255"] {
    border-bottom-color: rgba(15,23,42,0.08) !important;
}
/* Discount code cards */
[data-theme="light"] .discount-card {
    background: #FFFFFF !important;
    border-color: rgba(15,23,42,0.08) !important;
}
[data-theme="light"] .discount-card [style*="color:rgba(255,255,255,0.4)"] { color: rgba(15,23,42,0.45) !important; }
[data-theme="light"] .discount-card [style*="color:rgba(255,255,255,0.3)"] { color: rgba(15,23,42,0.35) !important; }
[data-theme="light"] .discount-card [style*="background:rgba(255,255,255,0.05)"],
[data-theme="light"] .discount-card [style*="background:rgba(255,255,255,0.06)"] { background: rgba(15,23,42,0.04) !important; }
[data-theme="light"] .discount-card [style*="border:1px solid rgba(255,255,255,0.08)"] { border-color: rgba(15,23,42,0.08) !important; }
/* Stats strip */
[data-theme="light"] .discount-stat [style*="background:rgba(255,255,255,0.06)"] { background: rgba(15,23,42,0.05) !important; }
/* Filter tabs */
[data-theme="light"] .discount-filter-tab {
    border-color: rgba(15,23,42,0.12) !important;
    color: rgba(15,23,42,0.55) !important;
}
[data-theme="light"] .discount-filter-tab.active {
    background: rgba(249,115,22,0.08) !important;
    border-color: #F97316 !important;
    color: #F97316 !important;
}
</style>
@endpush

@endsection
