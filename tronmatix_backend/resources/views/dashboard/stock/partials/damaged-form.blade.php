{{-- resources/views/dashboard/stock/partials/damaged-form.blade.php --}}
<div id="damagedForm" class="stock-inline-form card" style="display:none;margin-bottom:20px;border:1px solid rgba(239,68,68,0.3);">
    <div class="card-header" style="border-bottom:1px solid rgba(239,68,68,0.2);">
        <span class="card-title" style="color:#ef4444;">⚠ {{ strtoupper(__('dashboard.stock.reportDamaged')) }}</span>
        <button onclick="closeForm('damagedForm')" style="background:none;border:none;color:var(--text-muted);font-size:var(--title-size);cursor:pointer;">✕</button>
    </div>
    <div class="card-body">
        <div style="font-size:var(--title-size);color:var(--text-muted);margin-bottom:14px;">{{ __('dashboard.stock.damaged') }}</div>
        <form method="POST" action="{{ route('dashboard.stock.damaged') }}">
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
            <div class="form-group">
                <label class="form-label">{{ __('dashboard.stock.damageNote') }}</label>
                <input type="text" name="note" class="form-control" value="{{ old('note') }}" required />
            </div>
            <button type="submit" class="btn btn-danger">{{ __('dashboard.stock.submit') }}</button>
        </form>
    </div>
</div>
