@extends('dashboard.layout')
@section('title', $product ? strtoupper(__('dashboard.form.editproduct')) : strtoupper(__('dashboard.form.addproduct')))

@section('content')

    @include('dashboard._permission_check', ['feature' => 'products'])
@php if(!isset($_permDenied)) $_permDenied = false; @endphp

@if(!$_permDenied)
        <div style="max-width:990px;">
            <a href="{{ route('dashboard.products') }}" class="btn btn-outline btn-sm" style="margin-bottom:20px;">
                ← {{ __('dashboard.form.btp') }}
            </a>
            <div class="card">
                <div class="card-header">
                    <span class="card-title" style="font-size: var(--title-size);">
                        {{ $product ? __('dashboard.form.editproduct') . ' ' . Str::limit($product->name, 40) : __('dashboard.form.addproduct') }}
                    </span>
                    @if ($product)
                        <span class="badge badge-orange">ID: {{ $product->id }}</span>
                    @endif
                </div>
                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-error" style="margin-bottom:20px;">
                            @foreach ($errors->all() as $error)
                                <div>✗ {{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST"
                        action="{{ $product ? route('dashboard.products.update', $product) : route('dashboard.products.store') }}"
                        enctype="multipart/form-data" id="productForm">
                        @csrf
                        @if ($product)
                            @method('PUT')
                        @endif

                        {{-- ── Two column layout ──────────────────────────────────── --}}
                        <div style="display:grid; grid-template-columns:1fr 300px; gap:24px;">

                            {{-- ── LEFT COLUMN ──────────────────────────────────────── --}}
                            <div>

                                {{-- Name --}}
                                <div class="form-group">
                                    <label class="form-label">{{ __('dashboard.form.productName') }}</label>
                                    <input type="text" name="name"
                                        class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                        value="{{ old('name', $product?->name) }}" placeholder="e.g. AMD Ryzen 7 9800X3D"
                                        required />
                                </div>

                                {{-- Caption --}}
                                <div class="form-group">
                                    <label class="form-label">Caption</label>
                                    <input type="text" name="caption"
                                        class="form-control {{ $errors->has('caption') ? 'is-invalid' : '' }}"
                                        value="{{ old('caption', $product?->caption) }}"
                                        placeholder="e.g. G SKILL TRIDENT Z DDR5 32GB 6000MHZ" />
                                </div>

                                {{-- Category + Brand --}}
                                <div class="form-grid-2">
                                    <div class="form-group" style="font-size: var(--title-size);">
                                        <label class="form-label">{{ __('dashboard.form.category') }}</label>
                                        <select name="category" id="categorySelect" class="form-control" required>
                                            <option value="" disabled
                                                {{ old('category', $product?->category) ? '' : 'selected' }}>
                                                — Select Category —
                                            </option>

                                            <optgroup label="─── PC BUILDS ───────────────">
                                                @foreach (['PC BUILD UNDER 1K', 'PC BUILD UNDER 2K', 'PC BUILD UNDER 3K', 'PC BUILD UNDER 4K', 'PC BUILD UNDER 5K', 'PC BUILD 5K UP'] as $cat)
                                                    <option value="{{ $cat }}"
                                                        {{ old('category', $product?->category) === $cat ? 'selected' : '' }}>
                                                        {{ $cat }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                            <optgroup label="─── MONITOR ───────────────">
                                                @foreach (['MONITOR 25INCH', 'MONITOR 27INCH', 'MONITOR 32INCH', 'MONITOR 34INCH', 'MONITOR 39INCH', 'MONITOR 42INCH', 'MONITOR 48INCH', 'MONITOR 49INCH'] as $cat)
                                                    <option value="{{ $cat }}"
                                                        {{ old('category', $product?->category) === $cat ? 'selected' : '' }}>
                                                        {{ $cat }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                            <optgroup label="─── PC PARTS ───────────────" id="pcPartsGroup">
                                                @foreach (['CPU', 'RAM', 'MAINBOARD', 'COOLING', 'M2', 'VGA', 'CASE', 'POWER SUPPLY', 'FAN'] as $cat)
                                                    <option value="{{ $cat }}"
                                                        {{ old('category', $product?->category) === $cat ? 'selected' : '' }}>
                                                        {{ $cat }}
                                                    </option>
                                                @endforeach
                                            </optgroup>

                                            <optgroup label="─── HOT ITEM ────────────────">
                                                @foreach (['BEST PRICE', 'BEST SET'] as $cat)
                                                    <option value="{{ $cat }}"
                                                        {{ old('category', $product?->category) === $cat ? 'selected' : '' }}>
                                                        {{ $cat }}
                                                    </option>
                                                @endforeach
                                            </optgroup>

                                            <optgroup label="─── ACCESSORY ────────────">
                                                @foreach (['KEYBOARD', 'MOUSE', 'HEADSET', 'EARPHONE', 'MONITOR STAND', 'SPEAKER', 'MICROPHONE', 'WEBCAM', 'MOUSEPAD', 'LIGHTBAR', 'ROUTER'] as $cat)
                                                    <option value="{{ $cat }}"
                                                        {{ old('category', $product?->category) === $cat ? 'selected' : '' }}>
                                                        {{ $cat }}
                                                    </option>
                                                @endforeach
                                            </optgroup>

                                            <optgroup label="─── TABLE CHAIR ─────────────">
                                                @foreach (['DX RACER', 'SECRETLAB', 'RAZER', 'CONSAIR', 'FANTECH', 'COOLER MASTER', 'TTR RACING'] as $cat)
                                                    <option value="{{ $cat }}"
                                                        {{ old('category', $product?->category) === $cat ? 'selected' : '' }}>
                                                        {{ $cat }}
                                                    </option>
                                                @endforeach
                                            </optgroup>

                                            <optgroup label="─── RESELL ITEM  ──────────────">
                                                @foreach (['Second hand', 'Used', 'Pre-owned'] as $cat)
                                                    <option value="{{ $cat }}"
                                                        {{ old('category', $product?->category) === $cat ? 'selected' : '' }}>
                                                        {{ $cat }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">{{ __('dashboard.form.brand') }}</label>
                                        <input type="text" name="brand" class="form-control"
                                            value="{{ old('brand', $product?->brand) }}"
                                            placeholder="e.g. AMD, Intel, NVIDIA" list="brandList" />
                                        <datalist id="brandList">
                                            @foreach (['AMD', 'Intel', 'NVIDIA', 'ASUS', 'MSI', 'Gigabyte', 'Corsair', 'Razer', 'SteelSeries', 'HyperX'] as $brand)
                                                <option value="{{ $brand }}">
                                            @endforeach
                                        </datalist>
                                    </div>
                                    <div class="form-group" id="pcPartBrandGroup" style="display: none;">
                                        <label class="form-label">PC Part Brand</label>
                                        <select name="brand_pc_part" id="pcPartBrandSelect" class="form-control">
                                            <option value="">— Select PC Part Brand —</option>
                                        </select>
                                    </div>

                                    <script>
                                        const categorySelect = document.getElementById('categorySelect');
                                        const pcPartBrandGroup = document.getElementById('pcPartBrandGroup');
                                        const pcPartBrandSelect = document.getElementById('pcPartBrandSelect');

                                        const pcPartBrands = {
                                            'CPU': ['INTEL 12TH', 'INTEL 13TH', 'INTEL 14TH', 'INTEL 15TH ULTRA', 'AMD ALL SERIES'],
                                            'RAM': ['8GB DDR4', '16GB DDR4', '16GB DDR5', '32GB DDR5', '24GB DDR5', '48GB DDR5', '96GB DDR5',
                                                'RAM DDR5 64GB X2 128GB'
                                            ],
                                            'MAINBOARD': ['H610 SERIES', 'B760 SERIES', 'Z790 SERIES', 'Z890 SERIES', 'X670 SERIES', 'X870 SERIES',
                                                'B850 SERIES', 'H810 SERIES', 'B860 SERIES'
                                            ],
                                            'COOLING': ['THERMAL GREASE', 'COOLER', 'LIQUID 240MM', 'LIQUID 360MM', 'LIQUID WATERLOOP'],
                                            'M2': ['256G', '500G', '1TB', '2TB', '4TB', '8TB', '4TB', 'ENCLOSURE', 'M.2 TRAY'],
                                            'VGA': ['RTX 3050', 'RTX 5080', 'RTX 5090', 'RTX 5070TI', 'INTER VGA', 'VGA AMD ALL SERIES', 'VGA RTX5070',
                                                'RTX5060TI', 'RTX 5060'
                                            ],
                                            'CASE': ['UNDER 50$', 'UNDER 100$', 'UNDER 200$', 'UNDER 300$', 'UNDER 500$', 'UNDER 1000$', 'UNDER 10000$',
                                                'MINI ITX'
                                            ],
                                            'POWER SUPPLY': ['550W', '650W', '750W', '850W', '1000W', '1200W', '1600W', '2200W'],
                                            'FAN': ['CASE FAN', 'RGB FAN', 'INDUSTRIAL FAN']
                                        };

                                        function updatePcPartBrands() {
                                            const category = categorySelect.value;
                                            const brands = pcPartBrands[category];

                                            if (brands) {
                                                pcPartBrandGroup.style.display = 'block';
                                                pcPartBrandSelect.innerHTML = '<option value="">— Select PC Part Brand —</option>';
                                                brands.forEach(b => {
                                                    const opt = document.createElement('option');
                                                    opt.value = b;
                                                    opt.textContent = b;
                                                    if (b === '{{ old('brand_pc_part', $product?->brand_pc_part) }}') opt.selected = true;
                                                    pcPartBrandSelect.appendChild(opt);
                                                });
                                            } else {
                                                pcPartBrandGroup.style.display = 'none';
                                            }
                                        }

                                        categorySelect.addEventListener('change', updatePcPartBrands);
                                        updatePcPartBrands();
                                    </script>
                                </div>
                            </div>

                            {{-- Price + Stock + Rating --}}
                            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
                                <div class="form-group">
                                    <label class="form-label">{{ __('dashboard.form.price') }}</label>
                                    <input type="text" name="price"
                                        class="form-control {{ $errors->has('price') ? 'is-invalid' : '' }}"
                                        value="{{ old('price', $product?->price) }}" placeholder="$ or 0.00"
                                        inputmode="decimal" oninput="this.value = this.value.replace(/[^0-9$.]/g, '');"
                                        required />
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ __('dashboard.form.stock') }}</label>
                                    <input type="number" name="stock"
                                        class="form-control {{ $errors->has('stock') ? 'is-invalid' : '' }}"
                                        value="{{ old('stock', $product?->stock ?? 0) }}" min="0" required />
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ __('dashboard.form.rating') }}</label>
                                    <input type="number" name="rating" class="form-control"
                                        value="{{ old('rating', $product?->rating ?? 0) }}" step="0.1"
                                        min="0" max="5" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ __('dashboard.form.warranty') }}</label>
                                    <input type="text" name="warranty" class="form-control"
                                        value="{{ old('warranty', $product?->warranty) }}" placeholder="e.g. 3 years" />
                                </div>
                            </div>

                            {{-- Stock Status + Details --}}
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                                <div class="form-group">
                                    <label class="form-label">Stock Status</label>
                                    <select name="stock_status" class="form-control">
                                        <option value=""
                                            {{ old('stock_status', $product?->stock_status) === '' ? 'selected' : '' }}>
                                            Select Status</option>
                                        @foreach (['Available InStock Now', 'Pre-order'] as $status)
                                            <option value="{{ $status }}"
                                                {{ old('stock_status', $product?->stock_status) === $status ? 'selected' : '' }}>
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Stock Details</label>
                                    <input type="text" name="stock_details" class="form-control"
                                        value="{{ old('stock_details', $product?->stock_details) }}"
                                        placeholder="e.g. Arriving next week" />
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="form-group">
                                <label class="form-label">{{ __('dashboard.form.description') }}</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Product description...">{{ old('description', $product?->description) }}</textarea>
                            </div>

                            {{-- Featured + Hot toggles --}}
                            <div style="display:flex; gap:32px; margin-top:4px;">
                                <div class="form-group">
                                    <label class="form-label">{{ __('dashboard.form.featuredProduct') }}</label>
                                    <label class="toggle-wrap" style="cursor:pointer;">
                                        <label class="toggle">
                                            <input type="checkbox" name="is_featured" value="1"
                                                {{ old('is_featured', $product?->is_featured) ? 'checked' : '' }} />
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span style="font-size: var(--title-size); color:rgba(255,255,255,0.5);">
                                            Show on featured section
                                        </span>
                                    </label>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">{{ __('dashboard.form.hotItem') }}</label>
                                    <label class="toggle-wrap" style="cursor:pointer;">
                                        <label class="toggle">
                                            <input type="checkbox" name="is_hot" value="1"
                                                {{ old('is_hot', $product?->is_hot) ? 'checked' : '' }} />
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span style="font-size: var(--title-size); color:rgba(255,255,255,0.5);">
                                            Mark as hot item 🔥
                                        </span>
                                    </label>
                                </div>
                            </div>

                        </div>

                        {{-- ── RIGHT COLUMN: Image Upload ────────────────────── --}}
                        <div>
                            <div class="form-group">
                                <label class="form-label">{{ __('dashboard.form.productImages') }}</label>
                                <div style="font-size: var(--title-size); color:rgba(255,255,255,0.3); margin-bottom:8px;">
                                    {{ __('dashboard.form.firstimage') }}
                                </div>

                                {{-- Multi-image gallery grid --}}
                                <div id="imageGallery"
                                    style="
                                    display:grid;
                                    grid-template-columns: repeat(3, 1fr);
                                    gap:8px;
                                    margin-bottom:10px;
                                ">
                                    @if ($product?->images && count($product->images))
                                        @foreach ($product->images as $idx => $img)
                                            @php
                                                $imgSrc = Str::startsWith($img, ['http://', 'https://'])
                                                    ? $img
                                                    : asset(ltrim($img, '/'));
                                            @endphp
                                            <div class="gallery-thumb {{ $idx === 0 ? 'gallery-main' : '' }}"
                                                data-index="{{ $idx }}" data-raw-path="{{ $img }}"
                                                data-type="existing" draggable="true">
                                                <img src="{{ $imgSrc }}" alt="Image {{ $idx + 1 }}" />
                                                @if ($idx === 0)
                                                    <div class="gallery-main-badge">MAIN</div>
                                                @endif
                                                <button type="button" class="gallery-remove"
                                                    onclick="removeExistingImage({{ $idx }}, this)">✕</button>
                                                <input type="hidden" name="existing_images[]"
                                                    value="{{ $img }}" id="existing_{{ $idx }}" />
                                            </div>
                                        @endforeach
                                    @elseif ($product?->image)
                                        @php
                                            $imgSrc = Str::startsWith($product->image, ['http://', 'https://'])
                                                ? $product->image
                                                : asset(ltrim($product->image, '/'));
                                        @endphp
                                        <div class="gallery-thumb gallery-main" data-index="0"
                                            data-raw-path="{{ $product->image }}" data-type="existing" draggable="true">
                                            <img src="{{ $imgSrc }}" alt="Main image" />
                                            <div class="gallery-main-badge">MAIN</div>
                                            <button type="button" class="gallery-remove"
                                                onclick="removeExistingImage(0, this)">✕</button>
                                            <input type="hidden" name="existing_images[]" value="{{ $product->image }}"
                                                id="existing_0" />
                                        </div>
                                    @endif

                                    {{-- Add more slot --}}
                                    <div id="addImageSlot" class="gallery-add-slot"
                                        onclick="document.getElementById('multiImageInput').click()">
                                        <svg width="24" height="24" fill="none" stroke="rgba(255,255,255,0.2)"
                                            stroke-width="1.5" viewBox="0 0 24 24">
                                            <line x1="12" y1="5" x2="12" y2="19" />
                                            <line x1="5" y1="12" x2="19" y2="12" />
                                        </svg>
                                        <span>{{ __('dashboard.form.addImages') }}</span>
                                    </div>
                                </div>

                                {{-- Hidden multi file input --}}
                                <input type="file" name="image_files[]" id="multiImageInput"
                                    accept="image/jpeg,image/png,image/webp" multiple style="display:none;"
                                    onchange="handleMultiImages(this)" />

                                {{-- Upload button --}}
                                <button type="button" class="btn btn-outline"
                                    style="width:100%; margin-bottom:10px; justify-content:center;"
                                    onclick="document.getElementById('multiImageInput').click()">
                                    <svg width="14" height="14" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <polyline points="16 16 12 12 8 16" />
                                        <line x1="12" y1="12" x2="12" y2="21" />
                                        <path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3" />
                                    </svg>
                                    {{ __('dashboard.form.uploadimages') }} (Multi-select)
                                </button>

                                {{-- Divider --}}
                                <div style="display:flex; align-items:center; gap:10px; margin:14px 0;">
                                    <div style="flex:1; height:1px; background:rgba(255,255,255,0.07);"></div>
                                    <span
                                        style="font-size: var(--title-size); color:rgba(255,255,255,0.2); letter-spacing:1px;">OR</span>
                                    <div style="flex:1; height:1px; background:rgba(255,255,255,0.07);"></div>
                                </div>

                                {{-- URL Input for extra images --}}
                                <label class="form-label">{{ __('dashboard.form.addImageUrl') }}</label>
                                <div style="display:flex; gap:8px;">
                                    <input type="text" id="imageUrlInput" class="form-control"
                                        placeholder="https://example.com/image.jpg" style="flex:1;" />
                                    <button type="button" class="btn btn-outline" onclick="addImageByUrl()"
                                        style="white-space:nowrap; padding:0 14px;">+ ADD</button>
                                </div>
                                <div id="urlPreviewMsg"
                                    style="margin-top:6px; font-size: var(--title-size); color:rgba(255,255,255,0.2);">
                                </div>

                                {{-- Hidden field to track new URL images --}}
                                <div id="urlImagesContainer"></div>

                                {{-- Remove image button (edit mode only) --}}
                                @if ($product?->image || ($product?->images && count($product->images)))
                                    <div style="margin-top:12px;">
                                        <label class="toggle-wrap" style="cursor:pointer;">
                                            <input type="checkbox" name="remove_image" value="1"
                                                id="removeImageCheck" onchange="toggleRemoveImage(this)" />
                                            <span style="font-size: var(--title-size); color:rgba(239,68,68,0.7);">
                                                {{ __('dashboard.form.removeallimages') }}
                                            </span>
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>

                </div>

                {{-- ── Submit Buttons ────────────────────────────────────── --}}
                <div
                    style="display:flex; gap:12px; margin-top:24px; padding-top:20px; border-top:1px solid rgba(255,255,255,0.07);">
                    <button type="submit" class="btn btn-orange" id="submitBtn">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        {{ $product ? strtoupper(__('dashboard.form.updateProduct')) : strtoupper(__('dashboard.form.createProduct')) }}
                    </button>
                    <a href="{{ route('dashboard.products') }}" class="btn btn-outline">
                        {{ __('dashboard.form.cancel') }}
                    </a>
                    @if ($product)
                        <div style="margin-left:auto;">
                            <button type="button" class="btn btn-danger"
                                onclick="if(confirm('Delete this product permanently?')) document.getElementById('deleteForm').submit()">
                                {{ __('dashboard.form.delete') }}
                            </button>
                        </div>
                    @endif
                </div>

                </form>

                {{-- ── Delete Form — MUST sit outside the main <form> ─────── --}}
                @if ($product)
                    <form id="deleteForm" method="POST" action="{{ route('dashboard.products.destroy', $product) }}">
                        @csrf
                        @method('DELETE')
                    </form>
                @endif
            </div>
        </div>
        </div>

    @endif
