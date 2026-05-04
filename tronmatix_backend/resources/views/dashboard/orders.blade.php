@extends('dashboard.layout')
@section('title', 'ORDERS')

@section('content')

@php
    use App\Models\AdminSetting;
    $_pRole = Auth::guard('admin')->user()?->role ?? 'viewer';
    $_pFeat = 'orders';
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
            <div style="font-size:16px;font-weight:800;color:rgba(255,255,255,0.6);letter-spacing:1px;">{{ strtoupper(str_replace('_',' ','orders')) }}</div>
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
                $_fActive = ($_fKey === 'orders');
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



{{-- ── Status category tabs ──────────────────────────────────────────────────── --}}
@php
    $tabs = [
        'all'        => ['label' => 'ALL',        'icon' => '📋', 'color' => '#fff',    'dark' => '#111'],
        'pending'    => ['label' => 'PENDING',    'icon' => '⏳', 'color' => '#eab308', 'dark' => '#111'],
        'confirmed'  => ['label' => 'CONFIRMED',  'icon' => '✅', 'color' => '#22c55e', 'dark' => '#fff'],
        'processing' => ['label' => 'PROCESSING', 'icon' => '⚙️', 'color' => '#3b82f6', 'dark' => '#fff'],
        'shipped'    => ['label' => 'SHIPPED',    'icon' => '🚚', 'color' => '#a78bfa', 'dark' => '#fff'],
        'delivered'  => ['label' => 'DELIVERED',  'icon' => '📦', 'color' => '#F97316', 'dark' => '#fff'],
        'cancelled'  => ['label' => 'CANCELLED',  'icon' => '❌', 'color' => '#ef4444', 'dark' => '#fff'],
    ];
    $activeTab = $status ?? 'all';
    $totalAll  = $statusCounts->sum();
@endphp

<div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px; align-items:center;">
    @foreach($tabs as $key => $tab)
    @php
        $count    = $key === 'all' ? $totalAll : ($statusCounts[$key] ?? 0);
        $isActive = ($activeTab === $key) || ($key === 'all' && !$activeTab);
        $href     = route('dashboard.orders', array_filter(['status' => $key === 'all' ? null : $key, 'search' => $search ?: null]));
    @endphp
    <a href="{{ $href }}" {{ !$isActive ? 'class="order-status-tab-inactive"' : '' }} style="
        display:inline-flex; align-items:center; gap:6px;
        padding:8px 16px; border-radius:30px; font-family:Rajdhani,sans-serif;
        font-size:13px; font-weight:700; letter-spacing:1px; text-decoration:none;
        transition:all 0.2s;
        background:  {{ $isActive ? $tab['color'] : 'rgba(255,255,255,0.06)' }};
        color:       {{ $isActive ? $tab['dark']  : 'rgba(255,255,255,0.5)'  }};
        border: 1.5px solid {{ $isActive ? $tab['color'] : 'rgba(255,255,255,0.1)' }};
        box-shadow:  {{ $isActive ? '0 0 14px '.$tab['color'].'66' : 'none' }};
    " onmouseover="this.style.opacity='.82'" onmouseout="this.style.opacity='1'">
        {{ $tab['icon'] }} {{ $tab['label'] }}
        <span {{ !$isActive ? 'class="order-tab-count"' : '' }} style="
            background:{{ $isActive ? 'rgba(0,0,0,0.18)' : 'rgba(255,255,255,0.1)' }};
            color:{{ $isActive ? '#fff' : 'rgba(255,255,255,0.6)' }};
            border-radius:20px; padding:0 8px; font-size:12px; font-weight:800; line-height:20px;
        ">{{ $count }}</span>
    </a>
    @endforeach

    {{-- Search --}}
    <form method="GET" action="{{ route('dashboard.orders') }}" style="margin-left:auto; display:flex; gap:8px; align-items:center;">
        @if($activeTab && $activeTab !== 'all')
            <input type="hidden" name="status" value="{{ $activeTab }}">
        @endif
        <input type="text" name="search" value="{{ $search ?? '' }}"
            placeholder="Search order ID or customer…"
            class="orders-search-input"
            style="background:rgba(255,255,255,0.07); border:1.5px solid rgba(255,255,255,0.12);
                   color:#fff; border-radius:10px; padding:8px 16px; font-size:13px;
                   font-family:Rajdhani,sans-serif; outline:none; width:230px; transition:border-color .2s;"
            onfocus="this.style.borderColor='#F97316'" onblur="this.style.borderColor=''" />
        <button type="submit" style="background:#F97316; color:#fff; border:none; border-radius:10px;
            padding:8px 16px; font-family:Rajdhani,sans-serif; font-size:13px; font-weight:700;
            cursor:pointer; letter-spacing:1px;">SEARCH</button>
        @if($search)
        <a href="{{ route('dashboard.orders', $activeTab && $activeTab !== 'all' ? ['status' => $activeTab] : []) }}"
           class="orders-clear-btn"
           style="background:rgba(255,255,255,0.07); color:rgba(255,255,255,0.5); border:1.5px solid rgba(255,255,255,0.1);
                  border-radius:10px; padding:8px 12px; font-size:14px; text-decoration:none;">✕</a>
        @endif
    </form>
