@extends('dashboard.layout')
@section('title', strtoupper(__('dashboard.nav.ordershow')) . ' #' . $order->order_id)

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
         display:flex;align-items:center;justify-content:center;font-size: var(--title-size);
         box-shadow:0 0 60px rgba(239,68,68,0.12);animation:lockPulse 2.5s ease-in-out infinite;">🔒</div>
    <div style="font-size: var(--title-size);font-weight:900;letter-spacing:3px;color:#ef4444;margin-bottom:8px;">ACCESS DENIED</div>
    <div style="font-size: var(--title-size);color:rgba(255,255,255,0.35);margin-bottom:32px;max-width:380px;line-height:1.6;">
        Your role does not have permission to access this module.<br>
        Contact a <span style="color:#F97316;font-weight:700;">Super Admin</span> to request access.
    </div>
    <div style="display:inline-flex;align-items:center;gap:10px;padding:12px 24px;border-radius:16px;
         margin-bottom:32px;background:{{ $_pRM['color'] }}12;border:1.5px solid {{ $_pRM['color'] }}40;">
        <span style="font-size: var(--title-size);">{{ $_pRM['icon'] }}</span>
        <div style="text-align:left;">
            <div style="font-size: var(--title-size);color:rgba(255,255,255,0.4);letter-spacing:2px;font-weight:700;">YOUR ROLE</div>
            <div style="font-size: var(--title-size);font-weight:800;color:{{ $_pRM['color'] }};letter-spacing:1px;">{{ strtoupper($_pRM['label']) }}</div>
        </div>
        <div style="width:1px;height:32px;background:rgba(255,255,255,0.1);margin:0 4px;"></div>
        <div style="text-align:left;">
            <div style="font-size: var(--title-size);color:rgba(255,255,255,0.4);letter-spacing:2px;font-weight:700;">MODULE</div>
            <div style="font-size: var(--title-size);font-weight:800;color:rgba(255,255,255,0.6);letter-spacing:1px;">{{ strtoupper(str_replace('_',' ','orders')) }}</div>
        </div>
    </div>
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);
         border-radius:16px;padding:20px 24px;margin-bottom:32px;max-width:480px;width:100%;">
        <div style="font-size: var(--title-size);color:rgba(255,255,255,0.3);letter-spacing:2px;font-weight:700;margin-bottom:16px;text-align:left;">YOUR ACCESS OVERVIEW</div>
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
                <span style="font-size: var(--title-size);{{ !$_fHas ? 'opacity:0.3;' : '' }}">{{ $_fIcon }}</span>
                <span style="font-size: var(--title-size);letter-spacing:1px;font-weight:700;
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
           font-size: var(--title-size);font-weight:700;letter-spacing:1px;box-shadow:0 4px 16px rgba(249,115,22,0.3);"
           onmouseover="this.style.background='#fb923c'" onmouseout="this.style.background='#F97316'">
            🏠 GO TO DASHBOARD
        </a>
        <a href="javascript:history.back()" style="display:inline-flex;align-items:center;gap:8px;
           padding:12px 24px;border-radius:12px;text-decoration:none;
           background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);
           color:rgba(255,255,255,0.6);font-size: var(--title-size);font-weight:700;letter-spacing:1px;"
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
    font-size: var(--title-size); font-weight:700; letter-spacing:1px;
    box-shadow:0 8px 32px rgba(34,197,94,0.4);
    display:flex; align-items:center; gap:10px;
    animation:slideDown .35s cubic-bezier(0.34,1.56,0.64,1);
">
    <span style="font-size: var(--title-size);">✅</span> {{ session('success') }}
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
    font-size: var(--title-size); font-weight:700; letter-spacing:1px;
    box-shadow:0 8px 32px rgba(239,68,68,0.4);
    display:flex; align-items:center; gap:10px;
    animation:slideDown .35s cubic-bezier(0.34,1.56,0.64,1);