@endsection

@push('styles')
    <style>
        :lang(km) label {
            font-weight: 500;
        }

        /* ── Multi-Image Gallery ──────────────────────────────────────────────── */
        .gallery-thumb {
            position: relative;
            aspect-ratio: 1/1;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.08);
            cursor: grab;
            background: #1A1A1A;
            transition: border-color 0.2s, transform 0.15s;
        }

        .gallery-thumb:hover {
            border-color: rgba(249, 115, 22, 0.5);
        }

        .gallery-thumb.gallery-main {
            border-color: #F97316;
            border-width: 2px;
        }

        .gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .gallery-main-badge {
            position: absolute;
            top: 5px;
            left: 5px;
            background: #F97316;
            color: #fff;
            font-size: var(--title-size);
            font-weight: 900;
            letter-spacing: 1px;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .gallery-remove {
            position: absolute;
            top: 4px;
            right: 4px;
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: var(--title-size);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .gallery-thumb:hover .gallery-remove {
            opacity: 1;
        }

        .gallery-add-slot {
            aspect-ratio: 1/1;
            border-radius: 10px;
            border: 2px dashed rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
            font-size: var(--title-size);
            color: rgba(255, 255, 255, 0.25);
            transition: border-color 0.2s, background 0.2s;
            background: #1A1A1A;
        }

        .gallery-add-slot:hover {
            border-color: rgba(249, 115, 22, 0.4);
            background: rgba(249, 115, 22, 0.04);
        }

        .gallery-thumb.drag-over {
            transform: scale(1.05);
            border-color: #F97316;
        }

        /* ── Form invalid ──────────────────────────────────────────────────────── */
        .form-control.is-invalid {
            border-color: #EF4444;
        }

        /* ── Optgroup styling ──────────────────────────────────────────────────── */
        select.form-control option,
        select.form-control optgroup {
            background: #1A1A1A;
            color: #fff;
            font-family: 'Rajdhani', sans-serif;
        }

        select.form-control optgroup {
            color: #F97316;
            font-weight: 700;
            font-size: var(--title-size);
        }

        /* ── Responsive ────────────────────────────────────────────────────────── */
        @media (max-width: 768px) {
            div[style*="grid-template-columns:1fr 300px"] {
                grid-template-columns: 1fr !important;
            }

            div[style*="grid-template-columns:1fr 1fr 1fr"] {
                grid-template-columns: 1fr 1fr !important;
            }
        }
    </style>
@endpush


@push('styles')
    <style>
        /* ── Products Form – light theme ─────────────────────────────────────────── */
        [data-theme="light"] .gallery-thumb {
            background: #F8FAFC !important;
            border-color: rgba(15, 23, 42, 0.12) !important;
        }

        [data-theme="light"] .gallery-add-slot {
            background: #F8FAFC !important;
            border-color: rgba(15, 23, 42, 0.15) !important;
            color: rgba(15, 23, 42, 0.35) !important;
        }

        [data-theme="light"] .gallery-add-slot:hover {
            border-color: rgba(249, 115, 22, 0.40) !important;
            background: rgba(249, 115, 22, 0.03) !important;
        }

        [data-theme="light"] [style*="color:rgba(255,255,255,0.4)"] {
            color: rgba(15, 23, 42, 0.45) !important;
        }

        [data-theme="light"] [style*="color:rgba(255,255,255,0.3)"] {
            color: rgba(15, 23, 42, 0.35) !important;
        }

        [data-theme="light"] [style*="color:rgba(255,255,255,0.5)"] {
            color: rgba(15, 23, 42, 0.55) !important;
        }

        [data-theme="light"] [style*="background:rgba(255,255,255,0.05)"] {
            background: rgba(15, 23, 42, 0.04) !important;
        }

        [data-theme="light"] [style*="background:rgba(255,255,255,0.04)"] {
            background: rgba(15, 23, 42, 0.03) !important;
        }

        [data-theme="light"] [style*="border:1px solid rgba(255,255,255,0.08)"] {
            border-color: rgba(15, 23, 42, 0.08) !important;
        }

        [data-theme="light"] [style*="border:1px solid rgba(255,255,255,0.1)"] {
            border-color: rgba(15, 23, 42, 0.10) !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // ── Multi-image gallery state ─────────────────────────────────────────────
        let newFileObjects = [] // File objects from <input type=file>
        let urlImageCount = 0

        // ── Handle multiple file upload ───────────────────────────────────────────
        function handleMultiImages(input) {
            const files = Array.from(input.files)
            const gallery = document.getElementById('imageGallery')
            const addSlot = document.getElementById('addImageSlot')
            const thumbs = gallery.querySelectorAll('.gallery-thumb')

            if (thumbs.length + files.length > 8) {
                alert('Maximum 8 images allowed.')
                return
            }

            files.forEach(file => {
                if (!file.type.startsWith('image/')) return
                if (file.size > 2 * 1024 * 1024) {
                    alert(`${file.name} exceeds 2MB limit.`)
                    return
                }

                const reader = new FileReader()
                reader.onload = e => {
                    newFileObjects.push(file)
                    const idx = newFileObjects.length - 1
                    const thumb = createThumb(e.target.result, false)
                    thumb.dataset.newIdx = idx
                    thumb.dataset.type = 'new'
                    gallery.insertBefore(thumb, addSlot)
                    updateMainBadge()
                    syncNewFilesInput()
                }
                reader.readAsDataURL(file)
            })
            input.value = '' // reset so same file can be re-added
        }

        // ── Create thumbnail element ──────────────────────────────────────────────
        function createThumb(src, isMain, rawPath = null) {
            const div = document.createElement('div')
            div.className = 'gallery-thumb' + (isMain ? ' gallery-main' : '')
            div.draggable = true
            // rawPath = the value to submit (e.g. /storage/... or https://...).
            // For URL images: src IS the raw path. For new file uploads: null (sent via image_files[]).
            if (rawPath !== null) div.dataset.rawPath = rawPath
            div.innerHTML = `
                <img src="${src}" alt="Image" />
                ${isMain ? '<div class="gallery-main-badge">MAIN</div>' : ''}
                <button type="button" class="gallery-remove" onclick="removeThumb(this)">✕</button>
            `
            // Drag events
            div.addEventListener('dragstart', onDragStart)
            div.addEventListener('dragover', onDragOver)
            div.addEventListener('drop', onDrop)
            div.addEventListener('dragend', onDragEnd)
            return div
        }

        // ── Remove thumbnail ──────────────────────────────────────────────────────
        function removeThumb(btn) {
            btn.closest('.gallery-thumb').remove()
            updateMainBadge()
            syncNewFilesInput()
        }

        function removeExistingImage(idx, btn) {
            const hidden = document.getElementById('existing_' + idx)
            if (hidden) hidden.remove()
            btn.closest('.gallery-thumb').remove()
            updateMainBadge()
        }

        // ── Update MAIN badge on first thumb ─────────────────────────────────────
        function updateMainBadge() {
            const gallery = document.getElementById('imageGallery')
            const thumbs = gallery.querySelectorAll('.gallery-thumb')
            thumbs.forEach((t, i) => {
                t.classList.toggle('gallery-main', i === 0)
                const old = t.querySelector('.gallery-main-badge')
                if (i === 0 && !old) {
                    const b = document.createElement('div')
                    b.className = 'gallery-main-badge'
                    b.textContent = 'MAIN'
                    t.appendChild(b)
                } else if (i !== 0 && old) {
                    old.remove()
                }
            })
        }

        // ── Sync new file inputs via DataTransfer API ────────────────────────────
        // Called after every add/remove of new file thumbs.
        // Rebuilds the image_files[] <input> to match current gallery DOM order.
        function syncNewFilesInput() {
            const gallery = document.getElementById('imageGallery')
            const thumbs = Array.from(gallery.querySelectorAll('.gallery-thumb[data-type="new"]'))
            const input = document.getElementById('multiImageInput')
            const dt = new DataTransfer()
            thumbs.forEach(t => {
                const idx = parseInt(t.dataset.newIdx, 10)
                if (!isNaN(idx) && newFileObjects[idx]) {
                    dt.items.add(newFileObjects[idx])
                }
            })
            input.files = dt.files
        }

        // ── Add image by URL ──────────────────────────────────────────────────────
        function addImageByUrl() {
            const url = document.getElementById('imageUrlInput').value.trim()
            if (!url) return
            const msg = document.getElementById('urlPreviewMsg')

            const img = new Image()
            img.onload = () => {
                const gallery = document.getElementById('imageGallery')
                const addSlot = document.getElementById('addImageSlot')
                const thumbs = gallery.querySelectorAll('.gallery-thumb')
                if (thumbs.length >= 8) {
                    alert('Max 8 images.');
                    return
                }

                const hidden = document.createElement('input')
                hidden.type = 'hidden'
                hidden.name = 'image_urls[]'
                hidden.value = url
                document.getElementById('urlImagesContainer').appendChild(hidden)

                const thumb = createThumb(url, thumbs.length === 0, url)
                thumb.dataset.type = 'url'
                gallery.insertBefore(thumb, addSlot)
                updateMainBadge()

                document.getElementById('imageUrlInput').value = ''
                msg.textContent = '✅ Image added!'
                msg.style.color = '#4ade80'
                setTimeout(() => msg.textContent = '', 2000)
            }
            img.onerror = () => {
                msg.textContent = '❌ Invalid image URL'
                msg.style.color = '#f87171'
            }
            img.src = url
        }

        // ── Drag-and-drop reorder ─────────────────────────────────────────────────
        let dragSrc = null

        function onDragStart(e) {
            dragSrc = this;
            this.style.opacity = '0.4'
        }

        function onDragEnd() {
            dragSrc.style.opacity = '1';
            document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('drag-over'))
        }

        function onDragOver(e) {
            e.preventDefault();
            this.classList.add('drag-over')
        }

        function onDrop(e) {
            e.preventDefault()
            if (dragSrc === this) return
            const gallery = document.getElementById('imageGallery')
            const addSlot = document.getElementById('addImageSlot')
            const thumbs = Array.from(gallery.querySelectorAll('.gallery-thumb'))
            const fromIdx = thumbs.indexOf(dragSrc)
            const toIdx = thumbs.indexOf(this)
            if (fromIdx < toIdx) gallery.insertBefore(dragSrc, this.nextSibling || addSlot)
            else gallery.insertBefore(dragSrc, this)
            updateMainBadge()
            this.classList.remove('drag-over')
        }

        // ── Remove image toggle ───────────────────────────────────────────────────
        function toggleRemoveImage(checkbox) {
            const gallery = document.getElementById('imageGallery')
            gallery.style.opacity = checkbox.checked ? '0.3' : '1'
        }

        // ── Rebuild existing_images[] + sync file input before submit ───────────
        document.getElementById('productForm').addEventListener('submit', function(e) {
            const gallery = document.getElementById('imageGallery')
            const thumbs = Array.from(gallery.querySelectorAll('.gallery-thumb'))

            // 1. Collect ordered existing + URL images using data-raw-path.
            //    CRITICAL: do NOT use img.src — browsers resolve that to a full
            //    URL (http://127.0.0.1:8000/storage/...) which, if saved to DB,
            //    causes asset() to double-prefix it on the next edit.
            let existingOrder = []
            thumbs.forEach(t => {
                if (t.dataset.rawPath) {
                    existingOrder.push(t.dataset.rawPath)
                }
            })

            // 2. Rebuild existing_images[] hidden inputs in current DOM order
            document.querySelectorAll('input[name="existing_images[]"]').forEach(el => el.remove())
            existingOrder.forEach(rawPath => {
                const h = document.createElement('input')
                h.type = 'hidden';
                h.name = 'existing_images[]';
                h.value = rawPath
                document.getElementById('urlImagesContainer').appendChild(h)
            })

            // 3. Sync file input so new uploads are submitted in gallery order
            syncNewFilesInput()

            // Submit button loading state
            const btn = document.getElementById('submitBtn')
            btn.innerHTML = `<svg style="animation:spin 0.8s linear infinite" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/>
                <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/>
                <line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/>
            </svg> SAVING...`
            btn.disabled = true
        })
    </script>

    <style>
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush
