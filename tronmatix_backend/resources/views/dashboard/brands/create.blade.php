@extends('dashboard.layout')

@section('title', 'ADD BRAND')

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
        <h1 style="font-size:var(--text-2xl); font-weight:800; letter-spacing:2px; color:var(--text); margin:12px 0 4px;">ADD BRAND LOGO</h1>
        <p style="font-size:var(--text-sm); color:var(--text-muted);">Upload a logo image and set the brand name.</p>
    </div>

    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:28px;">
        <form method="POST" action="{{ route('dashboard.brands.store') }}" enctype="multipart/form-data">
            @csrf

            <div style="display:flex; flex-direction:column; gap:24px;">

                {{-- Logo upload area --}}
                <div>
                    <label class="block font-bold mb-3" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">BRAND LOGO</label>
                    <div id="logoDropZone" style="
                        border:2px dashed var(--border-input); border-radius:16px; padding:32px 20px;
                        text-align:center; cursor:pointer; transition:all .2s;
                        background:var(--surface-2);"
                        onclick="document.getElementById('logoFileInput').click()"
                        onmouseenter="this.style.borderColor='#F97316'; this.style.background='rgba(249,115,22,0.03)'"
                        onmouseleave="this.style.borderColor='var(--border-input)'; this.style.background='var(--surface-2)'"
                        ondragover="event.preventDefault(); this.style.borderColor='#F97316'; this.style.background='rgba(249,115,22,0.05)'"
                        ondragleave="this.style.borderColor='var(--border-input)'; this.style.background='var(--surface-2)'"
                        ondrop="event.preventDefault(); this.style.borderColor='var(--border-input)'; this.style.background='var(--surface-2)'; handleLogoDrop(event)">
                        <div id="logoPreview" style="display:none; margin-bottom:12px;">
                            <img id="logoPreviewImg" src="" alt="Logo preview"
                                 style="max-width:180px; max-height:120px; object-fit:contain; border-radius:10px;
                                        background:var(--surface); padding:8px; border:1px solid var(--border);">
                        </div>
                        <div id="logoPlaceholder">
                            <div style="font-size:36px; margin-bottom:8px;">🏷️</div>
                            <div style="font-size:14px; font-weight:700; color:var(--text); margin-bottom:4px;">
                                Drop logo here or click to browse
                            </div>
                            <div style="font-size:12px; color:var(--text-xfaint);">
                                PNG, JPG, WebP, SVG · max 2MB
                            </div>
                        </div>
                    </div>
                    <input type="file" name="image_file" id="logoFileInput" accept="image/*" style="display:none;"
                           onchange="previewLogo(this)">
                    <div style="font-size:11px; color:var(--text-xfaint); margin-top:6px;">
                        Or paste a URL below instead of uploading a file.
                    </div>
                    @error('image_file') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                {{-- Logo URL --}}
                <div>
                    <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">OR LOGO URL</label>
                    <input type="url" name="image_url" id="logoUrlInput" value="{{ old('image_url') }}" class="s-input" style="margin-top:6px;"
                           placeholder="https://example.com/logo.png" oninput="previewLogoUrl(this)">
                    @error('image_url') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                {{-- Name + Order row --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">BRAND NAME *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="s-input" style="margin-top:6px;"
                               placeholder="e.g. AMD, INTEL, NVIDIA">
                        @error('name') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">SORT ORDER</label>
                        <input type="number" name="order" value="{{ old('order', 0) }}" min="0" class="s-input" style="margin-top:6px;">
                        <div style="font-size:11px; color:var(--text-xfaint); margin-top:4px;">0 = first</div>
                        @error('order') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Active toggle --}}
                <div style="display:flex; align-items:center; gap:10px; margin-top:4px;">
                    <label style="position:relative; display:inline-block; width:44px; height:24px; cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               style="opacity:0; width:0; height:0;">
                        <span style="position:absolute; inset:0; background:#22c55e; border-radius:24px; transition:.2s;"
                              class="toggle-track"></span>
                        <span style="position:absolute; top:2px; left:2px; width:20px; height:20px; background:#fff;
                                     border-radius:50%; transition:.2s; box-shadow:0 1px 3px rgba(0,0,0,0.2);"
                              class="toggle-dot"></span>
                    </label>
                    <span style="font-size:14px; color:var(--text-muted);">Active — visible to customers</span>
                </div>

                {{-- Actions --}}
                <div style="display:flex; gap:12px; margin-top:8px; padding-top:16px; border-top:1px solid var(--border);">
                    <button type="submit" class="btn btn-orange" style="flex:1; padding:14px; font-size:15px;">
                        💾 CREATE BRAND
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
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const input = document.getElementById('logoFileInput');
            const dt = new DataTransfer();
            dt.items.add(files[0]);
            input.files = dt.files;
            previewLogo(input);
        }
    }

    // Toggle switch visual
    const toggle = document.querySelector('input[name="is_active"]');
    const track = document.querySelector('.toggle-track');
    const dot = document.querySelector('.toggle-dot');
    function updateToggle() {
        if (toggle.checked) {
            track.style.background = '#22c55e';
            dot.style.transform = 'translateX(20px)';
        } else {
            track.style.background = '#6b7280';
            dot.style.transform = 'translateX(0)';
        }
    }
    toggle.addEventListener('change', updateToggle);
    updateToggle();
</script>
@endpush
@endsection
