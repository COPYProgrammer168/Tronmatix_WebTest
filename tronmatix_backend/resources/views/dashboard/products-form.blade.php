@extends('dashboard.layout')
@section('title', $product ? 'EDIT PRODUCT' : 'ADD PRODUCT')

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



    <div style="max-width:860px;">

        <a href="{{ route('dashboard.products') }}" class="btn btn-outline btn-sm" style="margin-bottom:20px;">
            ← BACK TO PRODUCTS
        </a>

        <div class="card">
            <div class="card-header">
                <span class="card-title" style="font-size:25px;">
                    {{ $product ? 'EDIT: ' . Str::limit($product->name, 40) : 'ADD NEW PRODUCT' }}
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
                                <label class="form-label">PRODUCT NAME *</label>
                                <input type="text" name="name"
                                    class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                    value="{{ old('name', $product?->name) }}" placeholder="e.g. AMD Ryzen 7 9800X3D"
                                    required />
                            </div>

                            {{-- Category + Brand --}}
                            <div class="form-grid-2">
                                <div class="form-group" style="font-size:18px;">
                                    <label class="form-label">CATEGORY *</label>
                                    <select name="category" class="form-control" required>
                                        <option value="" disabled
                                            {{ old('category', $product?->category) ? '' : 'selected' }}>
                                            — Select Category —
                                        </option>

                                        {{-- <optgroup label="─── NEW ADD ───────────────">
                                            @foreach (['New Arrival'] as $cat)
                                                <option value="{{ $cat }}"
                                                    {{ request('category') === $cat ? 'selected' : '' }}>
                                                    {{ $cat }}
                                                </option>
                                            @endforeach
                                        </optgroup> --}}

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
                                        <optgroup label="─── PC PARTS ───────────────">
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

                                        <optgroup label="─── FURNITURE ──────────────">
                                            @foreach (['CHAIR', 'DESK', 'MONITOR STAND'] as $cat)
                                                <option value="{{ $cat }}"
                                                    {{ old('category', $product?->category) === $cat ? 'selected' : '' }}>
                                                    {{ $cat }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">BRAND</label>
                                    <input type="text" name="brand" class="form-control"
                                        value="{{ old('brand', $product?->brand) }}" placeholder="e.g. AMD, Intel, NVIDIA"
                                        list="brandList" />
                                    <datalist id="brandList">
                                        @foreach (['AMD', 'Intel', 'NVIDIA', 'ASUS', 'MSI', 'Gigabyte', 'Corsair', 'G.Skill', 'Kingston', 'Samsung', 'WD', 'Seagate', 'Noctua', 'NZXT', 'EVGA', 'Seasonic', 'Lian Li', 'Fractal', 'Logitech', 'Razer', 'SteelSeries', 'HyperX'] as $brand)
                                            <option value="{{ $brand }}">
                                        @endforeach
                                    </datalist>
                                </div>
                            </div>

                            {{-- Price + Stock + Rating --}}
                            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
                                <div class="form-group">
                                    <label class="form-label">PRICE (USD) *</label>
                                    <input type="number" name="price"
                                        class="form-control {{ $errors->has('price') ? 'is-invalid' : '' }}"
                                        value="{{ old('price', $product?->price) }}" step="0.01" min="0"
                                        placeholder="0.00" required />
                                </div>
                                <div class="form-group">
                                    <label class="form-label">STOCK *</label>
                                    <input type="number" name="stock"
                                        class="form-control {{ $errors->has('stock') ? 'is-invalid' : '' }}"
                                        value="{{ old('stock', $product?->stock ?? 0) }}" min="0" required />
                                </div>
                                <div class="form-group">
                                    <label class="form-label">RATING (0-5)</label>
                                    <input type="number" name="rating" class="form-control"
                                        value="{{ old('rating', $product?->rating ?? 0) }}" step="0.1" min="0"
                                        max="5" />
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="form-group">
                                <label class="form-label">DESCRIPTION</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Product description...">{{ old('description', $product?->description) }}</textarea>
                            </div>

                            {{-- Featured + Hot toggles --}}
                            <div style="display:flex; gap:32px; margin-top:4px;">
                                <div class="form-group">
                                    <label class="form-label">FEATURED PRODUCT</label>
                                    <label class="toggle-wrap" style="cursor:pointer;">
                                        <label class="toggle">
                                            <input type="checkbox" name="is_featured" value="1"
                                                {{ old('is_featured', $product?->is_featured) ? 'checked' : '' }} />
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span style="font-size:13px; color:rgba(255,255,255,0.5);">
                                            Show on featured section
                                        </span>
                                    </label>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">HOT ITEM</label>
                                    <label class="toggle-wrap" style="cursor:pointer;">
                                        <label class="toggle">
                                            <input type="checkbox" name="is_hot" value="1"
                                                {{ old('is_hot', $product?->is_hot) ? 'checked' : '' }} />
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span style="font-size:13px; color:rgba(255,255,255,0.5);">
                                            Mark as hot item 🔥
                                        </span>
                                    </label>
                                </div>
                            </div>

                        </div>

                        {{-- ── RIGHT COLUMN: Image Upload ────────────────────── --}}
                        <div>
                            <div class="form-group">
                                <label class="form-label">PRODUCT IMAGES</label>
                                <div style="font-size:11px; color:rgba(255,255,255,0.3); margin-bottom:8px;">
                                    First image = main image. Drag to reorder. Max 8 images, 2MB each.
                                </div>

                                {{-- Multi-image gallery grid --}}
                                <div id="imageGallery"
                                    style="
                                    display:grid;
                                    grid-template-columns: repeat(3, 1fr);
                                    gap:8px;
                                    margin-bottom:10px;
                                ">
                                    {{-- Existing images (edit mode) --}}
                                    {{-- data-raw-path = original DB value. Submit handler reads this,
                                         NOT img.src which is a full browser URL and would get saved
                                         back to DB causing double-prefix on next edit. --}}
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
                                        <span>Add Images</span>
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
                                    UPLOAD IMAGES (Multi-select)
                                </button>

                                {{-- Divider --}}
                                <div style="display:flex; align-items:center; gap:10px; margin:14px 0;">
                                    <div style="flex:1; height:1px; background:rgba(255,255,255,0.07);"></div>
                                    <span
                                        style="font-size:11px; color:rgba(255,255,255,0.2); letter-spacing:1px;">OR</span>
                                    <div style="flex:1; height:1px; background:rgba(255,255,255,0.07);"></div>
                                </div>

                                {{-- URL Input for extra images --}}
                                <label class="form-label">ADD IMAGE BY URL</label>
                                <div style="display:flex; gap:8px;">
                                    <input type="text" id="imageUrlInput" class="form-control"
                                        placeholder="https://example.com/image.jpg" style="flex:1;" />
                                    <button type="button" class="btn btn-outline" onclick="addImageByUrl()"
                                        style="white-space:nowrap; padding:0 14px;">+ ADD</button>
                                </div>
                                <div id="urlPreviewMsg"
                                    style="margin-top:6px; font-size:11px; color:rgba(255,255,255,0.2);"></div>

                                {{-- Hidden field to track new URL images --}}
                                <div id="urlImagesContainer"></div>

                                {{-- Remove image button (edit mode only) --}}
                                @if ($product?->image || ($product?->images && count($product->images)))
                                    <div style="margin-top:12px;">
                                        <label class="toggle-wrap" style="cursor:pointer;">
                                            <input type="checkbox" name="remove_image" value="1"
                                                id="removeImageCheck" onchange="toggleRemoveImage(this)" />
                                            <span style="font-size:12px; color:rgba(239,68,68,0.7);">
                                                Remove all images
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
                            {{ $product ? 'UPDATE PRODUCT' : 'CREATE PRODUCT' }}
                        </button>
                        <a href="{{ route('dashboard.products') }}" class="btn btn-outline">
                            CANCEL
                        </a>
                        @if ($product)
                            <div style="margin-left:auto;">
                                <button type="button" class="btn btn-danger"
                                    onclick="if(confirm('Delete this product permanently?')) document.getElementById('deleteForm').submit()">
                                    DELETE PRODUCT
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
            font-size: 9px;
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
            font-size: 11px;
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
            font-size: 11px;
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
            font-size: 11px;
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