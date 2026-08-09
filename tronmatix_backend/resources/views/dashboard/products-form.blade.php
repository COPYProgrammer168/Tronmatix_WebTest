@extends('dashboard.layout')
@section('title', $product ? strtoupper(__('dashboard.form.editproduct')) : strtoupper(__('dashboard.form.addproduct')))

@section('content')

    @include('dashboard._permission_check', ['feature' => 'products'])
    @php
        if (!isset($_permDenied)) {
            $_permDenied = false;
        }
    @endphp

    @if (!$_permDenied)
        <div>
            <a href="{{ route('dashboard.products') }}" class="btn btn-outline btn-sm" style="margin-bottom:20px;">
                ← {{ __('dashboard.form.btp') }}
            </a>
            <div class="card" id="productFormCard">
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

                        {{-- ── Image Upload Section ────────────────────── --}}
                        <div class="form-group" style="margin-bottom:32px;">
                            <label class="form-label">{{ __('dashboard.form.productImages') }}</label>
                            <div style="font-size: var(--title-size); color:rgba(255,255,255,0.3); margin-bottom:12px;">
                                {{ __('dashboard.form.firstimage') }}
                            </div>

                            {{-- Multi-image gallery grid --}}
                            <div id="imageGallery"
                                style="display:grid; grid-template-columns:repeat(auto-fill,minmax(120px,1fr)); gap:8px; margin-bottom:10px;">
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
                                                <div class="gallery-main-badge">Cover</div>
                                            @endif
                                            <button type="button" class="gallery-remove"
                                                onclick="removeExistingImage('{{ $idx }}', this)">✕</button>
                                            <input type="hidden" name="existing_images[]" value="{{ $img }}"
                                                id="existing_{{ $idx }}" />
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
                                        <div class="gallery-main-badge">Cover</div>
                                        <button type="button" class="gallery-remove"
                                            onclick="removeExistingImage(0, this)">✕</button>
                                        <input type="hidden" name="existing_images[]" value="{{ $product->image }}"
                                            id="existing_0" />
                                    </div>
                                @endif

                                {{-- Add photo tile --}}
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
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
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

                            {{-- URL Input for extra images — auto-adds on paste --}}
                            <label class="form-label">{{ __('dashboard.form.addImageUrl') }}</label>
                            <div style="display:flex; gap:8px;">
                                <input type="text" id="imageUrlInput" class="form-control"
                                    placeholder="https://example.com/image.jpg" style="flex:1;"
                                    onpaste="setTimeout(autoAddImageByUrl, 150)"
                                    onchange="setTimeout(autoAddImageByUrl, 150)"
                                    onblur="setTimeout(autoAddImageByUrl, 150)" />
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
                                        <input type="checkbox" name="remove_image" value="1" id="removeImageCheck"
                                            onchange="toggleRemoveImage(this)" />
                                        <span style="font-size: var(--title-size); color:rgba(239,68,68,0.7);">
                                            {{ __('dashboard.form.removeallimages') }}
                                        </span>
                                    </label>
                                </div>
                            @endif
                        </div>

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

                        {{-- Category + Brand + SKU --}}
                        <div class="form-grid-3">
                            <div class="form-group" style="font-size: var(--title-size);">
                                <label class="form-label">{{ __('dashboard.form.category') }}</label>
                                <select name="category" id="categorySelect" class="form-control" required>
                                    <option value="" disabled
                                        {{ old('category', $product?->category) ? '' : 'selected' }}>
                                        — Select Category —
                                    </option>

                                    {{-- Dynamic optgroups from the category tree:
                                                 group title = MAIN CATEGORY, options = its SUB CATEGORIES. --}}
                                    @forelse($categoryGroups ?? [] as $group)
                                        <optgroup label="────────── {{ $group['label'] }} ──────────">
                                            @foreach ($group['options'] as $opt)
                                                <option value="{{ $opt['value'] }}"
                                                    {{ old('category', $product?->category) === $opt['value'] ? 'selected' : '' }}>
                                                    {{ $opt['label'] }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @empty
                                        <option value="" disabled>No categories available</option>
                                    @endforelse
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">{{ __('dashboard.form.brand') }}</label>
                                <input type="text" name="brand" class="form-control"
                                    value="{{ old('brand', $product?->brand) }}" placeholder="e.g. AMD, Intel, NVIDIA"
                                    list="brandList" />
                                <datalist id="brandList">
                                    @foreach (['AMD', 'Intel', 'NVIDIA', 'ASUS', 'MSI', 'Gigabyte', 'Corsair', 'Razer', 'SteelSeries', 'HyperX'] as $brand)
                                        <option value="{{ $brand }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="form-group" id="pcPartBrandGroup" style="display: none;">
                                <label class="form-label">PC Part Brand</label>
                                <select name="brand_pc_part" id="pcPartBrandSelect" class="form-control"
                                    data-selected="{{ old('brand_pc_part', $product?->brand_pc_part) }}">
                                    <option value="">— Select PC Part Brand —</option>
                                </select>
                            </div>
                            {{-- SKU (read-only preview on create, immutable on edit) --}}
                        <div class="form-group">
                            <label class="form-label">SKU</label>
                            @if ($product && $product->sku)
                                <input type="text" value="{{ $product->sku }}" readonly
                                    class="form-control"
                                    title="SKU is permanent and cannot be changed after creation" />
                                <div style="font-size: var(--title-size); color:rgba(255,255,255,0.35); margin-top:6px;">
                                    SKU is permanent and cannot be changed after creation.
                                </div>
                            @else
                                <input type="text" id="skuPreview" readonly
                                    class="form-control"
                                    value="{{ old('sku', \App\Services\SkuGenerator::preview(old('category', ''))) }}"
                                    style="font-family:monospace;"
                                    placeholder="Auto-generated on save" />
                                <div style="font-size: var(--title-size); color:rgba(255,255,255,0.35); margin-top:6px;">
                                    Auto-generated when you save. Preview updates as you pick a category.
                                </div>
                            @endif
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
                                    'M2': ['256GB', '500GB', '1TB', '2TB', '4TB', '8TB', 'ENCLOSURE', 'M.2 TRAY'],
                                    'VGA': ['RTX3050', 'RTX5080', 'RTX5090', 'RTX 5070TI', 'INTEL VGA', 'VGA AMD ALL SERIES', 'VGA RTX5070',
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
                                    const selectedBrand = pcPartBrandSelect.dataset.selected || '';

                                    if (brands) {
                                        pcPartBrandGroup.style.display = 'block';
                                        pcPartBrandSelect.innerHTML = '<option value="">— Select PC Part Brand —</option>';
                                        brands.forEach(b => {
                                            const opt = document.createElement('option');
                                            opt.value = b;
                                            opt.textContent = b;
                                            if (b === selectedBrand) opt.selected = true;
                                            pcPartBrandSelect.appendChild(opt);
                                        });
                                    } else {
                                        pcPartBrandGroup.style.display = 'none';
                                    }
                                }

                                categorySelect.addEventListener('change', updatePcPartBrands);
                                updatePcPartBrands();
                            </script>

                            <script>
                                // ── SKU preview: mirrors the server-side PREFIX_MAP so the
                                //    read-only preview updates as the category changes. The real
                                //    SKU is generated server-side on save. ─────────────────
                                const skuPrefixMap = {
                                    'CPU': 'CPU', 'RAM': 'RAM', 'VGA': 'VGA', 'MAINBOARD': 'MB',
                                    'COOLING': 'COOL', 'M2': 'M2', 'CASE': 'CASE',
                                    'POWER SUPPLY': 'PSU', 'FAN': 'FAN', 'KEYBOARD': 'KBD',
                                    'MOUSE': 'MOU', 'MOUSEPAD': 'MPD', 'HEADSET': 'HDS',
                                    'EARPHONE': 'EAR', 'SPEAKER': 'SPK', 'MICROPHONE': 'MIC',
                                    'MONITOR STAND': 'MST', 'ROUTER': 'RTR', 'STRIMER SET': 'STS',
                                    'SECRETLAB': 'SECRET', 'TTR RACING': 'TTR', 'DX RACER': 'DXR',
                                    'ASUS': 'ASU', 'FANTECH': 'FTK', 'BEST PRICE': 'BP'
                                };
                                const skuFallbackPrefixes = {};
                                ['MONITOR 25INCH','MONITOR 27INCH','MONITOR 32INCH','MONITOR 34INCH',
                                 'MONITOR 39INCH','MONITOR 42INCH','MONITOR 45INCH','MONITOR 48INCH',
                                 'MONITOR 49INCH'].forEach(c => skuFallbackPrefixes[c] = 'MON');
                                ['PC BUILD UNDER 1K','PC BUILD UNDER 2K','PC BUILD UNDER 3K',
                                 'PC BUILD UNDER 4K','PC BUILD UNDER 5K','PC BUILD 5K UP'].forEach(c => skuFallbackPrefixes[c] = 'PB');

                                function skuPrefixFor(category) {
                                    const c = (category || '').trim().toUpperCase();
                                    if (skuPrefixMap[c]) return skuPrefixMap[c];
                                    if (skuFallbackPrefixes[c]) return skuFallbackPrefixes[c];
                                    // Fallback: first 4 letters of the first word
                                    return (c.split(' ')[0] || c).substring(0, 4);
                                }

                                function updateSkuPreview() {
                                    const el = document.getElementById('skuPreview');
                                    if (!el) return;
                                    const cat = categorySelect.value;
                                    el.value = cat ? (skuPrefixFor(cat) + 'XXXXX') : '';
                                }
                                categorySelect.addEventListener('change', updateSkuPreview);
                            </script>
                        </div>


                        {{-- Price + Stock + Warranty --}}
                        <div class="form-grid-3 px-10">
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
                                <label class="form-label">{{ __('dashboard.form.warranty') }}</label>
                                <input type="text" name="warranty" class="form-control"
                                    value="{{ old('warranty', $product?->warranty) }}" placeholder="e.g. 3 years" />
                            </div>
                        </div>
                        {{-- Description --}}
                        <div class="form-group">
                            <label class="form-label">{{ __('dashboard.form.description') }}</label>
                            <textarea name="description" id="descriptionField" class="form-control" rows="6"
                                placeholder="Product description...">{{ old('description', $product?->description) }}</textarea>
                        </div>

                        {{-- ── Specifications Table ──────────────────────────── --}}
                        <div style="margin-top:24px; border-top:1px solid rgba(255,255,255,0.07); padding-top:20px;">
                            <label class="form-label"
                                style="font-size: var(--title-size); font-weight:800; letter-spacing:2px; margin-bottom:12px;">
                                📋 SPECIFICATIONS TABLE
                            </label>

                            <div class="form-group" style="margin-bottom:12px;">
                                <label class="form-label" style="font-size: var(--title-size);">TABLE TITLE</label>
                                <input type="text" name="specs_title" id="specsTitle" class="form-control"
                                    value="{{ old('specs_title', $product?->specs_title) }}"
                                    placeholder="e.g. Technical Specifications" />
                            </div>

                            <div id="specsRows">
                                @php
                                    $_specs = old('specs.key')
                                        ? array_combine(old('specs.key'), old('specs.value'))
                                        : $product?->specs ?? [];
                                @endphp
                                @if (is_array($_specs) && count($_specs))
                                    @foreach ($_specs as $sk => $sv)
                                        @continue(is_numeric($sk) && $sk === '')
                                        <div class="spec-row" style="display:flex; gap:8px; margin-bottom:8px;">
                                            <input type="text" name="specs[key][]" value="{{ $sk }}"
                                                class="form-control" placeholder="Key (e.g. Model)"
                                                style="flex:1; min-width:0;" onfocusin="this.style.borderColor='#F97316'"
                                                onfocusout="this.style.borderColor=''">
                                            <input type="text" name="specs[value][]" value="{{ $sv }}"
                                                class="form-control" placeholder="Value (e.g. AMD Ryzen 7)"
                                                style="flex:2; min-width:0;" onfocusin="this.style.borderColor='#F97316'"
                                                onfocusout="this.style.borderColor=''">
                                            <button type="button" onclick="this.closest('.spec-row').remove()"
                                                class="btn btn-sm"
                                                style="border:1px solid rgba(239,68,68,0.3); color:#ef4444; background:transparent; flex-shrink:0; padding:0 12px;">✕</button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="spec-row" style="display:flex; gap:8px; margin-bottom:8px;">
                                        <input type="text" name="specs[key][]" class="form-control"
                                            placeholder="Key (e.g. Model)" style="flex:1; min-width:0;"
                                            onfocusin="this.style.borderColor='#F97316'"
                                            onfocusout="this.style.borderColor=''">
                                        <input type="text" name="specs[value][]" class="form-control"
                                            placeholder="Value (e.g. AMD Ryzen 7)" style="flex:2; min-width:0;"
                                            onfocusin="this.style.borderColor='#F97316'"
                                            onfocusout="this.style.borderColor=''">
                                        <button type="button" onclick="this.closest('.spec-row').remove()"
                                            class="btn btn-sm"
                                            style="border:1px solid rgba(239,68,68,0.3); color:#ef4444; background:transparent; flex-shrink:0; padding:0 12px;">✕</button>
                                    </div>
                                @endif
                            </div>

                            <button type="button" onclick="addSpecRow()" class="btn btn-outline btn-sm"
                                style="margin-top:4px;">
                                + ADD SPEC ROW
                            </button>
                        </div>

                        {{-- Stock Status + Details --}}
                        <div class="form-grid-2" style="margin-top:20px;">
                            <div class="form-group">
                                <label class="form-label">Stock Status</label>
                                <input type="text" name="stock_status" class="form-control" list="stockStatusList"
                                    value="{{ old('stock_status', $product?->stock_status) }}"
                                    placeholder="Select or type status" />
                                <datalist id="stockStatusList">
                                    @foreach ($stockStatuses ?? [] as $status)
                                        <option value="{{ $status }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Stock Details</label>
                                <input type="text" name="stock_details" class="form-control"
                                    value="{{ old('stock_details', $product?->stock_details) }}"
                                    placeholder="e.g. Arriving next week" />
                            </div>
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

                        {{-- ── Submit Buttons ────────────────────────────────────── --}}
                        <div
                            style="position:sticky; bottom:0; background:#1A1A1A; display:flex; justify-content:center; gap:12px; margin-top:24px; padding:20px 0; border-top:1px solid rgba(255,255,255,0.07);">
                            <button type="submit" class="btn btn-orange" id="submitBtn">
                                <svg width="14" height="14" fill="none" stroke="currentColor"
                                    stroke-width="2.5" viewBox="0 0 24 24">
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
                        <form id="deleteForm" method="POST"
                            action="{{ route('dashboard.products.destroy', $product) }}">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </div>
            </div>
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

        #productFormCard {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 auto !important;
        }

        /* Product form: SINGLE source of card-level horizontal padding. The
                       layout's .content (24px) provides the outer page gap; this controls
                       the gap from the card border to the fields. Values mirror the
                       layout's breakpoints (≤1024px → 16px, ≤480px → 12px) so the
                       responsive intent is preserved instead of overridden. */
        #productFormCard .card-body {
            padding-left: 20px;
            padding-right: 20px;
        }

        @media (max-width: 1024px) {
            #productFormCard .card-body {
                padding-left: 16px;
                padding-right: 16px;
            }
        }

        @media (max-width: 480px) {
            #productFormCard .card-body {
                padding-left: 12px;
                padding-right: 12px;
            }
        }

        /* ── Form Grid Utilities ───────────────────────────────────────────────── */
        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 16px;
        }

        /* ── Horizontal Form Group (label | input + description) ─────────── */
        .form-group-horizontal {
            display: grid;
            grid-template-columns: minmax(140px, 180px) 1fr;
            gap: 16px;
            align-items: start;
        }

        .form-group-horizontal .form-label {
            margin-bottom: 0;
            padding-top: 2px;
            white-space: nowrap;
        }

        .form-group-horizontal .form-control-wrapper {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group-horizontal .form-description {
            font-size: var(--title-size);
            color: rgba(255, 255, 255, 0.35);
        }

        @media (max-width: 768px) {
            .form-group-horizontal {
                grid-template-columns: 1fr;
            }

            .form-group-horizontal .form-label {
                margin-bottom: 7px;
            }
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

        /* ── Gallery as a drag-and-drop upload target ────────────────── */
        .gallery-drop-target {
            outline: 2px dashed #F97316;
            outline-offset: 4px;
            border-radius: 12px;
            background: rgba(249, 115, 22, 0.05);
        }

        .gallery-drop-target .gallery-add-slot {
            border-color: #F97316;
            color: #F97316;
            background: rgba(249, 115, 22, 0.08);
        }

        /* ── Form invalid ──────────────────────────────────────────────────────── */
        .form-control.is-invalid {
            border-color: #EF4444;
        }

        /* ── Category optgroup styling — scoped to the product form card, matching
                         the banner category select (dark bg, bold orange optgroup headers) ── */
        #productFormCard select.form-control option,
        #productFormCard select.form-control optgroup {
            background: #1A1A1A;
            color: #fff;
            font-family: 'Rajdhani', sans-serif;
            /* Center the label text, keep rows compact with tight padding + line-height */
            text-align: center;
            padding: 7px 10px;
            line-height: 1.3;
        }

        #productFormCard select.form-control optgroup {
            color: #F97316;
            font-weight: 700;
            font-size: 19px;
        }

        #productFormCard select.form-control option {
            font-weight: 700;
            font-size: 19px;
        }

        /* ── Responsive ────────────────────────────────────────────────────────── */
        @media (max-width: 768px) {
            .form-grid-3 {
                grid-template-columns: 1fr 1fr;
            }

            .form-grid-2 {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .form-grid-3 {
                grid-template-columns: 1fr;
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

        [data-theme="light"] .gallery-drop-target {
            outline: 2px dashed #F97316;
            background: rgba(249, 115, 22, 0.04) !important;
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

        [data-theme="light"] [style*="background:#1A1A1A"] {
            background: #F8FAFC !important;
            border-top-color: rgba(15, 23, 42, 0.08) !important;
        }

        [data-theme="light"] .form-description {
            color: rgba(15, 23, 42, 0.45) !important;
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
            // Accept either an <input> element or a raw FileList/File[] (from a
            // drag-and-drop drop event) so both paths share the same pipeline.
            const files = Array.from(input.files || input || [])
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

        // ── Auto-add image by URL (fires after paste/change) ──────────────────────
        // Automatically adds the pasted URL without requiring the "+ ADD" click.
        // Only triggers when the field contains a valid-looking http(s) URL and the
        // input is still focused/active, so it doesn't loop or re-add on blur.
        let _autoAddingUrl = false

        function autoAddImageByUrl() {
            const input = document.getElementById('imageUrlInput')
            const url = (input ? input.value : '').trim()
            if (!url) return
            if (!/^https?:\/\/\S+$/i.test(url)) return
            if (_autoAddingUrl) return

            const gallery = document.getElementById('imageGallery')
            const existing = Array.from(gallery.querySelectorAll('.gallery-thumb'))
            if (existing.length >= 8) {
                alert('Max 8 images.');
                return
            }

            _autoAddingUrl = true
            const img = new Image()
            img.onload = () => {
                _autoAddingUrl = false
                // Add via the same path as the "+ ADD" button (dedupes by clearing the input)
                addImageByUrl()
            }
            img.onerror = () => {
                _autoAddingUrl = false
            }
            img.src = url
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

        // ── Drag-and-drop multi-image upload ──────────────────────────────────
        // The gallery doubles as a drop zone for uploading image *files*.
        // Thumbnail reordering is handled separately by each thumb's own native
        // drag handlers, so these gallery-level listeners only react to file
        // drags (dataTransfer carries Files) and skip thumbnail drags entirely.
        const galleryEl = document.getElementById('imageGallery')
        let dragDepth = 0 // dragenter/dragleave fire once per child — track nesting

        function isFileDrag(e) {
            return e.dataTransfer && Array.from(e.dataTransfer.types || []).indexOf('Files') !== -1
        }

        galleryEl.addEventListener('dragenter', (e) => {
            if (!isFileDrag(e)) return // thumbnail reorder drags don't concern us
            e.preventDefault()
            dragDepth++
            galleryEl.classList.add('gallery-drop-target')
        })
        galleryEl.addEventListener('dragover', (e) => {
            if (!isFileDrag(e)) return
            e.preventDefault() // required for the browser to allow the drop
            e.dataTransfer.dropEffect = 'copy'
            galleryEl.classList.add('gallery-drop-target')
        })
        galleryEl.addEventListener('dragleave', (e) => {
            if (!isFileDrag(e)) return
            e.preventDefault()
            dragDepth = Math.max(0, dragDepth - 1)
            if (dragDepth === 0) galleryEl.classList.remove('gallery-drop-target')
        })
        galleryEl.addEventListener('drop', (e) => {
            // Only handle real file drops here; thumbnail drops are handled by
            // each thumb's native ondrop, which fires on the target before this
            // listener sees the bubbling event (and isFileDrag() is false there).
            if (!isFileDrag(e)) return
            e.preventDefault()
            e.stopPropagation()
            dragDepth = 0
            galleryEl.classList.remove('gallery-drop-target')

            const files = Array.from(e.dataTransfer?.files || []).filter(f => f.type.startsWith('image/'))
            if (files.length > 0) {
                handleMultiImages({ files })
            }
        })

        // ── Add spec row ────────────────────────────────────────────────────────
        function addSpecRow() {
            const container = document.getElementById('specsRows');
            const row = document.createElement('div');
            row.className = 'spec-row';
            row.style.cssText = 'display:flex; gap:8px; margin-bottom:8px;';
            row.innerHTML = `
                <input type="text" name="specs[key][]" class="form-control"
                    placeholder="Key (e.g. Model)"
                    style="flex:1; min-width:0;"
                    onfocusin="this.style.borderColor='#F97316'"
                    onfocusout="this.style.borderColor=''">
                <input type="text" name="specs[value][]" class="form-control"
                    placeholder="Value (e.g. AMD Ryzen 7)"
                    style="flex:2; min-width:0;"
                    onfocusin="this.style.borderColor='#F97316'"
                    onfocusout="this.style.borderColor=''">
                <button type="button" onclick="this.closest('.spec-row').remove()"
                    class="btn btn-sm"
                    style="border:1px solid rgba(239,68,68,0.3); color:#ef4444; background:transparent; flex-shrink:0; padding:0 12px;">✕</button>
            `;
            container.appendChild(row);
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
