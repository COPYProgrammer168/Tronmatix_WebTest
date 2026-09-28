@extends('dashboard.layout')

@section('title', 'EDIT SUB CATEGORY')

@section('content')
@include('dashboard._permission_check', ['feature' => 'products'])
@php $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false; @endphp

@if(!$_permDenied)
<div style="max-width:800px; margin:0 auto; padding:24px;">
    <div style="margin-bottom:24px;">
        <a href="{{ route('dashboard.sub-categories.index') }}" style="
            color:var(--text-muted); text-decoration:none; font-size:14px; font-weight:600;
            transition:color .2s;
        " onmouseover="this.style.color='#F97316'" onmouseout="this.style.color='var(--text-muted)'">← Back to sub categories</a>
        <h1 style="font-size:var(--text-2xl); font-weight:800; letter-spacing:2px; color:var(--text); margin:12px 0 4px;">EDIT SUB CATEGORY</h1>
        <p style="font-size:var(--text-sm); color:var(--text-muted);">Level 3 of the navigation tree</p>
    </div>

    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:24px;">
        <form method="POST" action="{{ route('dashboard.sub-categories.update', $subCategory) }}">
            @csrf
            @method('PUT')

            <div style="display:flex; flex-direction:column; gap:20px;">
                <div>
                    <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">PARENT MAIN CATEGORY *</label>
                    <select name="main_category_id" required class="s-input" style="margin-top:6px;">
                        <option value="">— Select a Main Category —</option>
                        @foreach($mainCategories as $mc)
                            <option value="{{ $mc->id }}" {{ old('main_category_id', $subCategory->main_category_id) == $mc->id ? 'selected' : '' }}>
                                {{ $mc->category->name ?? '—' }} → {{ $mc->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('main_category_id') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">NAME *</label>
                    <input type="text" name="name" value="{{ old('name', $subCategory->name) }}" required class="s-input" style="margin-top:6px;" placeholder="e.g. UNDER 1K">
                    <div style="font-size:12px; color:var(--text-xfaint); margin-top:4px;">Current slug: /{{ $subCategory->slug }} — regenerated automatically when the name changes.</div>
                    @error('name') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">SORT ORDER</label>
                        <input type="number" name="order" value="{{ old('order', $subCategory->order) }}" min="0" class="s-input" style="margin-top:6px;">
                        @error('order') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">STATUS</label>
                        <label style="display:flex; align-items:center; gap:8px; margin-top:10px; cursor:pointer;">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $subCategory->is_active) ? 'checked' : '' }} style="width:18px; height:18px; accent-color:#F97316;">
                            <span style="font-size:14px; color:var(--text-muted);">Active (visible to customers)</span>
                        </label>
                    </div>
                </div>

                <div style="display:flex; gap:12px; margin-top:8px;">
                    <button type="submit" class="btn btn-orange" style="flex:1; padding:14px;">
                        💾 UPDATE SUB CATEGORY
                    </button>
                    <a href="{{ route('dashboard.sub-categories.index') }}" class="btn" style="
                        flex:1; padding:14px; text-align:center; text-decoration:none;
                        border:1.5px solid var(--border-input); background:transparent;
                        color:var(--text-muted);">CANCEL</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
