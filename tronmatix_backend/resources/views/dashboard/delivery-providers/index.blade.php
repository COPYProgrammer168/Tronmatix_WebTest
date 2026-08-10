@extends('dashboard.layout')

@section('title', __('dashboard.nav.deliveryProviders'))

@section('content')
    @php
        $user = Auth::guard('admin')->user() ?? Auth::guard('staff')->user();
    @endphp

    <style>
        .s-input {
            width: 100%;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid var(--border-input);
            background: var(--surface-2);
            color: var(--text);
            font-size: var(--text-base);
            outline: none;
            transition: border-color .2s;
        }

        .s-input:focus {
            border-color: var(--orange);
        }

        .s-input::placeholder {
            color: var(--text-xfaint);
        }

        select.s-input {
            cursor: pointer;
        }

        /* ── Modal ──────────────────────────────────────────────────────── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: var(--overlay);
            backdrop-filter: blur(4px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background: var(--modal-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            width: 100%;
            max-width: 640px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 28px 32px;
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: 16px;
            right: 18px;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: 1px solid var(--border-input);
            background: transparent;
            color: var(--text-muted);
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .2s;
        }

        .modal-close:hover {
            background: var(--hover-bg);
            color: var(--text);
        }

        /* ── Toast ───────────────────────────────────────────────────────── */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .toast {
            padding: 12px 20px;
            border-radius: 10px;
            font-size: var(--text-sm);
            font-weight: 700;
            letter-spacing: 0.5px;
            animation: slideDown .25s ease;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Form label ──────────────────────────────────────────────────── */
        .form-label {
            font-size: var(--text-xs);
            font-weight: 700;
            letter-spacing: 1.5px;
            color: var(--text-muted);
            display: block;
            margin-bottom: 6px;
        }

        :lang(km) .form-label {
            font-family: var(--font-kh);
            font-weight: 400;
            line-height: var(--lh-kh);
            font-size: var(--text-xs);
        }

        /* ── Khmer font override for all text on this page ───────────────── */
        :lang(km) body,
        :lang(km) .s-input,
        :lang(km) .modal-box,
        :lang(km) .modal-box *,
        :lang(km) #providerTable,
        :lang(km) #providerTable *,
        :lang(km) button,
        :lang(km) .btn-text,
        :lang(km) label,
        :lang(km) input,
        :lang(km) select,
        :lang(km) textarea {
            font-family: var(--font-kh) !important;
            font-weight: 400 !important;
        }
    </style>

    <div style="max-width:1200px; margin:0 auto; padding:24px;">
        {{-- ── Modal ──────────────────────────────────────────────────────── --}}
        <div class="modal-overlay" id="providerModal">
            <div class="modal-box">
                <button class="modal-close" onclick="closeModal()">×</button>
                <h2 id="modalTitle" style="font-size:20px; font-weight:800; color:var(--text); margin-bottom:20px;">ADD
                    DELIVERY PROVIDER</h2>
                <form id="providerForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div id="methodField"></div>
                    @include('dashboard.delivery-providers._form')

                    <div style="display:flex; gap:12px; margin-top:24px;">
                        <button type="submit" class="btn" style="
                        flex:1; padding:14px; border-radius:10px; border:none; cursor:pointer;
                        background:linear-gradient(135deg,#F97316,#ea580c); color:#fff;
                        font-size:16px; font-weight:800; letter-spacing:2px;
                        box-shadow:0 4px 20px rgba(249,115,22,0.35); transition:all .2s;
                    " onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                            💾 <span id="btnText">{{ __('dashboard.btn.createProvider') }}</span>
                        </button>
                        <button type="button" class="btn" onclick="closeModal()" style="
                        flex:1; padding:14px; border-radius:10px; text-align:center;
                        border:1.5px solid var(--border-input); background:var(--surface-2);
                        color:var(--text-muted);
                        font-size:16px; font-weight:700; letter-spacing:1px; transition:all .2s;
                    " onmouseover="this.style.borderColor='var(--text-muted)'; this.style.color='var(--text)'"
                            onmouseout="this.style.borderColor='var(--border-input)'; this.style.color='var(--text-muted)'">
                            {{ __('dashboard.btn.cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div style="max-width:1200px; margin:0 auto; padding:24px;">
            {{-- Header --}}
            <div
                style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
                <div>
                    <h1
                        style="font-size:var(--text-2xl); font-weight:800; letter-spacing:2px; color:var(--text); margin:0;">
                        {{ __('dashboard.nav.deliveryProviders') }}</h1>
                    <p style="font-size:var(--text-sm); color:var(--text-muted); margin-top:4px;">
                        {{ __('Manage delivery zones, providers, fees and estimated times') }}</p>
                </div>
                <button onclick="openModal()" style="
                padding:12px 24px; border-radius:10px; border:none; cursor:pointer;
                background:linear-gradient(135deg,#F97316,#ea580c); color:#fff; display:inline-block;
                font-size:var(--text-base); font-weight:700; letter-spacing:1px;
                box-shadow:0 4px 16px rgba(249,115,22,0.3); transition:all .2s;
            " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(249,115,22,0.45)'"
                    onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 16px rgba(249,115,22,0.3)'">
                    + @lang('dashboard.btn.addProvider', [])
                </button>
            </div>

            {{-- Table --}}
            <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; overflow:hidden;"
                id="providerTable">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:var(--surface-2); border-bottom:1px solid var(--border);">
                            <th
                                style="text-align:left; padding:14px 16px; font-size:var(--text-sm); font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">
                                @lang('dashboard.form.providerName')</th>
                            <th
                                style="text-align:left; padding:14px 16px; font-size:var(--text-sm); font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">
                                ZONE RATES (PP / PROVINCE)</th>
                            <th
                                style="text-align:center; padding:14px 16px; font-size:var(--text-sm); font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">
                                @lang('dashboard.table.status')</th>
                            <th
                                style="text-align:center; padding:14px 16px; font-size:var(--text-sm); font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">
                                @lang('dashboard.table.actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($providers as $provider)
                            <tr data-id="{{ $provider->id }}"
                                style="border-bottom:1px solid var(--border-faint); transition:background .2s;"
                                onmouseover="this.style.background='var(--hover-bg)'"
                                onmouseout="this.style.background='transparent'">
                                <td style="padding:14px 16px;">
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        @if($provider->logo)
                                            <img src="{{ asset($provider->logo) }}" alt="{{ $provider->name }}"
                                                style="width:36px; height:36px; border-radius:8px; object-fit:contain; background:var(--surface-2);">
                                        @else
                                            <div
                                                style="width:36px; height:36px; border-radius:8px; background:rgba(249,115,22,0.1); border:1px solid rgba(249,115,22,0.2); display:flex; align-items:center; justify-content:center; font-size:18px;">
                                                🚚</div>
                                        @endif
                                        <div>
                                            <div class="provider-name"
                                                style="font-size:var(--text-md); font-weight:700; color:var(--text);">
                                                {{ $provider->name }}</div>
                                            <div style="font-size:var(--text-xs); color:var(--text-xfaint); margin-top:2px;">
                                                {{ __('dashboard.btn.sort') }}: {{ $provider->sort_order }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:14px 16px;">
                                    @php
                                        $ppZ = $provider->zones->firstWhere('zone', 'phnom_penh');
                                        $prZ = $provider->zones->firstWhere('zone', 'province');
                                    @endphp
                                    <div style="display:flex; flex-direction:column; gap:4px; font-size:var(--text-xs);">
                                        <span style="color:#F97316; font-weight:700;">
                                            🏙 PP: @if($ppZ) ${{ number_format($ppZ->fee ?? 0, 2) }} @if($ppZ->fee === null)
                                                <span style="color:var(--text-xfaint); font-style:italic;">varies</span> @endif ·
                                            {{ $ppZ->estimated_time ?? '—' }} @else <span
                                            style="color:var(--text-xfaint); font-style:italic;">not served</span> @endif
                                        </span>
                                        <span style="color:#3b82f6; font-weight:700;">
                                            🏞 Prov: @if($prZ) ${{ number_format($prZ->fee ?? 0, 2) }} @if($prZ->fee === null)
                                                <span style="color:var(--text-xfaint); font-style:italic;">varies</span> @endif ·
                                            {{ $prZ->estimated_time ?? '—' }} @else <span
                                            style="color:var(--text-xfaint); font-style:italic;">not served</span> @endif
                                        </span>
                                    </div>
                                </td>
                                <td style="padding:14px 16px; text-align:center;">
                                    <form method="POST" action="{{ route('dashboard.delivery-providers.toggle', $provider) }}"
                                        style="display:inline;">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn-text" style="
                                        padding:6px 14px; border-radius:20px; border:none; cursor:pointer;
                                        font-size:var(--text-xs); font-weight:700; letter-spacing:1px; transition:all .2s;
                                        background:{{ $provider->is_active ? 'rgba(34,197,94,0.12)' : 'rgba(239,68,68,0.12)' }};
                                        color:{{ $provider->is_active ? '#22c55e' : '#ef4444' }};
                                        border:1px solid {{ $provider->is_active ? 'rgba(34,197,94,0.25)' : 'rgba(239,68,68,0.25)' }};
                                    " onmouseover="this.style.transform='scale(1.05)'"
                                            onmouseout="this.style.transform='none'">
                                            {{ $provider->is_active ? '● ' . strtoupper(__('dashboard.status.active')) : '○ ' . strtoupper(__('dashboard.status.inactive')) }}
                                        </button>
                                    </form>
                                </td>
                                <td style="padding:14px 16px; text-align:center;">
                                    <div style="display:flex; gap:8px; justify-content:center;">
                                        <button type="button" onclick="openModal({{ Illuminate\Support\Js::from($provider) }})"
                                            style="
                                        padding:8px 16px; border-radius:8px; cursor:pointer;
                                        font-size:var(--text-xs); font-weight:700;
                                        background:rgba(59,130,246,0.1); color:#3b82f6; border:1px solid rgba(59,130,246,0.2);
                                        transition:all .2s;
                                    " onmouseover="this.style.background='rgba(59,130,246,0.18)'"
                                            onmouseout="this.style.background='rgba(59,130,246,0.1)'">@lang('dashboard.btn.edit')</button>
                                        <form method="POST"
                                            action="{{ route('dashboard.delivery-providers.destroy', $provider) }}"
                                            style="display:inline;" onsubmit="return confirmDelete(this)">
                                            @csrf @method('DELETE')
                                            <input type="hidden" name="delete_name" value="{{ $provider->name }}">
                                            <button type="submit" style="
                                            padding:8px 16px; border-radius:8px; border:none; cursor:pointer;
                                            font-size:var(--text-xs); font-weight:700;
                                            background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.2);
                                            transition:all .2s;
                                        " onmouseover="this.style.background='rgba(239,68,68,0.18)'"
                                                onmouseout="this.style.background='rgba(239,68,68,0.1)'">@lang('dashboard.btn.delete')</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6"
                                    style="padding:40px; text-align:center; color:var(--text-xfaint); font-size:var(--text-md);">
                                    {{ __('No delivery providers yet.') }}
                                    <a href="{{ route('dashboard.delivery-providers.create') }}"
                                        style="color:var(--orange); text-decoration:none; font-weight:700;">{{ __('Add the first one') }}</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Toast container ────────────────────────────────────────────────────── --}}
        <div class="toast-container" id="toastContainer"></div>

        @push('scripts')
            <script>
                // ── Modal Handlers ────────────────────────────────────────────────
                function openModal(provider = null) {
                    const modal = document.getElementById('providerModal');
                    const form = document.getElementById('providerForm');
                    const modalTitle = document.getElementById('modalTitle');
                    const btnText = document.getElementById('btnText');
                    const methodField = document.getElementById('methodField');

                    // Always start from a clean form
                    form.reset();
                    form.querySelector('input[name="is_active"]').checked = true;

                    if (provider) {
                        // ── Edit ──
                        modalTitle.textContent = 'EDIT DELIVERY PROVIDER';
                        btnText.textContent = '{{ __('dashboard.btn.updateProvider') }}';
                        form.action = `/dashboard/delivery-providers/${provider.id}`;
                        methodField.innerHTML = '@method('PUT')';

                        form.querySelector('input[name="name"]').value = provider.name || '';
                        form.querySelector('input[name="sort_order"]').value = provider.sort_order ?? 0;
                        form.querySelector('input[name="is_active"]').checked = !!provider.is_active;

                        // Populate per-zone fee/time sections from the loaded `zones` relation.
                        const zones = (provider.zones || []);
                        ['phnom_penh', 'province'].forEach(function (z) {
                            const zoneRow = zones.find(r => r.zone === z);
                            form.querySelector(`input[name="zone_${z}_enabled"]`).checked = !!zoneRow;
                            form.querySelector(`input[name="zone_${z}_fee"]`).value = (zoneRow && zoneRow.fee !== null && zoneRow.fee !== undefined) ? zoneRow.fee : '';
                            form.querySelector(`input[name="zone_${z}_time"]`).value = (zoneRow && zoneRow.estimated_time) ? zoneRow.estimated_time : '';
                            toggleZone(form.querySelector(`input[name="zone_${z}_enabled"]`), z);
                        });

                        // Logo — show URL if one exists, else leave file input empty for optional replace
                        const logoUrl = provider.logo_url || '';
                        const logoInput = form.querySelector('input[name="logo_url"]');
                        if (logoInput) {
                            logoInput.value = logoUrl;
                            previewLogoUrl(logoInput);
                        }
                    } else {
                        // ── Create ──
                        modalTitle.textContent = 'ADD DELIVERY PROVIDER';
                        btnText.textContent = '{{ __('dashboard.btn.createProvider') }}';
                        form.action = '{{ route('dashboard.delivery-providers.store') }}';
                        methodField.innerHTML = '';
                        ['phnom_penh', 'province'].forEach(function (z) {
                            const cb = form.querySelector(`input[name="zone_${z}_enabled"]`);
                            if (cb) toggleZone(cb, z);
                        });
                        const logoInput = form.querySelector('input[name="logo_url"]');
                        if (logoInput) previewLogoUrl(logoInput);
                    }
                    modal.classList.add('active');
                }

                function closeModal() {
                    const modal = document.getElementById('providerModal');
                    if (modal) modal.classList.remove('active');
                }

                // Simple alert-based delete confirmation.
                function confirmDelete(form) {
                    const input = form && form.querySelector('input[name="delete_name"]');
                    const name = (input && input.value) || 'this provider';
                    return confirm('Delete "' + name + '"?');
                }

                function toggleZone(cb, zone) {
                    const fields = document.querySelector('.zone-fields-' + zone);
                    if (fields) fields.style.opacity = cb.checked ? '1' : '0.35';
                }
                document.addEventListener('DOMContentLoaded', function () {
                    ['phnom_penh', 'province'].forEach(function (z) {
                        const cb = document.querySelector('input[name="zone_' + z + '_enabled"]');
                        if (cb) toggleZone(cb, z);
                    });
                });

                // ── Logo preview from file ────────────────────────────────────────
                function previewLogo(input) {
                    const container = document.getElementById('logoPreviewContainer');
                    const img = document.getElementById('logoPreviewImg');
                    const nameEl = document.getElementById('logoPreviewName');
                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            img.src = e.target.result;
                            container.style.display = 'flex';
                            nameEl.textContent = input.files[0].name;
                        };
                        reader.readAsDataURL(input.files[0]);
                    } else {
                        container.style.display = 'none';
                    }
                }

                // ── Logo preview from URL ─────────────────────────────────────────
                function previewLogoUrl(input) {
                    const container = document.getElementById('logoUrlPreviewContainer');
                    const img = document.getElementById('logoUrlPreviewImg');
                    const nameEl = document.getElementById('logoUrlPreviewName');
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
@endsection