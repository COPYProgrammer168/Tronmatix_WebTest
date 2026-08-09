@extends('dashboard.layout')
@section('title', strtoupper(__('dashboard.nav.stock')))

@section('content')

@include('dashboard._permission_check', ['feature' => 'stock'])
@php $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false; @endphp
@if(!$_permDenied)

@php
    $_km = app()->getLocale() === 'km';
    $_fw7 = $_km ? 400 : 700;
    $_fw9 = $_km ? 400 : 900;
@endphp

{{-- ── Flash toasts ─────────────────────────────────────────────────────────── --}}
@if (session('success'))
    <div id="page-toast" style="position:fixed;top:24px;right:24px;z-index:9999;display:flex;align-items:center;gap:12px;padding:14px 22px;border-radius:16px;background:var(--dark-800,#1a1a1a);border:1px solid rgba(34,197,94,0.4);box-shadow:0 16px 48px rgba(0,0,0,0.6);font-family:Rajdhani, var(--font-kh), sans-serif;animation:stToastIn .4s cubic-bezier(0.34,1.4,0.64,1);max-width:380px;">
        <div style="width:38px;height:38px;border-radius:50%;background:rgba(34,197,94,0.15);flex-shrink:0;border:1.5px solid rgba(34,197,94,0.4);display:flex;align-items:center;justify-content:center;font-size: var(--title-size);">✓</div>
        <div style="flex:1;min-width:0;"><div style="font-size: var(--title-size);font-weight:{{ $_fw9 }};color:#22c55e;letter-spacing:1.5px;">SUCCESS</div><div style="font-size: var(--title-size);color:var(--text-muted);margin-top:2px;line-height:1.4;">{{ session('success') }}</div></div>
        <button onclick="this.closest('#page-toast').remove()" style="flex-shrink:0;width:28px;height:28px;border-radius:8px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.3);font-size: var(--title-size);cursor:pointer;">×</button>
    </div>
    <script>setTimeout(()=>{let e=document.getElementById('page-toast'); if(e)e.remove();},8000);</script>
@endif

@if (session('error') || $errors->any())
    <div id="page-err" style="position:fixed;top:24px;right:24px;z-index:9999;display:flex;align-items:center;gap:12px;padding:14px 22px;border-radius:16px;background:var(--surface-2);border:1px solid rgba(239,68,68,0.4);box-shadow:0 16px 48px rgba(0,0,0,0.6);font-family:Rajdhani, var(--font-kh), sans-serif;max-width:380px;">
        <div style="width:38px;height:38px;border-radius:50%;background:rgba(239,68,68,0.12);flex-shrink:0;border:1px solid rgba(239,68,68,0.4);display:flex;align-items:center;justify-content:center;font-size: var(--title-size);">✕</div>
        <div style="flex:1;min-width:0;">
            @if (session('error'))<div style="font-size: var(--title-size);color:var(--text-muted);">{{ session('error') }}</div>@endif
            @if ($errors->any())<div style="font-size: var(--title-size);color:#ef4444;margin-top:2px;">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>@endif
        </div>
        <button onclick="this.closest('#page-err').remove()" style="flex-shrink:0;width:28px;height:28px;border-radius:8px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.3);font-size: var(--title-size);cursor:pointer;">×</button>
    </div>
@endif

