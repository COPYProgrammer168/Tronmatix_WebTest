@extends('dashboard.layout')

@section('title', __('dashboard.nav.deliveryProviders'))

@section('content')
@php
    $user = Auth::guard('admin')->user() ?? Auth::guard('staff')->user();
@endphp

<style>
    .s-input {
        width:100%;
        padding:10px 14px;
        border-radius:8px;
        border:1px solid var(--border-input);
        background:var(--surface-2);
        color:var(--text);
        font-size:var(--text-base);
        outline:none;
        transition:border-color .2s;
    }
    .s-input:focus {
        border-color:var(--orange);
    }
    .s-input::placeholder {
        color:var(--text-xfaint);
    }
    select.s-input {
        cursor:pointer;
    }
    /* ── Modal ──────────────────────────────────────────────────────── */
    .modal-overlay {
        display:none;
        position:fixed; inset:0;
        background:var(--overlay);
        backdrop-filter:blur(4px);
        z-index:9999;
        align-items:center;
        justify-content:center;
        padding:20px;
    }
    .modal-overlay.active {
        display:flex;
    }
    .modal-box {
        background:var(--modal-bg);
        border:1px solid var(--border);
        border-radius:20px;
        width:100%;
        max-width:640px;
        max-height:90vh;
        overflow-y:auto;
        padding:28px 32px;
        position:relative;
    }
    .modal-close {
        position:absolute; top:16px; right:18px;
        width:36px; height:36px; border-radius:10px;
        border:1px solid var(--border-input);
        background:transparent;
        color:var(--text-muted);
        font-size:20px;
        cursor:pointer;
        display:flex; align-items:center; justify-content:center;
        transition:all .2s;
    }
    .modal-close:hover {
        background:var(--hover-bg);
        color:var(--text);
    }

    /* ── Toast ───────────────────────────────────────────────────────── */
    .toast-container {
        position:fixed; top:20px; right:20px; z-index:99999;
        display:flex; flex-direction:column; gap:8px;
    }
    .toast {
        padding:12px 20px; border-radius:10px;
        font-size:var(--text-sm); font-weight:700;
        letter-spacing:0.5px;
        animation:slideDown .25s ease;
        box-shadow:0 8px 24px rgba(0,0,0,0.3);
    }
    @keyframes slideDown {
        from { opacity:0; transform:translateY(-12px); }
        to   { opacity:1; transform:translateY(0); }
    }

    /* ── Form label ──────────────────────────────────────────────────── */
    .form-label {
        font-size:var(--text-xs);
        font-weight:700;
        letter-spacing:1.5px;
        color:var(--text-muted);
        display:block;
        margin-bottom:6px;
    }
    :lang(km) .form-label {
        font-family:var(--font-kh);
        font-weight:400;
        line-height:var(--lh-kh);
    }

    /* ── Table overrides for Khmer ───────────────────────────────────── */
    :lang(km) #providerTable thead th,
    :lang(km) #providerTable tbody td {
        font-family:var(--font-kh) !important;
        font-weight:400 !important;
        line-height:var(--lh-kh);
    }
    :lang(km) #providerTable .provider-name {
        font-weight:400 !important;
    }
    :lang(km) .btn-text {
        font-family:var(--font-kh) !important;
        font-weight:400 !important;
    }
</style>