">
    <span style="font-size: var(--title-size);">⚠️</span> {{ session('error') }}
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
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    {{-- Fulfillment type badge & Payment status --}}
                    @if(($order->fulfillment_type ?? 'delivery') === 'pickup')
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;
                            border-radius:20px;font-size: var(--title-size);font-weight:700;letter-spacing:1px;
                            background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.3);color:#22c55e;">
                            🏪 PICKUP
                        </span>
                    @else
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;
                            border-radius:20px;font-size: var(--title-size);font-weight:700;letter-spacing:1px;
                            background:rgba(167,139,250,0.12);border:1px solid rgba(167,139,250,0.3);color:#a78bfa;">
                            🚚 DELIVERY
                        </span>
                    @endif
                    <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;
                        border-radius:20px;font-size: var(--title-size);font-weight:700;letter-spacing:1px;
                        background:{{ ($order->payment_status ?? 'pending') === 'paid' ? 'rgba(34,197,94,0.12)' : 'rgba(239,68,68,0.12)' }};
                        border:1px solid {{ ($order->payment_status ?? 'pending') === 'paid' ? 'rgba(34,197,94,0.3)' : 'rgba(239,68,68,0.3)' }};
                        color:{{ ($order->payment_status ?? 'pending') === 'paid' ? '#22c55e' : '#ef4444' }};">
                        {{ strtoupper($order->payment_status ?? 'pending') }}
                    </span>
                    <span class="badge badge-{{ $order->status }}" style="font-size: var(--title-size);">
                        {{ strtoupper($order->status) }}
                    </span>
                </div>
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
                        <div style="font-size: var(--title-size); letter-spacing:2px; color:rgba(255,255,255,0.3); margin-bottom:5px;">
                            {{ strtoupper($label) }}
                        </div>
                        <div style="font-weight:700; color:{{ $label === 'Payment Status' && $value === 'PAID' ? '#22c55e' : '#fff' }};">
                            {{ $value }}
                        </div>
                    </div>
                    @endforeach

                    @if($order->delivery_date)
                    <div>
                        <div style="font-size: var(--title-size); letter-spacing:2px; color:rgba(255,255,255,0.3); margin-bottom:5px;">DELIVERY DATE</div>
                        <div style="font-weight:700; color:#F97316;">
                            🗓 {{ \Carbon\Carbon::parse($order->delivery_date)->format('d M Y') }}
                            @if($order->delivery_time_slot) · {{ $order->delivery_time_slot }} @endif
                        </div>
                    </div>
                    @endif

                    @if($order->delivery_confirmed_at)
                    <div>
                        <div style="font-size: var(--title-size); letter-spacing:2px; color:rgba(255,255,255,0.3); margin-bottom:5px;">CONFIRMED AT</div>
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
                @if($order->isPickup())
                    <span class="card-title">🏪 PICKUP TIMELINE</span>
                @else
                    <span class="card-title">🚚 DELIVERY TIMELINE</span>
                @endif
            </div>
            <div class="card-body">
                @php
                    // Pickup orders skip the "Shipped" step — item stays at store
                    if ($order->isPickup()) {
                        $steps   = ['pending','confirmed','processing','delivered'];
                        $labels  = ['Pending','Confirmed','Ready','Picked Up'];
                        $icons   = ['⏳','✅','📦','🏪'];
                        $colors  = ['#eab308','#22c55e','#3b82f6','#F97316'];
                    } else {
                        $steps   = ['pending','confirmed','processing','shipped','delivered'];
                        $labels  = ['Pending','Confirmed','Processing','Shipped','Delivered'];
                        $icons   = ['⏳','✅','⚙️','🚚','📦'];
                        $colors  = ['#eab308','#22c55e','#3b82f6','#a78bfa','#F97316'];
                    }
                    $current = array_search($order->status, $steps);
                    if ($current === false) $current = ($order->status === 'cancelled') ? -1 : 0;
                @endphp

                {{-- Cancelled banner --}}
                @if($order->status === 'cancelled')
                <div style="text-align:center; padding:20px; border-radius:12px;
                    background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2);">
                    <div style="font-size: var(--title-size); margin-bottom:6px;">❌</div>
                    <div style="font-size: var(--title-size); font-weight:800; color:#ef4444; letter-spacing:2px;">ORDER CANCELLED</div>
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
                                display:flex; align-items:center; justify-content:center; font-size: var(--title-size);
                                background: {{ $i < $current ? $colors[$i].'22' : ($i === $current ? $colors[$i] : 'rgba(255,255,255,0.06)') }};
                                border: 2px solid {{ $i <= $current ? $colors[$i] : 'rgba(255,255,255,0.1)' }};
                                box-shadow: {{ $i === $current ? '0 0 20px '.$colors[$i].'55' : 'none' }};
                                transition: all .5s ease;
                                {{ $i === $current ? 'animation:stepPulse 2s ease-in-out infinite;' : '' }}
                                position:relative; z-index:2;
                            ">
                                @if($i < $current)
                                    <span style="color:{{ $colors[$i] }}; font-size: var(--title-size);">✓</span>
                                @else
                                    {{ $icons[$i] }}
                                @endif
                            </div>
                            {{-- Step label --}}
                            <div style="margin-top:8px; font-size: var(--title-size); text-align:center; font-weight:700; letter-spacing:1px; line-height:1.3;
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
                <span style="color:rgba(255,255,255,0.4); font-size: var(--title-size);">
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
                                        <div class="product-thumb" style="display:none; align-items:center; justify-content:center; font-size: var(--title-size);">📦</div>
                                    @else
                                        <div class="product-thumb" style="display:flex; align-items:center; justify-content:center; font-size: var(--title-size);">📦</div>
                                    @endif
                                    <div>
                                        <div style="font-weight:600;">{{ $item->name }}</div>
                                        @if($item->warranty_start && $item->warranty_end)
                                        <div style="font-size: var(--title-size); color:#F97316; margin-top:3px;">
                                            🛡️ {{ $item->warranty_start->format('d M Y') }} → {{ $item->warranty_end->format('d M Y') }}
                                        </div>
                                        @elseif($item->warranty_start)
                                        <div style="font-size: var(--title-size); color:rgba(255,255,255,0.4); margin-top:3px;">
                                            🛡️ From {{ $item->warranty_start->format('d M Y') }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Unit price — strike-through if discounted --}}
                            <td>
                                @if($hasDiscount)
                                    <span style="text-decoration:line-through; color:rgba(255,255,255,0.35); font-size: var(--title-size);">
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
                                <div style="font-size: var(--title-size); color:rgba(74,222,128,0.6); margin-top:1px;">
                                    −{{ round($discountRate * 100, 1) }}%
                                </div>
                            </td>
                            @endif

                            <td>×{{ $item->qty }}</td>

                            {{-- Line total: discounted if applicable --}}
                            <td style="color:#F97316; font-weight:700;">
                                ${{ number_format($hasDiscount ? $discountedLine : $lineTotal, 2) }}
                                @if($hasDiscount)
                                <div style="font-size: var(--title-size); text-decoration:line-through; color:rgba(255,255,255,0.25);">
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

         {{-- ══ Shipping Address + Map (inside left column, below order items) ══ --}}
        @php
            $name    = $order->location?->name    ?? ($order->shipping['name']    ?? '—');
            $phone   = $order->location?->phone   ?? ($order->shipping['phone']   ?? '—');
            $address = $order->location?->address ?? ($order->shipping['address'] ?? '—');
            $city    = $order->location?->city    ?? ($order->shipping['city']    ?? '');
            $note    = $order->location?->note    ?? ($order->shipping['note']    ?? '');
            $mapLat  = $order->location?->lat
                     ?? ($order->shipping['lat']  ?? null)
                     ?? $order->getRawOriginal('delivery_lat');
            $mapLng  = $order->location?->lng
                     ?? ($order->shipping['lng']  ?? null)
                     ?? $order->getRawOriginal('delivery_lng');
            $mapAddr = $order->location?->map_address
                     ?? ($order->shipping['map_address'] ?? null)
                     ?? $order->getRawOriginal('delivery_map_address');
        @endphp

        <div class="card">
            <div class="card-header">
                @if(($order->fulfillment_type ?? 'delivery') === 'pickup')
                    <span class="card-title">🏪 STORE PICKUP — CUSTOMER INFO</span>
                @else
                    <span class="card-title">🚚 SHIPPING ADDRESS & DELIVERY MAP</span>
                @endif
                @if($order->location)
                <span style="font-size: var(--title-size); color:#F97316; letter-spacing:1px;">
                    📌 SAVED #{{ $order->location->id }}
                    @if($order->location->is_default) · DEFAULT @endif
                </span>
                @endif
            </div>
            <div class="card-body">

                {{-- Address info row --}}
                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; margin-bottom:{{ $mapLat && $mapLng ? '20px' : '0' }};">
                    <div style="display:flex; align-items:flex-start; gap:10px;">
                        <span style="font-size: var(--title-size);">👤</span>
                        <div>
                            <div style="font-size: var(--title-size); letter-spacing:2px; color:rgba(255,255,255,0.3); margin-bottom:2px;">NAME</div>
                            <div style="font-weight:700; color:#fff; font-size: var(--title-size);">{{ $name }}</div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:flex-start; gap:10px;">
                        <span style="font-size: var(--title-size);">📞</span>
                        <div>
                            <div style="font-size: var(--title-size); letter-spacing:2px; color:rgba(255,255,255,0.3); margin-bottom:2px;">PHONE</div>
                            <div style="font-weight:700; color:#F97316; font-size: var(--title-size);">{{ $phone }}</div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:flex-start; gap:10px;">
                        <span style="font-size: var(--title-size);">📍</span>
                        <div>
                            <div style="font-size: var(--title-size); letter-spacing:2px; color:rgba(255,255,255,0.3); margin-bottom:2px;">ADDRESS</div>
                            <div style="font-weight:700; color:rgba(255,255,255,0.85); font-size: var(--title-size); line-height:1.5;">
                                {{ $address }}{{ $city ? ', '.$city : '' }}
                            </div>
                        </div>
                    </div>
                    @if($note)
                    <div style="display:flex; align-items:flex-start; gap:10px;">
                        <span style="font-size: var(--title-size);">📝</span>
                        <div>
                            <div style="font-size: var(--title-size); letter-spacing:2px; color:rgba(255,255,255,0.3); margin-bottom:2px;">NOTE</div>
                            <div style="color:rgba(255,255,255,0.5); font-size: var(--title-size); font-style:italic;">{{ $note }}</div>
                        </div>
                    </div>
                    @endif
                        {{-- ── Print Receipt Button ──────────────────────────────────────── --}}
                    <button onclick="window.print()" style="
                        width:100%; padding:13px; border-radius:12px; border:1.5px solid rgba(255,255,255,0.12);
                        background:rgba(255,255,255,0.04); color:rgba(255,255,255,0.7);
                        font-family:Rajdhani,sans-serif; font-size: var(--title-size); font-weight:700;
                        letter-spacing:2px; cursor:pointer; transition:all .2s;
                        display:flex; align-items:center; justify-content:center; gap:8px;
                    " onmouseover="this.style.borderColor='#F97316';this.style.color='#F97316'"
                       onmouseout="this.style.borderColor='rgba(255,255,255,0.12)';this.style.color='rgba(255,255,255,0.7)'">
                        🖨️ PRINT RECEIPT
                    </button>
                </div>

                {{-- Map — only if coordinates exist --}}
                @if($mapLat && $mapLng)
                <div>
                    <div style="font-size: var(--title-size); letter-spacing:2px; color:rgba(255,255,255,0.3); font-weight:700; margin-bottom:8px; display:flex; align-items:center; gap:8px;">
                        📍 PINNED DELIVERY ROUTE
                        <span id="map-route-label" style="color:rgba(255,255,255,0.2); font-weight:400; font-size: var(--title-size);">Loading route...</span>
                    </div>
                    <div id="order-map" style="height:400px; border-radius:12px; overflow:hidden; border:1px solid rgba(255,255,255,0.08);"></div>
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-top:8px; flex-wrap:wrap; gap:8px;">
                        @if($mapAddr)
                        <div style="font-size: var(--title-size); color:rgba(255,255,255,0.4);">📍 {{ $mapAddr }}</div>
                        @endif
                        <div style="display:flex; gap:16px;">
                            <span style="font-size: var(--title-size); color:rgba(255,255,255,0.35); display:flex; align-items:center; gap:5px;">
                                <span style="width:10px;height:10px;border-radius:50%;background:#F97316;display:inline-block;"></span> Tronmatix Store
                            </span>
                            <span style="font-size: var(--title-size); color:rgba(255,255,255,0.35); display:flex; align-items:center; gap:5px;">
                                <span style="width:10px;height:10px;border-radius:50%;background:#3b82f6;display:inline-block;"></span> Customer
                            </span>
                        </div>
                    </div>
                </div>

                <script>
                @if($mapLat && $mapLng)
                (function(){
                    const STORE_LAT = 11.5629735, STORE_LNG = 104.8995165;
                    const USER_LAT  = {{ (float) $mapLat }};
                    const USER_LNG  = {{ (float) $mapLng }};
                    const KEY       = '{{ config("services.google.maps_key") }}';

                    function initMap(){
                        const g = window.google;

                        const map = new g.maps.Map(document.getElementById('order-map'), {
                            center: { lat: (STORE_LAT + USER_LAT) / 2, lng: (STORE_LNG + USER_LNG) / 2 },
                            zoom: 13,
                            styles: [
                                { elementType: 'geometry',          stylers: [{ color: '#1a1a2e' }] },
                                { elementType: 'labels.text.fill',  stylers: [{ color: '#8ec3b9' }] },
                                { elementType: 'labels.text.stroke',stylers: [{ color: '#1a1a2e' }] },
                                { featureType: 'road',              elementType: 'geometry',        stylers: [{ color: '#2d3561' }] },
                                { featureType: 'road',              elementType: 'geometry.stroke', stylers: [{ color: '#212a37' }] },
                                { featureType: 'road.highway',      elementType: 'geometry',        stylers: [{ color: '#3a4d8c' }] },
                                { featureType: 'water',             elementType: 'geometry',        stylers: [{ color: '#0f3460' }] },
                                { featureType: 'poi',               stylers: [{ visibility: 'off' }] },
                            ],
                            disableDefaultUI: true,
                            zoomControl: true,
                            gestureHandling: 'cooperative',
                        });

                        // Store pin — orange
                        new g.maps.Marker({
                            position: { lat: STORE_LAT, lng: STORE_LNG }, map,
                            title: '🏪 Tronmatix Computer Store',
                            icon: { path: g.maps.SymbolPath.CIRCLE, scale: 10, fillColor: '#F97316', fillOpacity: 1, strokeColor: '#fff', strokeWeight: 2.5 },
                            zIndex: 2,
                        });

                        // Customer pin — blue
                        new g.maps.Marker({
                            position: { lat: USER_LAT, lng: USER_LNG }, map,
                            title: 'Customer Location',
                            icon: { path: g.maps.SymbolPath.CIRCLE, scale: 10, fillColor: '#3b82f6', fillOpacity: 1, strokeColor: '#fff', strokeWeight: 2.5 },
                            zIndex: 2,
                        });

                        // Try Directions API for real road route
                        const directionsService  = new g.maps.DirectionsService();
                        const directionsRenderer = new g.maps.DirectionsRenderer({
                            suppressMarkers: true,
                            polylineOptions: {
                                strokeColor:   '#F97316',
                                strokeOpacity: 0.85,
                                strokeWeight:  4,
                            },
                        });
                        directionsRenderer.setMap(map);

                        directionsService.route({
                            origin:      { lat: STORE_LAT, lng: STORE_LNG },
                            destination: { lat: USER_LAT,  lng: USER_LNG  },
                            travelMode:  g.maps.TravelMode.DRIVING,
                        }, function(result, status) {
                            if (status === 'OK') {
                                directionsRenderer.setDirections(result);
                                const leg = result.routes[0]?.legs[0];
                                if (leg) {
                                    const label = document.getElementById('map-route-label');
                                    if (label) label.textContent = leg.distance.text + ' · ' + leg.duration.text;
                                }
                            } else {
                                // Directions API unavailable — fall back to dashed straight line
                                directionsRenderer.setMap(null);
                                new g.maps.Polyline({
                                    path: [{ lat: STORE_LAT, lng: STORE_LNG }, { lat: USER_LAT, lng: USER_LNG }],
                                    strokeColor: '#F97316', strokeOpacity: 0,
                                    icons: [{ icon: { path: 'M 0,-1 0,1', strokeOpacity: 1, scale: 3, strokeColor: '#F97316' }, offset: '0', repeat: '12px' }],
                                    map,
                                });
                                const b = new g.maps.LatLngBounds();
                                b.extend({ lat: STORE_LAT, lng: STORE_LNG });
                                b.extend({ lat: USER_LAT,  lng: USER_LNG  });
                                map.fitBounds(b, 60);
                                const label = document.getElementById('map-route-label');
                                if (label) label.textContent = '(straight-line — enable Directions API for road route)';
                            }
                        });
                    }

                    // Load Maps JS API with directions library
                    if (window.google?.maps?.DirectionsService) {
                        initMap();
                    } else if (window.google?.maps) {
                        initMap();
                    } else {
                        const existing = document.getElementById('google-maps-script');
                        if (existing) {
                            existing.addEventListener('load', initMap);
                            if (window.google?.maps) initMap();
                        } else {
                            const s = document.createElement('script');
                            s.id    = 'google-maps-script';
                            s.src   = 'https://maps.googleapis.com/maps/api/js?key=' + KEY + '&libraries=directions';
                            s.async = true;
                            s.onload = initMap;
                            document.head.appendChild(s);
                        }
                    }
                })();
                @endif
                </script>
                @endif

            </div>
        </div>

    </div>{{-- /left --}}

    {{-- ══ RIGHT COLUMN ═════════════════════════════════════════════════════════ --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- ── Smart next-action card ─────────────────────────────────────────────
             Advances: confirmed → processing → shipped → delivered.
             Shows ONE correct next button per status; never a generic "process" CTA.
        --}}
        @php
            // ── Smart next-action: pickup orders skip Shipped, go confirmed→processing→delivered
            $isPickupOrder = $order->isPickup();

            $nextActions = [
                'confirmed'  => [
                    'status'   => 'processing',
                    'icon'     => $isPickupOrder ? '📦' : '⚙️',
                    'label'    => $isPickupOrder ? 'MARK AS READY' : 'START PROCESSING',
                    'title'    => $isPickupOrder ? 'ORDER READY FOR PICKUP' : 'CONFIRM &amp; PROCESS',
                    'desc'     => $isPickupOrder
                        ? 'Mark order as <strong style="color:#3b82f6;">Ready</strong> — customer will be notified to come collect.'
                        : 'Move order to <strong style="color:#3b82f6;">Processing</strong> → Shipped → Delivered.',
                    'color'    => '#3b82f6',
                    'gradient' => 'linear-gradient(135deg,#3b82f6,#2563eb)',
                    'shadow'   => 'rgba(59,130,246,0.35)',
                    'border'   => 'rgba(59,130,246,0.3)',
                    'bg'       => 'rgba(59,130,246,0.04)',
                ],
            ];

            // Delivery: processing → shipped
            if (! $isPickupOrder) {
                $nextActions['processing'] = [
                    'status'   => 'shipped',
                    'icon'     => '🚚',
                    'label'    => 'SHIP ORDER',
                    'title'    => 'MARK AS SHIPPED',
                    'desc'     => 'Confirm the order has been <strong style="color:#a78bfa;">dispatched</strong> to the customer.',
                    'color'    => '#a78bfa',
                    'gradient' => 'linear-gradient(135deg,#a78bfa,#7c3aed)',
                    'shadow'   => 'rgba(167,139,250,0.35)',
                    'border'   => 'rgba(167,139,250,0.3)',
                    'bg'       => 'rgba(167,139,250,0.04)',
                ];
                $nextActions['shipped'] = [
                    'status'   => 'delivered',
                    'icon'     => '📦',
                    'label'    => 'CONFIRM DELIVERY',
                    'title'    => 'MARK AS DELIVERED',
                    'desc'     => 'Confirm the order has been <strong style="color:#22c55e;">delivered</strong> successfully.',
                    'color'    => '#22c55e',
                    'gradient' => 'linear-gradient(135deg,#22c55e,#16a34a)',
                    'shadow'   => 'rgba(34,197,94,0.35)',
                    'border'   => 'rgba(34,197,94,0.3)',
                    'bg'       => 'rgba(34,197,94,0.04)',
                ];
            } else {
                // Pickup: processing (Ready) → delivered (Picked Up)
                $nextActions['processing'] = [
                    'status'   => 'delivered',
                    'icon'     => '🏪',
                    'label'    => 'CONFIRM PICKUP',
                    'title'    => 'MARK AS PICKED UP',
                    'desc'     => 'Confirm the customer has <strong style="color:#22c55e;">collected</strong> their order at the store.',
                    'color'    => '#22c55e',
                    'gradient' => 'linear-gradient(135deg,#22c55e,#16a34a)',
                    'shadow'   => 'rgba(34,197,94,0.35)',
                    'border'   => 'rgba(34,197,94,0.3)',
                    'bg'       => 'rgba(34,197,94,0.04)',
                ];
            }

            $nextAction = $nextActions[$order->status] ?? null;
        @endphp

        @if($nextAction && !$order->delivery_confirmed_at)
        <div class="card" style="border-color:{{ $nextAction['border'] }}; background:{{ $nextAction['bg'] }};">
            <div class="card-body" style="text-align:center;">
                <div style="font-size: var(--title-size); margin-bottom:8px;">{{ $nextAction['icon'] }}</div>
                <div style="font-weight:700; color:{{ $nextAction['color'] }}; font-size: var(--title-size); margin-bottom:6px; letter-spacing:1px;">
                    {!! $nextAction['title'] !!}
                </div>
                <div style="color:rgba(255,255,255,0.45); font-size: var(--title-size); margin-bottom:18px;">
                    {!! $nextAction['desc'] !!}
                </div>
                <button onclick="openPopup('confirm-delivery')" style="
                    background:{{ $nextAction['gradient'] }}; color:#fff; font-weight:700;
                    width:100%; border:none; padding:13px; border-radius:10px; font-size: var(--title-size);
                    letter-spacing:1px; cursor:pointer; font-family:Rajdhani,sans-serif;
                    box-shadow:0 4px 20px {{ $nextAction['shadow'] }}; transition:all .2s;
                " onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                    {{ $nextAction['icon'] }} {{ $nextAction['label'] }}
                </button>
            </div>
        </div>

        @elseif($order->delivery_confirmed_at)
        <div class="card" style="border-color:rgba(34,197,94,0.3); background:rgba(34,197,94,0.04);">
            <div class="card-body" style="text-align:center;">
                <div style="font-size: var(--title-size); margin-bottom:8px;">✅</div>
                <div style="font-weight:700; color:#22c55e; font-size: var(--title-size);">Delivery Confirmed</div>
                <div style="color:rgba(255,255,255,0.35); font-size: var(--title-size); margin-top:4px;">
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
                || (AdminSetting::get($_editKey, $_editDefs["{$_editRole}_orders_edit"] ?? '0') === '1');
        @endphp
        @if($_canEdit)
        <div class="card">
            <div class="card-header">
                <span class="card-title">UPDATE STATUS</span>
            </div>
            <div class="card-body">
                @php
                    if ($order->isPickup()) {
                        $statusMeta = [
                            'pending'    => ['icon'=>'⏳','color'=>'#eab308','label'=>'PENDING'],
                            'confirmed'  => ['icon'=>'✅','color'=>'#22c55e','label'=>'CONFIRMED'],
                            'processing' => ['icon'=>'📦','color'=>'#3b82f6','label'=>'READY'],
                            'delivered'  => ['icon'=>'🏪','color'=>'#F97316','label'=>'PICKED UP'],
                            'cancelled'  => ['icon'=>'❌','color'=>'#ef4444','label'=>'CANCELLED'],
                        ];
                    } else {
                        $statusMeta = [
                            'pending'    => ['icon'=>'⏳','color'=>'#eab308','label'=>'PENDING'],
                            'confirmed'  => ['icon'=>'✅','color'=>'#22c55e','label'=>'CONFIRMED'],
                            'processing' => ['icon'=>'⚙️','color'=>'#3b82f6','label'=>'PROCESSING'],
                            'shipped'    => ['icon'=>'🚚','color'=>'#a78bfa','label'=>'SHIPPED'],
                            'delivered'  => ['icon'=>'📦','color'=>'#F97316','label'=>'DELIVERED'],
                            'cancelled'  => ['icon'=>'❌','color'=>'#ef4444','label'=>'CANCELLED'],
                        ];
                    }
                @endphp
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    @foreach($statusMeta as $key => $meta)
                    @php $isCurrentStatus = $order->status === $key; @endphp
                    <button onclick="openStatusPopup('{{ $key }}')"
                        @if($isCurrentStatus) disabled @endif
                        style="
                        display:flex; align-items:center; gap:7px;
                        padding:9px 12px; border-radius:10px; font-family:Rajdhani,sans-serif;
                        font-size: var(--title-size); font-weight:700; letter-spacing:1px;
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
                        <span style="font-size: var(--title-size);">{{ $meta['icon'] }}</span>
                        {{ $meta['label'] }}
                        @if($isCurrentStatus)
                            <span style="margin-left:auto; width:7px; height:7px; border-radius:50%;
                                background:{{ $meta['color'] }}; box-shadow:0 0 6px {{ $meta['color'] }};"></span>
                        @endif
                    </button>
                    @endforeach
                </div>
                {{-- Payment Verification Button --}}
                <div style="margin-top:16px; padding-top:16px; border-top:1px solid rgba(255,255,255,0.07);">
                    <form method="POST" action="{{ route('dashboard.orders.verify-payment', $order) }}">
                        @csrf
                        <button type="submit" style="
                            width: 100%; padding: 10px; border-radius: 10px; border: none;
                            background: #22c55e; color: #fff; font-weight: 700; font-family: Rajdhani,sans-serif;
                            font-size: var(--title-size); letter-spacing: 1px; cursor: pointer; display: flex;
                            align-items: center; justify-content: center; gap: 8px;
                        ">
                            💳 VERIFY PAYMENT
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif {{-- orders_edit permission --}}

        {{-- Payment Verification Card — visible if order status is pending and payment is not paid --}}
        @if($order->status === 'pending' && ($order->payment_status ?? 'pending') !== 'paid')
        <div class="card" style="border-color:rgba(34,197,94,0.3); background:rgba(34,197,94,0.04);">
            <div class="card-body">
                <form method="POST" action="{{ route('dashboard.orders.verify-payment', $order) }}">
                    @csrf
                    <button type="submit" style="
                        width: 100%; padding: 12px; border-radius: 10px; border: none;
                        background: #22c55e; color: #fff; font-weight: 700; font-family: Rajdhani,sans-serif;
                        font-size: var(--title-size); letter-spacing: 1px; cursor: pointer; display: flex;
                        align-items: center; justify-content: center; gap: 8px;
                    ">
                        💳 VERIFY PAYMENT
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Order Summary --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">ORDER SUMMARY</span>
            </div>
            <div class="card-body">
                <div style="display:flex; flex-direction:column; gap:12px;">
                    {{-- Subtotal --}}
                    <div style="display:flex; justify-content:space-between; font-size: var(--title-size);">
                        <span style="color:rgba(255,255,255,0.4);">Subtotal</span>
                        <span>${{ number_format($order->subtotal ?? $order->total, 2) }}</span>
                    </div>

                    {{-- Discount — show code + amount, handle both code-based and public discounts --}}
                    @if($order->discount_amount > 0)
                    <div style="display:flex; justify-content:space-between; font-size: var(--title-size);">
                        <span style="color:rgba(255,255,255,0.4);">
                            Discount
                            @if($order->discount_code)
                                <span style="font-family:monospace; font-size: var(--title-size); background:rgba(74,222,128,0.1);
                                    border:1px solid rgba(74,222,128,0.25); border-radius:4px; padding:1px 6px;
                                    color:#4ade80; margin-left:4px;">{{ $order->discount_code }}</span>
                            @else
                                <span style="font-size: var(--title-size); color:rgba(74,222,128,0.6); margin-left:4px;">(auto)</span>
                            @endif
                        </span>
                        <span style="color:#22c55e; font-weight:700;">−${{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                    @endif

                    {{-- Delivery --}}
                    @if($order->delivery > 0)
                    <div style="display:flex; justify-content:space-between; font-size: var(--title-size);">
                        <span style="color:rgba(255,255,255,0.4);">Delivery</span>
                        <span>${{ number_format($order->delivery, 2) }}</span>
                    </div>
                    @endif

                    {{-- Tax --}}
                    @if($order->tax > 0)
                    <div style="display:flex; justify-content:space-between; font-size: var(--title-size);">
                        <span style="color:rgba(255,255,255,0.4);">Tax</span>
                        <span>${{ number_format($order->tax, 2) }}</span>
                    </div>
                    @endif

                    <div style="border-top:1px solid rgba(255,255,255,0.07); padding-top:12px;
                                display:flex; justify-content:space-between; font-weight:700;">
                        <span>TOTAL</span>
                        <span style="color:#F97316; font-size: var(--title-size);">${{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Shipping Address / Pickup Contact --}}
        <div class="card">
            <div class="card-header">
                @if($order->isPickup())
                    <span class="card-title">🏪 PICKUP CONTACT</span>
                @else
                    <span class="card-title">SHIPPING ADDRESS</span>
                @endif
                @if($order->location)
                <span style="font-size: var(--title-size); color:#F97316; letter-spacing:1px;">
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
                        <span style="font-size: var(--title-size);">{{ $icon }}</span>
                        <div>
                            <div style="font-size: var(--title-size); letter-spacing:2px; color:rgba(255,255,255,0.3); margin-bottom:2px;">{{ $label }}</div>
                            <div style="font-weight:700; color:{{ $color }}; font-size: var(--title-size); line-height:1.5; white-space:pre-line;">{{ $val }}</div>
                        </div>
                    </div>
                    @endforeach
                    @if($note)
                    <div style="display:flex; align-items:flex-start; gap:10px;">
                        <span style="font-size: var(--title-size);">📝</span>
                        <div>
                            <div style="font-size: var(--title-size); letter-spacing:2px; color:rgba(255,255,255,0.3); margin-bottom:2px;">NOTE</div>
                            <div style="color:rgba(255,255,255,0.5); font-size: var(--title-size); font-style:italic;">{{ $note }}</div>
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

{{-- ── Confirm-delivery Popup (dynamic — content reflects $nextAction) ── --}}
@if($nextAction)
<div id="popup-confirm-delivery" class="popup-overlay" onclick="if(event.target===this) closePopup('confirm-delivery')">
    <div class="popup-box" id="popup-confirm-delivery-box">
        <div style="text-align:center; margin-bottom:20px;">
            <div style="
                width:80px; height:80px; border-radius:50%; margin:0 auto 12px;
                background:{{ $nextAction['gradient'] }};
                display:flex; align-items:center; justify-content:center;
                font-size: var(--title-size); box-shadow:0 0 32px {{ $nextAction['shadow'] }};
                animation:popIn .5s cubic-bezier(0.34,1.56,0.64,1);
            ">{{ $nextAction['icon'] }}</div>
            <div style="font-size: var(--title-size); font-weight:900; color:{{ $nextAction['color'] }}; letter-spacing:2px; font-family:Rajdhani,sans-serif;">
                {!! $nextAction['title'] !!}
            </div>
            <div style="color:rgba(255,255,255,0.45); font-size: var(--title-size); margin-top:6px;">
                Order <strong style="color:#F97316;">#{{ $order->order_id }}</strong> will move to
                <strong style="color:{{ $nextAction['color'] }};">{{ ucfirst($nextAction['status']) }}</strong> status.
            </div>
        </div>

        {{-- Step flow — highlight the target status --}}
        @php
            // Popup flow: pickup orders don't have 'shipped'
            $popupSteps = $order->isPickup()
                ? [
                    'pending'    => ['label'=>'Pending',    'color'=>'#eab308'],
                    'confirmed'  => ['label'=>'Confirmed',  'color'=>'#22c55e'],
                    'processing' => ['label'=>'Ready',      'color'=>'#3b82f6'],
                    'delivered'  => ['label'=>'Picked Up',  'color'=>'#F97316'],
                  ]
                : [
                    'pending'    => ['label'=>'Pending',    'color'=>'#eab308'],
                    'confirmed'  => ['label'=>'Confirmed',  'color'=>'#22c55e'],
                    'processing' => ['label'=>'Processing', 'color'=>'#3b82f6'],
                    'shipped'    => ['label'=>'Shipped',    'color'=>'#a78bfa'],
                    'delivered'  => ['label'=>'Delivered',  'color'=>'#F97316'],
                  ];
            $currentIdx = array_search($order->status, array_keys($popupSteps));
            $targetIdx  = array_search($nextAction['status'], array_keys($popupSteps));
        @endphp
        <div style="background:{{ $nextAction['color'] }}11; border:1px solid {{ $nextAction['color'] }}33; border-radius:12px; padding:14px 16px; margin-bottom:20px;">
            <div style="font-size: var(--title-size); color:rgba(255,255,255,0.35); letter-spacing:2px; font-weight:700; margin-bottom:10px;">FLOW</div>
            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                @foreach($popupSteps as $sKey => $sData)
                @php
                    $sIdx    = array_search($sKey, array_keys($popupSteps));
                    $sDone   = $sIdx < $currentIdx;
                    $sCurrent= $sKey === $order->status;
                    $sTarget = $sKey === $nextAction['status'];
                    $sFuture = $sIdx > $targetIdx;
                @endphp
                <div style="display:flex; align-items:center; gap:4px;">
                    <div style="padding:4px 10px; border-radius:20px; font-size: var(--title-size); font-weight:700; letter-spacing:0.5px;
                        background: {{ $sTarget ? $sData['color'].'22' : ($sDone||$sCurrent ? 'rgba(34,197,94,0.08)' : 'rgba(255,255,255,0.04)') }};
                        border: 1px solid {{ $sTarget ? $sData['color'] : ($sDone||$sCurrent ? 'rgba(34,197,94,0.25)' : 'rgba(255,255,255,0.07)') }};
                        color: {{ $sTarget ? $sData['color'] : ($sDone||$sCurrent ? '#22c55e' : 'rgba(255,255,255,0.25)') }};
                        opacity: {{ $sFuture ? '0.45' : '1' }};">
                        {{ ($sDone||$sCurrent) && !$sTarget ? '✓ ' : '' }}{{ $sData['label'] }}{{ $sTarget ? ' ◀' : '' }}
                    </div>
                    @if(!$loop->last)<span style="color:rgba(255,255,255,0.2);font-size: var(--title-size);">›</span>@endif
                </div>
                @endforeach
            </div>
        </div>

        <div style="display:flex; gap:10px;">
            <button onclick="closePopup('confirm-delivery')" class="popup-btn-cancel">CANCEL</button>
            <form method="POST" action="{{ route('dashboard.orders.status', $order) }}" style="flex:2;">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="{{ $nextAction['status'] }}">
                <button type="submit" class="popup-btn-confirm" style="background:{{ $nextAction['gradient'] }}; width:100%;">
                    {{ $nextAction['icon'] }} YES, {{ $nextAction['label'] }}
                </button>
            </form>
        </div>
    </div>
</div>
@endif

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
                display:flex; align-items:center; justify-content:center; font-size: var(--title-size);
                box-shadow:0 0 28px {{ $meta['color'] }}55;
                animation:popIn .45s cubic-bezier(0.34,1.56,0.64,1);
            ">{{ $meta['icon'] }}</div>
            <div style="font-size: var(--title-size); font-weight:900; color:{{ $meta['color'] }}; letter-spacing:2px; font-family:Rajdhani,sans-serif;">
                SET TO {{ $meta['label'] }}
            </div>
            <div style="color:rgba(255,255,255,0.4); font-size: var(--title-size); margin-top:6px;">
                Order <strong style="color:#F97316;">#{{ $order->order_id }}</strong>
            </div>
        </div>

        <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:14px 16px; margin-bottom:20px;">
            <div style="display:flex; justify-content:space-between; font-size: var(--title-size); margin-bottom:8px;">
                <span style="color:rgba(255,255,255,0.4);">Current status</span>
                <span class="badge badge-{{ $order->status }}" style="font-size: var(--title-size);">{{ strtoupper($order->status) }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size: var(--title-size);">
                <span style="color:rgba(255,255,255,0.4);">New status</span>
                <span style="font-weight:800; color:{{ $meta['color'] }}; font-size: var(--title-size);">{{ $meta['icon'] }} {{ $meta['label'] }}</span>
            </div>
            <div style="margin-top:10px; font-size: var(--title-size); color:rgba(255,255,255,0.4); border-top:1px solid rgba(255,255,255,0.07); padding-top:10px;">
                {{ $meta['msg'] }}
            </div>
        </div>

        @if($key === 'cancelled')
        <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:10px; padding:11px 14px; margin-bottom:16px; font-size: var(--title-size); color:rgba(255,255,255,0.5); display:flex; gap:8px;">
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

{{-- ══ Thermal Receipt (print only) ══════════════════════════════════════════ --}}
@php
    $rName    = $order->location?->name    ?? ($order->shipping['name']    ?? '—');
    $rPhone   = $order->location?->phone   ?? ($order->shipping['phone']   ?? '—');
    $rAddress = $order->location?->address ?? ($order->shipping['address'] ?? '—');
    $rCity    = $order->location?->city    ?? ($order->shipping['city']    ?? '');
    $rNote    = $order->location?->note    ?? ($order->shipping['note']    ?? '');
    $rHasDiscount   = $order->discount_amount > 0;
    $rItemsSubtotal = $order->items->sum(fn($i) => $i->price * $i->qty);
    $rDiscountRate  = ($rHasDiscount && $rItemsSubtotal > 0)
        ? min($order->discount_amount / $rItemsSubtotal, 1.0) : 0;
@endphp
<div id="thermal-receipt">
    <div class="tr-center tr-bold" style="font-size: var(--title-size);">TRONMATIX COMPUTER</div>
    <div class="tr-center" style="font-size: var(--title-size); margin-bottom:2px;">TronmatixComputer.com</div>
    <div class="tr-divider"></div>

    <div class="tr-row"><span>Order</span><span>#{{ $order->order_id }}</span></div>
    <div class="tr-row"><span>Date</span><span>{{ $order->created_at->setTimezone('+07:00')->format('d M Y H:i') }}</span></div>
    <div class="tr-row"><span>Payment</span><span>{{ strtoupper($order->payment_method) }}</span></div>
    <div class="tr-row"><span>Type</span><span>{{ $order->isPickup() ? '🏪 PICKUP' : '🚚 DELIVERY' }}</span></div>
    <div style="font-weight:700;">{{ $item->name }}</div>
    @if($item->warranty_start && $item->warranty_end)
    <div style="font-size: var(--title-size); color:#444;">
        Warranty: {{ $item->warranty_start->format('d M Y') }} - {{ $item->warranty_end->format('d M Y') }}
    </div>
    @elseif($item->warranty_start)
    <div style="font-size: var(--title-size); color:#444;">
        Warranty from: {{ $item->warranty_start->format('d M Y') }}
    </div>
    @endif
    <div class="tr-divider"></div>

    <div class="tr-bold" style="margin-bottom:6px; font-size: var(--title-size); letter-spacing:1px;">ITEMS</div>
    @foreach($order->items as $item)
    @php
        $rLineTotal      = $item->price * $item->qty;
        $rDiscountedLine = $rHasDiscount ? round($rLineTotal * (1 - $rDiscountRate), 2) : null;
        $rFinal          = $rHasDiscount ? $rDiscountedLine : $rLineTotal;
    @endphp
    <div style="margin-bottom:4px;">
        <div style="font-weight:700;">{{ $item->name }}</div>
        <div class="tr-row" style="font-size: var(--title-size); color:#555;">
            <span>${{ number_format($item->price,2) }} × {{ $item->qty }}</span>
            <span style="font-weight:700; color:#000;">${{ number_format($rFinal,2) }}</span>
        </div>
        @if($rHasDiscount)
        <div style="font-size: var(--title-size); color:#888; text-align:right;">
            (orig. ${{ number_format($rLineTotal,2) }})
        </div>
        @endif
    </div>
    @endforeach
    <div class="tr-divider"></div>

    @if($rHasDiscount)
    <div class="tr-row"><span>Subtotal</span><span>${{ number_format($order->subtotal ?? $order->total, 2) }}</span></div>
    <div class="tr-row"><span>Discount{{ $order->discount_code ? ' ('.$order->discount_code.')' : '' }}</span><span>−${{ number_format($order->discount_amount,2) }}</span></div>
    @endif
    @if($order->delivery > 0)
    <div class="tr-row"><span>Delivery</span><span>${{ number_format($order->delivery,2) }}</span></div>
    @endif
    @if($order->tax > 0)
    <div class="tr-row"><span>Tax</span><span>${{ number_format($order->tax,2) }}</span></div>
    @endif
    <div class="tr-row tr-bold" style="font-size: var(--title-size); margin-top:4px;">
        <span>TOTAL</span><span>${{ number_format($order->total,2) }}</span>
    </div>
    <div class="tr-divider"></div>

    <div class="tr-bold" style="margin-bottom:4px; font-size: var(--title-size); letter-spacing:1px;">
        {{ $order->isPickup() ? 'PICKUP' : 'DELIVERY' }} TO Customer INFO
    </div>
    <div style="font-size: var(--title-size); margin-bottom:2px;">👤 {{ $rName }}</div>
    <div style="font-size: var(--title-size); margin-bottom:2px;">📞 {{ $rPhone }}</div>
    <div style="font-size: var(--title-size); margin-bottom:2px;">📍 {{ $rAddress }}{{ $rCity ? ', '.$rCity : '' }}</div>
    @if($rNote)<div style="font-size: var(--title-size); color:#555; font-style:italic;">Note: {{ $rNote }}</div>@endif

    <div class="tr-divider"></div>
    <div class="tr-center tr-bold" style="font-size: var(--title-size);">Thank you for your order!</div>
    <div class="tr-center" style="font-size: var(--title-size); margin-top:2px; color:#555;">Status: {{ strtoupper($order->status) }}</div>
    <div style="margin-top:16px;"></div>
</div>

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
    font-family:Rajdhani,sans-serif; font-size: var(--title-size); font-weight:700;
    letter-spacing:1px; cursor:pointer; transition:all .15s;
}
.popup-btn-cancel:hover { background:rgba(255,255,255,0.1); color:#fff; }
.popup-btn-confirm {
    flex:2; padding:12px; border-radius:10px; border:none;
    color:#fff; font-family:Rajdhani,sans-serif; font-size: var(--title-size); font-weight:700;
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

#thermal-receipt { display: none; }

@media print {
    /* Hide everything */
    body > * { visibility: hidden !important; }

    /* Show only receipt */
    #thermal-receipt { visibility: visible !important; display: block !important; }
    #thermal-receipt * { visibility: visible !important; }

    #thermal-receipt {
        position: fixed !important;
        top: 0 !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        width: 72mm !important;
        margin: 0 !important;
        font-family: 'Courier New', monospace !important;
        font-size: var(--title-size) !important;
        color: #000 !important;
        background: #fff !important;
        padding: 4mm 2mm !important;
        line-height: 1.5 !important;
    }
    .tr-divider {
        display: block !important;
        border-top: 1px dashed #000 !important;
        margin: 6px 0 !important;
    }
    .tr-row {
        display: flex !important;
        justify-content: space-between !important;
        font-size: var(--title-size) !important;
    }
    .tr-bold  { font-weight: 700 !important; }
    .tr-center { text-align: center !important; }
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

@push('styles')
<style>
/* ── Orders Show – light theme ────────────────────────────────────────────── */
/* Timeline connector line */
[data-theme="light"] .timeline-line { background: rgba(15,23,42,0.08) !important; }
/* Section sub-cards */
[data-theme="light"] [style*="background:rgba(255,255,255,0.03)"] { background: rgba(15,23,42,0.025) !important; }
/* Muted labels */
[data-theme="light"] [style*="color:rgba(255,255,255,0.35)"] { color: rgba(15,23,42,0.40) !important; }
[data-theme="light"] [style*="color:rgba(255,255,255,0.3)"]  { color: rgba(15,23,42,0.35) !important; }
[data-theme="light"] [style*="color:rgba(255,255,255,0.5)"]  { color: rgba(15,23,42,0.55) !important; }
/* Status change select */
[data-theme="light"] .status-select,
[data-theme="light"] select.form-control {
    background: #F8FAFC !important;
    border-color: rgba(15,23,42,0.14) !important;
    color: #0F172A !important;
}
/* Confirm popup */
[data-theme="light"] .popup-box {
    background: #FFFFFF !important;
    border-color: rgba(15,23,42,0.10) !important;
    box-shadow: 0 20px 60px rgba(15,23,42,0.15) !important;
}
/* Product card within order */
[data-theme="light"] [style*="background:rgba(255,255,255,0.04)"] { background: rgba(15,23,42,0.03) !important; }
[data-theme="light"] [style*="border:1px solid rgba(255,255,255,0.06)"] { border-color: rgba(15,23,42,0.07) !important; }
[data-theme="light"] [style*="border:1px solid rgba(255,255,255,0.08)"] { border-color: rgba(15,23,42,0.08) !important; }
[data-theme="light"] [style*="border:1px solid rgba(255,255,255,0.1)"]  { border-color: rgba(15,23,42,0.10) !important; }
/* Divider lines */
[data-theme="light"] [style*="border-top:1px solid rgba(255,255,255"] { border-top-color: rgba(15,23,42,0.07) !important; }
</style>
@endpush

@endsection