{{-- ── Header ───────────────────────────────────────────────────────────────── --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:14px;">
        <div style="width:48px;height:48px;border-radius:14px;background:rgba(249,115,22,0.12);border:1px solid rgba(249,115,22,0.3);display:flex;align-items:center;justify-content:center;font-size: var(--title-size);">📦</div>
        <div>
            <div style="font-size: var(--title-size);font-weight:{{ $_fw9 }};letter-spacing:3px;">{{ strtoupper(__('dashboard.stock.title')) }}</div>
            <div style="font-size: var(--title-size);color:var(--text-muted);margin-top:2px;">{{ __('dashboard.stock.subtitle') }}</div>
        </div>
    </div>
    <div style="display:flex;gap:10px;">
        <button onclick="openForm('receiveForm')" class="stock-action-btn"
            style="display:flex;align-items:center;gap:8px;padding:10px 18px;border-radius:10px;border:none;cursor:pointer;background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;font-family:Rajdhani, var(--font-kh), sans-serif;font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:1px;transition:all .2s;">
            ⬇ {{ __('dashboard.stock.receiveStock') }}
        </button>
        <button onclick="openForm('adjustForm')" class="stock-btn"
            style="display:flex;align-items:center;gap:8px;padding:10px 18px;border-radius:10px;border:1px solid rgba(59,130,246,0.3);cursor:pointer;background:rgba(59,130,246,0.08);color:#3b82f6;font-family:Rajdhani, var(--font-kh), sans-serif;font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:1px;">
            ⟲ {{ __('dashboard.stock.adjustStock') }}
        </button>
        <button onclick="openForm('damagedForm')" class="stock-btn"
            style="display:flex;align-items:center;gap:8px;padding:10px 18px;border-radius:10px;border:1px solid rgba(239,68,68,0.4);cursor:pointer;background:rgba(239,68,68,0.08);color:#ef4444;font-family:Rajdhani, var(--font-kh), sans-serif;font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:1px;">
            ⚠ {{ __('dashboard.stock.reportDamaged') }}
        </button>
        <button onclick="window.location.href='{{ route('dashboard.stock.report') }}'" class="stock-btn"
            style="display:flex;align-items:center;gap:8px;padding:10px 18px;border-radius:10px;border:1px solid rgba(249,115,22,0.3);cursor:pointer;background:rgba(249,115,22,0.08);color:#F97316;font-family:Rajdhani, var(--font-kh), sans-serif;font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:1px;">
            🧾 {{ __('dashboard.stock.report') }}
        </button>
    </div>
</div>

{{-- ── Inline Action Forms (hidden until opened) ─────────────────────────────--}}
@include('dashboard.stock.partials.receive-form')
@include('dashboard.stock.partials.adjust-form')
@include('dashboard.stock.partials.damaged-form')

{{-- ── Search ──────────────────────────────────────────────────────────────── --}}
<div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;align-items:center;">
    <form method="GET" action="{{ route('dashboard.stock.index') }}" id="stockFilterForm" style="position:relative;flex:1;min-width:220px;">
        <svg style="position:absolute;left:14px;top:50%;transform:translateY(-50%);opacity:0.35;" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('dashboard.stock.searchProducts') }}"
            oninput="let t=this;setTimeout(()=>{if(t.value===this.value)document.getElementById('stockFilterForm').submit()},450)"
            style="width:100%;padding:10px 14px 10px 42px;border-radius:10px;background:var(--surface-2);border:1px solid var(--border);color:var(--text);font-family:Rajdhani, var(--font-kh), sans-serif;font-size: var(--title-size);outline:none;"/>
    </form>
    <div style="display:flex;gap:8px;">
        <span class="badge badge-paid" style="font-size: var(--title-size);">{{ __('dashboard.stock.inStock') }}</span>
        <span class="badge badge-pending" style="font-size: var(--title-size);">{{ __('dashboard.stock.lowStock') }} (≤{{ $threshold }})</span>
        <span class="badge badge-cancelled" style="font-size: var(--title-size);">{{ __('dashboard.stock.outOfStock') }}</span>
    </div>
</div>

