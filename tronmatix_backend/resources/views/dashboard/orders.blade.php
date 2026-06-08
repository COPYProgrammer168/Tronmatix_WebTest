@extends('dashboard.layout')
@section('title', strtoupper(__('dashboard.nav.orders')))

@section('content')

    @include('dashboard._permission_check', ['feature' => 'orders'])

@php $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false; @endphp

    @if(!$_permDenied)
        {{-- ── Status category tabs ──────────────────────────────────────────────────── --}}
        @php
            $tabs = [
                'all' => ['label' => 'ALL', 'icon' => '📋', 'color' => '#fff', 'dark' => '#111'],
                'pending' => ['label' => 'PENDING', 'icon' => '⏳', 'color' => '#eab308', 'dark' => '#111'],
                'confirmed' => ['label' => 'CONFIRMED', 'icon' => '✅', 'color' => '#22c55e', 'dark' => '#fff'],
                'processing' => ['label' => 'PROCESSING', 'icon' => '⚙️', 'color' => '#3b82f6', 'dark' => '#fff'],
                'shipped' => ['label' => 'SHIPPED', 'icon' => '🚚', 'color' => '#a78bfa', 'dark' => '#fff'],
                'delivered' => ['label' => 'DELIVERED', 'icon' => '📦', 'color' => '#F97316', 'dark' => '#fff'],
                'cancelled' => ['label' => 'CANCELLED', 'icon' => '❌', 'color' => '#ef4444', 'dark' => '#fff'],
            ];
            $activeTab = $status ?? 'all';
            $totalAll = $statusCounts->sum();
        @endphp

        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px; align-items:center;">
            @foreach ($tabs as $key => $tab)
                @php
                    $count = $key === 'all' ? $totalAll : $statusCounts[$key] ?? 0;
                    $isActive = $activeTab === $key || ($key === 'all' && !$activeTab);
                    $href = route(
                        'dashboard.orders',
                        array_filter(['status' => $key === 'all' ? null : $key, 'search' => $search ?: null]),
                    );
                @endphp
                <a href="{{ $href }}" {{ !$isActive ? 'class="order-status-tab-inactive"' : '' }}
                    style="
                    display:inline-flex; align-items:center; gap:6px;
                    padding:8px 16px; border-radius:30px; font-family:Rajdhani,sans-serif;
                    font-size: var(--title-size); font-weight:700; letter-spacing:1px; text-decoration:none;
                    transition:all 0.2s;
                    background: {{ $isActive ? $tab['color'] : 'var(--hover-bg)' }};
                    color:      {{ $isActive ? $tab['dark'] : 'var(--text-muted)' }};
                    border: 1.5px solid {{ $isActive ? $tab['color'] : 'var(--border-input)' }};
                    box-shadow: {{ $isActive ? '0 0 14px ' . $tab['color'] . '66' : 'none' }};
                "
                    onmouseover="this.style.opacity='.82'" onmouseout="this.style.opacity='1'">
                    {{ $tab['icon'] }} {{ $tab['label'] }}
                    <span {{ !$isActive ? 'class="order-tab-count"' : '' }}
                        style="
                        background:{{ $isActive ? 'rgba(0,0,0,0.18)' : 'var(--border-input)' }};
                        color:{{ $isActive ? '#fff' : 'var(--text-muted)' }};
                        border-radius:20px; padding:0 8px; font-size: var(--title-size); font-weight:800; line-height:20px;
                    ">{{ $count }}</span>
                </a>
            @endforeach

            {{-- Search --}}
            <form method="GET" action="{{ route('dashboard.orders') }}"
                style="margin-left:auto; display:flex; gap:8px; align-items:center;">
                @if ($activeTab && $activeTab !== 'all')
                    <input type="hidden" name="status" value="{{ $activeTab }}">
                @endif
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search order ID or customer…"
                    class="orders-search-input"
                    style="background:rgba(255,255,255,0.07); border:1.5px solid rgba(255,255,255,0.12);
                   color:#fff; border-radius:10px; padding:8px 16px; font-size: var(--title-size);
                   font-family:Rajdhani,sans-serif; outline:none; width:230px; transition:border-color .2s;"
                    onfocus="this.style.borderColor='#F97316'" onblur="this.style.borderColor=''" />
                <button type="submit"
                    style="background:#F97316; color:#fff; border:none; border-radius:10px;
            padding:8px 16px; font-family:Rajdhani,sans-serif; font-size: var(--title-size); font-weight:700;
            cursor:pointer; letter-spacing:1px;">SEARCH</button>
                @if ($search)
                    <a href="{{ route('dashboard.orders', $activeTab && $activeTab !== 'all' ? ['status' => $activeTab] : []) }}"
                        class="orders-clear-btn"
                        style="background:rgba(255,255,255,0.07); color:rgba(255,255,255,0.5); border:1.5px solid rgba(255,255,255,0.1);
                  border-radius:10px; padding:8px 12px; font-size: var(--title-size); text-decoration:none;">✕</a>
                @endif
            </form>
        </div>

        {{-- ── Table ──────────────────────────────────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title" style="font-size: var(--title-size);">
                    @if ($activeTab && $activeTab !== 'all')
                        {{ strtoupper($activeTab) }} ORDERS
                    @else
                        ALL ORDERS
                    @endif
                </span>
                <span style="color:rgba(255,255,255,0.45); font-size: var(--title-size);">
                    {{ $orders->total() }} result{{ $orders->total() !== 1 ? 's' : '' }}
                </span>
                <div class="pagination-top" style="margin-left: auto;">
                    {{ $orders->links('dashboard.pagination') }}
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    {{-- ── Column widths ── --}}
                    <colgroup>
                        <col style="width:130px;"> {{-- ORDER ID --}}
                        <col style="width:110px;"> {{-- CUSTOMER --}}
                        <col style="width:150px;"> {{-- SHIPPING TO --}}
                        <col style="width:200px;"> {{-- ITEMS --}}
                        <col style="width:100px;"> {{-- SUBTOTAL --}}
                        <col style="width:120px;"> {{-- DISCOUNT --}}
                        <col style="width:100px;"> {{-- TOTAL --}}
                        <col style="width:80px;"> {{-- PAYMENT --}}
                        <col style="width:80px;"> {{-- PAY STATUS --}}
                        <col style="width:110px;"> {{-- STATUS --}}
                        <col style="width:120px;"> {{-- ORDER DATE --}}
                        <col style="width:130px;"> {{-- DELIVERY --}}
                    </colgroup>

                    <thead>
                        <tr>
                            <th>ORDER ID</th>
                            <th>CUSTOMER</th>
                            <th>SHIPPING TO</th>
                            <th>ITEMS</th>
                            <th>SUBTOTAL</th>
                            <th>DISCOUNT</th>
                            <th>TOTAL</th>
                            <th>PAYMENT</th>
                            <th>PAY STATUS</th>
                            <th>STATUS</th>
                            <th>ORDER DATE</th>
                            <th>DELIVERY</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($orders as $order)
                            @php
                                $shipName = $order->shipping['name'] ?? ($order->location?->name ?? '—');
                                $shipPhone = $order->shipping['phone'] ?? ($order->location?->phone ?? '');
                                $shipCity = $order->shipping['city'] ?? ($order->location?->city ?? '');
                                $payStatus = $order->payment_status ?? 'pending';
                                if ($order->payment_method === 'cash' && $payStatus === 'pending') {
                                    $payStatus = 'cash';
                                }
                                $allItems = $order->items;
                                $firstItem = $allItems->first();
                                $extraCount = $allItems->count() - 1;
                            @endphp

                            <tr style="animation:rowIn .3s ease both; animation-delay:{{ $loop->index * 20 }}ms;">
                                {{-- ORDER ID — sticky left, clickable --}}
                                <td>
                                    <a href="{{ route('dashboard.orders.show', $order) }}"
                                        style="color:#F97316; font-weight:700; font-family:monospace;
                                               font-size:var(--text-base); text-decoration:none; white-space:nowrap;"
                                        onmouseover="this.style.textDecoration='underline'"
                                        onmouseout="this.style.textDecoration='none'">
                                        {{ $order->order_id }}
                                    </a>
                                </td>

                                {{-- CUSTOMER --}}
                                <td style="font-weight:600; color:var(--text); white-space:nowrap;">
                                    {{ $order->user?->username ?? 'Guest' }}
                                </td>

                                {{-- SHIPPING TO --}}
                                <td>
                                    <div
                                        style="font-weight:700; color:var(--text); font-size:var(--text-sm); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:140px;">
                                        {{ $shipName }}
                                    </div>
                                    @if ($shipCity)
                                        <div style="font-size:var(--text-xs); color:var(--text-muted); margin-top:1px;">
                                            📍 {{ $shipCity }}
                                        </div>
                                    @endif
                                    @if ($shipPhone)
                                        <div
                                            style="font-size:var(--text-xs); color:#F97316; margin-top:1px; font-weight:600;">
                                            {{ $shipPhone }}
                                        </div>
                                    @endif
                                </td>

                                {{-- ITEMS — first item only + "+N more" badge --}}
                                <td>
                                    @if ($firstItem)
                                        @php
                                            $thumb = $firstItem->image
                                                ? (Str::startsWith($firstItem->image, ['http://', 'https://'])
                                                    ? $firstItem->image
                                                    : asset(ltrim($firstItem->image, '/')))
                                                : null;
                                        @endphp
                                        <div style="display:flex; align-items:center; gap:7px;">
                                            <div
                                                style="width:28px; height:28px; border-radius:5px; overflow:hidden; flex-shrink:0;
                                                        background:rgba(255,255,255,0.05); display:flex; align-items:center; justify-content:center;
                                                        border:1px solid rgba(255,255,255,0.07);">
                                                @if ($thumb)
                                                    <img src="{{ $thumb }}" alt="{{ $firstItem->name }}"
                                                        style="width:100%; height:100%; object-fit:contain;"
                                                        onerror="this.style.display='none';this.nextElementSibling.style.display='block'" />
                                                    <span style="display:none; font-size:11px;">📦</span>
                                                @else
                                                    <span style="font-size:11px;">📦</span>
                                                @endif
                                            </div>
                                            <div style="min-width:0;">
                                                <div
                                                    style="font-size:var(--text-xs); font-weight:600; color:var(--text);
                                                            white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:140px;">
                                                    {{ $firstItem->name }}
                                                    <span
                                                        style="color:#F97316; font-weight:700;">×{{ $firstItem->qty }}</span>
                                                </div>
                                                @if ($extraCount > 0)
                                                    <span class="items-more-badge">+{{ $extraCount }} more</span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span style="color:rgba(255,255,255,0.2);">—</span>
                                    @endif
                                </td>

                                {{-- SUBTOTAL --}}
                                <td style="font-weight:600; white-space:nowrap;">
                                    ${{ number_format($order->subtotal ?? $order->total, 2) }}
                                </td>

                                {{-- DISCOUNT --}}
                                <td>
                                    @if ($order->discount_amount > 0)
                                        @php $dBadge = $order->discount?->badge_config; @endphp
                                        @if ($dBadge && !empty($dBadge['text']))
                                            <div
                                                style="display:inline-flex; align-items:center; gap:4px; padding:2px 7px;
                                                        border-radius:12px; font-size:var(--text-xs); font-weight:800; letter-spacing:0.5px;
                                                        background:{{ $dBadge['bg'] ?? 'rgba(249,115,22,0.15)' }};
                                                        border:1px solid {{ $dBadge['border'] ?? 'rgba(249,115,22,0.4)' }};
                                                        color:{{ $dBadge['color'] ?? '#F97316' }};">
                                                {{ $dBadge['icon'] ?? '🏷️' }} {{ $dBadge['text'] }}
                                            </div>
                                        @elseif($order->discount_code)
                                            <span
                                                style="font-family:monospace; font-size:var(--text-xs); color:#4ade80; font-weight:700;
                                                         background:rgba(74,222,128,0.08); border:1px solid rgba(74,222,128,0.2);
                                                         border-radius:4px; padding:1px 6px;">
                                                {{ $order->discount_code }}
                                            </span>
                                        @else
                                            <span
                                                style="font-size:var(--text-xs); color:rgba(74,222,128,0.6); font-style:italic;">auto</span>
                                        @endif
                                        <div
                                            style="color:#4ade80; font-size:var(--text-xs); margin-top:2px; font-weight:700;">
                                            −${{ number_format($order->discount_amount, 2) }}
                                        </div>
                                    @else
                                        <span style="color:rgba(255,255,255,0.15);">—</span>
                                    @endif
                                </td>

                                {{-- TOTAL --}}
                                <td style="color:#F97316; font-weight:700; white-space:nowrap;">
                                    ${{ number_format($order->total, 2) }}
                                </td>

                                {{-- PAYMENT --}}
                                <td>
                                    <span class="badge badge-gray" style="font-size:var(--text-xs); white-space:nowrap;">
                                        {{ $order->payment_method === 'bakong' ? '📱 BAKONG' : '💵 CASH' }}
                                    </span>
                                </td>

                                {{-- PAY STATUS --}}
                                <td>
                                    @if ($payStatus === 'paid')
                                        <span class="badge badge-paid" style="font-size:var(--text-xs);"
                                            title="Ref: {{ $order->payment_ref ?? '—' }}">✅ PAID</span>
                                    @elseif($payStatus === 'cash')
                                        <span class="badge badge-gray" style="font-size:var(--text-xs);">💵 COD</span>
                                    @elseif($payStatus === 'manual_pending')
                                        <span class="badge"
                                            style="background:rgba(249,115,22,.12); color:#F97316;
                                              border:1px solid rgba(249,115,22,.3); font-size:var(--text-xs);">⚠️
                                            VERIFY</span>
                                    @elseif($payStatus === 'failed')
                                        <span class="badge badge-cancelled" style="font-size:var(--text-xs);">❌
                                            FAILED</span>
                                    @else
                                        <span class="badge"
                                            style="background:rgba(234,179,8,.12); color:#eab308;
                                              border:1px solid rgba(234,179,8,.3); font-size:var(--text-xs);">⏳
                                            PENDING</span>
                                    @endif
                                </td>

                                {{-- ORDER STATUS --}}
                                <td>
                                    @if (($order->fulfillment_type ?? 'delivery') === 'pickup' && $order->status === 'delivered')
                                        <span class="badge badge-confirmed"
                                            style="font-size:var(--text-xs); white-space:nowrap;">
                                            PICKED UP
                                        </span>
                                    @else
                                        <span class="badge badge-{{ $order->status }}"
                                            style="font-size:var(--text-xs); white-space:nowrap;">
                                            {{ strtoupper($order->status) }}
                                        </span>
                                    @endif
                                </td>

                                {{-- ORDER DATE --}}
                                <td style="white-space:nowrap;">
                                    <div style="color:var(--text); font-weight:600; font-size:var(--text-xs);">
                                        {{ $order->created_at->setTimezone('Asia/Phnom_Penh')->format('d M Y') }}
                                    </div>
                                    <div style="color:var(--text-muted); font-size:var(--text-xs); margin-top:1px;">
                                        🕐 {{ $order->created_at->setTimezone('Asia/Phnom_Penh')->format('H:i') }}
                                    </div>
                                </td>

                                {{-- DELIVERY --}}
                                <td style="white-space:nowrap;">
                                    @if (($order->fulfillment_type ?? 'delivery') === 'pickup')
                                        <span
                                            style="display:inline-flex; align-items:center; gap:4px; padding:2px 8px;
                                                     border-radius:20px; font-size:var(--text-xs); font-weight:700;
                                                     background:rgba(34,197,94,0.12); border:1px solid rgba(34,197,94,0.3); color:#22c55e;">
                                            🏪 PICKUP
                                        </span>
                                    @else
                                        <span
                                            style="display:inline-flex; align-items:center; gap:4px; padding:2px 8px;
                                                     border-radius:20px; font-size:var(--text-xs); font-weight:700;
                                                     background:rgba(167,139,250,0.12); border:1px solid rgba(167,139,250,0.3); color:#a78bfa;">
                                            🚚 DELIVERY
                                        </span>
                                    @endif
                                    @if ($order->delivery_date)
                                        <div
                                            style="font-size:var(--text-xs); font-weight:600; margin-top:3px;
                                                    color:{{ ($order->fulfillment_type ?? 'delivery') === 'pickup' ? '#22c55e' : '#a78bfa' }};">
                                            📅 {{ \Carbon\Carbon::parse($order->delivery_date)->format('d M Y') }}
                                        </div>
                                        @if ($order->delivery_time_slot)
                                            <div style="color:rgba(167,139,250,0.55); font-size:var(--text-xs);">
                                                🕐 {{ $order->delivery_time_slot }}
                                            </div>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" style="text-align:center; padding:60px; color:rgba(255,255,255,0.3);">
                                    <div style="font-size:32px; margin-bottom:10px;">📭</div>
                                    <div style="font-size:var(--text-md); font-weight:700; margin-bottom:4px;">No orders
                                        found</div>
                                    @if ($search)
                                        <div style="font-size:var(--text-sm);">No results for
                                            "<strong style="color:#F97316">{{ $search }}</strong>"
                                        </div>
                                    @elseif($activeTab && $activeTab !== 'all')
                                        <div style="font-size:var(--text-sm);">No {{ $activeTab }} orders yet</div>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @endif

    <style>
        @keyframes rowIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }
    </style>

    {{-- ── Real-time Search ────────────────────────────────────────────────────── --}}
    <script>
        let searchTimeout;
        document.getElementById('orderSearchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = this.value;
            const status = new URLSearchParams(window.location.search).get('status') || 'all';

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('dashboard.orders') }}?search=${encodeURIComponent(searchTerm)}&status=${encodeURIComponent(status)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newTableBody = doc.getElementById('orderTableBody');
                        if (newTableBody) {
                            document.getElementById('orderTableBody').innerHTML = newTableBody
                            .innerHTML;
                        }

                        // Update URL for consistency without page reload
                        const newUrl =
                            `{{ route('dashboard.orders') }}?search=${encodeURIComponent(searchTerm)}&status=${encodeURIComponent(status)}`;
                        window.history.pushState({
                            path: newUrl
                        }, '', newUrl);
                    })
                    .catch(err => console.error('Search failed:', err));
            }, 300); // 300ms debounce
        });
    </script>
@endsection
