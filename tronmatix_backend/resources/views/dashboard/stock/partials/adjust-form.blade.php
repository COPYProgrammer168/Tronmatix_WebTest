{{-- resources/views/dashboard/stock/partials/adjust-form.blade.php --}}
<div id="adjustForm" class="stock-inline-form card" style="display:none;margin-bottom:20px;border:1px solid rgba(59,130,246,0.25);">
    <div class="card-header" style="border-bottom:1px solid rgba(59,130,246,0.15);">
        <span class="card-title" style="color:#3b82f6;">⟲ {{ strtoupper(__('dashboard.stock.adjustStock')) }}</span>
        <button onclick="closeForm('adjustForm')" style="background:none;border:none;color:var(--text-muted);font-size:var(--title-size);cursor:pointer;">✕</button>
    </div>
    <div class="card-body">
        <div style="font-size:var(--title-size);color:var(--text-muted);margin-bottom:14px;">{{ __('dashboard.stock.adjusting') }}</div>
        <form method="POST" action="{{ route('dashboard.stock.adjust') }}">
            @csrf
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ __('dashboard.stock.selectProductLabel') }}</label>
                    <select name="product_id" id="adjust-product" class="form-control" required data-adjust-current="1" onchange="previewAdjust()">
                        <option value="" disabled selected>{{ __('dashboard.stock.selectProduct') }}</option>
                        @foreach (\App\Models\Product::orderBy('name')->get() as $opt)
                            <option value="{{ $opt->id }}" data-current="{{ $opt->current_stock }}" {{ old('product_id') == $opt->id ? 'selected' : '' }}>{{ $opt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('dashboard.stock.countedQuantity') }}</label>
                    <input type="number" id="adjust-counted" name="counted_quantity" class="form-control" value="{{ old('counted_quantity') }}" min="0" required />
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('dashboard.stock.note') }}</label>
                <input type="text" name="note" class="form-control" value="{{ old('note') }}" placeholder="Optional" />
            </div>
            <div id="adjust-diff" style="font-size:var(--title-size);color:var(--text-muted);margin-bottom:12px;"></div>
            <button type="submit" class="btn btn-orange">{{ __('dashboard.stock.submit') }}</button>
        </form>
    </div>
</div>
