@extends('dashboard.layout')

@section('title', 'ADD BRAND')

@section('content')
@include('dashboard._permission_check', ['feature' => 'products'])
@php $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false; @endphp

@if(!$_permDenied)
<div style="max-width:800px; margin:0 auto; padding:24px;">
    <div style="margin-bottom:24px;">
        <a href="{{ route('dashboard.brands.index') }}" style="
            color:var(--text-muted); text-decoration:none; font-size:14px; font-weight:600;
            transition:color .2s;
        " onmouseover="this.style.color='#F97316'" onmouseout="this.style.color='var(--text-muted)'">← Back to brands</a>
        <h1 style="font-size:var(--text-2xl); font-weight:800; letter-spacing:2px; color:var(--text); margin:12px 0 4px;">ADD BRAND</h1>
        <p style="font-size:var(--text-sm); color:var(--text-muted);">Level 4 of the navigation tree (Category → MainCate → SubCate → Brand)</p>
    </div>

    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:24px;">
        <form method="POST" action="{{ route('dashboard.brands.store') }}" enctype="multipart/form-data">
            @csrf

            <div style="display:flex; flex-direction:column; gap:20px;">
                <div>
                    <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">PARENT SUB CATEGORY *</label>
                    <select name="sub_category_id" required class="s-input" style="margin-top:6px;">
                        <option value="">— Select a Sub Category —</option>
                        @foreach($subCategories as $sc)
                            <option value="{{ $sc->id }}" {{ old('sub_category_id') == $sc->id ? 'selected' : '' }}>
                                {{ $sc->mainCategory->category->name ?? '—' }} → {{ $sc->mainCategory->name ?? '—' }} → {{ $sc->name }}
                            </option>
                        @endforeach
                    </select>
                    <div style="font-size:12px; color:var(--text-xfaint); margin-top:4px;">Only active sub categories are shown.</div>
                    @error('sub_category_id') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">NAME *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="s-input" style="margin-top:6px;" placeholder="e.g. INTEL 12TH">
                    <div style="font-size:12px; color:var(--text-xfaint); margin-top:4px;">Slug is generated automatically from the name.</div>
                    @error('name') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">BRAND LOGO IMAGE</label>
                    <input type="file" name="image_file" accept="image/*" class="s-input" style="margin-top:6px; padding:8px;" id="imageFileInput"
                        onchange="previewImage(this)">
                    <div style="font-size:12px; color:var(--text-xfaint); margin-top:4px;">JPG, PNG, WebP or GIF (max 50MB)</div>
                    @error('image_file') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                <div id="imagePreviewContainer" style="display:none;">
                    <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">IMAGE PREVIEW</label>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <img id="imagePreviewImg" src="" alt="Image preview" style="width:64px; height:64px; border-radius:10px; object-fit:cover; background:var(--surface-2); border:1px solid var(--border);">
                        <span style="font-size:12px; color:var(--text-xfaint);" id="imagePreviewName"></span>
                    </div>
                </div>

                <div>
                    <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">OR IMAGE URL</label>
                    <input type="url" name="image_url" value="{{ old('image_url') }}" class="s-input" style="margin-top:6px;" placeholder="https://example.com/logo.png"
                        oninput="previewImageUrl(this)">
                    @error('image_url') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                <div id="imageUrlPreviewContainer" style="display:none;">
                    <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">URL PREVIEW</label>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <img id="imageUrlPreviewImg" src="" alt="Image URL preview" style="width:64px; height:64px; border-radius:10px; object-fit:cover; background:var(--surface-2); border:1px solid var(--border);">
                        <span style="font-size:12px; color:var(--text-xfaint);" id="imageUrlPreviewName"></span>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">SORT ORDER</label>
                        <input type="number" name="order" value="{{ old('order', 0) }}" min="0" class="s-input" style="margin-top:6px;">
                        @error('order') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">STATUS</label>
                        <label style="display:flex; align-items:center; gap:8px; margin-top:10px; cursor:pointer;">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} style="width:18px; height:18px; accent-color:#F97316;">
                            <span style="font-size:14px; color:var(--text-muted);">Active (visible to customers)</span>
                        </label>
                    </div>
                </div>

                <div style="display:flex; gap:12px; margin-top:8px;">
                    <button type="submit" class="btn btn-orange" style="flex:1; padding:14px;">
                        💾 CREATE BRAND
                    </button>
                    <a href="{{ route('dashboard.brands.index') }}" class="btn" style="
                        flex:1; padding:14px; text-align:center; text-decoration:none;
                        border:1.5px solid var(--border-input); background:transparent;
                        color:var(--text-muted);">CANCEL</a>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function previewImage(input) {
        const container = document.getElementById('imagePreviewContainer');
        const img = document.getElementById('imagePreviewImg');
        const nameEl = document.getElementById('imagePreviewName');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                container.style.display = 'flex';
                nameEl.textContent = input.files[0].name;
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            container.style.display = 'none';
        }
    }
    function previewImageUrl(input) {
        const container = document.getElementById('imageUrlPreviewContainer');
        const img = document.getElementById('imageUrlPreviewImg');
        const nameEl = document.getElementById('imageUrlPreviewName');
        if (input.value.trim()) {
            img.src = input.value;
            container.style.display = 'flex';
            nameEl.textContent = input.value;
        } else {
            container.style.display = 'none';
        }
    }
</script>
@endpush
@endif
@endsection
