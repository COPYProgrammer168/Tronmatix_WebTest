@extends('dashboard.layout')

@section('title', 'EDIT MAIN CATEGORY')

@section('content')
@include('dashboard._permission_check', ['feature' => 'products'])
@php $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false; @endphp

@if(!$_permDenied)
<div style="max-width:800px; margin:0 auto; padding:24px;">
    <div style="margin-bottom:24px;">
        <a href="{{ route('dashboard.main-categories.index') }}" style="
            color:var(--text-muted); text-decoration:none; font-size:14px; font-weight:600;
            transition:color .2s;
        " onmouseover="this.style.color='#F97316'" onmouseout="this.style.color='var(--text-muted)'">← Back to main categories</a>
        <h1 style="font-size:var(--text-2xl); font-weight:800; letter-spacing:2px; color:var(--text); margin:12px 0 4px;">EDIT MAIN CATEGORY</h1>
        <p style="font-size:var(--text-sm); color:var(--text-muted);">Level 2 of the navigation tree</p>
    </div>

    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:24px;">
        <form method="POST" action="{{ route('dashboard.main-categories.update', $mainCategory) }}">
            @csrf
            @method('PUT')

            <div style="display:flex; flex-direction:column; gap:20px;">
                <div>
                    <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">PARENT CATEGORY *</label>
                    <select name="category_id" required class="s-input" style="margin-top:6px;">
                        <option value="">— Select a Category —</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $mainCategory->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">NAME *</label>
                    <input type="text" name="name" value="{{ old('name', $mainCategory->name) }}" required class="s-input" style="margin-top:6px;" placeholder="e.g. PC BUILD">
                    <div style="font-size:12px; color:var(--text-xfaint); margin-top:4px;">Current slug: /{{ $mainCategory->slug }} — regenerated automatically when the name changes.</div>
                    @error('name') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">SORT ORDER</label>
                        <input type="number" name="order" value="{{ old('order', $mainCategory->order) }}" min="0" class="s-input" style="margin-top:6px;">
                        @error('order') <div style="color:#ef4444; font-size:13px; margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block font-bold mb-2" style="font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--text-muted);">STATUS</label>
                        <label style="display:flex; align-items:center; gap:8px; margin-top:10px; cursor:pointer;">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $mainCategory->is_active) ? 'checked' : '' }} style="width:18px; height:18px; accent-color:#F97316;">
                            <span style="font-size:14px; color:var(--text-muted);">Active (visible to customers)</span>
                        </label>
                    </div>
                </div>

                <div style="display:flex; gap:12px; margin-top:8px;">
                    <button type="submit" class="btn btn-orange" style="flex:1; padding:14px;">
                        💾 UPDATE MAIN CATEGORY
                    </button>
                    <a href="{{ route('dashboard.main-categories.index') }}" class="btn" style="
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