</div>

{{-- ── Table ──────────────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <span class="card-title" style="font-size:22px;">
            @if($activeTab && $activeTab !== 'all') {{ strtoupper($activeTab) }} ORDERS
            @else ALL ORDERS @endif
        </span>
        <span style="color:rgba(255,255,255,0.45); font-size:14px;">
            {{ $orders->total() }} result{{ $orders->total() !== 1 ? 's' : '' }}
        </span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ORDER ID</th><th>CUSTOMER</th><th>SHIPPING TO</th><th>ITEMS</th>
                    <th>SUBTOTAL</th><th>DISCOUNT</th><th>TOTAL</th>
                    <th>PAYMENT</th><th>PAY STATUS</th><th>STATUS</th>
                    <th>ORDER DATE</th><th>DELIVERY</th><th>ACTION</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                @php
                    $shipName  = $order->shipping['name']  ?? ($order->location?->name  ?? '—');
                    $shipPhone = $order->shipping['phone'] ?? ($order->location?->phone ?? '');
                    $shipCity  = $order->shipping['city']  ?? ($order->location?->city  ?? '');
                    $payStatus = $order->payment_status ?? 'pending';
                    if ($order->payment_method === 'cash' && $payStatus === 'pending') $payStatus = 'cash';
                @endphp
                <tr style="animation:rowIn .3s ease both; animation-delay:{{ $loop->index * 25 }}ms;">

                    <td><span style="color:#F97316; font-weight:700; font-family:monospace;">{{ $order->order_id }}</span></td>

                    <td style="font-weight:600;">{{ $order->user?->username ?? 'Guest' }}</td>

                    <td>
                        <div style="font-weight:700; color:#fff; font-size:14px;">{{ $shipName }}</div>
                        @if($shipCity)  <div style="font-size:12px; color:rgba(255,255,255,0.4);">📍 {{ $shipCity }}</div> @endif
                        @if($shipPhone) <div style="font-size:12px; color:#F97316;">{{ $shipPhone }}</div> @endif
                    </td>

                    <td style="min-width:170px;">
                        @forelse($order->items as $item)
                        @php $thumb = $item->image ? (Str::startsWith($item->image,['http://','https://']) ? $item->image : asset(ltrim($item->image,'/'))) : null; @endphp
                        <div style="display:flex; align-items:center; gap:7px; margin-bottom:4px;">
                            <div style="width:30px; height:30px; border-radius:5px; overflow:hidden; flex-shrink:0;
                                background:rgba(255,255,255,0.05); display:flex; align-items:center; justify-content:center;
                                border:1px solid rgba(255,255,255,0.07);">
                                @if($thumb)
                                    <img src="{{ $thumb }}" alt="{{ $item->name }}" style="width:100%; height:100%; object-fit:contain;"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='block'" />
                                    <span style="display:none; font-size:13px;">📦</span>
                                @else <span style="font-size:13px;">📦</span> @endif
                            </div>
                            <div>
                                <span style="font-size:12px; font-weight:600; color:rgba(255,255,255,0.8);">{{ $item->name }}</span>
                                <span style="color:#F97316; font-weight:700; font-size:12px; margin-left:2px;">×{{ $item->qty }}</span>
                            </div>
                        </div>
                        @empty <span style="color:rgba(255,255,255,0.2);">—</span> @endforelse
                    </td>

                    <td style="font-weight:700;">${{ number_format($order->subtotal ?? $order->total, 2) }}</td>

                    <td>
                        @if($order->discount_amount > 0)
                            @php
                                $dBadge = $order->discount?->badge_config;
                            @endphp
                            @if($dBadge && !empty($dBadge['text']))
                                <div style="display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:12px; font-size:10px; font-weight:800; letter-spacing:0.5px;
                                    background:{{ $dBadge['bg'] ?? 'rgba(249,115,22,0.15)' }};
                                    border:1px solid {{ $dBadge['border'] ?? 'rgba(249,115,22,0.4)' }};
                                    color:{{ $dBadge['color'] ?? '#F97316' }};">
                                    {{ $dBadge['icon'] ?? '🏷️' }} {{ $dBadge['text'] }}
                                </div>
                            @elseif($order->discount_code)
                                <span style="font-family:monospace; font-size:11px; color:#4ade80; font-weight:700;
                                    background:rgba(74,222,128,0.08); border:1px solid rgba(74,222,128,0.2);
                                    border-radius:4px; padding:1px 6px;">{{ $order->discount_code }}</span>
                            @else
                                <span style="font-size:11px; color:rgba(74,222,128,0.6); font-style:italic;">auto</span>
                            @endif
                            <div style="color:#4ade80; font-size:11px; margin-top:2px; font-weight:700;">
                                −${{ number_format($order->discount_amount, 2) }}
                            </div>
                        @else
                            <span style="color:rgba(255,255,255,0.15);">—</span>
                        @endif
                    </td>

                    <td style="color:#F97316; font-weight:700;">${{ number_format($order->total, 2) }}</td>

                    <td><span class="badge badge-gray" style="font-size:11px;">{{ $order->payment_method === 'bakong' ? '📱 BAKONG' : '💵 CASH' }}</span></td>

                    <td>
                        @if    ($payStatus === 'paid')           <span class="badge badge-paid" style="font-size:11px;" title="Ref: {{ $order->payment_ref ?? '—' }}">✅ PAID</span>
                        @elseif($payStatus === 'cash')           <span class="badge badge-gray" style="font-size:11px;">💵 COD</span>
                        @elseif($payStatus === 'manual_pending') <span class="badge" style="background:rgba(249,115,22,.12); color:#F97316; border:1px solid rgba(249,115,22,.3); font-size:11px;">⚠️ VERIFY</span>
                        @elseif($payStatus === 'failed')         <span class="badge badge-cancelled" style="font-size:11px;">❌ FAILED</span>
                        @else                                    <span class="badge" style="background:rgba(234,179,8,.12); color:#eab308; border:1px solid rgba(234,179,8,.3); font-size:11px;">⏳ PENDING</span>
                        @endif
                    </td>

                    <td><span class="badge badge-{{ $order->status }}" style="font-size:11px;">{{ strtoupper($order->status) }}</span></td>

                    <td style="font-size:12px; white-space:nowrap;">
                        <div style="color:rgba(255,255,255,0.75); font-weight:600;">{{ $order->created_at->setTimezone('Asia/Phnom_Penh')->format('d M Y') }}</div>
                        <div style="color:rgba(255,255,255,0.3); font-size:11px;">🕐 {{ $order->created_at->setTimezone('Asia/Phnom_Penh')->format('H:i') }}</div>
                    </td>

                    <td style="font-size:12px; white-space:nowrap;">
                        {{-- Fulfillment type badge --}}
                        @if(($order->fulfillment_type ?? 'delivery') === 'pickup')
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;
                                border-radius:20px;font-size:11px;font-weight:700;letter-spacing:0.5px;
                                background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.3);
                                color:#22c55e;margin-bottom:4px;">
                                🏪 PICKUP
                            </span>
                        @else
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;
                                border-radius:20px;font-size:11px;font-weight:700;letter-spacing:0.5px;
                                background:rgba(167,139,250,0.12);border:1px solid rgba(167,139,250,0.3);
                                color:#a78bfa;margin-bottom:4px;">
                                🚚 DELIVERY
                            </span>
                        @endif
                        {{-- Scheduled date if set --}}
                        @if($order->delivery_date)
                            @if(($order->fulfillment_type ?? 'delivery') === 'pickup')
                                <div style="color:#22c55e; font-weight:700; margin-top:2px;">📅 {{ \Carbon\Carbon::parse($order->delivery_date)->format('d M Y') }}</div>
                            @else
                                <div style="color:#a78bfa; font-weight:700; margin-top:2px;">📅 {{ \Carbon\Carbon::parse($order->delivery_date)->format('d M Y') }}</div>
                            @endif
                            @if($order->delivery_time_slot)
                                <div style="color:rgba(167,139,250,0.55); font-size:11px;">🕐 {{ $order->delivery_time_slot }}</div>
                            @endif
                        @endif
                    </td>

                    <td><a href="{{ route('dashboard.orders.show', $order) }}" class="btn btn-outline btn-sm">VIEW</a></td>
                </tr>
                @empty
                <tr>
                    <td colspan="13" style="text-align:center; padding:60px; color:rgba(255,255,255,0.3);">
                        <div style="font-size:40px; margin-bottom:10px;">📭</div>
                        <div style="font-size:16px; font-weight:700; margin-bottom:4px;">No orders found</div>
                        @if($search) <div style="font-size:13px;">No results for "<strong style="color:#F97316">{{ $search }}</strong>"</div>
                        @elseif($activeTab && $activeTab !== 'all') <div style="font-size:13px;">No {{ $activeTab }} orders yet</div>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div style="padding:16px 20px; border-top:1px solid rgba(255,255,255,0.07);">
        {{ $orders->links('dashboard.pagination') }}
    </div>
    @endif
</div>

<style>
@keyframes rowIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:none; } }
</style>

@endif
@endsection
