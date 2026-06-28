@extends('dashboard.layout')
@section('title', strtoupper(__('dashboard.nav.products')))

@section('content')

@include('dashboard._permission_check', ['feature' => 'products'])
@php $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false; @endphp
@if(!$_permDenied)

    {{-- ── Header ──────────────────────────────────────────────────────────────── --}}
    <div
        style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <div>
            <p style="color:rgba(255,255,255,0.8); font-size: var(--title-size);">
                {{ $products->total() }} {{ __('dashboard.productsPage.productsTotal') }}
            </p>
        </div>
        <a href="{{ route('dashboard.products.create') }}" class="btn btn-orange" style="font-size: var(--title-size);">
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

                    <optgroup label="─── RESELL ITEM  ──────────────">
                        @foreach (['Second hand', 'Used', 'Pre-owned'] as $cat)
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
                        <th>{{ __('dashboard.productsPage.warranty') }}</th>
                        <th>{{ __('dashboard.productsPage.price') }}</th>
                        <th>{{ __('dashboard.productsPage.stock') }}</th>
                        {{-- <th>{{ __('dashboard.productsPage.featured') }}</th>
                        <th>{{ __('dashboard.productsPage.hot') }}</th> --}}
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
                                        <div class="product-thumb" style="display:none;background:rgba(255,255,255,0.04);align-items:center;justify-content:center;font-size: var(--title-size);">📦</div>
                                    @else
                                        <div class="product-thumb" style="background:rgba(255,255,255,0.04);display:flex;align-items:center;justify-content:center;font-size: var(--title-size);">📦</div>
                                    @endif
                                    <div>
                                        <div style="font-weight:700; color:#fff; font-size: var(--title-size);">
                                            {{ Str::limit($product->name, 30) }}
                                        </div>
                                        <div style="font-size: var(--title-size); color:rgba(255,255,255,0.7);">
                                            ID: {{ $product->id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-orange">{{ $product->category }}</span>
                            </td>
                            <td style="color:rgba(255,255,255,0.6);">{{ $product->brand ?? '—' }}</td>
                            <td style="color:rgba(255,255,255,0.6);">{{ $product->warranty ?? '—' }}</td>
                            @php
                                $p = $product->price;
                                if (!$p || $p == 0) {
                                    $priceDisplay = '$';
                                } elseif (preg_match('/^\$+$/', (string)$p)) {
                                    $priceDisplay = $p; // show $, $$, $$$ as-is
                                } else {
                                    $priceDisplay = '$' . number_format((float)$p, 2);
                                }
                            @endphp
                            <td style="color:#F97316; font-weight:700;">{{ $priceDisplay }}</td>
                            <td>
                                <span
                                    class="badge {{ $product->stock <= 0 ? 'badge-cancelled' : ($product->stock <= 5 ? 'badge-pending' : 'badge-paid') }}">
                                    {{ $product->stock }}
                                </span>
                            </td>
                            {{-- <td>
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
                            </td> --}}
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
            font-size: var(--title-size);
            transition: border-color 0.2s;
        }

        :lang(km) .filter-input {
            font-family: var(--font-kh) !important;
        }
        :lang(km) .filter-select option {
            font-family: var(--font-kh) !important;
            font-size: 14px;
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
            font-size: var(--title-size);
            font-weight: 400;
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

        :lang(km) .filter-select {
            font-family: var(--font-kh) !important;
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
            font-weight: 400;
            font-size: var(--title-size);
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
