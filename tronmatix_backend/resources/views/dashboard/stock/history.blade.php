@extends('dashboard.layout')
@section('title', strtoupper(__('dashboard.stock.historyTitle')))

@section('content')

@include('dashboard._permission_check', ['feature' => 'stock'])
@php $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false; @endphp
@if(!$_permDenied)

@php
    $_km = app()->getLocale() === 'km';
    $_fw7 = $_km ? 400 : 700;
    $_fw8 = $_km ? 400 : 800;
@endphp

{{-- ── Header ───────────────────────────────────────────────────────────────── --}}
<div style="display:flex;align-items:center;gap:14px;margin-bottom:24px;flex-wrap:wrap;">
    <a href="{{ route('dashboard.stock.index') }}" class="btn btn-outline btn-sm">{{ __('dashboard.stock.backToStock') }}</a>
    <div style="flex:1;min-width:0;">
        <div style="font-size: var(--title-size);font-weight:{{ $_fw8 }};letter-spacing:2px;">{{ __('dashboard.stock.historyTitle') }}</div>
        <div style="font-size: var(--title-size);color:var(--text-muted);margin-top:2px;">
            {{ __('dashboard.stock.movementsFor') }} <strong style="color:#F97316;">{{ $product->name }}</strong>
            · {{ $movements->total() }} {{ __('dashboard.stock.totalMovements') }}
        </div>
    </div>
    <div style="text-align:right;">
        <div style="font-size: var(--title-size);color:var(--text-muted);">CURRENT STOCK</div>
        <div style="font-size: var(--title-size);font-weight:{{ $_fw8 }};color:#22c55e;">{{ $product->current_stock }} {{ __('dashboard.stock.units') }}</div>
    </div>
</div>

{{-- ── Movements table ─────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="table-wrap" style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--border);">
                    <th style="padding:14px 20px;text-align:left;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">{{ strtoupper(__('dashboard.stock.date')) }}</th>
                    <th style="padding:14px 14px;text-align:center;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">{{ strtoupper(__('dashboard.stock.type')) }}</th>
                    <th style="padding:14px 14px;text-align:center;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">{{ strtoupper(__('dashboard.stock.qtyChange')) }}</th>
                    <th style="padding:14px 14px;text-align:center;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">{{ strtoupper(__('dashboard.stock.unitCost')) }}</th>
                    <th style="padding:14px 14px;text-align:left;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">NOTE</th>
                    <th style="padding:14px 14px;text-align:left;font-size: var(--title-size);letter-spacing:2px;color:var(--text-muted);font-weight:{{ $_fw7 }};">{{ strtoupper(__('dashboard.stock.performedBy')) }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $m)
                    @php
                        $typeKey = [
                            \App\Models\StockMovement::TYPE_IN => 'in',
                            \App\Models\StockMovement::TYPE_OUT => 'out',
                            \App\Models\StockMovement::TYPE_ADJUSTMENT => 'adjustment',
                            \App\Models\StockMovement::TYPE_DAMAGED => 'damaged',
                            \App\Models\StockMovement::TYPE_REVERSAL => 'reversal',
                        ][$m->type] ?? $m->type;
                        $typeMeta = match ($m->type) {
                            \App\Models\StockMovement::TYPE_IN => ['#22c55e', 'bg'],
                            \App\Models\StockMovement::TYPE_OUT => ['#3b82f6', 'bg'],
                            \App\Models\StockMovement::TYPE_ADJUSTMENT => ['#eab308', 'bg'],
                            \App\Models\StockMovement::TYPE_DAMAGED => ['#ef4444', 'bg'],
                            \App\Models\StockMovement::TYPE_REVERSAL => ['#a855f7', 'bg'],
                            default => ['#6b7280', 'bg'],
                        };
                        $qtyColor = $m->quantity >= 0 ? '#22c55e' : '#ef4444';
                        $who = $m->createdBy?->name ?? __('dashboard.stock.unknownUser');
                    @endphp
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:14px 20px;color:var(--text-secondary);white-space:nowrap;">{{ $m->created_at->format('d M Y H:i') }}</td>
                        <td style="padding:14px 14px;text-align:center;">
                            <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:6px;font-size: var(--title-size);font-weight:{{ $_fw7 }};letter-spacing:1px;background:{{ $typeMeta[0] }}18;border:1px solid {{ $typeMeta[0] }}44;color:{{ $typeMeta[0] }};">{{ $m->typeLabel() }}</span>
                        </td>
                        <td style="padding:14px 14px;text-align:center;font-weight:{{ $_fw7 }};color:{{ $qtyColor }};">{{ $m->quantity > 0 ? '+' : '' }}{{ $m->quantity }}</td>
                        <td style="padding:14px 14px;text-align:center;color:var(--text-secondary);">{{ $m->unit_cost !== null ? '$'.number_format((float)$m->unit_cost,2) : '—' }}</td>
                        <td style="padding:14px 14px;color:var(--text-muted);max-width:320px;word-break:break-word;">{{ $m->note ?? '—' }}</td>
                        <td style="padding:14px 14px;color:var(--text-muted);">{{ $who }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="padding:50px;text-align:center;color:var(--text-muted);">{{ __('dashboard.stock.noMovements') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($movements->hasPages())
        <div style="padding:16px 20px;border-top:1px solid var(--border);">
            {{ $movements->links('dashboard.pagination') }}
        </div>
    @endif
</div>

@endif
@endsection