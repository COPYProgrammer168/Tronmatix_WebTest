@extends('dashboard.layout')
@section('title', 'ORDER #' . $order->order_id)

{{-- Suppress the layout's inline flash — this page uses floating toast messages instead --}}
@section('suppress_flash') @endsection

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



{{-- ── Floating flash toasts (fixed-position, page-specific) ───────────────── --}}
@if(session('success'))
<div id="flash-success" style="
    position:fixed; top:24px; left:50%; transform:translateX(-50%); z-index:9999;
    background:linear-gradient(135deg,#22c55e,#16a34a); color:#fff;
    border-radius:14px; padding:14px 28px; font-family:Rajdhani,sans-serif;
    font-size:15px; font-weight:700; letter-spacing:1px;
    box-shadow:0 8px 32px rgba(34,197,94,0.4);
    display:flex; align-items:center; gap:10px;
    animation:slideDown .35s cubic-bezier(0.34,1.56,0.64,1);
">
    <span style="font-size:20px;">✅</span> {{ session('success') }}
</div>
<script>
    setTimeout(() => {
        const el = document.getElementById('flash-success');
        if (el) { el.style.animation = 'fadeOut .4s ease forwards'; setTimeout(() => el?.remove(), 400); }
    }, 3200);
</script>
@endif

@if(session('error'))
<div id="flash-error" style="
    position:fixed; top:24px; left:50%; transform:translateX(-50%); z-index:9999;
    background:linear-gradient(135deg,#ef4444,#dc2626); color:#fff;
    border-radius:14px; padding:14px 28px; font-family:Rajdhani,sans-serif;
    font-size:15px; font-weight:700; letter-spacing:1px;
    box-shadow:0 8px 32px rgba(239,68,68,0.4);
    display:flex; align-items:center; gap:10px;
    animation:slideDown .35s cubic-bezier(0.34,1.56,0.64,1);
">
    <span style="font-size:20px;">⚠️</span> {{ session('error') }}
</div>
<script>
    setTimeout(() => {
        const el = document.getElementById('flash-error');
        if (el) { el.style.animation = 'fadeOut .4s ease forwards'; setTimeout(() => el?.remove(), 400); }
    }, 3200);
</script>
@endif

{{-- ── Back button ───────────────────────────────────────────────────────────── --}}
<a href="{{ route('dashboard.orders') }}" class="btn btn-outline btn-sm" style="margin-bottom:20px;">
    ← BACK TO ORDERS
</a>

{{-- ── Two-column layout ─────────────────────────────────────────────────────── --}}
<div style="display:grid; grid-template-columns:1fr 340px; gap:20px;">

    {{-- ══ LEFT COLUMN ══════════════════════════════════════════════════════════ --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- Order Info --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">ORDER INFORMATION</span>
                <span class="badge badge-{{ $order->status }}" style="font-size:13px;">
                    {{ strtoupper($order->status) }}
                </span>
            </div>
            <div class="card-body">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    @foreach([
                        'Order ID'       => $order->order_id,
                        'Customer'       => $order->user?->username ?? 'Guest',
                        'Payment Method' => strtoupper($order->payment_method),
                        'Date'           => $order->created_at->setTimezone('Asia/Phnom_Penh')->format('d M Y H:i').' (ICT)',
                    ] as $label => $value)
                    <div>
                        <div style="font-size:10px; letter-spacing:2px; color:rgba(255,255,255,0.3); margin-bottom:5px;">
                            {{ strtoupper($label) }}
                        </div>
                        <div style="font-weight:700; color:#fff;">{{ $value }}</div>
                    </div>
                    @endforeach

                    @if($order->delivery_date)
                    <div>
                        <div style="font-size:10px; letter-spacing:2px; color:rgba(255,255,255,0.3); margin-bottom:5px;">DELIVERY DATE</div>
                        <div style="font-weight:700; color:#F97316;">
                            🗓 {{ \Carbon\Carbon::parse($order->delivery_date)->format('d M Y') }}
                            @if($order->delivery_time_slot) · {{ $order->delivery_time_slot }} @endif
                        </div>
                    </div>
                    @endif

                    @if($order->delivery_confirmed_at)
                    <div>
                        <div style="font-size:10px; letter-spacing:2px; color:rgba(255,255,255,0.3); margin-bottom:5px;">CONFIRMED AT</div>
                        <div style="font-weight:700; color:#22c55e;">
                            ✅ {{ $order->delivery_confirmed_at->setTimezone('Asia/Phnom_Penh')->format('d M Y, H:i') }} (ICT)
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Delivery Timeline --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">DELIVERY TIMELINE</span>
            </div>
            <div class="card-body">
                @php
                    // Full delivery pipeline — pending → confirmed → processing → shipped → delivered
                    $steps   = ['pending','confirmed','processing','shipped','delivered'];
                    $labels  = ['Pending','Confirmed','Processing','Shipped','Delivered'];
                    $icons   = ['⏳','✅','⚙️','🚚','📦'];
                    $colors  = ['#eab308','#22c55e','#3b82f6','#a78bfa','#F97316'];
                    $current = array_search($order->status, $steps);
                    if ($current === false) $current = ($order->status === 'cancelled') ? -1 : 0;
                @endphp

                {{-- Cancelled banner --}}
                @if($order->status === 'cancelled')
                <div style="text-align:center; padding:20px; border-radius:12px;
                    background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2);">
                    <div style="font-size:28px; margin-bottom:6px;">❌</div>
                    <div style="font-size:14px; font-weight:800; color:#ef4444; letter-spacing:2px;">ORDER CANCELLED</div>
                </div>
                @else
                <div style="overflow-x:auto; padding-bottom:8px;">
                <div style="display:flex; align-items:flex-start; min-width:420px;">
                    @foreach($steps as $i => $s)
                    <div style="display:flex; align-items:center; flex:1; min-width:0;">
                        <div style="display:flex; flex-direction:column; align-items:center; flex:1; min-width:60px;">
                            {{-- Step circle --}}
                            <div style="
                                width:46px; height:46px; border-radius:50%;
                                display:flex; align-items:center; justify-content:center; font-size:18px;
                                background: {{ $i < $current ? $colors[$i].'22' : ($i === $current ? $colors[$i] : 'rgba(255,255,255,0.06)') }};
                                border: 2px solid {{ $i <= $current ? $colors[$i] : 'rgba(255,255,255,0.1)' }};
                                box-shadow: {{ $i === $current ? '0 0 20px '.$colors[$i].'55' : 'none' }};
                                transition: all .5s ease;
                                {{ $i === $current ? 'animation:stepPulse 2s ease-in-out infinite;' : '' }}
                                position:relative; z-index:2;
                            ">
                                @if($i < $current)
                                    <span style="color:{{ $colors[$i] }}; font-size:16px;">✓</span>
                                @else
                                    {{ $icons[$i] }}
                                @endif
                            </div>
                            {{-- Step label --}}
                            <div style="margin-top:8px; font-size:10px; text-align:center; font-weight:700; letter-spacing:1px; line-height:1.3;
                                color: {{ $i <= $current ? $colors[$i] : 'rgba(255,255,255,0.2)' }};">
                                {{ $labels[$i] }}
                                @if($i === $current)
                                <div style="width:6px;height:6px;border-radius:50%;background:{{ $colors[$i] }};
                                    margin:4px auto 0;animation:stepPulse 1.5s ease infinite;"></div>
                                @endif
                            </div>
                        </div>
                        {{-- Connector line --}}
                        @if($i < count($steps)-1)
                        <div style="height:2px; flex:1; margin:0 2px; border-radius:1px; margin-bottom:26px;
                            background: {{ $i < $current ? 'linear-gradient(90deg,'.$colors[$i].','.$colors[$i+1].')' : 'rgba(255,255,255,0.07)' }};
                            transition: all .6s ease; min-width:10px;"></div>
                        @endif
                    </div>
                    @endforeach
                </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Order Items --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">ORDER ITEMS</span>
                <span style="color:rgba(255,255,255,0.4); font-size:13px;">
                    {{ $order->items->count() }} item(s)
                </span>
            </div>
            <div class="table-wrap">
                @php
                    // Calculate per-item discount rate so we can show discounted prices.
                    // Use subtotal as base — if items sum matches subtotal, pro-rate the discount.
                    $hasDiscount     = $order->discount_amount > 0;
                    $itemsSubtotal   = $order->items->sum(fn($i) => $i->price * $i->qty);
                    // Discount rate: what fraction of each item's price is discounted
                    $discountRate    = ($hasDiscount && $itemsSubtotal > 0)
                        ? min($order->discount_amount / $itemsSubtotal, 1.0)
                        : 0;
                @endphp
                <table>
                    <thead>
                        <tr>
                            <th>PRODUCT</th>
                            <th>UNIT PRICE</th>
                            @if($hasDiscount) <th style="color:#4ade80;">AFTER DISCOUNT</th> @endif
                            <th>QTY</th>
                            <th>TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        @php
                            $lineTotal      = $item->price * $item->qty;
                            $discountedUnit = $hasDiscount ? round($item->price * (1 - $discountRate), 2) : null;
                            $discountedLine = $hasDiscount ? round($lineTotal   * (1 - $discountRate), 2) : null;
                        @endphp
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:12px;">
                                    @php
                                        $thumbSrc = $item->image
                                            ? (Str::startsWith($item->image, ['http://','https://'])
                                                ? $item->image
                                                : asset(ltrim($item->image,'/')))
                                            : null;
                                    @endphp
                                    @if($thumbSrc)
                                        <img src="{{ $thumbSrc }}" class="product-thumb" alt="{{ $item->name }}"
                                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
                                        <div class="product-thumb" style="display:none; align-items:center; justify-content:center; font-size:18px;">📦</div>
                                    @else
                                        <div class="product-thumb" style="display:flex; align-items:center; justify-content:center; font-size:18px;">📦</div>
                                    @endif
                                    <span style="font-weight:600;">{{ $item->name }}</span>
                                </div>
                            </td>

                            {{-- Unit price — strike-through if discounted --}}
                            <td>
                                @if($hasDiscount)
                                    <span style="text-decoration:line-through; color:rgba(255,255,255,0.35); font-size:12px;">
                                        ${{ number_format($item->price, 2) }}
                                    </span>
                                @else
                                    ${{ number_format($item->price, 2) }}
                                @endif
                            </td>

                            {{-- Discounted unit price column --}}
                            @if($hasDiscount)
                            <td style="color:#4ade80; font-weight:700;">
                                ${{ number_format($discountedUnit, 2) }}
                                <div style="font-size:10px; color:rgba(74,222,128,0.6); margin-top:1px;">
                                    −{{ round($discountRate * 100, 1) }}%
                                </div>
                            </td>
                            @endif

                            <td>×{{ $item->qty }}</td>

                            {{-- Line total: discounted if applicable --}}
                            <td style="color:#F97316; font-weight:700;">
                                ${{ number_format($hasDiscount ? $discountedLine : $lineTotal, 2) }}
                                @if($hasDiscount)
                                <div style="font-size:10px; text-decoration:line-through; color:rgba(255,255,255,0.25);">
                                    ${{ number_format($lineTotal, 2) }}
                                </div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>{{-- /left --}}

    {{-- ══ RIGHT COLUMN ═════════════════════════════════════════════════════════ --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- Confirm Delivery card --}}
        @if(!$order->delivery_confirmed_at && in_array($order->status, ['shipped','processing','confirmed']))
        <div class="card" style="border-color:rgba(34,197,94,0.3); background:rgba(34,197,94,0.04);">
            <div class="card-body" style="text-align:center;">
                <div style="font-size:36px; margin-bottom:8px;">📦</div>
                <div style="font-weight:700; color:#3b82f6; font-size:16px; margin-bottom:6px; letter-spacing:1px;">
                    CONFIRM &amp; PROCESS
                </div>
                <div style="color:rgba(255,255,255,0.45); font-size:13px; margin-bottom:18px;">
                    Move order to <strong style="color:#3b82f6;">Processing</strong> → Shipped → Delivered.
                </div>
                <button onclick="openPopup('confirm-delivery')" style="
                    background:linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; font-weight:700;
                    width:100%; border:none; padding:13px; border-radius:10px; font-size:15px;
                    letter-spacing:1px; cursor:pointer; font-family:Rajdhani,sans-serif;
                    box-shadow:0 4px 20px rgba(59,130,246,0.35); transition:all .2s;
                " onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                    ⚙️ START PROCESSING
                </button>
            </div>
        </div>

        @elseif($order->delivery_confirmed_at)
        <div class="card" style="border-color:rgba(34,197,94,0.3); background:rgba(34,197,94,0.04);">
            <div class="card-body" style="text-align:center;">
                <div style="font-size:32px; margin-bottom:8px;">✅</div>
                <div style="font-weight:700; color:#22c55e; font-size:15px;">Delivery Confirmed</div>
                <div style="color:rgba(255,255,255,0.35); font-size:12px; margin-top:4px;">
                    {{ $order->delivery_confirmed_at->setTimezone('Asia/Phnom_Penh')->format('d M Y, H:i') }} (ICT)
                </div>
            </div>
        </div>
        @endif

        {{-- Update Status — only for roles with orders_edit permission --}}
        @php
            $_editRole = Auth::guard('admin')->user()?->role ?? 'viewer';
            $_editKey  = "perm_{$_editRole}_orders_edit";
            $_editDefs = ['admin_orders_edit'=>'1','editor_orders_edit'=>'0','viewer_orders_edit'=>'0'];
            $_canEdit  = $_editRole === 'superadmin'
                || (\App\Models\AdminSetting::get($_editKey, $_editDefs["{$_editRole}_orders_edit"] ?? '0') === '1');
        @endphp
        @if($_canEdit)
        <div class="card">
            <div class="card-header">
                <span class="card-title">UPDATE STATUS</span>
            </div>
            <div class="card-body">
                @php
                    $statusMeta = [
                        'pending'    => ['icon'=>'⏳','color'=>'#eab308','label'=>'PENDING'],
                        'confirmed'  => ['icon'=>'✅','color'=>'#22c55e','label'=>'CONFIRMED'],
                        'processing' => ['icon'=>'⚙️','color'=>'#3b82f6','label'=>'PROCESSING'],
                        'shipped'    => ['icon'=>'🚚','color'=>'#a78bfa','label'=>'SHIPPED'],
                        'delivered'  => ['icon'=>'📦','color'=>'#F97316','label'=>'DELIVERED'],
                        'cancelled'  => ['icon'=>'❌','color'=>'#ef4444','label'=>'CANCELLED'],
                    ];
                @endphp
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    @foreach($statusMeta as $key => $meta)
                    @php $isCurrentStatus = $order->status === $key; @endphp
                    <button onclick="openStatusPopup('{{ $key }}')"
                        @if($isCurrentStatus) disabled @endif
                        style="
                        display:flex; align-items:center; gap:7px;
                        padding:9px 12px; border-radius:10px; font-family:Rajdhani,sans-serif;
                        font-size:12px; font-weight:700; letter-spacing:1px;
                        cursor:{{ $isCurrentStatus ? 'default' : 'pointer' }};
                        border: 1.5px solid {{ $isCurrentStatus ? $meta['color'] : 'rgba(255,255,255,0.1)' }};
                        background: {{ $isCurrentStatus ? 'rgba(255,255,255,0.05)' : 'rgba(255,255,255,0.03)' }};
                        color: {{ $isCurrentStatus ? $meta['color'] : 'rgba(255,255,255,0.45)' }};
                        opacity:{{ $isCurrentStatus ? '1' : '.85' }};
                        transition:all .15s;
                        box-shadow: {{ $isCurrentStatus ? '0 0 10px '.$meta['color'].'44' : 'none' }};
                    "
                    onmouseover="if(!this.disabled){ this.style.borderColor='{{ $meta['color'] }}'; this.style.color='{{ $meta['color'] }}'; this.style.background='rgba(255,255,255,0.06)'; }"
                    onmouseout="if(!this.disabled){ this.style.borderColor='rgba(255,255,255,0.1)'; this.style.color='rgba(255,255,255,0.45)'; this.style.background='rgba(255,255,255,0.03)'; }">
                        <span style="font-size:14px;">{{ $meta['icon'] }}</span>
                        {{ $meta['label'] }}
                        @if($isCurrentStatus)
                            <span style="margin-left:auto; width:7px; height:7px; border-radius:50%;
                                background:{{ $meta['color'] }}; box-shadow:0 0 6px {{ $meta['color'] }};"></span>
                        @endif
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
        @endif {{-- orders_edit permission --}}

        {{-- Order Summary --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">ORDER SUMMARY</span>
            </div>
            <div class="card-body">
                <div style="display:flex; flex-direction:column; gap:12px;">
                    {{-- Subtotal --}}
                    <div style="display:flex; justify-content:space-between; font-size:13px;">
                        <span style="color:rgba(255,255,255,0.4);">Subtotal</span>
                        <span>${{ number_format($order->subtotal ?? $order->total, 2) }}</span>
                    </div>

                    {{-- Discount — show code + amount, handle both code-based and public discounts --}}
                    @if($order->discount_amount > 0)
                    <div style="display:flex; justify-content:space-between; font-size:13px;">
                        <span style="color:rgba(255,255,255,0.4);">
                            Discount
                            @if($order->discount_code)
                                <span style="font-family:monospace; font-size:11px; background:rgba(74,222,128,0.1);
                                    border:1px solid rgba(74,222,128,0.25); border-radius:4px; padding:1px 6px;
                                    color:#4ade80; margin-left:4px;">{{ $order->discount_code }}</span>
                            @else
                                <span style="font-size:11px; color:rgba(74,222,128,0.6); margin-left:4px;">(auto)</span>
                            @endif
                        </span>
                        <span style="color:#22c55e; font-weight:700;">−${{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                    @endif

                    {{-- Delivery --}}
                    @if($order->delivery > 0)
                    <div style="display:flex; justify-content:space-between; font-size:13px;">
                        <span style="color:rgba(255,255,255,0.4);">Delivery</span>
                        <span>${{ number_format($order->delivery, 2) }}</span>
                    </div>
                    @endif

                    {{-- Tax --}}
                    @if($order->tax > 0)
                    <div style="display:flex; justify-content:space-between; font-size:13px;">
                        <span style="color:rgba(255,255,255,0.4);">Tax</span>
                        <span>${{ number_format($order->tax, 2) }}</span>
                    </div>
                    @endif

                    <div style="border-top:1px solid rgba(255,255,255,0.07); padding-top:12px;
                                display:flex; justify-content:space-between; font-weight:700;">
                        <span>TOTAL</span>
                        <span style="color:#F97316; font-size:18px;">${{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Shipping Address --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">SHIPPING ADDRESS</span>
                @if($order->location)
                <span style="font-size:11px; color:#F97316; letter-spacing:1px;">
                    📌 SAVED #{{ $order->location->id }}
                    @if($order->location->is_default) · DEFAULT @endif
                </span>
                @endif
            </div>
            <div class="card-body">
                @php
                    $name    = $order->location?->name    ?? ($order->shipping['name']    ?? '—');
                    $phone   = $order->location?->phone   ?? ($order->shipping['phone']   ?? '—');
                    $address = $order->location?->address ?? ($order->shipping['address'] ?? '—');
                    $city    = $order->location?->city    ?? ($order->shipping['city']    ?? '');
                    $note    = $order->location?->note    ?? ($order->shipping['note']    ?? '');
                @endphp
                <div style="display:flex; flex-direction:column; gap:12px;">
                    @foreach([['👤','NAME',$name,'#fff'],['📞','PHONE',$phone,'#F97316'],['📍','ADDRESS',$address.($city ? "\n".$city : ''),'rgba(255,255,255,0.85)']] as [$icon,$label,$val,$color])
                    <div style="display:flex; align-items:flex-start; gap:10px;">
                        <span style="font-size:16px;">{{ $icon }}</span>
                        <div>
                            <div style="font-size:10px; letter-spacing:2px; color:rgba(255,255,255,0.3); margin-bottom:2px;">{{ $label }}</div>
                            <div style="font-weight:700; color:{{ $color }}; font-size:13px; line-height:1.5; white-space:pre-line;">{{ $val }}</div>
                        </div>
                    </div>
                    @endforeach
                    @if($note)
                    <div style="display:flex; align-items:flex-start; gap:10px;">
                        <span style="font-size:16px;">📝</span>
                        <div>
                            <div style="font-size:10px; letter-spacing:2px; color:rgba(255,255,255,0.3); margin-bottom:2px;">NOTE</div>
                            <div style="color:rgba(255,255,255,0.5); font-size:13px; font-style:italic;">{{ $note }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- /right --}}
</div>

{{-- ══════════════════════════════════════════════════════════════════════════════
     POPUP MODALS
══════════════════════════════════════════════════════════════════════════════ --}}

{{-- Confirm Delivery Popup --}}
<div id="popup-confirm-delivery" class="popup-overlay" onclick="if(event.target===this) closePopup('confirm-delivery')">
    <div class="popup-box" id="popup-confirm-delivery-box">
        <div style="text-align:center; margin-bottom:20px;">
            <div style="
                width:80px; height:80px; border-radius:50%; margin:0 auto 12px;
                background:linear-gradient(135deg,#22c55e,#16a34a);
                display:flex; align-items:center; justify-content:center;
                font-size:40px; box-shadow:0 0 32px rgba(34,197,94,0.4);
                animation:popIn .5s cubic-bezier(0.34,1.56,0.64,1);
            ">📦</div>
            <div style="font-size:22px; font-weight:900; color:#3b82f6; letter-spacing:2px; font-family:Rajdhani,sans-serif;">
                CONFIRM &amp; PROCESS
            </div>
            <div style="color:rgba(255,255,255,0.45); font-size:13px; margin-top:6px;">
                Order <strong style="color:#F97316;">#{{ $order->order_id }}</strong> will move to
                <strong style="color:#3b82f6;">Processing</strong> status.
            </div>
        </div>

        {{-- Step flow indicator --}}
        <div style="background:rgba(59,130,246,0.06); border:1px solid rgba(59,130,246,0.2); border-radius:12px; padding:14px 16px; margin-bottom:20px;">
            <div style="font-size:10px; color:rgba(255,255,255,0.35); letter-spacing:2px; font-weight:700; margin-bottom:10px;">NEXT STEPS</div>
            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                @foreach(['Confirmed' => '#22c55e', 'Processing' => '#3b82f6', 'Shipped' => '#a78bfa', 'Delivered' => '#F97316'] as $stepName => $stepColor)
                @php
                    $stepActive = $stepName === 'Processing';
                    $stepDone   = in_array($stepName, ['Confirmed']);
                @endphp
                <div style="display:flex; align-items:center; gap:4px;">
                    <div style="padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; letter-spacing:0.5px;
                        background: {{ $stepActive ? $stepColor.'22' : ($stepDone ? 'rgba(34,197,94,0.08)' : 'rgba(255,255,255,0.04)') }};
                        border: 1px solid {{ $stepActive ? $stepColor : ($stepDone ? 'rgba(34,197,94,0.2)' : 'rgba(255,255,255,0.08)') }};
                        color: {{ $stepActive ? $stepColor : ($stepDone ? '#22c55e' : 'rgba(255,255,255,0.3)') }};">
                        {{ $stepDone ? '✓ ' : '' }}{{ $stepName }}{{ $stepActive ? ' ◀ NEXT' : '' }}
                    </div>
                    @if($stepName !== 'Delivered') <span style="color:rgba(255,255,255,0.2); font-size:10px;">›</span> @endif
                </div>
                @endforeach
            </div>
        </div>

        <div style="display:flex; gap:10px;">
            <button onclick="closePopup('confirm-delivery')" class="popup-btn-cancel">CANCEL</button>
            {{-- FIX: was confirm-delivery (sets delivered directly) → now orders.status + processing --}}
            <form method="POST" action="{{ route('dashboard.orders.status', $order) }}" style="flex:2;">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="processing">
                <button type="submit" class="popup-btn-confirm" style="background:linear-gradient(135deg,#3b82f6,#2563eb); width:100%;">
                    ⚙️ YES, START PROCESSING
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Status Change Popups --}}
@php
    $statusMeta2 = [
        'pending'    => ['icon'=>'⏳','color'=>'#eab308','gradient'=>'linear-gradient(135deg,#eab308,#ca8a04)','label'=>'PENDING',    'msg'=>'Order is waiting to be confirmed.'],
        'confirmed'  => ['icon'=>'✅','color'=>'#22c55e','gradient'=>'linear-gradient(135deg,#22c55e,#16a34a)','label'=>'CONFIRMED',  'msg'=>'Order has been confirmed and will be processed.'],
        'processing' => ['icon'=>'⚙️','color'=>'#3b82f6','gradient'=>'linear-gradient(135deg,#3b82f6,#2563eb)','label'=>'PROCESSING', 'msg'=>'Order is currently being prepared.'],
        'shipped'    => ['icon'=>'🚚','color'=>'#a78bfa','gradient'=>'linear-gradient(135deg,#a78bfa,#7c3aed)','label'=>'SHIPPED',    'msg'=>'Order has been dispatched to the customer.'],
        'delivered'  => ['icon'=>'📦','color'=>'#F97316','gradient'=>'linear-gradient(135deg,#F97316,#ea580c)','label'=>'DELIVERED',  'msg'=>'Order has been delivered successfully.'],
        'cancelled'  => ['icon'=>'❌','color'=>'#ef4444','gradient'=>'linear-gradient(135deg,#ef4444,#dc2626)','label'=>'CANCELLED',  'msg'=>'This order will be cancelled and cannot be undone.'],
    ];
@endphp

@foreach($statusMeta2 as $key => $meta)
@if($order->status !== $key)
<div id="popup-status-{{ $key }}" class="popup-overlay" onclick="if(event.target===this) closeStatusPopup('{{ $key }}')">
    <div class="popup-box" id="popup-status-{{ $key }}-box">
        <div style="text-align:center; margin-bottom:20px;">
            <div style="
                width:72px; height:72px; border-radius:50%; margin:0 auto 14px;
                background:{{ $meta['gradient'] }};
                display:flex; align-items:center; justify-content:center; font-size:34px;
                box-shadow:0 0 28px {{ $meta['color'] }}55;
                animation:popIn .45s cubic-bezier(0.34,1.56,0.64,1);
            ">{{ $meta['icon'] }}</div>
            <div style="font-size:20px; font-weight:900; color:{{ $meta['color'] }}; letter-spacing:2px; font-family:Rajdhani,sans-serif;">
                SET TO {{ $meta['label'] }}
            </div>
            <div style="color:rgba(255,255,255,0.4); font-size:13px; margin-top:6px;">
                Order <strong style="color:#F97316;">#{{ $order->order_id }}</strong>
            </div>
        </div>

        <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:14px 16px; margin-bottom:20px;">
            <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:8px;">
                <span style="color:rgba(255,255,255,0.4);">Current status</span>
                <span class="badge badge-{{ $order->status }}" style="font-size:11px;">{{ strtoupper($order->status) }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:13px;">
                <span style="color:rgba(255,255,255,0.4);">New status</span>
                <span style="font-weight:800; color:{{ $meta['color'] }}; font-size:13px;">{{ $meta['icon'] }} {{ $meta['label'] }}</span>
            </div>
            <div style="margin-top:10px; font-size:12px; color:rgba(255,255,255,0.4); border-top:1px solid rgba(255,255,255,0.07); padding-top:10px;">
                {{ $meta['msg'] }}
            </div>
        </div>

        @if($key === 'cancelled')
        <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:10px; padding:11px 14px; margin-bottom:16px; font-size:12px; color:rgba(255,255,255,0.5); display:flex; gap:8px;">
            <span>⚠️</span> <span>Cancellation cannot be undone. Stock will be restored if applicable.</span>
        </div>
        @endif

        <div style="display:flex; gap:10px;">
            <button onclick="closeStatusPopup('{{ $key }}')" class="popup-btn-cancel">CANCEL</button>
            <form method="POST" action="{{ route('dashboard.orders.status', $order) }}" style="flex:2;">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="{{ $key }}">
                <button type="submit" class="popup-btn-confirm" style="background:{{ $meta['gradient'] }}; width:100%;">
                    {{ $meta['icon'] }} CONFIRM
                </button>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

{{-- ── Styles ─────────────────────────────────────────────────────────────────── --}}
<style>
@keyframes stepPulse {
    0%,100% { box-shadow: 0 0 8px rgba(249,115,22,0.3); transform: scale(1); }
    50%      { box-shadow: 0 0 24px rgba(249,115,22,0.6); transform: scale(1.08); }
}
@keyframes slideDown {
    from { opacity:0; transform:translateX(-50%) translateY(-20px); }
    to   { opacity:1; transform:translateX(-50%) translateY(0); }
}
@keyframes fadeOut {
    to { opacity:0; transform:translateX(-50%) translateY(-10px); }
}
@keyframes popIn {
    0%   { transform:scale(0) rotate(-10deg); }
    60%  { transform:scale(1.18) rotate(3deg); }
    100% { transform:scale(1) rotate(0); }
}
@keyframes overlayIn { from{opacity:0} to{opacity:1} }
@keyframes boxIn {
    from { opacity:0; transform:scale(0.88) translateY(24px); }
    to   { opacity:1; transform:scale(1) translateY(0); }
}
@keyframes boxOut {
    from { opacity:1; transform:scale(1); }
    to   { opacity:0; transform:scale(0.92) translateY(16px); }
}

.popup-overlay {
    display:none; position:fixed; inset:0; z-index:9000;
    background:rgba(0,0,0,0.7); backdrop-filter:blur(6px);
    align-items:center; justify-content:center; padding:20px;
}
.popup-overlay.open {
    display:flex;
    animation:overlayIn .2s ease;
}
.popup-box {
    background:linear-gradient(145deg,#1a1a2e,#16213e,#0f3460);
    border:1px solid rgba(255,255,255,0.1);
    border-radius:20px; padding:28px 24px;
    width:100%; max-width:420px;
    box-shadow:0 24px 64px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.05);
    animation:boxIn .3s cubic-bezier(0.34,1.2,0.64,1);
    font-family:Rajdhani,sans-serif;
}
.popup-box.closing { animation:boxOut .25s ease forwards; }

.popup-btn-cancel {
    flex:1; padding:12px; border-radius:10px;
    border:1.5px solid rgba(255,255,255,0.12);
    background:rgba(255,255,255,0.06); color:rgba(255,255,255,0.6);
    font-family:Rajdhani,sans-serif; font-size:14px; font-weight:700;
    letter-spacing:1px; cursor:pointer; transition:all .15s;
}
.popup-btn-cancel:hover { background:rgba(255,255,255,0.1); color:#fff; }
.popup-btn-confirm {
    flex:2; padding:12px; border-radius:10px; border:none;
    color:#fff; font-family:Rajdhani,sans-serif; font-size:14px; font-weight:700;
    letter-spacing:1px; cursor:pointer; transition:transform .15s;
    box-shadow:0 4px 16px rgba(0,0,0,0.3);
}
.popup-btn-confirm:hover { transform:scale(1.02); }

/* ── Responsive order-show layout ───────────────────────────────── */
@media (max-width: 960px) {
    div[style*="grid-template-columns:1fr 340px"] {
        grid-template-columns: 1fr !important;
    }
}
/* Order info grid: 2-col → 1-col on mobile */
@media (max-width: 540px) {
    div[style*="grid-template-columns:1fr 1fr; gap:16px"] {
        grid-template-columns: 1fr !important;
    }
    /* Status buttons: 2-col → 1-col */
    div[style*="grid-template-columns:1fr 1fr; gap:8px"] {
        grid-template-columns: 1fr !important;
    }
    /* Table horizontal scroll */
    .table-wrap { overflow-x: auto; }
    /* Timeline horizontal scroll */
    div[style*="min-width:420px"] {
        min-width: 340px !important;
    }
    /* Back button full width */
    a[href*="orders"].btn { display: block; text-align: center; }
    /* Popup box padding */
    .popup-box { padding: 20px 16px !important; }
}
@media (max-width: 400px) {
    div[style*="min-width:420px"] { min-width: 300px !important; }
}
</style>

{{-- ── Scripts ────────────────────────────────────────────────────────────────── --}}
<script>
function openPopup(id) {
    const el  = document.getElementById('popup-' + id);
    const box = document.getElementById('popup-' + id + '-box');
    if (!el) return;
    el.classList.add('open');
    if (box) box.classList.remove('closing');
}

function closePopup(id) {
    const el  = document.getElementById('popup-' + id);
    const box = document.getElementById('popup-' + id + '-box');
    if (!el || !box) return;
    box.classList.add('closing');
    setTimeout(() => { el.classList.remove('open'); box.classList.remove('closing'); }, 240);
}

function openStatusPopup(status)  { openPopup('status-'  + status); }
function closeStatusPopup(status) { closePopup('status-' + status); }

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.popup-overlay.open').forEach(el => {
            closePopup(el.id.replace('popup-', ''));
        });
    }
});
</script>

@endif
@endsection
