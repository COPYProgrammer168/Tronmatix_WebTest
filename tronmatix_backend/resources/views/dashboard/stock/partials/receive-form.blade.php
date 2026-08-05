{{-- resources/views/dashboard/stock/partials/receive-form.blade.php --}}
<div id="receiveForm" class="stock-inline-form card" style="display:none;margin-bottom:20px;border:1px solid rgba(34,197,94,0.25);">
    <div class="card-header" style="border-bottom:1px solid rgba(34,197,94,0.15);">
        <span class="card-title" style="color:#22c55e;">⬇ {{ strtoupper(__('dashboard.stock.receiveStock')) }}</span>
        <button onclick="closeForm('receiveForm')" style="background:none;border:none;color:var(--text-muted);font-size:var(--title-size);cursor:pointer;">✕</button>
    </div>
    <div class="card-body">
        <div style="font-size:var(--title-size);color:var(--text-muted);margin-bottom:14px;">{{ __('dashboard.stock.receiving') }}</div>
        <form method="POST" action="{{ route('dashboard.stock.receive') }}">
            @csrf
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ __('dashboard.stock.selectProductLabel') }}</label>
                    <select name="product_id" class="form-control" required>
                        <option value="" disabled selected>{{ __('dashboard.stock.selectProduct') }}</option>
                        @foreach (\App\Models\Product::orderBy('name')->get() as $opt)
                            <option value="{{ $opt->id }}" {{ old('product_id') == $opt->id ? 'selected' : '' }}>{{ $opt->name }} ({{ $opt->current_stock }} {{ __('dashboard.stock.units') }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('dashboard.stock.quantity') }}</label>
                    <input type="number" name="quantity" class="form-control" value="{{ old('quantity') }}" min="1" required />
                </div>
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ __('dashboard.stock.unitCost') }}</label>
                    <input type="number" step="0.01" min="0" name="unit_cost" class="form-control" value="{{ old('unit_cost') }}" required />
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('dashboard.stock.note') }}</label>
                    <input type="text" name="note" class="form-control" value="{{ old('note') }}" placeholder="Optional" />
                </div>
            </div>
            <button type="submit" class="btn btn-orange">{{ __('dashboard.stock.submit') }}</button>
        </form>
    </div>
</div>
