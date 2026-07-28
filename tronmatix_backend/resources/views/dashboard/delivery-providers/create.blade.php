@extends('dashboard.layout')

@section('title', 'ADD DELIVERY PROVIDER')

@section('content')
<div style="max-width:800px; margin:0 auto; padding:24px;">
    <div style="margin-bottom:24px;">
        <a href="{{ route('dashboard.delivery-providers.index') }}" style="
            color:rgba(255,255,255,0.4); text-decoration:none; font-size:14px; font-weight:600;
            transition:color .2s;
        " onmouseover="this.style.color='#F97316'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">← Back to providers</a>
        <h1 style="font-size:28px; font-weight:800; letter-spacing:2px; color:#fff; margin:12px 0 4px;">ADD DELIVERY PROVIDER</h1>
        <p style="font-size:14px; color:rgba(255,255,255,0.4);">Create a new delivery provider for a zone</p>
    </div>

    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:16px; padding:24px;">
        <form method="POST" action="{{ route('dashboard.delivery-providers.store') }}" enctype="multipart/form-data">
            @csrf

            <div style="display:flex; flex-direction:column; gap:20px;">
                <div>
                    <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.6);">DELIVERY ZONE *</label>
                    <select name="delivery_zone_id" required class="s-input" style="margin-top:6px; cursor:pointer;">
                        <option value="">— Select zone —</option>
                        @foreach($zones as $zone)
                        <option value="{{ $zone->id }}" {{ old('delivery_zone_id') == $zone->id ? 'selected' : '' }}>{{ $zone->name }}</option>
                        @endforeach
                    </select>
                    @error('delivery_zone_id') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.6);">PROVIDER NAME *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="s-input" style="margin-top:6px;" placeholder="e.g. Naga Express">
                    @error('name') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.6);">FEE ($)</label>
                        <input type="number" name="fee" value="{{ old('fee') }}" step="0.01" min="0" class="s-input" style="margin-top:6px;" placeholder="e.g. 2.50">
                        <div style="font-size:12px; color:rgba(255,255,255,0.3); margin-top:4px;">Leave empty for negotiable / varies</div>
                        @error('fee') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.6);">ESTIMATED TIME</label>
                        <input type="text" name="estimated_time" value="{{ old('estimated_time') }}" class="s-input" style="margin-top:6px;" placeholder="e.g. 30–60 min">
                        @error('estimated_time') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div>
                    <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.6);">LOGO</label>
                    <input type="file" name="logo_file" accept="image/*" class="s-input" style="margin-top:6px; padding:8px;">
                    <div style="font-size:12px; color:rgba(255,255,255,0.3); margin-top:4px;">JPG, PNG, WebP or GIF (max 50MB)</div>
                    @error('logo_file') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.6);">OR LOGO URL</label>
                    <input type="url" name="logo_url" value="{{ old('logo_url') }}" class="s-input" style="margin-top:6px;" placeholder="https://example.com/logo.png">
                    @error('logo_url') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.6);">SORT ORDER</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="s-input" style="margin-top:6px;">
                        @error('sort_order') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.6);">STATUS</label>
                        <label style="display:flex; align-items:center; gap:8px; margin-top:10px; cursor:pointer;">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} style="width:18px; height:18px; accent-color:#F97316;">
                            <span style="font-size:14px; color:rgba(255,255,255,0.7);">Active (visible to customers)</span>
                        </label>
                    </div>
                </div>

                <div style="display:flex; gap:12px; margin-top:8px;">
                    <button type="submit" style="
                        flex:1; padding:14px; border-radius:10px; border:none; cursor:pointer;
                        background:linear-gradient(135deg,#F97316,#ea580c); color:#fff;
                        font-family:Rajdhani,sans-serif; font-size:16px; font-weight:800; letter-spacing:2px;
                        box-shadow:0 4px 20px rgba(249,115,22,0.35); transition:all .2s;
                    " onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                        💾 CREATE PROVIDER
                    </button>
                    <a href="{{ route('dashboard.delivery-providers.index') }}" style="
                        flex:1; padding:14px; border-radius:10px; text-align:center; text-decoration:none;
                        border:1.5px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04);
                        color:rgba(255,255,255,0.4); font-family:Rajdhani,sans-serif;
                        font-size:16px; font-weight:700; letter-spacing:1px; transition:all .2s;
                    " onmouseover="this.style.borderColor='rgba(255,255,255,0.2)'; this.style.color='rgba(255,255,255,0.6)'"
                       onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.color='rgba(255,255,255,0.4)'">
                        CANCEL
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