{{-- ── Stock table ─────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">{{ strtoupper(__('dashboard.nav.stock')) }}</div>
        <div style="font-size: var(--title-size);color:var(--text-muted);">{{ $products->total() }} {{ __('dashboard.stock.units') }}</div>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--border);">
                    <th style="padding:14px 20px;text-align:left;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">{{ strtoupper(__('dashboard.stock.product')) }}</th>
                    <th style="padding:14px 14px;text-align:left;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">{{ __('dashboard.stock.sku') }}</th>
                    <th style="padding:14px 14px;text-align:left;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">{{ __('dashboard.stock.category') }}</th>
                    <th style="padding:14px 14px;text-align:center;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">{{ __('dashboard.stock.currentStock') }}</th>
                    <th style="padding:14px 14px;text-align:center;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">{{ __('dashboard.stock.lowStockThreshold') }}</th>
                    <th style="padding:14px 14px;text-align:center;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">{{ __('dashboard.stock.costPrice') }}</th>
                    <th style="padding:14px 14px;text-align:right;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">{{ __('dashboard.stock.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                    @php
                        $low = $p->current_stock <= ($p->low_stock_threshold ?: $threshold) && $p->current_stock > 0;
                        $out = $p->current_stock <= 0;
                        $badge = $out ? ['badge-cancelled', '#ef4444'] : ($low ? ['badge-pending', '#eab308'] : ['badge-paid', '#22c55e']);
                        $stockValue = $p->current_stock * (float)($p->cost_price ?? 0);
                    @endphp
                    <tr style="border-bottom:1px solid var(--border);" onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background=''">
                        <td style="padding:14px 20px;">
                            <div style="display:flex;align-items:center;gap:12px;">
                                @php $img = $p->image ? (Str::startsWith($p->image,['http://','https://']) ? $p->image : asset(ltrim($p->image,'/'))) : null; @endphp
                                @if($img)<img src="{{ $img }}" style="width:40px;height:40px;border-radius:8px;object-fit:cover;flex-shrink:0;" onerror="this.style.display='none'">@endif
                                <div>
                                    <div style="font-weight:{{ $_fw7 }};color:var(--text);">{{ Str::limit($p->name, 34) }}</div>
                                    <div style="font-size: var(--title-size);color:var(--text-muted);">ID {{ $p->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:14px 14px;font-family:monospace;color:{{ ($p->sku ?? '') ? 'var(--text-secondary)' : 'var(--text-xfaint)' }};">{{ $p->sku ?? '—' }}</td>
                        <td style="padding:14px 14px;font-size: var(--title-size);color:var(--text-secondary);">{{ $p->category }}</td>
                        <td style="padding:14px 14px;text-align:center;">
                            <span class="badge {{ $badge[0] }}" style="font-size: var(--title-size);">{{ $p->current_stock }} {{ __('dashboard.stock.units') }}</span>
                        </td>
                        <td style="padding:14px 14px;text-align:center;color:var(--text-muted);">{{ $p->low_stock_threshold ?: $threshold }}</td>
                        <td style="padding:14px 14px;text-align:center;color:var(--text-secondary);">{{ $p->cost_price !== null ? '$'.number_format((float)$p->cost_price,2) : __('dashboard.stock.noCost') }}</td>
                        <td style="padding:14px 20px;text-align:right;white-space:nowrap;">
                            <a href="{{ route('dashboard.stock.history', $p) }}" class="btn btn-outline btn-sm">{{ __('dashboard.stock.history') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="padding:50px;text-align:center;color:var(--text-muted);">{{ __('dashboard.stock.emptyState') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($products->hasPages())
        <div style="padding:16px 20px;border-top:1px solid var(--border);">
            {{ $products->links('dashboard.pagination') }}
        </div>
    @endif
</div>

<script>
function openForm(id) {
    document.querySelectorAll('.stock-inline-form').forEach(f => f.style.display = 'none');
    const el = document.getElementById(id);
    if (el) { el.style.display = 'block'; el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
}
function closeForm(id) {
    document.getElementById(id).style.display = 'none';
}
// Live difference preview on adjust form
function previewAdjust(prodIdEl) {
    const sel = prodIdEl;
    const opt = sel.options[sel.selectedIndex];
    const crn = parseFloat(opt?.dataset?.current ?? 0);
    const counted = parseInt(document.getElementById('adjust-counted').value || 0, 10);
    const diff = counted - crn;
    const el = document.getElementById('adjust-diff');
    if (isNaN(diff)) { el.textContent = ''; return; }
    el.textContent = (diff === 0 ? 'No change' : (diff > 0 ? '+' : '') + diff + ' units');
    el.style.color = diff === 0 ? 'var(--text-muted)' : (diff > 0 ? '#22c55e' : '#ef4444');
}
document.addEventListener('change', function(e){ if(e.target.matches('[data-adjust-current]')) previewAdjust(); });
document.addEventListener('input', function(e){ if(e.id && e.id === 'adjust-counted') previewAdjust(); });
</script>

<style>
    @keyframes stToastIn { from{opacity:0;transform:translateX(30px) scale(.95)} to{opacity:1;transform:none} }
</style>

@endif
@endsection