<div style="max-width:1200px; margin:0 auto; padding:24px;">
    {{-- Header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:var(--text-2xl); font-weight:800; letter-spacing:2px; color:var(--text); margin:0;">{{ __('dashboard.nav.deliveryProviders') }}</h1>
            <p style="font-size:var(--text-sm); color:var(--text-muted); margin-top:4px;">{{ __('Manage delivery zones, providers, fees and estimated times') }}</p>
        </div>
        <button onclick="openModal(null)" style="
            padding:12px 24px; border-radius:10px; border:none; cursor:pointer;
            background:linear-gradient(135deg,#F97316,#ea580c); color:#fff;
            font-size:var(--text-base); font-weight:700; letter-spacing:1px;
            box-shadow:0 4px 16px rgba(249,115,22,0.3); transition:all .2s;
        " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(249,115,22,0.45)'"
           onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 16px rgba(249,115,22,0.3)'">
            + @lang('dashboard.btn.addProvider', [])
        </button>
    </div>

    {{-- Table --}}
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; overflow:hidden;" id="providerTable">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:var(--surface-2); border-bottom:1px solid var(--border);">
                    <th style="text-align:left; padding:14px 16px; font-size:var(--text-xs); font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">@lang('dashboard.form.providerName')</th>
                    <th style="text-align:left; padding:14px 16px; font-size:var(--text-xs); font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">@lang('dashboard.form.deliveryZone')</th>
                    <th style="text-align:left; padding:14px 16px; font-size:var(--text-xs); font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">@lang('dashboard.form.fee')</th>
                    <th style="text-align:left; padding:14px 16px; font-size:var(--text-xs); font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">@lang('dashboard.form.estimatedTime')</th>
                    <th style="text-align:center; padding:14px 16px; font-size:var(--text-xs); font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">@lang('dashboard.table.status')</th>
                    <th style="text-align:center; padding:14px 16px; font-size:var(--text-xs); font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">@lang('dashboard.table.actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($providers as $provider)
                <tr data-id="{{ $provider->id }}" style="border-bottom:1px solid var(--border-faint); transition:background .2s;"
                    onmouseover="this.style.background='var(--hover-bg)'"
                    onmouseout="this.style.background='transparent'">
                    <td style="padding:14px 16px;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            @if($provider->logo)
                            <img src="{{ asset($provider->logo) }}" alt="{{ $provider->name }}" style="width:36px; height:36px; border-radius:8px; object-fit:contain; background:var(--surface-2);">
                            @else
                            <div style="width:36px; height:36px; border-radius:8px; background:rgba(249,115,22,0.1); border:1px solid rgba(249,115,22,0.2); display:flex; align-items:center; justify-content:center; font-size:18px;">🚚</div>
                            @endif
                            <div>
                                <div class="provider-name" style="font-size:var(--text-md); font-weight:700; color:var(--text);">{{ $provider->name }}</div>
                                <div style="font-size:var(--text-xs); color:var(--text-xfaint); margin-top:2px;">{{ __('Sort') }}: {{ $provider->sort_order }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="padding:14px 16px; font-size:var(--text-sm); color:var(--text-muted);">
                        {{ $provider->deliveryZone->name ?? '—' }}
                    </td>
                    <td style="padding:14px 16px;">
                        @if($provider->fee !== null)
                        <span style="font-size:var(--text-md); font-weight:800; color:var(--orange);">${{ number_format($provider->fee, 2) }}</span>
                        @else
                        <span style="font-size:var(--text-sm); color:var(--text-xfaint); font-style:italic;">{{ __('Varies') }}</span>
                        @endif
                    </td>
                    <td style="padding:14px 16px; font-size:var(--text-sm); color:var(--text-muted);">
                        {{ $provider->estimated_time ?? '—' }}
                    </td>
                    <td style="padding:14px 16px; text-align:center;">
                        <form method="POST" action="{{ route('dashboard.delivery-providers.toggle', $provider) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn-text" style="
                                padding:6px 14px; border-radius:20px; border:none; cursor:pointer;
                                font-size:var(--text-xs); font-weight:700; letter-spacing:1px; transition:all .2s;
                                background:{{ $provider->is_active ? 'rgba(34,197,94,0.12)' : 'rgba(239,68,68,0.12)' }};
                                color:{{ $provider->is_active ? '#22c55e' : '#ef4444' }};
                                border:1px solid {{ $provider->is_active ? 'rgba(34,197,94,0.25)' : 'rgba(239,68,68,0.25)' }};
                            " onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='none'">
                                {{ $provider->is_active ? '● ' . __('ACTIVE') : '○ ' . __('INACTIVE') }}
                            </button>
                        </form>
                    </td>
                    <td style="padding:14px 16px; text-align:center;">
                        <div style="display:flex; gap:8px; justify-content:center;">
                            <button onclick='openModal(@json($provider))' style="
                                padding:8px 16px; border-radius:8px; border:none; cursor:pointer;
                                font-size:var(--text-xs); font-weight:700;
                                background:rgba(59,130,246,0.1); color:#3b82f6; border:1px solid rgba(59,130,246,0.2);
                                transition:all .2s;
                            " onmouseover="this.style.background='rgba(59,130,246,0.18)'" onmouseout="this.style.background='rgba(59,130,246,0.1)'">@lang('dashboard.btn.edit')</button>
                            <form method="POST" action="{{ route('dashboard.delivery-providers.destroy', $provider) }}" style="display:inline;" onsubmit="return confirm('Delete &quot;{{ $provider->name }}&quot;?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="
                                    padding:8px 16px; border-radius:8px; border:none; cursor:pointer;
                                    font-size:var(--text-xs); font-weight:700;
                                    background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.2);
                                    transition:all .2s;
                                " onmouseover="this.style.background='rgba(239,68,68,0.18)'" onmouseout="this.style.background='rgba(239,68,68,0.1)'">@lang('dashboard.btn.delete')</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:40px; text-align:center; color:var(--text-xfaint); font-size:var(--text-md);">
                        {{ __('No delivery providers yet.') }}
                        <a href="#" onclick="openModal(null); return false;" style="color:var(--orange); text-decoration:none; font-weight:700;">{{ __('Add the first one') }}</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Create/Edit Modal ─────────────────────────────────────────────────---- --}}
<div id="providerModal" class="modal-overlay" onclick="if(event.target===this)closeModal()">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal()">×</button>

        <div style="margin-bottom:24px;">
            <h2 id="modalTitle" style="font-size:var(--text-xl); font-weight:800; letter-spacing:1px; color:var(--text); margin:0;">@lang('dashboard.btn.addProvider')</h2>
            <p id="modalSub" style="font-size:var(--text-sm); color:var(--text-muted); margin-top:4px;">{{ __('Fill in the provider details below') }}</p>
        </div>

        <form id="providerForm" method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:20px;">
            @csrf
            <input type="hidden" id="formMethod" name="_method" value="POST">
            <input type="hidden" id="providerId" value="">

            {{-- Delivery Zone --}}
            <div>
                <label class="form-label">@lang('dashboard.form.deliveryZone') *</label>
                <select id="zoneSelect" name="delivery_zone_id" required class="s-input">
                    <option value="">— @lang('Select zone') —</option>
                    @foreach($zones as $zone)
                    <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                    @endforeach
                </select>
                <div class="field-error" id="zoneError" style="color:#ef4444; font-size:var(--text-xs); margin-top:4px; display:none;"></div>
            </div>

            {{-- Provider Name --}}
            <div>
                <label class="form-label">@lang('dashboard.form.providerName') *</label>
                <input type="text" id="nameInput" name="name" required class="s-input" placeholder="e.g. Naga Express">
                <div class="field-error" id="nameError" style="color:#ef4444; font-size:var(--text-xs); margin-top:4px; display:none;"></div>
            </div>

            {{-- Fee + Estimated Time --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div>
                    <label class="form-label">@lang('dashboard.form.fee') ($)</label>
                    <input type="number" id="feeInput" name="fee" step="0.01" min="0" class="s-input" placeholder="e.g. 2.50">
                    <div style="font-size:var(--text-xs); color:var(--text-xfaint); margin-top:4px;">{{ __('Leave empty for negotiable / varies') }}</div>
                </div>
                <div>
                    <label class="form-label">@lang('dashboard.form.estimatedTime')</label>
                    <input type="text" id="timeInput" name="estimated_time" class="s-input" placeholder="e.g. 30–60 min">
                </div>
            </div>

            {{-- Current Logo (preview for edit) --}}
            <div id="currentLogoSection" style="display:none;">
                <label class="form-label">{{ __('Current Logo') }}</label>
                <div style="display:flex; align-items:center; gap:16px; margin-top:6px;">
                    <img id="currentLogoImg" src="" alt="" style="width:64px; height:64px; border-radius:10px; object-fit:contain; background:var(--surface-2); border:1px solid var(--border);">
                    <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size:var(--text-sm); color:var(--text-muted);">
                        <input type="checkbox" name="remove_logo" value="1" style="width:16px; height:16px; accent-color:#ef4444;">
                        @lang('dashboard.form.removeLogo')
                    </label>
                </div>
            </div>

            {{-- File Upload --}}
            <div>
                <label class="form-label">@lang('dashboard.form.uploadLogo')</label>
                <input type="file" id="logoFileInput" name="logo_file" accept="image/*" class="s-input" style="padding:8px;" onchange="previewNewLogo(this)">
                <div style="font-size:var(--text-xs); color:var(--text-xfaint); margin-top:4px;">JPG, PNG, WebP or GIF (max 50MB)</div>
            </div>

            {{-- New logo preview --}}
            <div id="logoPreviewContainer" style="display:none;">
                <label class="form-label">{{ __('Logo Preview') }}</label>
                <div style="display:flex; align-items:center; gap:12px;">
                    <img id="logoPreviewImg" src="" alt="" style="width:64px; height:64px; border-radius:10px; object-fit:contain; background:var(--surface-2); border:1px solid var(--border);">
                    <span style="font-size:var(--text-xs); color:var(--text-xfaint);" id="logoPreviewName"></span>
                </div>
            </div>

            {{-- Logo URL --}}
            <div>
                <label class="form-label">@lang('dashboard.form.logoUrl')</label>
                <input type="url" id="logoUrlInput" name="logo_url" class="s-input" placeholder="https://example.com/logo.png" oninput="previewLogoUrl(this)">
            </div>

            {{-- URL preview --}}
            <div id="logoUrlPreviewContainer" style="display:none;">
                <label class="form-label">{{ __('URL Preview') }}</label>
                <div style="display:flex; align-items:center; gap:12px;">
                    <img id="logoUrlPreviewImg" src="" alt="" style="width:64px; height:64px; border-radius:10px; object-fit:contain; background:var(--surface-2); border:1px solid var(--border);">
                    <span style="font-size:var(--text-xs); color:var(--text-xfaint);" id="logoUrlPreviewName"></span>
                </div>
            </div>

            {{-- Sort + Active --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div>
                    <label class="form-label">{{ __('Sort Order') }}</label>
                    <input type="number" id="sortInput" name="sort_order" value="0" min="0" class="s-input">
                </div>
                <div>
                    <label class="form-label">@lang('dashboard.table.status')</label>
                    <label style="display:flex; align-items:center; gap:8px; margin-top:10px; cursor:pointer;">
                        <input type="checkbox" id="activeCheck" name="is_active" value="1" checked style="width:18px; height:18px; accent-color:#F97316;">
                        <span style="font-size:var(--text-sm); color:var(--text-muted);">{{ __('Active (visible to customers)') }}</span>
                    </label>
                </div>
            </div>

            {{-- Submit --}}
            <div style="display:flex; gap:12px; margin-top:8px;">
                <button type="submit" id="submitBtn" style="
                    flex:1; padding:14px; border-radius:10px; border:none; cursor:pointer;
                    background:linear-gradient(135deg,#F97316,#ea580c); color:#fff;
                    font-size:var(--text-base); font-weight:800; letter-spacing:2px;
                    box-shadow:0 4px 20px rgba(249,115,22,0.35); transition:all .2s;
                " onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                    💾 <span id="submitLabel">@lang('dashboard.btn.createProvider')</span>
                </button>
                <button type="button" onclick="closeModal()" style="
                    flex:1; padding:14px; border-radius:10px; text-align:center; text-decoration:none;
                    border:1.5px solid var(--border-input); background:transparent;
                    color:var(--text-muted); font-size:var(--text-base); font-weight:700; letter-spacing:1px;
                    cursor:pointer; transition:all .2s;
                " onmouseover="this.style.borderColor='rgba(255,255,255,0.2)'; this.style.color='var(--text)'"
                   onmouseout="this.style.borderColor='var(--border-input)'; this.style.color='var(--text-muted)'">
                    @lang('dashboard.btn.cancel')
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Toast container ────────────────────────────────────────────────────── --}}
<div class="toast-container" id="toastContainer"></div>

@push('scripts')
<script>
    // ── Show toast ────────────────────────────────────────────────────
    function showToast(msg, color = '#22C55E') {
        const c = document.getElementById('toastContainer')
        const t = document.createElement('div')
        t.className = 'toast'
        t.style.background = color
        t.style.color = '#fff'
        t.textContent = msg
        c.appendChild(t)
        setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300) }, 3500)
    }

    // ── Open modal ────────────────────────────────────────────────────
    function openModal(provider) {
        const isEdit = provider && provider.id

        document.getElementById('modalTitle').textContent = isEdit ? '{{ __("Edit Provider") }}' : '{{ __("dashboard.btn.addProvider") }}'
        document.getElementById('modalSub').textContent  = isEdit ? '{{ __("Update provider details, logo and settings") }}' : '{{ __("Fill in the provider details below") }}'
        document.getElementById('submitLabel').textContent = isEdit ? '{{ __("dashboard.btn.updateProvider") }}' : '{{ __("dashboard.btn.createProvider") }}'

        // Reset form
        document.getElementById('providerForm').reset()
        document.getElementById('zoneError').style.display = 'none'
        document.getElementById('nameError').style.display = 'none'
        document.getElementById('logoPreviewContainer').style.display = 'none'
        document.getElementById('logoUrlPreviewContainer').style.display = 'none'
        document.getElementById('currentLogoSection').style.display = 'none'

        if (isEdit) {
            // Fill form with provider data
            document.getElementById('providerId').value = provider.id
            document.getElementById('formMethod').value = 'PUT'
            document.getElementById('providerForm').action = '{{ url("dashboard/delivery-providers") }}/' + provider.id
            document.getElementById('zoneSelect').value = provider.delivery_zone_id
            document.getElementById('nameInput').value = provider.name
            document.getElementById('feeInput').value = provider.fee ?? ''
            document.getElementById('timeInput').value = provider.estimated_time ?? ''
            document.getElementById('sortInput').value = provider.sort_order ?? 0
            document.getElementById('activeCheck').checked = provider.is_active

            // Show current logo if exists
            if (provider.logo) {
                const img = document.getElementById('currentLogoImg')
                img.src = provider.logo.startsWith('http') ? provider.logo : '{{ asset("") }}' + provider.logo
                document.getElementById('currentLogoSection').style.display = 'block'
            }
        } else {
            // Fresh form
            document.getElementById('providerId').value = ''
            document.getElementById('formMethod').value = 'POST'
            document.getElementById('providerForm').action = '{{ route("dashboard.delivery-providers.store") }}'
            document.getElementById('zoneSelect').value = ''
            document.getElementById('nameInput').value = ''
            document.getElementById('feeInput').value = ''
            document.getElementById('timeInput').value = ''
            document.getElementById('sortInput').value = '0'
            document.getElementById('activeCheck').checked = true
        }

        document.getElementById('providerModal').classList.add('active')
    }

    // ── Close modal ───────────────────────────────────────────────────
    function closeModal() {
        document.getElementById('providerModal').classList.remove('active')
    }

    // ── Logo preview from file ────────────────────────────────────────
    function previewNewLogo(input) {
        const container = document.getElementById('logoPreviewContainer')
        const img = document.getElementById('logoPreviewImg')
        const nameEl = document.getElementById('logoPreviewName')
        if (input.files && input.files[0]) {
            const reader = new FileReader()
            reader.onload = function(e) {
                img.src = e.target.result
                container.style.display = 'flex'
                nameEl.textContent = input.files[0].name
            }
            reader.readAsDataURL(input.files[0])
        } else {
            container.style.display = 'none'
        }
    }

    // ── Logo preview from URL ─────────────────────────────────────────
    function previewLogoUrl(input) {
        const container = document.getElementById('logoUrlPreviewContainer')
        const img = document.getElementById('logoUrlPreviewImg')
        const nameEl = document.getElementById('logoUrlPreviewName')
        if (input.value.trim()) {
            img.src = input.value
            container.style.display = 'flex'
            nameEl.textContent = input.value
        } else {
            container.style.display = 'none'
        }
    }

    // ── Close modal on Escape key ─────────────────────────────────────
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal()
    })
</script>
@endpush
@endsection
