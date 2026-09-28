{{-- resources/views/dashboard/stock/partials/reset-form.blade.php --}}
<div id="resetForm" class="stock-inline-form card" style="display:none;margin-bottom:20px;border:1px solid rgba(168,85,247,0.3);">
    <div class="card-header" style="border-bottom:1px solid rgba(168,85,247,0.18);">
        <span class="card-title" style="color:#a855f7;">🎲 {{ strtoupper(__('dashboard.stock.resetStock')) }}</span>
        <button onclick="closeForm('resetForm')" style="background:none;border:none;color:var(--text-muted);font-size:var(--title-size);cursor:pointer;">✕</button>
    </div>
    <div class="card-body">
        <div style="font-size:var(--title-size);color:var(--text-muted);margin-bottom:14px;">{{ __('dashboard.stock.resetDesc') }}</div>
        <form method="POST" action="{{ route('dashboard.stock.reset') }}" id="resetStockForm">
            @csrf
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ __('dashboard.stock.resetScope') }}</label>
                    <select name="scope" id="reset-scope" class="form-control" onchange="resetScopeChanged()">
                        <option value="all" {{ old('scope', 'all') === 'all' ? 'selected' : '' }}>{{ __('dashboard.stock.allProducts') }}</option>
                        <option value="category" {{ old('scope') === 'category' ? 'selected' : '' }}>{{ __('dashboard.stock.resetCategoryScope') }}</option>
                    </select>
                </div>
                <div class="form-group" id="reset-category-wrap" style="display:{{ old('scope') === 'category' ? 'block' : 'none' }};">
                    <label class="form-label">{{ __('dashboard.stock.category') }}</label>
                    <select name="category" id="reset-category" class="form-control">
                        <option value="" disabled selected>{{ __('dashboard.stock.resetCategoryPlaceholder') }}</option>
                        @foreach ($categories ?? [] as $cat)
                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('dashboard.stock.resetNote') }}</label>
                <input type="text" name="note" class="form-control" value="{{ old('note') }}" placeholder="Optional" />
            </div>
            <div id="reset-warn" style="font-size:var(--title-size);color:#f59e0b;background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);padding:10px 14px;border-radius:10px;margin-bottom:14px;">
                ⚠️ {{ __('dashboard.stock.resetConfirmDesc') }}
            </div>
            @if ($errors->any())
                <div style="font-size:var(--title-size);color:#ef4444;margin-bottom:12px;">
                    @foreach ($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
                </div>
            @endif
            <button type="submit" class="btn btn-orange" onclick="return confirmReset()">🎲 {{ strtoupper(__('dashboard.stock.resetStock')) }}</button>
        </form>
    </div>
</div>

<script>
function resetScopeChanged() {
    const scope = document.getElementById('reset-scope').value;
    document.getElementById('reset-category-wrap').style.display = scope === 'category' ? 'block' : 'none';
    if (scope === 'all') document.getElementById('reset-category').required = false;
}
document.addEventListener('DOMContentLoaded', resetScopeChanged);

function confirmReset() {
    const scope = document.getElementById('reset-scope').value;
    if (scope === 'category') {
        const cat = document.getElementById('reset-category').value;
        if (!cat) { alert('Please select a category first.'); return false; }
        return confirm("Reset stock for ALL products in '" + cat + "'?");
    }
    return confirm("Reset stock for ALL products? This randomizes current stock to 1–150.");
}
</script>