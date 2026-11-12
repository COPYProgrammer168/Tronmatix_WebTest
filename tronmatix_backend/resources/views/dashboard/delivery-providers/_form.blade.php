<div style="display:flex; flex-direction:column; gap:20px;">
    <div>
        <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.6);">PROVIDER NAME *</label>
        <input type="text" name="name" value="{{ old('name', $provider->name ?? '') }}" required class="s-input" style="margin-top:6px;" placeholder="e.g. Naga Express">
        @error('name') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
    </div>

    {{-- Per-zone fee/time sections — a provider can serve each zone with its own rates --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

        {{-- PHNOM PENH zone --}}
        <div style="background:rgba(249,115,22,0.05); border:1px solid rgba(249,115,22,0.2); border-radius:12px; padding:16px;">
            <label style="display:flex; align-items:center; gap:8px; margin-bottom:12px; cursor:pointer;">
                <input type="checkbox" name="zone_phnom_penh_enabled" value="1" {{ old('zone_phnom_penh_enabled', isset($provider) ? $provider->zones->contains('zone', 'phnom_penh') : true) ? 'checked' : '' }}
                    style="width:18px; height:18px; accent-color:#F97316;"
                    onchange="toggleZone(this, 'phnom_penh')">
                <span style="font-size:14px; font-weight:800; letter-spacing:1px; color:#F97316;">🏙 PHNOM PENH</span>
            </label>
            <div class="zone-fields-phnom_penh" style="display:flex; flex-direction:column; gap:10px;">
                <div>
                    <label style="font-size:11px; font-weight:700; letter-spacing:1px; color:rgba(255,255,255,0.5);">FEE ($)</label>
                    <input type="number" name="zone_phnom_penh_fee" value="{{ old('zone_phnom_penh_fee', isset($provider) ? ($provider->zones->firstWhere('zone', 'phnom_penh')->fee ?? '') : '') }}" step="0.01" min="0" class="s-input" style="margin-top:4px;" placeholder="e.g. 1.50">
                    <div style="font-size:11px; color:rgba(255,255,255,0.3); margin-top:4px;">Empty = negotiable / varies</div>
                    @error('zone_phnom_penh_fee') <div style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:11px; font-weight:700; letter-spacing:1px; color:rgba(255,255,255,0.5);">ESTIMATED TIME</label>
                    <input type="text" name="zone_phnom_penh_time" value="{{ old('zone_phnom_penh_time', isset($provider) ? ($provider->zones->firstWhere('zone', 'phnom_penh')->estimated_time ?? '') : '') }}" class="s-input" style="margin-top:4px;" placeholder="e.g. 20-40 min">
                    @error('zone_phnom_penh_time') <div style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- PROVINCE zone --}}
        <div style="background:rgba(59,130,246,0.05); border:1px solid rgba(59,130,246,0.2); border-radius:12px; padding:16px;">
            <label style="display:flex; align-items:center; gap:8px; margin-bottom:12px; cursor:pointer;">
                <input type="checkbox" name="zone_province_enabled" value="1" {{ old('zone_province_enabled', isset($provider) ? $provider->zones->contains('zone', 'province') : false) ? 'checked' : '' }}
                    style="width:18px; height:18px; accent-color:#3b82f6;"
                    onchange="toggleZone(this, 'province')">
                <span style="font-size:14px; font-weight:800; letter-spacing:1px; color:#3b82f6;">🏞 PROVINCE</span>
            </label>
            <div class="zone-fields-province" style="display:flex; flex-direction:column; gap:10px;">
                <div>
                    <label style="font-size:11px; font-weight:700; letter-spacing:1px; color:rgba(255,255,255,0.5);">FEE ($)</label>
                    <input type="number" name="zone_province_fee" value="{{ old('zone_province_fee', isset($provider) ? ($provider->zones->firstWhere('zone', 'province')->fee ?? '') : '') }}" step="0.01" min="0" class="s-input" style="margin-top:4px;" placeholder="e.g. 3.50">
                    <div style="font-size:11px; color:rgba(255,255,255,0.3); margin-top:4px;">Empty = negotiable / varies</div>
                    @error('zone_province_fee') <div style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:11px; font-weight:700; letter-spacing:1px; color:rgba(255,255,255,0.5);">ESTIMATED TIME</label>
                    <input type="text" name="zone_province_time" value="{{ old('zone_province_time', isset($provider) ? ($provider->zones->firstWhere('zone', 'province')->estimated_time ?? '') : '') }}" class="s-input" style="margin-top:4px;" placeholder="e.g. 1-2 days">
                    @error('zone_province_time') <div style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

    </div>

    <div>
        <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.6);">LOGO</label>
        <input type="file" name="logo_file" accept="image/*" class="s-input" style="margin-top:6px; padding:8px;" id="logoFileInput"
            onchange="previewLogo(this)">
        <div style="font-size:12px; color:rgba(255,255,255,0.3); margin-top:4px;">JPG, PNG, WebP or GIF (max 50MB)</div>
        @error('logo_file') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
    </div>

    {{-- Live logo preview --}}
    <div id="logoPreviewContainer" style="display:{{ isset($provider) && $provider->logo ? 'flex' : 'none' }};">
        <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.6);">LOGO PREVIEW</label>
        <div style="display:flex; align-items:center; gap:12px;">
            <img id="logoPreviewImg" src="{{ isset($provider) && $provider->logo ? asset($provider->logo) : '' }}" alt="Logo preview" style="width:64px; height:64px; border-radius:10px; object-fit:contain; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08);">
            <span style="font-size:12px; color:rgba(255,255,255,0.3);" id="logoPreviewName">{{ isset($provider) && $provider->logo ? basename($provider->logo) : '' }}</span>
        </div>
    </div>

    <div>
        <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.6);">OR LOGO URL</label>
        <input type="url" name="logo_url" value="{{ old('logo_url', $provider->logo_url ?? '') }}" class="s-input" style="margin-top:6px;" placeholder="https://example.com/logo.png"
            oninput="previewLogoUrl(this)">
        @error('logo_url') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
    </div>

    {{-- URL preview --}}
    <div id="logoUrlPreviewContainer" style="display:{{ isset($provider) && $provider->logo_url ? 'flex' : 'none' }};">
        <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.6);">URL PREVIEW</label>
        <div style="display:flex; align-items:center; gap:12px;">
            <img id="logoUrlPreviewImg" src="{{ old('logo_url', $provider->logo_url ?? '') }}" alt="Logo URL preview" style="width:64px; height:64px; border-radius:10px; object-fit:contain; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08);">
            <span style="font-size:12px; color:rgba(255,255,255,0.3);" id="logoUrlPreviewName">{{ old('logo_url', $provider->logo_url ?? '') }}</span>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
        <div>
            <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.6);">SORT ORDER</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $provider->sort_order ?? 0) }}" min="0" class="s-input" style="margin-top:6px;">
            @error('sort_order') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.6);">STATUS</label>
            <label style="display:flex; align-items:center; gap:8px; margin-top:10px; cursor:pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', isset($provider) ? $provider->is_active : true) ? 'checked' : '' }} style="width:18px; height:18px; accent-color:#F97316;">
                <span style="font-size:14px; color:rgba(255,255,255,0.7);">Active (visible to customers)</span>
            </label>
        </div>
    </div>
</div>
