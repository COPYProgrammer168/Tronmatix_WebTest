@extends('dashboard.layout')
@section('title', strtoupper(__('dashboard.stock.reportTitle')) )

@section('content')
@php $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false; @endphp
@if(!$_permDenied)

<style>
@media print {
  body * { visibility: hidden; }
  #stockReport, #stockReport * { visibility: visible !important; }
  #stockReport { position: absolute; inset: 0; padding: 20px; }
  .no-print { display: none !important; }
}
</style>

<div id="stockReport">
  <div class="no-print" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
    <div style="display:flex; align-items:center; gap:10px;">
      <a href="{{ route('dashboard.stock.index') }}" class="btn btn-outline btn-sm">{{ __('dashboard.stock.backToStock') }}</a>
      <span style="font-size: var(--title-size); font-weight:900; letter-spacing:2px;">{{ strtoupper(__('dashboard.stock.reportTitle')) }}</span>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
      <button onclick="window.print()" class="btn btn-orange btn-sm">PRINT</button>
      <button onclick="exportExcel()" class="btn btn-outline btn-sm">EXCEL</button>
    </div>
  </div>

  <div class="card" style="margin-bottom:20px;">
    <div class="card-body">
      <form method="GET" action="{{ route('dashboard.stock.report') }}" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px; align-items:flex-end;">
        <div style="display:flex; flex-direction:column; gap:6px;">
          <label style="font-size: var(--title-size); color:var(--text-faint); letter-spacing:1px; font-weight:700;">FROM</label>
          <input type="date" name="from" value="{{ $from }}" class="form-control" style="width:100%;">
        </div>
        <div style="display:flex; flex-direction:column; gap:6px;">
          <label style="font-size: var(--title-size); color:var(--text-faint); letter-spacing:1px; font-weight:700;">TO</label>
          <input type="date" name="to" value="{{ $to }}" class="form-control" style="width:100%;">
        </div>
        <div style="display:flex; flex-direction:column; gap:6px;">
          <label style="font-size: var(--title-size); color:var(--text-faint); letter-spacing:1px; font-weight:700;">TYPE</label>
          <select name="type" class="form-control" style="width:100%;">
            <option value="">ALL</option>
            <option value="in" {{ $type === 'in' ? 'selected' : '' }}>IN</option>
            <option value="out" {{ $type === 'out' ? 'selected' : '' }}>OUT</option>
            <option value="adjustment" {{ $type === 'adjustment' ? 'selected' : '' }}>ADJUSTMENT</option>
            <option value="damaged" {{ $type === 'damaged' ? 'selected' : '' }}>DAMAGED</option>
            <option value="reversal" {{ $type === 'reversal' ? 'selected' : '' }}>REVERSAL</option>
          </select>
        </div>
        <div style="display:flex; flex-direction:column; gap:6px;">
          <label style="font-size: var(--title-size); color:var(--text-faint); letter-spacing:1px; font-weight:700;">PRODUCT</label>
          <select name="product_id" class="form-control" style="width:100%;">
            <option value="">ALL PRODUCTS</option>
            @foreach($products as $p)
              <option value="{{ $p->id }}" {{ (string)$product === (string)$p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
          </select>
        </div>
        <button type="submit" class="btn btn-orange" style="padding:10px 18px; height:42px; display:flex; align-items:center; justify-content:center;">FILTER</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr style="border-bottom:1px solid var(--border);">
            <th style="padding:12px 14px; text-align:left; letter-spacing:2px; color:var(--text-muted); font-weight:700;">{{ strtoupper(__('dashboard.stock.date')) }}</th>
            <th style="padding:12px 14px; text-align:left; letter-spacing:2px; color:var(--text-muted); font-weight:700;">PRODUCT</th>
            <th style="padding:12px 14px; text-align:center; letter-spacing:2px; color:var(--text-muted); font-weight:700;">TYPE</th>
            <th style="padding:12px 14px; text-align:center; letter-spacing:2px; color:var(--text-muted); font-weight:700;">QTY</th>
            <th style="padding:12px 14px; text-align:right; letter-spacing:2px; color:var(--text-muted); font-weight:700;">UNIT COST</th>
            <th style="padding:12px 14px; text-align:right; letter-spacing:2px; color:var(--text-muted); font-weight:700;">TOTAL</th>
            <th style="padding:12px 14px; text-align:left; letter-spacing:2px; color:var(--text-muted); font-weight:700;">NOTE</th>
            <th style="padding:12px 14px; text-align:left; letter-spacing:2px; color:var(--text-muted); font-weight:700;">PERFORMED BY</th>
          </tr>
        </thead>
        <tbody>
          @forelse($movements as $m)
            @php
              $typeMeta = match ($m->type) {
                'in'          => ['#22c55e', 'IN'],
                'out'         => ['#3b82f6', 'OUT'],
                'adjustment'  => ['#eab308', 'ADJUSTMENT'],
                'damaged'     => ['#ef4444', 'DAMAGED'],
                'reversal'    => ['#a855f7', 'REVERSAL'],
                default       => ['#6b7280', strtoupper($m->type)],
              };
              $qtyColor = $m->quantity >= 0 ? '#22c55e' : '#ef4444';
            @endphp
            <tr style="border-bottom:1px solid var(--border);">
              <td style="padding:12px 14px; color:var(--text-secondary); white-space:nowrap;">{{ $m->created_at->format('d M Y H:i') }}</td>
              <td style="padding:12px 14px; font-weight:600;">{{ $m->product?->name ?? ('#'.$m->product_id) }}</td>
              <td style="padding:12px 14px; text-align:center;">
                <span style="display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:6px; font-size: var(--title-size); font-weight:700; letter-spacing:1px; background:{{ $typeMeta[0] }}18; border:1px solid {{ $typeMeta[0] }}44; color:{{ $typeMeta[0] }};">{{ $typeMeta[1] }}</span>
              </td>
              <td style="padding:12px 14px; text-align:center; font-weight:700; color:{{ $qtyColor }};">{{ $m->quantity > 0 ? '+' : '' }}{{ $m->quantity }}</td>
              <td style="padding:12px 14px; text-align:right; color:var(--text-secondary);">{{ $m->unit_cost !== null ? '$'.number_format((float)$m->unit_cost, 2) : '—' }}</td>
              <td style="padding:12px 14px; text-align:right; font-weight:700; color:var(--text);">{{ $m->unit_cost !== null ? '$'.number_format((float)$m->unit_cost * $m->quantity, 2) : '—' }}</td>
              <td style="padding:12px 14px; color:var(--text-muted); max-width:260px; word-break:break-word;">{{ $m->note ?? '—' }}</td>
              <td style="padding:12px 14px; color:var(--text-muted);">{{ $m->createdBy?->name ?? '—' }}</td>
            </tr>
          @empty
            <tr><td colspan="8" style="padding:40px; text-align:center; color:var(--text-muted);">No stock movements found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($movements->hasPages())
      <div style="padding:14px 20px; border-top:1px solid var(--border);">{{ $movements->links('dashboard.pagination') }}</div>
    @endif
  </div>

  <form id="exportForm" method="GET" action="{{ route('dashboard.stock.export') }}" style="display:none;">
    <input type="hidden" name="from" value="{{ $from }}">
    <input type="hidden" name="to" value="{{ $to }}">
    <input type="hidden" name="type" value="{{ $type }}">
  </form>
</div>

<script>
function exportExcel() {
  document.getElementById('exportForm').submit();
}
</script>

@endif

@endsection
