@extends('dashboard.layout')

@section('title', 'EDIT BRAND')

@section('content')
@include('dashboard._permission_check', ['feature' => 'products'])
@php $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false; @endphp

@if(!$_permDenied)
<div style="max-width:720px; margin:0 auto; padding:24px;">

    {{-- Header --}}
    <div style="margin-bottom:24px;">
        <a href="{{ route('dashboard.brands.index') }}" style="
            color:var(--text-muted); text-decoration:none; font-size:14px; font-weight:600;
        " onmouseover="this.style.color='#F97316'" onmouseout="this.style.color='var(--text-muted)'">← Back to brand logos</a>
        <h1 style="font-size:var(--text-2xl); font-weight:800; letter-spacing:2px; color:var(--text); margin:12px 0 4px;">EDIT BRAND</h1>
        <p style="font-size:var(--text-sm); color:var(--text-muted);">Change the logo, name, or display order.</p>
    </div>

    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:28px;">
        <form method="POST" action="{{ route('dashboard.brands.update', $brand) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div style="display:flex; flex-direction:column; gap:24px;">

                {{-- Current logo + upload --}}
                <div>
                    <label class="block font-bold mb-3" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">BRAND LOGO</label>

                    @if($brand->image)
                    <div id="currentImageSection" style="margin-bottom:16px; display:flex; align-items:center; gap:16px;">
                        <img src="{{ storage_url($brand->image) }}" alt="{{ $brand->name }}"
                             style="width:80px; height:80px; object-fit:contain; border-radius:12px;
                                    background:var(--surface-2); border:1px solid var(--border); padding:6px;">
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size:13px; color:#ef4444; font-weight:600;">
                            <input type="checkbox" name="remove_image" value="1" style="width:16px; height:16px; accent-color:#ef4444;">
                            Remove current logo
                        </label>
                    </div>
                    @endif

                    <div id="logoDropZone" style="
                        border:2px dashed var(--border-input); border-radius:16px; padding:28px 20px;
                        text-align:center; cursor:pointer; transition:all .2s;
                        background:var(--surface-2);"
                        onclick="document.getElementById('logoFileInput').click()"
                        onmouseenter="this.style.borderColor='#F97316'; this.style.background='rgba(249,115,22,0.03)'"
                        onmouseleave="this.style.borderColor='var(--border-input)'; this.style.background='var(--surface-2)'"
                        ondragover="event.preventDefault(); this.style.borderColor='#F97316'; this.style.background='rgba(249,115,22,0.05)'"
                        ondragleave="event.preventDefault(); this.style.borderColor='var(--border-input)'; this.style.background='var(--surface-2)'"
                        ondrop="handleLogoDrop(event)">
                        <div id="logoPreview" style="display:none; margin-bottom:10px;">
                            <img id="logoPreviewImg" src="" alt="Logo preview"
                                 style="max-width:180px; max-height:120px; object-fit:contain; border-radius:10px;
                                        background:var(--surface); padding:8px; border:1px solid var(--border);">
                        </div>
                        <div id="logoPlaceholder">
                            <div style="font-size:32px; margin-bottom:6px;">📁</div>
                            <div style="font-size:14px; font-weight:700; color:var(--text);">
                                Drop new logo or click to browse
                            </div>
                            <div style="font-size:12px; color:var(--text-xfaint);">PNG, JPG, WebP, SVG · max 2MB</div>
                        </div>
                    </div>
                    <input type="file" name="image_file" id="logoFileInput" accept="image/*" style="display:none;"
                           onchange="previewLogo(this)">
                    @error('image_file') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                {{-- Logo URL --}}
                <div>
                    <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">OR LOGO URL</label>
                    <input type="url" name="image_url" id="logoUrlInput" value="{{ old('image_url', $brand->image_url) }}" class="s-input" style="margin-top:6px;"
                           placeholder="https://example.com/logo.png" oninput="previewLogoUrl(this)">
                    @error('image_url') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                {{-- Name + Order --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">BRAND NAME *</label>
                        <input type="text" name="name" value="{{ old('name', $brand->name) }}" required class="s-input" style="margin-top:6px;">
                        @error('name') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">SORT ORDER</label>
                        <input type="number" name="order" value="{{ old('order', $brand->order) }}" min="0" class="s-input" style="margin-top:6px;">
                        @error('order') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Active toggle --}}
                <div style="display:flex; align-items:center; gap:10px; margin-top:4px;">
                    <label style="position:relative; display:inline-block; width:44px; height:24px; cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $brand->is_active) ? 'checked' : '' }}
                               style="opacity:0; width:0; height:0;" onchange="updateEditToggle(this)">
                        <span id="editTrack" style="position:absolute; inset:0; background:#22c55e; border-radius:24px; transition:.2s;"></span>
                        <span id="editDot" style="position:absolute; top:2px; left:2px; width:20px; height:20px; background:#fff;
                                     border-radius:50%; transition:.2s; box-shadow:0 1px 3px rgba(0,0,0,0.2);"></span>
                    </label>
                    <span style="font-size:14px; color:var(--text-muted);">
                        {{ $brand->is_active ? 'Active — visible to customers' : 'Hidden — not visible to customers' }}
                    </span>
                </div>

                {{-- Actions --}}
                <div style="display:flex; gap:12px; margin-top:8px; padding-top:16px; border-top:1px solid var(--border);">
                    <button type="submit" class="btn btn-orange" style="flex:1; padding:14px; font-size:15px;">
                        💾 UPDATE BRAND
                    </button>
                    <a href="{{ route('dashboard.brands.index') }}" class="btn" style="
                        flex:1; padding:14px; text-align:center; text-decoration:none; font-size:15px;
                        border:1.5px solid var(--border-input); background:transparent; color:var(--text-muted);">
                        CANCEL
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function previewLogo(input) {
        const preview = document.getElementById('logoPreview');
        const img = document.getElementById('logoPreviewImg');
        const placeholder = document.getElementById('logoPlaceholder');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    function previewLogoUrl(input) {
        const url = (input.value || '').trim();
        const preview = document.getElementById('logoPreview');
        const img = document.getElementById('logoPreviewImg');
        const placeholder = document.getElementById('logoPlaceholder');
        if (!url) { preview.style.display = 'none'; placeholder.style.display = 'block'; return; }
        const test = new Image();
        test.onload = function() {
            img.src = url;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        };
        test.onerror = function() { preview.style.display = 'none'; placeholder.style.display = 'block'; };
        test.src = url;
    }
    function handleLogoDrop(e) {
        e.preventDefault();
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const input = document.getElementById('logoFileInput');
            const dt = new DataTransfer();
            dt.items.add(files[0]);
            input.files = dt.files;
            previewLogo(input);
        }
    }
    function updateEditToggle(cb) {
        const track = document.getElementById('editTrack');
        const dot = document.getElementById('editDot');
        if (cb.checked) {
            track.style.background = '#22c55e';
            dot.style.transform = 'translateX(20px)';
        } else {
            track.style.background = '#6b7280';
            dot.style.transform = 'translateX(0)';
        }
    }
</script>
@endpush
@endif
@endsection
