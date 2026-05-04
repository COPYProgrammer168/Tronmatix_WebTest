@extends('dashboard.layout')
@section('title', 'PRODUCTS')

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
            <div style="font-size:16px;font-weight:800;color:rgba(255,255,255,0.6);letter-spacing:1px;">{{ strtoupper(str_replace('_',' ','products')) }}</div>
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
                $_fActive = ($_fKey === 'products');
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



    {{-- ── Header ──────────────────────────────────────────────────────────────── --}}
    <div
        style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <div>
            <p style="color:rgba(255,255,255,0.8); font-size:20px;">
                {{ $products->total() }} {{ __('dashboard.productsPage.productsTotal') }}
            </p>
        </div>
        <a href="{{ route('dashboard.products.create') }}" class="btn btn-orange" style="font-size:16px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19" />
                <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            {{ __('dashboard.productsPage.addProduct') }}
        </a>
    </div>

    {{-- ── Filter Bar ───────────────────────────────────────────────────────────── --}}
    <div class="filter-bar">
        <form method="GET" action="{{ route('dashboard.products') }}" id="filterForm">

            {{-- Search --}}
            <div class="filter-search">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="text" name="search" placeholder="{{ __('dashboard.productsPage.searchPlaceholder') }}" value="{{ request('search') }}"
                    class="filter-input" oninput="debounceSubmit()" />
            </div>

            {{-- Category Dropdown --}}
            <div class="filter-select-wrap">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path
                        d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z" />
                </svg>
                <select name="category" class="filter-select" onchange="this.form.submit()">
                    <option value="">{{ __('dashboard.productsPage.allCategories') }}</option>

                    {{-- <optgroup label="─── NEW ADD ───────────────">
                        @foreach (['New Arrival'] as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </optgroup> --}}

                    <optgroup label="─── PC BUILDS ───────────────">
                        @foreach (['PC BUILD UNDER 1K','PC BUILD UNDER 2K','PC BUILD UNDER 3K','PC BUILD UNDER 4K','PC BUILD UNDER 5K','PC BUILD 5K UP'] as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="─── MONITOR ───────────────">
                        @foreach (['MONITOR 25INCH','MONITOR 27INCH','MONITOR 32INCH','MONITOR 34INCH','MONITOR 39INCH','MONITOR 42INCH','MONITOR 48INCH','MONITOR 49INCH'] as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="─── PC PARTS ───────────────">
                        @foreach (['CPU','RAM','MAINBOARD','COOLING','M2','VGA','CASE','POWER SUPPLY','FAN'] as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </optgroup>

                    <optgroup label="─── HOT ITEM ────────────────">
                        @foreach (['BEST PRICE','BEST SET'] as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </optgroup>

                    <optgroup label="─── ACCESSORY ────────────">
                        @foreach (['KEYBOARD','MOUSE','HEADSET','EARPHONE','MONITOR STAND','SPEAKER','MICROPHONE','WEBCAM','MOUSEPAD','LIGHTBAR','ROUTER'] as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </optgroup>

                    <optgroup label="─── TABLE CHAIR ─────────────">
                        @foreach (['DX RACER','SECRETLAB','RAZER','CONSAIR','FANTECH','COOLER MASTER','TTR RACING'] as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </optgroup>

                    <optgroup label="─── FURNITURE ──────────────">
                        @foreach (['CHAIR', 'DESK', 'MONITOR STAND'] as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>
            </div>

            {{-- Stock Filter --}}
            <div class="filter-select-wrap">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z" />
                    <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16" />
                </svg>
                <select name="stock" class="filter-select" onchange="this.form.submit()">
                    <option value="">{{ __('dashboard.productsPage.allStock') }}</option>
                    <option value="in" {{ request('stock') === 'in' ? 'selected' : '' }}>{{ __('dashboard.productsPage.inStock') }}</option>
                    <option value="low" {{ request('stock') === 'low' ? 'selected' : '' }}>{{ __('dashboard.productsPage.lowStock') }}</option>
                    <option value="out" {{ request('stock') === 'out' ? 'selected' : '' }}>{{ __('dashboard.productsPage.outOfStock') }}</option>
                </select>
            </div>

            {{-- Hot / Featured --}}
            <div class="filter-select-wrap">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" />
                    <path d="M12 6v6l4 2" />
                </svg>
                <select name="filter" class="filter-select" onchange="this.form.submit()">
                    <option value="">{{ __('dashboard.productsPage.allProducts') }}</option>
                    <option value="hot" {{ request('filter') === 'hot' ? 'selected' : '' }}>{{ __('dashboard.productsPage.hotItems') }}</option>
                    <option value="featured" {{ request('filter') === 'featured' ? 'selected' : '' }}>{{ __('dashboard.productsPage.featured') }}</option>
                </select>
            </div>

            {{-- Clear filters --}}
            @if (request()->hasAny(['search', 'category', 'stock', 'filter']))
                <a href="{{ route('dashboard.products') }}" class="btn btn-outline btn-sm" style="white-space:nowrap;">
                    {{ __('dashboard.productsPage.clearFilters') }}
                </a>
            @endif

        </form>
    </div>

    {{-- ── Active filter badges ─────────────────────────────────────────────────── --}}
    @if (request()->hasAny(['search', 'category', 'stock', 'filter']))
        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px;">
            @if (request('search'))
                <span class="badge badge-orange">
                    SEARCH: "{{ request('search') }}"
                </span>
            @endif
            @if (request('category'))
                <span class="badge badge-orange">
                    CATEGORY: {{ request('category') }}
                </span>
            @endif
            @if (request('stock'))
                <span class="badge badge-pending">
                    STOCK: {{ strtoupper(request('stock')) }}
                </span>
            @endif
            @if (request('filter'))
                <span class="badge badge-orange">
                    {{ strtoupper(request('filter')) }}
                </span>
            @endif
        </div>
    @endif

    {{-- ── Products Table ───────────────────────────────────────────────────────── --}}
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('dashboard.productsPage.product') }}</th>
                        <th>{{ __('dashboard.productsPage.category') }}</th>
                        <th>{{ __('dashboard.productsPage.brand') }}</th>
                        <th>{{ __('dashboard.productsPage.price') }}</th>
                        <th>{{ __('dashboard.productsPage.stock') }}</th>
                        <th>{{ __('dashboard.productsPage.featured') }}</th>
                        <th>{{ __('dashboard.productsPage.hot') }}</th>
                        <th>{{ __('dashboard.productsPage.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:12px;">
                                    @php
                                        $thumbSrc = $product->image
                                            ? (Str::startsWith($product->image, ['http://', 'https://'])
                                                ? $product->image
                                                : asset(ltrim($product->image, '/')))
                                            : null;
                                    @endphp
                                    @if($thumbSrc)
                                        <img src="{{ $thumbSrc }}" class="product-thumb" alt=""
                                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
                                        <div class="product-thumb" style="display:none;background:rgba(255,255,255,0.04);align-items:center;justify-content:center;font-size:18px;">📦</div>
                                    @else
                                        <div class="product-thumb" style="background:rgba(255,255,255,0.04);display:flex;align-items:center;justify-content:center;font-size:18px;">📦</div>
                                    @endif
                                    <div>
                                        <div style="font-weight:700; color:#fff; font-size:18px;">
                                            {{ Str::limit($product->name, 30) }}
                                        </div>
                                        <div style="font-size:16px; color:rgba(255,255,255,0.7);">
                                            ID: {{ $product->id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-orange">{{ $product->category }}</span>
                            </td>
                            <td style="color:rgba(255,255,255,0.6);">{{ $product->brand ?? '—' }}</td>
                            <td style="color:#F97316; font-weight:700;">${{ number_format($product->price, 2) }}</td>
                            <td>
                                <span
                                    class="badge {{ $product->stock <= 0 ? 'badge-cancelled' : ($product->stock <= 5 ? 'badge-pending' : 'badge-paid') }}">
                                    {{ $product->stock }}
                                </span>
                            </td>
                            <td>
                                @if ($product->is_featured)
                                    <span class="badge badge-paid">{{ __('dashboard.btn.yes') }}</span>
                                @else
                                    <span class="badge badge-gray">{{ __('dashboard.btn.no') }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($product->is_hot)
                                    <span class="badge badge-orange">{{ __('dashboard.productsPage.hot') }}</span>
                                @else
                                    <span class="badge badge-gray">—</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex; gap:6px;">
                                    <a href="{{ route('dashboard.products.edit', $product) }}"
                                        class="btn btn-outline btn-sm">{{ __('dashboard.btn.edit') }}</a>
                                    <form method="POST" action="{{ route('dashboard.products.destroy', $product) }}"
                                        onsubmit="return confirm('{{ __('dashboard.productsPage.deleteConfirm') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" type="submit">{{ __('dashboard.btn.delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; color:rgba(255,255,255,0.3); padding:50px;">
                                @if (request()->hasAny(['search', 'category', 'stock', 'filter']))
                                    {{ __('dashboard.productsPage.noProductsFiltered') }}
                                    <a href="{{ route('dashboard.products') }}" style="color:#F97316;">{{ __('dashboard.productsPage.clearFiltersLink') }}</a>
                                @else
                                    {{ __('dashboard.productsPage.noProducts') }}
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->hasPages())
            <div style="padding:16px 20px; border-top:1px solid rgba(255,255,255,0.07);">
                {{ $products->links('dashboard.pagination') }}
            </div>
        @endif
    </div>

@endif
@endsection

@push('styles')
    <style>
        /* ── Filter Bar ────────────────────────────────────────────────────────── */
        .filter-bar {
            background: #111;
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 16px;
        }

        .filter-bar form {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Search input */
        .filter-search {
            position: relative;
            flex: 1;
            min-width: 180px;
        }

        .filter-search svg {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.25);
            pointer-events: none;
        }

        .filter-input {
            width: 100%;
            background: #1A1A1A;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            padding: 9px 12px 9px 36px;
            color: #fff;
            font-family: 'Rajdhani', sans-serif;
            font-size: 13px;
            transition: border-color 0.2s;
        }

        .filter-input:focus {
            outline: none;
            border-color: #F97316;
        }

        .filter-input::placeholder {
            color: rgba(255, 255, 255, 0.2);
        }

        /* Select wrapper */
        .filter-select-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .filter-select-wrap svg {
            position: absolute;
            left: 10px;
            color: rgba(255, 255, 255, 0.25);
            pointer-events: none;
            z-index: 1;
        }

        .filter-select {
            background: #1A1A1A;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            padding: 9px 12px 9px 32px;
            color: #fff;
            font-family: 'Rajdhani', sans-serif;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: border-color 0.2s;
            appearance: none;
            -webkit-appearance: none;
            min-width: 160px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='rgba(255,255,255,0.3)' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 30px;
        }

        .filter-select:focus {
            outline: none;
            border-color: #F97316;
        }

        /* Optgroup styling */
        .filter-select option,
        .filter-select optgroup {
            background: #1A1A1A;
            color: #fff;
            font-family: 'Rajdhani', sans-serif;
        }

        .filter-select optgroup {
            color: #F97316;
            font-weight: 700;
            font-size: 11px;
        }

        .filter-select option {
            color: rgba(255, 255, 255, 0.85);
            padding: 6px 12px;
        }

        /* Highlight active filters */
        .filter-select:has(option:checked:not([value=""])) {
            border-color: rgba(249, 115, 22, 0.5);
            color: #F97316;
        }

        @media (max-width: 768px) {
            .filter-bar form {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-search {
                min-width: 100%;
            }

            .filter-select {
                min-width: 100%;
                width: 100%;
            }

            .filter-select-wrap {
                width: 100%;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Debounce search input so it doesn't submit on every keystroke
        let debounceTimer;

        function debounceSubmit() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                document.getElementById('filterForm').submit();
            }, 500);
        }
    </script>
@endpush
